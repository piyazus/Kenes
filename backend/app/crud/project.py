"""CRUD operations for Project."""

from __future__ import annotations

from typing import List, Optional
from uuid import UUID

from sqlalchemy.orm import Session

from app.models.project import Project
from app.schemas.project import ProjectCreate, ProjectUpdate


def create_project(
    db: Session,
    *,
    obj_in: ProjectCreate,
    tenant_id: int,
    created_by: Optional[int],
) -> Project:
    """Create a new project for a given tenant."""
    db_obj = Project(
        tenant_id=tenant_id,
        client_id=obj_in.client_id,
        name=obj_in.name,
        description=obj_in.description,
        status=obj_in.status,
        created_by=created_by,
    )
    db.add(db_obj)
    db.commit()
    db.refresh(db_obj)
    return db_obj


def get_project(db: Session, *, project_id: UUID) -> Optional[Project]:
    """Get project by ID."""
    return db.query(Project).filter(Project.id == project_id).first()


def get_project_for_tenant(
    db: Session,
    *,
    project_id: UUID,
    tenant_id: int,
) -> Optional[Project]:
    """Get project by ID ensuring it belongs to the given tenant."""
    return (
        db.query(Project)
        .filter(Project.id == project_id, Project.tenant_id == tenant_id)
        .first()
    )


def list_projects_for_tenant(
    db: Session,
    *,
    tenant_id: int,
    skip: int = 0,
    limit: int = 100,
) -> List[Project]:
    """List projects for a given tenant."""
    return (
        db.query(Project)
        .filter(Project.tenant_id == tenant_id)
        .order_by(Project.created_at.desc())
        .offset(skip)
        .limit(limit)
        .all()
    )


def update_project(
    db: Session,
    *,
    db_obj: Project,
    obj_in: ProjectUpdate,
) -> Project:
    """Update a project with provided fields."""
    update_data = obj_in.model_dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(db_obj, field, value)
    db.add(db_obj)
    db.commit()
    db.refresh(db_obj)
    return db_obj


def delete_project(db: Session, *, db_obj: Project) -> None:
    """Delete a project."""
    db.delete(db_obj)
    db.commit()




