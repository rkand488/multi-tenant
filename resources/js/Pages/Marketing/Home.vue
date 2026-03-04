<script setup>
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import {
    ShieldCheckIcon,
    ChartBarIcon,
    CodeBracketIcon,
    ArrowRightIcon,
    BoltIcon,
    GlobeAltIcon,
} from '@heroicons/vue/24/outline';
import HeroSection from '@/Components/Marketing/HeroSection.vue';
import FeaturesSection from '@/Components/Marketing/FeaturesSection.vue';
import ProductScreensSection from '@/Components/Marketing/ProductScreensSection.vue';
import PricingSection from '@/Components/Marketing/PricingSection.vue';
import TestimonialsSection from '@/Components/Marketing/TestimonialsSection.vue';
import FAQSection from '@/Components/Marketing/FAQSection.vue';

defineOptions({ layout: MarketingLayout });

// ── Scroll-triggered animation ─────────────────────────────────────────────

function setupObserver() {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1 },
    );

    document.querySelectorAll('[data-animate]').forEach((el) => {
        observer.observe(el);
    });
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────

onMounted(() => {
    setupObserver();
});
</script>

<template>
    <Head title="Modern Multi-Tenant SaaS Platform" />

        <main>

            <HeroSection />

            <!-- ════════════════════════════════════════════════════════════════
                 TRUSTED BY
            ════════════════════════════════════════════════════════════════ -->
            <section class="border-y border-gray-100 bg-gray-50 py-10">
                <div class="mx-auto max-w-7xl px-6">
                    <p class="mb-7 text-center text-xs font-semibold uppercase tracking-widest text-gray-400">
                        Trusted by teams building SaaS products worldwide
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-8 sm:gap-14">
                        <div v-for="company in ['Flowbase', 'Stackly', 'Crisp Analytics', 'Devhub', 'Launchify', 'Orbit']" :key="company"
                            class="text-sm font-bold tracking-tight text-gray-300 transition-colors hover:text-gray-500"
                        >
                            {{ company }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- KEY FEATURES -->
            <FeaturesSection />

            <!-- PRODUCT SCREENSHOTS -->
            <ProductScreensSection />

            <!-- PRICING -->
            <PricingSection />

            <!-- TESTIMONIALS -->
            <TestimonialsSection />

            <!-- ════════════════════════════════════════════════════════════════
                 DEVELOPER SECTION
            ════════════════════════════════════════════════════════════════ -->
            <section class="py-24 sm:py-32">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="grid items-center gap-16 lg:grid-cols-2">

                        <!-- Text -->
                        <div data-animate>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-indigo-600">Built for Developers</p>
                            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                                A solid Laravel foundation you can extend
                            </h2>
                            <p class="mt-4 text-lg leading-relaxed text-gray-500">
                                Production-ready architecture so you ship faster without cutting corners.
                            </p>

                            <ul class="mt-8 space-y-4">
                                <li v-for="item in [
                                    { icon: BoltIcon, text: 'Clean Laravel 12 architecture with service layer pattern' },
                                    { icon: GlobeAltIcon, text: 'Inertia.js — no separate API needed for your frontend' },
                                    { icon: CodeBracketIcon, text: 'Versioned RESTful API with per-tenant Sanctum tokens' },
                                    { icon: ShieldCheckIcon, text: 'Fine-grained RBAC ready to extend with custom roles' },
                                    { icon: ChartBarIcon, text: 'Full Pest test suite included with factories and seeders' },
                                ]" :key="item.text"
                                    class="flex items-start gap-3"
                                >
                                    <div class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                                        <component :is="item.icon" class="size-4 text-indigo-600" />
                                    </div>
                                    <span class="text-sm text-gray-600">{{ item.text }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Code block -->
                        <div data-animate data-animate-delay="150">
                            <div class="overflow-hidden rounded-2xl shadow-xl">
                                <!-- Editor chrome -->
                                <div class="flex h-9 items-center gap-2 bg-gray-800 px-4">
                                    <span class="size-2.5 rounded-full bg-red-500/60" />
                                    <span class="size-2.5 rounded-full bg-amber-500/60" />
                                    <span class="size-2.5 rounded-full bg-emerald-500/60" />
                                    <span class="ml-3 text-xs text-gray-500">routes/api.php</span>
                                </div>
                                <!-- Code -->
                                <div class="bg-gray-900 p-5 text-xs leading-relaxed">
                                    <pre class="overflow-x-auto font-mono"><code><span class="text-indigo-400">Route</span><span class="text-gray-300">::prefix(</span><span class="text-emerald-400">'v1'</span><span class="text-gray-300">)</span>
    <span class="text-gray-300">->middleware([</span><span class="text-emerald-400">'auth:sanctum'</span><span class="text-gray-300">,</span> <span class="text-emerald-400">'tenant'</span><span class="text-gray-300">])</span>
    <span class="text-gray-300">->group(</span><span class="text-violet-400">function</span><span class="text-gray-300"> () {</span>

    <span class="text-indigo-400">Route</span><span class="text-gray-300">::apiResource(</span><span class="text-emerald-400">'users'</span><span class="text-gray-300">,</span>
        <span class="text-amber-400">UserController</span><span class="text-gray-300">::</span><span class="text-violet-400">class</span><span class="text-gray-300">);</span>

    <span class="text-indigo-400">Route</span><span class="text-gray-300">::get(</span><span class="text-emerald-400">'analytics/overview'</span><span class="text-gray-300">,</span>
        <span class="text-amber-400">AnalyticsController</span><span class="text-gray-300">::</span><span class="text-violet-400">class</span><span class="text-gray-300">);</span>

    <span class="text-indigo-400">Route</span><span class="text-gray-300">::get(</span><span class="text-emerald-400">'subscription'</span><span class="text-gray-300">,</span>
        <span class="text-amber-400">SubscriptionController</span><span class="text-gray-300">::</span><span class="text-violet-400">class</span><span class="text-gray-300">);</span>
<span class="text-gray-300">});</span></code></pre>
                                </div>

                                <!-- Integration logos -->
                                <div class="flex flex-wrap items-center gap-4 border-t border-gray-700/60 bg-gray-800/80 px-5 py-4">
                                    <span v-for="tech in ['Stripe', 'AWS S3', 'Mailgun', 'GitHub', 'Slack']" :key="tech"
                                        class="rounded-full border border-gray-600 bg-gray-700/60 px-3 py-1 text-xs font-semibold text-gray-300"
                                    >
                                        {{ tech }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- FAQ -->
            <FAQSection />

            <!-- ════════════════════════════════════════════════════════════════
                 CTA SECTION
            ════════════════════════════════════════════════════════════════ -->
            <section class="relative overflow-hidden py-24 sm:py-32">
                <!-- Background gradient -->
                <div class="absolute inset-0 -z-10 bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-700" />
                <!-- Dot pattern overlay -->
                <div class="absolute inset-0 -z-10 opacity-10"
                    style="background-image: radial-gradient(circle, rgba(255,255,255,0.4) 1px, transparent 1px); background-size: 24px 24px;"
                />

                <div class="mx-auto max-w-4xl px-6 text-center" data-animate>
                    <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-5xl">
                        Start building your SaaS today.
                    </h2>
                    <p class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-indigo-200">
                        Get started in minutes. No credit card required. Fully production-ready infrastructure out of the box.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                        <Link
                            :href="route('register')"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 text-sm font-bold text-indigo-600 shadow-lg transition-all hover:bg-indigo-50 hover:shadow-xl active:scale-95"
                        >
                            Create Free Account
                            <ArrowRightIcon class="size-4" />
                        </Link>
                        <Link
                            :href="route('demo.admin')"
                            class="inline-flex items-center gap-2 rounded-xl border-2 border-white/30 px-8 py-4 text-sm font-bold text-white backdrop-blur transition-all hover:border-white/60 hover:bg-white/10"
                        >
                            Try Demo
                        </Link>
                        <a
                            href="mailto:sales@yoursaas.com"
                            class="inline-flex items-center gap-2 rounded-xl border-2 border-white/30 px-8 py-4 text-sm font-bold text-white backdrop-blur transition-all hover:border-white/60 hover:bg-white/10"
                        >
                            Talk to Sales
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-indigo-300">
                        Trusted by 500+ teams · No credit card required · Cancel anytime
                    </p>
                </div>
            </section>

        </main>
</template>

<style>
[data-animate] {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.55s ease, transform 0.55s ease;
}
[data-animate].animate-in {
    opacity: 1;
    transform: translateY(0);
}
[data-animate-delay="100"] { transition-delay: 0.10s; }
[data-animate-delay="150"] { transition-delay: 0.15s; }
[data-animate-delay="200"] { transition-delay: 0.20s; }
[data-animate-delay="250"] { transition-delay: 0.25s; }
[data-animate-delay="300"] { transition-delay: 0.30s; }
[data-animate-delay="350"] { transition-delay: 0.35s; }
[data-animate-delay="400"] { transition-delay: 0.40s; }
[data-animate-delay="450"] { transition-delay: 0.45s; }
</style>
