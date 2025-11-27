from dataclasses import dataclass
import os


@dataclass
class Settings:
    project_name: str = "KenesCloud API"
    version: str = "0.1.0"
    api_v1_prefix: str = "/api/v1"

    # База данных PostgreSQL
    database_url: str = os.getenv(
        "DATABASE_URL",
        "postgresql+psycopg2://postgres:postgres@localhost:5432/kenescloud",
    )


settings = Settings()
