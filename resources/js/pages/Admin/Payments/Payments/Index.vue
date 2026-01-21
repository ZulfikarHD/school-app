<script setup lang="ts">
/**
 * Payments Index Page - Daftar pembayaran dengan filter dan statistik
 * untuk Admin/TU mengelola semua pembayaran (legacy + transactions)
 */
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import {
    Plus, Search, Receipt, Banknote, CreditCard, Eye, Printer,
    Clock, CheckCircle2, XCircle, Calendar, TrendingUp,
    ChevronLeft, ChevronRight, RefreshCw, Layers
} from 'lucide-vue-next';
import { index, create, show, receipt } from '@/routes/admin/payments/records';
import { show as showTransaction, receipt as transactionReceipt } from '@/routes/admin/payments/transactions';
import { Motion } from 'motion-v';

// Unified Record interface untuk gabungan payment + transaction
interface PaymentRecord {
    id: number;
    type: 'payment' | 'transaction';
    reference_number: string;
    students: Array<{
        id: number;
        nama_lengkap: string;
        nis: string;
        kelas: string;
    }>;
    bills: Array<{
        id: number;
        category: string;
        periode: string;
        amount: number;
    }>;
    bill_count: number;
    nominal: number;
    formatted_nominal: string;
    metode_pembayaran: string;
    metode_label: string;
    tanggal_bayar: string;
    formatted_tanggal: string;
    waktu_bayar: string;
    status: 'pending' | 'verified' | 'cancelled';
    status_label: string;
    creator: { id: number; name: string } | null;
    verifier: { id: number; name: string } | null;
    verified_at: string | null;
    created_at: string;
}

interface Stats {
    today: {
        total_transactions: number;
        total_amount: number;
        formatted_total: string;
        pending_count: number;
        verified_count: number;
        by_method: {
            tunai: number;
            transfer: number;
            qris: number;
        };
    };
    pending_verification: number;
}

interface PaginatedData {
    data: PaymentRecord[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Filters {
    search?: string;
    status?: string;
    metode?: string;
    start_date?: string;
    end_date?: string;
    date?: string;
}

interface Props {
    records: PaginatedData;
    stats: Stats;
    filters: Filters;
}

const props = defineProps<Props>();

const haptics = useHaptics();
useModal();

// Local filter state
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const metodeFilter = ref(props.filters.metode || '');
const dateFilter = ref(props.filters.date || new Date().toISOString().split('T')[0]);

// Debounce timer
let searchDebounce: ReturnType<typeof setTimeout> | null = null;

// Computed
const hasRecords = computed(() => props.records.data.length > 0);

const statusOptions = [
    { value: '', label: 'Semua Status' },
    { value: 'pending', label: 'Menunggu Verifikasi' },
    { value: 'verified', label: 'Terverifikasi' },
    { value: 'cancelled', label: 'Dibatalkan' },
];

const metodeOptions = [
    { value: '', label: 'Semua Metode' },
    { value: 'tunai', label: 'Tunai' },
    { value: 'transfer', label: 'Transfer' },
    { value: 'qris', label: 'QRIS' },
];

// Methods
const applyFilters = () => {
    haptics.light();
    router.get(index().url, {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        metode: metodeFilter.value || undefined,
        date: dateFilter.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleSearchInput = () => {
    if (searchDebounce) clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        applyFilters();
    }, 400);
};

const clearFilters = () => {
    haptics.light();
    search.value = '';
    statusFilter.value = '';
    metodeFilter.value = '';
    dateFilter.value = new Date().toISOString().split('T')[0];
    router.get(index().url, {}, { preserveState: true, preserveScroll: true });
};

const goToPage = (url: string | null) => {
    if (!url) return;
    haptics.light();
    router.get(url, {}, { preserveState: true, preserveScroll: true });
};

const viewRecord = (record: PaymentRecord) => {
    haptics.light();
    if (record.type === 'transaction') {
        router.get(showTransaction(record.id).url);
    } else {
        router.get(show(record.id).url);
    }
};

const downloadReceipt = (record: PaymentRecord) => {
    haptics.medium();
    // Gunakan URL stream untuk membuka PDF di browser
    if (record.type === 'transaction') {
        window.open(`/admin/payments/transactions/${record.id}/receipt/stream`, '_blank');
    } else {
        window.open(`/admin/payments/records/${record.id}/receipt/stream`, '_blank');
    }
};

const getStatusConfig = (status: string) => {
    const configs: Record<string, { icon: typeof CheckCircle2; bgClass: string; textClass: string }> = {
        pending: {
            icon: Clock,
            bgClass: 'bg-amber-100 dark:bg-amber-900/30',
            textClass: 'text-amber-700 dark:text-amber-400',
        },
        verified: {
            icon: CheckCircle2,
            bgClass: 'bg-emerald-100 dark:bg-emerald-900/30',
            textClass: 'text-emerald-700 dark:text-emerald-400',
        },
        cancelled: {
            icon: XCircle,
            bgClass: 'bg-slate-100 dark:bg-zinc-800',
            textClass: 'text-slate-500 dark:text-slate-400',
        },
    };
    return configs[status] || configs.pending;
};

const getMetodeIcon = (metode: string) => {
    switch (metode) {
        case 'tunai':
            return Banknote;
        case 'transfer':
        case 'qris':
            return CreditCard;
        default:
            return Banknote;
    }
};

// Watch filters
watch([statusFilter, metodeFilter, dateFilter], () => {
    applyFilters();
});
</script>

<template>
    <AppLayout title="Riwayat Pembayaran">
        <Head title="Riwayat Pembayaran" />

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
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-emerald-400 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/25 shrink-0">
                                <Receipt class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Riwayat Pembayaran
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Kelola dan pantau semua pembayaran
                                </p>
                            </div>
                        </div>
                        <Motion :whileTap="{ scale: 0.97 }">
                            <Link
                                :href="create()"
                                @click="haptics.medium()"
                                class="flex items-center justify-center gap-2 px-5 py-3 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors font-medium shadow-lg shadow-emerald-500/25 active:scale-97
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                            >
                                <Plus class="w-5 h-5" />
                                Catat Pembayaran
                            </Link>
                        </Motion>
                    </div>
                </div>
            </Motion>

            <!-- Stats Cards -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
            >
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Hari Ini -->
                    <div class="col-span-2 bg-linear-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white shadow-lg shadow-emerald-500/25">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-emerald-100 text-sm">Total Hari Ini</p>
                                <p class="text-3xl font-bold mt-1">{{ stats.today.formatted_total }}</p>
                                <p class="text-emerald-200 text-sm mt-2">
                                    {{ stats.today.total_transactions }} transaksi
                                </p>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                                <TrendingUp class="w-6 h-6" />
                            </div>
                        </div>
                    </div>

                    <!-- Verified -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <CheckCircle2 class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ stats.today.verified_count }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Terverifikasi</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pending -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <Clock class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ stats.pending_verification }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Menunggu Verifikasi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Filters -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                        <!-- Search -->
                        <div class="flex-1 relative">
                            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                            <input
                                v-model="search"
                                @input="handleSearchInput"
                                type="text"
                                placeholder="Cari no. kwitansi, nama siswa..."
                                class="w-full pl-12 pr-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:bg-white dark:focus:bg-zinc-900 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                            />
                        </div>

                        <!-- Filter Dropdowns -->
                        <div class="flex flex-wrap gap-3">
                            <!-- Date Filter -->
                            <div class="relative">
                                <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                                <input
                                    v-model="dateFilter"
                                    type="date"
                                    class="pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                                />
                            </div>

                            <!-- Status Filter -->
                            <select
                                v-model="statusFilter"
                                class="px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                            >
                                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>

                            <!-- Metode Filter -->
                            <select
                                v-model="metodeFilter"
                                class="px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                            >
                                <option v-for="opt in metodeOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>

                            <!-- Clear Filters -->
                            <button
                                @click="clearFilters"
                                class="px-4 py-2.5 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-colors flex items-center gap-2"
                            >
                                <RefreshCw class="w-4 h-4" />
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Records List -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <!-- Records List -->
                    <div class="divide-y divide-slate-200 dark:divide-zinc-800">
                        <div
                            v-for="record in records.data"
                            :key="`${record.type}-${record.id}`"
                            class="p-4 hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-colors"
                        >
                            <div class="flex items-start gap-4">
                                <!-- Left: Main Info -->
                                <div class="flex-1 min-w-0">
                                    <!-- Reference & Status -->
                                    <div class="flex items-center gap-2 flex-wrap mb-2">
                                        <p class="font-mono text-sm font-semibold text-slate-900 dark:text-slate-100">
                                            {{ record.reference_number }}
                                        </p>
                                        <span
                                            v-if="record.type === 'transaction' && record.bill_count > 1"
                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400"
                                        >
                                            <Layers class="w-3 h-3" />
                                            {{ record.bill_count }}
                                        </span>
                                        <span
                                            :class="[
                                                'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium',
                                                getStatusConfig(record.status).bgClass,
                                                getStatusConfig(record.status).textClass
                                            ]"
                                        >
                                            <component :is="getStatusConfig(record.status).icon" class="w-3 h-3" />
                                            {{ record.status_label }}
                                        </span>
                                    </div>

                                    <!-- Student & Bills -->
                                    <div class="text-sm text-slate-600 dark:text-slate-400 mb-2">
                                        <span class="font-medium text-slate-900 dark:text-slate-100">
                                            {{ record.students.length === 1 ? record.students[0].nama_lengkap : `${record.students.length} siswa` }}
                                        </span>
                                        <span v-if="record.students.length === 1"> • {{ record.students[0].kelas }}</span>
                                    </div>

                                    <!-- Bills summary -->
                                    <div class="text-sm text-slate-500 dark:text-slate-400">
                                        <span v-if="record.bills.length === 1">
                                            {{ record.bills[0].category }} ({{ record.bills[0].periode }})
                                        </span>
                                        <span v-else>
                                            {{ record.bills.map(b => b.category).join(', ') }}
                                        </span>
                                    </div>

                                    <!-- Date & Method -->
                                    <div class="flex items-center gap-3 mt-2 text-xs text-slate-500 dark:text-slate-400">
                                        <span class="flex items-center gap-1">
                                            <Calendar class="w-3.5 h-3.5" />
                                            {{ record.formatted_tanggal }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <component :is="getMetodeIcon(record.metode_pembayaran)" class="w-3.5 h-3.5" />
                                            {{ record.metode_label }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Right: Amount & Actions -->
                                <div class="text-right shrink-0">
                                    <p class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-2">{{ record.formatted_nominal }}</p>
                                    <div class="flex items-center gap-1 justify-end">
                                        <button
                                            @click="viewRecord(record)"
                                            class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                            title="Lihat Detail"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </button>
                                        <button
                                            v-if="record.status === 'verified'"
                                            @click="downloadReceipt(record)"
                                            class="p-2 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors"
                                            title="Cetak Kwitansi"
                                        >
                                            <Printer class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!hasRecords" class="p-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                            <Receipt class="w-8 h-8 text-slate-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">Belum Ada Pembayaran</h3>
                        <p class="text-slate-500 dark:text-slate-400 mb-4">Belum ada pembayaran yang dicatat untuk filter ini</p>
                        <Link
                            :href="create()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors font-medium"
                        >
                            <Plus class="w-4 h-4" />
                            Catat Pembayaran
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="records.last_page > 1" class="px-6 py-4 border-t border-slate-200 dark:border-zinc-800 flex items-center justify-between">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Menampilkan {{ records.data.length }} dari {{ records.total }} pembayaran
                        </p>
                        <div class="flex items-center gap-1">
                            <button
                                @click="goToPage(records.links[0]?.url)"
                                :disabled="!records.links[0]?.url"
                                class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <ChevronLeft class="w-5 h-5" />
                            </button>
                            <span class="px-3 text-sm text-slate-600 dark:text-slate-400">
                                {{ records.current_page }} / {{ records.last_page }}
                            </span>
                            <button
                                @click="goToPage(records.links[records.links.length - 1]?.url)"
                                :disabled="!records.links[records.links.length - 1]?.url"
                                class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <ChevronRight class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
