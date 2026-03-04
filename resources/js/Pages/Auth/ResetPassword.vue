<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm } from '@inertiajs/vue3';

defineOptions({ layout: AuthLayout });

const props = defineProps({
    token: {
        type: String,
        required: true,
    },
    email: {
        type: String,
        default: '',
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="space-y-6">

        <!-- Heading -->
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gradient-primary">Reset your password</h1>
            <p class="mt-2 text-sm text-gray-500">Choose a new strong password for your account.</p>
        </div>

        <form class="space-y-4" @submit.prevent="submit">

            <!-- Email (readonly, confirms which account is being reset) -->
            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-primary-800">
                    Email address
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    name="email"
                    autocomplete="email"
                    required
                    class="block w-full rounded-lg border px-4 py-3 text-sm shadow-sm bg-gray-50
                           border-gray-300 text-gray-700"
                />
                <p v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</p>
            </div>

            <!-- New password -->
            <div class="space-y-2">
                <label for="password" class="block text-sm font-medium text-primary-800">
                    New password
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    name="password"
                    autocomplete="new-password"
                    required
                    autofocus
                    placeholder="Min. 8 characters"
                    class="block w-full rounded-lg border px-4 py-3 text-sm shadow-sm placeholder:text-gray-400
                           transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white"
                    :class="form.errors.password
                        ? 'border-red-300 text-red-900 focus:ring-red-500'
                        : 'border-gray-300 hover:border-gray-400'"
                />
                <p v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</p>
            </div>

            <!-- Confirm password -->
            <div class="space-y-2">
                <label for="password_confirmation" class="block text-sm font-medium text-primary-800">
                    Confirm new password
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    name="password_confirmation"
                    autocomplete="new-password"
                    required
                    placeholder="Repeat your new password"
                    class="block w-full rounded-lg border px-4 py-3 text-sm shadow-sm placeholder:text-gray-400
                           transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white"
                    :class="form.errors.password_confirmation
                        ? 'border-red-300 text-red-900 focus:ring-red-500'
                        : 'border-gray-300 hover:border-gray-400'"
                />
                <p v-if="form.errors.password_confirmation" class="text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-primary px-4 py-3 text-sm
                       font-semibold text-white shadow-lg shadow-primary-800/40 transition-all hover:opacity-90 hover:shadow-xl
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2
                       disabled:cursor-not-allowed disabled:opacity-60 disabled:shadow-none"
            >
                <svg v-if="form.processing" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                {{ form.processing ? 'Resetting…' : 'Reset password' }}
            </button>
        </form>
    </div>
</template>
