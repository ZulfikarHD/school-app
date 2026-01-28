<script setup lang="ts">
/**
 * Admin PSB Payments Page
 *
 * Halaman untuk verifikasi pembayaran PSB,
 * yaitu: listing pending payments, preview bukti bayar, dan approve/reject
 */
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    CreditCard,
    Search,
    FileCheck,
    Eye,
    CheckCircle,
    XCircle,
    Clock,
    Calendar,
    User,
    Receipt,
    ArrowLeft,
    X,
    ZoomIn,
    ChevronLeft,
    ChevronRight,
    Filter
} from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import Badge from '@/components/ui/Badge.vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import DialogModal from '@/components/ui/DialogModal.vue';
import { useHaptics } from '@/composables/useHaptics';
// TODO: Uncomment setelah backend routes dibuat dan Wayfinder generate routes
// import { index as paymentsIndex, verify } from '@/routes/admin/psb/payments';
import { index as psbIndex } from '@/routes/admin/psb';

// Temporary route functions sampai Wayfinder generate
const paymentsIndex = (options?: { query?: Record<string, any> }) => ({
    url: '/admin/psb/payments' + (options?.query ? '?' + new URLSearchParams(options.query as any).toString() : ''),
});
const verify = (paymentId: number) => ({ url: `/admin/psb/payments/${paymentId}/verify` });

/**
 * Interface untuk data payment
 */
interface Payment {
    id: number;
    registration: {
        id: number;
        registration_number: string;
        student_name: string;
        parent_name: string;
    };
    amount: number;
    formatted_amount: string;
    proof_url: string;
    status: 'pending' | 'approved' | 'rejected';
    uploaded_at: string;
    verified_at: string | null;
    verified_by: string | null;
    notes: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    payments: {
        data: Payment[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        per_page: number;
    };
    filters: {
        status?: string;
        search?: string;
    };
}

const props = defineProps<Props>();
const haptics = useHaptics();

// Local state
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const showFilters = ref(false);

// Preview modal state
const previewPayment = ref<Payment | null>(null);
const showPreviewModal = ref(false);

// Verification modal state
const verifyingPayment = ref<Payment | null>(null);
const showVerifyModal = ref(false);
const verifyAction = ref<'approve' | 'reject'>('approve');

// Form untuk verifikasi
const verifyForm = useForm({
    approved: true,
    notes: '',
});

// Debounce search
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

/**
 * Handle search dengan debounce
 */
const handleSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

/**
 * Apply filters
 */
const applyFilters = () => {
    haptics.selection();

    router.get(
        paymentsIndex().url,
        {
            search: search.value || undefined,
            status: statusFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

/**
 * Open preview modal
 */
const openPreview = (payment: Payment) => {
    haptics.light();
    previewPayment.value = payment;
    showPreviewModal.value = true;
};

/**
 * Close preview modal
 */
const closePreview = () => {
    showPreviewModal.value = false;
    previewPayment.value = null;
};

/**
 * Open verify modal
 */
const openVerifyModal = (payment: Payment, action: 'approve' | 'reject') => {
    haptics.medium();
    verifyingPayment.value = payment;
    verifyAction.value = action;
    verifyForm.reset();
    verifyForm.approved = action === 'approve';
    showVerifyModal.value = true;
};

/**
 * Close verify modal
 */
const closeVerifyModal = () => {
    showVerifyModal.value = false;
    verifyingPayment.value = null;
    verifyForm.reset();
};

/**
 * Submit verification
 */
const submitVerification = () => {
    if (!verifyingPayment.value) return;

    verifyForm.post(verify(verifyingPayment.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            closeVerifyModal();
        },
        onError: () => {
            haptics.error();
        },
    });
};

/**
 * Get status badge variant
 */
const getStatusVariant = (status: string): 'success' | 'warning' | 'error' | 'default' => {
    const variants: Record<string, 'success' | 'warning' | 'error' | 'default'> = {
        approved: 'success',
        pending: 'warning',
        rejected: 'error',
    };
    return variants[status] || 'default';
};

/**
 * Get status label
 */
const getStatusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        approved: 'Disetujui',
        pending: 'Menunggu',
        rejected: 'Ditolak',
    };
    return labels[status] || status;
};

/**
 * Format date
 */
const formatDate = (dateString: string): string => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

/**
 * Verify modal config
 */
const verifyModalConfig = computed(() => {
    if (verifyAction.value === 'approve') {
        return {
            type: 'success' as const,
            title: 'Setujui Pembayaran',
            message: `Apakah Anda yakin ingin menyetujui pembayaran dari ${verifyingPayment.value?.registration.student_name}? Siswa akan otomatis terdaftar ke sistem.`,
            confirmText: 'Ya, Setujui',
        };
    }
    return {
        type: 'danger' as const,
        title: 'Tolak Pembayaran',
        message: `Apakah Anda yakin ingin menolak pembayaran dari ${verifyingPayment.value?.registration.student_name}?`,
        confirmText: 'Ya, Tolak',
    };
});
</script>

<template>
    <AppLayout>
        <Head title="Verifikasi Pembayaran PSB" />

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
                            <Link
                                :href="psbIndex().url"
                                class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                            >
                                <ArrowLeft :size="20" class="text-slate-600 dark:text-slate-400" />
                            </Link>
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/25 shrink-0">
                                <CreditCard :size="24" class="text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Verifikasi Pembayaran
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Verifikasi bukti pembayaran daftar ulang
                                </p>
                            </div>
                        </div>

                        <button
                            @click="showFilters = !showFilters"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-zinc-800 rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                        >
                            <Filter class="w-4 h-4" />
                            Filter
                        </button>
                    </div>

                    <!-- Filters -->
                    <Motion
                        v-if="showFilters"
                        :initial="{ opacity: 0, height: 0 }"
                        :animate="{ opacity: 1, height: 'auto' }"
                        :transition="{ duration: 0.2 }"
                    >
                        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-zinc-800">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <!-- Search -->
                                <div class="flex-1">
                                    <div class="relative">
                                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                        <input
                                            v-model="search"
                                            @input="handleSearch"
                                            type="text"
                                            placeholder="Cari nama siswa..."
                                            class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                                        />
                                    </div>
                                </div>

                                <!-- Status Filter -->
                                <div class="sm:w-48">
                                    <select
                                        v-model="statusFilter"
                                        @change="applyFilters"
                                        class="w-full text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2.5 px-3 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                                    >
                                        <option value="">Semua Status</option>
                                        <option value="pending">Menunggu</option>
                                        <option value="approved">Disetujui</option>
                                        <option value="rejected">Ditolak</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </Motion>
                </div>
            </Motion>

            <!-- Desktop Table -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="hidden md:block bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-100 dark:border-zinc-800">
                                <tr>
                                    <th class="px-6 py-3.5 font-semibold tracking-wide">Pendaftar</th>
                                    <th class="px-6 py-3.5 font-semibold tracking-wide">Jumlah</th>
                                    <th class="px-6 py-3.5 font-semibold tracking-wide">Bukti</th>
                                    <th class="px-6 py-3.5 font-semibold tracking-wide">Tanggal Upload</th>
                                    <th class="px-6 py-3.5 font-semibold tracking-wide">Status</th>
                                    <th class="px-6 py-3.5 font-semibold tracking-wide text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                <!-- Empty State -->
                                <tr v-if="payments.data.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                                                <Receipt class="w-8 h-8 text-slate-300 dark:text-zinc-600" />
                                            </div>
                                            <div>
                                                <p class="font-medium text-slate-700 dark:text-slate-300">Tidak ada pembayaran</p>
                                                <p class="text-sm text-slate-400 mt-1">
                                                    Belum ada bukti pembayaran yang diupload
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Data Rows -->
                                <tr
                                    v-for="payment in payments.data"
                                    :key="payment.id"
                                    class="group hover:bg-slate-50/80 dark:hover:bg-zinc-800/50 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-900 dark:text-slate-100">
                                                {{ payment.registration.student_name }}
                                            </span>
                                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                {{ payment.registration.registration_number }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-slate-900 dark:text-slate-100">
                                            {{ payment.formatted_amount }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button
                                            @click="openPreview(payment)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors"
                                        >
                                            <Eye :size="14" />
                                            Lihat Bukti
                                        </button>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                                            <Calendar :size="14" />
                                            <span class="text-xs">{{ formatDate(payment.uploaded_at) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <Badge :variant="getStatusVariant(payment.status)" size="sm" dot>
                                            {{ getStatusLabel(payment.status) }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div v-if="payment.status === 'pending'" class="flex items-center justify-end gap-1.5">
                                            <Motion :whileTap="{ scale: 0.95 }">
                                                <button
                                                    @click="openVerifyModal(payment, 'approve')"
                                                    class="w-9 h-9 flex items-center justify-center text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-xl transition-colors border border-transparent hover:border-emerald-200 dark:hover:border-emerald-800"
                                                    title="Setujui"
                                                >
                                                    <CheckCircle class="w-4 h-4" />
                                                </button>
                                            </Motion>
                                            <Motion :whileTap="{ scale: 0.95 }">
                                                <button
                                                    @click="openVerifyModal(payment, 'reject')"
                                                    class="w-9 h-9 flex items-center justify-center text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors border border-transparent hover:border-red-200 dark:hover:border-red-800"
                                                    title="Tolak"
                                                >
                                                    <XCircle class="w-4 h-4" />
                                                </button>
                                            </Motion>
                                        </div>
                                        <span v-else class="text-xs text-slate-400 dark:text-slate-500">
                                            {{ payment.verified_by ? `oleh ${payment.verified_by}` : '-' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </Motion>

            <!-- Mobile Cards -->
            <div class="md:hidden space-y-3">
                <!-- Empty State -->
                <div v-if="payments.data.length === 0" class="py-12 text-center bg-white dark:bg-zinc-900 rounded-2xl border border-slate-100 dark:border-zinc-800">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-linear-to-br from-slate-100 to-slate-200 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center">
                        <Receipt class="w-8 h-8 text-slate-400 dark:text-zinc-500" />
                    </div>
                    <p class="font-medium text-slate-700 dark:text-slate-300">Tidak ada pembayaran</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Belum ada bukti pembayaran yang diupload</p>
                </div>

                <!-- Data Cards -->
                <Motion
                    v-for="payment in payments.data"
                    :key="payment.id"
                    :whileTap="{ scale: 0.98 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-800 overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-slate-900 dark:text-slate-100 truncate">
                                        {{ payment.registration.student_name }}
                                    </h3>
                                    <p class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ payment.registration.registration_number }}
                                    </p>
                                </div>
                                <Badge :variant="getStatusVariant(payment.status)" size="xs" dot>
                                    {{ getStatusLabel(payment.status) }}
                                </Badge>
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Jumlah</p>
                                    <p class="font-bold text-lg text-slate-900 dark:text-slate-100">
                                        {{ payment.formatted_amount }}
                                    </p>
                                </div>
                                <button
                                    @click="openPreview(payment)"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors"
                                >
                                    <Eye :size="16" />
                                    Lihat Bukti
                                </button>
                            </div>

                            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-zinc-800">
                                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <Clock :size="12" />
                                    <span>Upload: {{ formatDate(payment.uploaded_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div v-if="payment.status === 'pending'" class="flex border-t border-slate-100 dark:border-zinc-800">
                            <button
                                @click="openVerifyModal(payment, 'approve')"
                                class="flex-1 flex items-center justify-center gap-2 py-3 text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"
                            >
                                <CheckCircle :size="16" />
                                Setujui
                            </button>
                            <div class="w-px bg-slate-100 dark:bg-zinc-800"></div>
                            <button
                                @click="openVerifyModal(payment, 'reject')"
                                class="flex-1 flex items-center justify-center gap-2 py-3 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            >
                                <XCircle :size="16" />
                                Tolak
                            </button>
                        </div>
                    </div>
                </Motion>
            </div>

            <!-- Pagination -->
            <Motion
                v-if="payments.total > payments.per_page"
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ duration: 0.3, delay: 0.2 }"
                class="pt-4 border-t border-slate-200 dark:border-zinc-800"
            >
                <!-- Mobile Pagination -->
                <div class="flex items-center justify-between md:hidden">
                    <div class="text-sm text-slate-500">
                        {{ payments.from }}-{{ payments.to }} dari {{ payments.total }}
                    </div>
                    <div class="flex gap-2">
                        <Link
                            :href="payments.links[0]?.url || '#'"
                            :class="[
                                'flex items-center justify-center w-11 h-11 rounded-xl border transition-colors',
                                payments.links[0]?.url
                                    ? 'border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800'
                                    : 'border-slate-100 dark:border-zinc-800 text-slate-300 dark:text-zinc-600 cursor-not-allowed'
                            ]"
                            preserve-scroll
                            preserve-state
                        >
                            <ChevronLeft class="w-5 h-5" />
                        </Link>
                        <div class="flex items-center justify-center px-4 h-11 bg-emerald-500 text-white rounded-xl font-semibold text-sm min-w-[44px]">
                            {{ payments.current_page }}
                        </div>
                        <Link
                            :href="payments.links[payments.links.length - 1]?.url || '#'"
                            :class="[
                                'flex items-center justify-center w-11 h-11 rounded-xl border transition-colors',
                                payments.links[payments.links.length - 1]?.url
                                    ? 'border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800'
                                    : 'border-slate-100 dark:border-zinc-800 text-slate-300 dark:text-zinc-600 cursor-not-allowed'
                            ]"
                            preserve-scroll
                            preserve-state
                        >
                            <ChevronRight class="w-5 h-5" />
                        </Link>
                    </div>
                </div>

                <!-- Desktop Pagination -->
                <div class="hidden md:flex items-center justify-between">
                    <div class="text-sm text-slate-500">
                        Menampilkan {{ payments.from }} - {{ payments.to }} dari {{ payments.total }} data
                    </div>
                    <div class="flex gap-1">
                        <Link
                            v-for="(link, i) in payments.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'min-w-[36px] h-9 px-3 flex items-center justify-center text-sm rounded-lg transition-colors',
                                link.active
                                    ? 'bg-emerald-500 text-white font-semibold shadow-sm'
                                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-zinc-800',
                                !link.url && 'opacity-50 cursor-not-allowed hover:bg-transparent'
                            ]"
                            preserve-scroll
                            preserve-state
                        >
                            <span v-html="link.label" />
                        </Link>
                    </div>
                </div>
            </Motion>
        </div>

        <!-- Preview Modal -->
        <BaseModal
            :show="showPreviewModal"
            size="lg"
            @close="closePreview"
        >
            <template #header>
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Bukti Pembayaran
                    </h3>
                    <button
                        @click="closePreview"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                    >
                        <X :size="20" />
                    </button>
                </div>
            </template>

            <div v-if="previewPayment" class="space-y-4">
                <!-- Payment Info -->
                <div class="grid grid-cols-2 gap-4 p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-xl">
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Nama Siswa</p>
                        <p class="font-medium text-slate-900 dark:text-slate-100">
                            {{ previewPayment.registration.student_name }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">No. Pendaftaran</p>
                        <p class="font-mono text-sm text-slate-900 dark:text-slate-100">
                            {{ previewPayment.registration.registration_number }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Jumlah</p>
                        <p class="font-bold text-emerald-600 dark:text-emerald-400">
                            {{ previewPayment.formatted_amount }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tanggal Upload</p>
                        <p class="text-sm text-slate-900 dark:text-slate-100">
                            {{ formatDate(previewPayment.uploaded_at) }}
                        </p>
                    </div>
                </div>

                <!-- Proof Image -->
                <div class="relative rounded-xl overflow-hidden border border-slate-200 dark:border-zinc-700">
                    <img
                        :src="previewPayment.proof_url"
                        :alt="`Bukti pembayaran ${previewPayment.registration.student_name}`"
                        class="w-full h-auto max-h-[60vh] object-contain bg-slate-100 dark:bg-zinc-800"
                    />
                    <a
                        :href="previewPayment.proof_url"
                        target="_blank"
                        class="absolute top-3 right-3 p-2 bg-white/90 dark:bg-zinc-900/90 rounded-lg shadow-sm hover:bg-white dark:hover:bg-zinc-900 transition-colors"
                    >
                        <ZoomIn :size="18" class="text-slate-600 dark:text-slate-400" />
                    </a>
                </div>
            </div>

            <template #footer>
                <div v-if="previewPayment?.status === 'pending'" class="flex gap-3">
                    <button
                        @click="closePreview(); openVerifyModal(previewPayment!, 'reject')"
                        class="flex-1 px-4 py-2.5 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 rounded-xl font-medium hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                    >
                        Tolak
                    </button>
                    <button
                        @click="closePreview(); openVerifyModal(previewPayment!, 'approve')"
                        class="flex-1 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-medium transition-colors shadow-sm"
                    >
                        Setujui
                    </button>
                </div>
                <button
                    v-else
                    @click="closePreview"
                    class="w-full px-4 py-2.5 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                >
                    Tutup
                </button>
            </template>
        </BaseModal>

        <!-- Verification Modal -->
        <DialogModal
            :show="showVerifyModal"
            :type="verifyModalConfig.type"
            :title="verifyModalConfig.title"
            :message="verifyModalConfig.message"
            :confirm-text="verifyModalConfig.confirmText"
            cancel-text="Batal"
            :loading="verifyForm.processing"
            @confirm="submitVerification"
            @cancel="closeVerifyModal"
            @close="closeVerifyModal"
        />
    </AppLayout>
</template>
