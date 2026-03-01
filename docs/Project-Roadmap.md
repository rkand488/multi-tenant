# Project Roadmap

> **Project:** SaaS Multi-Tenant Platform (Laravel + Inertia + Vue 3)  
> **Purpose:** Production-ready portfolio / client-facing SaaS starter kit  
> **Last updated:** 2026-03-01

---

## Vision

Deliver a fully production-ready, extensible SaaS Multi-Tenant platform that demonstrates:

- Clean, idiomatic Laravel 12 architecture
- Bulletproof tenant isolation (database-per-tenant)
- Comprehensive billing simulation with real Stripe integration path
- Enterprise-grade security, observability, and DevOps practices
- A polished Inertia + Vue 3 dashboard ready for white-labelling

---

## Milestones

### Phase 1 — Foundation  *(Weeks 1–2)*

**Goal:** Working application skeleton with tenant resolution and central auth.

| # | Task | Priority | Status |
|---|---|---|---|
| 1.1 | Laravel 12 project scaffolding with Docker Compose | Critical | ☐ |
| 1.2 | Central database schema + migrations (tenants, domains, plans, subscriptions) | Critical | ☐ |
| 1.3 | `TenantResolver`, `TenantContext`, `DatabaseManager` | Critical | ☐ |
| 1.4 | `IdentifyTenant` and `EnsureTenantIsActive` middleware | Critical | ☐ |
| 1.5 | Super-admin auth (login, guard, protected routes) | Critical | ☐ |
| 1.6 | Tenant registration + `ProvisionTenantDatabase` job | Critical | ☐ |
| 1.7 | Inertia + Vue 3 bootstrap (layouts, app.blade.php) | Critical | ☐ |
| 1.8 | Vite config + Tailwind CSS v4 setup | Critical | ☐ |
| 1.9 | Base Pest test suite + architecture tests | High | ☐ |
| 1.10 | GitHub Actions CI pipeline | High | ☐ |

**Deliverable:** A new tenant can register; subdomain routes to a tenant dashboard (empty). CI is green.

---

### Phase 2 — Core Tenant Features  *(Weeks 3–4)*

**Goal:** Full user management, roles, permissions, and tenant settings.

| # | Task | Priority | Status |
|---|---|---|---|
| 2.1 | Tenant user CRUD (invite, activate, deactivate, remove) | Critical | ☐ |
| 2.2 | Email verification for invited users | Critical | ☐ |
| 2.3 | Role & permission system (CRUD + assignment) | Critical | ☐ |
| 2.4 | `hasPermission` helper + policy-based authorization | Critical | ☐ |
| 2.5 | Tenant settings (general, mail, notifications groups) | High | ☐ |
| 2.6 | Activity log (write + read via API) | High | ☐ |
| 2.7 | Audit log (automatic model observer) | High | ☐ |
| 2.8 | Password policy enforcement | High | ☐ |
| 2.9 | Feature tests for all user/role endpoints | Critical | ☐ |
| 2.10 | Tenant isolation tests | Critical | ☐ |

**Deliverable:** Admins can invite users, assign roles, view audit trail. All tests passing.

---

### Phase 3 — Billing & Subscriptions  *(Weeks 5–6)*

**Goal:** Full subscription lifecycle with Stripe-ready structure.

| # | Task | Priority | Status |
|---|---|---|---|
| 3.1 | Plan management (CRUD in admin, public listing API) | Critical | ☐ |
| 3.2 | Subscription creation + plan assignment | Critical | ☐ |
| 3.3 | Invoice generation (simulated + Stripe skeleton) | Critical | ☐ |
| 3.4 | Stripe webhook listener (`invoice.paid`, failures, cancellations) | Critical | ☐ |
| 3.5 | Usage limit enforcement middleware (`CheckPlanLimit`) | Critical | ☐ |
| 3.6 | Billing dashboard for tenant owners | High | ☐ |
| 3.7 | Invoice PDF generation (using `barryvdh/laravel-dompdf`) | Medium | ☐ |
| 3.8 | Plan change (upgrade / downgrade) with proration logic | High | ☐ |
| 3.9 | Trial period support (`trial_ends_at`, grace access) | Medium | ☐ |
| 3.10 | Billing feature tests + webhook tests | Critical | ☐ |

**Deliverable:** Tenants can subscribe, upgrade, cancel. Stripe webhooks update DB. Usage limits enforced.

---

### Phase 4 — API & File Storage  *(Weeks 7–8)*

**Goal:** Full REST API with Sanctum tokens + tenant file storage.

| # | Task | Priority | Status |
|---|---|---|---|
| 4.1 | API versioning structure (`/api/v1`) + Sanctum token auth | Critical | ☐ |
| 4.2 | API Resources for all models | High | ☐ |
| 4.3 | Rate limiting (per-user + per-plan) | Critical | ☐ |
| 4.4 | API token management UI (create, list, revoke) | High | ☐ |
| 4.5 | File upload endpoint with mime/size validation | High | ☐ |
| 4.6 | Storage quota enforcement from plan limits | Critical | ☐ |
| 4.7 | Temporary signed URL generation for file access | High | ☐ |
| 4.8 | S3-compatible storage (MinIO in dev, S3 in prod) | Critical | ☐ |
| 4.9 | API feature tests + rate limit tests | Critical | ☐ |
| 4.10 | Postman / Bruno collection export | Medium | ☐ |

**Deliverable:** Full RESTful API documented and tested. File uploads stored per-tenant with quota enforcement.

---

### Phase 5 — Admin Dashboard & Observability  *(Weeks 9–10)*

**Goal:** Super-admin dashboard + Horizon + structured logging.

| # | Task | Priority | Status |
|---|---|---|---|
| 5.1 | Super-admin dashboard (tenant KPIs, MRR, active users) | High | ☐ |
| 5.2 | Tenant management UI (list, search, inspect, suspend) | High | ☐ |
| 5.3 | Platform-wide activity feed | Medium | ☐ |
| 5.4 | Laravel Horizon configuration + UI (auth-protected) | Critical | ☐ |
| 5.5 | Structured JSON logging with tenant/user context | High | ☐ |
| 5.6 | Scheduled commands (invoice generation, log cleanup) | Medium | ☐ |
| 5.7 | Notification system (DB notifications + broadcasts stub) | Medium | ☐ |
| 5.8 | Admin Inertia pages (Plans CRUD, Subscriptions overview) | High | ☐ |
| 5.9 | Sentry / Flare error tracking integration | Medium | ☐ |
| 5.10 | Admin feature tests | High | ☐ |

**Deliverable:** Super admins can monitor the platform, manage tenants, observe queue activity.

---

### Phase 6 — Security Hardening & Compliance  *(Week 11)*

| # | Task | Priority | Status |
|---|---|---|---|
| 6.1 | Security headers middleware | Critical | ☐ |
| 6.2 | CSP nonce propagation with Inertia | High | ☐ |
| 6.3 | CORS configuration | Critical | ☐ |
| 6.4 | 2FA (TOTP) enrollment and enforcement | High | ☐ |
| 6.5 | Brute-force lockout (5 attempts → lockout + email) | High | ☐ |
| 6.6 | Breached password check on registration/reset | Medium | ☐ |
| 6.7 | Larastan level-9 analysis (zero errors) | High | ☐ |
| 6.8 | Secrets rotation runbook | Medium | ☐ |

---

### Phase 7 — DevOps, Documentation & Polish  *(Week 12)*

| # | Task | Priority | Status |
|---|---|---|---|
| 7.1 | Production-ready Docker Compose + docker/php/Dockerfile | Critical | ☐ |
| 7.2 | `deploy.sh` zero-downtime deployment script | Critical | ☐ |
| 7.3 | GitHub Actions CD (staging → production) | High | ☐ |
| 7.4 | Multi-tenant migration artisan command (`tenants:migrate`) | Critical | ☐ |
| 7.5 | Backup scripts (central DB + per-tenant DB + S3) | High | ☐ |
| 7.6 | `README.md` with quickstart, architecture summary, screenshots | Critical | ☐ |
| 7.7 | Demo tenant auto-seeder for reviewers | High | ☐ |
| 7.8 | Final test coverage audit (≥ 80%) | Critical | ☐ |
| 7.9 | Code review + Laravel Pint clean pass | High | ☐ |
| 7.10 | Tag `v1.0.0` release with changelog | Medium | ☐ |

---

## Feature Backlog *(Post v1.0)*

These features are scoped but deferred to future releases:

| Feature | Notes |
|---|---|
| Two-way email integration (inbox per tenant) | Inbound mail via Mailgun routes |
| Tenant white-label branding | Custom logo, colours, email footer |
| Webhook outbound (per tenant) | User-defined event webhooks |
| SSO / SAML for enterprise tenants | OneLogin / Okta integration |
| GraphQL API variant | Apollo + lighthouse |
| Mobile app API (React Native) | Sanctum tokens already in place |
| Per-tenant custom database server | `db_connection` JSON column ready |
| Multi-region deployment | EU / US / APAC partitioning |
| Usage analytics dashboards | Tenant-level real-time charts |
| Automated tenant health checks | Periodic ping + alert |

---

## Technology Decisions Log

| Decision | Chosen | Rejected | Reason |
|---|---|---|---|
| Multi-tenant model | Database-per-tenant | Single DB + tenant_id | Isolation, compliance, enterprise flexibility |
| Frontend | Inertia + Vue 3 | Next.js / separate SPA | Simpler deployment, native Laravel routing |
| Auth | Sanctum | Passport | Lighter, supports both SPA cookies and tokens |
| Queue | Redis + Horizon | Database queue | Performance, real-time monitoring, per-tenant tagging |
| CSS | Tailwind v4 | Bootstrap / Shadcn | Utility-first, matches current stack |
| Package manager | Yarn | npm / pnpm | Faster CI, lockfile reliability |
| Testing | Pest 4 | PHPUnit alone | Expressive syntax, architecture assertions |

---

## Success Criteria for v1.0

- [ ] All 7 phases completed
- [ ] Zero Larastan level-9 errors
- [ ] Laravel Pint passes with no changes
- [ ] ≥ 80% test coverage
- [ ] CI pipeline green on `main`
- [ ] Demo environment live at `demo.app.com`
- [ ] README includes architecture diagram and setup instructions
- [ ] Stripe test mode payment flow works end-to-end
- [ ] 3 demo tenants seeded with realistic data

---

*Last updated: 2026-03-01*
