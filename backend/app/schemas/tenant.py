"""Pydantic schemas for Tenant entity."""

from __future__ import annotations

from datetime import datetime
from typing import Optional

from pydantic import BaseModel, Field


class TenantBase(BaseModel):
    """Shared tenant fields."""

    name: str = Field(..., min_length=1, max_length=255)


class TenantCreate(TenantBase):
    """Payload for creating a tenant."""

    pass


class TenantUpdate(BaseModel):
    """Payload for updating a tenant."""

    name: Optional[str] = Field(None, min_length=1, max_length=255)


class TenantRead(TenantBase):
    """Representation of a tenant returned by the API."""

    id: int
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True
