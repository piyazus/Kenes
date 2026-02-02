"""Loom router: variables and formula calculations."""

from __future__ import annotations

import logging
from typing import Any, Dict, List
from uuid import UUID

from fastapi import APIRouter, Depends, HTTPException, status
from pydantic import BaseModel
from sqlalchemy.orm import Session

from app.core.deps import TestUser, get_current_project, get_current_user, get_db
from app.crud import variable as variable_crud
from app.models.project import Project
from app.models.variable import Variable, VariableCategory, ValueType
from app.schemas.variable import (
    VariableCreate,
    VariableResponse,
    VariableUpdate,
)
from app.services.dependency_resolver import DependencyResolver
from app.services.formula_parser import FormulaParser
from app.services.loom_engine import LoomEngine
from app.services.template_service import TemplateService
from app.models.model_template import ModelTemplate
from app.models.model_version import ModelVersion

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/loom", tags=["loom"])


@router.get(
    "/projects/{project_id}/variables",
    response_model=Dict[str, List[VariableResponse]],
)
async def get_project_variables(
    project_id: UUID,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> Dict[str, List[VariableResponse]]:
    """
    Get all variables grouped by category.

    Returns: {
        "assumptions": [...],
        "inputs": [...],
        "calculations": [...],
        "outputs": [...]
    }
    """
    # Verify project access
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

    variables = variable_crud.list_variables_for_project(db, project_id=project_id)

    # Group by category
    grouped: Dict[str, List[VariableResponse]] = {
        "assumptions": [],
        "inputs": [],
        "calculations": [],
        "outputs": [],
    }

    for var in variables:
        category_key = var.category.value
        if category_key in grouped:
            grouped[category_key].append(VariableResponse.model_validate(var))

    return grouped


@router.post(
    "/projects/{project_id}/variables",
    response_model=VariableResponse,
    status_code=status.HTTP_201_CREATED,
)
async def create_variable(
    project_id: UUID,
    variable: VariableCreate,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> VariableResponse:
    """Create a new variable"""
    # Verify project access
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

    # If formula variable, validate formula
    depends_on: List[UUID] = []
    if variable.formula:
        engine = LoomEngine(db)
        validation = await engine.validate_formula(variable.formula, project_id)

        if not validation["valid"]:
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail=validation.get("error", "Invalid formula"),
            )

        # Extract dependencies from formula
        parser = FormulaParser()
        parse_result = parser.parse_formula(variable.formula)
        if parse_result["valid"]:
            # Find variable IDs for dependencies
            for var_ref in parse_result["variables"]:
                dep_var = (
                    db.query(Variable)
                    .filter(
                        Variable.project_id == project_id,
                        (Variable.key == var_ref) | (Variable.label == var_ref),
                    )
                    .first()
                )
                if dep_var:
                    depends_on.append(dep_var.id)

    # Create variable
    db_var = Variable(
        project_id=project_id,
        key=variable.key,
        label=variable.label,
        value_type=variable.value_type,
        category=variable.category,
        raw_value=variable.raw_value or "",
        formula=variable.formula,
        depends_on=depends_on if depends_on else None,
        display_order=variable.display_order,
        description=variable.description,
        unit=variable.unit,
        validation_rules=variable.validation_rules,
    )

    db.add(db_var)
    db.commit()
    db.refresh(db_var)

    # If formula variable, calculate initial value
    if variable.formula:
        try:
            engine = LoomEngine(db)
            result = await engine._calculate_variable(db_var)
            db_var.calculated_value = str(result)
            db.commit()
            db.refresh(db_var)
        except Exception as e:
            logger.warning("Could not calculate initial formula value: %s", e)

    logger.info(
        "Variable created id=%s project_id=%s tenant_id=%s",
        db_var.id,
        project_id,
        current_user.tenant_id,
    )

    return VariableResponse.model_validate(db_var)


@router.put("/variables/{variable_id}", response_model=Dict[str, Any])
async def update_variable(
    variable_id: UUID,
    update_data: VariableUpdate,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> Dict[str, Any]:
    """
    Update variable - triggers cascade recalculation.

    Returns: {
        "updated_variable": {...},
        "affected_variables": [...]
    }
    """
    variable = db.query(Variable).filter(Variable.id == variable_id).first()
    if not variable:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Variable not found",
        )

    # Verify project access
    project = variable.project
    if project.tenant_id != current_user.tenant_id:  # type: ignore[comparison-overlap]
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Not enough permissions to modify this variable",
        )

    engine = LoomEngine(db)

    # Handle value update (triggers cascade)
    if update_data.raw_value is not None:
        result = await engine.update_variable(variable_id, update_data.raw_value)
        return result

    # Handle other updates (name, description, etc.)
    if update_data.key is not None:
        variable.key = update_data.key
    if update_data.label is not None:
        variable.label = update_data.label
    if update_data.description is not None:
        variable.description = update_data.description
    if update_data.display_order is not None:
        variable.display_order = update_data.display_order
    if update_data.validation_rules is not None:
        variable.validation_rules = update_data.validation_rules

    db.commit()
    db.refresh(variable)

    return {
        "updated_variable": VariableResponse.model_validate(variable),
        "affected_variables": [],
    }


@router.delete("/variables/{variable_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_variable(
    variable_id: UUID,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> None:
    """Delete variable (checks for dependencies first)"""
    variable = db.query(Variable).filter(Variable.id == variable_id).first()
    if not variable:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Variable not found",
        )

    # Verify project access
    project = variable.project
    if project.tenant_id != current_user.tenant_id:  # type: ignore[comparison-overlap]
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Not enough permissions to delete this variable",
        )

    # Check if other variables depend on this one
    resolver = DependencyResolver(db)
    graph = resolver.build_dependency_graph(variable.project_id)
    affected = resolver.get_affected_variables(variable_id, graph)

    if affected:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"Cannot delete variable: {len(affected)} variables depend on it",
        )

    db.delete(variable)
    db.commit()

    logger.info(
        "Variable deleted id=%s project_id=%s tenant_id=%s",
        variable_id,
        project.id,
        current_user.tenant_id,
    )


class FormulaRequest(BaseModel):
    """Request body for formula operations."""

    formula: str
    depends_on: List[UUID] | None = None


@router.post("/variables/{variable_id}/formula", response_model=VariableResponse)
async def set_variable_formula(
    variable_id: UUID,
    formula_data: FormulaRequest,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> VariableResponse:
    """Set or update formula for a variable"""
    variable = db.query(Variable).filter(Variable.id == variable_id).first()
    if not variable:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Variable not found",
        )

    # Verify project access
    project = variable.project
    if project.tenant_id != current_user.tenant_id:  # type: ignore[comparison-overlap]
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Not enough permissions to modify this variable",
        )

    engine = LoomEngine(db)

    # Validate formula
    validation = await engine.validate_formula(
        formula_data.formula, variable.project_id
    )

    if not validation["valid"]:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=validation.get("error", "Invalid formula"),
        )

    # Update variable
    variable.formula = formula_data.formula
    variable.depends_on = formula_data.depends_on or []
    variable.value_type = variable.value_type  # Keep existing type or set to FORMULA

    # Calculate initial value
    try:
        result = await engine._calculate_variable(variable)
        variable.calculated_value = str(result)
    except Exception as e:
        logger.error("Error calculating formula: %s", e)
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"Formula calculation error: {str(e)}",
        )

    db.commit()
    db.refresh(variable)

    return VariableResponse.model_validate(variable)


class ValidateFormulaRequest(BaseModel):
    """Request body for formula validation."""

    formula: str
    project_id: UUID


@router.post("/formula/validate", response_model=Dict[str, Any])
async def validate_formula(
    validation_request: ValidateFormulaRequest,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> Dict[str, Any]:
    """Validate formula syntax and dependencies"""
    # Verify project access
    project = (
        db.query(Project)
        .filter(
            Project.id == validation_request.project_id,
            Project.tenant_id == current_user.tenant_id,
        )
        .first()
    )
    if not project:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Project not found",
        )

    engine = LoomEngine(db)
    return await engine.validate_formula(
        validation_request.formula, validation_request.project_id
    )


@router.post("/projects/{project_id}/recalculate", response_model=Dict[str, Any])
async def recalculate_project(
    project_id: UUID,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> Dict[str, Any]:
    """Force recalculation of all formulas in project"""
    # Verify project access
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

    engine = LoomEngine(db)
    result = await engine.calculate_all(project_id)

    logger.info(
        "Project recalculated project_id=%s tenant_id=%s variables=%d",
        project_id,
        current_user.tenant_id,
        result["variables_calculated"],
    )

    return result
