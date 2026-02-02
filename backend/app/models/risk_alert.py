"""Risk Alert ORM model."""

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


class AlertType(str, Enum):
    """Types of risk alerts."""

    FINANCIAL_RISK = "financial_risk"
    COMPLIANCE = "compliance"
    MARKET_CHANGE = "market_change"
    OPERATIONAL = "operational"


class RiskSeverity(str, Enum):
    """Severity levels for risk alerts."""

    LOW = "low"
    MEDIUM = "medium"
    HIGH = "high"
    CRITICAL = "critical"


class RiskStatus(str, Enum):
    """Status of risk alert."""

    NEW = "new"
    REVIEWED = "reviewed"
    RESOLVED = "resolved"
    DISMISSED = "dismissed"


class RiskAlert(Base):
    """Represents a risk alert detected in documents."""

    __tablename__ = "risk_alerts"

    id: Mapped[uuid.UUID] = mapped_column(
        PGUUID(as_uuid=True),
        primary_key=True,
        default=uuid.uuid4,
    )
    project_id: Mapped[uuid.UUID] = mapped_column(
        PGUUID(as_uuid=True),
        ForeignKey("projects.id", ondelete="CASCADE"),
        nullable=False,
        index=True,
    )
    document_id: Mapped[uuid.UUID | None] = mapped_column(
        PGUUID(as_uuid=True),
        ForeignKey("documents.id", ondelete="SET NULL"),
        nullable=True,
        index=True,
    )
    alert_type: Mapped[AlertType] = mapped_column(
        SQLEnum(AlertType, name="alert_type"),
        nullable=False,
    )
    severity: Mapped[RiskSeverity] = mapped_column(
        SQLEnum(RiskSeverity, name="risk_severity"),
        nullable=False,
        index=True,
    )
    title: Mapped[str] = mapped_column(String(255), nullable=False)
    description: Mapped[str | None] = mapped_column(Text, nullable=True)
    source_text: Mapped[str | None] = mapped_column(Text, nullable=True)
    recommendation: Mapped[str | None] = mapped_column(Text, nullable=True)
    status: Mapped[RiskStatus] = mapped_column(
        SQLEnum(RiskStatus, name="risk_status"),
        nullable=False,
        default=RiskStatus.NEW,
        server_default=RiskStatus.NEW.value,
        index=True,
    )
    reviewed_by_id: Mapped[int | None] = mapped_column(
        Integer,
        ForeignKey("users.id", ondelete="SET NULL"),
        nullable=True,
    )
    reviewed_at: Mapped[datetime | None] = mapped_column(
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
    project: Mapped["Project"] = relationship("Project")
    document: Mapped["Document | None"] = relationship("Document")
    reviewed_by: Mapped["User | None"] = relationship("User")

