"""
Пакет для Pydantic схем.

Содержит все Pydantic модели для валидации и сериализации:
- User схемы
- Tenant схемы
- Client схемы
"""

from app.schemas.user import (
    UserBase,
    UserCreate,
    UserUpdate,
    UserRead,
    UserLogin,
    UserRegister,
)
from app.schemas.tenant import (
    TenantBase,
    TenantCreate,
    TenantUpdate,
    TenantRead,
)
from app.schemas.client import ClientBase, ClientCreate, ClientUpdate, ClientRead
from app.schemas.auth import (
    RefreshTokenRequest,
    TokenResponse,
)
from app.schemas.project import ProjectBase, ProjectCreate, ProjectUpdate, ProjectResponse
from app.schemas.document import (
    DocumentBase,
    DocumentCreate,
    DocumentUpdate,
    DocumentResponse,
    DocumentUploadResponse,
)
from app.schemas.variable import (
    VariableBase,
    VariableCreate,
    VariableUpdate,
    VariableResponse,
)
from app.schemas.podium_access import (
    PodiumAccessBase,
    PodiumAccessCreate,
    PodiumAccessRead,
)
from app.schemas.chat_message import (
    ChatMessageBase,
    ChatMessageCreate,
    ChatMessageRead,
)
from app.schemas.risk_alert import (
    RiskAlertBase,
    RiskAlertCreate,
    RiskAlertUpdate,
    RiskAlertResponse,
)
from app.schemas.notification import (
    NotificationBase,
    NotificationCreate,
    NotificationUpdate,
    NotificationResponse,
)

__all__ = [
    # User schemas
    "UserBase",
    "UserCreate",
    "UserUpdate",
    "UserRead",
    "UserLogin",
    "UserRegister",
    # Tenant schemas
    "TenantBase",
    "TenantCreate",
    "TenantUpdate",
    "TenantRead",
    # Client schemas
    "ClientBase",
    "ClientCreate",
    "ClientUpdate",
    "ClientRead",
    # Auth schemas
    "RefreshTokenRequest",
    "TokenResponse",
    # Project schemas
    "ProjectBase",
    "ProjectCreate",
    "ProjectUpdate",
    "ProjectResponse",
    # Document schemas
    "DocumentBase",
    "DocumentCreate",
    "DocumentUpdate",
    "DocumentResponse",
    "DocumentUploadResponse",
    # Variable schemas
    "VariableBase",
    "VariableCreate",
    "VariableUpdate",
    "VariableResponse",
    # PodiumAccess schemas
    "PodiumAccessBase",
    "PodiumAccessCreate",
    "PodiumAccessRead",
    # ChatMessage schemas
    "ChatMessageBase",
    "ChatMessageCreate",
    "ChatMessageRead",
    # RiskAlert schemas
    "RiskAlertBase",
    "RiskAlertCreate",
    "RiskAlertUpdate",
    "RiskAlertResponse",
    # Notification schemas
    "NotificationBase",
    "NotificationCreate",
    "NotificationUpdate",
    "NotificationResponse",
]

