"""
Auth schemas for token refresh.
"""

from pydantic import BaseModel


class RefreshTokenRequest(BaseModel):
    """Schema for refresh token request."""
    refresh_token: str


class TokenResponse(BaseModel):
    """Schema for token response."""
    access_token: str
    refresh_token: str | None = None
    token_type: str = "bearer"

