# Database Schema Design

> **Central database:** `landlord`  
> **Tenant databases:** `tenant_{slug}` (one per organisation)

---

## Central Database (`landlord`)

### `tenants`

Stores every registered organisation. The source of truth for provisioning, billing, and status.

```sql
CREATE TABLE tenants (
    id              CHAR(36)        NOT NULL,           -- UUID v4
    name            VARCHAR(255)    NOT NULL,
    slug            VARCHAR(63)     NOT NULL UNIQUE,    -- subdomain safe
    status          ENUM('provisioning','active','suspended','cancelled')
                                    NOT NULL DEFAULT 'provisioning',
    owner_email     VARCHAR(255)    NOT NULL,
    trial_ends_at   TIMESTAMP       NULL,
    db_connection   JSON            NULL,               -- for dedicated DB servers (enterprise)
    extra           JSON            NULL,               -- extensible metadata
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP       NULL,

    PRIMARY KEY (id),
    INDEX idx_tenants_status (status),
    INDEX idx_tenants_owner_email (owner_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Laravel model:** `App\Central\Models\Tenant`  
**Casts:** `status → TenantStatus enum`, `db_connection → array`, `extra → array`

---

### `domains`

Supports both auto-generated subdomains and custom domains (CNAME).

```sql
CREATE TABLE domains (
    id          BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    tenant_id   CHAR(36)            NOT NULL,
    domain      VARCHAR(253)        NOT NULL UNIQUE,    -- RFC 1035 max
    is_primary  TINYINT(1)          NOT NULL DEFAULT 0,
    is_verified TINYINT(1)          NOT NULL DEFAULT 0,
    verified_at TIMESTAMP           NULL,
    created_at  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_domains_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `plans`

Product plans available for subscription.

```sql
CREATE TABLE plans (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name            VARCHAR(100)    NOT NULL,           -- "Starter", "Pro", "Enterprise"
    slug            VARCHAR(100)    NOT NULL UNIQUE,
    description     TEXT            NULL,
    price_monthly   DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    price_yearly    DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    is_active       TINYINT(1)      NOT NULL DEFAULT 1,
    is_public       TINYINT(1)      NOT NULL DEFAULT 1,
    stripe_price_id_monthly VARCHAR(100) NULL,
    stripe_price_id_yearly  VARCHAR(100) NULL,
    features        JSON            NOT NULL,           -- {"max_users":5,"storage_gb":10,...}
    sort_order      SMALLINT        NOT NULL DEFAULT 0,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Plan features JSON schema:**
```json
{
  "max_users": 5,
  "storage_gb": 10,
  "api_calls_per_day": 1000,
  "custom_domains": 1,
  "audit_logs_days": 30,
  "support_level": "community"
}
```

---

### `subscriptions`

Tracks the active billing relationship between a tenant and a plan.

```sql
CREATE TABLE subscriptions (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id           CHAR(36)        NOT NULL,
    plan_id             BIGINT UNSIGNED NOT NULL,
    status              ENUM('trialing','active','past_due','cancelled','paused')
                                        NOT NULL DEFAULT 'trialing',
    billing_cycle       ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly',
    stripe_subscription_id VARCHAR(100) NULL,
    current_period_start TIMESTAMP      NOT NULL,
    current_period_end   TIMESTAMP      NOT NULL,
    trial_ends_at        TIMESTAMP      NULL,
    cancelled_at         TIMESTAMP      NULL,
    ends_at              TIMESTAMP      NULL,
    created_at           TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id)   REFERENCES plans(id),
    INDEX idx_sub_tenant (tenant_id),
    INDEX idx_sub_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Only one subscription per tenant should be `active` or `trialing` at any time — enforced at the application layer.

---

### `invoices`

Billing records (mapped from Stripe events or generated locally for simulation).

```sql
CREATE TABLE invoices (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id       CHAR(36)        NOT NULL,
    subscription_id BIGINT UNSIGNED NULL,
    stripe_invoice_id VARCHAR(100)  NULL,
    number          VARCHAR(50)     NOT NULL UNIQUE,    -- INV-2026-00001
    status          ENUM('draft','open','paid','void','uncollectible')
                                    NOT NULL DEFAULT 'draft',
    subtotal        DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    tax             DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    total           DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    currency        CHAR(3)         NOT NULL DEFAULT 'USD',
    paid_at         TIMESTAMP       NULL,
    due_date        DATE            NULL,
    period_start    DATE            NULL,
    period_end      DATE            NULL,
    pdf_url         VARCHAR(500)    NULL,
    metadata        JSON            NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL,
    INDEX idx_invoices_tenant (tenant_id),
    INDEX idx_invoices_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `super_admins`

Platform-level admins who access `/admin/*` dashboard.

```sql
CREATE TABLE super_admins (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name            VARCHAR(255)    NOT NULL,
    email           VARCHAR(255)    NOT NULL UNIQUE,
    password        VARCHAR(255)    NOT NULL,
    remember_token  VARCHAR(100)    NULL,
    last_login_at   TIMESTAMP       NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Tenant Database (`tenant_{slug}`)

Every tenant database is created from the same set of migrations in `database/migrations/tenant/`.

---

### `users`

```sql
CREATE TABLE users (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name                VARCHAR(255)    NOT NULL,
    email               VARCHAR(255)    NOT NULL UNIQUE,
    email_verified_at   TIMESTAMP       NULL,
    password            VARCHAR(255)    NOT NULL,
    avatar_path         VARCHAR(500)    NULL,
    remember_token      VARCHAR(100)    NULL,
    is_owner            TINYINT(1)      NOT NULL DEFAULT 0,   -- tenant account owner
    status              ENUM('active','inactive','invited')
                                        NOT NULL DEFAULT 'invited',
    invited_by          BIGINT UNSIGNED NULL,
    last_login_at       TIMESTAMP       NULL,
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at          TIMESTAMP       NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `roles`

```sql
CREATE TABLE roles (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100)    NOT NULL UNIQUE,   -- "admin", "editor", "viewer"
    display_name VARCHAR(150)   NOT NULL,
    description TEXT            NULL,
    is_system   TINYINT(1)      NOT NULL DEFAULT 0, -- cannot be deleted
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Seeded system roles:** `owner`, `admin`, `member`, `viewer`

---

### `permissions`

```sql
CREATE TABLE permissions (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(150)    NOT NULL UNIQUE,   -- "users.create", "billing.view"
    group       VARCHAR(100)    NOT NULL,           -- "users", "billing", "files"
    description VARCHAR(255)    NULL,

    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `role_user` (pivot)

```sql
CREATE TABLE role_user (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

---

### `permission_role` (pivot)

```sql
CREATE TABLE permission_role (
    permission_id   BIGINT UNSIGNED NOT NULL,
    role_id         BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (permission_id, role_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id)       REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

---

### `activity_logs`

Human-readable event stream — who did what.

```sql
CREATE TABLE activity_logs (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED NULL,
    user_name       VARCHAR(255)    NULL,     -- denormalised for historical accuracy
    event           VARCHAR(100)    NOT NULL, -- "users.created", "file.uploaded"
    subject_type    VARCHAR(100)    NULL,     -- Eloquent morph type
    subject_id      BIGINT UNSIGNED NULL,
    description     TEXT            NULL,
    properties      JSON            NULL,
    ip_address      VARCHAR(45)     NULL,
    user_agent      TEXT            NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_activity_user (user_id),
    INDEX idx_activity_event (event),
    INDEX idx_activity_subject (subject_type, subject_id),
    INDEX idx_activity_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `audit_logs`

Low-level immutable record of every model mutation.

```sql
CREATE TABLE audit_logs (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED NULL,
    event           ENUM('created','updated','deleted','restored') NOT NULL,
    auditable_type  VARCHAR(100)    NOT NULL,
    auditable_id    BIGINT UNSIGNED NOT NULL,
    old_values      JSON            NULL,
    new_values      JSON            NULL,
    url             VARCHAR(1000)   NULL,
    ip_address      VARCHAR(45)     NULL,
    user_agent      TEXT            NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_audit_auditable (auditable_type, auditable_id),
    INDEX idx_audit_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `settings`

Key-value store for tenant-level configuration.

```sql
CREATE TABLE settings (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    group       VARCHAR(100)    NOT NULL DEFAULT 'general', -- "general","mail","notifications"
    key         VARCHAR(150)    NOT NULL,
    value       TEXT            NULL,
    is_encrypted TINYINT(1)     NOT NULL DEFAULT 0,

    PRIMARY KEY (id),
    UNIQUE KEY uq_settings_group_key (group, key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `files`

Tracks uploaded files with storage path and quota contribution.

```sql
CREATE TABLE files (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED NOT NULL,
    disk            VARCHAR(50)     NOT NULL DEFAULT 's3',
    path            VARCHAR(500)    NOT NULL,
    original_name   VARCHAR(255)    NOT NULL,
    mime_type       VARCHAR(127)    NULL,
    size_bytes      BIGINT UNSIGNED NOT NULL DEFAULT 0,
    collection      VARCHAR(100)    NULL,    -- "avatars", "documents", etc.
    meta            JSON            NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP       NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_files_user (user_id),
    INDEX idx_files_collection (collection)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Entity Relationship Summary

```
[central DB]
Tenant (1) ──< Domain (*)
Tenant (1) ──< Subscription (*)
Subscription (*) >── Plan (1)
Tenant (1) ──< Invoice (*)
Invoice (*) >── Subscription (1)

[tenant DB]
User (*) >─< Role (*) [role_user]
Role (*) >─< Permission (*) [permission_role]
User (1) ──< ActivityLog (*)
User (1) ──< AuditLog (*)
User (1) ──< File (*)
```

---

## Migration Strategy

```
database/
├── migrations/
│   ├── central/                  # Run once on landlord DB
│   │   ├── 2026_01_01_create_tenants_table.php
│   │   ├── 2026_01_01_create_domains_table.php
│   │   ├── 2026_01_01_create_plans_table.php
│   │   ├── 2026_01_01_create_subscriptions_table.php
│   │   └── 2026_01_01_create_invoices_table.php
│   └── tenant/                   # Run per tenant DB at creation + upgrade
│       ├── 2026_01_01_create_users_table.php
│       ├── 2026_01_01_create_roles_table.php
│       ├── 2026_01_01_create_permissions_table.php
│       ├── 2026_01_01_create_activity_logs_table.php
│       ├── 2026_01_01_create_audit_logs_table.php
│       ├── 2026_01_01_create_settings_table.php
│       └── 2026_01_01_create_files_table.php
└── seeders/
    ├── Central/
    │   ├── PlanSeeder.php
    │   └── SuperAdminSeeder.php
    └── Tenant/
        ├── RoleSeeder.php           # owner, admin, member, viewer
        └── PermissionSeeder.php
```

**Artisan command to migrate all tenants:**

```bash
php artisan tenants:migrate          # migrate all active tenant DBs
php artisan tenants:migrate --fresh  # fresh + seed (dev/test only)
php artisan tenants:migrate --tenant=acme  # single tenant
```

---

*Last updated: 2026-03-01*
