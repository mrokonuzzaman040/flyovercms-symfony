# FlyoverCMS — Symfony Edition

Multi-tenant CRM/CMS for immigration consultancies. Built with Symfony 7.4 LTS, Inertia.js, and Vue 3. Rebuilt from Laravel 12 as a production-grade multi-tenant SaaS.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2+, Symfony 7.4 LTS |
| ORM | Doctrine ORM 3.x + Migrations |
| Frontend | Vue 3, Inertia.js, Vite 8 |
| Styling | Tailwind CSS v4 |
| UI Components | shadcn/vue (reka-ui) |
| Auth | Symfony Security (bcrypt, session) |
| Queue | Symfony Messenger (sync dev / Redis prod) |
| Cache/Sessions | Filesystem dev / Redis prod |
| Database | MySQL 8.0+ |

## Features

- Multi-tenant architecture (subdomain or `X-Tenant-Slug` header)
- Lead management — full CRUD, status pipeline, follow-ups, call history, transfers
- Department modules — Visa, Transfers
- Marketing — Campaigns, Templates, Segments, SMS, Email, WhatsApp, Drip, Automated, Multi-channel
- Document management — upload, download, preview per lead
- Messaging — conversations and messages
- User/Role/Permission management
- Branch and organization management
- Notifications, Activity log, Reports
- Admin settings (modules, security, integrations)

---

## Local Development Setup

### Requirements

- PHP 8.2+ with extensions: `pdo_mysql`, `intl`, `ctype`, `iconv`, `openssl`, `mbstring`
- Composer 2.x
- Node.js 20+ and npm 10+
- MySQL 8.0+

### 1. Clone and install dependencies

```bash
git clone <repo-url> flyovercms-symfony
cd flyovercms-symfony

composer install
npm install
```

### 2. Configure environment

```bash
cp .env .env.local
```

Edit `.env.local`:

```dotenv
APP_ENV=dev
APP_SECRET=generate_a_32_char_random_string_here

DATABASE_URL="mysql://root:yourpassword@127.0.0.1:3306/flyovercms?serverVersion=8.0&charset=utf8mb4"
```

Generate a secret:

```bash
php bin/console secrets:generate-keys   # or use: openssl rand -hex 16
```

### 3. Create database and run migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

### 4. Build frontend assets

```bash
npm run build
```

Or for hot-reload development (run in a second terminal):

```bash
npm run dev
```

### 5. Start the dev server

```bash
php -S 127.0.0.1:8000 -t public/
```

App runs at `http://127.0.0.1:8000`

### 6. Seed a tenant and admin user

Run directly in MySQL or create a Symfony command. Minimum required data:

```sql
-- Tenant
INSERT INTO tenants (name, slug, is_active, created_at, updated_at)
VALUES ('Demo Company', 'demo', 1, NOW(), NOW());

-- Role
INSERT INTO roles (name, slug, tenant_id, created_at, updated_at)
VALUES ('Admin', 'admin', 1, NOW(), NOW());

-- User (password = 'password')
INSERT INTO users (name, email, password, tenant_id, is_active, created_at, updated_at)
VALUES ('Admin User', 'admin@demo.test',
  '$2y$12$mFxd20YUi18QFtuAfcTp4ON/fyih42VqwbS1G3uI5TE2Lf2yjTlAG', 1, 1, NOW(), NOW());

-- Assign role to user
INSERT INTO role_user (role_id, user_id) VALUES (1, 1);
```

Login: `admin@demo.test` / `password`

---

## Deployment to cPanel (Shared Hosting)

### Server Requirements

- PHP 8.2+ (select via cPanel PHP Selector)
- Required PHP extensions: `pdo_mysql`, `intl`, `ctype`, `iconv`, `mbstring`, `openssl`, `opcache`
- MySQL 8.0+
- SSH access (recommended) or File Manager
- Composer available via SSH (`composer` or `/usr/local/bin/composer`)
- Node.js 20+ (for building assets — build locally if not available on server)

### Directory Structure on cPanel

cPanel's document root is typically `public_html/`. Symfony's public folder must be the document root. **Do not put the entire project inside `public_html/`.**

Recommended layout on the server:

```
/home/username/
├── flyovercms/          ← project root (outside public_html)
│   ├── src/
│   ├── config/
│   ├── var/
│   ├── vendor/
│   ├── public/          ← symlink or copy target
│   └── ...
└── public_html/         ← must point to flyovercms/public/
```

**Option A — Subdomain pointing to `public/`**: Create a subdomain in cPanel and set its document root to `/home/username/flyovercms/public`.

**Option B — Main domain**: In cPanel → Domains → set document root to `flyovercms/public`. Some hosts don't allow changing the main domain root — in that case, copy/symlink `public/` contents into `public_html/` and update paths.

### Step-by-Step Deployment

#### 1. Upload project files

Via SSH (Git — recommended):

```bash
ssh user@yourserver.com
cd /home/username
git clone <repo-url> flyovercms
cd flyovercms
```

Via FTP/File Manager: upload all files except `vendor/`, `node_modules/`, `var/`, `.env.local`.

#### 2. Set PHP version in cPanel

cPanel → PHP Selector → select PHP 8.2 or 8.3. Enable extensions:

```
pdo_mysql, intl, mbstring, ctype, iconv, openssl, opcache, redis (optional)
```

#### 3. Upload php.ini overrides

Copy `deploy/php.ini` to your home directory or `public_html/`:

```bash
cp deploy/php.ini ~/public_html/php.ini
# Edit error_log path to match your server:
# error_log=/home/yourusername/logs/php_errors.log
```

#### 4. Configure environment

```bash
cp .env .env.local
```

Edit `.env.local` for production:

```dotenv
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=your_32_char_random_secret_here

DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0&charset=utf8mb4"

# Mailer (optional)
MAILER_DSN=smtp://user:pass@smtp.yourhost.com:587

# Redis (optional — falls back to filesystem)
REDIS_URL=redis://127.0.0.1:6379
```

Create the MySQL database via cPanel → MySQL Databases. Note the database name, username, and password.

#### 5. Install PHP dependencies (production)

```bash
cd /home/username/flyovercms
composer install --no-dev --optimize-autoloader --no-interaction
```

#### 6. Build frontend assets

**Recommended: build locally, upload `public/build/`**

```bash
# On your local machine:
npm ci
npm run build
# Upload public/build/ to the server
```

If Node.js is available on the server:

```bash
npm ci
npm run build
```

#### 7. Run database migrations

```bash
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

#### 8. Clear and warm Symfony cache

```bash
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug
```

#### 9. Set file permissions

```bash
chmod -R 775 var/
chmod -R 775 public/build/
chmod -R 775 public/uploads/
```

#### 10. Verify .htaccess is in place

`public/.htaccess` is already included in the repo and handles Symfony's front controller routing. cPanel/Apache must have `mod_rewrite` enabled (it almost always does on shared hosts).

If you get 403/404 on all pages, verify `AllowOverride All` is set for your document root (contact host support if needed).

#### One-command deploy (after initial setup)

Use the included script for subsequent deploys:

```bash
cd /home/username/flyovercms
git pull origin main
bash deploy/deploy.sh
```

### Automated deploy (via cPanel Git Version Control)

1. cPanel → Git Version Control → Create Repository → point to `/home/username/flyovercms`
2. Add a `.cpanel.yml` file to the repo root:

```yaml
---
deployment:
  tasks:
    - export COMPOSER_HOME=$HOME/.composer && /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction
    - /usr/local/bin/php bin/console cache:clear --env=prod --no-debug
    - /usr/local/bin/php bin/console cache:warmup --env=prod --no-debug
    - /usr/local/bin/php bin/console doctrine:migrations:migrate --no-interaction --env=prod
    - chmod -R 775 var/ public/build/ public/uploads/
```

3. Push to the connected branch → cPanel auto-deploys.

> **Note**: Build frontend assets locally before pushing. cPanel shared hosting usually doesn't have Node.js in `$PATH` for deployment hooks.

### Troubleshooting

| Symptom | Fix |
|---|---|
| 500 on all pages | Check `var/log/prod.log`. Usually missing `.env.local` or wrong DB credentials |
| 404 on all routes | `.htaccess` not applied — verify `mod_rewrite` enabled and `AllowOverride All` |
| "No tenant set" error | Add `X-Tenant-Slug` header or access via subdomain |
| Blank page after login | Check `APP_SECRET` is set and consistent across deploys |
| Assets not loading | Run `npm run build` and upload `public/build/`. Check `vite_entry_link_tags` in Twig |
| Permissions denied on `var/` | Run `chmod -R 775 var/` |
| Composer memory limit | Add `php -d memory_limit=-1 /usr/local/bin/composer install ...` |

### Multi-tenant Setup

Each tenant is identified by subdomain (e.g. `company1.yourdomain.com`). To add a tenant:

```sql
INSERT INTO tenants (name, slug, custom_domain, is_active, created_at, updated_at)
VALUES ('Client Company', 'client1', NULL, 1, NOW(), NOW());
```

Point `client1.yourdomain.com` to the same document root. The app resolves the tenant from the subdomain automatically.

For single-domain installs (localhost, staging), the app auto-resolves the first active tenant.

---

## Environment Variables Reference

| Variable | Required | Description |
|---|---|---|
| `APP_ENV` | Yes | `dev` or `prod` |
| `APP_SECRET` | Yes | 32+ char random string — never share |
| `APP_DEBUG` | No | `false` in prod |
| `DATABASE_URL` | Yes | MySQL DSN |
| `MAILER_DSN` | No | SMTP connection string |
| `REDIS_URL` | No | Redis connection (falls back to filesystem) |

---

## Directory Reference

```
├── assets/js/          Vue 3 pages, components, composables
├── config/             Symfony config (packages, routes, security)
├── deploy/             cPanel deploy script + php.ini
├── migrations/         Doctrine migration files
├── public/             Web root — index.php, .htaccess, build/
├── src/
│   ├── Controller/     Route controllers by module
│   ├── Entity/         Doctrine entities
│   ├── Repository/     Custom query methods
│   ├── Security/       Login authenticator
│   ├── Service/        Business logic (LeadReadService, LeadWriteService)
│   └── Tenant/         Multi-tenancy core (TenantContext, TenantResolver)
├── templates/          Twig base template (app.html.twig)
└── var/                Cache, logs (git-ignored)
```
