"""Risk Alert Pydantic schemas."""

from __future__ import annotations

from datetime import datetime
from uuid import UUID

from pydantic import BaseModel, ConfigDict, Field

from app.models.risk_alert import AlertType, RiskSeverity, RiskStatus


class RiskAlertBase(BaseModel):
    """Base schema for risk alerts."""

    title: str = Field(..., max_length=255)
    description: str | None = None
    alert_type: AlertType
    severity: RiskSeverity
    source_text: str | None = None
    recommendation: str | None = None


class RiskAlertCreate(RiskAlertBase):
    """Schema for creating a risk alert."""

    project_id: UUID
    document_id: UUID | None = None


class RiskAlertUpdate(BaseModel):
    """Schema for updating a risk alert."""

    status: RiskStatus | None = None
    reviewed_at: datetime | None = None
    description: str | None = None
    recommendation: str | None = None


class RiskAlertResponse(RiskAlertBase):
    """Schema for risk alert response."""

    model_config = ConfigDict(from_attributes=True)

    id: UUID
    project_id: UUID
    document_id: UUID | None = None
    status: RiskStatus
    reviewed_by_id: int | None = None
    reviewed_at: datetime | None = None
    created_at: datetime

