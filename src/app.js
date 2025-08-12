import path from 'node:path';
import express from 'express';
import dotenv from 'dotenv';
import helmet from 'helmet';
import morgan from 'morgan';
import rateLimit from 'express-rate-limit';
import multer from 'multer';
import sharp from 'sharp';
import expressLayouts from 'express-ejs-layouts';

import { initSchema, query } from './db.js';
import { generateStoredFilename, originalPath, thumbPath, ensureDirs } from './storage.js';

dotenv.config();

const app = express();

// Security and utils
app.use(helmet());
app.use(morgan('dev'));
const limiter = rateLimit({ windowMs: 60_000, max: 200 });
app.use(limiter);

// Views
app.set('view engine', 'ejs');
app.set('views', path.join(process.cwd(), 'views'));
app.use(expressLayouts);
app.set('layout', 'layout');

// Static assets
app.use('/assets', express.static(path.join(process.cwd(), 'public', 'assets')));
app.use('/uploads', express.static(path.join(process.cwd(), 'uploads')));

// Parsers
app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// Multer storage in memory to allow sharp processing
const upload = multer({
  storage: multer.memoryStorage(),
  limits: { fileSize: 15 * 1024 * 1024 },
  fileFilter: (req, file, cb) => {
    if (/^image\//.test(file.mimetype)) cb(null, true);
    else cb(new Error('Only image uploads are allowed'));
  }
});

// Home page - list recent photos
app.get('/', async (req, res, next) => {
  try {
    const photos = await query('SELECT id, stored_filename, title, created_at FROM photos ORDER BY id DESC LIMIT 60');
    res.render('index', { photos });
  } catch (err) {
    next(err);
  }
});

// Upload form
app.get('/upload', (req, res) => {
  res.render('upload');
});

// Handle upload
app.post('/upload', upload.single('photo'), async (req, res, next) => {
  try {
    if (!req.file) {
      return res.status(400).send('No file uploaded');
    }
    const image = sharp(req.file.buffer);
    const metadata = await image.metadata();

    const storedFilename = generateStoredFilename(req.file.originalname);

    // Save original
    await image.toFile(originalPath(storedFilename));

    // Create thumbnail
    await image
      .resize({ width: 480, height: 480, fit: 'inside', withoutEnlargement: true })
      .jpeg({ quality: 80 })
      .toFile(thumbPath(storedFilename));

    const { title = null, description = null } = req.body ?? {};

    const result = await query(
      `INSERT INTO photos (original_filename, stored_filename, mime_type, file_size, width, height, title, description)
       VALUES (:original_filename, :stored_filename, :mime_type, :file_size, :width, :height, :title, :description)`,
      {
        original_filename: req.file.originalname,
        stored_filename: storedFilename,
        mime_type: req.file.mimetype,
        file_size: req.file.size,
        width: metadata.width ?? null,
        height: metadata.height ?? null,
        title,
        description,
      }
    );

    res.redirect(`/photo/${result.insertId}`);
  } catch (err) {
    next(err);
  }
});

// View photo page
app.get('/photo/:id', async (req, res, next) => {
  try {
    const [photo] = await query('SELECT * FROM photos WHERE id = :id', { id: req.params.id });
    if (!photo) return res.status(404).send('Not found');
    res.render('photo', { photo });
  } catch (err) {
    next(err);
  }
});

// Download original file
app.get('/download/:id', async (req, res, next) => {
  try {
    const [photo] = await query('SELECT * FROM photos WHERE id = :id', { id: req.params.id });
    if (!photo) return res.status(404).send('Not found');
    const filePath = originalPath(photo.stored_filename);
    res.download(filePath, photo.original_filename);
  } catch (err) {
    next(err);
  }
});

// JSON API list
app.get('/api/photos', async (req, res, next) => {
  try {
    const photos = await query('SELECT id, stored_filename, title, created_at FROM photos ORDER BY id DESC LIMIT 200');
    res.json({ photos });
  } catch (err) {
    next(err);
  }
});

// Error handler
app.use((err, req, res, next) => {
  console.error(err);
  res.status(500).send('Internal Server Error');
});

export async function start() {
  await ensureDirs();
  await initSchema();
  const port = Number(process.env.PORT || 3000);
  return new Promise((resolve) => {
    const server = app.listen(port, () => {
      console.log(`Server running on http://localhost:${port}`);
      resolve(server);
    });
  });
}

export default app;