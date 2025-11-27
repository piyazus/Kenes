@echo off
chcp 65001 > nul
cd /d "c:\Users\User\OneDrive\Desktop\kenes\backend"
C:/Users/User/OneDrive/Desktop/kenes/.venv/Scripts/python.exe -m uvicorn app.main:app --reload --host 127.0.0.1 --port 8000
