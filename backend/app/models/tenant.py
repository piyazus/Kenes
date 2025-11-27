"""
Tenant model.

Represents a company/organization/firm (tenant) in the system.
Supports multi-tenancy architecture where each tenant is isolated.
"""

from datetime import datetime

from sqlalchemy import Column, Integer, String, DateTime
from sqlalchemy.orm import relationship

from app.db.base import Base


class Tenant(Base):
    """
    Tenant model for multi-tenancy support.
    
    Each tenant represents a company/organization that has their own
    users, clients, and data (completely isolated from other tenants).
    
    Attributes:
        id: Unique tenant identifier
        name: Tenant company name
        slug: URL-friendly identifier (lowercase, no spaces)
        description: Company description
        created_at: Creation timestamp
        updated_at: Last update timestamp
    """
    
    __tablename__ = "tenants"
    
    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(255), unique=True, nullable=False)
    slug = Column(String(255), unique=True, index=True, nullable=False)
    description = Column(String(1000), nullable=True)
    
    # Timestamps
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)
    
    # Relationships
    users = relationship("User", back_populates="tenant", cascade="all, delete-orphan")
    clients = relationship("Client", back_populates="tenant", cascade="all, delete-orphan")
    
    def __repr__(self) -> str:
        return f"<Tenant(id={self.id}, name={self.name}, slug={self.slug})>"
