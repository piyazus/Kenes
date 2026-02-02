"""Add KenesCloud models: projects, documents, variables, podium_accesses, chat_messages."""

from __future__ import annotations

from alembic import op
import sqlalchemy as sa
from sqlalchemy.dialects import postgresql


revision = "2025022201"
down_revision = "2025012701"
branch_labels = None
depends_on = None


def upgrade() -> None:
    # Enums
    project_status_enum = sa.Enum(
        "draft",
        "active",
        "archived",
        name="project_status",
    )
    project_status_enum.create(op.get_bind(), checkfirst=True)

    embedding_status_enum = sa.Enum(
        "pending",
        "processing",
        "completed",
        "failed",
        name="embedding_status",
    )
    embedding_status_enum.create(op.get_bind(), checkfirst=True)

    variable_type_enum = sa.Enum(
        "number",
        "formula",
        "text",
        name="variable_type",
    )
    variable_type_enum.create(op.get_bind(), checkfirst=True)

    chat_role_enum = sa.Enum(
        "user",
        "assistant",
        name="chat_role",
    )
    chat_role_enum.create(op.get_bind(), checkfirst=True)

    # Projects
    op.create_table(
        "projects",
        sa.Column(
            "id",
            postgresql.UUID(as_uuid=True),
            primary_key=True,
            nullable=False,
        ),
        sa.Column("tenant_id", sa.Integer(), nullable=False),
        sa.Column("client_id", sa.Integer(), nullable=False),
        sa.Column("name", sa.String(length=255), nullable=False),
        sa.Column("description", sa.Text(), nullable=True),
        sa.Column("status", project_status_enum, nullable=False, server_default="draft"),
        sa.Column("created_by", sa.Integer(), nullable=True),
        sa.Column(
            "created_at",
            sa.DateTime(timezone=True),
            server_default=sa.text("NOW()"),
            nullable=False,
        ),
        sa.Column(
            "updated_at",
            sa.DateTime(timezone=True),
            server_default=sa.text("NOW()"),
            nullable=False,
        ),
        sa.ForeignKeyConstraint(
            ["tenant_id"],
            ["tenants.id"],
            ondelete="CASCADE",
        ),
        sa.ForeignKeyConstraint(
            ["client_id"],
            ["clients.id"],
            ondelete="CASCADE",
        ),
        sa.ForeignKeyConstraint(
            ["created_by"],
            ["users.id"],
            ondelete="SET NULL",
        ),
    )
    op.create_index("ix_projects_tenant_id", "projects", ["tenant_id"])
    op.create_index("ix_projects_client_id", "projects", ["client_id"])
    op.create_index("ix_projects_created_by", "projects", ["created_by"])

    # Documents
    op.create_table(
        "documents",
        sa.Column(
            "id",
            postgresql.UUID(as_uuid=True),
            primary_key=True,
            nullable=False,
        ),
        sa.Column(
            "project_id",
            postgresql.UUID(as_uuid=True),
            nullable=False,
        ),
        sa.Column("filename", sa.String(length=255), nullable=False),
        sa.Column("file_path", sa.String(length=1024), nullable=False),
        sa.Column("file_size", sa.BigInteger(), nullable=False),
        sa.Column("mime_type", sa.String(length=255), nullable=False),
        sa.Column(
            "embedding_status",
            embedding_status_enum,
            nullable=False,
            server_default="pending",
        ),
        sa.Column("uploaded_by", sa.Integer(), nullable=True),
        sa.Column("error_message", sa.Text(), nullable=True),
        sa.Column(
            "uploaded_at",
            sa.DateTime(timezone=True),
            server_default=sa.text("NOW()"),
            nullable=False,
        ),
        sa.ForeignKeyConstraint(
            ["project_id"],
            ["projects.id"],
            ondelete="CASCADE",
        ),
        sa.ForeignKeyConstraint(
            ["uploaded_by"],
            ["users.id"],
            ondelete="SET NULL",
        ),
    )
    op.create_index("ix_documents_project_id", "documents", ["project_id"])
    op.create_index("ix_documents_uploaded_by", "documents", ["uploaded_by"])
    op.create_index("ix_documents_uploaded_at", "documents", ["uploaded_at"])

    # Variables
    op.create_table(
        "variables",
        sa.Column(
            "id",
            postgresql.UUID(as_uuid=True),
            primary_key=True,
            nullable=False,
        ),
        sa.Column(
            "project_id",
            postgresql.UUID(as_uuid=True),
            nullable=False,
        ),
        sa.Column("name", sa.String(length=255), nullable=False),
        sa.Column("display_name", sa.String(length=255), nullable=False),
        sa.Column("type", variable_type_enum, nullable=False),
        sa.Column("value", sa.Text(), nullable=False),
        sa.Column("unit", sa.String(length=32), nullable=True),
        sa.Column("description", sa.Text(), nullable=True),
        sa.Column("order", sa.Integer(), nullable=False, server_default="0"),
        sa.Column(
            "created_at",
            sa.DateTime(timezone=True),
            server_default=sa.text("NOW()"),
            nullable=False,
        ),
        sa.Column(
            "updated_at",
            sa.DateTime(timezone=True),
            server_default=sa.text("NOW()"),
            nullable=False,
        ),
        sa.ForeignKeyConstraint(
            ["project_id"],
            ["projects.id"],
            ondelete="CASCADE",
        ),
    )
    op.create_index("ix_variables_project_id", "variables", ["project_id"])
    op.create_index("ix_variables_order", "variables", ["order"])

    # Podium accesses
    op.create_table(
        "podium_accesses",
        sa.Column(
            "id",
            postgresql.UUID(as_uuid=True),
            primary_key=True,
            nullable=False,
        ),
        sa.Column(
            "project_id",
            postgresql.UUID(as_uuid=True),
            nullable=False,
        ),
        sa.Column("access_token", sa.String(length=255), nullable=False, unique=True),
        sa.Column("expires_at", sa.DateTime(timezone=True), nullable=True),
        sa.Column(
            "is_active",
            sa.Boolean(),
            nullable=False,
            server_default=sa.true(),
        ),
        sa.Column("last_accessed_at", sa.DateTime(timezone=True), nullable=True),
        sa.Column(
            "created_at",
            sa.DateTime(timezone=True),
            server_default=sa.text("NOW()"),
            nullable=False,
        ),
        sa.ForeignKeyConstraint(
            ["project_id"],
            ["projects.id"],
            ondelete="CASCADE",
        ),
    )
    op.create_index("ix_podium_accesses_project_id", "podium_accesses", ["project_id"])
    op.create_index("ix_podium_accesses_expires_at", "podium_accesses", ["expires_at"])

    # Chat messages
    op.create_table(
        "chat_messages",
        sa.Column(
            "id",
            postgresql.UUID(as_uuid=True),
            primary_key=True,
            nullable=False,
        ),
        sa.Column(
            "project_id",
            postgresql.UUID(as_uuid=True),
            nullable=False,
        ),
        sa.Column("user_id", sa.Integer(), nullable=True),
        sa.Column("role", chat_role_enum, nullable=False),
        sa.Column("content", sa.Text(), nullable=False),
        sa.Column(
            "created_at",
            sa.DateTime(timezone=True),
            server_default=sa.text("NOW()"),
            nullable=False,
        ),
        sa.ForeignKeyConstraint(
            ["project_id"],
            ["projects.id"],
            ondelete="CASCADE",
        ),
        sa.ForeignKeyConstraint(
            ["user_id"],
            ["users.id"],
            ondelete="SET NULL",
        ),
    )
    op.create_index("ix_chat_messages_project_id", "chat_messages", ["project_id"])
    op.create_index("ix_chat_messages_user_id", "chat_messages", ["user_id"])
    op.create_index("ix_chat_messages_created_at", "chat_messages", ["created_at"])


def downgrade() -> None:
    # Drop tables in reverse order
    op.drop_index("ix_chat_messages_created_at", table_name="chat_messages")
    op.drop_index("ix_chat_messages_user_id", table_name="chat_messages")
    op.drop_index("ix_chat_messages_project_id", table_name="chat_messages")
    op.drop_table("chat_messages")

    op.drop_index("ix_podium_accesses_expires_at", table_name="podium_accesses")
    op.drop_index("ix_podium_accesses_project_id", table_name="podium_accesses")
    op.drop_table("podium_accesses")

    op.drop_index("ix_variables_order", table_name="variables")
    op.drop_index("ix_variables_project_id", table_name="variables")
    op.drop_table("variables")

    op.drop_index("ix_documents_uploaded_at", table_name="documents")
    op.drop_index("ix_documents_uploaded_by", table_name="documents")
    op.drop_index("ix_documents_project_id", table_name="documents")
    op.drop_table("documents")

    op.drop_index("ix_projects_created_by", table_name="projects")
    op.drop_index("ix_projects_client_id", table_name="projects")
    op.drop_index("ix_projects_tenant_id", table_name="projects")
    op.drop_table("projects")

    # Drop enums
    chat_role_enum = sa.Enum(name="chat_role")
    chat_role_enum.drop(op.get_bind(), checkfirst=True)

    variable_type_enum = sa.Enum(name="variable_type")
    variable_type_enum.drop(op.get_bind(), checkfirst=True)

    embedding_status_enum = sa.Enum(name="embedding_status")
    embedding_status_enum.drop(op.get_bind(), checkfirst=True)

    project_status_enum = sa.Enum(name="project_status")
    project_status_enum.drop(op.get_bind(), checkfirst=True)




