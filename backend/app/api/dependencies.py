"""
Общие зависимости для всех роутеров.

Здесь определяются:
- get_db: зависимость для получения сессии БД
- get_current_user: зависимость для получения текущего пользователя (позже)
- другие общие зависимости
"""

from typing import Generator

from sqlalchemy.orm import Session

from app.db.session import SessionLocal


def get_db() -> Generator[Session, None, None]:
    """
    Зависимость для получения сессии БД.
    
    Использование в роутерах:
    ```python
    @router.get("/items")
    def get_items(db: Session = Depends(get_db)):
        # Здесь можно использовать db для запросов
        pass
    ```
    """
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()
