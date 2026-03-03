<script setup>
// Minimal centred layout used for login, register, forgot-password, etc.
</script>

<template>
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden px-4">
        <!-- Gradient Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary-900 via-primary-800 to-accent-600"></div>

        <!-- Animated background pattern -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(37,99,235,0.1),transparent_50%),radial-gradient(circle_at_70%_80%,rgba(6,182,212,0.1),transparent_50%)]"></div>

        <!-- Content -->
        <div class="relative z-10 w-full max-w-6xl mx-auto">
            <!-- Brand mark -->
            <a href="/" class="mb-8 flex items-center justify-center">
                <img src="/logo.png" alt="Tenantrix" class="h-12" />
            </a>

            <!-- Card -->
            <div class="mx-auto w-full max-w-xl overflow-hidden rounded-2xl bg-white/95 backdrop-blur-sm shadow-2xl ring-1 ring-white/20">
                <!-- Brand gradient top strip -->
                <div class="h-1.5 w-full bg-gradient-primary" />
                <div class="px-10 py-12">
                    <slot />
                </div>
            </div>

            <!-- Footer -->
            <p class="mt-8 text-center text-sm text-white/50">
                &copy; {{ new Date().getFullYear() }} {{ $page.props.appName ?? 'Tenantrix' }}. All rights reserved.
            </p>
        </div>
    </div>
</template>
