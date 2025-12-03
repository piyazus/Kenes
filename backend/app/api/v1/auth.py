"""
Auth router.

Endpoints для аутентификации и авторизации:
- POST /register - регистрация пользователя в рамках tenant
- POST /login - вход, возврат access и refresh токенов
- POST /refresh - обновление access токена
"""

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session

from app.core.deps import get_db
from app.core.security import (
    create_access_token,
    create_refresh_token,
    decode_token,
    get_password_hash,
    verify_password,
)
from app.models.user import User
from app.models.tenant import Tenant
from app.schemas.auth import RefreshTokenRequest, TokenResponse
from app.schemas.user import UserLogin, UserRead, UserRegister

router = APIRouter()


@router.post(
    "/auth/register",
    response_model=UserRead,
    status_code=status.HTTP_201_CREATED,
    summary="Register a new user",
)
def register(
    user_data: UserRegister,
    db: Session = Depends(get_db),
):
    """
    Регистрация нового пользователя.
    
    Создает пользователя и привязывает его к tenant (если указан tenant_id).
    Если tenant_id не указан, пользователь создается без привязки к tenant.
    """
    # Проверяем, что email не занят
    existing_user = db.query(User).filter(User.email == user_data.email).first()
    if existing_user:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Email already registered",
        )
    
    # Проверяем, что tenant существует
    tenant = db.query(Tenant).filter(Tenant.id == user_data.tenant_id).first()
    if not tenant:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Tenant not found",
        )
    
    # Создаем пользователя
    hashed_password = get_password_hash(user_data.password)
    db_user = User(
        email=user_data.email,
        hashed_password=hashed_password,
        tenant_id=user_data.tenant_id,
        is_active=True,
    )
    
    db.add(db_user)
    db.commit()
    db.refresh(db_user)
    
    return UserRead.model_validate(db_user)


@router.post("/auth/login", response_model=TokenResponse, summary="Login and get access token")
def login(
    credentials: UserLogin,
    db: Session = Depends(get_db),
):
    """
    Вход пользователя.
    
    Возвращает access token и refresh token для дальнейшей аутентификации.
    """
    # Находим пользователя по email
    user = db.query(User).filter(User.email == credentials.email).first()
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Incorrect email or password",
        )
    
    # Проверяем пароль
    if not verify_password(credentials.password, user.hashed_password):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Incorrect email or password",
        )
    
    # Проверяем, что пользователь активен
    if not user.is_active:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="User account is inactive",
        )
    
    # Создаем токены
    access_token = create_access_token(data={"sub": user.id, "email": user.email})
    refresh_token = create_refresh_token(data={"sub": user.id})
    
    return TokenResponse(
        access_token=access_token,
        refresh_token=refresh_token,
        token_type="bearer",
    )


@router.post("/auth/refresh", response_model=TokenResponse, summary="Refresh access token")
def refresh(
    token_data: RefreshTokenRequest,
    db: Session = Depends(get_db),
):
    """
    Обновление access токена с помощью refresh токена.
    
    Принимает refresh_token в теле запроса (JSON) и возвращает новый access_token.
    """
    # Декодируем refresh token
    payload = decode_token(token_data.refresh_token)
    if payload is None:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid or expired refresh token",
        )
    
    # Проверяем тип токена
    if payload.get("type") != "refresh":
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid token type",
        )
    
    # Получаем user_id из токена
    user_id = payload.get("sub")
    if user_id is None:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid token payload",
        )
    
    # Проверяем, что пользователь существует и активен
    user = db.query(User).filter(User.id == user_id).first()
    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="User not found",
        )
    
    if not user.is_active:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="User account is inactive",
        )
    
    # Создаем новый access token
    access_token = create_access_token(data={"sub": user.id, "email": user.email})
    
    return {
        "access_token": access_token,
        "token_type": "bearer",
    }


@router.post("/auth/logout", summary="Logout (placeholder)")
def logout():
    """
    Выход пользователя.
    
    В текущей реализации это placeholder. В будущем здесь можно добавить
    инвалидацию токенов через blacklist или удаление refresh токенов из БД.
    """
    return {"message": "Logged out successfully"}

