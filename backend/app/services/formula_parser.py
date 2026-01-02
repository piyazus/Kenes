"""Formula parser service for extracting and evaluating variable references in formulas."""

from __future__ import annotations

import re
from typing import Any, Dict

from uuid import UUID


class FormulaParser:
    """
    Parses formulas and extracts variable references.

    Formula syntax: {{variable_name}} or {{variable_id}}
    Example: "{{revenue}} * (1 + {{growth_rate}} / 100)"
    """

    VARIABLE_PATTERN = r'\{\{([a-zA-Z0-9_\-]+)\}\}'

    def parse_formula(self, formula: str) -> Dict[str, Any]:
        """
        Parse formula and extract:
        - variables referenced
        - valid syntax check

        Returns: {
            "valid": bool,
            "variables": ["var_id_1", "var_id_2"],
            "error": str | None
        }
        """
        if not formula:
            return {"valid": False, "variables": [], "error": "Formula is empty"}

        # Extract all {{variable}} references
        matches = re.findall(self.VARIABLE_PATTERN, formula)

        if not matches:
            return {
                "valid": False,
                "variables": [],
                "error": "No variables found in formula",
            }

        # Check for invalid characters (prevent SQL injection, code execution)
        allowed_chars = re.compile(r'^[\w\s\{\}\+\-\*\/\(\)\.\,\[\]]+$')
        if not allowed_chars.match(formula):
            return {
                "valid": False,
                "variables": [],
                "error": "Formula contains invalid characters",
            }

        return {
            "valid": True,
            "variables": list(set(matches)),  # Unique variables
            "error": None,
        }

    def replace_variables(self, formula: str, variable_values: Dict[str, Any]) -> str:
        """
        Replace {{variable}} with actual values.

        Example: "{{revenue}} * 1.15" → "1000000 * 1.15"
        """
        result = formula
        for var_ref, value in variable_values.items():
            result = result.replace(f"{{{{{var_ref}}}}}", str(value))
        return result

    def evaluate_formula(self, formula: str, variable_values: Dict[str, Any]) -> Any:
        """
        Safely evaluate formula after replacing variables.

        Uses restricted eval (no __import__, exec, etc.)
        """
        try:
            # Replace variable references
            expression = self.replace_variables(formula, variable_values)

            # Safe eval with restricted builtins
            allowed_names = {
                "abs": abs,
                "min": min,
                "max": max,
                "round": round,
                "sum": sum,
                "len": len,
                "pow": pow,
                "__builtins__": {},
            }

            result = eval(expression, {"__builtins__": allowed_names}, {})
            return result
        except Exception as e:
            raise ValueError(f"Formula evaluation error: {str(e)}")

