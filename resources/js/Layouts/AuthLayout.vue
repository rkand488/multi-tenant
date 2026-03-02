<script setup>
// Minimal centred layout used for login, register, forgot-password, etc.
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-4 dark:bg-gray-900">

        <!-- Brand mark -->
        <a href="/" class="mb-8 flex items-center gap-2">
            <div class="flex size-10 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white shadow-md">
                S
            </div>
            <span class="text-xl font-bold text-gray-900 dark:text-white">
                {{ $page.props.appName ?? 'SaaS Platform' }}
            </span>
        </a>

        <!-- Card -->
        <div class="w-full max-w-sm rounded-2xl bg-white px-8 py-8 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
            <slot />
        </div>

        <!-- Footer -->
        <p class="mt-8 text-xs text-gray-400 dark:text-gray-500">
            &copy; {{ new Date().getFullYear() }} SaaS Platform. All rights reserved.
        </p>
    </div>
</template>
