"""Pydantic schemas for Variable entity."""

from __future__ import annotations

from datetime import datetime
from typing import Optional
from uuid import UUID

from pydantic import BaseModel, ConfigDict, Field

from app.models.variable import ValueType, VariableCategory


class VariableBase(BaseModel):
    """Shared variable fields."""

    key: str = Field(..., min_length=1, max_length=255)
    label: str = Field(..., min_length=1, max_length=255)
    value_type: ValueType
    category: VariableCategory
    description: Optional[str] = None
    unit: Optional[str] = None
    display_order: int = 0


class VariableCreate(VariableBase):
    """Payload for creating a variable."""

    project_id: UUID
    raw_value: Optional[str] = None
    formula: Optional[str] = None
    validation_rules: Optional[dict] = None


class VariableUpdate(BaseModel):
    """Payload for updating a variable."""

    key: Optional[str] = Field(None, min_length=1, max_length=255)
    label: Optional[str] = Field(None, min_length=1, max_length=255)
    raw_value: Optional[str] = None
    formula: Optional[str] = None
    description: Optional[str] = None
    display_order: Optional[int] = None
    validation_rules: Optional[dict] = None


class VariableResponse(VariableBase):
    """Representation of a variable returned by the API."""

    model_config = ConfigDict(from_attributes=True)

    id: UUID
    project_id: UUID
    raw_value: Optional[str]
    calculated_value: Optional[str] = None
    formula: Optional[str] = None
    depends_on: list[UUID] = []
    validation_rules: Optional[dict] = None
    created_at: datetime


