<script setup>
import { onMounted } from 'vue';
import { StarIcon } from '@heroicons/vue/20/solid';

// ── Testimonials data ──────────────────────────────────────────────────────────

const testimonials = [
    {
        quote: 'We launched our SaaS in three weeks instead of three months. The multi-tenant infrastructure was already there — we just built our product on top and shipped.',
        name: 'Sarah Chen',
        role: 'CTO',
        company: 'Flowbase',
        initials: 'SC',
        avatarColor: 'from-primary-500 to-accent-600',
        companyColor: 'text-primary-600',
        featured: true,
    },
    {
        quote: 'The role and permission system saved weeks of engineering time. Our enterprise clients love the granular access controls right out of the box — no customisation needed.',
        name: 'Marcus Webb',
        role: 'Founder',
        company: 'Stackly',
        initials: 'MW',
        avatarColor: 'from-accent-500 to-fuchsia-600',
        companyColor: 'text-accent-600',
        featured: false,
    },
    {
        quote: 'Billing, analytics, and tenant isolation — all production-ready on day one. I wish I had found this a year earlier. The developer experience is genuinely exceptional.',
        name: 'Priya Nair',
        role: 'Lead Engineer',
        company: 'Crisp Analytics',
        initials: 'PN',
        avatarColor: 'from-success-500 to-secondary-600',
        companyColor: 'text-success-600',
        featured: false,
    },
    {
        quote: 'Onboarding enterprise clients used to take days. With this platform, provisioning a new tenant workspace takes under two seconds. Our churn is down 40% since switching.',
        name: 'James O\'Brien',
        role: 'Product Lead',
        company: 'Devhub',
        initials: 'JO',
        avatarColor: 'from-warning-500 to-orange-600',
        companyColor: 'text-warning-600',
        featured: false,
    },
];

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
    document.querySelectorAll('[data-testi-animate]').forEach((el) => io.observe(el));
});
</script>

<template>
    <section class="relative overflow-hidden bg-gray-50 py-24 sm:py-32">

        <!-- Decorative blobs -->
        <div class="pointer-events-none absolute -left-32 -top-32 size-96 rounded-full bg-primary-100/70 blur-3xl" />
        <div class="pointer-events-none absolute -bottom-32 -right-32 size-96 rounded-full bg-accent-100/50 blur-3xl" />

        <div class="relative mx-auto max-w-7xl px-6">

            <!-- Heading ──────────────────────────────────────────────────── -->
            <div class="mx-auto mb-16 max-w-xl text-center" data-testi-animate>
                <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-primary-600">Testimonials</p>
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Loved by builders worldwide
                </h2>
                <p class="mt-4 text-base text-gray-500">
                    Thousands of developers and founders trust this platform to power their SaaS products.
                </p>
            </div>

            <!-- Card grid ─────────────────────────────────────────────────── -->
            <!--
                Layout: featured card spans 2 cols on lg, three small cards fill remaining space.
                sm: single column stacked; lg: asymmetric 2+1 grid.
            -->
            <div class="grid gap-6 lg:grid-cols-3">

                <!-- Featured (wide) card -->
                <div
                    data-testi-animate
                    style="transition-delay: 80ms"
                    class="testi-card relative flex flex-col rounded-2xl bg-gradient-primary p-8 shadow-xl shadow-primary-900/30 ring-1 ring-primary-500 lg:col-span-2"
                >
                    <!-- Decorative large quote marks -->
                    <span class="absolute right-6 top-4 select-none text-8xl font-black leading-none text-primary-500/30">"</span>

                    <!-- Stars -->
                    <div class="mb-5 flex gap-1">
                        <StarIcon v-for="n in 5" :key="n" class="size-4 text-warning-300" />
                    </div>

                    <!-- Quote -->
                    <p class="flex-1 text-lg font-medium leading-relaxed text-white">
                        "{{ testimonials[0].quote }}"
                    </p>

                    <!-- Author -->
                    <div class="mt-8 flex items-center gap-4">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br text-sm font-bold text-white shadow-md"
                            :class="testimonials[0].avatarColor"
                        >
                            {{ testimonials[0].initials }}
                        </div>
                        <div>
                            <p class="font-semibold text-white">{{ testimonials[0].name }}</p>
                            <p class="text-sm text-primary-200">
                                {{ testimonials[0].role }} ·
                                <span class="font-medium text-primary-100">{{ testimonials[0].company }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right column: two stacked cards -->
                <div class="flex flex-col gap-6">
                    <div
                        v-for="(t, idx) in testimonials.slice(1, 3)"
                        :key="t.name"
                        data-testi-animate
                        :style="{ transitionDelay: (idx + 2) * 100 + 'ms' }"
                        class="testi-card flex flex-col rounded-2xl border border-gray-100 bg-white p-7 shadow-sm"
                    >
                        <!-- Stars -->
                        <div class="mb-4 flex gap-0.5">
                            <StarIcon v-for="n in 5" :key="n" class="size-3.5 text-warning-400" />
                        </div>

                        <!-- Quote -->
                        <p class="flex-1 text-sm leading-relaxed text-gray-700">"{{ t.quote }}"</p>

                        <!-- Author -->
                        <div class="mt-6 flex items-center gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br text-xs font-bold text-white shadow"
                                :class="t.avatarColor"
                            >
                                {{ t.initials }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ t.name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ t.role }} ·
                                    <span class="font-medium" :class="t.companyColor">{{ t.company }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fourth card — full width on lg -->
                <div
                    data-testi-animate
                    style="transition-delay: 380ms"
                    class="testi-card flex flex-col rounded-2xl border border-gray-100 bg-white p-7 shadow-sm sm:flex-row sm:items-center sm:gap-8 lg:col-span-3"
                >
                    <!-- Large quote accent -->
                    <p class="mb-4 text-6xl font-black leading-none text-primary-100 sm:mb-0 sm:shrink-0">"</p>

                    <div class="flex flex-1 flex-col">
                        <!-- Stars -->
                        <div class="mb-3 flex gap-0.5">
                            <StarIcon v-for="n in 5" :key="n" class="size-3.5 text-warning-400" />
                        </div>
                        <p class="flex-1 text-sm leading-relaxed text-gray-700">"{{ testimonials[3].quote }}"</p>
                    </div>

                    <!-- Separator on sm+ -->
                    <div class="mt-5 border-t border-gray-100 pt-5 sm:mt-0 sm:border-l sm:border-t-0 sm:pl-8 sm:pt-0">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br text-sm font-bold text-white shadow"
                                :class="testimonials[3].avatarColor"
                            >
                                {{ testimonials[3].initials }}
                            </div>
                            <div class="min-w-max">
                                <p class="text-sm font-semibold text-gray-900">{{ testimonials[3].name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ testimonials[3].role }} ·
                                    <span class="font-medium" :class="testimonials[3].companyColor">{{ testimonials[3].company }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Social proof strip ────────────────────────────────────────── -->
            <div class="mt-14 flex flex-wrap items-center justify-center gap-x-10 gap-y-4 text-center" data-testi-animate style="transition-delay: 460ms">
                <div
                    v-for="stat in [
                        { value: '2,400+', label: 'SaaS products built' },
                        { value: '98%', label: 'Customer satisfaction' },
                        { value: '< 2 s', label: 'Avg. tenant provisioning' },
                    ]"
                    :key="stat.label"
                >
                    <p class="text-2xl font-extrabold text-gray-900">{{ stat.value }}</p>
                    <p class="text-xs text-gray-500">{{ stat.label }}</p>
                </div>
            </div>

        </div>
    </section>
</template>

<style scoped>
[data-testi-animate] {
    opacity: 0;
    transform: translateY(22px);
    transition:
        opacity 0.5s ease,
        transform 0.5s ease;
}

[data-testi-animate].visible {
    opacity: 1;
    transform: translateY(0);
}

.testi-card {
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;
}

.testi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 40px -8px rgb(0 0 0 / 0.1);
}
</style>
