"""Customer ORM model."""

from datetime import datetime
from sqlalchemy import String, Integer, DateTime, func, Text
from sqlalchemy.orm import Mapped, mapped_column, relationship
from app.db.base import Base

class Customer(Base):
    __tablename__ = "customers"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    email: Mapped[str] = mapped_column(String(255), unique=True, index=True, nullable=False)
    password_hash: Mapped[str] = mapped_column(String(255), nullable=False)
    full_name: Mapped[str] = mapped_column(String(255), nullable=False)
    iin_number: Mapped[str] = mapped_column(String(20), unique=True, nullable=False)
    phone_number: Mapped[str] = mapped_column(String(50), nullable=True)
    address: Mapped[str] = mapped_column(Text, nullable=True)
    created_at: Mapped[datetime] = mapped_column(
        DateTime(timezone=True), server_default=func.now(), nullable=False
    )

    # Relationships
    applications: Mapped[list["Application"]] = relationship("Application", back_populates="customer")
