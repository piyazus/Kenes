"""
Client model.

Represents a client/customer in the system.
Clients belong to a tenant.
"""

from datetime import datetime

from sqlalchemy import Column, Integer, String, DateTime, ForeignKey, Boolean
from sqlalchemy.orm import relationship

from app.db.base import Base


class Client(Base):
    """
    Client model for customer management.
    
    Each client belongs to a tenant and is managed by users of that tenant.
    
    Attributes:
        id: Unique client identifier
        name: Client name
        email: Client email
        phone: Client phone number
        company_name: Client company name (optional)
        address: Client address (optional)
        is_active: Whether client is active
        tenant_id: Foreign key to tenant
        created_at: Creation timestamp
        updated_at: Last update timestamp
    """
    
    __tablename__ = "clients"
    
    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(255), nullable=False)
    email = Column(String(255), index=True, nullable=False)
    phone = Column(String(20), nullable=True)
    company_name = Column(String(255), nullable=True)
    address = Column(String(500), nullable=True)
    is_active = Column(Boolean, default=True, nullable=False)
    
    # Foreign key to tenant
    tenant_id = Column(Integer, ForeignKey("tenants.id"), nullable=False, index=True)
    
    # Timestamps
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)
    
    # Relationships
    tenant = relationship("Tenant", back_populates="clients")
    
    def __repr__(self) -> str:
        return f"<Client(id={self.id}, name={self.name}, email={self.email}, tenant_id={self.tenant_id})>"
