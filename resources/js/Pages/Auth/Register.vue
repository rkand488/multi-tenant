<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

defineOptions({ layout: AuthLayout });

const form = useForm({
    workspace_name: '',
    slug: '',
    owner_name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

// Auto-generate slug from workspace name
const onWorkspaceNameInput = () => {
    form.slug = form.workspace_name
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-');
};

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="space-y-6">

        <!-- Heading -->
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">Create your workspace</h1>
            <p class="mt-2 text-sm text-gray-600">Get started for free — no credit card required</p>
        </div>

        <form class="space-y-5" @submit.prevent="submit">

            <!-- Workspace name -->
            <div class="space-y-2">
                <label for="workspace_name" class="block text-sm font-medium text-gray-700">
                    Workspace name
                </label>
                <input
                    id="workspace_name"
                    v-model="form.workspace_name"
                    type="text"
                    autocomplete="organization"
                    required
                    autofocus
                    placeholder="Acme Corp"
                    class="block w-full rounded-lg border px-4 py-3 text-sm shadow-sm placeholder:text-gray-400
                           transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
                           bg-white"
                    :class="form.errors.workspace_name
                        ? 'border-red-300 text-red-900 focus:ring-red-500'
                        : 'border-gray-300 hover:border-gray-400'"
                    @input="onWorkspaceNameInput"
                />
                <p v-if="form.errors.workspace_name" class="text-sm text-red-600">{{ form.errors.workspace_name }}</p>
            </div>

            <!-- Workspace URL / slug -->
            <div class="space-y-2">
                <label for="slug" class="block text-sm font-medium text-gray-700">
                    Workspace URL
                </label>
                <div class="flex rounded-lg border shadow-sm overflow-hidden transition-colors"
                     :class="form.errors.slug ? 'border-red-300' : 'border-gray-300 hover:border-gray-400'">
                    <span class="inline-flex items-center px-3 bg-gray-50 text-sm text-gray-600 border-r border-gray-300">
                        app/
                    </span>
                    <input
                        id="slug"
                        v-model="form.slug"
                        type="text"
                        autocomplete="off"
                        required
                        placeholder="acme-corp"
                        class="flex-1 min-w-0 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-inset
                               focus:ring-primary-500 bg-white"
                    />
                </div>
                <p v-if="form.errors.slug" class="text-sm text-red-600">{{ form.errors.slug }}</p>
            </div>

            <!-- Owner name -->
            <div class="space-y-2">
                <label for="owner_name" class="block text-sm font-medium text-gray-700">
                    Your name
                </label>
                <input
                    id="owner_name"
                    v-model="form.owner_name"
                    type="text"
                    autocomplete="name"
                    required
                    placeholder="Jane Smith"
                    class="block w-full rounded-lg border px-4 py-3 text-sm shadow-sm placeholder:text-gray-400
                           transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
                           bg-white"
                    :class="form.errors.owner_name
                        ? 'border-red-300 text-red-900 focus:ring-red-500'
                        : 'border-gray-300 hover:border-gray-400'"
                />
                <p v-if="form.errors.owner_name" class="text-sm text-red-600">{{ form.errors.owner_name }}</p>
            </div>

            <!-- Email -->
            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Email address
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    required
                    placeholder="you@example.com"
                    class="block w-full rounded-lg border px-4 py-3 text-sm shadow-sm placeholder:text-gray-400
                           transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
                           bg-white"
                    :class="form.errors.email
                        ? 'border-red-300 text-red-900 focus:ring-red-500'
                        : 'border-gray-300 hover:border-gray-400'"
                />
                <p v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</p>
            </div>

            <!-- Password -->
            <div class="space-y-2">
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Password
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    required
                    placeholder="Min. 8 characters"
                    class="block w-full rounded-lg border px-4 py-3 text-sm shadow-sm placeholder:text-gray-400
                           transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
                           bg-white"
                    :class="form.errors.password
                        ? 'border-red-300 text-red-900 focus:ring-red-500'
                        : 'border-gray-300 hover:border-gray-400'"
                />
                <p v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</p>
            </div>

            <!-- Confirm password -->
            <div class="space-y-2">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Confirm password
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    required
                    placeholder="Confirm your password"
                    class="block w-full rounded-lg border px-4 py-3 text-sm shadow-sm placeholder:text-gray-400
                           transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
                           bg-white"
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
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-primary-600 to-primary-700 px-4 py-3 text-sm
                       font-semibold text-white shadow-lg shadow-primary-500/50 transition-all hover:shadow-xl hover:shadow-primary-500/60 hover:from-primary-700 hover:to-primary-800
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2
                       disabled:cursor-not-allowed disabled:opacity-60 disabled:shadow-none"
            >
                <svg
                    v-if="form.processing"
                    class="size-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>

                {{ form.processing ? 'Creating workspace…' : 'Create workspace' }}
            </button>
        </form>

        <!-- Login link -->
        <p class="text-center text-sm text-gray-600">
            Already have an account?
            <Link
                :href="route('login')"
                class="font-semibold text-primary-600 hover:text-primary-700 transition-colors"
            >
                Sign in
            </Link>
        </p>
    </div>
</template>
