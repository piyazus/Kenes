"""CRUD operations for Document."""

from __future__ import annotations

from typing import List, Optional
from uuid import UUID

from sqlalchemy.orm import Session

from app.models.document import Document
from app.schemas.document import DocumentCreate


def create_document(db: Session, *, obj_in: DocumentCreate, uploaded_by: Optional[int]) -> Document:
    """Create a new document record."""
    db_obj = Document(
        project_id=obj_in.project_id,
        filename=obj_in.filename,
        file_path=obj_in.file_path,
        file_size=obj_in.file_size,
        mime_type=obj_in.mime_type,
        uploaded_by=uploaded_by,
    )
    db.add(db_obj)
    db.commit()
    db.refresh(db_obj)
    return db_obj


def get_document(db: Session, *, document_id: UUID) -> Optional[Document]:
    """Get a document by its ID."""
    return db.query(Document).filter(Document.id == document_id).first()


def list_documents_for_project(
    db: Session,
    *,
    project_id: UUID,
) -> List[Document]:
    """List all documents for a given project."""
    return (
        db.query(Document)
        .filter(Document.project_id == project_id)
        .order_by(Document.uploaded_at.desc())
        .all()
    )


def delete_document(db: Session, *, db_obj: Document) -> None:
    """Delete a document."""
    db.delete(db_obj)
    db.commit()




