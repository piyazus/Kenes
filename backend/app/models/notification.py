"""Notification ORM model."""

from __future__ import annotations

from datetime import datetime
import uuid
from enum import Enum

from sqlalchemy import (
    Boolean,
    DateTime,
    Enum as SQLEnum,
    ForeignKey,
    Integer,
    String,
    Text,
    func,
)
from sqlalchemy.dialects.postgresql import UUID as PGUUID
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.db.base import Base


class NotificationType(str, Enum):
    """Types of notifications."""

    RISK_ALERT = "risk_alert"
    CLIENT_QUESTION = "client_question"
    MODEL_UPDATE = "model_update"
    DOCUMENT_UPLOADED = "document_uploaded"


class RelatedEntityType(str, Enum):
    """Types of entities that can be related to notifications."""

    PROJECT = "project"
    DOCUMENT = "document"
    RISK_ALERT = "risk_alert"
    VARIABLE = "variable"


class Notification(Base):
    """Represents an in-app notification for consultants."""

    __tablename__ = "notifications"

    id: Mapped[uuid.UUID] = mapped_column(
        PGUUID(as_uuid=True),
        primary_key=True,
        default=uuid.uuid4,
    )
    user_id: Mapped[int] = mapped_column(
        Integer,
        ForeignKey("users.id", ondelete="CASCADE"),
        nullable=False,
        index=True,
    )
    notification_type: Mapped[NotificationType] = mapped_column(
        SQLEnum(NotificationType, name="notification_type"),
        nullable=False,
    )
    title: Mapped[str] = mapped_column(String(255), nullable=False)
    message: Mapped[str | None] = mapped_column(Text, nullable=True)
    related_entity_type: Mapped[RelatedEntityType | None] = mapped_column(
        SQLEnum(RelatedEntityType, name="related_entity_type"),
        nullable=True,
    )
    related_entity_id: Mapped[uuid.UUID | None] = mapped_column(
        PGUUID(as_uuid=True),
        nullable=True,
    )
    is_read: Mapped[bool] = mapped_column(
        Boolean,
        default=False,
        nullable=False,
        index=True,
    )
    read_at: Mapped[datetime | None] = mapped_column(
        DateTime(timezone=True),
        nullable=True,
    )
    created_at: Mapped[datetime] = mapped_column(
        DateTime(timezone=True),
        server_default=func.now(),
        nullable=False,
        index=True,
    )

    # Relationships
    user: Mapped["User"] = relationship("User")

