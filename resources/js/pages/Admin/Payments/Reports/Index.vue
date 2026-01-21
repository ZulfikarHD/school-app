<script setup lang="ts">
/**
 * Admin Payment Reports Page
 *
 * Menampilkan laporan keuangan dengan summary cards, grafik trend,
 * breakdown per kategori, dan akses ke siswa menunggak
 *
 * Menggunakan Chart.js untuk visualisasi data yang lebih baik
 */
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    TrendingUp, Wallet, AlertTriangle, Download, Users,
    Banknote, CreditCard, QrCode, ChevronRight, Filter,
    Calendar, PieChart, BarChart3, ArrowLeft
} from 'lucide-vue-next';
import { Bar, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement,
} from 'chart.js';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import { index as reportsIndex, delinquents as delinquentsRoute, exportMethod } from '@/routes/admin/payments/reports';

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

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

// Chart.js configurations
const chartColors = {
    violet: 'rgb(139, 92, 246)',
    blue: 'rgb(59, 130, 246)',
    emerald: 'rgb(16, 185, 129)',
    amber: 'rgb(245, 158, 11)',
    rose: 'rgb(244, 63, 94)',
    cyan: 'rgb(6, 182, 212)',
};

// Bar Chart data untuk trend pendapatan
const trendChartData = computed(() => ({
    labels: props.trend.map(t => t.month),
    datasets: [
        {
            label: 'Pendapatan',
            data: props.trend.map(t => t.income),
            backgroundColor: chartColors.violet,
            borderRadius: 8,
            barThickness: 24,
        },
    ],
}));

const trendChartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: (context: any) => {
                    const value = context.raw as number;
                    return `Rp ${value.toLocaleString('id-ID')}`;
                },
            },
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            ticks: {
                color: '#64748b',
            },
        },
        y: {
            grid: {
                color: 'rgba(100, 116, 139, 0.1)',
            },
            ticks: {
                color: '#64748b',
                callback: (value: number) => {
                    if (value >= 1000000) {
                        return `${(value / 1000000).toFixed(0)}jt`;
                    }
                    if (value >= 1000) {
                        return `${(value / 1000).toFixed(0)}rb`;
                    }
                    return value;
                },
            },
        },
    },
}));

// Doughnut Chart data untuk kategori
const categoryChartColors = [
    chartColors.violet,
    chartColors.blue,
    chartColors.emerald,
    chartColors.amber,
    chartColors.rose,
    chartColors.cyan,
];

const categoryChartData = computed(() => ({
    labels: props.categoryBreakdown.map(c => c.category),
    datasets: [
        {
            data: props.categoryBreakdown.map(c => c.amount),
            backgroundColor: categoryChartColors.slice(0, props.categoryBreakdown.length),
            borderWidth: 0,
            hoverOffset: 4,
        },
    ],
}));

const categoryChartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: (context: any) => {
                    const value = context.raw as number;
                    const total = props.categoryBreakdown.reduce((sum, c) => sum + c.amount, 0);
                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                    return `Rp ${value.toLocaleString('id-ID')} (${percentage}%)`;
                },
            },
        },
    },
    cutout: '60%',
}));

// Chart key untuk force re-render saat data berubah
const chartKey = computed(() => `${props.filters.month}-${props.filters.year}-${props.filters.category_id || 'all'}`);
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
                        :href="delinquentsRoute().url"
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
                <!-- Monthly Trend Chart - Chart.js Bar -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
                    class="h-full"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800 h-full flex flex-col">
                        <div class="flex items-center gap-2 mb-4">
                            <BarChart3 class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                Trend Pendapatan
                            </h2>
                        </div>
                        <div class="flex-1 min-h-[200px]">
                            <Bar
                                v-if="trend.length > 0"
                                :key="`bar-${chartKey}`"
                                :data="trendChartData"
                                :options="trendChartOptions"
                            />
                            <div v-else class="h-full flex items-center justify-center text-slate-500 dark:text-slate-400">
                                Belum ada data trend
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Category Breakdown - Chart.js Doughnut + Legend -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
                    class="h-full"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800 h-full flex flex-col">
                        <div class="flex items-center gap-2 mb-4">
                            <PieChart class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                Berdasarkan Kategori
                            </h2>
                        </div>
                        <div v-if="categoryBreakdown.length > 0" class="flex-1 flex flex-col sm:flex-row items-center justify-center gap-6">
                            <!-- Doughnut Chart - Bigger size -->
                            <div class="w-40 h-40 shrink-0">
                                <Doughnut
                                    :key="`doughnut-${chartKey}`"
                                    :data="categoryChartData"
                                    :options="categoryChartOptions"
                                />
                            </div>
                            <!-- Legend -->
                            <div class="flex-1 space-y-3 w-full max-w-xs">
                                <div
                                    v-for="(cat, index) in categoryBreakdown"
                                    :key="cat.category"
                                    class="flex items-center gap-3"
                                >
                                    <div
                                        class="w-3 h-3 rounded-full shrink-0"
                                        :style="{ backgroundColor: categoryChartColors[index % categoryChartColors.length] }"
                                    ></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-slate-700 dark:text-slate-300 truncate">
                                            {{ cat.category }}
                                        </p>
                                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                            {{ cat.formatted }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex-1 flex items-center justify-center text-slate-500 dark:text-slate-400">
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
                            :href="delinquentsRoute().url"
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
