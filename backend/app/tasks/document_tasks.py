"""Background tasks for document processing."""

from __future__ import annotations

import asyncio
import logging
from typing import Callable, Optional

from sqlalchemy.orm import Session

from app.models.document import Document, DocumentStatus
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

        doc.status = DocumentStatus.PROCESSING
        db.add(doc)
        db.commit()

        text = await extract_text(doc.file_path)
        if text:
            doc.status = DocumentStatus.READY
            doc.extracted_text = text
        else:
            doc.status = DocumentStatus.ERROR

        db.add(doc)
        db.commit()
    except Exception as exc:
        if db is not None:
            doc = db.query(Document).filter(Document.id == document_id).first()
            if doc:
                doc.status = DocumentStatus.ERROR
                db.add(doc)
                db.commit()
        logger.exception("Document processing failed for id=%s: %s", document_id, exc)
    finally:
        if db is not None:
            db.close()




