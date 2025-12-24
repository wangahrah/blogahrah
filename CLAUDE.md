# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

Start the PHP development server:
```bash
php -S localhost:8000
```

Access points:
- Home (terminal): http://localhost:8000/
- Blog reader: http://localhost:8000/blogahrah/
- Blog editor: http://localhost:8000/write/

No build step required - vanilla JavaScript and PHP run directly.

## Architecture

This is a personal blog/portfolio with a terminal-themed interface. It uses vanilla JavaScript (no framework) on the frontend and PHP for the backend API.

### Frontend

- **Terminal Emulator** (`js/wangahrah.js`) - Custom class handling text-adventure style navigation with commands like BLOG, LINKEDIN, HELP, directional movement
- **Blog Reader** (`blogahrah/index.html`) - Fetches posts via API, renders Markdown with marked.js, syntax highlighting with Prism.js
- **Blog Editor** (`write/index.php`) - Password-protected, uses EasyMDE for Markdown editing, supports drag-and-drop media uploads

### Backend API (`/api/`)

- `auth.php` - Session-based authentication (POST login, GET status, DELETE logout)
- `posts.php` - Blog CRUD (GET public list, POST authenticated create)
- `upload.php` - Media upload with MIME validation (images/videos, 50MB max)
- `config.php` - Password hash, paths, allowed MIME types

### Data Storage

Blog posts are Markdown files in `/blogs/` with YAML frontmatter:
```yaml
---
title: Post Title
date: 2025-12-24
---
Content here
```

Filename format: `YYYY-MM-DD-slug.md`

Uploaded media goes to `/media/`.

## Key Patterns

- No npm/composer - external libraries loaded via CDN (unpkg.com)
- File-based persistence, no database
- Password hashing uses bcrypt via `password_hash()`/`password_verify()`
- Posts API returns JSON with parsed frontmatter metadata
