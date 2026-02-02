"""Pydantic schemas for ChatMessage entity."""

from __future__ import annotations

from datetime import datetime
from typing import Optional
from uuid import UUID

from pydantic import BaseModel, Field

from app.models.chat_message import ChatRole


class ChatMessageBase(BaseModel):
    """Shared chat message fields."""

    role: ChatRole
    content: str = Field(..., min_length=1)


class ChatMessageCreate(ChatMessageBase):
    """Payload for creating a chat message."""

    project_id: UUID


class ChatMessageRead(ChatMessageBase):
    """Representation of a chat message returned by the API."""

    id: UUID
    project_id: UUID
    created_at: datetime

    class Config:
        from_attributes = True


