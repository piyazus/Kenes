# KenesCloud MVP — шаги запуска

## 0) Что установить
- Python 3.10+
- Node.js LTS (включает npm)
- Любой редактор (VS Code)

## 1) Backend (Flask)
```bash
cd backend
python -m venv .venv
# Windows: .venv\Scripts\activate
source .venv/bin/activate
pip install -r requirements.txt

# (опционально) подключить OpenAI для более умных ответов
# mac/linux:
export OPENAI_API_KEY="sk-..."
export CALENDLY_URL="https://calendly.com/your-brother/30min"
# windows powershell:
# $env:OPENAI_API_KEY="sk-..."; $env:CALENDLY_URL="https://calendly.com/your-brother/30min"

python app.py
# Сервер: http://127.0.0.1:8000/health
```

## 2) Frontend (React + Vite)
```bash
cd ../frontend
cp .env.example .env       # отредактируйте при необходимости
npm install
npm run dev
# Откройте: http://localhost:5173
```

## 3) Как это работает
- Введите вопрос в виджет (справа снизу).
- Backend ищет похожие ответы в `backend/data/damu_faq.json`.
- Если уверенность низкая или вопрос сложный — бот предложит запись и пришлет ссылку Calendly.

## 4) Что поменять для брата
- Заполните `backend/data/damu_faq.json` реальными ответами.
- Установите `CALENDLY_URL` для бронирования.
- (Опционально) получите ключ OpenAI и установите `OPENAI_API_KEY`.

## 5) Деплой (коротко)
- Backend: Render/Railway/Fly.io (Python). Обязательно используйте переменную PORT.
- Frontend: Vercel/Netlify. В `.env` укажите публичный URL backend в переменной `VITE_API_BASE`.

Удачи!
