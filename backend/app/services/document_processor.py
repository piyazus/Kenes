"""Utilities for extracting and chunking document text."""

from __future__ import annotations

import asyncio
import os
from pathlib import Path
from typing import List

import PyPDF2
from docx import Document as DocxDocument


async def extract_text(file_path: str) -> str:
    """
    Extract text from a document based on its extension.

    Supports PDF, DOCX, TXT. Returns empty string on errors.
    """
    path = Path(file_path)
    if not path.exists() or not path.is_file():
        return ""

    suffix = path.suffix.lower()
    try:
        if suffix == ".pdf":
            return await asyncio.to_thread(_extract_pdf, path)
        if suffix in {".docx", ".doc"}:
            return await asyncio.to_thread(_extract_docx, path)
        # Fallback to plain text
        return await asyncio.to_thread(path.read_text, encoding="utf-8", errors="ignore")
    except Exception:
        return ""


async def chunk_text(text: str, chunk_size: int = 2000) -> List[str]:
    """Split text into chunks of roughly `chunk_size` characters."""
    if not text:
        return []
    chunks: List[str] = []
    start = 0
    while start < len(text):
        end = start + chunk_size
        chunks.append(text[start:end])
        start = end
    return chunks


def _extract_pdf(path: Path) -> str:
    text_parts: List[str] = []
    with path.open("rb") as f:
        reader = PyPDF2.PdfReader(f)
        for page in reader.pages:
            try:
                text_parts.append(page.extract_text() or "")
            except Exception:
                continue
    return "\n".join(text_parts)


def _extract_docx(path: Path) -> str:
    doc = DocxDocument(path)
    return "\n".join(p.text for p in doc.paragraphs)




