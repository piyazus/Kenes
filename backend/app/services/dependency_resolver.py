"""Dependency resolver for variable formulas - handles dependency graphs and calculation order."""

from __future__ import annotations

from typing import Dict, List, Set
from uuid import UUID

from sqlalchemy.orm import Session


class DependencyResolver:
    """
    Resolves variable dependencies and determines calculation order.

    Detects circular dependencies.
    """

    def __init__(self, db: Session):
        self.db = db

    def build_dependency_graph(self, project_id: UUID) -> Dict[UUID, Set[UUID]]:
        """
        Build a graph of variable dependencies.

        Returns: {variable_id: set_of_variables_it_depends_on}
        """
        from app.models.variable import Variable

        variables = (
            self.db.query(Variable)
            .filter(Variable.project_id == project_id)
            .all()
        )

        graph: Dict[UUID, Set[UUID]] = {}
        for var in variables:
            depends_on = set(var.depends_on) if var.depends_on else set()
            graph[var.id] = depends_on

        return graph

    def topological_sort(self, graph: Dict[UUID, Set[UUID]]) -> List[UUID]:
        """
        Sort variables in calculation order (dependencies first).

        Raises ValueError if circular dependency detected.

        Uses Kahn's algorithm.
        """
        # Calculate in-degree (how many variables depend on this one)
        in_degree: Dict[UUID, int] = {node: 0 for node in graph}
        for node, deps in graph.items():
            for dep in deps:
                if dep in in_degree:
                    in_degree[dep] += 1

        # Start with nodes that have no dependencies
        queue: List[UUID] = [node for node, degree in in_degree.items() if degree == 0]
        sorted_order: List[UUID] = []

        while queue:
            node = queue.pop(0)
            sorted_order.append(node)

            # Remove edges from this node
            for neighbor, deps in graph.items():
                if node in deps:
                    in_degree[neighbor] -= 1
                    if in_degree[neighbor] == 0:
                        queue.append(neighbor)

        # Check for circular dependencies
        if len(sorted_order) != len(graph):
            raise ValueError("Circular dependency detected in variable formulas")

        return sorted_order

    def get_affected_variables(
        self, variable_id: UUID, graph: Dict[UUID, Set[UUID]]
    ) -> Set[UUID]:
        """
        Get all variables that depend on the given variable (directly or indirectly).
        """
        affected: Set[UUID] = set()
        to_check: List[UUID] = [variable_id]

        while to_check:
            current = to_check.pop()
            for var_id, deps in graph.items():
                if current in deps and var_id not in affected:
                    affected.add(var_id)
                    to_check.append(var_id)

        return affected

