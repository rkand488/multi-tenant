<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Alert from '@/Components/UI/Alert.vue';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { CurrencyDollarIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    plan: { type: Object, default: null },  // null = create mode
});

const isEditing = computed(() => !!props.plan);

const form = useForm({
    name:                   props.plan?.name             ?? '',
    slug:                   props.plan?.slug             ?? '',
    description:            props.plan?.description      ?? '',
    price_monthly:          props.plan ? (props.plan.price_monthly / 100).toFixed(2) : '',
    price_yearly:           props.plan ? (props.plan.price_yearly  / 100).toFixed(2) : '',
    trial_days:             props.plan?.trial_days        ?? 14,
    is_active:              props.plan?.is_active         ?? true,
    sort_order:             props.plan?.sort_order         ?? 0,
    // Feature limits as simple key/value
    feature_max_users:      props.plan?.features?.max_users              ?? '',
    feature_storage_gb:     props.plan?.features?.storage_gb             ?? '',
    feature_api_calls_day:  props.plan?.features?.api_calls_per_day      ?? '',
    feature_api_access:     props.plan?.features?.api_access             ?? false,
    feature_audit_logs:     props.plan?.features?.audit_logs             ?? false,
    feature_custom_roles:   props.plan?.features?.custom_roles           ?? false,
});

// Auto-slug from name when creating
const handleNameChange = () => {
    if (!isEditing.value) {
        form.slug = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    }
};

const submit = () => {
    const payload = {
        ...form.data(),
        // Convert dollars → cents
        price_monthly: Math.round(parseFloat(form.price_monthly) * 100),
        price_yearly:  Math.round(parseFloat(form.price_yearly)  * 100),
        features: {
            max_users:          form.feature_max_users      ? parseInt(form.feature_max_users)      : null,
            storage_gb:         form.feature_storage_gb     ? parseFloat(form.feature_storage_gb)   : null,
            api_calls_per_day:  form.feature_api_calls_day  ? parseInt(form.feature_api_calls_day)  : null,
            api_access:         form.feature_api_access,
            audit_logs:         form.feature_audit_logs,
            custom_roles:       form.feature_custom_roles,
        },
    };

    if (isEditing.value) {
        form.transform(() => payload).put(route('admin.plans.update', props.plan.id));
    } else {
        form.transform(() => payload).post(route('admin.plans.store'));
    }
};
</script>

<template>
    <div class="space-y-5">

        <!-- Header -->
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                {{ isEditing ? 'Edit Plan' : 'Create Plan' }}
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ isEditing ? `Editing: ${plan.name}` : 'Create a new subscription plan.' }}
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">

            <!-- Basic info -->
            <Card title="Plan details">
                <div class="grid gap-4 sm:grid-cols-2">
                    <Input
                        v-model="form.name"
                        label="Plan name"
                        placeholder="e.g. Starter"
                        :error="form.errors.name"
                        @input="handleNameChange"
                    />
                    <Input
                        v-model="form.slug"
                        label="Slug"
                        placeholder="e.g. starter"
                        :error="form.errors.slug"
                    />
                    <div class="sm:col-span-2">
                        <Input
                            v-model="form.description"
                            label="Description"
                            placeholder="Short description"
                            :error="form.errors.description"
                        />
                    </div>
                    <Input
                        v-model="form.price_monthly"
                        label="Monthly price (USD)"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        :error="form.errors.price_monthly"
                    />
                    <Input
                        v-model="form.price_yearly"
                        label="Yearly price (USD)"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        :error="form.errors.price_yearly"
                    />
                    <Input
                        v-model="form.trial_days"
                        label="Trial days"
                        type="number"
                        min="0"
                        placeholder="0"
                        :error="form.errors.trial_days"
                    />
                    <Input
                        v-model="form.sort_order"
                        label="Sort order"
                        type="number"
                        min="0"
                        :error="form.errors.sort_order"
                    />
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <input id="is_active" v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600" />
                    <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Plan is active (visible to users)</label>
                </div>
            </Card>

            <!-- Feature limits -->
            <Card title="Feature limits">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Input
                        v-model="form.feature_max_users"
                        label="Max users"
                        type="number"
                        min="0"
                        placeholder="Unlimited"
                    />
                    <Input
                        v-model="form.feature_storage_gb"
                        label="Storage (GB)"
                        type="number"
                        step="0.1"
                        min="0"
                        placeholder="Unlimited"
                    />
                    <Input
                        v-model="form.feature_api_calls_day"
                        label="API calls / day"
                        type="number"
                        min="0"
                        placeholder="Unlimited"
                    />
                </div>
                <div class="mt-4 space-y-2">
                    <label class="flex items-center gap-2">
                        <input v-model="form.feature_api_access"   type="checkbox" class="rounded border-gray-300 text-indigo-600" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">API access</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input v-model="form.feature_audit_logs"   type="checkbox" class="rounded border-gray-300 text-indigo-600" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">Audit logs</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input v-model="form.feature_custom_roles" type="checkbox" class="rounded border-gray-300 text-indigo-600" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">Custom roles</span>
                    </label>
                </div>
            </Card>

            <!-- Submit -->
            <div class="flex justify-end gap-3">
                <Button type="button" variant="secondary" @click="() => history.back()">Cancel</Button>
                <Button type="submit" :loading="form.processing" class="flex items-center gap-2">
                    <CurrencyDollarIcon class="size-4" />
                    {{ isEditing ? 'Save changes' : 'Create plan' }}
                </Button>
            </div>

        </form>

    </div>
</template>
