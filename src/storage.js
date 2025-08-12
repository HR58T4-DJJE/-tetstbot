import path from 'node:path';
import fs from 'node:fs/promises';
import crypto from 'node:crypto';

export const ROOT = process.cwd();
export const UPLOADS_DIR = path.join(ROOT, 'uploads');
export const ORIGINAL_DIR = path.join(UPLOADS_DIR, 'original');
export const THUMBS_DIR = path.join(UPLOADS_DIR, 'thumbs');

export async function ensureDirs() {
  for (const dir of [UPLOADS_DIR, ORIGINAL_DIR, THUMBS_DIR]) {
    try {
      await fs.mkdir(dir, { recursive: true });
    } catch {}
  }
}

export function generateStoredFilename(originalFilename) {
  const ext = path.extname(originalFilename).toLowerCase();
  const base = crypto.randomBytes(16).toString('hex');
  return `${base}${ext}`;
}

export function originalPath(storedFilename) {
  return path.join(ORIGINAL_DIR, storedFilename);
}

export function thumbPath(storedFilename) {
  return path.join(THUMBS_DIR, storedFilename);
}

export async function removeFilesIfExist(storedFilename) {
  const files = [originalPath(storedFilename), thumbPath(storedFilename)];
  await Promise.all(files.map(async (file) => {
    try {
      await fs.unlink(file);
    } catch {}
  }));
}