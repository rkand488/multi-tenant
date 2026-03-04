<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Alert from '@/Components/UI/Alert.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { CheckIcon, StarIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    plans:          { type: Array,  required: true },
    currentPlan:    { type: Object, default: null },
    subscription:   { type: Object, default: null },
    billingCycle:   { type: String, default: 'monthly' },  // monthly | yearly
});

const cycle = ref(props.billingCycle);
const upgradeForm = useForm({ plan_id: '', billing_cycle: cycle });

const upgradeTo = (plan) => {
    upgradeForm.plan_id      = plan.id;
    upgradeForm.billing_cycle = cycle.value;
    upgradeForm.post(route('tenant.billing.upgrade'));
};

const price = (plan) => cycle.value === 'yearly' ? plan.price_yearly : plan.price_monthly;
const priceFormatted = (plan) => '$' + (price(plan) / 100).toFixed(2);

const isCurrentPlan = (plan) => props.currentPlan?.id === plan.id;

const activePlans = computed(() => props.plans.filter((p) => p.is_active));
</script>

<template>
    <div class="space-y-6">

        <!-- Header -->
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Choose a Plan</h1>
            <p class="mt-1 text-sm text-gray-500">Upgrade or change your subscription plan.</p>
        </div>

        <!-- Billing cycle toggle -->
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 dark:text-gray-400">Monthly</span>
            <button
                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none"
                :class="cycle === 'yearly' ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700'"
                @click="cycle = cycle === 'yearly' ? 'monthly' : 'yearly'"
            >
                <span
                    class="pointer-events-none inline-block size-5 rounded-full bg-white shadow ring-0 transition-transform"
                    :class="cycle === 'yearly' ? 'translate-x-5' : 'translate-x-0'"
                />
            </button>
            <span class="text-sm text-gray-600 dark:text-gray-400">
                Yearly
                <span class="ml-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">
                    Save ~17%
                </span>
            </span>
        </div>

        <!-- Plan cards -->
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="plan in activePlans"
                :key="plan.id"
                class="relative rounded-2xl border p-6 transition"
                :class="isCurrentPlan(plan)
                    ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/10'
                    : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-700 dark:bg-gray-900'"
            >
                <!-- Current badge -->
                <span
                    v-if="isCurrentPlan(plan)"
                    class="absolute right-4 top-4 flex items-center gap-1 rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400"
                >
                    <CheckIcon class="size-3" />
                    Current
                </span>

                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ plan.name }}</h3>
                <p v-if="plan.description" class="mt-1 text-sm text-gray-500">{{ plan.description }}</p>

                <div class="my-4">
                    <span class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ priceFormatted(plan) }}</span>
                    <span class="text-sm text-gray-500">/{{ cycle === 'yearly' ? 'year' : 'month' }}</span>
                </div>

                <!-- Features -->
                <ul class="mb-6 space-y-2">
                    <li
                        v-for="(value, key) in plan.features"
                        :key="key"
                        class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
                    >
                        <CheckIcon class="size-4 shrink-0 text-green-500" />
                        <span class="capitalize">{{ String(key).replace(/_/g, ' ') }}: {{ value === true ? 'Yes' : value }}</span>
                    </li>
                </ul>

                <Button
                    v-if="!isCurrentPlan(plan)"
                    class="w-full justify-center"
                    :loading="upgradeForm.processing && upgradeForm.plan_id === plan.id"
                    @click="upgradeTo(plan)"
                >
                    {{ subscription ? 'Switch to this plan' : 'Get started' }}
                </Button>
                <p v-else class="text-center text-sm font-medium text-indigo-600 dark:text-indigo-400">
                    You are on this plan
                </p>
            </div>
        </div>

    </div>
</template>
