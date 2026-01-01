"""
Main FastAPI application.

Creates and configures the FastAPI application with all routers and middleware.
"""

import time
import uuid
from contextlib import asynccontextmanager

from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from starlette.middleware.base import BaseHTTPMiddleware

from app.api.v1 import router as api_v1_router
from app.core.config import settings


class RequestIDMiddleware(BaseHTTPMiddleware):
    """Middleware для добавления request_id к каждому запросу."""

    async def dispatch(self, request: Request, call_next):
        request_id = str(uuid.uuid4())
        request.state.request_id = request_id
        
        start_time = time.time()
        response = await call_next(request)
        process_time = time.time() - start_time
        
        response.headers["X-Request-ID"] = request_id
        response.headers["X-Process-Time"] = str(process_time)
        
        return response


@asynccontextmanager
async def lifespan(app: FastAPI):
    """
    Lifespan context manager для startup и shutdown событий.
    
    Вся работа с созданием/миграциями БД выполняется через Alembic.
    """
    yield


def create_application() -> FastAPI:
    """
    Создает и настраивает FastAPI приложение.
    
    Подключает все роутеры, middleware и настраивает CORS.
    """
    app = FastAPI(
        title=settings.project_name,
        version=settings.version,
        lifespan=lifespan,
    )

    # Middleware
    app.add_middleware(RequestIDMiddleware)
    
    # CORS middleware (для разработки, на проде настроить правильно)
    app.add_middleware(
        CORSMiddleware,
        allow_origins=["*"],  # В продакшене указать конкретные домены
        allow_credentials=True,
        allow_methods=["*"],
        allow_headers=["*"],
    )

    # Подключаем все роутеры API v1 через агрегатор
    app.include_router(api_v1_router, prefix=settings.api_v1_prefix)

    return app


app = create_application()
