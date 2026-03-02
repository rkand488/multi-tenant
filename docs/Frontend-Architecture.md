# Frontend Architecture

> **Status:** Production-Ready Design
> **Stack:** Laravel 12 · Inertia.js v2 · Vue 3 (Composition API) · Vite · Yarn · TailwindCSS v4 · Pinia

---

## 1. Overview

The frontend is a **server-driven SPA** built with Inertia.js. There is no separate API for the dashboard UI — Laravel handles routing and sends props directly into Vue page components. The browser receives a full HTML document on first load (enabling SEO and fast TTFB), and subsequent navigations are XHR-driven with JSON responses, giving a fluid single-page experience.

```
Browser ──► Laravel Route ──► Controller ──► Inertia::render('Page', $props)
                                                          │
                                          ┌───────────────▼───────────────┐
                                          │  Vue 3 Page Component          │
                                          │  resources/js/Pages/...        │
                                          │  receives props as defineProps  │
                                          └───────────────────────────────┘
```

### Why Inertia.js over a separate SPA / API?

| Concern | Separate Vue SPA + API | Inertia.js (chosen) |
|---|---|---|
| Auth complexity | JWT / Sanctum SPA tokens required | Session cookies, same CSRF tower |
| Code duplication | Validation rules duplicated client + server | Server validates, errors passed as props |
| SEO / TTFB | Requires SSR setup | First render is server-rendered HTML |
| Routing maintenance | Two route definitions | Single Laravel route file |
| Deployment | Two deployable artefacts | One Laravel app |

---

## 2. Package Setup

### Install Dependencies with Yarn

```bash
# Core Inertia packages
yarn add @inertiajs/vue3

# Vue 3 + build tooling
yarn add --dev vue @vitejs/plugin-vue

# State management
yarn add pinia

# UI primitives (Headless UI)
yarn add @headlessui/vue @heroicons/vue

# Animation
yarn add @vueuse/core

# Form handling
yarn add vee-validate yup

# Date utilities
yarn add dayjs

# HTTP client (wraps axios, already present)
yarn add axios
```

### Updated `package.json`

```json
{
  "$schema": "https://www.schemastore.org/package.json",
  "private": true,
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  },
  "dependencies": {
    "@headlessui/vue": "^1.7.23",
    "@heroicons/vue": "^2.2.0",
    "@inertiajs/vue3": "^2.0.0",
    "@vueuse/core": "^13.0.0",
    "axios": "^1.11.0",
    "dayjs": "^1.11.13",
    "pinia": "^3.0.0",
    "vee-validate": "^4.15.0",
    "vue": "^3.5.0",
    "yup": "^1.6.0"
  },
  "devDependencies": {
    "@tailwindcss/vite": "^4.0.0",
    "@vitejs/plugin-vue": "^5.2.0",
    "concurrently": "^9.0.1",
    "laravel-vite-plugin": "^2.0.0",
    "tailwindcss": "^4.0.0",
    "vite": "^7.0.7"
  }
}
```

### Updated `vite.config.js`

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['vue', '@inertiajs/vue3', 'pinia'],
                    ui: ['@headlessui/vue', '@heroicons/vue'],
                },
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
```

---

## 3. Inertia Bootstrap

### `resources/js/app.js`

```javascript
import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'SaaS Platform';

createInertiaApp({
    title: (title) => `${title} — ${appName}`,

    // Lazy-load pages for automatic code splitting
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),

    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .mount(el);
    },

    progress: {
        color: '#6366f1',   // indigo-500 — matches brand
        delay: 100,
        includeCSS: true,
        showSpinner: false,
    },
});
```

### `resources/js/bootstrap.js`

```javascript
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
```

### Laravel Blade Entry Point

`resources/views/app.blade.php`

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="antialiased bg-gray-50 dark:bg-gray-900">
    @inertia
</body>
</html>
```

---

## 4. Folder Structure

```
resources/js/
├── app.js                          # Inertia + Pinia bootstrap
├── bootstrap.js                    # Axios configuration
│
├── Pages/                          # Inertia page components (one per route)
│   ├── Auth/
│   │   ├── Login.vue
│   │   ├── Register.vue
│   │   ├── VerifyEmail.vue
│   │   ├── ForgotPassword.vue
│   │   └── ResetPassword.vue
│   │
│   ├── Admin/                      # Super-Admin panel pages
│   │   ├── Dashboard.vue
│   │   ├── Tenants/
│   │   │   ├── Index.vue
│   │   │   ├── Show.vue
│   │   │   └── Create.vue
│   │   ├── Plans/
│   │   │   ├── Index.vue
│   │   │   ├── Show.vue
│   │   │   └── Create.vue
│   │   ├── Analytics/
│   │   │   └── Index.vue
│   │   └── Billing/
│   │       └── Index.vue
│   │
│   └── Tenant/                     # Tenant dashboard pages
│       ├── Dashboard.vue
│       ├── Users/
│       │   ├── Index.vue
│       │   ├── Show.vue
│       │   └── Invite.vue
│       ├── Roles/
│       │   ├── Index.vue
│       │   └── Show.vue
│       ├── Subscription/
│       │   └── Index.vue
│       ├── Settings/
│       │   └── Index.vue
│       └── ActivityLog/
│           └── Index.vue
│
├── Layouts/                        # Persistent layout wrappers
│   ├── AppLayout.vue               # Shared authenticated shell
│   ├── AdminLayout.vue             # Super-admin sidebar + nav
│   ├── TenantLayout.vue            # Tenant workspace sidebar + nav
│   └── GuestLayout.vue             # Auth pages (login, register, etc.)
│
├── Components/                     # Reusable UI components
│   ├── Base/                       # Atomic building blocks
│   │   ├── AppButton.vue
│   │   ├── AppInput.vue
│   │   ├── AppSelect.vue
│   │   ├── AppTextarea.vue
│   │   ├── AppCheckbox.vue
│   │   ├── AppBadge.vue
│   │   ├── AppAvatar.vue
│   │   └── AppSpinner.vue
│   │
│   ├── Layout/                     # Structural layout pieces
│   │   ├── Sidebar.vue
│   │   ├── Topbar.vue
│   │   ├── PageHeader.vue
│   │   └── Breadcrumbs.vue
│   │
│   ├── Data/                       # Data display components
│   │   ├── DataTable.vue
│   │   ├── TablePagination.vue
│   │   ├── StatsCard.vue
│   │   └── EmptyState.vue
│   │
│   ├── Overlays/                   # Modal / sheet components
│   │   ├── Modal.vue
│   │   ├── ConfirmDialog.vue
│   │   ├── SlideOver.vue
│   │   └── Dropdown.vue
│   │
│   ├── Forms/                      # Form-level composable components
│   │   ├── FormGroup.vue
│   │   ├── FormError.vue
│   │   └── SearchInput.vue
│   │
│   └── Feedback/                   # User feedback
│       ├── Alert.vue
│       ├── Toast.vue
│       └── ToastContainer.vue
│
├── Composables/                    # Reusable Vue Composition API logic
│   ├── useAuth.js
│   ├── useTenant.js
│   ├── useToast.js
│   ├── useConfirm.js
│   ├── useForm.js                  # Thin wrapper around Inertia useForm
│   ├── usePagination.js
│   ├── usePermission.js
│   └── useDarkMode.js
│
├── Stores/                         # Pinia stores
│   ├── auth.js
│   ├── tenant.js
│   ├── notifications.js
│   └── subscription.js
│
├── Services/                       # API service layer (axios wrappers)
│   ├── api.js                      # Base axios instance
│   ├── authService.js
│   ├── tenantService.js
│   └── subscriptionService.js
│
└── Utils/                          # Pure helper functions
    ├── formatters.js               # Date, currency, number formatting
    ├── validators.js               # Shared Yup schemas
    ├── constants.js                # Enums / magic strings
    └── helpers.js                  # Misc pure utilities
```

---

## 5. Layout System

Layouts wrap page components persistently. Inertia v2 uses `defineOptions({ layout })` inside each page to specify which layout to use.

### `resources/js/Layouts/AppLayout.vue`

```vue
<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Layout/Sidebar.vue';
import Topbar from '@/Components/Layout/Topbar.vue';
import ToastContainer from '@/Components/Feedback/ToastContainer.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-gray-100 dark:bg-gray-900">
        <Sidebar />

        <div class="flex flex-1 flex-col overflow-hidden">
            <Topbar :user="user" />

            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>

        <ToastContainer />
    </div>
</template>
```

### `resources/js/Layouts/AdminLayout.vue`

```vue
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const adminNav = [
    { label: 'Dashboard',  href: route('admin.dashboard'),           icon: 'HomeIcon' },
    { label: 'Tenants',    href: route('admin.tenants.index'),        icon: 'BuildingOfficeIcon' },
    { label: 'Plans',      href: route('admin.plans.index'),          icon: 'CreditCardIcon' },
    { label: 'Analytics',  href: route('admin.analytics.index'),      icon: 'ChartBarIcon' },
    { label: 'Billing',    href: route('admin.billing.index'),        icon: 'BanknotesIcon' },
];
</script>

<template>
    <AppLayout :nav-items="adminNav">
        <slot />
    </AppLayout>
</template>
```

### `resources/js/Layouts/TenantLayout.vue`

```vue
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const tenantNav = [
    { label: 'Dashboard',    href: route('tenant.dashboard'),          icon: 'HomeIcon' },
    { label: 'Users',        href: route('tenant.users.index'),         icon: 'UsersIcon' },
    { label: 'Roles',        href: route('tenant.roles.index'),         icon: 'ShieldCheckIcon' },
    { label: 'Subscription', href: route('tenant.subscription.index'),  icon: 'CreditCardIcon' },
    { label: 'Settings',     href: route('tenant.settings.index'),      icon: 'Cog6ToothIcon' },
    { label: 'Activity Log', href: route('tenant.activity-log.index'),  icon: 'ClipboardDocumentListIcon' },
];
</script>

<template>
    <AppLayout :nav-items="tenantNav">
        <slot />
    </AppLayout>
</template>
```

### Assigning a Layout to a Page

```vue
<!-- resources/js/Pages/Tenant/Dashboard.vue -->
<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';

defineOptions({ layout: TenantLayout });

defineProps({
    stats: Object,
    recentActivity: Array,
});
</script>

<template>
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
        <!-- page content -->
    </div>
</template>
```

---

## 6. Admin vs Tenant Separation

### Page Namespacing

All Super-Admin pages live under `resources/js/Pages/Admin/` and all tenant pages live under `resources/js/Pages/Tenant/`. Inertia resolves these from the controller:

```php
// Super-admin controller
return Inertia::render('Admin/Dashboard', $props);

// Tenant controller
return Inertia::render('Tenant/Dashboard', $props);
```

### Middleware Guards

Two separate middleware groups prevent cross-context access at the routing layer (see [Frontend-Routing.md](Frontend-Routing.md) for full details).

### Shared Global Props

The `HandleInertiaRequests` middleware shares context-aware props:

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user'        => $request->user()?->only('id', 'name', 'email', 'avatar'),
            'permissions' => $request->user()?->getAllPermissions()->pluck('name'),
            'roles'       => $request->user()?->getRoleNames(),
        ],
        'tenant' => $request->user()?->tenant?->only('id', 'name', 'slug', 'logo'),
        'flash'  => [
            'success' => $request->session()->get('success'),
            'error'   => $request->session()->get('error'),
        ],
    ];
}
```

---

## 7. Performance Strategy

### Code Splitting

Vite automatically code-splits at the page level via `import.meta.glob('./Pages/**/*.vue')` with dynamic imports. Each page is its own async chunk.

Additional manual chunks are defined in `vite.config.js` to separate vendor libraries from application code:

```javascript
manualChunks: {
    vendor: ['vue', '@inertiajs/vue3', 'pinia'],
    ui:     ['@headlessui/vue', '@heroicons/vue'],
},
```

### Lazy-Loaded Pages

Because `resolvePageComponent` uses dynamic glob imports, every page is lazy-loaded on first visit. Subsequent visits to the same page reuse the cached module.

### Optimised API Calls

- Inertia `only` for partial reloads — request only the props needed by the component refreshing:

```javascript
router.reload({ only: ['stats'] });
```

- Debounce search inputs before triggering Inertia visits:

```javascript
// Composables/useDebouncedSearch.js
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';

export function useDebouncedSearch(routeName, extraParams = {}) {
    const search = ref('');

    const trigger = useDebounceFn(() => {
        router.get(
            route(routeName),
            { search: search.value, ...extraParams },
            { preserveState: true, replace: true },
        );
    }, 300);

    watch(search, trigger);

    return { search };
}
```

- Eager load only necessary relationships in controllers — avoid passing megabytes of JSON as props.

### Asset Optimisation

- TailwindCSS v4 purges unused classes at build time.
- `vite build` minifies and fingerprints all assets.
- Use `@vueuse/core`'s `useIntersectionObserver` for lazy rendering of off-screen data tables or charts.

---

## 8. Dark Mode

TailwindCSS v4 dark mode is controlled via the `class` strategy. The `useDarkMode` composable persists the preference to `localStorage` and toggles the `dark` class on `<html>`:

```javascript
// resources/js/Composables/useDarkMode.js
import { ref, watchEffect } from 'vue';

const isDark = ref(localStorage.getItem('theme') === 'dark');

watchEffect(() => {
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
});

export function useDarkMode() {
    const toggle = () => { isDark.value = !isDark.value; };
    return { isDark, toggle };
}
```

---

## 9. Environment Variables

All frontend environment variables must be prefixed with `VITE_` to be exposed by Vite:

```dotenv
VITE_APP_NAME="SaaS Platform"
VITE_APP_ENV=production
```

Access in JavaScript:

```javascript
const appName = import.meta.env.VITE_APP_NAME;
```

Never expose sensitive values (API keys, DB credentials) via `VITE_` variables — they are inlined into the public bundle.

---

## Related Documentation

- [Frontend-Routing.md](Frontend-Routing.md) — Laravel route definitions and Inertia rendering patterns
- [State-Management.md](State-Management.md) — Pinia store definitions
- [UI-Components.md](UI-Components.md) — Component API reference
- [SaaS-Architecture.md](SaaS-Architecture.md) — Full system design
