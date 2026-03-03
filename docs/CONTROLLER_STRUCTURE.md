# Controller Structure Documentation

## Overview

This Laravel application follows a clean separation between **API** and **Web** controllers to ensure:
- Scramble API documentation only scans API controllers
- Clear separation of concerns between API and Web logic
- Better maintainability and organization

## Directory Structure

```
app/Http/Controllers/
├── Controller.php                    # Base controller
├── Api/                             # API Controllers (documented by Scramble)
│   ├── Admin/                       # Admin API endpoints
│   │   ├── InvoiceController.php
│   │   ├── PlanController.php
│   │   ├── SystemAnalyticsController.php
│   │   ├── TenantController.php
│   │   └── UsageStatisticController.php
│   ├── Auth/                        # Authentication API endpoints
│   │   ├── AuthController.php
│   │   └── InvitationController.php
│   ├── Billing/                     # Billing API endpoints
│   │   ├── InvoiceController.php
│   │   ├── PlanController.php
│   │   ├── SubscriptionController.php
│   │   └── UsageController.php
│   └── Tenant/                      # Tenant API endpoints
│       ├── ActivityLogController.php
│       ├── FileStorageController.php
│       ├── RolePermissionController.php
│       ├── TeamSettingController.php
│       └── UserController.php
├── Auth/                            # Web Authentication
│   └── WebAuthController.php
└── Web/                             # Web Controllers (Inertia.js)
    ├── Admin/                       # Admin Web UI
    │   ├── AnalyticsController.php
    │   ├── DashboardController.php
    │   ├── PlanController.php
    │   ├── SettingsController.php
    │   ├── SubscriptionController.php
    │   └── TenantController.php
    ├── Tenant/                      # Tenant Web UI
    │   ├── ActivityLogController.php
    │   ├── BillingController.php
    │   ├── DashboardController.php
    │   ├── RoleController.php
    │   ├── SettingsController.php
    │   ├── UsageController.php
    │   └── UserController.php
    ├── DemoController.php           # Demo pages
    └── HomeController.php           # Landing pages
```

## Namespaces

### API Controllers
All API controllers use the `App\Http\Controllers\Api\*` namespace:

```php
namespace App\Http\Controllers\Api\Auth;
namespace App\Http\Controllers\Api\Tenant;
namespace App\Http\Controllers\Api\Billing;
namespace App\Http\Controllers\Api\Admin;
```

### Web Controllers
All Web controllers use the `App\Http\Controllers\Web\*` namespace:

```php
namespace App\Http\Controllers\Web\Admin;
namespace App\Http\Controllers\Web\Tenant;
```

## Route Files

### routes/api.php
Contains all API endpoints with controllers from `App\Http\Controllers\Api\*`

```php
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Tenant\UserController;
// etc...
```

### routes/web.php
Contains all web routes with controllers from `App\Http\Controllers\Web\*`

```php
use App\Http\Controllers\Web\Tenant\DashboardController;
use App\Http\Controllers\Web\Admin\TenantController;
// etc...
```

## Scramble Configuration

Scramble is configured in `config/scramble.php` to:
- Only scan routes with `api_path` prefix (defaults to `'api'`)
- Automatically document all controllers in `App\Http\Controllers\Api\*`
- Ignore Web controllers in `App\Http\Controllers\Web\*`

## Documentation Tags

API controllers use PHPDoc tags for organization:

```php
/**
 * @tags Authentication
 */
class AuthController extends Controller
{
    /**
     * Login to the application.
     *
     * @unauthenticated
     * @response array{user: object, token: string}
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // ...
    }
}
```

### Available Tags

| Tag | Purpose |
|-----|---------|
| `@tags Authentication` | Authentication endpoints |
| `@tags Tenant Management` | Invitations and file storage |
| `@tags Users` | User CRUD operations |
| `@tags Roles & Permissions` | Role management |
| `@tags Subscriptions` | Plan and subscription management |
| `@tags Billing` | Invoices and usage tracking |
| `@tags Settings` | Team settings |
| `@tags Activity Logs` | Audit trail |
| `@tags Admin System` | Admin-only endpoints |

### Response Annotations

Use `@response` to document return types:

```php
@response array{message: string, data: object}
@response array{user: object, token: string}
@response object
```

### Authentication

Use `@unauthenticated` for public endpoints:

```php
/**
 * @unauthenticated
 */
public function login(LoginRequest $request)
```

## Benefits of This Structure

1. **Clean Documentation**: Only API endpoints appear in Scramble docs
2. **Separation of Concerns**: API logic separate from Web UI logic
3. **Easier Testing**: Can mock/test API and Web separately
4. **Better IDE Support**: Clear namespaces improve autocomplete
5. **Scalability**: Easy to add new API or Web controllers
6. **Team Collaboration**: Frontend and backend teams can work independently

## Adding New Controllers

### For API Endpoints
1. Create controller in `app/Http/Controllers/Api/{Module}/`
2. Use namespace `App\Http\Controllers\Api\{Module}`
3. Add `@tags` PHPDoc comment
4. Document methods with `@response` annotations
5. Register in `routes/api.php`

### For Web Pages
1. Create controller in `app/Http/Controllers/Web/{Module}/`
2. Use namespace `App\Http\Controllers\Web\{Module}`
3. Return `Inertia::render()` responses
4. Register in `routes/web.php`

## Viewing API Documentation

Access the interactive API documentation at:
```
http://localhost/docs/api
```

Export OpenAPI schema:
```bash
php artisan scramble:export
```
