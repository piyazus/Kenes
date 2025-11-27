from fastapi import FastAPI

from app.api.v1 import router as v1_router
from app.api.v1.health import router as health_router
from app.core.config import settings
from app.db.session import engine
from app.db.base import Base


def create_application() -> FastAPI:
    app = FastAPI(
        title=settings.project_name,
        version=settings.version,
    )

    # Монтируем health check отдельно
    app.include_router(health_router, prefix=settings.api_v1_prefix, tags=["health"])
    
    # Монтируем все остальные роутеры v1
    app.include_router(v1_router, prefix=settings.api_v1_prefix)

    return app


app = create_application()


@app.on_event("startup")
def on_startup():
    """
    При старте создаем таблицы, если их еще нет.
    На проде это будет отключено, так как миграции будет выполнять Alembic.
    
    Обработка ошибок подключения к БД для локальной разработки.
    """
    try:
        Base.metadata.create_all(bind=engine)
    except Exception as e:
        print(f"⚠️  Warning: Could not connect to database on startup: {e}")
        print("⚠️  Application will continue, but database operations will fail until DB is available")
