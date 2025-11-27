# KenesCloud Backend

Минимальный каркас FastAPI приложения для MVP KenesCloud.

## Быстрый запуск

```bash
cd backend

# 1. Создать и активировать виртуальное окружение (рекомендуется)
python -m venv .venv
# Windows:
.venv\Scripts\activate
# Linux/macOS:
source .venv/bin/activate

# 2. Установить зависимости
pip install --upgrade pip
pip install -r requirements.txt

# 3. Запустить сервер разработки
uvicorn app.main:app --reload
```

После запуска проверь:

### Healthcheck
http://127.0.0.1:8000/api/v1/health

Ожидаемый ответ:

```json
{"status": "ok"}
```
