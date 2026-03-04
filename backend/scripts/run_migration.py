import os
import sys

# Attempt to fix encoding
try:
    sys.stdout.reconfigure(encoding='utf-8')
    sys.stderr.reconfigure(encoding='utf-8')
except Exception:
    pass

from alembic.config import Config
from alembic import command

def run_migrations():
    print("Starting migration...")
    try:
        # Assuming alembic.ini is in the current directory (backend)
        alembic_cfg = Config("alembic.ini")
        command.upgrade(alembic_cfg, "head")
        print("Migration successful!")
    except Exception as e:
        print(f"Migration failed: {e}")
        # Print more details if possible
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    run_migrations()
