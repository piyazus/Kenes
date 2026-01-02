"""Loom calculation engine - handles variable updates and cascading recalculations."""

from __future__ import annotations

import logging
from typing import Any, Dict
from uuid import UUID

from sqlalchemy.orm import Session

from app.models.variable import Variable
from app.services.dependency_resolver import DependencyResolver
from app.services.formula_parser import FormulaParser

logger = logging.getLogger(__name__)


class LoomEngine:
    """
    Main calculation engine for The Loom.

    Handles variable updates and cascading recalculations.
    """

    def __init__(self, db: Session):
        self.db = db
        self.parser = FormulaParser()
        self.resolver = DependencyResolver(db)

    async def calculate_all(self, project_id: UUID) -> Dict[str, Any]:
        """
        Recalculate all formula variables in the project.

        Returns summary of calculations performed.
        """
        # Build dependency graph
        graph = self.resolver.build_dependency_graph(project_id)

        # Get calculation order
        try:
            calc_order = self.resolver.topological_sort(graph)
        except ValueError as e:
            logger.error("Circular dependency detected: %s", e)
            raise

        # Calculate each variable in order
        calculated = []
        for var_id in calc_order:
            var = self.db.query(Variable).filter(Variable.id == var_id).first()

            if var and var.value_type.value == "formula" and var.formula:
                try:
                    result = await self._calculate_variable(var)
                    calculated.append(
                        {
                            "variable_id": var.id,
                            "name": var.key,
                            "old_value": var.calculated_value,
                            "new_value": result,
                        }
                    )

                    var.calculated_value = str(result)
                except Exception as e:
                    logger.error(
                        "Error calculating variable %s: %s", var.key, e
                    )
                    continue

        self.db.commit()

        return {
            "variables_calculated": len(calculated),
            "details": calculated,
        }

    async def update_variable(
        self, variable_id: UUID, new_value: Any
    ) -> Dict[str, Any]:
        """
        Update a variable and recalculate all dependent variables.

        Returns summary of affected variables.
        """
        var = self.db.query(Variable).filter(Variable.id == variable_id).first()
        if not var:
            raise ValueError("Variable not found")

        old_value = var.raw_value
        var.raw_value = str(new_value)
        # Also update calculated_value for non-formula variables
        if var.value_type.value != "formula":
            var.calculated_value = str(new_value)

        # Get all affected variables
        graph = self.resolver.build_dependency_graph(var.project_id)
        affected_ids = self.resolver.get_affected_variables(variable_id, graph)

        if not affected_ids:
            self.db.commit()
            return {
                "updated_variable": {
                    "id": variable_id,
                    "name": var.key,
                    "new_value": new_value,
                },
                "affected_variables": [],
            }

        # Recalculate affected variables in dependency order
        try:
            calc_order = self.resolver.topological_sort(graph)
        except ValueError as e:
            logger.error("Circular dependency detected: %s", e)
            raise

        affected_order = [vid for vid in calc_order if vid in affected_ids]

        affected_results = []
        for aff_id in affected_order:
            aff_var = self.db.query(Variable).filter(Variable.id == aff_id).first()

            if aff_var and aff_var.value_type.value == "formula" and aff_var.formula:
                try:
                    old = aff_var.calculated_value
                    new = await self._calculate_variable(aff_var)
                    aff_var.calculated_value = str(new)

                    affected_results.append(
                        {
                            "id": aff_var.id,
                            "name": aff_var.key,
                            "old_value": old,
                            "new_value": new,
                        }
                    )
                except Exception as e:
                    logger.error(
                        "Error recalculating dependent variable %s: %s",
                        aff_var.key,
                        e,
                    )
                    continue

        self.db.commit()

        return {
            "updated_variable": {
                "id": variable_id,
                "name": var.key,
                "old_value": old_value,
                "new_value": new_value,
            },
            "affected_variables": affected_results,
        }

    async def _calculate_variable(self, variable: Variable) -> Any:
        """Calculate value for a formula variable"""
        if not variable.formula:
            return variable.raw_value

        # Parse formula to get dependencies
        parse_result = self.parser.parse_formula(variable.formula)
        if not parse_result["valid"]:
            raise ValueError(f"Invalid formula: {parse_result['error']}")

        # Get values of dependent variables
        var_values: Dict[str, Any] = {}
        for dep_ref in parse_result["variables"]:
            # Try to find variable by ID or key
            dep_var = (
                self.db.query(Variable)
                .filter(
                    Variable.project_id == variable.project_id,
                    (Variable.id == UUID(dep_ref))
                    | (Variable.key == dep_ref)
                    | (Variable.label == dep_ref),
                )
                .first()
            )

            if not dep_var:
                raise ValueError(f"Dependent variable not found: {dep_ref}")

            # Use calculated_value if available, otherwise raw_value
            value_to_use = dep_var.calculated_value or dep_var.raw_value
            if value_to_use:
                try:
                    var_values[dep_ref] = float(value_to_use)
                except (ValueError, TypeError):
                    var_values[dep_ref] = 0
            else:
                var_values[dep_ref] = 0

        # Evaluate formula
        result = self.parser.evaluate_formula(variable.formula, var_values)
        return result

    async def validate_formula(
        self, formula: str, project_id: UUID
    ) -> Dict[str, Any]:
        """Validate formula before saving"""
        parse_result = self.parser.parse_formula(formula)

        if not parse_result["valid"]:
            return parse_result

        # Check that all referenced variables exist
        missing = []
        for var_ref in parse_result["variables"]:
            exists = (
                self.db.query(Variable)
                .filter(
                    Variable.project_id == project_id,
                    (Variable.id == UUID(var_ref))
                    | (Variable.key == var_ref)
                    | (Variable.label == var_ref),
                )
                .first()
            )

            if not exists:
                missing.append(var_ref)

        if missing:
            return {
                "valid": False,
                "variables": parse_result["variables"],
                "error": f"Variables not found: {', '.join(missing)}",
            }

        return parse_result

