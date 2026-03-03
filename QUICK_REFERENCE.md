# Quick Reference - Project Commands

## 📦 Package Manager: YARN (Always!)

```bash
# Install dependencies
yarn install

# Build for production
yarn build

# Development server
yarn dev

# Add package
yarn add <package>
yarn add -D <package>  # dev dependency
```

## 🎨 Assets & Frontend

```bash
# Build assets (after Vue/CSS changes)
yarn build

# Watch for changes
yarn dev

# Check build
ls -la public/build/
```

## 🔧 Laravel Commands

```bash
# Code formatting (before commit)
vendor/bin/pint

# Run tests
php artisan test

# View all routes
php artisan route:list

# View API routes only
php artisan route:list | grep "api/v1"

# View admin routes
php artisan route:list | grep "admin\."

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## 📖 API Documentation

```bash
# Export OpenAPI schema
php artisan scramble:export

# View in browser
http://localhost/docs/api

# Get JSON
http://localhost/docs/api.json
```

## 🏗️ Controller Structure

### API Controllers (Scramble documented)
```
app/Http/Controllers/Api/
├── Admin/      # Admin API
├── Auth/       # Authentication API
├── Billing/    # Billing API
└── Tenant/     # Tenant API
```

### Web Controllers (Inertia.js)
```
app/Http/Controllers/Web/
├── Admin/      # Admin UI
├── Tenant/     # Tenant UI
├── DemoController.php
└── HomeController.php
```

## 📝 Creating New Controllers

### For API (with Scramble docs)
```bash
# 1. Create controller
php artisan make:controller Api/YourModule/YourController

# 2. Add to namespace
namespace App\Http\Controllers\Api\YourModule;

# 3. Add PHPDoc tag
/**
 * @tags Your Module
 */
class YourController extends Controller

# 4. Add method docs
/**
 * Your method description
 *
 * @response array{data: object}
 */

# 5. Register in routes/api.php
```

### For Web (with Inertia)
```bash
# 1. Create controller
php artisan make:controller Web/YourModule/YourController

# 2. Add to namespace
namespace App\Http\Controllers\Web\YourModule;

# 3. Return Inertia view
return Inertia::render('Module/ViewName', $data);

# 4. Register in routes/web.php
```

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter TestName

# With coverage
php artisan test --coverage
```

## 🗄️ Database

```bash
# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Fresh migrate with seed
php artisan migrate:fresh --seed

# Tinker
php artisan tinker
```

## 🚀 Development Workflow

```bash
# 1. Pull latest
git pull

# 2. Install/update dependencies
composer install
yarn install

# 3. Build assets
yarn build

# 4. Clear cache
php artisan cache:clear
php artisan config:clear

# 5. Run migrations
php artisan migrate

# 6. Start dev server
php artisan serve
# In another terminal:
yarn dev
```

## 📊 Route Naming Convention

```
API:     api.{module}.{resource}.{action}
Web:     {module}.{resource}.{action}

Examples:
api.tenant.users.index
api.admin.plans.store
admin.tenants.edit
tenant.users.create
```

## 🎯 Important Files

```
config/scramble.php          # API docs config
routes/api.php               # API routes
routes/web.php               # Web routes
resources/js/Pages/          # Inertia views
app/Http/Controllers/Api/    # API controllers
app/Http/Controllers/Web/    # Web controllers
```

## 🔍 Debugging

```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear everything
php artisan optimize:clear

# Check environment
php artisan about

# List service providers
php artisan about --only=providers
```

## 📚 Documentation Links

- [CONTROLLER_STRUCTURE.md](CONTROLLER_STRUCTURE.md) - Controller organization
- [docs/ADMIN_ROUTES_VERIFICATION.md](docs/ADMIN_ROUTES_VERIFICATION.md) - Admin routes
- [docs/LOGIN_PAGE_FIX.md](docs/LOGIN_PAGE_FIX.md) - Login troubleshooting
- [README.md](README.md) - Full documentation
- [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) - Complete summary

## ⚡ Quick Commands Cheat Sheet

```bash
# Most used commands
yarn build                    # Build assets
vendor/bin/pint              # Format code
php artisan route:list       # View routes
php artisan scramble:export  # Export API docs
php artisan test             # Run tests
```

## 🎨 Admin Routes Quick Access

```
/login                    # Login page
/register                 # Register page
/dashboard                # Tenant dashboard
/admin                    # Admin dashboard
/admin/tenants            # Manage tenants
/admin/plans              # Manage plans
/admin/subscriptions      # View subscriptions
/admin/analytics          # Analytics
/docs/api                 # API documentation
```

---

**Remember: Always use YARN for this project! 🧶**
