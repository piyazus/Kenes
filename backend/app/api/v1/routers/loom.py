"""Loom router: variables and formula calculations."""

from __future__ import annotations

import logging
from typing import Dict, List
from uuid import UUID

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session

from app.core.deps import TestUser, get_current_project, get_current_user, get_db
from app.crud import variable as variable_crud
from app.models.variable import Variable
from app.schemas.variable import (
    VariableCreate,
    VariableRead,
    VariableUpdate,
    VariableCalculated,
)
from app.services.formula_calculator import evaluate_formula


logger = logging.getLogger(__name__)

router = APIRouter(prefix="/loom", tags=["loom"])


@router.get("/variables/{project_id}", response_model=List[VariableRead])
def list_variables(
    project=Depends(get_current_project),
    db: Session = Depends(get_db),
) -> List[VariableRead]:
    """List all variables for a project."""
    vars_ = variable_crud.list_variables_for_project(db, project_id=project.id)
    return vars_


@router.post(
    "/variables",
    response_model=VariableRead,
    status_code=status.HTTP_201_CREATED,
)
def create_variable(
    payload: VariableCreate,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> VariableRead:
    """Create a variable for a project, ensuring tenant isolation."""
    project = get_current_project(
        project_id=payload.project_id,
        db=db,
        current_user=current_user,
    )
    db_var = variable_crud.create_variable(db, obj_in=payload)
    logger.info(
        "Variable created id=%s project_id=%s tenant_id=%s",
        db_var.id,
        project.id,
        current_user.tenant_id,
    )
    return db_var


@router.put("/variables/{variable_id}", response_model=VariableRead)
def update_variable(
    variable_id: UUID,
    payload: VariableUpdate,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> VariableRead:
    """Update a variable if it belongs to a project of the current tenant."""
    db_var = variable_crud.get_variable(db, variable_id=variable_id)
    if not db_var:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Variable not found",
        )
    project = db_var.project  # type: ignore[assignment]
    if project.tenant_id != current_user.tenant_id:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Not enough permissions to modify this variable",
        )
    updated = variable_crud.update_variable(db, db_obj=db_var, obj_in=payload)
    logger.info(
        "Variable updated id=%s project_id=%s tenant_id=%s",
        variable_id,
        project.id,
        current_user.tenant_id,
    )
    return updated


@router.delete(
    "/variables/{variable_id}",
    status_code=status.HTTP_204_NO_CONTENT,
)
def delete_variable(
    variable_id: UUID,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> None:
    """Delete a variable if it belongs to a project of the current tenant."""
    db_var = variable_crud.get_variable(db, variable_id=variable_id)
    if not db_var:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Variable not found",
        )
    project = db_var.project  # type: ignore[assignment]
    if project.tenant_id != current_user.tenant_id:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Not enough permissions to delete this variable",
        )
    variable_crud.delete_variable(db, db_obj=db_var)
    logger.info(
        "Variable deleted id=%s project_id=%s tenant_id=%s",
        variable_id,
        project.id,
        current_user.tenant_id,
    )


@router.post("/calculate/{project_id}", response_model=Dict[UUID, float])
def calculate_project_variables(
    project=Depends(get_current_project),
    db: Session = Depends(get_db),
) -> Dict[UUID, float]:
    """
    Recalculate all formula variables for a project.

    Returns mapping {variable_id: calculated_value}.
    """
    vars_ = variable_crud.list_variables_for_project(db, project_id=project.id)
    context = variable_crud.build_context_from_variables(vars_)

    results: Dict[UUID, float] = {}
    for v in vars_:
        if v.type.name.lower() == "formula":
            try:
                value = evaluate_formula(v.value, context)
                results[v.id] = value
            except Exception:
                continue

    logger.info(
        "Variables calculated project_id=%s tenant_id=%s count=%s",
        project.id,
        project.tenant_id,
        len(results),
    )

    return results




