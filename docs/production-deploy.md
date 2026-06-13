# Hibachi Catering Production Deploy

This checklist assumes a standard Laravel server where the web root points to
the repository `public/` directory.

## Requirements

- PHP 8.2 or newer with common Laravel extensions enabled.
- Composer 2.
- Node.js 20.19+ or 22.12+ for Vite 7 builds.
- A production database with backups enabled.
- A queue worker process manager, such as Supervisor.
- HTTPS configured at the web server or load balancer.

## Required Production Environment

Create the production `.env` on the server from `.env.example` and set real
values for:

```dotenv
APP_NAME="HIBACHI CATERING"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-production-domain.com
APP_TIMEZONE=America/Los_Angeles

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hibachi_catering
DB_USERNAME=hibachi_user
DB_PASSWORD=change_me

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=change_me
MAIL_PASSWORD=change_me
MAIL_EHLO_DOMAIN=your-production-domain.com
MAIL_FROM_ADDRESS=noreply@your-production-domain.com
MAIL_FROM_NAME="${APP_NAME}"
ADMIN_NOTIFICATION_EMAIL=ops@your-production-domain.com

GOOGLE_MAPS_KEY=change_me
STRIPE_KEY=change_me
STRIPE_SECRET=change_me
STRIPE_PAY_DEBUG=false
```

Generate `APP_KEY` only once for a new production install:

```bash
php artisan key:generate --force
```

Do not regenerate `APP_KEY` after the site has real encrypted data or active
sessions.

## First Server Install

```bash
cd /var/www
git clone <repo-url> hibachi-catering
cd hibachi-catering

composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build

cp .env.example .env
php artisan key:generate --force

php artisan migrate --force
php artisan storage:link

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Make sure the web server user can write to:

```bash
storage
bootstrap/cache
```

## Normal Deploy

Run these commands from the production checkout.

```bash
php artisan down --render="errors::503" || true

git fetch --all --prune
git checkout main
git pull --ff-only origin main

composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build

php artisan migrate --force
php artisan storage:link

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan queue:restart
php artisan up
```

If the host builds assets in CI instead of on the server, replace `npm ci` and
`npm run build` with the artifact upload step that provides `public/build`.

## Queue Worker

Because this app uses `QUEUE_CONNECTION=database`, production needs a persistent
queue worker. Example Supervisor program:

```ini
[program:hibachi-catering-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hibachi-catering/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/hibachi-catering/storage/logs/worker.log
stopwaitsecs=3600
```

Reload Supervisor after adding or changing the worker:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart hibachi-catering-worker:*
```

## Scheduler

Add Laravel's scheduler to cron:

```cron
* * * * * cd /var/www/hibachi-catering && php artisan schedule:run >> /dev/null 2>&1
```

## Post-Deploy Checks

Run:

```bash
php artisan app:production-readiness --strict
php artisan about
php artisan migrate:status
php artisan queue:failed
```

Then verify in the browser:

- Admin login works.
- Reservations page loads.
- Invoice create, preview, print, and download use the same invoice design.
- Invoice tax uses the event city custom tax rate first, otherwise default tax.
- Staff dashboard loads assigned events.
- Team profile photos and document views load through `storage`.
- Password reset email can be requested.

## Rollback

If the deploy fails before migrations:

```bash
git checkout <previous-good-commit>
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up
```

If migrations already ran, do not blindly run `migrate:rollback` on production.
First confirm whether the migration changed or removed production data. Prefer
restoring from a tested database backup when schema changes are not safely
reversible.

## Local Release Checks Before Deploy

Run these before pushing or deploying:

```bash
composer validate --no-check-publish
php artisan test
npm run build
php artisan app:production-readiness
git diff --check
git status --short
```

Expected result: tests and build pass, whitespace check is clean, and the only
local files left are intentional changes to commit.

## Laravel Cloud Deploy

Use this section when deploying from GitHub to Laravel Cloud instead of a
traditional SSH server.

### 1. Create The Application

In Laravel Cloud:

- Create a new application from GitHub.
- Select repository `Chingon84/hibachi-catering`.
- Select branch `main`.
- Use PHP 8.2, 8.3, or 8.4.
- Use Node.js 20.19+ or 22.12+.

### 2. Attach Resources

Add these resources to the production environment:

- Database: MySQL.
- Object Storage: create an S3-compatible Laravel Object Storage bucket.
- Queue: create a Managed Queue and mark it as the default queue.

For the Object Storage bucket, use disk name `uploads`. If the bucket is
private, set `UPLOADS_TEMPORARY_URLS=true` so profile photos and uploaded images
can render through temporary URLs. If the bucket is public, use
`UPLOADS_TEMPORARY_URLS=false`.

Laravel Cloud injects database and object-storage credentials automatically when
resources are attached to the environment. Avoid overriding those generated
database or AWS variables unless you intentionally use external services.

### 3. Environment Variables

Set these manually in Laravel Cloud environment variables:

```dotenv
APP_NAME="HIBACHI CATERING"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-laravel-cloud-or-custom-domain.com
APP_TIMEZONE=America/Los_Angeles

LOG_LEVEL=info

SESSION_DRIVER=database
CACHE_STORE=database

# Use this after adding a Laravel Cloud Managed Queue.
QUEUE_CONNECTION=cloud

# Must match the Laravel Cloud Object Storage disk name.
UPLOADS_DISK=uploads
UPLOADS_TEMPORARY_URLS=false

MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=change_me
MAIL_PASSWORD=change_me
MAIL_EHLO_DOMAIN=your-domain.com
MAIL_FROM_ADDRESS=info@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
ADMIN_NOTIFICATION_EMAIL=info@your-domain.com

GOOGLE_MAPS_KEY=change_me
STRIPE_KEY=change_me
STRIPE_SECRET=change_me
STRIPE_PAY_DEBUG=false
```

Generate `APP_KEY` from Laravel Cloud's environment variables UI or run this
once in the environment command runner:

```bash
php artisan key:generate --show
```

Copy the generated value into `APP_KEY`, then redeploy.

### 4. Build Commands

Use these in Laravel Cloud Deployments > Build Commands:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan optimize
```

Laravel Cloud documentation recommends running optimization during build, not
during deploy.

### 5. Deploy Commands

Use these in Laravel Cloud Deployments > Deploy Commands:

```bash
php artisan migrate --force
php artisan app:production-readiness --strict
```

Do not add these commands in Laravel Cloud deploy commands:

```bash
php artisan queue:restart
php artisan optimize:clear
php artisan storage:link
```

Laravel Cloud automatically restarts queues after deploys, deploy filesystem
changes are not persisted, and persistent uploads should use Object Storage.

### 6. After First Deploy

In Laravel Cloud's Commands tab, run:

```bash
php artisan app:production-readiness --strict
php artisan migrate:status
php artisan queue:failed
```

Then verify:

- Admin login.
- Reservations table.
- Invoice create, preview, print, and download.
- Employee profile photo upload/delete.
- Team document upload/view/download.
- Client photo upload/view.
- Password reset email.
- Staff dashboard assigned events.
