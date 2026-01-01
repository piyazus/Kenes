"""Podium router: public access to project data."""

from __future__ import annotations

import logging
from datetime import datetime, timedelta, timezone
from typing import Any, Dict
from uuid import UUID

from fastapi import APIRouter, Depends, HTTPException, status
from pydantic import BaseModel
from sqlalchemy.orm import Session

from app.core.deps import TestUser, get_current_user, get_db
from app.crud import podium_access as podium_crud
from app.crud import variable as variable_crud
from app.models.project import Project
from app.schemas.podium_access import PodiumAccessCreate, PodiumAccessRead
from app.schemas.project import ProjectRead
from app.schemas.variable import VariableRead


logger = logging.getLogger(__name__)

router = APIRouter(prefix="/podium", tags=["podium"])


class CreatePodiumAccessRequest(BaseModel):
    project_id: UUID
    expires_in_days: int


@router.post(
    "/access",
    response_model=PodiumAccessRead,
    status_code=status.HTTP_201_CREATED,
)
def create_podium_access(
    payload: CreatePodiumAccessRequest,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> PodiumAccessRead:
    """Create a public access token for a project (authenticated)."""
    project = (
        db.query(Project)
        .filter(Project.id == payload.project_id, Project.tenant_id == current_user.tenant_id)
        .first()
    )
    if not project:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found",
        )

    expires_at = datetime.now(timezone.utc) + timedelta(days=payload.expires_in_days)
    access_in = PodiumAccessCreate(
        project_id=payload.project_id,
        access_token="placeholder",  # will be overridden in CRUD
        expires_at=expires_at,
        is_active=True,
    )
    access = podium_crud.create_podium_access(db, obj_in=access_in)

    logger.info(
        "Podium access created project_id=%s tenant_id=%s token=%s",
        project.id,
        current_user.tenant_id,
        access.access_token,
    )

    return access


@router.get("/{token}")
def get_podium_view(
    token: str,
    db: Session = Depends(get_db),
) -> Dict[str, Any]:
    """
    Public endpoint: get project data by token.

    Response: {project, variables, charts_data}
    """
    access = podium_crud.get_podium_access_by_token(db, token=token)
    if not access:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Invalid or expired token",
        )

    podium_crud.touch_podium_access(db, db_obj=access)

    project = db.query(Project).filter(Project.id == access.project_id).first()
    if not project:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found",
        )

    vars_ = variable_crud.list_variables_for_project(db, project_id=project.id)

    project_data = ProjectRead.model_validate(project)
    variables_data = [VariableRead.model_validate(v) for v in vars_]

    charts_data: Dict[str, Any] = {
        "variables_count": len(vars_),
    }

    logger.info(
        "Podium view accessed project_id=%s token=%s",
        project.id,
        token,
    )

    return {
        "project": project_data,
        "variables": variables_data,
        "charts_data": charts_data,
    }


