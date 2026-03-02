<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';
import Alert from '@/Components/UI/Alert.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { UserPlusIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    roles:         { type: Array,   default: () => [] },
    defaultRoleId: { type: [String, Number], default: null },
    canInvite:     { type: Boolean, default: true },
    slotsRemaining: { type: Number, default: null },
});

const form = useForm({
    email:   '',
    role_id: props.defaultRoleId ?? '',
    message: '',
});

const submit = () => {
    form.post(route('tenant.users.store'));
};

const roleOptions = computed(() => props.roles.map((r) => ({ value: r.id, label: r.name })));
</script>

<template>
    <div class="mx-auto max-w-xl space-y-5">

        <!-- ── Breadcrumb ──────────────────────────────────────────────── -->
        <nav class="flex items-center gap-2 text-sm text-gray-400">
            <Link :href="route('tenant.users.index')" class="hover:text-indigo-600">Team Members</Link>
            <span>/</span>
            <span class="text-gray-700 dark:text-gray-200">Invite Member</span>
        </nav>

        <!-- ── Seat limit warning ─────────────────────────────────────── -->
        <Alert
            v-if="!canInvite"
            variant="warning"
            title="Seat limit reached"
        >
            Your plan allows a limited number of team members.
            <Link :href="route('tenant.billing.index')" class="font-medium underline">Upgrade your plan</Link>
            to invite more people.
        </Alert>

        <Alert
            v-else-if="slotsRemaining !== null && slotsRemaining <= 2"
            variant="info"
        >
            You have {{ slotsRemaining }} seat{{ slotsRemaining === 1 ? '' : 's' }} remaining on your current plan.
        </Alert>

        <!-- ── Form card ───────────────────────────────────────────────── -->
        <Card>
            <template #header>
                <div class="flex items-center gap-3 px-5 py-4">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-900/20">
                        <UserPlusIcon class="size-5 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Invite Team Member</p>
                        <p class="text-xs text-gray-400">They'll receive an email with a link to join your workspace.</p>
                    </div>
                </div>
            </template>

            <form class="space-y-5" @submit.prevent="submit">
                <Input
                    id="invite-email"
                    v-model="form.email"
                    label="Email address"
                    type="email"
                    placeholder="colleague@example.com"
                    autocomplete="email"
                    :error="form.errors.email"
                    required
                />

                <Select
                    id="invite-role"
                    v-model="form.role_id"
                    label="Role"
                    placeholder="Select a role…"
                    :options="roleOptions"
                    :error="form.errors.role_id"
                    required
                />

                <!-- Role descriptions -->
                <div
                    v-if="roles.length"
                    class="divide-y divide-gray-50 rounded-xl border border-gray-100 dark:divide-gray-800 dark:border-gray-800"
                >
                    <div
                        v-for="r in roles"
                        :key="r.id"
                        class="flex items-start gap-3 px-4 py-3"
                        :class="String(form.role_id) === String(r.id)
                            ? 'bg-indigo-50/60 dark:bg-indigo-900/10'
                            : ''"
                    >
                        <div
                            class="mt-0.5 size-2 shrink-0 rounded-full"
                            :class="String(form.role_id) === String(r.id) ? 'bg-indigo-500' : 'bg-gray-200 dark:bg-gray-700'"
                        />
                        <div>
                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-100">{{ r.name }}</p>
                            <p class="mt-0.5 text-xs text-gray-400">{{ r.description ?? 'No description provided.' }}</p>
                        </div>
                    </div>
                </div>

                <Input
                    id="invite-message"
                    v-model="form.message"
                    label="Personal message (optional)"
                    placeholder="Hey! I'd love for you to join our workspace on…"
                    multiline
                    :rows="3"
                    :error="form.errors.message"
                />
            </form>

            <template #footer>
                <div class="flex items-center gap-3">
                    <Button :loading="form.processing" :disabled="!canInvite" @click="submit">
                        Send Invitation
                    </Button>
                    <Link :href="route('tenant.users.index')">
                        <Button variant="secondary">Cancel</Button>
                    </Link>
                </div>
            </template>
        </Card>
    </div>
</template>
