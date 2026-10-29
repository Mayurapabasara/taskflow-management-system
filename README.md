# TaskFlow

A Mini Project & Task Management System built for a Software Developer Internship assessment. TaskFlow organizes projects and their tasks in a responsive dashboard, with Livewire updates that do not require full page reloads.

The Laravel application lives in **`backend/`**. Run application commands from that directory.

## Technology stack

- Laravel 13 and PHP 8.3+
- Livewire 4 and Blade templates
- Tailwind CSS 4 and Vite 8
- MySQL and Eloquent ORM
- PHPUnit 12 and Laravel Pint

Composer and npm lockfiles are included for reproducible dependency installation. Existing Spatie Permission and Laravel development tools are retained; the assessment does not implement roles or authentication.

## Features

- Create, list, edit, and delete projects with validated names and descriptions.
- Display total/completed task counts and project progress.
- Select a project to create, list, rename, or delete its tasks.
- Toggle completion immediately, with strikethrough styling for completed tasks.
- Reject project deletion when any task is incomplete, preserving the project and all its tasks.
- Delete empty projects or projects containing only completed tasks, with foreign-key cascade cleanup.
- Use transactions and consistent parent-first locking for task writes and project deletion.
- Provide responsive cards, accessible labels, validation messages, empty states, loading feedback, and deletion confirmations.

This is a shared assessment dashboard without login or per-user ownership.

## Requirements

- PHP 8.3+ with Composer-required extensions, plus `pdo_mysql` for the application and `pdo_sqlite` for tests.
- Composer 2.x.
- Node.js 22.23.4 (the verified environment) and npm 10.x.
- A running MySQL server and an account permitted to create/alter tables and read/write the application database.

Verified development environment: Linux, PHP 8.3.6, Composer 2.7.1, Node.js 22.23.4, npm 10.9.9. Check PHP dependencies with `composer check-platform-reqs`.

## Installation

Clone or extract the repository, then enter the application directory:

```bash
cd TaskFlow/backend
composer install
npm ci --ignore-scripts
```

On a fresh checkout only, create the local environment file and application key:

```bash
cp .env.example .env
php artisan key:generate
```

Preserve an existing `.env` and application key when updating a local installation. Never commit credentials or `.env`.

## MySQL configuration

Create a database using an administrative MySQL session:

```sql
CREATE DATABASE taskwork CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Use your own MySQL account and credentials in `backend/.env`:

```dotenv
APP_NAME=TaskFlow
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taskwork
DB_USERNAME=your_mysql_user
DB_PASSWORD=your_mysql_password
```

The credential values above are placeholders. Quote values containing spaces or environment-file special characters. Sessions, cache, and queues use database drivers by default; their tables are included in the existing migrations.

After changing configuration, verify the connection and apply migrations:

```bash
php artisan config:clear
php artisan migrate:status
php artisan migrate
php artisan migrate:status
```

On an empty database, the first status check may report that the migration table does not yet exist; `migrate` creates it. No seed data is required. Do not use `migrate:fresh` on a database containing data you want to keep.

If MySQL returns **1045 (access denied)**, correct the username/password and account host permissions. Error **2002** generally requires checking the running server, host, port, or socket. Do not change the application to SQLite to bypass a MySQL configuration problem.

## Run locally

In one terminal, from `backend/`:

```bash
php artisan serve
```

In a second terminal, also from `backend/`:

```bash
npm run dev
```

Open **http://localhost:8000**. Alternatively, build assets once and run Laravel without the Vite development server:

```bash
npm run build
php artisan serve
```

The assessed operations are synchronous; no queue worker is required for project/task management. Keep Laravel's `storage/` and `bootstrap/cache/` writable by the application process.

## Using TaskFlow

1. Create a project with a name and description.
2. Select **Tasks** on its card to open the project task panel.
3. Add tasks, edit their titles, or delete them.
4. Check/uncheck tasks to change completion and refresh progress.
5. Try deleting a project with an incomplete task: a clear error must appear and its data must remain intact.
6. Complete every task, then delete the project and confirm removal.

Validation limits: project name and task title are required and limited to 255 characters; project description is required and limited to 10,000 characters. Leading/trailing whitespace is trimmed.

## Database and implementation

| Table | Main columns |
| --- | --- |
| `projects` | `id`, `name`, `description`, timestamps |
| `tasks` | `id`, required `project_id`, `title`, `is_completed` (default false), timestamps |

`Project::tasks()` is a `hasMany` relationship; `Task::project()` is `belongsTo`. The required task foreign key cascades on project deletion. The reusable deletion action enforces the incomplete-task restriction before allowing that cascade.

Key files:

- `backend/app/Livewire/Projects.php`: project/task interactions and reactive rendering.
- `backend/app/Actions/DeleteProject.php`: transactional project deletion rule.
- `backend/app/Models/`: Eloquent models and relationships.
- `backend/database/migrations/`: schema and constraints.
- `backend/resources/views/livewire/projects.blade.php`: dashboard and task panel.
- `backend/tests/Feature/`: behavior, validation, relationships, and deletion tests.

## Tests and quality checks

From `backend/`:

```bash
php vendor/bin/phpunit --do-not-cache-result
vendor/bin/pint --test
npm run build
composer validate --no-check-publish
composer check-platform-reqs
```

Run focused tests with:

```bash
php vendor/bin/phpunit --filter=ProjectsTest
php vendor/bin/phpunit --filter=TasksTest
php vendor/bin/phpunit --filter=DeleteProjectTest
php vendor/bin/phpunit --filter=ProjectTaskRelationshipsTest
```

The suite uses an isolated **in-memory SQLite** database configured in `phpunit.xml`, not the local MySQL application database. It covers CRUD, validation boundaries, task isolation, locked Livewire state, escaping, completion in both directions, reactive counts, relationships, timestamps, foreign keys, and all three project deletion scenarios.

Latest verified result: **50 tests passed, 269 assertions**. Pint, Composer validation/platform checks, and the production frontend build passed. The build emits a non-blocking warning about the optional `fontaine` font-fallback package.

## Screenshots

Screenshots have not yet been captured. Before submission, add real application screenshots under `docs/screenshots/` and link them here. Suggested captures:

| Screenshot | Suggested file |
| --- | --- |
| Project dashboard and progress | `docs/screenshots/dashboard.png` |
| Selected project with pending and completed tasks | `docs/screenshots/tasks.png` |
| Incomplete-task deletion error | `docs/screenshots/deletion-restriction.png` |
| Mobile layout | `docs/screenshots/mobile.png` |

Avoid including credentials, debug output, or personal data in screenshots.

## Verification limitations and submission notes

- The latest local MySQL connection check returned **1045 (access denied)**. Correct local credentials and verify migrations/schema before demonstrating the application.
- SQLite tests do not verify MySQL row-locking or concurrent-request behavior.
- Browser interaction, mobile appearance, and screen-reader behavior still need manual verification.
- `.env`, `vendor/`, `node_modules/`, compiled assets, and runtime output are ignored by Git. Commit both dependency lockfiles.
- Authentication is outside the assessment scope. For deployment, configure HTTPS, production credentials, `APP_ENV=production`, and `APP_DEBUG=false` before exposing the application.
