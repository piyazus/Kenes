"""
Clients router.

Endpoints для управления клиентами:
- GET /list - список всех клиентов
- GET /{client_id} - получение информации о клиенте
- POST / - создание нового клиента
- PUT /{client_id} - обновление информации о клиенте
- DELETE /{client_id} - удаление клиента

На этом этапе это заготовка.
Реализация будет в шаге 11 вместе с моделями Client.
"""

from fastapi import APIRouter

router = APIRouter()


@router.get("/list")
def list_clients():
    """
    Получить список всех клиентов.
    
    Позже здесь будет: фильтрация по статусу, тенанту, пагинация, поиск.
    """
    return {"message": "List clients endpoint - to be implemented in step 11", "clients": []}


@router.get("/{client_id}")
def get_client(client_id: int):
    """
    Получить информацию о конкретном клиенте.
    """
    return {"message": f"Get client {client_id} endpoint - to be implemented in step 11"}


@router.post("/")
def create_client():
    """
    Создать нового клиента.
    
    Позже здесь будет: валидация данных, создание записи в БД, привязка к тенанту.
    """
    return {"message": "Create client endpoint - to be implemented in step 11"}


@router.put("/{client_id}")
def update_client(client_id: int):
    """
    Обновить информацию о клиенте.
    """
    return {"message": f"Update client {client_id} endpoint - to be implemented in step 11"}


@router.delete("/{client_id}")
def delete_client(client_id: int):
    """
    Удалить клиента.
    
    Позже здесь будет: мягкое удаление, архивирование.
    """
    return {"message": f"Delete client {client_id} endpoint - to be implemented in step 11"}
