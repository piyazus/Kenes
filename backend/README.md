# KenesCloud Backend

Backend API для SaaS платформы KenesCloud - системы управления для консалтинговых фирм.

## Технологический стек

- **Framework**: FastAPI
- **ORM**: SQLAlchemy 2.x
- **База данных**: PostgreSQL
- **Миграции**: Alembic
- **Тесты**: pytest
- **Линтер**: ruff

## Быстрый запуск

### 1. Установка зависимостей

```bash
cd backend

# Создать и активировать виртуальное окружение
python -m venv .venv
# Windows:
.venv\Scripts\activate
# Linux/macOS:
source .venv/bin/activate

# Установить зависимости
pip install --upgrade pip
pip install -r requirements.txt
```

### 2. Настройка переменных окружения

Создайте файл `.env` в корне `backend/` или установите переменные окружения:

```bash
# База данных
DATABASE_URL=postgresql+psycopg2://postgres:postgres@localhost:5432/kenescloud

# JWT настройки
JWT_SECRET_KEY=your-secret-key-change-in-production-min-32-chars
JWT_ALGORITHM=HS256
JWT_ACCESS_TOKEN_EXPIRE_MINUTES=30
JWT_REFRESH_TOKEN_EXPIRE_DAYS=7
```

**Важно**: В продакшене используйте сильный секретный ключ (минимум 32 символа)!

### 3. Настройка базы данных

Убедитесь, что PostgreSQL запущен и создана база данных:

```bash
# Создать базу данных
createdb kenescloud

# Или через psql:
psql -U postgres
CREATE DATABASE kenescloud;
```

### 4. Миграции (Alembic)

Alembic уже инициализирован внутри `backend/`.

```bash
# Применить все миграции
alembic upgrade head

# Создать новую миграцию (пример)
alembic revision --autogenerate -m "add new table"
```

Переменная `DATABASE_URL` автоматически подхватывается из окружения (или берётся из `app/core/config.py`).

Перед запуском приложения убедитесь, что миграции применены.

### 5. Запуск сервера

```bash
# Запуск с hot-reload
uvicorn app.main:app --reload --host 127.0.0.1 --port 8000

# Или через скрипт (Windows)
run_server.bat
```

После запуска:

- **API**: http://127.0.0.1:8000
- **Swagger UI**: http://127.0.0.1:8000/docs
- **ReDoc**: http://127.0.0.1:8000/redoc
- **Healthcheck**: http://127.0.0.1:8000/api/v1/health

## API Endpoints

### Health

- `GET /api/v1/health` - Проверка работоспособности сервиса

### Authentication

- `POST /api/v1/auth/register` - Регистрация нового пользователя
- `POST /api/v1/auth/login` - Вход, получение access и refresh токенов
- `POST /api/v1/auth/refresh` - Обновление access токена
- `POST /api/v1/auth/logout` - Выход (placeholder)

### Users

- `GET /api/v1/users` - Список пользователей (placeholder)
- `GET /api/v1/users/{user_id}` - Информация о пользователе
- `POST /api/v1/users/` - Создание пользователя
- `PUT /api/v1/users/{user_id}` - Обновление пользователя
- `DELETE /api/v1/users/{user_id}` - Удаление пользователя

### Tenants

- `GET /api/v1/tenants` - Список всех тенантов (placeholder)
- `GET /api/v1/tenants/{tenant_id}` - Информация о тенанте
- `POST /api/v1/tenants/` - Создание тенанта
- `PUT /api/v1/tenants/{tenant_id}` - Обновление тенанта
- `DELETE /api/v1/tenants/{tenant_id}` - Удаление тенанта

### Clients

- `GET /api/v1/clients` - Список клиентов (placeholder)
- `GET /api/v1/clients/{client_id}` - Информация о клиенте
- `POST /api/v1/clients/` - Создание клиента
- `PUT /api/v1/clients/{client_id}` - Обновление клиента
- `DELETE /api/v1/clients/{client_id}` - Удаление клиента

## Аутентификация

API использует JWT токены для аутентификации. После успешного логина вы получите `access_token` и `refresh_token`.

### Использование токена

Добавьте заголовок в запросы:

```
Authorization: Bearer <access_token>
```

### Пример запроса

```bash
# Логин
curl -X POST "http://127.0.0.1:8000/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password123"}'

# Защищенный запрос
curl -X GET "http://127.0.0.1:8000/api/v1/users/list" \
  -H "Authorization: Bearer <access_token>"
```

## Tenant Isolation

Все запросы к клиентам и пользователям автоматически фильтруются по `tenant_id` текущего пользователя. Это обеспечивает полную изоляцию данных между разными фирмами (tenants).

## Тестирование

### Запуск тестов

```bash
# Все тесты
pytest tests/ -v

# Конкретный тест
pytest tests/test_health.py -v

# С покрытием (если установлен pytest-cov)
pytest tests/ --cov=app --cov-report=html
```

### Настройка тестовой БД

Тесты используют отдельную тестовую БД. Убедитесь, что переменная окружения `DATABASE_URL` указывает на тестовую базу.

## Линтинг

### Запуск линтера

```bash
# Проверка
ruff check .

# Автоисправление
ruff check . --fix

# Форматирование (если используется)
ruff format .
```

## CI/CD

GitHub Actions автоматически запускает линтер и тесты при каждом push и pull request.

Workflow файл: `.github/workflows/ci.yml`

### Локальный запуск CI

```bash
# Установить act (локальный runner для GitHub Actions)
# https://github.com/nektos/act

# Запустить workflow
act
```

## Структура проекта

```
backend/
├── app/
│   ├── __init__.py
│   ├── main.py                 # FastAPI приложение
│   ├── api/
│   │   └── v1/
│   │       ├── auth.py        # Аутентификация
│   │       ├── users.py       # Управление пользователями
│   │       ├── tenants.py     # Управление тенантами
│   │       ├── clients.py     # Управление клиентами
│   │       └── health.py      # Healthcheck
│   ├── core/
│   │   ├── config.py          # Настройки приложения
│   │   ├── deps.py            # Общие зависимости FastAPI
│   │   └── security.py        # JWT и password hashing
│   ├── db/
│   │   ├── base.py            # Базовый класс для моделей
│   │   └── session.py         # Database session
│   ├── models/
│   │   ├── user.py            # User модель
│   │   ├── tenant.py          # Tenant модель
│   │   └── client.py          # Client модель
│   └── schemas/
│       ├── user.py            # User Pydantic схемы
│       ├── tenant.py          # Tenant Pydantic схемы
│       ├── client.py          # Client Pydantic схемы
│       └── auth.py            # Auth Pydantic схемы
├── alembic/
│   ├── env.py                 # Alembic environment
│   ├── script.py.mako         # Шаблон миграций
│   └── versions/              # Каталог миграций
├── alembic.ini                # Конфигурация Alembic
├── tests/
│   ├── test_health.py         # Тесты healthcheck
│   └── test_auth.py           # Тесты аутентификации
├── .github/
│   └── workflows/
│       └── ci.yml             # GitHub Actions CI
├── requirements.txt           # Python зависимости
├── pyproject.toml             # Ruff конфигурация
└── README.md                  # Этот файл
```

## Разработка

### Добавление нового эндпоинта

1. Создайте роутер в `app/api/v1/`
2. Подключите роутер в `app/main.py`
3. Добавьте тесты в `tests/`

### Добавление новой модели

1. Создайте модель в `app/models/`
2. Импортируйте модель в `app/db/base.py` (для Alembic)
3. Создайте Pydantic схемы в `app/schemas/`
4. Создайте CRUD роутер в `app/api/v1/`

## Безопасность

- ✅ JWT токены с настраиваемым временем жизни
- ✅ Password hashing с bcrypt
- ✅ Tenant isolation для изоляции данных
- ✅ Валидация входных данных через Pydantic
- ⚠️ CORS настроен для разработки (`allow_origins=["*"]`) - измените для продакшена!

## Следующие шаги

- [ ] Реализовать реальную аутентификацию и tenant isolation
- [ ] Добавить email подтверждение
- [ ] Реализовать роли и права доступа (RBAC)
- [ ] Добавить rate limiting
- [ ] Настроить логирование (структурированные логи)
- [ ] Добавить мониторинг и метрики

## Лицензия

Proprietary - KenesCloud
