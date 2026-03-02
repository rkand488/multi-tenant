<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Checkbox from '@/Components/UI/Checkbox.vue';
import Button from '@/Components/UI/Button.vue';
import Alert from '@/Components/UI/Alert.vue';
import Select from '@/Components/UI/Select.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    CogIcon,
    EnvelopeIcon,
    LockClosedIcon,
    BellIcon,
    CircleStackIcon,
    CloudArrowUpIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
    dbStats:  { type: Object, default: () => ({}) },
    health:   { type: Object, default: () => ({}) },
});

const saved = ref(false);

const form = useForm({
    // General
    app_name:             props.settings.app_name             ?? '',
    app_url:              props.settings.app_url              ?? '',
    support_email:        props.settings.support_email        ?? '',
    // Auth
    allow_registration:   props.settings.allow_registration   ?? true,
    require_email_verify: props.settings.require_email_verify ?? true,
    session_lifetime:     props.settings.session_lifetime     ?? 120,
    // Mail
    mail_driver:          props.settings.mail_driver          ?? 'smtp',
    mail_from_address:    props.settings.mail_from_address    ?? '',
    mail_from_name:       props.settings.mail_from_name       ?? '',
    // Notifications
    notify_new_signup:    props.settings.notify_new_signup    ?? true,
    notify_payment_fail:  props.settings.notify_payment_fail  ?? true,
    notify_admin_email:   props.settings.notify_admin_email   ?? '',
    // Storage
    max_storage_per_tenant_mb: props.settings.max_storage_per_tenant_mb ?? 1024,
    allowed_file_types:        props.settings.allowed_file_types        ?? '',
});

const saveSettings = () => {
    form.post(route('admin.settings.update'), {
        onSuccess: () => {
            saved.value = true;
            setTimeout(() => (saved.value = false), 3000);
        },
    });
};

const mailDriverOptions = [
    { value: 'smtp',     label: 'SMTP' },
    { value: 'ses',      label: 'Amazon SES' },
    { value: 'mailgun',  label: 'Mailgun' },
    { value: 'postmark', label: 'Postmark' },
    { value: 'log',      label: 'Log (dev only)' },
];

const sections = [
    { id: 'general',       label: 'General',       icon: CogIcon },
    { id: 'auth',          label: 'Auth & Security', icon: LockClosedIcon },
    { id: 'mail',          label: 'Mail',           icon: EnvelopeIcon },
    { id: 'notifications', label: 'Notifications',  icon: BellIcon },
    { id: 'storage',       label: 'Storage',        icon: CloudArrowUpIcon },
    { id: 'system',        label: 'System',         icon: CircleStackIcon },
];

const active = ref('general');
</script>

<template>

    <div class="flex gap-6">

        <!-- ── Side nav ───────────────────────────────────────────────── -->
        <nav class="hidden w-48 shrink-0 lg:block">
            <ul class="space-y-0.5">
                <li v-for="sec in sections" :key="sec.id">
                    <button
                        type="button"
                        class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                        :class="active === sec.id
                            ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200'"
                        @click="active = sec.id"
                    >
                        <component :is="sec.icon" class="size-4 shrink-0" />
                        {{ sec.label }}
                    </button>
                </li>
            </ul>
        </nav>

        <!-- ── Settings panels ────────────────────────────────────────── -->
        <div class="flex-1 space-y-4 min-w-0">

            <!-- Success flash -->
            <Alert v-if="saved" variant="success" dismissible @dismiss="saved = false">
                Settings saved successfully.
            </Alert>

            <!-- ── General ─────────────────────────────────────────── -->
            <Card v-if="active === 'general'" title="General Settings">
                <form class="space-y-4" @submit.prevent="saveSettings">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Input
                            id="app-name"
                            v-model="form.app_name"
                            label="Application Name"
                            placeholder="My SaaS"
                            :error="form.errors.app_name"
                            required
                        />
                        <Input
                            id="app-url"
                            v-model="form.app_url"
                            label="App URL"
                            placeholder="https://app.example.com"
                            :error="form.errors.app_url"
                        />
                    </div>
                    <Input
                        id="support-email"
                        v-model="form.support_email"
                        label="Support email"
                        type="email"
                        placeholder="support@example.com"
                        :error="form.errors.support_email"
                    />
                </form>
            </Card>

            <!-- ── Auth & Security ─────────────────────────────────── -->
            <Card v-if="active === 'auth'" title="Auth & Security">
                <form class="space-y-4" @submit.prevent="saveSettings">
                    <div class="space-y-3">
                        <Checkbox
                            id="allow-reg"
                            v-model="form.allow_registration"
                            label="Allow public registration"
                            description="New tenants can sign up without an invitation."
                        />
                        <Checkbox
                            id="require-verify"
                            v-model="form.require_email_verify"
                            label="Require email verification"
                            description="Users must confirm their email before accessing the app."
                        />
                    </div>
                    <Input
                        id="session-lifetime"
                        v-model="form.session_lifetime"
                        label="Session lifetime (minutes)"
                        type="number"
                        :error="form.errors.session_lifetime"
                    />
                </form>
            </Card>

            <!-- ── Mail ────────────────────────────────────────────── -->
            <Card v-if="active === 'mail'" title="Mail Configuration">
                <form class="space-y-4" @submit.prevent="saveSettings">
                    <Select
                        id="mail-driver"
                        v-model="form.mail_driver"
                        label="Mail driver"
                        :options="mailDriverOptions"
                        :error="form.errors.mail_driver"
                    />
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Input
                            id="mail-from-address"
                            v-model="form.mail_from_address"
                            label="From address"
                            type="email"
                            placeholder="noreply@example.com"
                            :error="form.errors.mail_from_address"
                        />
                        <Input
                            id="mail-from-name"
                            v-model="form.mail_from_name"
                            label="From name"
                            placeholder="My SaaS"
                            :error="form.errors.mail_from_name"
                        />
                    </div>
                </form>
            </Card>

            <!-- ── Notifications ───────────────────────────────────── -->
            <Card v-if="active === 'notifications'" title="Notifications">
                <form class="space-y-4" @submit.prevent="saveSettings">
                    <div class="space-y-3">
                        <Checkbox
                            id="notif-signup"
                            v-model="form.notify_new_signup"
                            label="Notify on new tenant sign-up"
                        />
                        <Checkbox
                            id="notif-payment"
                            v-model="form.notify_payment_fail"
                            label="Notify on failed payment"
                        />
                    </div>
                    <Input
                        id="admin-email"
                        v-model="form.notify_admin_email"
                        label="Admin notification email"
                        type="email"
                        placeholder="admin@example.com"
                        :error="form.errors.notify_admin_email"
                    />
                </form>
            </Card>

            <!-- ── Storage ─────────────────────────────────────────── -->
            <Card v-if="active === 'storage'" title="Storage Settings">
                <form class="space-y-4" @submit.prevent="saveSettings">
                    <Input
                        id="max-storage"
                        v-model="form.max_storage_per_tenant_mb"
                        label="Max storage per tenant (MB)"
                        type="number"
                        :error="form.errors.max_storage_per_tenant_mb"
                    />
                    <Input
                        id="allowed-types"
                        v-model="form.allowed_file_types"
                        label="Allowed file types"
                        placeholder="jpg,png,pdf,xlsx"
                        help-text="Comma-separated list of extensions"
                        :error="form.errors.allowed_file_types"
                    />
                </form>
            </Card>

            <!-- ── System health ───────────────────────────────────── -->
            <Card v-if="active === 'system'" title="System Health">
                <div class="space-y-4">
                    <!-- Health indicators -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div
                            v-for="(value, key) in health"
                            :key="key"
                            class="flex items-center gap-3 rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800"
                        >
                            <span
                                class="block size-2.5 rounded-full"
                                :class="value ? 'bg-emerald-500' : 'bg-red-500'"
                            />
                            <span class="text-sm text-gray-700 dark:text-gray-200 capitalize">
                                {{ key.replace(/_/g, ' ') }}
                            </span>
                            <span
                                class="ml-auto text-xs font-medium"
                                :class="value ? 'text-emerald-600' : 'text-red-500'"
                            >
                                {{ value ? 'OK' : 'Fail' }}
                            </span>
                        </div>
                    </div>

                    <!-- DB stats -->
                    <div v-if="dbStats" class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Database</p>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <div v-for="(val, label) in dbStats" :key="label">
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ val }}</p>
                                <p class="text-xs text-gray-400 capitalize">{{ label.replace(/_/g, ' ') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- ── Save button ─────────────────────────────────────── -->
            <div v-if="active !== 'system'" class="flex justify-end">
                <Button :loading="form.processing" @click="saveSettings">Save Settings</Button>
            </div>
        </div>
    </div>
</template>
