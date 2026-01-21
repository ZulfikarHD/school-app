<script setup lang="ts">
/**
 * Bills Index Page - Halaman daftar tagihan pembayaran siswa
 * dengan fitur search, filter, dan aksi pembatalan tagihan
 */
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import {
    Receipt, Search, Filter, MoreVertical, XCircle, Plus, X,
    FileText, AlertCircle, CheckCircle2, Clock, ChevronLeft, ChevronRight
} from 'lucide-vue-next';
import { index, destroy, generate as showGenerate } from '@/routes/admin/payments/bills';
import { Motion } from 'motion-v';

interface Student {
    id: number;
    nis: string;
    nama_lengkap: string;
    kelas?: {
        id: number;
        tingkat: number;
        nama: string;
        nama_lengkap: string;
    };
}

interface PaymentCategory {
    id: number;
    nama: string;
    kode: string;
}

interface Bill {
    id: number;
    nomor_tagihan: string;
    student_id: number;
    student: Student;
    payment_category_id: number;
    payment_category: PaymentCategory;
    bulan: number;
    tahun: number;
    nominal: number;
    nominal_terbayar: number;
    status: 'belum_bayar' | 'sebagian' | 'lunas' | 'dibatalkan';
    tanggal_jatuh_tempo: string;
    formatted_nominal?: string;
    formatted_sisa?: string;
    status_label?: string;
    nama_bulan?: string;
    created_at: string;
}

interface SchoolClass {
    id: number;
    tingkat: number;
    nama: string;
}

interface Stats {
    total_bills: number;
    unpaid: number;
    partial: number;
    paid: number;
    total_nominal: number;
    total_paid: number;
    total_outstanding: number;
}

interface Props {
    bills: {
        data: Bill[];
        links: any;
        meta?: any;
    };
    categories: PaymentCategory[];
    classes: SchoolClass[];
    stats: Stats;
    filters: {
        search?: string;
        status?: string;
        category_id?: string;
        bulan?: string;
        tahun?: string;
        kelas_id?: string;
    };
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();
const loading = ref(false);
const showFilters = ref(false);
const activeDropdown = ref<number | null>(null);

// Local filter state
const searchQuery = ref(props.filters.search || '');
const filterStatus = ref(props.filters.status || '');
const filterCategoryId = ref(props.filters.category_id || '');
const filterBulan = ref(props.filters.bulan || '');
const filterTahun = ref(props.filters.tahun || '');
const filterKelasId = ref(props.filters.kelas_id || '');

// Month names
const months = [
    { value: '1', label: 'Januari' },
    { value: '2', label: 'Februari' },
    { value: '3', label: 'Maret' },
    { value: '4', label: 'April' },
    { value: '5', label: 'Mei' },
    { value: '6', label: 'Juni' },
    { value: '7', label: 'Juli' },
    { value: '8', label: 'Agustus' },
    { value: '9', label: 'September' },
    { value: '10', label: 'Oktober' },
    { value: '11', label: 'November' },
    { value: '12', label: 'Desember' },
];

// Generate year options
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => ({
    value: String(currentYear - 2 + i),
    label: String(currentYear - 2 + i),
}));

// Computed
const hasActiveFilters = computed(() => {
    return filterStatus.value || filterCategoryId.value || filterBulan.value || filterTahun.value || filterKelasId.value;
});

// Methods
const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const getStatusBadgeClass = (status: string): string => {
    const classes: Record<string, string> = {
        belum_bayar: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        sebagian: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        lunas: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        dibatalkan: 'bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-slate-400',
    };
    return classes[status] || 'bg-slate-100 text-slate-700';
};

const getStatusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        belum_bayar: 'Belum Bayar',
        sebagian: 'Sebagian',
        lunas: 'Lunas',
        dibatalkan: 'Dibatalkan',
    };
    return labels[status] || status;
};

const getStatusIcon = (status: string) => {
    const icons: Record<string, any> = {
        belum_bayar: Clock,
        sebagian: AlertCircle,
        lunas: CheckCircle2,
        dibatalkan: XCircle,
    };
    return icons[status] || Clock;
};

const getMonthName = (bulan: number): string => {
    return months.find(m => m.value === String(bulan))?.label || '';
};

const applyFilters = () => {
    haptics.light();
    router.get(index().url, {
        search: searchQuery.value || undefined,
        status: filterStatus.value || undefined,
        category_id: filterCategoryId.value || undefined,
        bulan: filterBulan.value || undefined,
        tahun: filterTahun.value || undefined,
        kelas_id: filterKelasId.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    haptics.light();
    searchQuery.value = '';
    filterStatus.value = '';
    filterCategoryId.value = '';
    filterBulan.value = '';
    filterTahun.value = '';
    filterKelasId.value = '';
    router.get(index().url, {}, { preserveState: true, replace: true });
};

const toggleDropdown = (id: number) => {
    activeDropdown.value = activeDropdown.value === id ? null : id;
};

const closeDropdown = () => {
    activeDropdown.value = null;
};

const handleCancel = async (bill: Bill) => {
    closeDropdown();
    const confirmed = await modal.confirmDelete(
        `Apakah Anda yakin ingin membatalkan tagihan "${bill.nomor_tagihan}"?`
    );

    if (confirmed) {
        haptics.heavy();
        router.delete(destroy(bill.id).url, {
            onStart: () => loading.value = true,
            onFinish: () => loading.value = false,
            onSuccess: () => {
                modal.success('Tagihan berhasil dibatalkan');
                haptics.success();
            },
            onError: (errors) => {
                haptics.error();
                modal.error(errors.error || 'Gagal membatalkan tagihan');
            },
        });
    }
};

const handleClickOutside = () => {
    closeDropdown();
};
</script>

<template>
    <AppLayout>
        <Head title="Daftar Tagihan" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8" @click="handleClickOutside">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-violet-400 to-violet-600 flex items-center justify-center shadow-lg shadow-violet-500/25 shrink-0">
                                <Receipt class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Daftar Tagihan
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Kelola tagihan pembayaran siswa
                                </p>
                            </div>
                        </div>

                        <Motion :whileTap="{ scale: 0.97 }">
                            <Link
                                :href="showGenerate()"
                                @click="haptics.light()"
                                class="group flex items-center gap-2.5 px-5 py-2.5 min-h-[44px] bg-linear-to-r from-violet-500 to-purple-500 text-white rounded-xl hover:from-violet-600 hover:to-purple-600 transition-all duration-200 shadow-lg shadow-violet-500/30
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                            >
                                <Plus class="w-5 h-5 transition-transform group-hover:rotate-90 duration-200" />
                                <span class="font-semibold hidden sm:inline">Generate Tagihan</span>
                                <span class="font-semibold sm:hidden">Generate</span>
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
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <Clock class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ stats.unpaid }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Belum Bayar</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <AlertCircle class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ stats.partial }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Sebagian</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <CheckCircle2 class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ stats.paid }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Lunas</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                <FileText class="w-5 h-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ formatCurrency(stats.total_outstanding) }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Total Tunggakan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Filters & Search -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Search -->
                        <div class="flex-1 relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari nomor tagihan atau nama siswa..."
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <!-- Filter Toggle Button (Mobile) -->
                        <button
                            @click="showFilters = !showFilters; haptics.light()"
                            class="sm:hidden flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300"
                        >
                            <Filter class="w-5 h-5" />
                            <span>Filter</span>
                            <span v-if="hasActiveFilters" class="w-2 h-2 rounded-full bg-violet-500"></span>
                        </button>

                        <!-- Desktop Filters -->
                        <div class="hidden sm:flex items-center gap-3 flex-wrap">
                            <select
                                v-model="filterStatus"
                                class="px-3 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"
                                @change="applyFilters"
                            >
                                <option value="">Semua Status</option>
                                <option value="belum_bayar">Belum Bayar</option>
                                <option value="sebagian">Sebagian</option>
                                <option value="lunas">Lunas</option>
                            </select>

                            <select
                                v-model="filterCategoryId"
                                class="px-3 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"
                                @change="applyFilters"
                            >
                                <option value="">Semua Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.nama }}
                                </option>
                            </select>

                            <select
                                v-model="filterBulan"
                                class="px-3 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"
                                @change="applyFilters"
                            >
                                <option value="">Semua Bulan</option>
                                <option v-for="month in months" :key="month.value" :value="month.value">
                                    {{ month.label }}
                                </option>
                            </select>

                            <button
                                @click="applyFilters"
                                class="px-4 py-2.5 bg-violet-500 text-white rounded-xl hover:bg-violet-600 transition-colors font-medium text-sm"
                            >
                                Cari
                            </button>

                            <button
                                v-if="hasActiveFilters || searchQuery"
                                @click="clearFilters"
                                class="p-2.5 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-colors"
                                title="Reset Filter"
                            >
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Filters (Collapsible) -->
                    <div v-if="showFilters" class="sm:hidden mt-3 pt-3 border-t border-slate-200 dark:border-zinc-700 space-y-3">
                        <select
                            v-model="filterStatus"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500"
                        >
                            <option value="">Semua Status</option>
                            <option value="belum_bayar">Belum Bayar</option>
                            <option value="sebagian">Sebagian</option>
                            <option value="lunas">Lunas</option>
                        </select>

                        <select
                            v-model="filterCategoryId"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500"
                        >
                            <option value="">Semua Kategori</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.nama }}
                            </option>
                        </select>

                        <div class="grid grid-cols-2 gap-3">
                            <select
                                v-model="filterBulan"
                                class="px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500"
                            >
                                <option value="">Bulan</option>
                                <option v-for="month in months" :key="month.value" :value="month.value">
                                    {{ month.label }}
                                </option>
                            </select>

                            <select
                                v-model="filterTahun"
                                class="px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500"
                            >
                                <option value="">Tahun</option>
                                <option v-for="year in years" :key="year.value" :value="year.value">
                                    {{ year.label }}
                                </option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button
                                @click="applyFilters"
                                class="flex-1 px-4 py-2.5 bg-violet-500 text-white rounded-xl hover:bg-violet-600 transition-colors font-medium"
                            >
                                Terapkan
                            </button>
                            <button
                                v-if="hasActiveFilters"
                                @click="clearFilters"
                                class="px-4 py-2.5 text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-zinc-800 rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Bills List -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50">
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">No. Tagihan</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Siswa</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori</th>
                                    <th class="text-center px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Periode</th>
                                    <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nominal</th>
                                    <th class="text-center px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                    <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-zinc-800">
                                <tr
                                    v-for="bill in bills.data"
                                    :key="bill.id"
                                    class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <code class="px-2 py-1 bg-slate-100 dark:bg-zinc-800 rounded text-sm font-mono text-slate-700 dark:text-slate-300">
                                            {{ bill.nomor_tagihan }}
                                        </code>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ bill.student.nama_lengkap }}</p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                                {{ bill.student.nis }} • {{ bill.student.kelas?.nama_lengkap || '-' }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-slate-700 dark:text-slate-300">{{ bill.payment_category.nama }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-slate-700 dark:text-slate-300">
                                            {{ getMonthName(bill.bulan) }} {{ bill.tahun }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency(bill.nominal) }}</p>
                                        <p v-if="bill.nominal_terbayar > 0" class="text-sm text-emerald-600 dark:text-emerald-400">
                                            Terbayar: {{ formatCurrency(bill.nominal_terbayar) }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium', getStatusBadgeClass(bill.status)]"
                                        >
                                            <component :is="getStatusIcon(bill.status)" class="w-3 h-3" />
                                            {{ getStatusLabel(bill.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="relative inline-block" @click.stop>
                                            <button
                                                @click="toggleDropdown(bill.id)"
                                                class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                            >
                                                <MoreVertical class="w-5 h-5" />
                                            </button>
                                            <div
                                                v-if="activeDropdown === bill.id"
                                                class="absolute right-0 mt-1 w-48 bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-slate-200 dark:border-zinc-700 py-1 z-50"
                                            >
                                                <button
                                                    v-if="bill.status !== 'lunas' && bill.status !== 'dibatalkan'"
                                                    @click="handleCancel(bill)"
                                                    class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                >
                                                    <XCircle class="w-4 h-4" />
                                                    Batalkan
                                                </button>
                                                <span
                                                    v-else
                                                    class="block px-4 py-2.5 text-sm text-slate-400 dark:text-slate-500"
                                                >
                                                    Tidak ada aksi
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden divide-y divide-slate-200 dark:divide-zinc-800">
                        <div
                            v-for="bill in bills.data"
                            :key="bill.id"
                            class="p-4 space-y-3"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ bill.student.nama_lengkap }}</p>
                                        <span
                                            :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium shrink-0', getStatusBadgeClass(bill.status)]"
                                        >
                                            <component :is="getStatusIcon(bill.status)" class="w-3 h-3" />
                                            {{ getStatusLabel(bill.status) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ bill.student.nis }} • {{ bill.student.kelas?.nama_lengkap || '-' }}
                                    </p>
                                    <code class="inline-block mt-1 px-1.5 py-0.5 bg-slate-100 dark:bg-zinc-800 rounded text-xs font-mono text-slate-600 dark:text-slate-400">
                                        {{ bill.nomor_tagihan }}
                                    </code>
                                </div>
                                <div class="relative" @click.stop>
                                    <button
                                        v-if="bill.status !== 'lunas' && bill.status !== 'dibatalkan'"
                                        @click="toggleDropdown(bill.id)"
                                        class="p-2 -m-2 text-slate-500"
                                    >
                                        <MoreVertical class="w-5 h-5" />
                                    </button>
                                    <div
                                        v-if="activeDropdown === bill.id"
                                        class="absolute right-0 mt-1 w-40 bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-slate-200 dark:border-zinc-700 py-1 z-50"
                                    >
                                        <button
                                            @click="handleCancel(bill)"
                                            class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                                        >
                                            <XCircle class="w-4 h-4" />
                                            Batalkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-zinc-800">
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ bill.payment_category.nama }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ getMonthName(bill.bulan) }} {{ bill.tahun }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency(bill.nominal) }}</p>
                                    <p v-if="bill.nominal_terbayar > 0" class="text-xs text-emerald-600 dark:text-emerald-400">
                                        Terbayar: {{ formatCurrency(bill.nominal_terbayar) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="bills.data.length === 0" class="p-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                            <Receipt class="w-8 h-8 text-slate-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">Belum Ada Tagihan</h3>
                        <p class="text-slate-500 dark:text-slate-400 mb-4">Mulai dengan generate tagihan untuk siswa.</p>
                        <Link
                            :href="showGenerate()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-violet-500 text-white rounded-xl hover:bg-violet-600 transition-colors font-medium"
                        >
                            <Plus class="w-4 h-4" />
                            Generate Tagihan
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="bills.data.length > 0 && bills.links" class="px-6 py-4 border-t border-slate-200 dark:border-zinc-800 flex items-center justify-between">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Menampilkan {{ bills.data.length }} tagihan
                        </p>
                        <div class="flex gap-2">
                            <template v-for="link in bills.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-1.5 text-sm rounded-lg transition-colors',
                                        link.active
                                            ? 'bg-violet-500 text-white'
                                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800'
                                    ]"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="px-3 py-1.5 text-sm text-slate-400 dark:text-slate-600"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
