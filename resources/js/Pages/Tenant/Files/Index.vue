<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Table from '@/Components/UI/Table.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Modal from '@/Components/UI/Modal.vue';
import Alert from '@/Components/UI/Alert.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    FolderIcon,
    ArrowUpTrayIcon,
    TrashIcon,
    ArrowDownTrayIcon,
    DocumentIcon,
    PhotoIcon,
    DocumentTextIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: TenantLayout });

const props = defineProps({
    files:       { type: Object, required: true }, // paginator
    usageBytes:  { type: Number, default: 0 },
    limitBytes:  { type: Number, default: 0 },
    canUpload:   { type: Boolean, default: true },
});

// ── Upload ────────────────────────────────────────────────────────────────────
const showUploadModal = ref(false);
const uploadForm = useForm({ file: null });

const onFileSelected = (e) => {
    uploadForm.file = e.target.files[0] ?? null;
};

const submitUpload = () => {
    uploadForm.post(route('tenant.files.store'), {
        forceFormData: true,
        onSuccess: () => {
            showUploadModal.value = false;
            uploadForm.reset();
        },
    });
};

// ── Delete ────────────────────────────────────────────────────────────────────
const showDeleteModal = ref(false);
const deletingFile = ref(null);
const deleteForm = useForm({});

const openDeleteModal = (file) => {
    deletingFile.value = file;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    deleteForm.delete(route('tenant.files.destroy', deletingFile.value.id), {
        onSuccess: () => (showDeleteModal.value = false),
    });
};

// ── Download ──────────────────────────────────────────────────────────────────
const download = (file) => {
    window.open(route('tenant.files.download', file.id), '_blank');
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const usagePercent = computed(() => {
    if (!props.limitBytes) { return 0; }
    return Math.min(100, Math.round((props.usageBytes / props.limitBytes) * 100));
});

const usageColor = computed(() => {
    if (usagePercent.value >= 90) { return 'bg-red-500'; }
    if (usagePercent.value >= 70) { return 'bg-amber-500'; }
    return 'bg-indigo-500';
});

const formatBytes = (bytes) => {
    if (bytes < 1024) { return bytes + ' B'; }
    if (bytes < 1048576) { return (bytes / 1024).toFixed(1) + ' KB'; }
    if (bytes < 1073741824) { return (bytes / 1048576).toFixed(1) + ' MB'; }
    return (bytes / 1073741824).toFixed(1) + ' GB';
};

const fileIcon = (mimeType) => {
    if (!mimeType) { return DocumentIcon; }
    if (mimeType.startsWith('image/')) { return PhotoIcon; }
    if (mimeType.includes('pdf') || mimeType.includes('text')) { return DocumentTextIcon; }
    return DocumentIcon;
};

const columns = [
    { key: 'name',    label: 'File' },
    { key: 'mime',    label: 'Type',    class: 'w-36' },
    { key: 'size',    label: 'Size',    class: 'w-28' },
    { key: 'date',    label: 'Uploaded', class: 'w-36' },
    { key: 'actions', label: '',        class: 'w-20 text-right' },
];
</script>

<template>
    <div class="space-y-5">

        <!-- Page header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Files</h1>
                <p class="mt-1 text-sm text-gray-500">Manage your workspace file storage.</p>
            </div>
            <Button v-if="canUpload" @click="showUploadModal = true" class="flex items-center gap-2">
                <ArrowUpTrayIcon class="size-4" />
                Upload file
            </Button>
        </div>

        <!-- Storage usage bar -->
        <Card v-if="limitBytes > 0">
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-gray-600 dark:text-gray-400">Storage used</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">
                    {{ formatBytes(usageBytes) }} / {{ formatBytes(limitBytes) }}
                </span>
            </div>
            <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-700">
                <div
                    :class="[usageColor, 'h-2 rounded-full transition-all']"
                    :style="{ width: usagePercent + '%' }"
                />
            </div>
            <p v-if="usagePercent >= 80" class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                You are using {{ usagePercent }}% of your storage. Consider upgrading your plan.
            </p>
        </Card>

        <!-- File list -->
        <Card>
            <Table :columns="columns" :rows="files.data">
                <template #cell-name="{ row }">
                    <div class="flex items-center gap-3">
                        <component :is="fileIcon(row.mime_type)" class="size-5 shrink-0 text-gray-400" />
                        <span class="truncate font-medium text-gray-900 dark:text-gray-100">
                            {{ row.original_name }}
                        </span>
                    </div>
                </template>
                <template #cell-mime="{ row }">
                    <span class="text-xs text-gray-500">{{ row.mime_type }}</span>
                </template>
                <template #cell-size="{ row }">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ formatBytes(row.size) }}</span>
                </template>
                <template #cell-date="{ row }">
                    <span class="text-sm text-gray-500">{{ new Date(row.created_at).toLocaleDateString() }}</span>
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <button
                            class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-indigo-600 dark:hover:bg-gray-700"
                            title="Download"
                            @click="download(row)"
                        >
                            <ArrowDownTrayIcon class="size-4" />
                        </button>
                        <button
                            class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-red-600 dark:hover:bg-gray-700"
                            title="Delete"
                            @click="openDeleteModal(row)"
                        >
                            <TrashIcon class="size-4" />
                        </button>
                    </div>
                </template>
                <template #empty>
                    <div class="py-12 text-center">
                        <FolderIcon class="mx-auto size-10 text-gray-300 dark:text-gray-600" />
                        <p class="mt-3 text-sm text-gray-500">No files uploaded yet.</p>
                    </div>
                </template>
            </Table>

            <div v-if="files.meta?.last_page > 1" class="mt-4">
                <Pagination :meta="files.meta" />
            </div>
        </Card>

        <!-- Upload modal -->
        <Modal :show="showUploadModal" title="Upload File" @close="showUploadModal = false">
            <form @submit.prevent="submitUpload" class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Select file
                    </label>
                    <input
                        type="file"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100"
                        @change="onFileSelected"
                    />
                    <p class="mt-1 text-xs text-gray-400">Max 20 MB. Allowed: jpg, png, pdf, docx, xlsx.</p>
                    <Alert v-if="uploadForm.errors.file" type="error" :message="uploadForm.errors.file" class="mt-2" />
                </div>
                <div class="flex justify-end gap-3">
                    <Button type="button" variant="secondary" @click="showUploadModal = false">Cancel</Button>
                    <Button type="submit" :loading="uploadForm.processing">Upload</Button>
                </div>
            </form>
        </Modal>

        <!-- Delete confirm modal -->
        <Modal :show="showDeleteModal" title="Delete File" @close="showDeleteModal = false">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Are you sure you want to delete
                <strong>{{ deletingFile?.original_name }}</strong>? This action cannot be undone.
            </p>
            <div class="mt-5 flex justify-end gap-3">
                <Button variant="secondary" @click="showDeleteModal = false">Cancel</Button>
                <Button variant="danger" :loading="deleteForm.processing" @click="confirmDelete">Delete</Button>
            </div>
        </Modal>

    </div>
</template>
