"""Tenants API placeholders."""

from typing import List

from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session

from app.core.deps import TestUser, get_current_user, get_db
from app.schemas.tenant import TenantCreate, TenantRead, TenantUpdate

router = APIRouter(prefix="/tenants", tags=["tenants"])


@router.get("/", response_model=List[TenantRead], summary="List tenants")
def list_tenants(
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> List[TenantRead]:
    """Placeholder for listing tenants."""
    pass


@router.get("/{tenant_id}", response_model=TenantRead, summary="Get tenant")
def get_tenant(
    tenant_id: int,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> TenantRead:
    """Placeholder for fetching a tenant."""
    pass


@router.post("/", response_model=TenantRead, summary="Create tenant")
def create_tenant(
    payload: TenantCreate,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> TenantRead:
    """Placeholder for creating a tenant."""
    pass


@router.put("/{tenant_id}", response_model=TenantRead, summary="Update tenant")
def update_tenant(
    tenant_id: int,
    payload: TenantUpdate,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> TenantRead:
    """Placeholder for updating a tenant."""
    pass


@router.delete("/{tenant_id}", summary="Delete tenant")
def delete_tenant(
    tenant_id: int,
    db: Session = Depends(get_db),
    current_user: TestUser = Depends(get_current_user),
) -> None:
    """Placeholder for deleting a tenant."""
    pass

