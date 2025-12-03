"""Compatibility shims for legacy imports."""

from app.core.deps import get_current_user, get_db  # noqa: F401

__all__ = ["get_db", "get_current_user"]
