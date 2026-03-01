# Deployment

> **Target environment:** Ubuntu 24.04 LTS  
> **Container orchestration:** Docker Compose (single-server) → Kubernetes (scale-out)  
> **CI/CD:** GitHub Actions

---

## 1. Docker Setup

### Services

```
docker-compose.yml services:
  app        — PHP 8.3 FPM (Laravel application)
  nginx      — Nginx reverse proxy
  mysql      — MySQL 8.0 (central landlord DB)
  redis      — Redis 7 (cache + queues + sessions)
  horizon    — Laravel Horizon (queue worker)
  scheduler  — Laravel scheduler (crontab)
  mailpit    — Local mail catcher (dev only)
  minio      — S3-compatible local storage (dev only)
```

### `docker-compose.yml`

```yaml
version: "3.9"

services:
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
      target: production
    restart: unless-stopped
    volumes:
      - .:/var/www/html
      - ./storage:/var/www/html/storage
    environment:
      - APP_ENV=${APP_ENV:-production}
    depends_on:
      - mysql
      - redis
    networks:
      - saas

  nginx:
    image: nginx:1.27-alpine
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - .:/var/www/html
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
      - ./docker/nginx/ssl:/etc/nginx/ssl
    depends_on:
      - app
    networks:
      - saas

  mysql:
    image: mysql:8.0
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: landlord
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/init:/docker-entrypoint-initdb.d
    ports:
      - "3306:3306"
    networks:
      - saas

  redis:
    image: redis:7-alpine
    restart: unless-stopped
    command: redis-server --requirepass ${REDIS_PASSWORD} --appendonly yes
    volumes:
      - redis_data:/data
    networks:
      - saas

  horizon:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
      target: production
    restart: unless-stopped
    command: php artisan horizon
    depends_on:
      - mysql
      - redis
    volumes:
      - .:/var/www/html
    networks:
      - saas

  scheduler:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
      target: production
    restart: unless-stopped
    command: sh -c "while true; do php artisan schedule:run --verbose --no-interaction; sleep 60; done"
    depends_on:
      - mysql
      - redis
    volumes:
      - .:/var/www/html
    networks:
      - saas

volumes:
  mysql_data:
  redis_data:

networks:
  saas:
    driver: bridge
```

### `docker/php/Dockerfile`

```dockerfile
FROM php:8.3-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    build-base \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# --- Development stage ---
FROM base AS development
COPY docker/php/php-dev.ini /usr/local/etc/php/conf.d/custom.ini
RUN composer global require laravel/pint

# --- Production stage ---
FROM base AS production

COPY docker/php/php-prod.ini /usr/local/etc/php/conf.d/custom.ini

COPY --chown=www-data:www-data . .

RUN composer install \
    --no-dev \
    --no-interaction \
    --optimize-autoloader \
    --prefer-dist

RUN yarn install --frozen-lockfile && yarn build

RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

USER www-data
```

---

## 2. Environment Configuration

### `.env` Reference

```dotenv
# Application
APP_NAME="SaaS Platform"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://app.com
APP_DOMAIN=app.com
CENTRAL_DOMAIN=app.com

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=landlord
DB_USERNAME=saas_user
DB_PASSWORD=secret
DB_ROOT_PASSWORD=rootsecret

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=redissecret
REDIS_PORT=6379
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.postmarkapp.com
MAIL_PORT=587
MAIL_USERNAME=api_token
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=noreply@app.com
MAIL_FROM_NAME="SaaS Platform"

# Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=saas-platform-storage

# Stripe
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Sanctum
SANCTUM_STATEFUL_DOMAINS=app.com,*.app.com

# Horizon
HORIZON_USERNAME=admin
HORIZON_PASSWORD=secret
```

### Per-Environment Overrides

| Key | Development | Staging | Production |
|---|---|---|---|
| `APP_DEBUG` | `true` | `false` | `false` |
| `MAIL_MAILER` | `log` | `smtp` (Mailtrap) | `smtp` (Postmark) |
| `FILESYSTEM_DISK` | `local` | `s3` | `s3` |
| `QUEUE_CONNECTION` | `redis` | `redis` | `redis` |
| `LOG_CHANNEL` | `stack` | `stderr` | `stderr` |
| `LOG_LEVEL` | `debug` | `warning` | `error` |

---

## 3. CI/CD Pipeline (GitHub Actions)

### Pipeline Stages

```
Push to PR  →  lint  →  test  →  static-analysis
Push to main  →  lint  →  test  →  build  →  deploy-staging
Tag (v*)      →  lint  →  test  →  build  →  deploy-production
```

### `.github/workflows/ci.yml`

```yaml
name: CI

on: [push, pull_request]

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.3' }
      - run: composer install --no-interaction --prefer-dist
      - run: ./vendor/bin/pint --test

  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: landlord
        ports: ["3306:3306"]
      redis:
        image: redis:7
        ports: ["6379:6379"]

    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo_mysql, redis
          coverage: xdebug
      - uses: actions/setup-node@v4
        with: { node-version: '20' }
      - run: corepack enable && yarn install --frozen-lockfile
      - run: yarn build
      - run: composer install --no-interaction --prefer-dist
      - run: cp .env.testing .env
      - run: php artisan key:generate
      - run: php artisan migrate --database=central --no-interaction
      - run: php artisan test --compact --coverage --min=80

  analyse:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.3' }
      - run: composer install --no-interaction --prefer-dist
      - run: ./vendor/bin/phpstan analyse --level=9

  deploy-staging:
    needs: [lint, test, analyse]
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Deploy to staging
        run: |
          echo "${{ secrets.STAGING_SSH_KEY }}" > /tmp/key && chmod 600 /tmp/key
          ssh -i /tmp/key deploy@staging.app.com "cd /var/www/saas && ./deploy.sh"
```

### `deploy.sh` (on server)

```bash
#!/usr/bin/env bash
set -e

git pull origin main

# Install PHP dependencies (production)
docker compose exec app composer install --no-dev --optimize-autoloader --no-interaction

# Install & build frontend
docker compose exec app yarn install --frozen-lockfile
docker compose exec app yarn build

# Run central migrations
docker compose exec app php artisan migrate --database=central --force --no-interaction

# Run tenant migrations for all active tenants
docker compose exec app php artisan tenants:migrate --force --no-interaction

# Clear & recompile caches
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose exec app php artisan event:cache

# Restart queue workers
docker compose exec app php artisan horizon:terminate
docker compose restart horizon scheduler

echo "Deploy complete."
```

---

## 4. Deployment Topology

### Single Server (Starter)

```
[Cloudflare] → [VPS: Nginx + PHP-FPM + MySQL + Redis]
```

Suitable for: 0–50 tenants.

### Multi-Server (Growth)

```
[Cloudflare / ALB]
         │
   ┌─────┴──────┐
 [App-1]     [App-2]    ← PHP-FPM (horizontal scale)
   └─────┬──────┘
         │
  [MySQL RDS]  [Redis ELC]  [S3]
         │
  [Horizon workers]
```

Suitable for: 50–500 tenants.

### Kubernetes (Enterprise)

- Laravel application as a `Deployment` with HPA (CPU-based autoscaling).
- MySQL via PlanetScale or RDS Multi-AZ.
- Redis via Elasticache with cluster mode.
- Horizon as a separate `Deployment` with per-tenant queue consumer pods.
- S3 + CloudFront CDN for static assets and tenant files.

---

## 5. Zero-Downtime Deployment

1. New code deployed to `app` container without restarting Nginx.
2. `php artisan down --retry=30` sets maintenance mode.
3. Migrations run with `--force`.
4. Caches cleared and rebuilt.
5. `php artisan up` removes maintenance mode.
6. Horizon terminated and restarted (graceful shutdown: current jobs finish).

Total downtime window: **< 5 seconds** (maintenance mode only).

---

## 6. Backup Strategy

| Data | Frequency | Retention | Tool |
|---|---|---|---|
| Central DB | Every 6 hours | 30 days | `mysqldump` → S3 |
| Tenant DBs | Daily per tenant | 14 days | `mysqldump` → S3 |
| Redis | RDB snapshot hourly | 7 days | Redis BGSAVE → S3 |
| S3 files | Versioning enabled | Indefinite | S3 lifecycle |

Restoring a single tenant: restore `tenant_{slug}.sql.gz` to a new DB, re-point the connection.

---

*Last updated: 2026-03-01*
