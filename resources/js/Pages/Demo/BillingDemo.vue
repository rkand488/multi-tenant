<script setup>
import { Head } from '@inertiajs/vue3';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import {
    HomeIcon,
    ChartBarIcon,
    UsersIcon,
    FolderOpenIcon,
    CreditCardIcon,
    Cog6ToothIcon,
    BellIcon,
    CheckCircleIcon,
    MinusCircleIcon,
    ArrowDownTrayIcon,
    SparklesIcon,
    BoltIcon,
    BuildingOfficeIcon,
} from '@heroicons/vue/24/outline';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

// ── Current plan summary ──────────────────────────────────────────────────────
const currentPlan = {
    name:        'Pro',
    price:       '$490',
    cycle:       '/month',
    nextBilling: 'April 1, 2025',
    seats:       24,
    seatLimit:   50,
    storage:     31,
    storageLimit: 50,
    apiCalls:    834210,
    apiLimit:    1000000,
};

// ── Monthly spend bar chart ───────────────────────────────────────────────────
const spendChartData = {
    labels: ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'],
    datasets: [
        {
            label: 'Amount ($)',
            data: [490, 490, 490, 490, 980, 490, 490, 490, 490],
            backgroundColor: '#6366f1',
            borderRadius: 5,
            borderSkipped: false,
        },
    ],
};

const spendChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 11 } } },
        y: { grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af', font: { size: 11 }, callback: (v) => `$${v}` } },
    },
};

// ── Plans ─────────────────────────────────────────────────────────────────────
const plans = [
    {
        name:    'Starter',
        price:   'Free',
        desc:    'Perfect for individuals and side projects.',
        icon:    BoltIcon,
        current: false,
        color:   'border-gray-200',
        badge:   null,
        features: [
            { label: '3 team members',             included: true  },
            { label: '5 GB storage',               included: true  },
            { label: '100K API calls/mo',           included: true  },
            { label: 'Community support',           included: true  },
            { label: 'Custom domain',               included: false },
            { label: 'Priority support',            included: false },
            { label: 'SSO / SAML',                  included: false },
            { label: 'SLA guarantee',               included: false },
        ],
    },
    {
        name:    'Pro',
        price:   '$490',
        desc:    'For growing teams that need more power.',
        icon:    SparklesIcon,
        current: true,
        color:   'border-indigo-500 ring-2 ring-indigo-500',
        badge:   'Current Plan',
        features: [
            { label: '50 team members',             included: true  },
            { label: '50 GB storage',               included: true  },
            { label: '1M API calls/mo',             included: true  },
            { label: 'Email support',               included: true  },
            { label: 'Custom domain',               included: true  },
            { label: 'Priority support',            included: true  },
            { label: 'SSO / SAML',                  included: false },
            { label: 'SLA guarantee',               included: false },
        ],
    },
    {
        name:    'Enterprise',
        price:   'Custom',
        desc:    'Dedicated infrastructure and white-glove support.',
        icon:    BuildingOfficeIcon,
        current: false,
        color:   'border-gray-200',
        badge:   null,
        features: [
            { label: 'Unlimited members',           included: true  },
            { label: 'Unlimited storage',           included: true  },
            { label: 'Unlimited API calls',         included: true  },
            { label: '24/7 dedicated support',      included: true  },
            { label: 'Custom domain',               included: true  },
            { label: 'Priority support',            included: true  },
            { label: 'SSO / SAML',                  included: true  },
            { label: 'SLA guarantee',               included: true  },
        ],
    },
];

// ── Invoices ──────────────────────────────────────────────────────────────────
const invoices = [
    { id: 'INV-0084', date: 'Mar 1, 2025', amount: '$490.00', status: 'Paid',   desc: 'Pro Plan — March 2025'    },
    { id: 'INV-0083', date: 'Feb 1, 2025', amount: '$490.00', status: 'Paid',   desc: 'Pro Plan — February 2025' },
    { id: 'INV-0082', date: 'Jan 1, 2025', amount: '$490.00', status: 'Paid',   desc: 'Pro Plan — January 2025'  },
    { id: 'INV-0081', date: 'Dec 1, 2024', amount: '$980.00', status: 'Paid',   desc: 'Pro Plan + Seat Expansion' },
    { id: 'INV-0080', date: 'Nov 1, 2024', amount: '$490.00', status: 'Paid',   desc: 'Pro Plan — November 2024' },
    { id: 'INV-0079', date: 'Oct 1, 2024', amount: '$490.00', status: 'Paid',   desc: 'Pro Plan — October 2024'  },
];

const statusBadge = {
    Paid:    'bg-emerald-100 text-emerald-700',
    Pending: 'bg-amber-100 text-amber-700',
    Failed:  'bg-rose-100 text-rose-700',
};

const navItems = [
    { label: 'Dashboard',  icon: HomeIcon,          active: false },
    { label: 'Analytics',  icon: ChartBarIcon,      active: false },
    { label: 'Users',      icon: UsersIcon,         active: false },
    { label: 'Projects',   icon: FolderOpenIcon,    active: false },
    { label: 'Billing',    icon: CreditCardIcon,    active: true  },
    { label: 'Settings',   icon: Cog6ToothIcon,     active: false },
];

// ── Usage helpers ─────────────────────────────────────────────────────────────
const usagePct = (used, limit) => Math.round((used / limit) * 100);

const usageColor = (pct) => {
    if (pct >= 90) { return 'bg-rose-500'; }
    if (pct >= 70) { return 'bg-amber-500'; }
    return 'bg-indigo-500';
};
</script>

<template>
    <Head title="Billing — Demo" />

    <div class="flex h-screen overflow-hidden bg-gray-50 font-sans antialiased">

        <!-- ── Sidebar ──────────────────────────────────────────────────────── -->
        <aside class="flex w-60 shrink-0 flex-col bg-white shadow-sm">

            <div class="flex h-16 items-center gap-3 border-b border-gray-100 px-5">
                <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-600 text-sm font-bold text-white">A</div>
                <div>
                    <p class="text-sm font-bold text-gray-900">Acme Corp</p>
                    <p class="text-xs text-gray-400">Pro Plan</p>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <a
                    v-for="item in navItems"
                    :key="item.label"
                    href="#"
                    class="mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
                    :class="item.active ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900'"
                >
                    <component :is="item.icon" class="size-4.5 shrink-0" />
                    {{ item.label }}
                </a>
            </nav>

            <div class="border-t border-gray-100 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-8 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">JL</div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-gray-900">Jordan Lee</p>
                        <p class="truncate text-xs text-gray-500">Owner</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ── Main ─────────────────────────────────────────────────────────── -->
        <div class="flex flex-1 flex-col overflow-hidden">

            <!-- Topbar -->
            <header class="flex h-16 shrink-0 items-center justify-between border-b border-gray-200 bg-white px-6">
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Billing &amp; Plans</h1>
                    <p class="text-xs text-gray-500">Manage your subscription and invoices</p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="rounded-lg border border-gray-200 p-2 text-gray-500 hover:bg-gray-50">
                        <BellIcon class="size-5" />
                    </button>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">

                <!-- Current plan + spend chart + payment method -->
                <div class="mb-6 grid gap-4 lg:grid-cols-3">

                    <!-- Current plan card -->
                    <div class="rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-600 to-violet-600 p-5 text-white shadow-md">
                        <div class="mb-4 flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-200">Current Plan</p>
                                <p class="text-3xl font-extrabold">Pro</p>
                            </div>
                            <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold backdrop-blur">Active</span>
                        </div>
                        <p class="mb-4 text-2xl font-bold">{{ currentPlan.price }}<span class="text-sm font-normal text-indigo-200">{{ currentPlan.cycle }}</span></p>
                        <div class="space-y-2 text-sm text-indigo-100">
                            <p>Next billing: <span class="font-semibold text-white">{{ currentPlan.nextBilling }}</span></p>
                        </div>
                        <button class="mt-5 w-full rounded-lg bg-white/20 py-2 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/30">
                            Upgrade to Enterprise →
                        </button>
                    </div>

                    <!-- Usage meters -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="mb-4 font-semibold text-gray-900">Usage This Month</p>
                        <div class="space-y-4">
                            <!-- Seats -->
                            <div>
                                <div class="mb-1.5 flex items-center justify-between text-xs">
                                    <span class="font-medium text-gray-700">Team Seats</span>
                                    <span class="text-gray-500">{{ currentPlan.seats }} / {{ currentPlan.seatLimit }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                    <div
                                        class="h-full rounded-full transition-all"
                                        :class="usageColor(usagePct(currentPlan.seats, currentPlan.seatLimit))"
                                        :style="{ width: usagePct(currentPlan.seats, currentPlan.seatLimit) + '%' }"
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-400">{{ usagePct(currentPlan.seats, currentPlan.seatLimit) }}% used</p>
                            </div>

                            <!-- Storage -->
                            <div>
                                <div class="mb-1.5 flex items-center justify-between text-xs">
                                    <span class="font-medium text-gray-700">Storage</span>
                                    <span class="text-gray-500">{{ currentPlan.storage }} GB / {{ currentPlan.storageLimit }} GB</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                    <div
                                        class="h-full rounded-full transition-all"
                                        :class="usageColor(usagePct(currentPlan.storage, currentPlan.storageLimit))"
                                        :style="{ width: usagePct(currentPlan.storage, currentPlan.storageLimit) + '%' }"
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-400">{{ usagePct(currentPlan.storage, currentPlan.storageLimit) }}% used</p>
                            </div>

                            <!-- API calls -->
                            <div>
                                <div class="mb-1.5 flex items-center justify-between text-xs">
                                    <span class="font-medium text-gray-700">API Calls</span>
                                    <span class="text-gray-500">{{ (currentPlan.apiCalls / 1000).toFixed(0) }}K / 1M</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                    <div
                                        class="h-full rounded-full transition-all"
                                        :class="usageColor(usagePct(currentPlan.apiCalls, currentPlan.apiLimit))"
                                        :style="{ width: usagePct(currentPlan.apiCalls, currentPlan.apiLimit) + '%' }"
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-400">{{ usagePct(currentPlan.apiCalls, currentPlan.apiLimit) }}% used</p>
                            </div>
                        </div>
                    </div>

                    <!-- Spend chart + payment method -->
                    <div class="flex flex-col gap-4">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <p class="mb-3 font-semibold text-gray-900">Monthly Spend</p>
                            <div class="h-28">
                                <Bar :data="spendChartData" :options="spendChartOptions" />
                            </div>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold text-gray-900">Payment Method</p>
                                <button class="text-xs font-medium text-indigo-600 hover:underline">Update</button>
                            </div>
                            <div class="mt-3 flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-lg bg-gray-900 text-white">
                                    <CreditCardIcon class="size-5" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Visa ending in 4242</p>
                                    <p class="text-xs text-gray-500">Expires 09 / 2027</p>
                                </div>
                                <span class="ml-auto rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">Default</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Plan comparison -->
                <div class="mb-6">
                    <p class="mb-4 font-semibold text-gray-900">Compare Plans</p>
                    <div class="grid gap-4 lg:grid-cols-3">
                        <div
                            v-for="plan in plans"
                            :key="plan.name"
                            class="rounded-xl border bg-white p-5 shadow-sm transition-shadow hover:shadow-md"
                            :class="plan.color"
                        >
                            <div class="mb-4 flex items-start justify-between">
                                <div class="flex items-center gap-2">
                                    <component :is="plan.icon" class="size-5" :class="plan.current ? 'text-indigo-600' : 'text-gray-500'" />
                                    <p class="font-bold text-gray-900">{{ plan.name }}</p>
                                </div>
                                <span v-if="plan.badge" class="rounded-full bg-indigo-600 px-2.5 py-0.5 text-xs font-semibold text-white">
                                    {{ plan.badge }}
                                </span>
                            </div>
                            <p class="mb-1 text-2xl font-extrabold text-gray-900">{{ plan.price }}<span v-if="plan.price !== 'Free' && plan.price !== 'Custom'" class="text-sm font-normal text-gray-400">/mo</span></p>
                            <p class="mb-4 text-xs text-gray-500">{{ plan.desc }}</p>
                            <ul class="mb-5 space-y-2">
                                <li v-for="feat in plan.features" :key="feat.label" class="flex items-center gap-2 text-xs">
                                    <CheckCircleIcon v-if="feat.included" class="size-4 shrink-0 text-emerald-500" />
                                    <MinusCircleIcon v-else class="size-4 shrink-0 text-gray-300" />
                                    <span :class="feat.included ? 'text-gray-700' : 'text-gray-400'">{{ feat.label }}</span>
                                </li>
                            </ul>
                            <button
                                class="w-full rounded-lg py-2 text-sm font-semibold transition"
                                :class="plan.current
                                    ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                                    : plan.name === 'Enterprise'
                                        ? 'border border-gray-200 text-gray-700 hover:bg-gray-50'
                                        : 'border border-gray-200 text-gray-500 cursor-not-allowed'"
                            >
                                {{ plan.current ? 'Current Plan' : plan.name === 'Enterprise' ? 'Contact Sales' : 'Downgrade' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Invoice table -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                        <p class="font-semibold text-gray-900">Invoice History</p>
                        <button class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:underline">
                            <ArrowDownTrayIcon class="size-4" /> Export All
                        </button>
                    </div>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Invoice</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500" />
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="inv in invoices" :key="inv.id" class="hover:bg-gray-50/60">
                                <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ inv.id }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ inv.desc }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ inv.date }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ inv.amount }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="statusBadge[inv.status]">{{ inv.status }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button class="text-xs text-indigo-600 hover:underline">PDF</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </main>
        </div>
    </div>
</template>
