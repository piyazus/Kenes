"""Integration with Anthropic Claude API for RAG over project documents."""

from __future__ import annotations

import asyncio
import logging
import os
from typing import Dict, List

from anthropic import AsyncAnthropic, APIStatusError

from app.models.document import Document
from app.services.document_processor import chunk_text, extract_text


logger = logging.getLogger(__name__)


class ClaudeService:
    """Wrapper around Anthropic API with RAG helpers."""

    def __init__(self, api_key: str):
        if not api_key:
            raise RuntimeError("Anthropic API key is not configured")
        self.client = AsyncAnthropic(api_key=api_key)

    async def query_with_context(
        self,
        query: str,
        documents: List[Document],
        max_context_length: int = 100_000,
    ) -> Dict[str, object]:
        """
        RAG-запрос: извлекает тексты, чанкует, формирует промпт и спрашивает Claude.

        Returns: {"answer": str, "sources": [doc_id]}
        """
        texts: List[str] = []
        sources: List[str] = []

        for doc in documents:
            text = await extract_text(doc.file_path)
            if not text:
                continue
            chunks = await chunk_text(text)
            for ch in chunks:
                if len(ch) == 0:
                    continue
                if sum(len(t) for t in texts) + len(ch) > max_context_length:
                    break
                texts.append(ch)
                sources.append(str(doc.id))

        if not texts:
            raise RuntimeError("No readable document content for RAG query")

        context_text = "\n\n---\n\n".join(texts)
        system_prompt = (
            "You are KenesCloud Council, a strategic consulting assistant. "
            "Answer strictly based on the provided project documents. "
            "If the answer is not in the documents, say you don't know."
        )

        try:
            resp = await self.client.messages.create(
                model="claude-3-opus-20240229",
                max_tokens=1024,
                temperature=0.1,
                system=system_prompt,
                messages=[
                    {
                        "role": "user",
                        "content": [
                            {
                                "type": "text",
                                "text": f"CONTEXT:\n{context_text}\n\nQUESTION:\n{query}",
                            }
                        ],
                    }
                ],
            )
        except APIStatusError as exc:  # type: ignore[match]
            logger.error("Claude API error: %s", exc)
            raise RuntimeError(f"Claude API error: {exc}") from exc

        parts = []
        for block in resp.content:
            if getattr(block, "type", None) == "text":
                parts.append(block.text)
        answer = "\n".join(parts).strip()
        return {"answer": answer, "sources": sources}

    async def stream_response(self, query: str, context: str):
        """Streaming response for real-time chat."""
        try:
            stream = await self.client.messages.create(
                model="claude-3-opus-20240229",
                max_tokens=1024,
                temperature=0.3,
                system="You are KenesCloud Council assistant.",
                messages=[
                    {
                        "role": "user",
                        "content": [
                            {
                                "type": "text",
                                "text": f"CONTEXT:\n{context}\n\nQUESTION:\n{query}",
                            }
                        ],
                    }
                ],
                stream=True,
            )
            async for event in stream:
                yield event
        except APIStatusError as exc:  # type: ignore[match]
            logger.error("Claude API stream error: %s", exc)
            raise RuntimeError(f"Claude API stream error: {exc}") from exc


