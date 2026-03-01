# Laravel SaaS Multi-Tenant Starter

A production-grade Laravel 12 starter for building multi-tenant SaaS platforms with clean tenant isolation, role-based access, subscription billing flows, and a scalable API-first architecture.

---

## Project Overview

This project is designed for teams and freelancers who need to ship SaaS products quickly without sacrificing architecture quality. It provides a central (landlord) control plane for tenants, plans, subscriptions, and invoices, while tenant requests are dynamically resolved and scoped at runtime.

### What this starter gives you

- Multi-tenant domain and subdomain resolution
- Dynamic per-tenant database switching
- Tenant lifecycle and onboarding-ready structure
- Subscription and usage endpoints for billing workflows
- Admin APIs for tenant and system management
- Sanctum-protected authentication flows
- Pest test suite for confidence and maintainability

---

## Architecture Diagram

```mermaid
flowchart LR
		C[Client App / Frontend] --> API[/API v1/]

		subgraph Central[Central Layer (Landlord DB)]
				T[Tenant Records]
				D[Domain Mapping]
				P[Plans & Subscriptions]
				I[Invoices & Usage]
				A[Admin APIs]
		end

		subgraph Runtime[Request Runtime]
				R[Tenant Resolver]
				M[Identify Tenant Middleware]
				S[Database Manager]
		end

		subgraph TenantData[Tenant Layer (Isolated DB per Tenant)]
				U[Users]
				F[Files]
				L[Activity Logs]
				TS[Team Settings]
		end

		API --> M --> R
		R --> D
		R --> T
		M --> S
		S --> TenantData

		API --> A
		A --> Central
```

---

## Core Features

- **Tenant Isolation**: Each tenant uses an isolated database connection resolved at runtime.
- **Flexible Tenant Resolution**: Supports custom domains and slug-based subdomains.
- **Authentication & Security**: Sanctum token authentication with route-level middleware enforcement.
- **Billing Module**: Plan listing, subscription lifecycle endpoints, invoice access, and usage reporting.
- **Admin Module**: System-level endpoints for tenant, plan, invoice, and analytics management.
- **Tenant Module**: User, roles/permissions, files, team settings, and activity log APIs.

---

## Tech Stack

- **Backend**: Laravel 12, PHP 8.3+
- **Auth**: Laravel Sanctum
- **Database**: SQLite (dev/test) + MySQL-compatible multi-connection tenancy flow
- **Frontend Tooling**: Vite + Tailwind CSS v4
- **Testing**: Pest v4 + PHPUnit 12
- **Code Quality**: Laravel Pint
- **Containerized Dev**: Laravel Sail (Docker)

---

## Installation (Local)

### 1) Clone and install dependencies

```bash
git clone <your-repo-url>
cd tenant
composer install
npm install
```

### 2) Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

### 3) Configure tenancy domain settings

Set these values in `.env`:

```dotenv
APP_DOMAIN=app.com
CENTRAL_DOMAIN=app.com
TENANT_DB_PREFIX=tenant_
```

### 4) Run migrations and start app

```bash
php artisan migrate
composer run dev
```

### One-command bootstrap

You can also use the built-in setup script:

```bash
composer run setup
```

---

## Docker Setup (Laravel Sail)

```bash
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Useful Sail commands:

```bash
./vendor/bin/sail artisan test --compact
./vendor/bin/sail artisan queue:listen
```

---

## How Multi-Tenancy Works

1. Incoming request host is inspected.
2. Tenant is resolved via:
	 - exact domain match from central `domains` table, or
	 - slug extraction from subdomain (e.g. `acme.app.com` → `acme`).
3. Tenant middleware switches default DB connection to the resolved tenant database.
4. Tenant-scoped APIs run against that isolated database.
5. Response ends and app returns to central connection for safety.

This design keeps central billing/admin concerns separated from tenant operational data while preserving a clean runtime flow.

---

## API Examples

### Authentication

```bash
curl -X POST http://localhost/api/v1/auth/register \
	-H "Accept: application/json" \
	-H "Content-Type: application/json" \
	-d '{
		"name": "Jane Doe",
		"email": "jane@example.com",
		"password": "password",
		"password_confirmation": "password"
	}'
```

```bash
curl -X POST http://localhost/api/v1/auth/login \
	-H "Accept: application/json" \
	-H "Content-Type: application/json" \
	-d '{
		"email": "jane@example.com",
		"password": "password"
	}'
```

### Tenant-scoped endpoint (authenticated)

```bash
curl -X GET http://localhost/api/v1/tenant/users \
	-H "Accept: application/json" \
	-H "Authorization: Bearer <token>"
```

### Billing endpoint (authenticated + subscription middleware)

```bash
curl -X GET http://localhost/api/v1/billing/usage \
	-H "Accept: application/json" \
	-H "Authorization: Bearer <token>"
```

---

## Screenshots

> Add screenshots or GIFs from your local/demo environment to make this repository instantly client-ready.

- `docs/screenshots/admin-dashboard.png`
- `docs/screenshots/tenant-users.png`
- `docs/screenshots/billing-subscription.png`
- `docs/screenshots/api-collection.png`

Example markdown:

```markdown
![Admin Dashboard](docs/screenshots/admin-dashboard.png)
![Tenant Users](docs/screenshots/tenant-users.png)
![Billing Subscription](docs/screenshots/billing-subscription.png)
```

---

## Testing & Quality

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

---

## Contributing Guide

1. Fork the repository.
2. Create a feature branch (`feature/your-feature-name`).
3. Make focused, test-covered changes.
4. Run tests and formatting before opening a PR.
5. Submit a clear pull request with context and screenshots (if UI-related).

### Suggested branch naming

- `feature/...`
- `fix/...`
- `chore/...`

---

## License

This project is open-sourced under the MIT License.
