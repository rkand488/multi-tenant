<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import Alert from '@/Components/UI/Alert.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { UserCircleIcon, KeyIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    user: { type: Object, required: true },
});

// ── Profile form ─────────────────────────────────────────────────────────────
const profileSaved = ref(false);

const profileForm = useForm({
    name:  props.user.name,
    email: props.user.email,
});

const updateProfile = () => {
    profileSaved.value = false;
    profileForm.put(route('tenant.settings.update-profile'), {
        onSuccess: () => {
            profileSaved.value = true;
        },
    });
};

// ── Password form ────────────────────────────────────────────────────────────
const passwordSaved = ref(false);

const passwordForm = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordSaved.value = false;
    passwordForm.put(route('tenant.settings.update-password'), {
        onSuccess: () => {
            passwordSaved.value = true;
            passwordForm.reset();
        },
    });
};
</script>

<template>
    <div class="mx-auto max-w-2xl space-y-6">

        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Profile Settings</h1>
            <p class="mt-1 text-sm text-gray-500">Update your personal details and login credentials.</p>
        </div>

        <!-- ── Profile information ───────────────────────────────────── -->
        <Card>
            <template #header>
                <div class="flex items-center gap-3 px-5 py-4">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-900/20">
                        <UserCircleIcon class="size-5 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Personal Information</p>
                </div>
            </template>

            <div class="space-y-4">
                <Alert v-if="profileSaved" variant="success">Profile updated successfully.</Alert>

                <div class="flex items-center gap-4">
                    <div class="flex size-16 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 text-xl font-bold text-white shadow-sm">
                        {{ user.name?.charAt(0)?.toUpperCase() ?? '?' }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</p>
                        <p class="text-xs text-gray-400">{{ user.email }}</p>
                    </div>
                </div>

                <Input
                    id="profile-name"
                    v-model="profileForm.name"
                    label="Full name"
                    autocomplete="name"
                    :error="profileForm.errors.name"
                    required
                />

                <Input
                    id="profile-email"
                    v-model="profileForm.email"
                    label="Email address"
                    type="email"
                    autocomplete="email"
                    :error="profileForm.errors.email"
                    required
                />
            </div>

            <template #footer>
                <Button :loading="profileForm.processing" @click="updateProfile">
                    Save Profile
                </Button>
            </template>
        </Card>

        <!-- ── Change password ───────────────────────────────────────── -->
        <Card>
            <template #header>
                <div class="flex items-center gap-3 px-5 py-4">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-900/20">
                        <KeyIcon class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Change Password</p>
                </div>
            </template>

            <div class="space-y-4">
                <Alert v-if="passwordSaved" variant="success">Password changed successfully.</Alert>

                <Input
                    id="current-password"
                    v-model="passwordForm.current_password"
                    label="Current password"
                    type="password"
                    autocomplete="current-password"
                    :error="passwordForm.errors.current_password"
                    required
                />

                <Input
                    id="new-password"
                    v-model="passwordForm.password"
                    label="New password"
                    type="password"
                    autocomplete="new-password"
                    :error="passwordForm.errors.password"
                    required
                />

                <Input
                    id="confirm-password"
                    v-model="passwordForm.password_confirmation"
                    label="Confirm new password"
                    type="password"
                    autocomplete="new-password"
                    :error="passwordForm.errors.password_confirmation"
                    required
                />
            </div>

            <template #footer>
                <Button :loading="passwordForm.processing" @click="updatePassword">
                    Update Password
                </Button>
            </template>
        </Card>
    </div>
</template>
