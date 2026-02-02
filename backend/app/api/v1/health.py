from fastapi import APIRouter

router = APIRouter(tags=["health"])


@router.get("/health", summary="Healthcheck")
def healthcheck() -> dict[str, str]:
    """
    Простой healthcheck endpoint.

    Возвращает статус ok если приложение поднято.
    В будущем сюда можно добавить проверки базы данных, очередей и других сервисов.
    """
    return {"status": "ok"}
