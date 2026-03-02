# UI Components

> **Design system:** TailwindCSS v4 · Headless UI v1 · Composition API
> **Convention:** All base components are prefixed `App` and live under `resources/js/Components/`.

---

## 1. Design Tokens

TailwindCSS v4 uses CSS custom properties for theming. Define tokens in `resources/css/app.css`:

```css
@import "tailwindcss";

@theme {
    /* Brand palette */
    --color-brand-50:  #eef2ff;
    --color-brand-100: #e0e7ff;
    --color-brand-500: #6366f1;
    --color-brand-600: #4f46e5;
    --color-brand-700: #4338ca;

    /* Semantic */
    --color-success: #22c55e;
    --color-warning: #f59e0b;
    --color-danger:  #ef4444;
    --color-info:    #3b82f6;

    /* Typography */
    --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;

    /* Radius */
    --radius-card: 0.75rem;
}
```

---

## 2. Base Components

### AppButton

**File:** `resources/js/Components/Base/AppButton.vue`

```vue
<script setup>
defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'secondary', 'danger', 'ghost', 'link'].includes(v),
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['xs', 'sm', 'md', 'lg'].includes(v),
    },
    loading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    type:    { type: String,  default: 'button' },
});

const variantClasses = {
    primary:   'bg-brand-600 text-white hover:bg-brand-700 focus-visible:ring-brand-500',
    secondary: 'bg-white text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50',
    danger:    'bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-500',
    ghost:     'text-gray-600 hover:bg-gray-100',
    link:      'text-brand-600 underline hover:text-brand-700',
};

const sizeClasses = {
    xs: 'px-2 py-1 text-xs rounded',
    sm: 'px-3 py-1.5 text-sm rounded-md',
    md: 'px-4 py-2 text-sm rounded-md',
    lg: 'px-5 py-2.5 text-base rounded-lg',
};
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        class="inline-flex items-center justify-center gap-2 font-medium transition-colors
               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2
               disabled:opacity-50 disabled:cursor-not-allowed"
        :class="[variantClasses[variant], sizeClasses[size]]"
    >
        <svg v-if="loading" class="animate-spin size-4" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
        </svg>
        <slot />
    </button>
</template>
```

**Usage:**

```vue
<AppButton variant="primary" :loading="form.processing" @click="submit">
    Save Changes
</AppButton>

<AppButton variant="danger" size="sm">Delete</AppButton>
```

---

### AppInput

**File:** `resources/js/Components/Base/AppInput.vue`

```vue
<script setup>
defineProps({
    modelValue: { type: [String, Number], default: '' },
    label:      { type: String, default: '' },
    error:      { type: String, default: '' },
    placeholder:{ type: String, default: '' },
    type:       { type: String, default: 'text' },
    disabled:   { type: Boolean, default: false },
    required:   { type: Boolean, default: false },
    id:         { type: String, required: true },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="space-y-1">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </label>

        <input
            :id="id"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            class="block w-full rounded-md border px-3 py-2 text-sm shadow-sm
                   transition-colors placeholder:text-gray-400
                   focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500
                   disabled:bg-gray-50 disabled:text-gray-500"
            :class="error
                ? 'border-red-400 text-red-900 focus:ring-red-500 focus:border-red-500'
                : 'border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100'"
            @input="$emit('update:modelValue', $event.target.value)"
        />

        <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
    </div>
</template>
```

---

### AppSelect

**File:** `resources/js/Components/Base/AppSelect.vue`

```vue
<script setup>
defineProps({
    modelValue: { type: [String, Number, null], default: null },
    options:    { type: Array, required: true }, // [{ value, label }]
    label:      { type: String, default: '' },
    error:      { type: String, default: '' },
    placeholder:{ type: String, default: 'Select an option' },
    id:         { type: String, required: true },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="space-y-1">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ label }}
        </label>

        <select
            :id="id"
            :value="modelValue"
            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2
                   text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500
                   dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
            :class="{ 'border-red-400': error }"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option value="" disabled>{{ placeholder }}</option>
            <option
                v-for="opt in options"
                :key="opt.value"
                :value="opt.value"
            >{{ opt.label }}</option>
        </select>

        <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
    </div>
</template>
```

---

### AppBadge

**File:** `resources/js/Components/Base/AppBadge.vue`

```vue
<script setup>
defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'success', 'warning', 'danger', 'info'].includes(v),
    },
    dot: { type: Boolean, default: false },
});

const classes = {
    default: 'bg-gray-100 text-gray-700',
    success: 'bg-green-100 text-green-700',
    warning: 'bg-yellow-100 text-yellow-700',
    danger:  'bg-red-100 text-red-700',
    info:    'bg-blue-100 text-blue-700',
};

const dotColors = {
    default: 'bg-gray-400',
    success: 'bg-green-500',
    warning: 'bg-yellow-500',
    danger:  'bg-red-500',
    info:    'bg-blue-500',
};
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
        :class="classes[variant]"
    >
        <span v-if="dot" class="size-1.5 rounded-full" :class="dotColors[variant]" />
        <slot />
    </span>
</template>
```

---

## 3. Data Display Components

### DataTable

A generic, slot-based table supporting sorting, pagination, and empty states.

**File:** `resources/js/Components/Data/DataTable.vue`

```vue
<script setup>
import TablePagination from '@/Components/Data/TablePagination.vue';
import EmptyState from '@/Components/Data/EmptyState.vue';

defineProps({
    columns: {
        type: Array,
        required: true,
        // [{ key, label, sortable?, class? }]
    },
    rows:    { type: Array,   required: true },
    meta:    { type: Object,  default: null },  // Laravel paginator meta
    loading: { type: Boolean, default: false },
    sortKey: { type: String,  default: '' },
    sortDir: { type: String,  default: 'asc' },
});

defineEmits(['sort', 'page-change']);
</script>

<template>
    <div class="overflow-hidden rounded-card shadow ring-1 ring-gray-200 dark:ring-gray-700">
        <!-- Loading overlay -->
        <div v-if="loading" class="flex items-center justify-center py-12 text-gray-400">
            <svg class="animate-spin size-6" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
            </svg>
        </div>

        <template v-else>
            <!-- Empty state -->
            <EmptyState v-if="rows.length === 0">
                <slot name="empty">No records found.</slot>
            </EmptyState>

            <table v-else class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            class="px-4 py-3 text-left text-xs font-semibold uppercase
                                   tracking-wide text-gray-500 dark:text-gray-400"
                            :class="col.class"
                        >
                            <button
                                v-if="col.sortable"
                                class="inline-flex items-center gap-1 hover:text-gray-900"
                                @click="$emit('sort', col.key)"
                            >
                                {{ col.label }}
                                <span class="text-gray-400">
                                    {{ sortKey === col.key ? (sortDir === 'asc' ? '↑' : '↓') : '↕' }}
                                </span>
                            </button>
                            <span v-else>{{ col.label }}</span>
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-900">
                    <tr
                        v-for="(row, idx) in rows"
                        :key="idx"
                        class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >
                        <slot name="row" :row="row" :index="idx" />
                    </tr>
                </tbody>
            </table>

            <TablePagination v-if="meta" :meta="meta" @change="$emit('page-change', $event)" />
        </template>
    </div>
</template>
```

**Usage:**

```vue
<DataTable
    :columns="[
        { key: 'name',       label: 'Name',   sortable: true },
        { key: 'email',      label: 'Email' },
        { key: 'created_at', label: 'Joined', sortable: true },
        { key: 'actions',    label: '' },
    ]"
    :rows="users.data"
    :meta="users.meta"
    :loading="isLoading"
    :sort-key="sortKey"
    :sort-dir="sortDir"
    @sort="handleSort"
    @page-change="handlePageChange"
>
    <template #row="{ row }">
        <td class="px-4 py-3 text-sm">{{ row.name }}</td>
        <td class="px-4 py-3 text-sm text-gray-500">{{ row.email }}</td>
        <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(row.created_at) }}</td>
        <td class="px-4 py-3 text-sm">
            <AppButton size="xs" variant="ghost">Edit</AppButton>
        </td>
    </template>
</DataTable>
```

---

### StatsCard

**File:** `resources/js/Components/Data/StatsCard.vue`

```vue
<script setup>
defineProps({
    label:  { type: String, required: true },
    value:  { type: [String, Number], required: true },
    change: { type: String, default: null },   // e.g. "+12%"
    trend:  { type: String, default: null, validator: (v) => ['up', 'down', 'flat'].includes(v) },
    icon:   { type: Object, default: null },   // Heroicon component
});
</script>

<template>
    <div class="rounded-card bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ label }}</p>
            <component v-if="icon" :is="icon" class="size-5 text-gray-400" />
        </div>

        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ value }}</p>

        <p v-if="change" class="mt-1 flex items-center gap-1 text-sm"
           :class="{
               'text-green-600': trend === 'up',
               'text-red-600':   trend === 'down',
               'text-gray-500':  trend === 'flat' || !trend,
           }"
        >
            {{ change }} <span class="text-gray-400">vs last period</span>
        </p>
    </div>
</template>
```

---

## 4. Overlay Components

### Modal

Built on Headless UI's `Dialog` for accessible, focus-trapped modals.

**File:** `resources/js/Components/Overlays/Modal.vue`

```vue
<script setup>
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';

defineProps({
    show:        { type: Boolean, required: true },
    title:       { type: String,  default: '' },
    maxWidth:    { type: String,  default: 'lg', validator: (v) => ['sm', 'md', 'lg', 'xl', '2xl'].includes(v) },
    closeable:   { type: Boolean, default: true },
});

defineEmits(['close']);

const maxWidthClass = {
    sm:  'sm:max-w-sm',
    md:  'sm:max-w-md',
    lg:  'sm:max-w-lg',
    xl:  'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
};
</script>

<template>
    <TransitionRoot appear :show="show" as="template">
        <Dialog as="div" class="relative z-50" @close="closeable && $emit('close')">
            <!-- Backdrop -->
            <TransitionChild
                enter="ease-out duration-200" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-150" leave-from="opacity-100" leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" />
            </TransitionChild>

            <!-- Panel -->
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <TransitionChild
                    enter="ease-out duration-200" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100"
                    leave="ease-in duration-150" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95"
                >
                    <DialogPanel
                        class="w-full rounded-xl bg-white shadow-xl dark:bg-gray-800"
                        :class="maxWidthClass[maxWidth]"
                    >
                        <div v-if="title || $slots.title" class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <DialogTitle class="text-base font-semibold text-gray-900 dark:text-white">
                                <slot name="title">{{ title }}</slot>
                            </DialogTitle>
                            <button
                                v-if="closeable"
                                class="rounded-md p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                @click="$emit('close')"
                            >
                                <span class="sr-only">Close</span>
                                <svg class="size-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                                </svg>
                            </button>
                        </div>

                        <div class="px-6 py-5">
                            <slot />
                        </div>

                        <div v-if="$slots.footer" class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                            <slot name="footer" />
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
```

---

### ConfirmDialog

**File:** `resources/js/Components/Overlays/ConfirmDialog.vue`

```vue
<script setup>
import Modal from '@/Components/Overlays/Modal.vue';
import AppButton from '@/Components/Base/AppButton.vue';

defineProps({
    show:    { type: Boolean,  required: true },
    title:   { type: String,   default: 'Are you sure?' },
    message: { type: String,   default: 'This action cannot be undone.' },
    danger:  { type: Boolean,  default: false },
    loading: { type: Boolean,  default: false },
});

defineEmits(['confirm', 'cancel']);
</script>

<template>
    <Modal :show="show" max-width="sm" @close="$emit('cancel')">
        <template #title>{{ title }}</template>

        <p class="text-sm text-gray-600 dark:text-gray-400">{{ message }}</p>

        <template #footer>
            <AppButton variant="secondary" @click="$emit('cancel')">Cancel</AppButton>
            <AppButton
                :variant="danger ? 'danger' : 'primary'"
                :loading="loading"
                @click="$emit('confirm')"
            >
                Confirm
            </AppButton>
        </template>
    </Modal>
</template>
```

**Usage with the `useConfirm` composable:**

```javascript
// resources/js/Composables/useConfirm.js
import { ref } from 'vue';

const isOpen    = ref(false);
const isLoading = ref(false);
let resolver    = null;

export function useConfirm() {
    const confirm = () =>
        new Promise((resolve) => {
            isOpen.value = true;
            resolver = resolve;
        });

    const onConfirm = () => { resolver(true);  isOpen.value = false; };
    const onCancel  = () => { resolver(false); isOpen.value = false; };

    return { isOpen, isLoading, confirm, onConfirm, onCancel };
}
```

---

### Dropdown

**File:** `resources/js/Components/Overlays/Dropdown.vue`

```vue
<script setup>
import {
    Menu,
    MenuButton,
    MenuItems,
    MenuItem,
    TransitionRoot,
} from '@headlessui/vue';

defineProps({
    align: { type: String, default: 'right', validator: (v) => ['left', 'right'].includes(v) },
});
</script>

<template>
    <Menu as="div" class="relative">
        <MenuButton as="template">
            <slot name="trigger" />
        </MenuButton>

        <TransitionRoot
            enter="transition ease-out duration-100"
            enter-from="transform opacity-0 scale-95"
            enter-to="transform opacity-100 scale-100"
            leave="transition ease-in duration-75"
            leave-from="transform opacity-100 scale-100"
            leave-to="transform opacity-0 scale-95"
        >
            <MenuItems
                class="absolute z-10 mt-2 w-48 rounded-lg bg-white py-1 shadow-lg ring-1 ring-gray-200
                       focus:outline-none dark:bg-gray-800 dark:ring-gray-700"
                :class="align === 'right' ? 'right-0' : 'left-0'"
            >
                <slot />
            </MenuItems>
        </TransitionRoot>
    </Menu>
</template>
```

---

## 5. Form Components

### FormGroup

**File:** `resources/js/Components/Forms/FormGroup.vue`

```vue
<script setup>
defineProps({
    label:    { type: String, default: '' },
    for:      { type: String, default: '' },
    error:    { type: String, default: '' },
    helpText: { type: String, default: '' },
    required: { type: Boolean, default: false },
});
</script>

<template>
    <div class="space-y-1">
        <label v-if="label" :for="for" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <slot />

        <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
        <p v-else-if="helpText" class="text-xs text-gray-500">{{ helpText }}</p>
    </div>
</template>
```

---

## 6. Feedback Components

### Alert

**File:** `resources/js/Components/Feedback/Alert.vue`

```vue
<script setup>
defineProps({
    variant: {
        type: String,
        default: 'info',
        validator: (v) => ['success', 'warning', 'danger', 'info'].includes(v),
    },
    dismissible: { type: Boolean, default: false },
});

defineEmits(['dismiss']);

const styles = {
    success: 'bg-green-50 text-green-800 border-green-200',
    warning: 'bg-yellow-50 text-yellow-800 border-yellow-200',
    danger:  'bg-red-50 text-red-800 border-red-200',
    info:    'bg-blue-50 text-blue-800 border-blue-200',
};
</script>

<template>
    <div
        class="flex items-start gap-3 rounded-lg border p-4 text-sm"
        :class="styles[variant]"
        role="alert"
    >
        <slot name="icon" />
        <div class="flex-1"><slot /></div>
        <button v-if="dismissible" class="text-current opacity-60 hover:opacity-100" @click="$emit('dismiss')">
            <span class="sr-only">Dismiss</span>×
        </button>
    </div>
</template>
```

### Toast / ToastContainer

Toasts are driven by the `useToast` composable and rendered in `ToastContainer` which is mounted once in `AppLayout`.

```javascript
// resources/js/Composables/useToast.js
import { ref } from 'vue';

const toasts = ref([]);
let nextId = 0;

export function useToast() {
    const add = (message, { variant = 'info', duration = 4000 } = {}) => {
        const id = ++nextId;
        toasts.value.push({ id, message, variant });

        if (duration > 0) {
            setTimeout(() => remove(id), duration);
        }
    };

    const remove = (id) => {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    };

    const success = (msg) => add(msg, { variant: 'success' });
    const error   = (msg) => add(msg, { variant: 'danger' });
    const warning = (msg) => add(msg, { variant: 'warning' });

    return { toasts, add, remove, success, error, warning };
}
```

```vue
<!-- resources/js/Components/Feedback/ToastContainer.vue -->
<script setup>
import { TransitionGroup } from 'vue';
import Alert from '@/Components/Feedback/Alert.vue';
import { useToast } from '@/Composables/useToast';

const { toasts, remove } = useToast();
</script>

<template>
    <div class="fixed right-4 top-4 z-[9999] flex flex-col gap-3" aria-live="polite">
        <TransitionGroup
            enter-active-class="transition-all duration-300"
            enter-from-class="translate-x-full opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition-all duration-200"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="translate-x-full opacity-0"
        >
            <Alert
                v-for="toast in toasts"
                :key="toast.id"
                :variant="toast.variant"
                dismissible
                class="w-80 shadow-lg"
                @dismiss="remove(toast.id)"
            >
                {{ toast.message }}
            </Alert>
        </TransitionGroup>
    </div>
</template>
```

---

## 7. TablePagination

**File:** `resources/js/Components/Data/TablePagination.vue`

```vue
<script setup>
defineProps({
    meta: {
        type: Object,
        required: true,
        // Laravel paginator meta: { current_page, last_page, from, to, total }
    },
});

defineEmits(['change']);
</script>

<template>
    <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 dark:border-gray-700">
        <p class="text-sm text-gray-500">
            Showing <span class="font-medium">{{ meta.from }}</span>
            to <span class="font-medium">{{ meta.to }}</span>
            of <span class="font-medium">{{ meta.total }}</span> results
        </p>

        <div class="flex gap-1">
            <button
                class="rounded px-2.5 py-1.5 text-sm font-medium disabled:opacity-40"
                :class="meta.current_page > 1 ? 'hover:bg-gray-100 text-gray-700' : 'cursor-not-allowed text-gray-400'"
                :disabled="meta.current_page === 1"
                @click="$emit('change', meta.current_page - 1)"
            >
                Previous
            </button>

            <button
                v-for="page in meta.last_page"
                :key="page"
                class="rounded px-2.5 py-1.5 text-sm font-medium"
                :class="page === meta.current_page
                    ? 'bg-brand-600 text-white'
                    : 'hover:bg-gray-100 text-gray-700'"
                @click="$emit('change', page)"
            >{{ page }}</button>

            <button
                class="rounded px-2.5 py-1.5 text-sm font-medium disabled:opacity-40"
                :class="meta.current_page < meta.last_page ? 'hover:bg-gray-100 text-gray-700' : 'cursor-not-allowed text-gray-400'"
                :disabled="meta.current_page === meta.last_page"
                @click="$emit('change', meta.current_page + 1)"
            >
                Next
            </button>
        </div>
    </div>
</template>
```

---

## 8. Authentication Pages

### Login

```vue
<!-- resources/js/Pages/Auth/Login.vue -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AppInput from '@/Components/Base/AppInput.vue';
import AppButton from '@/Components/Base/AppButton.vue';
import Alert from '@/Components/Feedback/Alert.vue';
import { useForm } from '@inertiajs/vue3';

defineOptions({ layout: GuestLayout });

defineProps({ status: String });

const form = useForm({
    email:     '',
    password:  '',
    remember:  false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="w-full max-w-md space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sign in</h1>
            <p class="mt-1 text-sm text-gray-500">to continue to your workspace</p>
        </div>

        <Alert v-if="status" variant="success">{{ status }}</Alert>

        <form class="space-y-4" @submit.prevent="submit">
            <AppInput
                id="email"
                v-model="form.email"
                type="email"
                label="Email address"
                :error="form.errors.email"
                required
            />

            <AppInput
                id="password"
                v-model="form.password"
                type="password"
                label="Password"
                :error="form.errors.password"
                required
            />

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.remember" type="checkbox" class="rounded border-gray-300" />
                    Remember me
                </label>
                <a :href="route('password.request')" class="text-sm text-brand-600 hover:text-brand-700">
                    Forgot password?
                </a>
            </div>

            <AppButton type="submit" variant="primary" class="w-full" :loading="form.processing">
                Sign in
            </AppButton>
        </form>

        <p class="text-center text-sm text-gray-500">
            Don't have an account?
            <a :href="route('register')" class="text-brand-600 hover:text-brand-700">Sign up</a>
        </p>
    </div>
</template>
```

---

## 9. Component Registration

Register base components globally to avoid repetitive imports:

```javascript
// resources/js/app.js  (add after createApp)
import AppButton   from '@/Components/Base/AppButton.vue';
import AppInput    from '@/Components/Base/AppInput.vue';
import AppSelect   from '@/Components/Base/AppSelect.vue';
import AppBadge    from '@/Components/Base/AppBadge.vue';
import AppSpinner  from '@/Components/Base/AppSpinner.vue';

const app = createApp({ render: () => h(App, props) });

// Global base components
const globals = { AppButton, AppInput, AppSelect, AppBadge, AppSpinner };
Object.entries(globals).forEach(([name, component]) => app.component(name, component));
```

Larger components (Modal, DataTable, etc.) remain locally imported to preserve code-splitting benefits.

---

## Related Documentation

- [Frontend-Architecture.md](Frontend-Architecture.md) — Folder structure, layouts, Inertia bootstrap
- [Frontend-Routing.md](Frontend-Routing.md) — Route definitions and controller patterns
- [State-Management.md](State-Management.md) — Pinia stores
