"""Council router: document upload and RAG chat."""

from __future__ import annotations

import logging
from pathlib import Path
from typing import Any, List
from uuid import UUID
import os

from fastapi import (
    APIRouter,
    Depends,
    File,
    HTTPException,
    UploadFile,
    BackgroundTasks,
    status,
)
from sqlalchemy.orm import Session
from pydantic import BaseModel

from app.core.deps import TestUser, get_current_project, get_current_user, get_db
from app.db.session import SessionLocal
from app.crud import document as document_crud
from app.models.chat_message import ChatMessage, ChatRole
from app.models.document import Document
from app.models.project import Project
from app.models.risk_alert import RiskAlert, RiskSeverity, RiskStatus
from app.schemas.document import DocumentCreate, DocumentResponse, DocumentUploadResponse
from app.schemas.risk_alert import RiskAlertResponse, RiskAlertUpdate
from app.services.claude_service import ClaudeService
from app.services.risk_scanner import RiskScannerService
from app.services.notification_service import NotificationService
from app.tasks.document_tasks import process_document
from app.core.config import settings


logger = logging.getLogger(__name__)

router = APIRouter(prefix="/council", tags=["council"])

UPLOAD_ROOT = Path("uploads")


@router.post(
    "/upload",
    response_model=DocumentUploadResponse,
    status_code=status.HTTP_201_CREATED,
)
async def upload_document(
    background_tasks: BackgroundTasks,
    project: Any = Depends(get_current_project),
    file: UploadFile = File(...),
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> DocumentUploadResponse:
    """Upload a document for a project and create a Document record."""
    project_id: UUID = project.id  # type: ignore[assignment]

    # Save file to local storage: ./uploads/{tenant_id}/{project_id}/
    target_dir = UPLOAD_ROOT / str(current_user.tenant_id) / str(project_id)
    target_dir.mkdir(parents=True, exist_ok=True)
    target_path = target_dir / file.filename

    content = await file.read()
    with target_path.open("wb") as f:
        f.write(content)

    # Determine file type from extension
    file_type = file.content_type or "application/octet-stream"
    if not file.content_type:
        ext = Path(file.filename).suffix.lower()
        if ext == ".pdf":
            file_type = "application/pdf"
        elif ext in [".docx", ".doc"]:
            file_type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
        elif ext == ".txt":
            file_type = "text/plain"

    doc_in = DocumentCreate(
        project_id=project_id,
        file_name=file.filename,
        file_path=str(target_path),
        file_type=file_type,
    )
    db_doc: Document = document_crud.create_document(
        db,
        obj_in=doc_in,
        uploaded_by=current_user.id,
    )

    logger.info(
        "Document uploaded id=%s project_id=%s tenant_id=%s",
        db_doc.id,
        project_id,
        current_user.tenant_id,
    )

    # Kick off background text extraction / embedding status update
    background_tasks.add_task(process_document, SessionLocal, str(db_doc.id))
    # Also scan for risks after document is processed
    background_tasks.add_task(
        scan_document_risks_background,
        document_id=db_doc.id,
        project_owner_id=current_user.id,
    )

    return DocumentUploadResponse(
        id=db_doc.id,
        project_id=db_doc.project_id,
        file_name=db_doc.file_name,
        url=str(target_path),
    )


@router.get("/documents/{project_id}", response_model=List[DocumentResponse])
def list_project_documents(
    project: Any = Depends(get_current_project),
    db: Session = Depends(get_db),
) -> List[DocumentResponse]:
    """List all documents for a given project."""
    docs = document_crud.list_documents_for_project(db, project_id=project.id)
    return docs


@router.delete(
    "/documents/{document_id}",
    status_code=status.HTTP_204_NO_CONTENT,
)
def delete_document(
    document_id: UUID,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> None:
    """Delete a document if it belongs to a project of the current tenant."""
    db_doc = document_crud.get_document(db, document_id=document_id)
    if not db_doc:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Document not found",
        )
    project = db_doc.project
    if project.tenant_id != current_user.tenant_id:  # type: ignore[comparison-overlap]
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Not enough permissions to delete this document",
        )

    # Optionally delete file from disk
    try:
        path_obj = Path(db_doc.file_path)
        if path_obj.exists():
            path_obj.unlink()
    except OSError:
        logger.warning("Failed to delete file at %s", db_doc.file_path)

    document_crud.delete_document(db, db_obj=db_doc)
    logger.info(
        "Document deleted id=%s project_id=%s tenant_id=%s",
        document_id,
        project.id,
        current_user.tenant_id,
    )


class CouncilQueryRequest(BaseModel):
    project_id: UUID
    query: str


class CouncilQueryResponse(BaseModel):
    answer: str
    sources: List[UUID]


@router.post("/query", response_model=CouncilQueryResponse)
async def council_query(
    payload: CouncilQueryRequest,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> CouncilQueryResponse:
    """Run a RAG query over project documents using Claude."""
    project = (
        db.query(Project)
        .filter(Project.id == payload.project_id, Project.tenant_id == current_user.tenant_id)
        .first()
    )
    if not project:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found",
        )

    docs = document_crud.list_documents_for_project(db, project_id=project.id)
    if not docs:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail="No documents available for this project",
        )

    contents: List[str] = []
    used_ids: List[UUID] = []
    for d in docs:
        try:
            path_obj = Path(d.file_path)
            if not path_obj.exists():
                continue
            text = path_obj.read_text(encoding="utf-8", errors="ignore")
            contents.append(text)
            used_ids.append(d.id)
        except OSError:
            continue

    if not contents:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail="Documents could not be read",
        )

    api_key = os.getenv("ANTHROPIC_API_KEY", "") or getattr(settings, "anthropic_api_key", "")
    if not api_key:
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail="Claude API key not configured",
        )
    service = ClaudeService(api_key=api_key)
    rag_result = await service.query_with_context(payload.query, docs)
    answer = rag_result["answer"]
    sources_ids = [UUID(s) for s in rag_result.get("sources", [])]

    # Store chat messages (user + assistant)
    user_msg = ChatMessage(
        project_id=project.id,
        user_id=current_user.id,
        role=ChatRole.USER,
        content=payload.query,
    )
    assistant_msg = ChatMessage(
        project_id=project.id,
        user_id=None,
        role=ChatRole.ASSISTANT,
        content=answer,
    )
    db.add_all([user_msg, assistant_msg])
    db.commit()

    logger.info(
        "Council query executed project_id=%s tenant_id=%s",
        project.id,
        current_user.tenant_id,
    )

    return CouncilQueryResponse(answer=answer, sources=sources_ids or used_ids)


@router.post("/projects/{project_id}/scan-risks", response_model=dict)
async def scan_project_risks(
    project_id: UUID,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> dict:
    """Manually trigger risk scan on all project documents."""
    # Verify user has access to project
    project = (
        db.query(Project)
        .filter(Project.id == project_id, Project.tenant_id == current_user.tenant_id)
        .first()
    )
    if not project:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found",
        )

    # Get API key
    api_key = os.getenv("ANTHROPIC_API_KEY", "") or getattr(
        settings, "anthropic_api_key", ""
    )
    if not api_key:
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail="Claude API key not configured",
        )

    # Scan for risks
    scanner = RiskScannerService(api_key=api_key)
    alerts = await scanner.scan_project(db, project_id)

    # Create notifications for each risk found
    notif_service = NotificationService()
    for alert in alerts:
        await notif_service.notify_risk_detected(db, alert, current_user.id)

    logger.info(
        "Risk scan completed project_id=%s tenant_id=%s alerts=%d",
        project_id,
        current_user.tenant_id,
        len(alerts),
    )

    return {
        "project_id": project_id,
        "alerts_found": len(alerts),
        "alerts": [RiskAlertResponse.model_validate(a) for a in alerts],
    }


@router.get("/projects/{project_id}/risks", response_model=List[RiskAlertResponse])
async def get_project_risks(
    project_id: UUID,
    severity: str | None = None,
    risk_status: str | None = None,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> List[RiskAlertResponse]:
    """Get all risk alerts for a project with optional filtering."""
    # Verify user has access to project
    project = (
        db.query(Project)
        .filter(Project.id == project_id, Project.tenant_id == current_user.tenant_id)
        .first()
    )
    if not project:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found",
        )

    # Build query
    query = db.query(RiskAlert).filter(RiskAlert.project_id == project_id)

    if severity:
        try:
            severity_enum = RiskSeverity(severity.lower())
            query = query.filter(RiskAlert.severity == severity_enum)
        except ValueError:
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail=f"Invalid severity: {severity}",
            )

    if risk_status:
        try:
            status_enum = RiskStatus(risk_status.lower())
            query = query.filter(RiskAlert.status == status_enum)
        except ValueError:
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail=f"Invalid status: {risk_status}",
            )

    alerts = query.order_by(RiskAlert.created_at.desc()).all()

    return [RiskAlertResponse.model_validate(a) for a in alerts]


@router.put("/risks/{risk_id}", response_model=RiskAlertResponse)
async def update_risk_status(
    risk_id: UUID,
    update_data: RiskAlertUpdate,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> RiskAlertResponse:
    """Update risk alert status (review, resolve, dismiss)."""
    # Get risk alert and verify it belongs to user's tenant
    risk = db.query(RiskAlert).filter(RiskAlert.id == risk_id).first()
    if not risk:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Risk alert not found",
        )

    project = risk.project
    if project.tenant_id != current_user.tenant_id:  # type: ignore[comparison-overlap]
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Not enough permissions to update this risk alert",
        )

    # Update fields
    if update_data.status is not None:
        risk.status = update_data.status
        if update_data.status == RiskStatus.REVIEWED:
            from datetime import datetime

            risk.reviewed_by_id = current_user.id
            risk.reviewed_at = datetime.utcnow()

    if update_data.description is not None:
        risk.description = update_data.description

    if update_data.recommendation is not None:
        risk.recommendation = update_data.recommendation

    db.commit()
    db.refresh(risk)

    logger.info(
        "Risk alert updated id=%s status=%s tenant_id=%s",
        risk_id,
        risk.status,
        current_user.tenant_id,
    )

    return RiskAlertResponse.model_validate(risk)


@router.post("/projects/{project_id}/insights", response_model=dict)
async def generate_project_insights(
    project_id: UUID,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> dict:
    """
    Use Claude to analyze all documents and generate:
    - Key findings (3-5 bullet points)
    - Opportunities identified
    - Threats/risks
    """
    # Verify user has access to project
    project = (
        db.query(Project)
        .filter(Project.id == project_id, Project.tenant_id == current_user.tenant_id)
        .first()
    )
    if not project:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found",
        )

    # Get all documents
    docs = document_crud.list_documents_for_project(db, project_id=project.id)
    if not docs:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail="No documents available for this project",
        )

    # Get API key
    api_key = os.getenv("ANTHROPIC_API_KEY", "") or getattr(
        settings, "anthropic_api_key", ""
    )
    if not api_key:
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail="Claude API key not configured",
        )

    # Use ClaudeService to analyze documents
    service = ClaudeService(api_key=api_key)
    prompt = """Analyze all the provided project documents and generate a comprehensive strategic insights report.

Please provide:
1. Key Findings: 3-5 bullet points summarizing the most important information
2. Opportunities: List any business opportunities identified
3. Threats/Risks: List any potential threats or risks identified

Format your response as JSON:
{
    "key_findings": ["finding 1", "finding 2", ...],
    "opportunities": ["opportunity 1", "opportunity 2", ...],
    "threats": ["threat 1", "threat 2", ...]
}"""

    try:
        result = await service.query_with_context(prompt, docs)
        answer = result["answer"]

        # Try to parse JSON from response
        import json
        import re

        json_match = re.search(r"\{[^{}]*\}", answer, re.DOTALL)
        if json_match:
            insights = json.loads(json_match.group(0))
        else:
            # Fallback: return as text
            insights = {
                "key_findings": [],
                "opportunities": [],
                "threats": [],
                "raw_analysis": answer,
            }

        logger.info(
            "Project insights generated project_id=%s tenant_id=%s",
            project_id,
            current_user.tenant_id,
        )

        return {
            "project_id": project_id,
            "insights": insights,
        }
    except Exception as e:
        logger.error("Error generating insights: %s", e)
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Failed to generate insights: {str(e)}",
        )


async def scan_document_risks_background(document_id: UUID, project_owner_id: int):
    """Background task to scan document and notify user."""
    db = SessionLocal()
    try:
        api_key = os.getenv("ANTHROPIC_API_KEY", "") or getattr(
            settings, "anthropic_api_key", ""
        )
        if not api_key:
            logger.warning("Claude API key not configured, skipping risk scan")
            return

        scanner = RiskScannerService(api_key=api_key)
        risks = await scanner.scan_document(db, document_id)

        if risks:
            notif_service = NotificationService()
            for risk in risks:
                await notif_service.notify_risk_detected(db, risk, project_owner_id)

        logger.info(
            "Background risk scan completed document_id=%s risks=%d",
            document_id,
            len(risks),
        )
    except Exception as e:
        logger.error("Error in background risk scan: %s", e)
    finally:
        db.close()


