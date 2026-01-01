"""add kenescloud models"""

from __future__ import annotations

from alembic import op
import sqlalchemy as sa
from sqlalchemy.dialects import postgresql


revision = "20250301"
down_revision = "2025022201"
branch_labels = None
depends_on = None


def upgrade() -> None:
    # Project: already exists; ensure status enum exists
    project_status_enum = sa.Enum("draft", "active", "archived", name="project_status")
    project_status_enum.create(op.get_bind(), checkfirst=True)

    # Documents adjustments
    with op.batch_alter_table("documents", schema=None) as batch_op:
        if op.get_bind().dialect.name == "postgresql":
            batch_op.alter_column("filename", new_column_name="file_name", existing_type=sa.String(length=255))
            batch_op.alter_column("mime_type", new_column_name="file_type", existing_type=sa.String(length=255))
            batch_op.alter_column("embedding_status", new_column_name="status")
        else:
            batch_op.alter_column("filename", new_column_name="file_name")
            batch_op.alter_column("mime_type", new_column_name="file_type")
            batch_op.alter_column("embedding_status", new_column_name="status")
        batch_op.add_column(sa.Column("extracted_text", sa.Text(), nullable=True))
        batch_op.add_column(sa.Column("metadata", postgresql.JSONB(astext_type=sa.Text()) if op.get_bind().dialect.name == "postgresql" else sa.JSON(), nullable=True))
        batch_op.add_column(sa.Column("created_at", sa.DateTime(timezone=True), server_default=sa.text("NOW()"), nullable=True))
        # remove legacy columns if exist
        for col in ("file_size", "uploaded_by", "error_message", "uploaded_at"):
            try:
                batch_op.drop_column(col)
            except Exception:
                pass

    # Document status enum
    document_status_enum = sa.Enum("processing", "ready", "error", name="document_status")
    document_status_enum.create(op.get_bind(), checkfirst=True)

    # Variables adjustments
    with op.batch_alter_table("variables", schema=None) as batch_op:
        batch_op.alter_column("name", new_column_name="key")
        batch_op.alter_column("display_name", new_column_name="label")
        batch_op.alter_column("type", new_column_name="value_type")
        batch_op.alter_column("value", new_column_name="raw_value")
        batch_op.add_column(sa.Column("calculated_value", sa.Text(), nullable=True))
        batch_op.add_column(sa.Column("formula", sa.Text(), nullable=True))
        for col in ("unit", "description", "order", "updated_at"):
            try:
                batch_op.drop_column(col)
            except Exception:
                pass

    value_type_enum = sa.Enum("number", "string", "boolean", "formula", name="value_type")
    value_type_enum.create(op.get_bind(), checkfirst=True)

    # Podium access token rename
    with op.batch_alter_table("podium_accesses", schema=None) as batch_op:
        batch_op.alter_column("access_token", new_column_name="public_token")

    # Chat messages: drop user_id if exists
    with op.batch_alter_table("chat_messages", schema=None) as batch_op:
        try:
            batch_op.drop_column("user_id")
        except Exception:
            pass


def downgrade() -> None:
    # Reverse operations (best-effort)
    with op.batch_alter_table("chat_messages", schema=None) as batch_op:
        batch_op.add_column(sa.Column("user_id", sa.Integer(), nullable=True))

    with op.batch_alter_table("podium_accesses", schema=None) as batch_op:
        batch_op.alter_column("public_token", new_column_name="access_token")

    with op.batch_alter_table("variables", schema=None) as batch_op:
        batch_op.alter_column("key", new_column_name="name")
        batch_op.alter_column("label", new_column_name="display_name")
        batch_op.alter_column("value_type", new_column_name="type")
        batch_op.alter_column("raw_value", new_column_name="value")
        for col in ("calculated_value", "formula"):
            try:
                batch_op.drop_column(col)
            except Exception:
                pass

    with op.batch_alter_table("documents", schema=None) as batch_op:
        for col in ("extracted_text", "metadata", "created_at"):
            try:
                batch_op.drop_column(col)
            except Exception:
                pass
        batch_op.alter_column("file_name", new_column_name="filename")
        batch_op.alter_column("file_type", new_column_name="mime_type")
        batch_op.alter_column("status", new_column_name="embedding_status")

    try:
        sa.Enum(name="value_type").drop(op.get_bind(), checkfirst=True)
        sa.Enum(name="document_status").drop(op.get_bind(), checkfirst=True)
    except Exception:
        pass


