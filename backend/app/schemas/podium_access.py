"""Pydantic schemas for PodiumAccess entity."""

from __future__ import annotations

from datetime import datetime
from typing import Optional
from uuid import UUID

from pydantic import BaseModel, Field


class PodiumAccessBase(BaseModel):
    """Shared podium access fields."""

    public_token: str = Field(..., min_length=8, max_length=255)
    expires_at: Optional[datetime] = None
    is_active: bool = True


class PodiumAccessCreate(PodiumAccessBase):
    """Payload for creating a podium access token."""

    project_id: UUID


class PodiumAccessRead(PodiumAccessBase):
    """Representation of a podium access token returned by the API."""

    id: UUID
    project_id: UUID
    last_accessed_at: Optional[datetime] = None
    created_at: datetime

    class Config:
        from_attributes = True


