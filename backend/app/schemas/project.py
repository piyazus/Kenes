"""Pydantic schemas for Project entity."""

from __future__ import annotations

from datetime import datetime
from typing import Optional
from uuid import UUID

from pydantic import BaseModel, Field

from app.models.project import ProjectStatus


class ProjectBase(BaseModel):
    """Shared project fields."""

    name: str = Field(..., min_length=1, max_length=255)
    description: Optional[str] = None
    status: ProjectStatus = ProjectStatus.DRAFT


class ProjectCreate(ProjectBase):
    """Payload for creating a project."""

    tenant_id: int = Field(..., ge=1)
    client_id: int = Field(..., ge=1)


class ProjectUpdate(BaseModel):
    """Payload for updating a project."""

    name: Optional[str] = Field(None, min_length=1, max_length=255)
    description: Optional[str] = None
    status: Optional[ProjectStatus] = None


class ProjectResponse(ProjectBase):
    """Representation of a project returned by the API."""

    id: UUID
    tenant_id: int
    client_id: int
    created_at: datetime

    class Config:
        from_attributes = True


