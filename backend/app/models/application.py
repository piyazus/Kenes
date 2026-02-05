"""Application ORM model."""

from datetime import datetime
from typing import List
from sqlalchemy import String, Integer, DateTime, func, ForeignKey
from sqlalchemy.orm import Mapped, mapped_column, relationship
from app.db.base import Base
# Import for type checking mostly, but needed for relationship if not string
from app.models.customer import Customer
from app.models.service import Service

class Application(Base):
    __tablename__ = "applications"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    customer_id: Mapped[int] = mapped_column(Integer, ForeignKey("customers.id", ondelete="CASCADE"), nullable=False)
    service_id: Mapped[int] = mapped_column(Integer, ForeignKey("services.id", ondelete="CASCADE"), nullable=False)
    
    status: Mapped[str] = mapped_column(String(50), default="pending", nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        DateTime(timezone=True), server_default=func.now(), nullable=False
    )

    # Relationships
    customer: Mapped["Customer"] = relationship("Customer", back_populates="applications")
    service: Mapped["Service"] = relationship("Service", back_populates="applications")
    documents: Mapped[List["ApplicationDocument"]] = relationship("ApplicationDocument", back_populates="application", cascade="all, delete-orphan")
    proposals: Mapped[List["Proposal"]] = relationship("Proposal", back_populates="application", cascade="all, delete-orphan")
