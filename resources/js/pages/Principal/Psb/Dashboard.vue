<script setup lang="ts">
/**
 * Principal PSB Dashboard Page
 *
 * Dashboard read-only untuk kepala sekolah melihat statistik PSB,
 * yaitu: summary cards, charts distribusi, dan trend pendaftaran
 */
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    UserPlus,
    Users,
    Clock,
    CheckCircle,
    XCircle,
    RefreshCw,
    Trophy,
    TrendingUp,
    PieChart,
    BarChart3
} from 'lucide-vue-next';
import { Bar, Doughnut, Pie } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    PointElement,
    LineElement,
} from 'chart.js';
import AppLayout from '@/components/layouts/AppLayout.vue';
// TODO: Gunakan route dari Wayfinder setelah backend dibuat

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement, PointElement, LineElement);

interface Summary {
    total: number;
    pending: number;
    approved: number;
    rejected: number;
    re_registration: number;
    completed: number;
}

interface DailyRegistration {
    date: string;
    count: number;
}

interface GenderDistribution {
    gender: string;
    count: number;
}

interface StatusDistribution {
    status: string;
    count: number;
}

interface Props {
    summary: Summary;
    dailyRegistrations: DailyRegistration[];
    genderDistribution: GenderDistribution[];
    statusDistribution: StatusDistribution[];
}

const props = defineProps<Props>();
const haptics = useHaptics();

/**
 * Stat cards configuration
 */
const statCards = computed(() => [
    {
        key: 'total',
        label: 'Total Pendaftar',
        value: props.summary.total,
        icon: Users,
        color: 'emerald',
        gradient: 'from-emerald-500 to-teal-600',
    },
    {
        key: 'pending',
        label: 'Menunggu Verifikasi',
        value: props.summary.pending,
        icon: Clock,
        color: 'amber',
        gradient: 'from-amber-500 to-orange-600',
    },
    {
        key: 'approved',
        label: 'Diterima',
        value: props.summary.approved,
        icon: CheckCircle,
        color: 'blue',
        gradient: 'from-blue-500 to-indigo-600',
    },
    {
        key: 'rejected',
        label: 'Ditolak',
        value: props.summary.rejected,
        icon: XCircle,
        color: 'red',
        gradient: 'from-red-500 to-rose-600',
    },
    {
        key: 're_registration',
        label: 'Daftar Ulang',
        value: props.summary.re_registration,
        icon: RefreshCw,
        color: 'purple',
        gradient: 'from-purple-500 to-violet-600',
    },
    {
        key: 'completed',
        label: 'Selesai',
        value: props.summary.completed,
        icon: Trophy,
        color: 'teal',
        gradient: 'from-teal-500 to-cyan-600',
    },
]);

/**
 * Chart colors
 */
const chartColors = {
    emerald: 'rgb(16, 185, 129)',
    blue: 'rgb(59, 130, 246)',
    amber: 'rgb(245, 158, 11)',
    red: 'rgb(239, 68, 68)',
    purple: 'rgb(139, 92, 246)',
    teal: 'rgb(20, 184, 166)',
    pink: 'rgb(236, 72, 153)',
    cyan: 'rgb(6, 182, 212)',
};

/**
 * Daily Registrations Bar Chart
 */
const dailyChartData = computed(() => ({
    labels: props.dailyRegistrations.map(d => {
        const date = new Date(d.date);
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    }),
    datasets: [
        {
            label: 'Pendaftar',
            data: props.dailyRegistrations.map(d => d.count),
            backgroundColor: chartColors.emerald,
            borderRadius: 8,
            barThickness: 20,
        },
    ],
}));

const dailyChartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: (context: any) => `${context.raw} pendaftar`,
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
                font: {
                    size: 11,
                },
            },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(100, 116, 139, 0.1)',
            },
            ticks: {
                color: '#64748b',
                precision: 0,
            },
        },
    },
}));

/**
 * Status Distribution Doughnut Chart
 */
const statusChartColors = [
    chartColors.amber,     // pending
    chartColors.blue,      // approved
    chartColors.red,       // rejected
    chartColors.purple,    // re_registration
    chartColors.teal,      // completed
];

const statusLabels: Record<string, string> = {
    pending: 'Menunggu',
    document_review: 'Review Dokumen',
    approved: 'Diterima',
    rejected: 'Ditolak',
    waiting_list: 'Waiting List',
    re_registration: 'Daftar Ulang',
    completed: 'Selesai',
};

const statusChartData = computed(() => ({
    labels: props.statusDistribution.map(s => statusLabels[s.status] || s.status),
    datasets: [
        {
            data: props.statusDistribution.map(s => s.count),
            backgroundColor: statusChartColors.slice(0, props.statusDistribution.length),
            borderWidth: 0,
            hoverOffset: 4,
        },
    ],
}));

const statusChartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: (context: any) => {
                    const total = props.statusDistribution.reduce((sum, s) => sum + s.count, 0);
                    const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                    return `${context.raw} (${percentage}%)`;
                },
            },
        },
    },
    cutout: '60%',
}));

/**
 * Gender Distribution Pie Chart
 */
const genderChartColors = [chartColors.blue, chartColors.pink];

const genderLabels: Record<string, string> = {
    L: 'Laki-laki',
    P: 'Perempuan',
};

const genderChartData = computed(() => ({
    labels: props.genderDistribution.map(g => genderLabels[g.gender] || g.gender),
    datasets: [
        {
            data: props.genderDistribution.map(g => g.count),
            backgroundColor: genderChartColors.slice(0, props.genderDistribution.length),
            borderWidth: 0,
            hoverOffset: 4,
        },
    ],
}));

const genderChartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: (context: any) => {
                    const total = props.genderDistribution.reduce((sum, g) => sum + g.count, 0);
                    const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                    return `${context.raw} (${percentage}%)`;
                },
            },
        },
    },
}));

/**
 * Chart key untuk force re-render
 */
const chartKey = computed(() => `${props.summary.total}-${props.dailyRegistrations.length}`);
</script>

<template>
    <AppLayout>
        <Head title="Dashboard PSB" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="w-12 h-12 rounded-xl bg-linear-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/25 shrink-0">
                            <UserPlus :size="24" class="text-white" />
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                Dashboard PSB
                            </h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                Statistik Penerimaan Siswa Baru
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                <Motion
                    v-for="(card, index) in statCards"
                    :key="card.key"
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 + (index * 0.05) }"
                >
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200 dark:border-zinc-800 transition-all hover:shadow-md"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 truncate">
                                    {{ card.label }}
                                </p>
                                <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-slate-100 mt-1 tabular-nums">
                                    {{ card.value }}
                                </p>
                            </div>
                            <div
                                class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center shrink-0"
                                :class="`bg-linear-to-br ${card.gradient}`"
                            >
                                <component :is="card.icon" :size="20" class="text-white sm:w-6 sm:h-6" />
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>

            <!-- Charts Grid -->
            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Daily Registrations Chart -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, delay: 0.3 }"
                    class="lg:col-span-2"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-2 mb-6">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <TrendingUp :size="18" class="text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                Trend Pendaftaran Harian
                            </h2>
                        </div>

                        <div class="h-64 sm:h-80">
                            <Bar
                                v-if="dailyRegistrations.length > 0"
                                :key="`daily-${chartKey}`"
                                :data="dailyChartData"
                                :options="dailyChartOptions"
                            />
                            <div
                                v-else
                                class="h-full flex items-center justify-center text-slate-500 dark:text-slate-400"
                            >
                                <div class="text-center">
                                    <BarChart3 :size="48" class="mx-auto mb-2 text-slate-300 dark:text-zinc-600" />
                                    <p>Belum ada data pendaftaran</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Status Distribution Chart -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, delay: 0.4 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800 h-full">
                        <div class="flex items-center gap-2 mb-6">
                            <div class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                                <PieChart :size="18" class="text-violet-600 dark:text-violet-400" />
                            </div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                Distribusi Status
                            </h2>
                        </div>

                        <div v-if="statusDistribution.length > 0" class="flex flex-col items-center gap-6">
                            <!-- Doughnut Chart -->
                            <div class="w-48 h-48">
                                <Doughnut
                                    :key="`status-${chartKey}`"
                                    :data="statusChartData"
                                    :options="statusChartOptions"
                                />
                            </div>

                            <!-- Legend -->
                            <div class="w-full grid grid-cols-2 gap-2">
                                <div
                                    v-for="(item, index) in statusDistribution"
                                    :key="item.status"
                                    class="flex items-center gap-2 text-sm"
                                >
                                    <div
                                        class="w-3 h-3 rounded-full shrink-0"
                                        :style="{ backgroundColor: statusChartColors[index % statusChartColors.length] }"
                                    ></div>
                                    <span class="text-slate-600 dark:text-slate-400 truncate">
                                        {{ statusLabels[item.status] || item.status }}
                                    </span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100 ml-auto">
                                        {{ item.count }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="h-64 flex items-center justify-center text-slate-500 dark:text-slate-400">
                            <div class="text-center">
                                <PieChart :size="48" class="mx-auto mb-2 text-slate-300 dark:text-zinc-600" />
                                <p>Belum ada data status</p>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Gender Distribution Chart -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, delay: 0.5 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800 h-full">
                        <div class="flex items-center gap-2 mb-6">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <Users :size="18" class="text-blue-600 dark:text-blue-400" />
                            </div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                Distribusi Gender
                            </h2>
                        </div>

                        <div v-if="genderDistribution.length > 0" class="flex flex-col items-center gap-6">
                            <!-- Pie Chart -->
                            <div class="w-48 h-48">
                                <Pie
                                    :key="`gender-${chartKey}`"
                                    :data="genderChartData"
                                    :options="genderChartOptions"
                                />
                            </div>

                            <!-- Legend -->
                            <div class="w-full flex justify-center gap-6">
                                <div
                                    v-for="(item, index) in genderDistribution"
                                    :key="item.gender"
                                    class="flex items-center gap-2 text-sm"
                                >
                                    <div
                                        class="w-3 h-3 rounded-full shrink-0"
                                        :style="{ backgroundColor: genderChartColors[index % genderChartColors.length] }"
                                    ></div>
                                    <span class="text-slate-600 dark:text-slate-400">
                                        {{ genderLabels[item.gender] || item.gender }}
                                    </span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">
                                        {{ item.count }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="h-64 flex items-center justify-center text-slate-500 dark:text-slate-400">
                            <div class="text-center">
                                <Users :size="48" class="mx-auto mb-2 text-slate-300 dark:text-zinc-600" />
                                <p>Belum ada data gender</p>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>
