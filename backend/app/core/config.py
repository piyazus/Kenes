"""
Application configuration.

Centralized settings for the KenesCloud backend.
All settings can be overridden via environment variables.
"""

import os
from dataclasses import dataclass


@dataclass
class Settings:
    """Application settings with environment variable support."""
    
    # Project metadata
    project_name: str = "KenesCloud API"
    version: str = "0.1.0"
    api_v1_prefix: str = "/api/v1"

    # Database
    database_url: str = os.getenv(
        "DATABASE_URL",
        "postgresql+psycopg2://postgres:postgres@localhost:5432/kenescloud",
    )

    # JWT Authentication
    jwt_secret_key: str = os.getenv(
        "JWT_SECRET_KEY",
        "your-secret-key-change-in-production-min-32-chars",
    )
    jwt_algorithm: str = os.getenv("JWT_ALGORITHM", "HS256")
    jwt_access_token_expire_minutes: int = int(
        os.getenv("JWT_ACCESS_TOKEN_EXPIRE_MINUTES", "30")
    )
    jwt_refresh_token_expire_days: int = int(
        os.getenv("JWT_REFRESH_TOKEN_EXPIRE_DAYS", "7")
    )


settings = Settings()
