"""Service ORM model."""

from datetime import datetime
from sqlalchemy import String, Integer, DateTime, func, Text, Float, ForeignKey
from sqlalchemy.orm import Mapped, mapped_column, relationship
from app.db.base import Base

class Service(Base):
    __tablename__ = "services"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    service_name: Mapped[str] = mapped_column(String(255), nullable=False)
    description: Mapped[str] = mapped_column(Text, nullable=True)
    interest_rate: Mapped[float] = mapped_column(Float, nullable=True)
    max_amount: Mapped[float] = mapped_column(Float, nullable=True)
    
    last_updated_by: Mapped[int | None] = mapped_column(Integer, ForeignKey("consultants.id"), nullable=True)
    updated_at: Mapped[datetime] = mapped_column(
        DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False
    )

    # Relationships
    applications: Mapped[list["Application"]] = relationship("Application", back_populates="service")
