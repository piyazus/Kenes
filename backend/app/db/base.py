"""Declarative base for all ORM models."""

from sqlalchemy.orm import DeclarativeBase


class Base(DeclarativeBase):
    """Base class for SQLAlchemy models."""

    pass


# Note: Models are imported in alembic/env.py to avoid circular imports
# Alembic will discover models through: from app import models