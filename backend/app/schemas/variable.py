"""Pydantic schemas for Variable entity."""

from __future__ import annotations

from datetime import datetime
from typing import Optional
from uuid import UUID

from pydantic import BaseModel, Field

from app.models.variable import ValueType


class VariableBase(BaseModel):
    """Shared variable fields."""

    key: str = Field(..., min_length=1, max_length=255)
    label: str = Field(..., min_length=1, max_length=255)
    value_type: ValueType
    raw_value: str = Field(..., min_length=1)
    calculated_value: Optional[str] = None
    formula: Optional[str] = None


class VariableCreate(VariableBase):
    """Payload for creating a variable."""

    project_id: UUID


class VariableUpdate(BaseModel):
    """Payload for updating a variable."""

    key: Optional[str] = Field(None, min_length=1, max_length=255)
    label: Optional[str] = Field(None, min_length=1, max_length=255)
    value_type: Optional[ValueType] = None
    raw_value: Optional[str] = Field(None, min_length=1)
    calculated_value: Optional[str] = None
    formula: Optional[str] = None


class VariableResponse(VariableBase):
    """Representation of a variable returned by the API."""

    id: UUID
    project_id: UUID
    created_at: datetime

    class Config:
        from_attributes = True


