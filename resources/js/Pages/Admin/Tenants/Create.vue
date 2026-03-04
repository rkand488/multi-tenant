<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: AdminLayout });

const form = useForm({
    name: '',
    slug: '',
    owner_email: '',
});

const autoSlug = () => {
    if (!form.slug) {
        form.slug = form.name
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
    }
};

const submit = () => {
    form.post(route('admin.tenants.store'));
};
</script>

<template>
    <div class="space-y-5">

        <!-- ── Header ──────────────────────────────────────────────────── -->
        <div class="flex items-center gap-3">
            <Link :href="route('admin.tenants.index')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                <ArrowLeftIcon class="size-5" />
            </Link>
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">New Tenant</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manually provision a new tenant workspace.</p>
            </div>
        </div>

        <!-- ── Form ───────────────────────────────────────────────────── -->
        <Card title="Tenant Details" class="max-w-lg">
            <form class="space-y-4" @submit.prevent="submit">

                <!-- Name -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Company / Tenant Name <span class="text-red-500">*</span>
                    </label>
                    <Input
                        v-model="form.name"
                        type="text"
                        placeholder="Acme Corp"
                        :error="form.errors.name"
                        @blur="autoSlug"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                </div>

                <!-- Slug -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Slug <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center rounded-lg border border-gray-200 bg-white shadow-sm focus-within:border-indigo-400 focus-within:ring-2 focus-within:ring-indigo-100 dark:border-gray-700 dark:bg-gray-800">
                        <input
                            v-model="form.slug"
                            type="text"
                            placeholder="acme-corp"
                            class="min-w-0 flex-1 bg-transparent px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none dark:text-gray-100"
                        />
                        <span class="whitespace-nowrap pr-3 text-xs text-gray-400">.app</span>
                    </div>
                    <p v-if="form.errors.slug" class="mt-1 text-xs text-red-500">{{ form.errors.slug }}</p>
                    <p v-else class="mt-1 text-xs text-gray-400">Lowercase letters, numbers, and hyphens only. Used as the subdomain.</p>
                </div>

                <!-- Owner email -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Owner Email <span class="text-red-500">*</span>
                    </label>
                    <Input
                        v-model="form.owner_email"
                        type="email"
                        placeholder="owner@example.com"
                        :error="form.errors.owner_email"
                    />
                    <p v-if="form.errors.owner_email" class="mt-1 text-xs text-red-500">{{ form.errors.owner_email }}</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3 pt-2">
                    <Button type="submit" :loading="form.processing">Create Tenant</Button>
                    <Link :href="route('admin.tenants.index')">
                        <Button type="button" variant="secondary">Cancel</Button>
                    </Link>
                </div>

            </form>
        </Card>
    </div>
</template>
