"""Clients API placeholders."""

from typing import List

from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session

from app.core.deps import TestUser, get_current_user, get_db
from app.schemas.client import ClientCreate, ClientRead, ClientUpdate

router = APIRouter(prefix="/clients", tags=["clients"])


@router.get("/", response_model=List[ClientRead], summary="List clients")
def list_clients(
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> List[ClientRead]:
    """Placeholder for listing clients."""
    pass


@router.get("/{client_id}", response_model=ClientRead, summary="Get client")
def get_client(
    client_id: int,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> ClientRead:
    """Placeholder for fetching a client."""
    pass


@router.post("/", response_model=ClientRead, summary="Create client")
def create_client(
    payload: ClientCreate,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> ClientRead:
    """Placeholder for creating a client."""
    pass


@router.put("/{client_id}", response_model=ClientRead, summary="Update client")
def update_client(
    client_id: int,
    payload: ClientUpdate,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> ClientRead:
    """Placeholder for updating a client."""
    pass


@router.delete("/{client_id}", summary="Delete client")
def delete_client(
    client_id: int,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> None:
    """Placeholder for deleting a client."""
    pass

