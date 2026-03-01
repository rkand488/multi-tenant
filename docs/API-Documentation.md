# API Documentation

> **Base URL (central):** `https://app.com/api/v1`  
> **Base URL (tenant):** `https://{slug}.app.com/api/v1`  
> **Authentication:** Laravel Sanctum — Bearer token in `Authorization` header  
> **Format:** JSON  
> **Versioning:** URI path prefix `/api/v1`

---

## Authentication Conventions

Every protected endpoint requires:

```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

Error responses follow RFC 7807:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

Successful paginated responses use:

```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}
```

---

## 1. Authentication Endpoints

### `POST /api/v1/auth/register`  *(Central)*

Register a new tenant (begins onboarding).

**Request body:**
```json
{
  "name":          "Acme Corporation",
  "slug":          "acme",
  "owner_email":   "admin@acme.com",
  "owner_name":    "Jane Smith",
  "password":      "SecurePassword1!",
  "plan_id":       1
}
```

**Response `202 Accepted`:**
```json
{
  "message": "Tenant is being provisioned.",
  "tenant_id": "01950e0a-4b43-7000-9b74-123456789abc",
  "status_url": "https://app.com/api/v1/tenants/01950e0a.../status"
}
```

---

### `POST /api/v1/auth/login`  *(Tenant)*

Authenticate a user and receive a Sanctum API token.

**Request body:**
```json
{
  "email":    "user@acme.com",
  "password": "SecurePassword1!",
  "device_name": "iPhone 15"
}
```

**Response `200 OK`:**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "Jane Smith",
    "email": "user@acme.com",
    "roles": ["admin"],
    "permissions": ["users.create", "users.view"]
  }
}
```

---

### `POST /api/v1/auth/logout`  *(Tenant — requires auth)*

Revoke the current token.

**Response `200 OK`:**
```json
{ "message": "Logged out successfully." }
```

---

### `POST /api/v1/auth/forgot-password`  *(Tenant)*

**Request body:**
```json
{ "email": "user@acme.com" }
```

**Response `200 OK`:**
```json
{ "message": "Password reset link sent." }
```

---

### `POST /api/v1/auth/reset-password`  *(Tenant)*

**Request body:**
```json
{
  "token":                 "abc123...",
  "email":                 "user@acme.com",
  "password":              "NewPassword1!",
  "password_confirmation": "NewPassword1!"
}
```

---

### `GET /api/v1/auth/me`  *(Tenant — requires auth)*

Returns the authenticated user with roles and permissions.

**Response `200 OK`:**
```json
{
  "id": 1,
  "name": "Jane Smith",
  "email": "user@acme.com",
  "avatar_url": "https://...",
  "is_owner": true,
  "status": "active",
  "roles": ["admin"],
  "permissions": ["users.create", "users.view", "roles.view"],
  "last_login_at": "2026-03-01T10:00:00Z"
}
```

---

## 2. Tenant Endpoints  *(Central — Super Admin)*

All endpoints under `/api/v1/admin/tenants` require super-admin authentication.

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/admin/tenants` | List all tenants (paginated, filterable) |
| `POST` | `/admin/tenants` | Manually create a tenant |
| `GET` | `/admin/tenants/{id}` | Get tenant details |
| `PATCH` | `/admin/tenants/{id}` | Update tenant metadata |
| `POST` | `/admin/tenants/{id}/suspend` | Suspend tenant |
| `POST` | `/admin/tenants/{id}/activate` | Reactivate tenant |
| `DELETE` | `/admin/tenants/{id}` | Soft-delete tenant |
| `GET` | `/admin/tenants/{id}/status` | Provisioning status check |

### `GET /api/v1/admin/tenants`

Query parameters: `?status=active&search=acme&page=1&per_page=20`

**Response `200 OK`:**
```json
{
  "data": [
    {
      "id": "01950e0a-...",
      "name": "Acme Corporation",
      "slug": "acme",
      "status": "active",
      "owner_email": "admin@acme.com",
      "plan": { "name": "Pro", "slug": "pro" },
      "subscription_status": "active",
      "users_count": 12,
      "created_at": "2026-01-15T09:00:00Z"
    }
  ],
  "meta": { "total": 45, "current_page": 1, "last_page": 3 }
}
```

---

## 3. Users Endpoints  *(Tenant — requires auth)*

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/users` | List tenant users |
| `POST` | `/api/v1/users/invite` | Invite a new user |
| `GET` | `/api/v1/users/{id}` | Get user profile |
| `PATCH` | `/api/v1/users/{id}` | Update user |
| `DELETE` | `/api/v1/users/{id}` | Remove user from tenant |
| `POST` | `/api/v1/users/{id}/roles` | Assign roles to user |
| `DELETE` | `/api/v1/users/{id}/roles/{roleId}` | Remove role from user |

### `POST /api/v1/users/invite`

Requires permission: `users.invite`

**Request body:**
```json
{
  "email": "newmember@acme.com",
  "name":  "Bob Jones",
  "roles": [2]
}
```

**Response `201 Created`:**
```json
{
  "message": "Invitation sent.",
  "user": {
    "id": 42,
    "email": "newmember@acme.com",
    "status": "invited"
  }
}
```

---

## 4. Roles & Permissions Endpoints  *(Tenant)*

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/roles` | List roles |
| `POST` | `/api/v1/roles` | Create custom role |
| `GET` | `/api/v1/roles/{id}` | Get role with permissions |
| `PATCH` | `/api/v1/roles/{id}` | Update role |
| `DELETE` | `/api/v1/roles/{id}` | Delete custom role |
| `PUT` | `/api/v1/roles/{id}/permissions` | Sync permissions for a role |
| `GET` | `/api/v1/permissions` | List all available permissions |

### `PUT /api/v1/roles/{id}/permissions`

**Request body:**
```json
{ "permissions": ["users.view", "users.create", "files.upload"] }
```

**Response `200 OK`:**
```json
{
  "role": {
    "id": 3,
    "name": "editor",
    "permissions": ["users.view", "users.create", "files.upload"]
  }
}
```

---

## 5. Billing Endpoints

### Tenant billing  *(Tenant — requires owner role)*

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/billing/subscription` | Current subscription details |
| `GET` | `/api/v1/billing/invoices` | Invoice history |
| `GET` | `/api/v1/billing/invoices/{id}` | Single invoice |
| `POST` | `/api/v1/billing/subscribe` | Subscribe to a plan |
| `PATCH` | `/api/v1/billing/subscribe` | Change plan (upgrade/downgrade) |
| `DELETE` | `/api/v1/billing/subscribe` | Cancel subscription |
| `GET` | `/api/v1/billing/plans` | Available public plans |
| `POST` | `/api/v1/billing/portal` | Generate Stripe Customer Portal URL |

### `GET /api/v1/billing/subscription`

**Response `200 OK`:**
```json
{
  "plan": { "name": "Pro", "price_monthly": 49.00 },
  "status": "active",
  "billing_cycle": "monthly",
  "current_period_end": "2026-04-01T00:00:00Z",
  "usage": {
    "users": { "used": 8, "limit": 20 },
    "storage_gb": { "used": 4.2, "limit": 50 },
    "api_calls_today": { "used": 320, "limit": 5000 }
  }
}
```

---

## 6. Settings Endpoints  *(Tenant)*

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/settings` | All settings (grouped) |
| `PATCH` | `/api/v1/settings` | Bulk update settings |
| `GET` | `/api/v1/settings/{group}` | Settings for one group |
| `PATCH` | `/api/v1/settings/{group}` | Update one group |

### `PATCH /api/v1/settings/general`

**Request body:**
```json
{
  "company_name": "Acme Corp Ltd",
  "timezone":     "America/New_York",
  "language":     "en"
}
```

---

## 7. Files Endpoints  *(Tenant)*

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/files` | List files (filterable by collection) |
| `POST` | `/api/v1/files` | Upload file (multipart/form-data) |
| `GET` | `/api/v1/files/{id}` | File metadata + temporary URL |
| `DELETE` | `/api/v1/files/{id}` | Delete file |
| `GET` | `/api/v1/files/storage-usage` | Current storage usage vs limit |

### `POST /api/v1/files`

Content-Type: `multipart/form-data`

| Field | Type | Required | Description |
|---|---|---|---|
| `file` | binary | yes | The file to upload |
| `collection` | string | no | "documents", "avatars" |

**Response `201 Created`:**
```json
{
  "id": 99,
  "original_name": "report.pdf",
  "size_bytes": 204800,
  "mime_type": "application/pdf",
  "temporary_url": "https://s3.example.com/...?X-Amz-Expires=3600",
  "created_at": "2026-03-01T11:00:00Z"
}
```

---

## 8. Activity & Audit Log Endpoints  *(Tenant)*

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/activity-logs` | Paginated activity feed |
| `GET` | `/api/v1/audit-logs` | Paginated audit trail |

Query parameters: `?user_id=1&event=user.created&from=2026-01-01&to=2026-03-01`

---

## 9. Notifications Endpoints  *(Tenant)*

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/notifications` | List notifications (unread first) |
| `POST` | `/api/v1/notifications/{id}/read` | Mark as read |
| `POST` | `/api/v1/notifications/read-all` | Mark all as read |
| `DELETE` | `/api/v1/notifications/{id}` | Delete notification |

---

## 10. Admin Endpoints  *(Central — Super Admin)*

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/admin/dashboard` | Platform KPIs |
| `GET` | `/api/v1/admin/plans` | List plans |
| `POST` | `/api/v1/admin/plans` | Create plan |
| `PATCH` | `/api/v1/admin/plans/{id}` | Update plan |
| `GET` | `/api/v1/admin/subscriptions` | All subscriptions |
| `GET` | `/api/v1/admin/invoices` | All invoices |
| `GET` | `/api/v1/admin/activity-logs` | Platform-wide activity |

---

## Rate Limiting

| Endpoint group | Limit (per minute) |
|---|---|
| Auth (login/register) | 10 per IP |
| Tenant API (general) | 60 per user |
| File upload | 20 per user |
| Admin API | 120 per admin |

Rate limit headers on every response:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58
X-RateLimit-Reset: 1740830460
```

On breach → `429 Too Many Requests`.

---

## Webhook Events  *(Stripe integration)*

`POST /api/v1/webhooks/stripe`

Events handled:

| Event | Action |
|---|---|
| `invoice.paid` | Mark invoice paid, extend subscription |
| `invoice.payment_failed` | Set subscription to `past_due`, notify tenant |
| `customer.subscription.deleted` | Cancel subscription |
| `customer.subscription.updated` | Sync plan/cycle changes |

All Stripe webhooks are verified using the `Stripe-Signature` header before processing.

---

*Last updated: 2026-03-01*
