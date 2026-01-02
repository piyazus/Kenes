"""Projects router."""

from __future__ import annotations

import logging
from typing import List
from uuid import UUID

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session

from app.core.deps import TestUser, get_current_project, get_current_user, get_db
from app.crud import project as project_crud
from app.schemas.project import ProjectCreate, ProjectResponse, ProjectUpdate


logger = logging.getLogger(__name__)

router = APIRouter(prefix="/projects", tags=["projects"])


@router.post(
    "/",
    response_model=ProjectResponse,
    status_code=status.HTTP_201_CREATED,
)
def create_project(
    payload: ProjectCreate,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> ProjectResponse:
    """Create a new project for the current tenant."""
    if payload.tenant_id != current_user.tenant_id:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Cannot create project for a different tenant",
        )
    db_obj = project_crud.create_project(
        db,
        obj_in=payload,
        tenant_id=current_user.tenant_id,
        created_by=current_user.id,
    )
    logger.info("Project created id=%s tenant_id=%s", db_obj.id, current_user.tenant_id)
    return db_obj


@router.get("/", response_model=List[ProjectResponse])
def list_projects(
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> List[ProjectResponse]:
    """List projects for the current tenant."""
    projects = project_crud.list_projects_for_tenant(
        db,
        tenant_id=current_user.tenant_id,
    )
    return projects


@router.get("/{project_id}", response_model=ProjectResponse)
def get_project(
    project: ProjectResponse = Depends(get_current_project),
) -> ProjectResponse:
    """Get project details (tenant-isolated)."""
    return project


@router.put("/{project_id}", response_model=ProjectResponse)
def update_project(
    project_id: UUID,
    payload: ProjectUpdate,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> ProjectResponse:
    """Update a project if it belongs to the current tenant."""
    db_obj = project_crud.get_project_for_tenant(
        db,
        project_id=project_id,
        tenant_id=current_user.tenant_id,
    )
    if not db_obj:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found",
        )
    updated = project_crud.update_project(db, db_obj=db_obj, obj_in=payload)
    logger.info("Project updated id=%s tenant_id=%s", project_id, current_user.tenant_id)
    return updated


@router.delete(
    "/{project_id}",
    status_code=status.HTTP_204_NO_CONTENT,
)
def delete_project(
    project_id: UUID,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> None:
    """Delete a project if it belongs to the current tenant."""
    db_obj = project_crud.get_project_for_tenant(
        db,
        project_id=project_id,
        tenant_id=current_user.tenant_id,
    )
    if not db_obj:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found",
        )
    project_crud.delete_project(db, db_obj=db_obj)
    logger.info("Project deleted id=%s tenant_id=%s", project_id, current_user.tenant_id)




