"""Application Document ORM model."""

from datetime import datetime
from sqlalchemy import String, Integer, DateTime, func, ForeignKey
from sqlalchemy.orm import Mapped, mapped_column, relationship
from app.db.base import Base

class ApplicationDocument(Base):
    __tablename__ = "application_documents"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    application_id: Mapped[int] = mapped_column(Integer, ForeignKey("applications.id", ondelete="CASCADE"), nullable=False)
    
    document_path: Mapped[str] = mapped_column(String(1024), nullable=False)
    status: Mapped[str] = mapped_column(String(50), default="processed", nullable=False)
    submission_date: Mapped[datetime] = mapped_column(
        DateTime(timezone=True), server_default=func.now(), nullable=False
    )

    # Relationships
    application: Mapped["Application"] = relationship("Application", back_populates="documents")
