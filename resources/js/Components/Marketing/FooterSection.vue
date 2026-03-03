<script setup>
/**
 * FooterSection — SaaS marketing footer.
 *
 * Four link columns: Product · Developers · Company · Resources
 * Brand column with tagline + social strip.
 * Bottom bar: copyright + legal links.
 */

const linkColumns = [
    {
        heading: 'Product',
        links: [
            { label: 'Features',   href: '#features'     },
            { label: 'Pricing',    href: '#pricing'      },
            { label: 'Changelog',  href: '#'             },
            { label: 'Roadmap',    href: '#'             },
            { label: 'Status',     href: '#'             },
        ],
    },
    {
        heading: 'Developers',
        links: [
            { label: 'API Reference',   href: '#api'          },
            { label: 'Documentation',   href: '#'             },
            { label: 'GitHub',          href: 'https://github.com', external: true },
            { label: 'Integrations',    href: '#'             },
            { label: 'Webhooks',        href: '#'             },
        ],
    },
    {
        heading: 'Company',
        links: [
            { label: 'About',    href: '#' },
            { label: 'Blog',     href: '#' },
            { label: 'Careers',  href: '#' },
            { label: 'Press',    href: '#' },
            { label: 'Contact',  href: 'mailto:hello@saas.example.com' },
        ],
    },
    {
        heading: 'Resources',
        links: [
            { label: 'Documentation', href: '#'                                 },
            { label: 'API',           href: '#api'                              },
            { label: 'GitHub',        href: 'https://github.com', external: true },
            { label: 'Contact',       href: 'mailto:hello@saas.example.com'     },
            { label: 'Community',     href: '#'                                 },
        ],
    },
];

const socialLinks = [
    {
        label: 'GitHub',
        href: 'https://github.com',
        svg: `<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/></svg>`,
    },
    {
        label: 'Twitter / X',
        href: 'https://twitter.com',
        svg: `<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>`,
    },
    {
        label: 'LinkedIn',
        href: 'https://linkedin.com',
        svg: `<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>`,
    },
];
</script>

<template>
    <footer class="bg-gray-950 text-gray-400">

        <!-- ── Main grid ──────────────────────────────────────────────────────── -->
        <div class="mx-auto max-w-7xl px-6 pb-10 pt-16">
            <div class="grid gap-12 lg:grid-cols-6">

                <!-- Brand column -->
                <div class="lg:col-span-2">
                    <a href="/" class="mb-5 inline-block">
                        <img src="/logo-with-tag.png" alt="Tenantrix" class="h-12" />
                    </a>

                    <p class="max-w-xs text-sm leading-relaxed text-gray-500">
                        The complete, production-ready infrastructure for building and scaling multi-tenant SaaS products.
                    </p>

                    <!-- Social links -->
                    <div class="mt-6 flex items-center gap-4">
                        <a
                            v-for="social in socialLinks"
                            :key="social.label"
                            :href="social.href"
                            target="_blank"
                            rel="noopener noreferrer"
                            :aria-label="social.label"
                            class="flex size-8 items-center justify-center rounded-lg bg-gray-800 text-gray-500 transition-colors hover:bg-gray-700 hover:text-white"
                        >
                            <!-- eslint-disable-next-line vue/no-v-html -->
                            <span class="size-4" v-html="social.svg" />
                        </a>
                    </div>

                    <!-- Quick access badges -->
                    <div class="mt-6 flex flex-wrap gap-2">
                        <a
                            href="https://github.com"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-800 bg-gray-900 px-3 py-1 text-xs font-medium text-gray-400 transition-colors hover:border-primary-500/60 hover:text-white"
                        >
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
                            </svg>
                            GitHub
                        </a>
                        <a
                            href="#"
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-800 bg-gray-900 px-3 py-1 text-xs font-medium text-gray-400 transition-colors hover:border-primary-500/60 hover:text-white"
                        >
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                            </svg>
                            Docs
                        </a>
                        <a
                            href="#api"
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-800 bg-gray-900 px-3 py-1 text-xs font-medium text-gray-400 transition-colors hover:border-primary-500/60 hover:text-white"
                        >
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/>
                            </svg>
                            API
                        </a>
                        <a
                            href="mailto:hello@saas.example.com"
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-800 bg-gray-900 px-3 py-1 text-xs font-medium text-gray-400 transition-colors hover:border-primary-500/60 hover:text-white"
                        >
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                            </svg>
                            Contact
                        </a>
                    </div>
                </div>

                <!-- Link columns -->
                <div class="grid grid-cols-2 gap-8 sm:grid-cols-4 lg:col-span-4">
                    <div v-for="col in linkColumns" :key="col.heading">
                        <h4 class="mb-4 text-xs font-semibold uppercase tracking-widest text-gray-500">
                            {{ col.heading }}
                        </h4>
                        <ul class="space-y-3">
                            <li v-for="link in col.links" :key="link.label">
                                <a
                                    :href="link.href"
                                    v-bind="link.external ? { target: '_blank', rel: 'noopener noreferrer' } : {}"
                                    class="inline-flex items-center gap-1 text-sm text-gray-500 transition-colors hover:text-white"
                                >
                                    {{ link.label }}
                                    <svg
                                        v-if="link.external"
                                        class="size-3 opacity-50"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- ── Newsletter strip ──────────────────────────────────────────── -->
            <div class="mt-14 rounded-2xl border border-gray-800 bg-gray-900/60 p-6 sm:flex sm:items-center sm:justify-between sm:gap-8">
                <div class="mb-4 sm:mb-0">
                    <p class="text-sm font-semibold text-white">Stay in the loop</p>
                    <p class="mt-0.5 text-xs text-gray-500">Get updates on new features, releases, and insights.</p>
                </div>
                <form class="flex min-w-0 flex-1 items-center gap-2 sm:max-w-sm" @submit.prevent>
                    <input
                        type="email"
                        placeholder="you@company.com"
                        class="min-w-0 flex-1 rounded-lg border border-gray-700 bg-gray-800 px-4 py-2 text-sm text-white placeholder-gray-600 outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/30"
                    />
                    <button
                        type="submit"
                        class="shrink-0 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-500 active:scale-95"
                    >
                        Subscribe
                    </button>
                </form>
            </div>

            <!-- ── Divider ────────────────────────────────────────────────────── -->
            <div class="mt-10 border-t border-gray-800 pt-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <p class="text-xs text-gray-600">
                        &copy; {{ new Date().getFullYear() }} SaaS Platform, Inc. All rights reserved.
                    </p>
                    <div class="flex flex-wrap justify-center gap-x-6 gap-y-2">
                        <a
                            v-for="link in ['Privacy Policy', 'Terms of Service', 'Cookie Policy', 'Security']"
                            :key="link"
                            href="#"
                            class="text-xs text-gray-600 transition-colors hover:text-gray-400"
                        >
                            {{ link }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>
