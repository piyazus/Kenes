"""
Users router.

Endpoints для управления пользователями:
- GET /list - список всех пользователей
- GET /{user_id} - получение информации о пользователе
- POST / - создание нового пользователя
- PUT /{user_id} - обновление информации о пользователе
- DELETE /{user_id} - удаление пользователя

На этом этапе это заготовка.
Реализация будет в шаге 11 вместе с моделями User.
"""

from fastapi import APIRouter

router = APIRouter()


@router.get("/list")
def list_users():
    """
    Получить список всех пользователей.
    
    Позже здесь будет: фильтрация по ролям, статусу, пагинация, поиск.
    """
    return {"message": "List users endpoint - to be implemented in step 11", "users": []}


@router.get("/{user_id}")
def get_user(user_id: int):
    """
    Получить информацию о конкретном пользователе.
    """
    return {"message": f"Get user {user_id} endpoint - to be implemented in step 11"}


@router.post("/")
def create_user():
    """
    Создать нового пользователя.
    
    Позже здесь будет: валидация данных, хеширование пароля, создание записи в БД.
    """
    return {"message": "Create user endpoint - to be implemented in step 11"}


@router.put("/{user_id}")
def update_user(user_id: int):
    """
    Обновить информацию о пользователе.
    """
    return {"message": f"Update user {user_id} endpoint - to be implemented in step 11"}


@router.delete("/{user_id}")
def delete_user(user_id: int):
    """
    Удалить пользователя.
    
    Позже здесь будет: мягкое удаление, архивирование.
    """
    return {"message": f"Delete user {user_id} endpoint - to be implemented in step 11"}
