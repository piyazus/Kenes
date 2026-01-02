"""Variable ORM model."""

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
from sqlalchemy.dialects.postgresql import UUID as PGUUID, JSONB
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.db.base import Base


class ValueType(str, Enum):
    """Supported variable types."""

    NUMBER = "number"
    STRING = "string"
    BOOLEAN = "boolean"
    FORMULA = "formula"


class VariableCategory(str, Enum):
    """Categories for variables."""

    ASSUMPTION = "assumption"  # User input (growth rate, price)
    INPUT = "input"  # External data (market size)
    CALCULATION = "calculation"  # Formula-based
    OUTPUT = "output"  # Final result to display


class Variable(Base):
    """Represents a configurable variable within a project."""

    __tablename__ = "variables"

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
    key: Mapped[str] = mapped_column(String(255), nullable=False)
    label: Mapped[str] = mapped_column(String(255), nullable=False)
    value_type: Mapped[ValueType] = mapped_column(
        SQLEnum(ValueType, name="value_type"),
        nullable=False,
    )
    category: Mapped[VariableCategory] = mapped_column(
        SQLEnum(VariableCategory, name="variable_category"),
        nullable=False,
        default=VariableCategory.ASSUMPTION,
        server_default=VariableCategory.ASSUMPTION.value,
        index=True,
    )
    raw_value: Mapped[str] = mapped_column(Text, nullable=False)
    calculated_value: Mapped[str | None] = mapped_column(Text, nullable=True)
    formula: Mapped[str | None] = mapped_column(Text, nullable=True)
    depends_on: Mapped[list[uuid.UUID] | None] = mapped_column(JSONB, nullable=True)
    display_order: Mapped[int] = mapped_column(
        Integer, nullable=False, default=0, index=True
    )
    description: Mapped[str | None] = mapped_column(Text, nullable=True)
    unit: Mapped[str | None] = mapped_column(String(50), nullable=True)
    validation_rules: Mapped[dict | None] = mapped_column(JSONB, nullable=True)
    created_at: Mapped[datetime] = mapped_column(
        DateTime(timezone=True),
        server_default=func.now(),
        nullable=False,
    )

    project: Mapped["Project"] = relationship("Project", back_populates="variables")

