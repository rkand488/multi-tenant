<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { CheckCircleIcon, MinusIcon } from '@heroicons/vue/20/solid';
import { BoltIcon, BuildingOffice2Icon, SparklesIcon } from '@heroicons/vue/24/outline';

// ── Billing toggle ─────────────────────────────────────────────────────────────

const isAnnual = ref(false);

// ── Plans ──────────────────────────────────────────────────────────────────────

const plans = [
    {
        id: 'starter',
        name: 'Starter',
        icon: BoltIcon,
        monthlyPrice: 0,
        annualPrice: 0,
        description: 'Everything you need to get started. No credit card required.',
        highlight: false,
        cta: 'Get Started Free',
        ctaHref: 'register',
        badge: null,
        features: [
            { text: '1 tenant workspace', included: true },
            { text: 'Up to 5 team members', included: true },
            { text: '500 MB storage', included: true },
            { text: 'Basic analytics', included: true },
            { text: 'Email support (48 h)', included: true },
            { text: 'API access', included: false },
            { text: 'Custom domain', included: false },
            { text: 'Role & permission management', included: false },
            { text: 'Priority support', included: false },
        ],
    },
    {
        id: 'pro',
        name: 'Pro',
        icon: SparklesIcon,
        monthlyPrice: 49,
        annualPrice: 39,
        description: 'For growing teams that need power, flexibility, and speed.',
        highlight: true,
        cta: 'Start 14-day Free Trial',
        ctaHref: 'register',
        badge: 'Most Popular',
        features: [
            { text: '10 tenant workspaces', included: true },
            { text: 'Unlimited team members', included: true },
            { text: '50 GB storage', included: true },
            { text: 'Advanced analytics', included: true },
            { text: 'Priority support (4 h)', included: true },
            { text: 'Full API access', included: true },
            { text: 'Custom domain per tenant', included: true },
            { text: 'Role & permission management', included: true },
            { text: 'Audit log (90 days)', included: false },
        ],
    },
    {
        id: 'enterprise',
        name: 'Enterprise',
        icon: BuildingOffice2Icon,
        monthlyPrice: null,
        annualPrice: null,
        description: 'Custom infrastructure, SLAs, and white-glove onboarding for scale.',
        highlight: false,
        cta: 'Contact Sales',
        ctaHref: null,
        badge: null,
        features: [
            { text: 'Unlimited tenant workspaces', included: true },
            { text: 'Unlimited team members', included: true },
            { text: 'Dedicated infrastructure', included: true },
            { text: 'Custom analytics & exports', included: true },
            { text: '24/7 dedicated support', included: true },
            { text: 'Full API access', included: true },
            { text: 'Custom domain + branded portal', included: true },
            { text: 'Granular RBAC + SSO / SAML', included: true },
            { text: 'Audit log (unlimited)', included: true },
        ],
    },
];

// ── Computed helpers ───────────────────────────────────────────────────────────

function displayPrice(plan) {
    if (plan.monthlyPrice === null) { return 'Custom'; }
    if (plan.monthlyPrice === 0) { return 'Free'; }
    return `$${isAnnual.value ? plan.annualPrice : plan.monthlyPrice}`;
}

function priceSuffix(plan) {
    if (plan.monthlyPrice === null || plan.monthlyPrice === 0) { return ''; }
    return isAnnual.value ? '/mo · billed annually' : '/month';
}

const annualSaving = computed(() => {
    const pro = plans.find((p) => p.id === 'pro');
    return (pro.monthlyPrice - pro.annualPrice) * 12;
});

// ── Scroll animation ───────────────────────────────────────────────────────────

onMounted(() => {
    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    io.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1 },
    );
    document.querySelectorAll('[data-price-animate]').forEach((el) => io.observe(el));
});
</script>

<template>
    <section id="pricing" class="relative overflow-hidden bg-white py-24 sm:py-32">

        <!-- Faint radial glow behind the highlighted card -->
        <div class="pointer-events-none absolute inset-0 flex items-start justify-center">
            <div class="h-[600px] w-[800px] rounded-full bg-indigo-100/60 blur-3xl" />
        </div>

        <div class="relative mx-auto max-w-7xl px-6">

            <!-- Heading ──────────────────────────────────────────────────── -->
            <div class="mx-auto mb-14 max-w-2xl text-center" data-price-animate>
                <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-indigo-600">Pricing</p>
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Simple, transparent pricing
                </h2>
                <p class="mt-4 text-lg text-gray-500">
                    Start free. Upgrade as you grow. No hidden fees, ever.
                </p>

                <!-- Monthly / Annual toggle -->
                <div class="mt-8 inline-flex items-center gap-1 rounded-xl border border-gray-200 bg-gray-100 p-1 shadow-inner">
                    <button
                        class="rounded-lg px-5 py-2 text-sm font-semibold transition-all"
                        :class="!isAnnual ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        @click="isAnnual = false"
                    >
                        Monthly
                    </button>
                    <button
                        class="flex items-center gap-2 rounded-lg px-5 py-2 text-sm font-semibold transition-all"
                        :class="isAnnual ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        @click="isAnnual = true"
                    >
                        Annual
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">
                            Save ${{ annualSaving }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Plan cards ────────────────────────────────────────────────── -->
            <div class="grid items-end gap-6 lg:grid-cols-3">

                <div
                    v-for="(plan, idx) in plans"
                    :key="plan.id"
                    data-price-animate
                    :style="{ transitionDelay: (idx + 1) * 80 + 'ms' }"
                    class="price-card relative flex flex-col rounded-2xl transition-all duration-300"
                    :class="plan.highlight
                        ? 'bg-gradient-to-b from-indigo-600 to-indigo-700 text-white shadow-2xl shadow-indigo-900/40 ring-2 ring-indigo-400 lg:-translate-y-4'
                        : 'border border-gray-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1'"
                >

                    <!-- Most Popular badge -->
                    <div v-if="plan.badge" class="absolute -top-4 inset-x-0 flex justify-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-violet-500 to-indigo-500 px-4 py-1 text-xs font-bold text-white shadow-md shadow-violet-900/30">
                            <SparklesIcon class="size-3" />
                            {{ plan.badge }}
                        </span>
                    </div>

                    <!-- Card header -->
                    <div class="p-8 pb-6">
                        <!-- Plan icon + name -->
                        <div class="mb-5 flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl"
                                :class="plan.highlight ? 'bg-white/15' : 'bg-indigo-50'"
                            >
                                <component
                                    :is="plan.icon"
                                    class="size-5"
                                    :class="plan.highlight ? 'text-white' : 'text-indigo-600'"
                                />
                            </div>
                            <span
                                class="text-sm font-bold uppercase tracking-widest"
                                :class="plan.highlight ? 'text-indigo-200' : 'text-gray-500'"
                            >{{ plan.name }}</span>
                        </div>

                        <!-- Price -->
                        <div class="flex items-end gap-1.5">
                            <Transition
                                mode="out-in"
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="opacity-0 translate-y-1"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition duration-100 ease-in"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 -translate-y-1"
                            >
                                <span
                                    :key="displayPrice(plan)"
                                    class="text-5xl font-extrabold tracking-tight"
                                    :class="plan.highlight ? 'text-white' : 'text-gray-900'"
                                >{{ displayPrice(plan) }}</span>
                            </Transition>
                            <span
                                v-if="priceSuffix(plan)"
                                class="mb-1.5 text-xs leading-tight"
                                :class="plan.highlight ? 'text-indigo-200' : 'text-gray-400'"
                            >{{ priceSuffix(plan) }}</span>
                        </div>

                        <!-- Description -->
                        <p
                            class="mt-3 text-sm leading-relaxed"
                            :class="plan.highlight ? 'text-indigo-200' : 'text-gray-500'"
                        >{{ plan.description }}</p>

                        <!-- Separator -->
                        <div
                            class="mt-6 border-t"
                            :class="plan.highlight ? 'border-indigo-500/50' : 'border-gray-100'"
                        />
                    </div>

                    <!-- Feature list -->
                    <ul class="flex-1 space-y-3 px-8 pb-8">
                        <li
                            v-for="feature in plan.features"
                            :key="feature.text"
                            class="flex items-start gap-3 text-sm"
                        >
                            <CheckCircleIcon
                                v-if="feature.included"
                                class="mt-0.5 size-5 shrink-0"
                                :class="plan.highlight ? 'text-indigo-200' : 'text-emerald-500'"
                            />
                            <MinusIcon
                                v-else
                                class="mt-0.5 size-5 shrink-0"
                                :class="plan.highlight ? 'text-indigo-400/60' : 'text-gray-300'"
                            />
                            <span :class="[
                                feature.included
                                    ? (plan.highlight ? 'text-indigo-100' : 'text-gray-700')
                                    : (plan.highlight ? 'text-indigo-300/60' : 'text-gray-400'),
                            ]">{{ feature.text }}</span>
                        </li>
                    </ul>

                    <!-- CTA button -->
                    <div class="px-8 pb-8">
                        <component
                            :is="plan.ctaHref ? Link : 'a'"
                            :href="plan.ctaHref ? route(plan.ctaHref) : '#contact'"
                            class="block w-full rounded-xl py-3.5 text-center text-sm font-bold shadow transition-all duration-200"
                            :class="plan.highlight
                                ? 'bg-white text-indigo-700 hover:bg-indigo-50 hover:shadow-lg active:scale-[0.98]'
                                : plan.id === 'enterprise'
                                    ? 'border-2 border-gray-300 bg-white text-gray-700 hover:border-indigo-400 hover:text-indigo-600 hover:shadow-md'
                                    : 'bg-indigo-600 text-white hover:bg-indigo-700 hover:shadow-md active:scale-[0.98]'"
                        >
                            {{ plan.cta }}
                        </component>

                        <!-- Sub-note -->
                        <p
                            class="mt-3 text-center text-xs"
                            :class="plan.highlight ? 'text-indigo-300' : 'text-gray-400'"
                        >
                            <template v-if="plan.id === 'starter'">No credit card required</template>
                            <template v-else-if="plan.id === 'pro'">14-day free trial · Cancel anytime</template>
                            <template v-else>Custom contract & SLA</template>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Trust strip ───────────────────────────────────────────────── -->
            <div class="mt-16 text-center" data-price-animate style="transition-delay: 320ms">
                <p class="text-sm text-gray-500">
                    All plans include SSL, automated backups, and 99.9% uptime SLA.
                    <a href="#faq" class="font-medium text-indigo-600 hover:underline">Have questions? See our FAQ →</a>
                </p>
            </div>

        </div>
    </section>
</template>

<style scoped>
[data-price-animate] {
    opacity: 0;
    transform: translateY(20px);
    transition:
        opacity 0.5s ease,
        transform 0.5s ease;
}

[data-price-animate].visible {
    opacity: 1;
    transform: translateY(0);
}

/* Keep the Pro card's vertical lift from competing with hover */
.price-card.lg\:-translate-y-4:hover {
    transform: translateY(-1.2rem);
}
</style>
