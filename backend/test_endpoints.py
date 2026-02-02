import requests
import sys

endpoints = [
    'health',
    'auth/login',
    'tenants/list',
    'users/list',
    'clients/list'
]

print("Testing API Endpoints:\n")
for endpoint in endpoints:
    try:
        response = requests.get(f'http://127.0.0.1:8000/api/v1/{endpoint}')
        print(f"✓ GET /api/v1/{endpoint}: {response.status_code}")
        if response.json().get('message'):
            print(f"  → {response.json()['message']}\n")
    except Exception as e:
        print(f"✗ GET /api/v1/{endpoint}: {str(e)}\n")
