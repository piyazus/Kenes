"""Pydantic schemas for Client entity."""

from __future__ import annotations

from datetime import datetime
from typing import Optional

from pydantic import BaseModel, EmailStr, Field


class ClientBase(BaseModel):
    """Shared client fields."""

    name: str = Field(..., min_length=1, max_length=255)
    email: Optional[EmailStr] = None
    phone: Optional[str] = Field(None, max_length=50)


class ClientCreate(ClientBase):
    """Payload for creating a client."""

    tenant_id: int = Field(..., ge=1)


class ClientUpdate(BaseModel):
    """Payload for updating a client."""

    name: Optional[str] = Field(None, min_length=1, max_length=255)
    email: Optional[EmailStr] = None
    phone: Optional[str] = Field(None, max_length=50)


class ClientRead(ClientBase):
    """Representation of a client returned by the API."""

    id: int
    tenant_id: int
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True
