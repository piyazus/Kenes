"""Proposal ORM model."""

from datetime import datetime
from sqlalchemy import String, Integer, DateTime, func, ForeignKey, Float, Text
from sqlalchemy.orm import Mapped, mapped_column, relationship
from app.db.base import Base

class Proposal(Base):
    __tablename__ = "proposals"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    application_id: Mapped[int] = mapped_column(Integer, ForeignKey("applications.id", ondelete="CASCADE"), nullable=False)
    consultant_id: Mapped[int | None] = mapped_column(Integer, ForeignKey("consultants.id", ondelete="SET NULL"), nullable=True)
    
    # AI Analysis
    ai_score: Mapped[int] = mapped_column(Integer, nullable=True)
    ai_risk_level: Mapped[str] = mapped_column(String(50), nullable=True) # Low, Medium, High
    ai_recommended_amount: Mapped[float] = mapped_column(Float, nullable=True)
    ai_analysis_json: Mapped[str] = mapped_column(Text, nullable=True)
    
    # Decisions
    consultant_notes: Mapped[str] = mapped_column(Text, nullable=True)
    bank_decision_status: Mapped[str] = mapped_column(String(50), nullable=True) # Approved, Pending, Rejected
    bank_comment: Mapped[str] = mapped_column(Text, nullable=True)
    decision_date: Mapped[datetime | None] = mapped_column(DateTime(timezone=True), nullable=True)
    
    created_at: Mapped[datetime] = mapped_column(
        DateTime(timezone=True), server_default=func.now(), nullable=False
    )

    # Relationships
    application: Mapped["Application"] = relationship("Application", back_populates="proposals")
    consultant: Mapped["Consultant"] = relationship("Consultant", back_populates="proposals")
