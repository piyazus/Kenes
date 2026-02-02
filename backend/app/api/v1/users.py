"""Users API placeholders."""

from typing import List

from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session

from app.core.deps import TestUser, get_current_user, get_db
from app.schemas.user import UserCreate, UserRead, UserUpdate

router = APIRouter(prefix="/users", tags=["users"])


@router.get("/", response_model=List[UserRead], summary="List users")
def list_users(
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> List[UserRead]:
    """Placeholder for listing users."""
    pass


@router.get("/{user_id}", response_model=UserRead, summary="Get user")
def get_user(
    user_id: int,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> UserRead:
    """Placeholder for fetching a user."""
    pass


@router.post("/", response_model=UserRead, summary="Create user")
def create_user(
    payload: UserCreate,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> UserRead:
    """Placeholder for creating a user."""
    pass


@router.put("/{user_id}", response_model=UserRead, summary="Update user")
def update_user(
    user_id: int,
    payload: UserUpdate,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> UserRead:
    """Placeholder for updating a user."""
    pass


@router.delete("/{user_id}", summary="Delete user")
def delete_user(
    user_id: int,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> None:
    """Placeholder for deleting a user."""
    pass

