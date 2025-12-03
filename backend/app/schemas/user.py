"""Pydantic schemas for User entity."""

from __future__ import annotations

from datetime import datetime
from typing import Optional

from pydantic import BaseModel, EmailStr, Field


class UserBase(BaseModel):
    """Shared user fields."""

    email: EmailStr
    is_active: bool = True


class UserCreate(UserBase):
    """Payload for creating a user."""

    password: str = Field(..., min_length=8, max_length=255)
    tenant_id: int = Field(..., ge=1)


class UserUpdate(BaseModel):
    """Payload for updating a user."""

    email: Optional[EmailStr] = None
    is_active: Optional[bool] = None
    password: Optional[str] = Field(None, min_length=8, max_length=255)


class UserRead(UserBase):
    """Representation of a user returned by the API."""

    id: int
    tenant_id: int
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True


class UserLogin(BaseModel):
    """Schema for user login."""

    email: EmailStr
    password: str = Field(..., min_length=8)


class UserRegister(UserCreate):
    """Schema for user registration (alias)."""

    pass
