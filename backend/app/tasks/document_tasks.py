"""Background tasks for document processing."""

from __future__ import annotations

import asyncio
import logging
from typing import Callable, Optional

from sqlalchemy.orm import Session

from app.models.document import Document, EmbeddingStatus
from app.services.document_processor import extract_text


logger = logging.getLogger(__name__)


async def process_document(
    db_factory: Callable[[], Session],
    document_id: str,
) -> None:
    """
    Process a document: extract text and update embedding_status.

    This function is designed to be run in background (asyncio).
    """
    db: Optional[Session] = None
    try:
        db = db_factory()
        doc: Document | None = db.query(Document).filter(Document.id == document_id).first()
        if not doc:
            return

        doc.embedding_status = EmbeddingStatus.PROCESSING
        db.add(doc)
        db.commit()

        text = await extract_text(doc.file_path)
        if text:
            doc.embedding_status = EmbeddingStatus.COMPLETED
            doc.error_message = None
        else:
            doc.embedding_status = EmbeddingStatus.FAILED
            doc.error_message = "Failed to extract text"

        db.add(doc)
        db.commit()
    except Exception as exc:
        if db is not None:
            doc = db.query(Document).filter(Document.id == document_id).first()
            if doc:
                doc.embedding_status = EmbeddingStatus.FAILED
                doc.error_message = str(exc)
                db.add(doc)
                db.commit()
        logger.exception("Document processing failed for id=%s: %s", document_id, exc)
    finally:
        if db is not None:
            db.close()




