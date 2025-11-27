"""
Auth router.

Endpoints для аутентификации и авторизации:
- POST /login - вход пользователя
- POST /register - регистрация
- POST /refresh - обновление токена
- POST /logout - выход

На этом этапе это заготовка.
Реализация будет в шаге 11 вместе с JWT токенами и моделями User.
"""

from fastapi import APIRouter

router = APIRouter()


@router.post("/login")
def login():
    """
    Endpoint для входа пользователя.
    
    Позже здесь будет: валидация email/password, генерация JWT токена.
    """
    return {"message": "Login endpoint - to be implemented in step 11"}


@router.post("/register")
def register():
    """
    Endpoint для регистрации нового пользователя.
    
    Позже здесь будет: валидация данных, создание записи в БД, отправка письма подтверждения.
    """
    return {"message": "Register endpoint - to be implemented in step 11"}


@router.post("/refresh")
def refresh_token():
    """
    Endpoint для обновления JWT токена.
    
    Позже здесь будет: валидация refresh токена, выдача нового access токена.
    """
    return {"message": "Refresh token endpoint - to be implemented in step 11"}


@router.post("/logout")
def logout():
    """
    Endpoint для выхода пользователя.
    
    Позже здесь будет: инвалидация токена.
    """
    return {"message": "Logout endpoint - to be implemented in step 11"}
