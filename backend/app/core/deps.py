"""Common FastAPI dependencies."""

from __future__ import annotations

from dataclasses import dataclass
from typing import Generator

from sqlalchemy.orm import Session

from app.db.session import SessionLocal


@dataclass
class TestUser:
    """Simple placeholder for authenticated user."""

    id: int = 1
    tenant_id: int = 1
    email: str = "test.user@kenescloud.local"


def get_db() -> Generator[Session, None, None]:
    """Provide a transactional database session."""
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


def get_current_user() -> TestUser:
    """Temporary stub that returns a static user."""
    return TestUser()

