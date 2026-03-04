"""
Temporary script to fix base.py circular import issue
"""

content = '''"""
Базовый класс для всех ORM моделей.

Alembic будет использовать этот файл для автогенерации миграций.
"""

from sqlalchemy.orm import DeclarativeBase


class Base(DeclarativeBase):
    pass
'''

with open('app/db/base.py', 'w', encoding='utf-8') as f:
    f.write(content)
    
print("✓ Updated base.py successfully")
