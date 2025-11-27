"""
User model.

Represents a user account in the system.
Each user belongs to a tenant (multi-tenancy support).
"""

from datetime import datetime

from sqlalchemy import Column, Integer, String, Boolean, DateTime, ForeignKey
from sqlalchemy.orm import relationship

from app.db.base import Base


class User(Base):
    """
    User model for authentication and account management.
    
    Attributes:
        id: Unique user identifier
        email: User email (unique)
        username: Username (unique)
        password_hash: Hashed password (never store plain text!)
        full_name: User's full name
        is_active: Whether user account is active
        tenant_id: Foreign key to tenant (multi-tenancy)
        created_at: Creation timestamp
        updated_at: Last update timestamp
    """
    
    __tablename__ = "users"
    
    id = Column(Integer, primary_key=True, index=True)
    email = Column(String(255), unique=True, index=True, nullable=False)
    username = Column(String(255), unique=True, index=True, nullable=False)
    password_hash = Column(String(255), nullable=False)
    full_name = Column(String(255), nullable=True)
    is_active = Column(Boolean, default=True, nullable=False)
    
    # Foreign key to tenant
    tenant_id = Column(Integer, ForeignKey("tenants.id"), nullable=True)
    
    # Timestamps
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)
    
    # Relationships
    tenant = relationship("Tenant", back_populates="users")
    
    def __repr__(self) -> str:
        return f"<User(id={self.id}, email={self.email}, username={self.username})>"
