"""Declarative base for all ORM models."""

from sqlalchemy.orm import DeclarativeBase


class Base(DeclarativeBase):
    """Base class for SQLAlchemy models."""

    pass


# Import models so Alembic can discover them via Base.metadata
from app.models import (  # noqa: F401,E402
    User,
    Tenant,
    Client,
    Project,
    Document,
    Variable,
    PodiumAccess,
    ChatMessage,
)