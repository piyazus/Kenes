"""
Роутеры для API v1.

Здесь агрегируются все роутеры разных модулей:
- auth: аутентификация и авторизация
- tenants: управление фирмами/организациями
- users: управление пользователями
- clients: управление клиентами
"""

from fastapi import APIRouter

from . import auth, clients, tenants, users

router = APIRouter()

# Подключаем все роутеры
router.include_router(auth.router, prefix="/auth", tags=["auth"])
router.include_router(users.router, prefix="/users", tags=["users"])
router.include_router(tenants.router, prefix="/tenants", tags=["tenants"])
router.include_router(clients.router, prefix="/clients", tags=["clients"])
