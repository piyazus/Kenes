"""CRUD operations for Variable."""

from __future__ import annotations

from typing import Dict, List, Optional
from uuid import UUID

from sqlalchemy.orm import Session

from app.models.variable import Variable, VariableType
from app.schemas.variable import VariableCreate, VariableUpdate


def create_variable(db: Session, *, obj_in: VariableCreate) -> Variable:
    """Create a new variable."""
    db_obj = Variable(
        project_id=obj_in.project_id,
        name=obj_in.name,
        display_name=obj_in.display_name,
        type=obj_in.type,
        value=obj_in.value,
        unit=obj_in.unit,
        description=obj_in.description,
        order=obj_in.order,
    )
    db.add(db_obj)
    db.commit()
    db.refresh(db_obj)
    return db_obj


def get_variable(db: Session, *, variable_id: UUID) -> Optional[Variable]:
    """Get a variable by its ID."""
    return db.query(Variable).filter(Variable.id == variable_id).first()


def list_variables_for_project(
    db: Session,
    *,
    project_id: UUID,
) -> List[Variable]:
    """List all variables for a given project."""
    return (
        db.query(Variable)
        .filter(Variable.project_id == project_id)
        .order_by(Variable.order.asc(), Variable.created_at.asc())
        .all()
    )


def update_variable(
    db: Session,
    *,
    db_obj: Variable,
    obj_in: VariableUpdate,
) -> Variable:
    """Update a variable with provided fields."""
    update_data = obj_in.model_dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(db_obj, field, value)
    db.add(db_obj)
    db.commit()
    db.refresh(db_obj)
    return db_obj


def delete_variable(db: Session, *, db_obj: Variable) -> None:
    """Delete a variable."""
    db.delete(db_obj)
    db.commit()


def build_context_from_variables(variables: List[Variable]) -> Dict[str, float]:
    """
    Build a numeric context from number variables that can be used in formula evaluation.
    Only variables of type NUMBER are included.
    """
    context: Dict[str, float] = {}
    for var in variables:
        if var.type == VariableType.NUMBER:
            try:
                context[var.name] = float(var.value)
            except (TypeError, ValueError):
                continue
    return context




