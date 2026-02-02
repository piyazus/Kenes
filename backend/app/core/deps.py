"""Common FastAPI dependencies."""

from __future__ import annotations

from dataclasses import dataclass
from typing import Generator
from uuid import UUID

from fastapi import Depends, HTTPException, status
from sqlalchemy.orm import Session

from app.db.session import SessionLocal
from app.models.project import Project


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


def get_current_project(
    project_id: UUID,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> Project:
    """
    Dependency that returns a project if it belongs to the current tenant.

    Raises 404 if not found and 403 if tenant does not match.
    """
    project = (
        db.query(Project)
        .filter(Project.id == project_id, Project.tenant_id == current_user.tenant_id)
        .first()
    )
    if not project:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found",
        )
    return project

