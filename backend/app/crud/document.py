"""CRUD operations for Document."""

from __future__ import annotations

from typing import List, Optional
from uuid import UUID

from sqlalchemy.orm import Session

from app.models.document import Document
from app.schemas.document import DocumentCreate


def create_document(db: Session, *, obj_in: DocumentCreate, uploaded_by: Optional[int] = None) -> Document:
    """Create a new document record."""
    db_obj = Document(
        project_id=obj_in.project_id,
        file_name=obj_in.file_name,
        file_path=obj_in.file_path,
        file_type=obj_in.file_type,
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
        .order_by(Document.created_at.desc())
        .all()
    )


def delete_document(db: Session, *, db_obj: Document) -> None:
    """Delete a document."""
    db.delete(db_obj)
    db.commit()




