"""Notification service for managing in-app notifications."""

from __future__ import annotations

import logging
from uuid import UUID

from sqlalchemy.orm import Session

from app.models.notification import (
    Notification,
    NotificationType,
    RelatedEntityType,
)
from app.models.risk_alert import RiskAlert

logger = logging.getLogger(__name__)


class NotificationService:
    """Manages in-app notifications for users."""

    async def create_notification(
        self,
        db: Session,
        user_id: int,
        notification_type: NotificationType | str,
        title: str,
        message: str | None = None,
        related_entity_type: RelatedEntityType | str | None = None,
        related_entity_id: UUID | None = None,
    ) -> Notification:
        """Create a new notification."""
        # Convert string to enum if needed
        if isinstance(notification_type, str):
            notification_type = NotificationType(notification_type)
        if isinstance(related_entity_type, str):
            related_entity_type = RelatedEntityType(related_entity_type)

        notification = Notification(
            user_id=user_id,
            notification_type=notification_type,
            title=title,
            message=message,
            related_entity_type=related_entity_type,
            related_entity_id=related_entity_id,
            is_read=False,
        )

        db.add(notification)
        db.commit()
        db.refresh(notification)

        logger.info("Notification created: %s for user %s", notification.id, user_id)
        return notification

    async def get_user_notifications(
        self,
        db: Session,
        user_id: int,
        unread_only: bool = False,
        limit: int = 50,
    ) -> list[Notification]:
        """Get notifications for a user."""
        query = db.query(Notification).filter(Notification.user_id == user_id)

        if unread_only:
            query = query.filter(Notification.is_read == False)  # noqa: E712

        notifications = (
            query.order_by(Notification.created_at.desc()).limit(limit).all()
        )

        return notifications

    async def mark_as_read(
        self, db: Session, notification_id: UUID, user_id: int
    ) -> Notification | None:
        """
        Mark notification as read.

        Only allows user to mark their own notifications as read.
        """
        notification = (
            db.query(Notification)
            .filter(
                Notification.id == notification_id,
                Notification.user_id == user_id,
            )
            .first()
        )

        if not notification:
            return None

        from datetime import datetime

        notification.is_read = True
        notification.read_at = datetime.utcnow()
        db.commit()
        db.refresh(notification)

        logger.info("Notification marked as read: %s", notification_id)
        return notification

    async def delete_notification(
        self, db: Session, notification_id: UUID, user_id: int
    ) -> bool:
        """
        Delete notification.

        Only allows user to delete their own notifications.
        """
        notification = (
            db.query(Notification)
            .filter(
                Notification.id == notification_id,
                Notification.user_id == user_id,
            )
            .first()
        )

        if not notification:
            return False

        db.delete(notification)
        db.commit()

        logger.info("Notification deleted: %s", notification_id)
        return True

    async def notify_risk_detected(
        self,
        db: Session,
        risk_alert: RiskAlert,
        project_owner_id: int,
    ) -> Notification:
        """Create notification when risk is detected."""
        severity_label = risk_alert.severity.value.upper()
        title = f"New {severity_label} risk detected: {risk_alert.title}"
        message = risk_alert.description or risk_alert.recommendation

        return await self.create_notification(
            db=db,
            user_id=project_owner_id,
            notification_type=NotificationType.RISK_ALERT,
            title=title,
            message=message,
            related_entity_type=RelatedEntityType.RISK_ALERT,
            related_entity_id=risk_alert.id,
        )

