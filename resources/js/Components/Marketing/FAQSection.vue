<script setup>
import { onMounted, ref } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/24/outline';

// ── FAQ data ───────────────────────────────────────────────────────────────────

const faqs = [
    {
        question: 'How does multi-tenant architecture work?',
        answer: 'Each tenant gets a fully isolated database and dedicated workspace — zero data bleed by design. When a new tenant signs up, our automated pipeline provisions their environment in under two seconds, including database, storage bucket, and subdomain routing.',
        category: 'Architecture',
    },
    {
        question: 'Can I create unlimited tenants?',
        answer: 'Pro plans support up to 10 tenant workspaces. Enterprise plans have no limit — you can provision as many tenants as your infrastructure requires. Need more on Pro? Reach out and we\'ll find a solution.',
        category: 'Plans',
    },
    {
        question: 'Does the platform support APIs?',
        answer: 'Yes — every tenant gets a full versioned RESTful API authenticated with Laravel Sanctum tokens. The API is scoped per-tenant so no cross-tenant data leaks. Full OpenAPI documentation is available in every developer dashboard.',
        category: 'Developer',
    },
    {
        question: 'Can I customize subscription plans?',
        answer: 'Absolutely. Plan limits (seats, storage, API calls) are all configurable per tenant via the admin dashboard without any code changes. Enterprise customers can also have bespoke pricing tiers and feature flags.',
        category: 'Billing',
    },
    {
        question: 'Is there a free trial available?',
        answer: 'The Starter plan is free forever — no credit card required. Pro plans include a 14-day free trial with full feature access so you can evaluate everything before committing.',
        category: 'Plans',
    },
    {
        question: 'Can tenants use a custom domain?',
        answer: 'Yes. Each tenant workspace can be mapped to a custom subdomain or a fully custom apex domain. SSL certificates are provisioned and renewed automatically.',
        category: 'Architecture',
    },
    {
        question: 'What happens when I upgrade or downgrade a plan?',
        answer: 'Upgrades take effect immediately and are prorated to the day. Downgrades apply at the end of the current billing cycle so tenants always receive what they paid for.',
        category: 'Billing',
    },
    {
        question: 'What support options are available?',
        answer: 'Starter and Pro plans include email support with a 48-hour response window. Enterprise plans include a dedicated support engineer, Slack channel access, and a customisable SLA.',
        category: 'Support',
    },
];

const categoryColors = {
    Architecture: 'bg-primary-50 text-primary-700',
    Plans: 'bg-accent-50 text-accent-700',
    Developer: 'bg-secondary-50 text-secondary-700',
    Billing: 'bg-success-50 text-success-700',
    Support: 'bg-warning-50 text-warning-700',
};

// ── Accordion state ────────────────────────────────────────────────────────────

const openIndex = ref(null);

function toggle(i) {
    openIndex.value = openIndex.value === i ? null : i;
}

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
    document.querySelectorAll('[data-faq-animate]').forEach((el) => io.observe(el));
});
</script>

<template>
    <section id="faq" class="bg-gray-50 py-24 sm:py-32">
        <div class="mx-auto max-w-3xl px-6">

            <!-- Heading -->
            <div class="mb-12 text-center" data-faq-animate>
                <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-primary-600">FAQ</p>
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Frequently asked questions
                </h2>
                <p class="mt-4 text-base text-gray-500">
                    Everything you need to know before you start building.
                </p>
            </div>

            <!-- Accordion list -->
            <div class="space-y-2" data-faq-animate style="transition-delay: 100ms">
                <div
                    v-for="(faq, i) in faqs"
                    :key="faq.question"
                    class="faq-item overflow-hidden rounded-xl border bg-white transition-all duration-200"
                    :class="openIndex === i
                        ? 'border-primary-200 shadow-sm shadow-primary-100'
                        : 'border-gray-200 hover:border-gray-300'"
                >
                    <!-- Trigger -->
                    <button
                        class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left"
                        :aria-expanded="openIndex === i"
                        @click="toggle(i)"
                    >
                        <div class="flex items-center gap-3">
                            <!-- Category pill -->
                            <span
                                class="hidden shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold sm:inline-block"
                                :class="categoryColors[faq.category]"
                            >{{ faq.category }}</span>
                            <span class="font-semibold text-gray-900">{{ faq.question }}</span>
                        </div>
                        <ChevronDownIcon
                            class="size-5 shrink-0 transition-transform duration-300"
                            :class="openIndex === i ? 'rotate-180 text-primary-600' : 'text-gray-400'"
                        />
                    </button>

                    <!-- Answer panel -->
                    <Transition
                        enter-active-class="transition-all duration-300 ease-out"
                        enter-from-class="opacity-0 max-h-0"
                        enter-to-class="opacity-100 max-h-96"
                        leave-active-class="transition-all duration-200 ease-in"
                        leave-from-class="opacity-100 max-h-96"
                        leave-to-class="opacity-0 max-h-0"
                    >
                        <div v-if="openIndex === i" class="border-t border-primary-100 bg-primary-50/30 px-6 pb-5 pt-4">
                            <p class="text-sm leading-relaxed text-gray-600">{{ faq.answer }}</p>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Bottom CTA -->
            <div class="mt-12 rounded-2xl border border-primary-100 bg-white p-6 text-center" data-faq-animate style="transition-delay: 200ms">
                <p class="text-sm text-gray-600">
                    Still have questions?
                    <a href="mailto:support@yoursaas.com" class="font-semibold text-primary-600 hover:underline">Talk to our team →</a>
                </p>
            </div>

        </div>
    </section>
</template>

<style scoped>
[data-faq-animate] {
    opacity: 0;
    transform: translateY(20px);
    transition:
        opacity 0.5s ease,
        transform 0.5s ease;
}

[data-faq-animate].visible {
    opacity: 1;
    transform: translateY(0);
}
</style>
