<script setup lang="ts">
/**
 * Payment Verification Queue Page - Antrian verifikasi pembayaran
 *
 * Menampilkan daftar pembayaran yang menunggu verifikasi
 * dengan fitur search dan bulk actions untuk Admin/TU
 */
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import {
    Shield, Search, Clock, CheckCircle2, Eye, ChevronLeft,
    ChevronRight, RefreshCw, AlertCircle
} from 'lucide-vue-next';
import { index, show, verify, verification as verificationRoute } from '@/routes/admin/payments/records';
import { Motion } from 'motion-v';

interface Payment {
    id: number;
    nomor_kwitansi: string;
    bill: {
        id: number;
        nomor_tagihan: string;
        category: string;
        periode: string;
    };
    student: {
        id: number;
        nama_lengkap: string;
        nis: string;
        kelas: string;
    };
    nominal: number;
    formatted_nominal: string;
    metode_pembayaran: string;
    metode_label: string;
    tanggal_bayar: string;
    formatted_tanggal: string;
    waktu_bayar: string;
    status: 'pending' | 'verified' | 'cancelled';
    status_label: string;
    keterangan: string | null;
    creator: { id: number; name: string } | null;
    created_at: string;
}

interface PaginatedData {
    data: Payment[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Filters {
    search?: string;
}

interface Props {
    payments: PaginatedData;
    filters: Filters;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

// Local filter state
const search = ref(props.filters.search || '');

// Debounce timer
let searchDebounce: ReturnType<typeof setTimeout> | null = null;

// Computed
const hasPayments = computed(() => props.payments.data.length > 0);

// Methods
const applyFilters = () => {
    haptics.light();
    router.get(verificationRoute().url, {
        search: search.value || undefined,
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
    router.get(verificationRoute().url, {}, { preserveState: true, preserveScroll: true });
};

const goToPage = (url: string | null) => {
    if (!url) return;
    haptics.light();
    router.get(url, {}, { preserveState: true, preserveScroll: true });
};

const viewPayment = (payment: Payment) => {
    haptics.light();
    router.get(show(payment.id).url);
};

const handleVerify = async (payment: Payment) => {
    const confirmed = await modal.confirm(
        'Verifikasi Pembayaran',
        `Verifikasi pembayaran ${payment.nomor_kwitansi} sebesar ${payment.formatted_nominal}?`,
        'Ya, Verifikasi',
        'Batal'
    );

    if (confirmed) {
        haptics.heavy();
        router.post(verify(payment.id).url, {}, {
            preserveScroll: true,
            onSuccess: () => {
                haptics.success();
            },
            onError: () => {
                haptics.error();
            },
        });
    }
};

const getMetodeIcon = (metode: string) => {
    switch (metode) {
        case 'transfer':
            return 'Transfer';
        case 'qris':
            return 'QRIS';
        default:
            return 'Tunai';
    }
};
</script>

<template>
    <AppLayout title="Verifikasi Pembayaran">
        <Head title="Verifikasi Pembayaran" />

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
                            <Motion :whileTap="{ scale: 0.95 }">
                                <Link
                                    :href="index()"
                                    @click="haptics.light()"
                                    class="w-11 h-11 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 active:bg-slate-200 rounded-xl transition-colors shrink-0
                                           focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
                                >
                                    <ChevronLeft class="w-5 h-5" />
                                </Link>
                            </Motion>
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-amber-400 to-amber-600 flex items-center justify-center shadow-lg shadow-amber-500/25 shrink-0">
                                <Shield class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Verifikasi Pembayaran
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ payments.total }} pembayaran menunggu verifikasi
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Filters -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
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
                                class="w-full pl-12 pr-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:bg-white dark:focus:bg-zinc-900 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                            />
                        </div>

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
            </Motion>

            <!-- Payments List -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <!-- Desktop Table -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50">
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">No. Kwitansi</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Siswa</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Tagihan</th>
                                    <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Nominal</th>
                                    <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Metode</th>
                                    <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Tanggal</th>
                                    <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-zinc-800">
                                <tr
                                    v-for="payment in payments.data"
                                    :key="payment.id"
                                    class="hover:bg-amber-50/50 dark:hover:bg-amber-900/10 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-sm font-medium text-slate-900 dark:text-slate-100">
                                            {{ payment.nomor_kwitansi }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ payment.student.nama_lengkap }}</p>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ payment.student.nis }} • {{ payment.student.kelas }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ payment.bill.category }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ payment.bill.periode }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-bold text-slate-900 dark:text-slate-100">{{ payment.formatted_nominal }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300">
                                            {{ payment.metode_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm text-slate-900 dark:text-slate-100">{{ payment.formatted_tanggal }}</span>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ payment.waktu_bayar }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button
                                                @click="viewPayment(payment)"
                                                class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                                title="Lihat Detail"
                                            >
                                                <Eye class="w-4 h-4" />
                                            </button>
                                            <Motion :whileTap="{ scale: 0.95 }">
                                                <button
                                                    @click="handleVerify(payment)"
                                                    class="flex items-center gap-1 px-3 py-1.5 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors text-sm font-medium"
                                                >
                                                    <CheckCircle2 class="w-4 h-4" />
                                                    Verifikasi
                                                </button>
                                            </Motion>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="lg:hidden divide-y divide-slate-200 dark:divide-zinc-800">
                        <div
                            v-for="payment in payments.data"
                            :key="payment.id"
                            class="p-4"
                        >
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div>
                                    <p class="font-mono text-sm font-medium text-slate-900 dark:text-slate-100">
                                        {{ payment.nomor_kwitansi }}
                                    </p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ payment.formatted_tanggal }} {{ payment.waktu_bayar }}
                                    </p>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                    <Clock class="w-3 h-3" />
                                    Pending
                                </span>
                            </div>

                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-900 dark:text-slate-100 truncate">
                                        {{ payment.student.nama_lengkap }}
                                    </p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ payment.student.nis }} • {{ payment.bill.category }}
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-bold text-slate-900 dark:text-slate-100">{{ payment.formatted_nominal }}</p>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ payment.metode_label }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    @click="viewPayment(payment)"
                                    class="flex-1 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-zinc-800 rounded-lg hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors flex items-center justify-center gap-1"
                                >
                                    <Eye class="w-4 h-4" />
                                    Detail
                                </button>
                                <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                                    <button
                                        @click="handleVerify(payment)"
                                        class="w-full py-2 text-sm font-medium text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 transition-colors flex items-center justify-center gap-1"
                                    >
                                        <CheckCircle2 class="w-4 h-4" />
                                        Verifikasi
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!hasPayments" class="p-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <CheckCircle2 class="w-8 h-8 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">Tidak Ada Pembayaran Pending</h3>
                        <p class="text-slate-500 dark:text-slate-400 mb-4">Semua pembayaran sudah terverifikasi</p>
                        <Link
                            :href="index()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors font-medium"
                        >
                            <ChevronLeft class="w-4 h-4" />
                            Kembali ke Riwayat
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="payments.last_page > 1" class="px-6 py-4 border-t border-slate-200 dark:border-zinc-800 flex items-center justify-between">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Menampilkan {{ payments.data.length }} dari {{ payments.total }} pembayaran
                        </p>
                        <div class="flex items-center gap-1">
                            <button
                                @click="goToPage(payments.links[0]?.url)"
                                :disabled="!payments.links[0]?.url"
                                class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <ChevronLeft class="w-5 h-5" />
                            </button>
                            <span class="px-3 text-sm text-slate-600 dark:text-slate-400">
                                {{ payments.current_page }} / {{ payments.last_page }}
                            </span>
                            <button
                                @click="goToPage(payments.links[payments.links.length - 1]?.url)"
                                :disabled="!payments.links[payments.links.length - 1]?.url"
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
