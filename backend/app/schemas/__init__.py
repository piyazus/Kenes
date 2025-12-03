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
from app.schemas.client import (
    ClientBase,
    ClientCreate,
    ClientUpdate,
    ClientRead,
)
from app.schemas.auth import (
    RefreshTokenRequest,
    TokenResponse,
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
]

