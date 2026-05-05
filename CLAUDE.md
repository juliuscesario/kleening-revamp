# CLAUDE.md — Kleening Revamp

> At the start of every session, read `.context/architecture.md`, `.context/stack.md`, and `.context/memory.md` before doing anything.

## Project Overview

Internal cleaning services business management platform for **Kleening.id** — a professional home cleaning service operating in Jakarta (Jabodetabek), Serang, and Malang. Manages the full operational lifecycle: customer ordering, scheduling, field staff work proof, invoicing, payment tracking, and analytics reporting.

Services: Hydrovacuum (HV), Premium Wash (CC), General Cleaning (GC), Deep Cleaning (DC), Car Interior Detailing (CID), Poles, and Survey.

## Tech Stack

| Layer | Tech |
|-------|------|
| Backend | Laravel 12, PHP 8.2+, MySQL |
| Frontend | Blade, Tailwind CSS 3.1.0, Vite 7.0.4 |
| UI Framework | Tabler UI (@tabler/core 1.4.0) |
| JS | Alpine.js 3.4.2, jQuery 3.7.1, DataTables.net 2.3.4, SweetAlert2 11.23.0, ApexCharts 5.3.5, Select2 4.1.0 |
| API Auth | Laravel Sanctum 4.2 |
| PDF | DOMPDF (barryvdh/laravel-dompdf 3.1) |
| DataTables | yajra/laravel-datatables-oracle 12.4 |
| Image | spatie/image 3.8 |
| API Docs | Scribe 5.9 |
| Code Style | Laravel Pint (PSR-12) |

## Context System

Read these files before starting any task:

| File | Purpose |
|------|---------|
| `.context/architecture.md` | System architecture, models, relationships, request lifecycle |
| `.context/stack.md` | Full tech stack with versions and config |
| `.context/conventions.md` | Coding standards, naming, file organization |
| `.context/memory.md` | Decisions, patterns, gotchas, external deps |
| `.context/progress.md` | Current sprint, backlog, changelog |

Full archived versions (when compressed): `.context/archive/`

## Rules

### Session Start
1. Read `.context/architecture.md`, `.context/stack.md`, and `.context/memory.md`
2. Check `.context/progress.md` for current sprint context
3. Only then begin the task

### After Every Task
1. Update `.context/memory.md` — add any new decisions, patterns, or gotchas discovered
2. Update `.context/progress.md` — log task completion with date in Changelog, update sprint status
3. If any `.context/` file exceeds **200 lines**, compress:
   - Archive to `.context/archive/[filename]-[date].md`
   - Rewrite main file as a concise quick-reference (under 200 lines)
   - Add at top: `<!-- Full version: .context/archive/[filename]-[date].md -->`
4. After every ask_qwen implementation task, always call review_code on the modified files.

### Before Making Changes
- Check `.context/memory.md` for known gotchas in the area you're touching
- Review `.context/conventions.md` for established patterns
- When unsure about architecture, read `.context/architecture.md` or the full archive

### Code Standards
- Follow existing codebase patterns — consistency over preference
- Laravel conventions: Resource Controllers, Form Requests, Policies
- Blade components over raw HTML
- Tailwind utility classes only — no custom CSS unless unavoidable
- All DB changes via migrations — never touch the database directly
- Type hints on all PHP method signatures
- Use Pint for formatting (PSR-12)

### Commit Message Format
```
feat: [description]     — new features
fix: [description]      — bug fixes
refactor: [description] — code restructuring
style: [description]    — UI/styling changes
chore: [description]    — maintenance tasks
```
