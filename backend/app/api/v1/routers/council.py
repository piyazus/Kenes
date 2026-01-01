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
from app.schemas.document import DocumentCreate, DocumentRead, DocumentUploadResponse
from app.services.claude_service import ClaudeService
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
    project: Any = Depends(get_current_project),
    file: UploadFile = File(...),
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
    background_tasks: BackgroundTasks | None = None,
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

    doc_in = DocumentCreate(
        project_id=project_id,
        filename=file.filename,
        file_path=str(target_path),
        file_size=len(content),
        mime_type=file.content_type or "application/octet-stream",
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
    if background_tasks is not None:
        background_tasks.add_task(process_document, SessionLocal, str(db_doc.id))

    return DocumentUploadResponse(
        id=db_doc.id,
        project_id=db_doc.project_id,
        filename=db_doc.filename,
        url=str(target_path),
    )


@router.get("/documents/{project_id}", response_model=List[DocumentRead])
def list_project_documents(
    project: Any = Depends(get_current_project),
    db: Session = Depends(get_db),
) -> List[DocumentRead]:
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


