<script setup>
import { Head } from '@inertiajs/vue3';
import { Doughnut, Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import {
    UsersIcon,
    FolderOpenIcon,
    CreditCardIcon,
    CheckCircleIcon,
    HomeIcon,
    Cog6ToothIcon,
    ChartBarIcon,
    DocumentTextIcon,
    BellIcon,
    PlusIcon,
    ArrowTrendingUpIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
);

// ── Stat cards ────────────────────────────────────────────────────────────────
const stats = [
    { label: 'Team Members',  value: '24',      change: '+3',    up: true,  icon: UsersIcon,        light: 'bg-indigo-50 text-indigo-600'  },
    { label: 'Active Projects', value: '7',    change: '+1',    up: true,  icon: FolderOpenIcon,   light: 'bg-emerald-50 text-emerald-600' },
    { label: 'Tasks Completed', value: '318',  change: '+42',   up: true,  icon: CheckCircleIcon,  light: 'bg-blue-50 text-blue-600'       },
    { label: 'Plan',          value: 'Pro',     change: '28 days', up: true, icon: CreditCardIcon, light: 'bg-violet-50 text-violet-600'   },
];

// ── Activity line chart ───────────────────────────────────────────────────────
const activityChartData = {
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    datasets: [
        {
            label: 'Logins',
            data: [18, 22, 19, 27, 31, 9, 6],
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.1)',
            borderWidth: 2.5,
            pointRadius: 3,
            tension: 0.4,
            fill: true,
        },
        {
            label: 'Tasks',
            data: [42, 55, 48, 63, 71, 15, 11],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.08)',
            borderWidth: 2.5,
            pointRadius: 3,
            tension: 0.4,
            fill: true,
        },
    ],
};

const activityChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 11 } } },
        tooltip: { mode: 'index', intersect: false },
    },
    scales: {
        x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 11 } } },
        y: { grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af', font: { size: 11 } } },
    },
};

// ── Storage donut ─────────────────────────────────────────────────────────────
const storageChartData = {
    labels: ['Used', 'Available'],
    datasets: [{
        data: [62, 38],
        backgroundColor: ['#6366f1', '#e5e7eb'],
        borderWidth: 0,
        hoverOffset: 4,
    }],
};

const storageChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '72%',
    plugins: { legend: { display: false }, tooltip: { enabled: true } },
};

// ── Team table ────────────────────────────────────────────────────────────────
const team = [
    { name: 'Jordan Lee',    email: 'jordan@acme.com',   role: 'Owner',    tasks: 84, status: 'Online'  },
    { name: 'Maria Torres',  email: 'maria@acme.com',    role: 'Admin',    tasks: 71, status: 'Online'  },
    { name: 'Sam Patel',     email: 'sam@acme.com',      role: 'Member',   tasks: 56, status: 'Away'    },
    { name: 'Chris Kim',     email: 'chris@acme.com',    role: 'Member',   tasks: 48, status: 'Offline' },
    { name: 'Alex Morgan',   email: 'alex@acme.com',     role: 'Viewer',   tasks: 22, status: 'Online'  },
];

const roleBadge = {
    Owner:  'bg-violet-100 text-violet-700',
    Admin:  'bg-indigo-100 text-indigo-700',
    Member: 'bg-blue-100 text-blue-700',
    Viewer: 'bg-gray-100 text-gray-600',
};

const statusDot = {
    Online:  'bg-emerald-500',
    Away:    'bg-amber-400',
    Offline: 'bg-gray-300',
};

// ── Recent activity ───────────────────────────────────────────────────────────
const recentActivity = [
    { icon: CheckCircleIcon, color: 'text-emerald-500', label: 'Jordan completed "API integration"',  time: '5 min ago'   },
    { icon: UsersIcon,       color: 'text-indigo-500',  label: 'Maria invited Alex Morgan',           time: '1 hr ago'    },
    { icon: DocumentTextIcon,color: 'text-blue-500',    label: 'Sam created "Q2 Roadmap" document',   time: '3 hr ago'    },
    { icon: FolderOpenIcon,  color: 'text-violet-500',  label: 'New project "Mobile App" created',    time: 'Yesterday'   },
    { icon: CreditCardIcon,  color: 'text-amber-500',   label: 'Pro plan renewed — $490/mo',          time: '2 days ago'  },
];

// ── Quick actions ─────────────────────────────────────────────────────────────
const quickActions = [
    { label: 'Invite Member', icon: UsersIcon,        color: 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100' },
    { label: 'New Project',   icon: FolderOpenIcon,   color: 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' },
    { label: 'View Report',   icon: ChartBarIcon,     color: 'bg-violet-50 text-violet-700 hover:bg-violet-100' },
    { label: 'Manage Plan',   icon: CreditCardIcon,   color: 'bg-amber-50 text-amber-700 hover:bg-amber-100' },
];

const navItems = [
    { label: 'Dashboard',  icon: HomeIcon,          active: true  },
    { label: 'Projects',   icon: FolderOpenIcon,    active: false },
    { label: 'Team',       icon: UsersIcon,         active: false },
    { label: 'Analytics',  icon: ChartBarIcon,      active: false },
    { label: 'Docs',       icon: DocumentTextIcon,  active: false },
    { label: 'Settings',   icon: Cog6ToothIcon,     active: false },
];
</script>

<template>
    <Head title="Tenant Dashboard — Demo" />

    <div class="flex h-screen overflow-hidden bg-gray-50 font-sans antialiased">

        <!-- ── Sidebar ──────────────────────────────────────────────────────── -->
        <aside class="flex w-60 shrink-0 flex-col bg-white shadow-sm">

            <!-- Logo -->
            <div class="flex h-16 items-center gap-3 border-b border-gray-100 px-5">
                <img src="/logo.png" alt="Tenantrix" class="h-7" />
                <div>
                    <p class="text-sm font-bold text-gray-900">Tenantrix</p>
                    <p class="text-xs text-gray-400">Acme Corp · Pro Plan</p>
                </div>
            </div>

            <!-- Nav -->
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

            <!-- User -->
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
                    <h1 class="text-lg font-bold text-gray-900">Workspace Dashboard</h1>
                    <p class="text-xs text-gray-500">Acme Corp — Pro Plan</p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        <PlusIcon class="size-4" /> New Project
                    </button>
                    <button class="relative rounded-lg border border-gray-200 p-2 text-gray-500 hover:bg-gray-50">
                        <BellIcon class="size-5" />
                        <span class="absolute right-1.5 top-1.5 size-2 rounded-full bg-indigo-500" />
                    </button>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">

                <!-- Stat cards -->
                <div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-500">{{ stat.label }}</p>
                            <div class="rounded-lg p-2" :class="stat.light">
                                <component :is="stat.icon" class="size-4" />
                            </div>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-900">{{ stat.value }}</p>
                        <div class="mt-1 flex items-center gap-1">
                            <ArrowTrendingUpIcon class="size-3.5 text-emerald-500" />
                            <span class="text-xs font-semibold text-emerald-600">{{ stat.change }}</span>
                            <span class="text-xs text-gray-400">this month</span>
                        </div>
                    </div>
                </div>

                <!-- Charts + Storage -->
                <div class="mb-6 grid gap-4 lg:grid-cols-3">

                    <!-- Activity line chart — 2/3 -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
                        <div class="mb-4">
                            <p class="font-semibold text-gray-900">Team Activity — This Week</p>
                            <p class="text-xs text-gray-400">Logins and task completions per day</p>
                        </div>
                        <div class="h-48">
                            <Line :data="activityChartData" :options="activityChartOptions" />
                        </div>
                    </div>

                    <!-- Storage donut + quick actions — 1/3 -->
                    <div class="flex flex-col gap-4">

                        <!-- Donut -->
                        <div class="flex flex-1 flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <p class="mb-3 self-start font-semibold text-gray-900">Storage</p>
                            <div class="relative h-32 w-32">
                                <Doughnut :data="storageChartData" :options="storageChartOptions" />
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-xl font-extrabold text-gray-900">62%</span>
                                    <span class="text-xs text-gray-400">of 50 GB</span>
                                </div>
                            </div>
                            <div class="mt-3 flex gap-4 text-xs text-gray-500">
                                <span class="flex items-center gap-1"><span class="inline-block size-2 rounded-full bg-indigo-500" />31 GB used</span>
                                <span class="flex items-center gap-1"><span class="inline-block size-2 rounded-full bg-gray-200" />19 GB free</span>
                            </div>
                        </div>

                        <!-- Quick actions -->
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                v-for="qa in quickActions"
                                :key="qa.label"
                                class="flex flex-col items-center gap-1.5 rounded-xl border border-gray-100 p-3 text-xs font-medium transition-colors"
                                :class="qa.color"
                            >
                                <component :is="qa.icon" class="size-5" />
                                {{ qa.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Team table + Recent activity -->
                <div class="grid gap-4 lg:grid-cols-3">

                    <!-- Team table — 2/3 -->
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:col-span-2">
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                            <p class="font-semibold text-gray-900">Team Members</p>
                            <button class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:underline">
                                <PlusIcon class="size-3.5" /> Invite
                            </button>
                        </div>
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50">
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Member</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Role</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Tasks</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="m in team" :key="m.email" class="hover:bg-gray-50/60">
                                    <td class="px-5 py-3">
                                        <div class="font-medium text-gray-900">{{ m.name }}</div>
                                        <div class="text-xs text-gray-400">{{ m.email }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="roleBadge[m.role]">{{ m.role }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-600">{{ m.tasks }}</td>
                                    <td class="px-4 py-3">
                                        <span class="flex items-center gap-1.5 text-xs text-gray-600">
                                            <span class="size-1.5 rounded-full" :class="statusDot[m.status]" />
                                            {{ m.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Activity feed — 1/3 -->
                    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 px-5 py-4">
                            <p class="font-semibold text-gray-900">Recent Activity</p>
                        </div>
                        <ul class="divide-y divide-gray-50">
                            <li v-for="ev in recentActivity" :key="ev.label" class="flex items-start gap-3 px-5 py-3.5">
                                <component :is="ev.icon" class="mt-0.5 size-4 shrink-0" :class="ev.color" />
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-700">{{ ev.label }}</p>
                                    <p class="mt-0.5 flex items-center gap-1 text-xs text-gray-400">
                                        <ClockIcon class="size-3" /> {{ ev.time }}
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

            </main>
        </div>
    </div>
</template>
