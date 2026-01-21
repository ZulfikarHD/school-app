<script setup lang="ts">
/**
 * Bank Reconciliation Index Page - Daftar rekonsiliasi dan upload statement
 *
 * Menampilkan daftar rekonsiliasi bank dengan fitur upload file baru,
 * filter berdasarkan status, dan navigasi ke halaman matching
 */
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import Badge from '@/components/ui/Badge.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import {
    FileSpreadsheet, Upload, Filter,
    CheckCircle2, Clock, AlertCircle, Trash2, Eye
} from 'lucide-vue-next';
import { index, upload, match, destroy } from '@/routes/admin/payments/reconciliation';
import { Motion } from 'motion-v';

interface Reconciliation {
    id: number;
    filename: string;
    bank_name: string | null;
    statement_date: string | null;
    total_transactions: number;
    total_amount: number;
    formatted_total_amount: string;
    matched_count: number;
    matched_amount: number;
    formatted_matched_amount: string;
    unmatched_count: number;
    match_rate: number;
    status: 'draft' | 'processing' | 'completed' | 'verified';
    status_label: string;
    uploader: { id: number; name: string } | null;
    verifier: { id: number; name: string } | null;
    verified_at: string | null;
    created_at: string;
}

interface Props {
    reconciliations: {
        data: Reconciliation[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        from: number;
        to: number;
        total: number;
    };
    filters: {
        status?: string;
        start_date?: string;
        end_date?: string;
    };
    statusOptions: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

// State
const showUploadModal = ref(false);
const uploadForm = useForm({
    file: null as File | null,
    bank_name: '',
    statement_date: '',
});

// Filter state
const statusFilter = ref(props.filters.status || '');

// Methods
const openUploadModal = () => {
    haptics.light();
    uploadForm.reset();
    showUploadModal.value = true;
};

const handleFileSelect = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        uploadForm.file = input.files[0];
    }
};

const handleUpload = () => {
    if (!uploadForm.file) {
        modal.error('Pilih file terlebih dahulu');
        return;
    }

    haptics.medium();
    uploadForm.post(upload().url, {
        forceFormData: true,
        onSuccess: () => {
            haptics.success();
            showUploadModal.value = false;
        },
        onError: () => {
            haptics.error();
        },
    });
};

const applyFilters = () => {
    haptics.selection();
    router.get(index().url, {
        status: statusFilter.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleDelete = async (reconciliation: Reconciliation) => {
    const confirmed = await modal.confirm(
        'Hapus Rekonsiliasi',
        `Hapus rekonsiliasi "${reconciliation.filename}"? Tindakan ini tidak dapat dibatalkan.`,
        'Ya, Hapus',
        'Batal'
    );

    if (confirmed) {
        haptics.heavy();
        router.delete(destroy(reconciliation.id).url);
    }
};

const getStatusBadgeVariant = (status: string): 'default' | 'success' | 'warning' | 'error' => {
    return {
        draft: 'default' as const,
        processing: 'warning' as const,
        completed: 'success' as const,
        verified: 'success' as const,
    }[status] || 'default';
};

</script>

<template>
    <AppLayout title="Rekonsiliasi Bank">
        <Head title="Rekonsiliasi Bank" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-teal-500/25 shrink-0">
                                <FileSpreadsheet class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Rekonsiliasi Bank
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Matching transaksi bank dengan pembayaran sistem
                                </p>
                            </div>
                        </div>
                        <Motion :whileTap="{ scale: 0.97 }">
                            <button
                                @click="openUploadModal"
                                class="flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors font-medium shadow-lg shadow-emerald-500/25"
                            >
                                <Upload class="w-5 h-5" />
                                Upload Statement
                            </button>
                        </Motion>
                    </div>
                </div>
            </Motion>

            <!-- Filters -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                        <div class="flex items-center gap-2 text-slate-500">
                            <Filter class="w-4 h-4" />
                            <span class="text-sm font-medium">Filter:</span>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <select
                                v-model="statusFilter"
                                @change="applyFilters"
                                class="text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2 px-3 min-h-[44px] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                            >
                                <option value="">Semua Status</option>
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Reconciliation List -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <!-- Empty State -->
                    <div v-if="reconciliations.data.length === 0" class="p-12 text-center">
                        <FileSpreadsheet class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-4" />
                        <p class="text-slate-500 dark:text-slate-400 mb-4">Belum ada data rekonsiliasi</p>
                        <button
                            @click="openUploadModal"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors font-medium"
                        >
                            <Upload class="w-4 h-4" />
                            Upload Statement Pertama
                        </button>
                    </div>

                    <!-- Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-100 dark:border-zinc-800">
                                <tr>
                                    <th class="px-6 py-3.5 text-left font-semibold">File</th>
                                    <th class="px-6 py-3.5 text-left font-semibold">Transaksi</th>
                                    <th class="px-6 py-3.5 text-left font-semibold">Matched</th>
                                    <th class="px-6 py-3.5 text-left font-semibold">Status</th>
                                    <th class="px-6 py-3.5 text-left font-semibold">Tanggal</th>
                                    <th class="px-6 py-3.5 text-right font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                <tr
                                    v-for="reconciliation in reconciliations.data"
                                    :key="reconciliation.id"
                                    class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-slate-100 truncate max-w-[200px]">
                                                {{ reconciliation.filename }}
                                            </p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ reconciliation.bank_name || 'Bank tidak diketahui' }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-slate-900 dark:text-slate-100">
                                            {{ reconciliation.total_transactions }} transaksi
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ reconciliation.formatted_total_amount }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-2 bg-slate-200 dark:bg-zinc-700 rounded-full overflow-hidden max-w-[100px]">
                                                <div
                                                    class="h-full bg-emerald-500 rounded-full transition-all"
                                                    :style="{ width: `${reconciliation.match_rate}%` }"
                                                />
                                            </div>
                                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                                {{ reconciliation.match_rate }}%
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            {{ reconciliation.matched_count }}/{{ reconciliation.total_transactions }} matched
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <Badge
                                            :variant="getStatusBadgeVariant(reconciliation.status)"
                                            size="sm"
                                            dot
                                        >
                                            {{ reconciliation.status_label }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-slate-600 dark:text-slate-400">
                                            {{ reconciliation.created_at }}
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ reconciliation.uploader?.name }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link
                                                :href="match(reconciliation.id)"
                                                @click="haptics.light()"
                                                class="p-2 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors"
                                                title="Lihat Detail"
                                            >
                                                <Eye class="w-4 h-4" />
                                            </Link>
                                            <button
                                                v-if="reconciliation.status === 'draft'"
                                                @click="handleDelete(reconciliation)"
                                                class="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                title="Hapus"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="reconciliations.total > 20" class="px-6 py-4 border-t border-slate-200 dark:border-zinc-800 flex items-center justify-between">
                        <p class="text-sm text-slate-500">
                            Menampilkan {{ reconciliations.from }} - {{ reconciliations.to }} dari {{ reconciliations.total }}
                        </p>
                        <div class="flex gap-1">
                            <Link
                                v-for="(link, i) in reconciliations.links"
                                :key="i"
                                :href="link.url || '#'"
                                :class="[
                                    'min-w-[36px] h-9 px-3 flex items-center justify-center text-sm rounded-lg transition-colors',
                                    link.active
                                        ? 'bg-emerald-500 text-white font-semibold'
                                        : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-zinc-800',
                                    !link.url && 'opacity-50 cursor-not-allowed'
                                ]"
                                preserve-scroll
                            >
                                <span v-html="link.label" />
                            </Link>
                        </div>
                    </div>
                </div>
            </Motion>
        </div>

        <!-- Upload Modal -->
        <BaseModal
            :show="showUploadModal"
            title="Upload Bank Statement"
            @close="showUploadModal = false"
        >
            <div class="p-6 space-y-4">
                <p class="text-slate-600 dark:text-slate-400 text-sm">
                    Upload file statement bank dalam format Excel (.xlsx, .xls) atau CSV untuk melakukan rekonsiliasi dengan pembayaran di sistem.
                </p>

                <!-- File Input -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        File Statement <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            @change="handleFileSelect"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                        />
                    </div>
                    <p v-if="uploadForm.errors.file" class="mt-1.5 text-sm text-red-500">{{ uploadForm.errors.file }}</p>
                    <p class="mt-1.5 text-xs text-slate-500">Format: .xlsx, .xls, .csv (max 10MB)</p>
                </div>

                <!-- Bank Name -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Nama Bank
                    </label>
                    <input
                        v-model="uploadForm.bank_name"
                        type="text"
                        placeholder="Contoh: BCA, Mandiri, BNI"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    />
                </div>

                <!-- Statement Date -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Tanggal Statement
                    </label>
                    <input
                        v-model="uploadForm.statement_date"
                        type="date"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    />
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-zinc-800 flex gap-3">
                <button
                    @click="showUploadModal = false"
                    class="flex-1 px-4 py-2.5 text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-zinc-800 rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors font-medium"
                >
                    Batal
                </button>
                <button
                    @click="handleUpload"
                    :disabled="uploadForm.processing || !uploadForm.file"
                    class="flex-1 px-4 py-2.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                    <Upload v-if="!uploadForm.processing" class="w-4 h-4" />
                    <span v-if="uploadForm.processing">Memproses...</span>
                    <span v-else>Upload & Proses</span>
                </button>
            </div>
        </BaseModal>
    </AppLayout>
</template>
