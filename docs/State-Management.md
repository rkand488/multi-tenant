# State Management

> **Library:** Pinia v3
> **Pattern:** Composition-API stores (not Options API stores)
> **Persistence:** `localStorage` via `@vueuse/core`'s `useStorage` where appropriate

---

## 1. Overview

[Pinia](https://pinia.vuejs.org/) is the official Vue 3 state management library. It replaces Vuex with a simpler, type-safe, Composition-API-friendly API.

### When to Use Pinia vs Inertia Shared Props

| Data type | Where it lives | Why |
|---|---|---|
| Authenticated user, tenant, flash messages | Inertia shared props (`usePage().props`) | Single source of truth — server-authoritative |
| UI state (sidebar open, dark mode, active tab) | Local `ref()` in composable or component | No serialisation needed |
| Notification queue (toasts) | Pinia `notifications` store | Cross-component, ephemeral |
| Subscription plan details | Pinia `subscription` store (hydrated from shared props) | Accessed by many components, avoids prop drilling |
| Auth computed helpers (`can()`, `is()`) | Pinia `auth` store (mirrors `usePage().props.auth`) | Reactive computed access throughout app |

---

## 2. Setup

Pinia is registered in `app.js`:

```javascript
import { createPinia } from 'pinia';

createInertiaApp({
    // ...
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .mount(el);
    },
});
```

No additional plugin configuration is required for basic usage. For SSR (future consideration), use `createPinia()` per request.

---

## 3. Auth Store

The auth store mirrors the `usePage().props.auth` shared props and exposes computed permission helpers, avoiding repeated `usePage()` calls throughout the codebase.

**File:** `resources/js/Stores/auth.js`

```javascript
import { defineStore } from 'pinia';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export const useAuthStore = defineStore('auth', () => {
    const page = usePage();

    // Reactive references to Inertia shared props
    const user        = computed(() => page.props.auth?.user   ?? null);
    const permissions = computed(() => page.props.auth?.permissions ?? []);
    const roles       = computed(() => page.props.auth?.roles  ?? []);

    // Computed helpers
    const isAuthenticated = computed(() => user.value !== null);
    const isSuperAdmin    = computed(() => roles.value.includes('super-admin'));

    /**
     * Check if the current user has a given permission.
     */
    const can = (permission) => permissions.value.includes(permission);

    /**
     * Check if the current user has any of the listed permissions.
     */
    const canAny = (...perms) => perms.some((p) => can(p));

    /**
     * Check if the current user has a given role.
     */
    const hasRole = (role) => roles.value.includes(role);

    return {
        user,
        permissions,
        roles,
        isAuthenticated,
        isSuperAdmin,
        can,
        canAny,
        hasRole,
    };
});
```

**Usage:**

```vue
<script setup>
import { useAuthStore } from '@/Stores/auth';

const auth = useAuthStore();
</script>

<template>
    <div>
        <p>Welcome, {{ auth.user.name }}</p>

        <AppButton
            v-if="auth.can('users.invite')"
            @click="openInviteModal"
        >
            Invite User
        </AppButton>

        <span v-if="auth.isSuperAdmin" class="text-xs text-purple-500">Super Admin</span>
    </div>
</template>
```

---

## 4. Tenant Store

Hydrated from Inertia shared props on mount. Exposes tenant metadata and subscription status helpers consumed by the sidebar, subscription banner, and feature-flag guards.

**File:** `resources/js/Stores/tenant.js`

```javascript
import { defineStore } from 'pinia';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export const useTenantStore = defineStore('tenant', () => {
    const page = usePage();

    const tenant = computed(() => page.props.tenant ?? null);

    const name   = computed(() => tenant.value?.name   ?? '');
    const slug   = computed(() => tenant.value?.slug   ?? '');
    const logo   = computed(() => tenant.value?.logo   ?? null);

    const subscriptionStatus = computed(() =>
        tenant.value?.subscription_status ?? 'inactive'
    );

    const isActive    = computed(() => subscriptionStatus.value === 'active');
    const isTrialing  = computed(() => subscriptionStatus.value === 'trialing');
    const isCancelled = computed(() => subscriptionStatus.value === 'cancelled');
    const isSuspended = computed(() => subscriptionStatus.value === 'suspended');

    /** Returns true if the tenant can access paid features */
    const canAccess = computed(() => isActive.value || isTrialing.value);

    return {
        tenant,
        name,
        slug,
        logo,
        subscriptionStatus,
        isActive,
        isTrialing,
        isCancelled,
        isSuspended,
        canAccess,
    };
});
```

**Usage:**

```vue
<script setup>
import { useTenantStore } from '@/Stores/tenant';

const tenant = useTenantStore();
</script>

<template>
    <!-- Subscription warning banner -->
    <Alert v-if="tenant.isCancelled" variant="warning">
        Your subscription has been cancelled. Some features may be restricted.
    </Alert>

    <!-- Feature gate -->
    <div v-if="tenant.canAccess">
        ... premium feature content ...
    </div>
</template>
```

---

## 5. Notifications Store

Manages the in-app notification feed (bell icon in the topbar). Data is fetched from a lightweight JSON endpoint on mount and polled periodically.

**File:** `resources/js/Stores/notifications.js`

```javascript
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useNotificationsStore = defineStore('notifications', () => {
    const items      = ref([]);
    const isLoading  = ref(false);
    const pollHandle = ref(null);

    const unreadCount = computed(() =>
        items.value.filter((n) => !n.read_at).length
    );

    const hasUnread = computed(() => unreadCount.value > 0);

    /**
     * Fetch notifications from the server.
     */
    const fetch = async () => {
        isLoading.value = true;

        try {
            const { data } = await axios.get('/api/notifications');
            items.value = data.data;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Mark a single notification as read.
     */
    const markRead = async (id) => {
        await axios.patch(`/api/notifications/${id}/read`);
        const notification = items.value.find((n) => n.id === id);
        if (notification) {
            notification.read_at = new Date().toISOString();
        }
    };

    /**
     * Mark all notifications as read.
     */
    const markAllRead = async () => {
        await axios.post('/api/notifications/read-all');
        items.value.forEach((n) => {
            n.read_at = n.read_at ?? new Date().toISOString();
        });
    };

    /**
     * Remove a notification from the list.
     */
    const dismiss = (id) => {
        items.value = items.value.filter((n) => n.id !== id);
    };

    /**
     * Start background polling every 60 seconds.
     */
    const startPolling = (intervalMs = 60_000) => {
        stopPolling();
        pollHandle.value = setInterval(fetch, intervalMs);
    };

    const stopPolling = () => {
        if (pollHandle.value) {
            clearInterval(pollHandle.value);
            pollHandle.value = null;
        }
    };

    return {
        items,
        isLoading,
        unreadCount,
        hasUnread,
        fetch,
        markRead,
        markAllRead,
        dismiss,
        startPolling,
        stopPolling,
    };
});
```

**Initialisation in `AppLayout.vue`:**

```javascript
import { useNotificationsStore } from '@/Stores/notifications';
import { onMounted, onUnmounted } from 'vue';

const notifications = useNotificationsStore();

onMounted(() => {
    notifications.fetch();
    notifications.startPolling();
});

onUnmounted(() => {
    notifications.stopPolling();
});
```

---

## 6. Subscription Store

Tracks the current tenant's subscription plan. Hydrated from Inertia shared props but may be refreshed after a plan change without a full page reload.

**File:** `resources/js/Stores/subscription.js`

```javascript
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

export const useSubscriptionStore = defineStore('subscription', () => {
    const page = usePage();

    // Hydrate from shared props initially
    const plan  = ref(page.props.subscription?.plan   ?? null);
    const usage = ref(page.props.subscription?.usage  ?? {});
    const limits= ref(page.props.subscription?.limits ?? {});

    const planName    = computed(() => plan.value?.name ?? 'Free');
    const isFreePlan  = computed(() => plan.value?.is_free ?? true);
    const isTrial     = computed(() => plan.value?.is_trial ?? false);
    const trialEndsAt = computed(() => plan.value?.trial_ends_at ?? null);

    /**
     * Check a named feature limit.
     * E.g. isAtLimit('users') — true if user count >= plan limit.
     */
    const isAtLimit = (feature) => {
        const used  = usage.value[feature]  ?? 0;
        const limit = limits.value[feature] ?? Infinity;
        return used >= limit;
    };

    /**
     * Return usage percentage for a feature (0–100).
     */
    const usagePercent = (feature) => {
        const used  = usage.value[feature]  ?? 0;
        const limit = limits.value[feature];
        if (!limit) { return 0; }
        return Math.min(Math.round((used / limit) * 100), 100);
    };

    /**
     * Refresh subscription data from the server (e.g. after a plan change).
     */
    const refresh = async () => {
        const { data } = await axios.get('/api/subscription');
        plan.value   = data.plan;
        usage.value  = data.usage;
        limits.value = data.limits;
    };

    return {
        plan,
        usage,
        limits,
        planName,
        isFreePlan,
        isTrial,
        trialEndsAt,
        isAtLimit,
        usagePercent,
        refresh,
    };
});
```

**Usage — feature gate component:**

```vue
<script setup>
import { useSubscriptionStore } from '@/Stores/subscription';

const subscription = useSubscriptionStore();
</script>

<template>
    <div>
        <!-- Usage bar -->
        <div class="space-y-1">
            <p class="text-sm font-medium">
                Users: {{ subscription.usage.users }} / {{ subscription.limits.users }}
            </p>
            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                <div
                    class="h-2 rounded-full transition-all"
                    :class="subscription.usagePercent('users') >= 90 ? 'bg-red-500' : 'bg-brand-500'"
                    :style="{ width: `${subscription.usagePercent('users')}%` }"
                />
            </div>
        </div>

        <!-- At-limit warning -->
        <Alert v-if="subscription.isAtLimit('users')" variant="warning">
            You've reached the user limit on your plan.
            <a :href="route('tenant.subscription.index')">Upgrade</a> to add more.
        </Alert>
    </div>
</template>
```

---

## 7. Using Stores in Composables

Stores compose naturally with other composables:

```javascript
// resources/js/Composables/usePermission.js
import { useAuthStore }   from '@/Stores/auth';
import { useTenantStore } from '@/Stores/tenant';

export function usePermission() {
    const auth   = useAuthStore();
    const tenant = useTenantStore();

    /**
     * Returns true if the user can do something AND the tenant has access.
     */
    const canDo = (permission) => {
        if (!tenant.canAccess) { return false; }
        return auth.can(permission);
    };

    return { can: auth.can, canAny: auth.canAny, canDo };
}
```

---

## 8. Hydration from Flash Messages

The `notifications` store can also be seeded from Inertia flash props, so server-triggered events (e.g. successful save) immediately surface as toasts:

```javascript
// resources/js/Composables/useFlash.js
import { watchEffect } from 'vue';
import { usePage }     from '@inertiajs/vue3';
import { useToast }    from '@/Composables/useToast';

export function useFlash() {
    const page  = usePage();
    const toast = useToast();

    watchEffect(() => {
        const flash = page.props.flash;
        if (flash?.success) { toast.success(flash.success); }
        if (flash?.error)   { toast.error(flash.error);     }
        if (flash?.warning) { toast.warning(flash.warning); }
    });
}
```

Mount once in `AppLayout.vue`:

```javascript
import { useFlash } from '@/Composables/useFlash';
useFlash();
```

---

## 9. Store Interaction Diagram

```
Inertia shared props (server)
        │
        ├──► useAuthStore         → can(), hasRole(), isSuperAdmin
        ├──► useTenantStore       → canAccess, subscriptionStatus
        └──► useSubscriptionStore → isAtLimit(), usagePercent()

API requests (axios)
        │
        └──► useNotificationsStore → items, unreadCount, markRead()

Ephemeral UI events
        │
        └──► useToast (composable) → success(), error(), warning()
             useFlash (composable) → bridges flash props → toast
```

---

## 10. Store Naming & File Conventions

| Store | File | Export |
|---|---|---|
| Auth | `Stores/auth.js` | `useAuthStore` |
| Tenant | `Stores/tenant.js` | `useTenantStore` |
| Notifications | `Stores/notifications.js` | `useNotificationsStore` |
| Subscription | `Stores/subscription.js` | `useSubscriptionStore` |

Rules:
- Store IDs match the file basename (`'auth'`, `'tenant'`, etc.).
- Stores use the Composition API style (`defineStore(id, () => { ... })`), never Options API.
- Stores do not import each other directly; provide composables (`usePermission`, `useFlash`) when cross-store logic is needed.

---

## Related Documentation

- [Frontend-Architecture.md](Frontend-Architecture.md) — App bootstrap, Pinia setup
- [Frontend-Routing.md](Frontend-Routing.md) — How server data reaches stores via shared props
- [UI-Components.md](UI-Components.md) — Components that consume stores
