"""Safe formula evaluation for Loom-style variables."""

from __future__ import annotations

import ast
import operator
from typing import Any, Dict


_ALLOWED_BIN_OPS = {
    ast.Add: operator.add,
    ast.Sub: operator.sub,
    ast.Mult: operator.mul,
    ast.Div: operator.truediv,
    ast.Mod: operator.mod,
    ast.Pow: operator.pow,
}

_ALLOWED_UNARY_OPS = {
    ast.UAdd: operator.pos,
    ast.USub: operator.neg,
}


def _eval_node(node: ast.AST, context: Dict[str, float]) -> float:
    if isinstance(node, ast.BinOp):
        if type(node.op) not in _ALLOWED_BIN_OPS:
            raise ValueError("Unsupported binary operator")
        left = _eval_node(node.left, context)
        right = _eval_node(node.right, context)
        return _ALLOWED_BIN_OPS[type(node.op)](left, right)

    if isinstance(node, ast.UnaryOp):
        if type(node.op) not in _ALLOWED_UNARY_OPS:
            raise ValueError("Unsupported unary operator")
        operand = _eval_node(node.operand, context)
        return _ALLOWED_UNARY_OPS[type(node.op)](operand)

    if isinstance(node, ast.Num):  # py<3.8
        return float(node.n)  # type: ignore[return-value]

    if isinstance(node, ast.Constant):
        if not isinstance(node.value, (int, float)):
            raise ValueError("Only numeric constants are allowed")
        return float(node.value)

    if isinstance(node, ast.Name):
        if node.id not in context:
            raise ValueError(f"Unknown variable: {node.id}")
        return float(context[node.id])

    raise ValueError("Unsupported expression node")


def evaluate_formula(formula: str, context: Dict[str, float]) -> float:
    """
    Safely evaluate a simple arithmetic formula with variables.

    Supported:
    - +, -, *, /, %, **, unary +/-
    - numeric literals
    - variable names present in `context`
    """
    try:
        expr = ast.parse(formula, mode="eval")
    except SyntaxError as exc:
        raise ValueError(f"Invalid formula syntax: {formula}") from exc

    if not isinstance(expr, ast.Expression):
        raise ValueError("Formula must be an expression")

    return float(_eval_node(expr.body, context))




