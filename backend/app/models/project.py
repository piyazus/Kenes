"""Project ORM model."""

from __future__ import annotations

from datetime import datetime
import uuid
from enum import Enum

from sqlalchemy import (
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


class ProjectStatus(str, Enum):
    """Possible statuses for a strategic project."""

    DRAFT = "draft"
    ACTIVE = "active"
    ARCHIVED = "archived"


class Project(Base):
    """Represents a strategic project for a client."""

    __tablename__ = "projects"

    id: Mapped[uuid.UUID] = mapped_column(
        PGUUID(as_uuid=True),
        primary_key=True,
        default=uuid.uuid4,
    )
    tenant_id: Mapped[int] = mapped_column(
        Integer,
        ForeignKey("tenants.id", ondelete="CASCADE"),
        nullable=False,
        index=True,
    )
    client_id: Mapped[int] = mapped_column(
        Integer,
        ForeignKey("clients.id", ondelete="CASCADE"),
        nullable=False,
        index=True,
    )
    name: Mapped[str] = mapped_column(String(255), nullable=False)
    description: Mapped[str | None] = mapped_column(Text, nullable=True)
    status: Mapped[ProjectStatus] = mapped_column(
        SQLEnum(ProjectStatus, name="project_status"),
        nullable=False,
        default=ProjectStatus.DRAFT,
        server_default=ProjectStatus.DRAFT.value,
    )
    created_at: Mapped[datetime] = mapped_column(
        DateTime(timezone=True),
        server_default=func.now(),
        nullable=False,
    )
    # Relationships
    tenant: Mapped["Tenant"] = relationship("Tenant")
    client: Mapped["Client"] = relationship("Client")
    documents: Mapped[list["Document"]] = relationship(
        "Document",
        cascade="all, delete-orphan",
    )
    variables: Mapped[list["Variable"]] = relationship(
        "Variable",
        cascade="all, delete-orphan",
    )
    podium_accesses: Mapped[list["PodiumAccess"]] = relationship(
        "PodiumAccess",
        cascade="all, delete-orphan",
    )
    chat_messages: Mapped[list["ChatMessage"]] = relationship(
        "ChatMessage",
        cascade="all, delete-orphan",
    )


