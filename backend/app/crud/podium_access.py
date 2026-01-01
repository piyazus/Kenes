"""CRUD operations for PodiumAccess."""

from __future__ import annotations

from datetime import datetime, timedelta, timezone
import secrets
from typing import Optional
from uuid import UUID

from sqlalchemy.orm import Session

from app.models.podium_access import PodiumAccess
from app.schemas.podium_access import PodiumAccessCreate


def _generate_token() -> str:
    """Generate a random URL-safe token."""
    return secrets.token_urlsafe(32)


def create_podium_access(
    db: Session,
    *,
    obj_in: PodiumAccessCreate,
) -> PodiumAccess:
    """Create a new podium access token for a project."""
    # Compute expires_at from expires_in_days if provided via object (stored in schema)
    expires_at = obj_in.expires_at

    token = _generate_token()
    # Ensure uniqueness
    while db.query(PodiumAccess).filter(PodiumAccess.access_token == token).first():
        token = _generate_token()

    db_obj = PodiumAccess(
        project_id=obj_in.project_id,
        access_token=token,
        expires_at=expires_at,
        is_active=obj_in.is_active,
    )
    db.add(db_obj)
    db.commit()
    db.refresh(db_obj)
    return db_obj


def get_podium_access_by_token(
    db: Session,
    *,
    token: str,
    now: Optional[datetime] = None,
) -> Optional[PodiumAccess]:
    """Get an active podium access by token, respecting expiration."""
    if now is None:
        now = datetime.now(timezone.utc)

    q = db.query(PodiumAccess).filter(
        PodiumAccess.access_token == token,
        PodiumAccess.is_active.is_(True),
    )
    access = q.first()
    if not access:
        return None

    if access.expires_at is not None and access.expires_at <= now:
        return None

    return access


def touch_podium_access(
    db: Session,
    *,
    db_obj: PodiumAccess,
    at: Optional[datetime] = None,
) -> PodiumAccess:
    """Update last_accessed_at timestamp."""
    if at is None:
        at = datetime.now(timezone.utc)
    db_obj.last_accessed_at = at
    db.add(db_obj)
    db.commit()
    db.refresh(db_obj)
    return db_obj




