"""Notification Pydantic schemas."""

from __future__ import annotations

from datetime import datetime
from uuid import UUID

from pydantic import BaseModel, ConfigDict, Field

from app.models.notification import NotificationType, RelatedEntityType


class NotificationBase(BaseModel):
    """Base schema for notifications."""

    notification_type: NotificationType
    title: str = Field(..., max_length=255)
    message: str | None = None


class NotificationCreate(NotificationBase):
    """Schema for creating a notification."""

    user_id: int
    related_entity_type: RelatedEntityType | None = None
    related_entity_id: UUID | None = None


class NotificationUpdate(BaseModel):
    """Schema for updating a notification."""

    is_read: bool | None = None
    read_at: datetime | None = None


class NotificationResponse(NotificationBase):
    """Schema for notification response."""

    model_config = ConfigDict(from_attributes=True)

    id: UUID
    user_id: int
    related_entity_type: RelatedEntityType | None = None
    related_entity_id: UUID | None = None
    is_read: bool
    read_at: datetime | None = None
    created_at: datetime

