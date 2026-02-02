"""Notifications router for in-app alerts."""

from __future__ import annotations

import logging
from typing import List
from uuid import UUID

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session

from app.core.deps import TestUser, get_current_user, get_db
from app.schemas.notification import NotificationResponse, NotificationUpdate
from app.services.notification_service import NotificationService

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/notifications", tags=["notifications"])


@router.get("/", response_model=List[NotificationResponse])
async def get_my_notifications(
    unread: bool = False,
    limit: int = 50,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> List[NotificationResponse]:
    """Get notifications for current user."""
    service = NotificationService()
    notifications = await service.get_user_notifications(
        db=db,
        user_id=current_user.id,
        unread_only=unread,
        limit=limit,
    )

    return [NotificationResponse.model_validate(n) for n in notifications]


@router.put("/{notification_id}/read", response_model=NotificationResponse)
async def mark_notification_read(
    notification_id: UUID,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> NotificationResponse:
    """Mark notification as read."""
    service = NotificationService()
    notification = await service.mark_as_read(
        db=db, notification_id=notification_id, user_id=current_user.id
    )

    if not notification:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Notification not found",
        )

    return NotificationResponse.model_validate(notification)


@router.delete("/{notification_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_notification(
    notification_id: UUID,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> None:
    """Delete notification."""
    service = NotificationService()
    deleted = await service.delete_notification(
        db=db, notification_id=notification_id, user_id=current_user.id
    )

    if not deleted:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Notification not found",
        )

    logger.info(
        "Notification deleted id=%s user_id=%s",
        notification_id,
        current_user.id,
    )

