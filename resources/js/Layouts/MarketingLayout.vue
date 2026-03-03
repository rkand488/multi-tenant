<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Bars3Icon, XMarkIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import FooterSection from '@/Components/Marketing/FooterSection.vue';

// ── Scroll state ──────────────────────────────────────────────────────────────

const isScrolled = ref(false);
const mobileMenuOpen = ref(false);

// Anchor links — scroll-to sections live in the page slot
const navLinks = [
    { label: 'Features', href: '#features' },
    { label: 'Product', href: '#screenshots' },
    { label: 'Pricing', href: '#pricing' },
    { label: 'Docs', href: '#' },
];

function handleScroll() {
    isScrolled.value = window.scrollY > 60;
}

function handleNavClick(href) {
    mobileMenuOpen.value = false;

    if (!href.startsWith('#')) {
        return;
    }

    const el = document.querySelector(href);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="min-h-screen bg-white font-sans text-gray-900 antialiased">

        <!-- ════════════════════════════════════════════════════════════════════
             NAVBAR
        ════════════════════════════════════════════════════════════════════ -->
        <header
            class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
            :class="isScrolled
                ? 'bg-white/90 shadow-sm backdrop-blur-md border-b border-gray-100'
                : 'bg-transparent'"
        >
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">

                <!-- Logo -->
                <a href="/" class="flex items-center">
                    <img src="/logo.png" alt="Tenantrix" class="h-10" />
                </a>

                <!-- Desktop nav links -->
                <nav class="hidden items-center gap-8 md:flex" aria-label="Main navigation">
                    <a
                        v-for="link in navLinks"
                        :key="link.href"
                        :href="link.href"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900"
                        @click.prevent="handleNavClick(link.href)"
                    >
                        {{ link.label }}
                    </a>
                </nav>

                <!-- Desktop auth actions -->
                <div class="hidden items-center gap-3 md:flex">
                    <Link
                        :href="route('login')"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900"
                    >
                        Login
                    </Link>
                    <Link
                        :href="route('register')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow-md active:scale-95"
                    >
                        Get Started
                        <ArrowRightIcon class="size-3.5" />
                    </Link>
                </div>

                <!-- Mobile hamburger -->
                <button
                    class="rounded-lg p-2 text-gray-600 transition-colors hover:bg-gray-100 md:hidden"
                    :aria-expanded="mobileMenuOpen"
                    aria-label="Toggle navigation menu"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <XMarkIcon v-if="mobileMenuOpen" class="size-5" />
                    <Bars3Icon v-else class="size-5" />
                </button>
            </div>

            <!-- ── Mobile slide-down menu ──────────────────────────── -->
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div
                    v-if="mobileMenuOpen"
                    class="border-t border-gray-100 bg-white px-6 pb-5 pt-4 shadow-md md:hidden"
                >
                    <nav class="flex flex-col gap-1" aria-label="Mobile navigation">
                        <a
                            v-for="link in navLinks"
                            :key="link.href"
                            :href="link.href"
                            class="rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                            @click.prevent="handleNavClick(link.href)"
                        >
                            {{ link.label }}
                        </a>
                    </nav>

                    <div class="mt-4 flex flex-col gap-2 border-t border-gray-100 pt-4">
                        <Link
                            :href="route('login')"
                            class="rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                            @click="mobileMenuOpen = false"
                        >
                            Login
                        </Link>
                        <Link
                            :href="route('register')"
                            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition-colors hover:bg-indigo-700"
                            @click="mobileMenuOpen = false"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
            </Transition>
        </header>

        <!-- ── Page content ─────────────────────────────────────────────────── -->
        <slot />

        <!-- FOOTER -->
        <FooterSection />

    </div>
</template>

<style>
html {
    scroll-behavior: smooth;
}
</style>
