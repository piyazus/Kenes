"""
Базовый класс для всех ORM моделей.
Позже сюда будут импортироваться модели User, Firm, Client, Council.

Alembic будет использовать этот файл для автогенерации миграций.
"""

from sqlalchemy.orm import DeclarativeBase


class Base(DeclarativeBase):
    pass
