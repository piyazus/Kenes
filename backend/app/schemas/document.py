"""Pydantic schemas for Document entity."""

from __future__ import annotations

from datetime import datetime
from typing import Optional
from uuid import UUID

from pydantic import BaseModel, Field

from app.models.document import DocumentStatus


class DocumentBase(BaseModel):
    """Shared document fields."""

    file_name: str = Field(..., min_length=1, max_length=255)
    file_type: str = Field(..., min_length=1, max_length=255)


class DocumentCreate(DocumentBase):
    """Payload for creating a document record (after upload)."""

    project_id: UUID
    file_path: str = Field(..., min_length=1, max_length=1024)


class DocumentUpdate(BaseModel):
    status: Optional[DocumentStatus] = None
    extracted_text: Optional[str] = None
    meta_data: Optional[dict] = None


class DocumentResponse(DocumentBase):
    """Representation of a document returned by the API."""

    id: UUID
    project_id: UUID
    file_path: str
    status: DocumentStatus
    extracted_text: Optional[str] = None
    meta_data: Optional[dict] = None
    created_at: datetime

    class Config:
        from_attributes = True


class DocumentUploadResponse(BaseModel):
    """Response after a successful document upload."""

    id: UUID
    project_id: UUID
    file_name: str
    url: str


