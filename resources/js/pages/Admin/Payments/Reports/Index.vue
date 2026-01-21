<script setup lang="ts">
/**
 * Admin Payment Reports Page
 *
 * Menampilkan laporan keuangan dengan summary cards, grafik trend,
 * breakdown per kategori, dan akses ke siswa menunggak
 */
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    TrendingUp, Wallet, AlertTriangle, Download, Users,
    Banknote, CreditCard, QrCode, ChevronRight, Filter,
    Calendar, PieChart, BarChart3, ArrowLeft
} from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import { index as reportsIndex, delinquents, exportMethod } from '@/routes/admin/payments/reports';

interface MethodBreakdown {
    amount: number;
    formatted: string;
}

interface Summary {
    total_income: number;
    formatted_income: string;
    transaction_count: number;
    total_piutang: number;
    formatted_piutang: string;
    collectibility: number;
    by_method: {
        tunai: MethodBreakdown;
        transfer: MethodBreakdown;
        qris: MethodBreakdown;
    };
}

interface TrendItem {
    month: string;
    month_full: string;
    year: number;
    income: number;
    formatted: string;
}

interface CategoryBreakdown {
    category: string;
    amount: number;
    formatted: string;
    count: number;
}

interface OverdueSummary {
    total_bills: number;
    total_students: number;
    total_amount: number;
    formatted_amount: string;
}

interface Category {
    id: number;
    nama: string;
    kode: string;
}

interface MonthOption {
    value: number;
    label: string;
}

interface YearOption {
    value: number;
    label: string;
}

interface Props {
    summary: Summary;
    trend: TrendItem[];
    categoryBreakdown: CategoryBreakdown[];
    overdueSummary: OverdueSummary;
    categories: Category[];
    filters: {
        month: number;
        year: number;
        category_id?: number | null;
    };
    monthOptions: MonthOption[];
    yearOptions: YearOption[];
}

const props = defineProps<Props>();

const haptics = useHaptics();

// Filter state
const filterForm = ref({
    month: props.filters.month,
    year: props.filters.year,
    category_id: props.filters.category_id || '',
});

const showFilters = ref(false);

// Computed
const maxTrendValue = computed(() => {
    return Math.max(...props.trend.map(t => t.income), 1);
});

const totalCategoryAmount = computed(() => {
    return props.categoryBreakdown.reduce((sum, c) => sum + c.amount, 0);
});

// Methods
const applyFilters = () => {
    haptics.light();
    const params: Record<string, any> = {
        month: filterForm.value.month,
        year: filterForm.value.year,
    };
    if (filterForm.value.category_id) {
        params.category_id = filterForm.value.category_id;
    }
    router.get(reportsIndex().url, params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const toggleFilters = () => {
    haptics.light();
    showFilters.value = !showFilters.value;
};

const handleExport = () => {
    haptics.light();
    window.open(exportMethod({
        query: {
            month: filterForm.value.month,
            year: filterForm.value.year,
        }
    }).url, '_blank');
};

const getBarWidth = (amount: number): string => {
    if (totalCategoryAmount.value === 0) return '0%';
    return `${(amount / totalCategoryAmount.value) * 100}%`;
};

const getTrendBarHeight = (income: number): string => {
    if (maxTrendValue.value === 0) return '0%';
    return `${(income / maxTrendValue.value) * 100}%`;
};

const getCollectibilityColor = (value: number): string => {
    if (value >= 80) return 'text-emerald-600 dark:text-emerald-400';
    if (value >= 60) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
};

const getCategoryColor = (index: number): string => {
    const colors = [
        'bg-violet-500',
        'bg-blue-500',
        'bg-emerald-500',
        'bg-amber-500',
        'bg-rose-500',
        'bg-cyan-500',
    ];
    return colors[index % colors.length];
};
</script>

<template>
    <AppLayout>
        <Head title="Laporan Keuangan" />

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
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-violet-400 to-violet-600 flex items-center justify-center shadow-lg shadow-violet-500/25 shrink-0">
                                <TrendingUp class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Laporan Keuangan
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Ringkasan pendapatan dan piutang
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                @click="toggleFilters"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-zinc-800 rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                            >
                                <Filter class="w-4 h-4" />
                                Filter
                            </button>
                            <button
                                @click="handleExport"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-violet-500 rounded-xl hover:bg-violet-600 transition-colors shadow-lg shadow-violet-500/25"
                            >
                                <Download class="w-4 h-4" />
                                Export
                            </button>
                        </div>
                    </div>

                    <!-- Filters (collapsible) -->
                    <Motion
                        v-if="showFilters"
                        :initial="{ opacity: 0, height: 0 }"
                        :animate="{ opacity: 1, height: 'auto' }"
                        :exit="{ opacity: 0, height: 0 }"
                        :transition="{ duration: 0.2 }"
                    >
                        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-zinc-800">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Bulan
                                    </label>
                                    <select
                                        v-model="filterForm.month"
                                        @change="applyFilters"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 focus:border-transparent"
                                    >
                                        <option v-for="month in monthOptions" :key="month.value" :value="month.value">
                                            {{ month.label }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Tahun
                                    </label>
                                    <select
                                        v-model="filterForm.year"
                                        @change="applyFilters"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 focus:border-transparent"
                                    >
                                        <option v-for="year in yearOptions" :key="year.value" :value="year.value">
                                            {{ year.label }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Kategori
                                    </label>
                                    <select
                                        v-model="filterForm.category_id"
                                        @change="applyFilters"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 focus:border-transparent"
                                    >
                                        <option value="">Semua Kategori</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                            {{ cat.nama }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </Motion>
                </div>
            </Motion>

            <!-- Summary Cards -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Pendapatan -->
                    <div class="bg-linear-to-br from-violet-500 to-violet-600 rounded-2xl p-5 text-white shadow-lg shadow-violet-500/25">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-violet-100 text-sm">Total Pendapatan</p>
                                <p class="text-2xl font-bold mt-1">{{ summary.formatted_income }}</p>
                                <p class="text-violet-200 text-sm mt-1">
                                    {{ summary.transaction_count }} transaksi
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                                <Wallet class="w-5 h-5" />
                            </div>
                        </div>
                    </div>

                    <!-- Total Piutang -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-slate-500 dark:text-slate-400 text-sm">Total Piutang</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">
                                    {{ summary.formatted_piutang }}
                                </p>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                                    Belum terbayar
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <CreditCard class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Kolektibilitas -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-slate-500 dark:text-slate-400 text-sm">Kolektibilitas</p>
                                <p :class="['text-2xl font-bold mt-1', getCollectibilityColor(summary.collectibility)]">
                                    {{ summary.collectibility }}%
                                </p>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                                    Tingkat pembayaran
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <PieChart class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Siswa Menunggak -->
                    <Link
                        :href="delinquents().url"
                        @click="haptics.light()"
                        class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-zinc-800 hover:border-red-200 dark:hover:border-red-800 transition-colors"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-slate-500 dark:text-slate-400 text-sm">Jatuh Tempo</p>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
                                    {{ overdueSummary.total_students }}
                                </p>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 flex items-center gap-1">
                                    Siswa
                                    <ChevronRight class="w-3 h-3" />
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                <AlertTriangle class="w-5 h-5 text-red-600 dark:text-red-400" />
                            </div>
                        </div>
                    </Link>
                </div>
            </Motion>

            <!-- Payment Method Breakdown -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Berdasarkan Metode Pembayaran
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Tunai -->
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <Banknote class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Tunai</p>
                                <p class="font-semibold text-slate-900 dark:text-slate-100">
                                    {{ summary.by_method.tunai.formatted }}
                                </p>
                            </div>
                        </div>

                        <!-- Transfer -->
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <CreditCard class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Transfer</p>
                                <p class="font-semibold text-slate-900 dark:text-slate-100">
                                    {{ summary.by_method.transfer.formatted }}
                                </p>
                            </div>
                        </div>

                        <!-- QRIS -->
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-purple-50 dark:bg-purple-900/20">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                <QrCode class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">QRIS</p>
                                <p class="font-semibold text-slate-900 dark:text-slate-100">
                                    {{ summary.by_method.qris.formatted }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Monthly Trend Chart -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-2 mb-4">
                            <BarChart3 class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                Trend Pendapatan
                            </h2>
                        </div>
                        <div class="h-48 flex items-end justify-between gap-2">
                            <div
                                v-for="(item, index) in trend"
                                :key="index"
                                class="flex-1 flex flex-col items-center"
                            >
                                <div class="w-full flex-1 flex items-end">
                                    <div
                                        :style="{ height: getTrendBarHeight(item.income) }"
                                        class="w-full bg-violet-500 dark:bg-violet-400 rounded-t-lg min-h-[4px] transition-all duration-500"
                                        :title="item.formatted"
                                    ></div>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                                    {{ item.month }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Category Breakdown -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-2 mb-4">
                            <PieChart class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                Berdasarkan Kategori
                            </h2>
                        </div>
                        <div v-if="categoryBreakdown.length > 0" class="space-y-3">
                            <div
                                v-for="(cat, index) in categoryBreakdown"
                                :key="cat.category"
                                class="flex items-center gap-3"
                            >
                                <div :class="['w-3 h-3 rounded-full shrink-0', getCategoryColor(index)]"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">
                                            {{ cat.category }}
                                        </p>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 shrink-0 ml-2">
                                            {{ cat.formatted }}
                                        </p>
                                    </div>
                                    <div class="h-2 bg-slate-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                        <div
                                            :class="[getCategoryColor(index), 'h-full rounded-full transition-all duration-500']"
                                            :style="{ width: getBarWidth(cat.amount) }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center text-slate-500 dark:text-slate-400">
                            Belum ada data pembayaran
                        </div>
                    </div>
                </Motion>
            </div>

            <!-- Quick Actions -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.25 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Aksi Cepat
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <Link
                            :href="delinquents().url"
                            @click="haptics.light()"
                            class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 dark:border-zinc-800 hover:border-red-200 dark:hover:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors"
                        >
                            <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                <Users class="w-6 h-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-slate-900 dark:text-slate-100">
                                    Siswa Menunggak
                                </p>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ overdueSummary.total_students }} siswa • {{ overdueSummary.formatted_amount }}
                                </p>
                            </div>
                            <ChevronRight class="w-5 h-5 text-slate-400" />
                        </Link>

                        <button
                            @click="handleExport"
                            class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 dark:border-zinc-800 hover:border-violet-200 dark:hover:border-violet-800 hover:bg-violet-50 dark:hover:bg-violet-900/10 transition-colors text-left"
                        >
                            <div class="w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                                <Download class="w-6 h-6 text-violet-600 dark:text-violet-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-slate-900 dark:text-slate-100">
                                    Export Laporan
                                </p>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    Download laporan bulan ini
                                </p>
                            </div>
                            <ChevronRight class="w-5 h-5 text-slate-400" />
                        </button>
                    </div>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
