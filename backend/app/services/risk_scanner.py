"""Risk scanner service for detecting risks in documents using keyword detection and Claude AI."""

from __future__ import annotations

import json
import logging
import os
import re
from typing import Dict, List
from uuid import UUID

from anthropic import AsyncAnthropic, APIStatusError
from sqlalchemy.orm import Session

from app.core.config import settings
from app.models.document import Document
from app.models.risk_alert import AlertType, RiskAlert, RiskSeverity, RiskStatus
from app.services.document_processor import extract_text

logger = logging.getLogger(__name__)


class RiskScannerService:
    """Scans documents for potential risks using keyword detection + Claude AI."""

    # Keywords to look for in each risk category
    RISK_KEYWORDS: Dict[str, List[str]] = {
        "financial_risk": [
            "bankruptcy",
            "insolvency",
            "debt",
            "default",
            "cash flow",
            "liquidity",
            "financial distress",
            "credit risk",
            "payment default",
        ],
        "compliance": [
            "violation",
            "regulatory",
            "non-compliant",
            "audit",
            "investigation",
            "compliance issue",
            "regulatory action",
            "legal violation",
        ],
        "market_change": [
            "competitor",
            "market share",
            "disruption",
            "new entrant",
            "market shift",
            "competitive threat",
            "market decline",
        ],
        "operational": [
            "delay",
            "shortage",
            "supply chain",
            "capacity",
            "operational risk",
            "production issue",
            "logistics problem",
        ],
    }

    def __init__(self, api_key: str | None = None):
        """Initialize the risk scanner with Claude API key."""
        self.api_key = api_key or os.getenv("ANTHROPIC_API_KEY", "")
        if not self.api_key:
            raise RuntimeError("Anthropic API key is not configured")
        self.client = AsyncAnthropic(api_key=self.api_key)

    async def scan_document(
        self, db: Session, document_id: UUID
    ) -> List[RiskAlert]:
        """
        Scan a single document for risks.

        1. Get document text from database
        2. Run keyword detection for each risk category
        3. For each match, extract surrounding context (±200 chars)
        4. Use Claude API to validate and analyze risks
        5. Create RiskAlert records in database
        6. Return list of created alerts
        """
        document = db.query(Document).filter(Document.id == document_id).first()
        if not document:
            logger.warning("Document not found: %s", document_id)
            return []

        # Get document text
        if document.extracted_text:
            text = document.extracted_text
        else:
            # Extract text if not already extracted
            text = await extract_text(document.file_path)
            if not text:
                logger.warning("Could not extract text from document: %s", document_id)
                return []

        # Detect keywords
        keyword_matches = self._detect_keywords(text)

        if not keyword_matches:
            logger.info("No risk keywords found in document: %s", document_id)
            return []

        # Analyze each match with Claude
        alerts: List[RiskAlert] = []
        for category, matches in keyword_matches.items():
            for match_info in matches:
                keyword = match_info["keyword"]
                position = match_info["position"]
                context = self._extract_context(text, position, window=200)

                try:
                    analysis = await self._analyze_with_claude(
                        context, keyword, category
                    )

                    if analysis.get("is_risk", False):
                        alert = RiskAlert(
                            project_id=document.project_id,
                            document_id=document.id,
                            alert_type=AlertType(category),
                            severity=RiskSeverity(analysis.get("severity", "medium")),
                            title=analysis.get("title", f"{category} risk detected"),
                            description=analysis.get("description"),
                            source_text=context,
                            recommendation=analysis.get("recommendation"),
                            status=RiskStatus.NEW,
                        )
                        db.add(alert)
                        alerts.append(alert)
                        logger.info(
                            "Risk alert created: %s - %s",
                            category,
                            analysis.get("severity"),
                        )
                except Exception as e:
                    logger.error(
                        "Error analyzing risk with Claude: %s - %s", category, e
                    )
                    continue

        db.commit()
        return alerts

    async def scan_project(
        self, db: Session, project_id: UUID
    ) -> List[RiskAlert]:
        """Scan all documents in a project for risks."""
        documents = (
            db.query(Document)
            .filter(Document.project_id == project_id)
            .all()
        )

        all_alerts: List[RiskAlert] = []
        for document in documents:
            alerts = await self.scan_document(db, document.id)
            all_alerts.extend(alerts)

        return all_alerts

    def _detect_keywords(self, text: str) -> Dict[str, List[Dict[str, int]]]:
        """
        Find all keyword matches and their positions.

        Returns dict mapping category to list of matches with position.
        """
        text_lower = text.lower()
        matches: Dict[str, List[Dict[str, int]]] = {}

        for category, keywords in self.RISK_KEYWORDS.items():
            category_matches: List[Dict[str, int]] = []
            for keyword in keywords:
                keyword_lower = keyword.lower()
                # Find all occurrences
                for match in re.finditer(re.escape(keyword_lower), text_lower):
                    category_matches.append(
                        {"keyword": keyword, "position": match.start()}
                    )
            if category_matches:
                matches[category] = category_matches

        return matches

    def _extract_context(self, text: str, position: int, window: int = 200) -> str:
        """Extract surrounding context from text around a position."""
        start = max(0, position - window)
        end = min(len(text), position + window)
        return text[start:end].strip()

    async def _analyze_with_claude(
        self, context: str, keyword: str, category: str
    ) -> Dict:
        """
        Call Claude API to analyze if a detected keyword represents a real risk.

        Returns dict with:
        - is_risk: bool
        - severity: "low" | "medium" | "high" | "critical"
        - title: str
        - description: str (optional)
        - recommendation: str (optional)
        """
        prompt = f"""You are analyzing a business document for risks.

Context from document:
{context}

Keyword found: "{keyword}"
Category: {category}

Is this a real risk that requires attention? If yes, provide:
- severity: low/medium/high/critical
- title: short risk description (max 100 chars)
- description: brief explanation (optional)
- recommendation: 1-2 sentence action item (optional)

Respond in JSON format:
{{
    "is_risk": true/false,
    "severity": "low|medium|high|critical" (only if is_risk is true),
    "title": "short title" (only if is_risk is true),
    "description": "explanation" (optional),
    "recommendation": "action item" (optional)
}}

If it's not a real risk, return {{"is_risk": false}}."""

        try:
            resp = await self.client.messages.create(
                model="claude-sonnet-4-20250514",
                max_tokens=512,
                temperature=0.1,
                system="You are a risk analysis expert for business documents. Be precise and conservative.",
                messages=[
                    {
                        "role": "user",
                        "content": [{"type": "text", "text": prompt}],
                    }
                ],
            )

            # Extract text response
            parts = []
            for block in resp.content:
                if getattr(block, "type", None) == "text":
                    parts.append(block.text)

            response_text = "\n".join(parts).strip()

            # Try to parse JSON from response
            # Sometimes Claude wraps JSON in markdown code blocks
            json_match = re.search(r"\{[^{}]*\}", response_text, re.DOTALL)
            if json_match:
                response_text = json_match.group(0)

            analysis = json.loads(response_text)
            return analysis

        except json.JSONDecodeError as e:
            logger.error("Failed to parse Claude response as JSON: %s", e)
            return {"is_risk": False}
        except APIStatusError as e:
            logger.error("Claude API error: %s", e)
            raise RuntimeError(f"Claude API error: {e}") from e
        except Exception as e:
            logger.error("Unexpected error in Claude analysis: %s", e)
            return {"is_risk": False}

