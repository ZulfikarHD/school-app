<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    Bell,
    RefreshCw,
    Users,
    GraduationCap,
    Building,
    ChevronRight,
    LayoutDashboard,
    Clock,
    CheckCircle,
    Wallet,
    TrendingUp,
    AlertTriangle,
    PieChart,
} from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import AttendanceSummaryCard from '@/components/dashboard/AttendanceSummaryCard.vue';
import TeacherPresenceWidget from '@/components/dashboard/TeacherPresenceWidget.vue';
import { useHaptics } from '@/composables/useHaptics';
import { reports as financialReports, delinquents as financialDelinquents } from '@/routes/principal/financial';
import axios from 'axios';

/**
 * Dashboard untuk Kepala Sekolah dengan overview sistem
 * dan akses ke laporan serta monitoring
 * dengan iOS-like staggered animations, haptic feedback, dan real-time polling
 *
 * UX Enhancement:
 * - Removed duplicate header (using AppLayout greeting)
 * - Added ARIA labels untuk accessibility
 * - Added focus states untuk keyboard navigation
 * - Consistent icon styling dan backgrounds
 * - Real-time polling dengan visual feedback
 * - Financial summary widget untuk monitoring keuangan
 */

interface FinancialSummary {
    monthly_income: number;
    formatted_monthly_income: string;
    transaction_count: number;
    total_piutang: number;
    formatted_piutang: string;
    collectibility: number;
    overdue_students: number;
}

interface Props {
    stats: {
        total_students: number;
        total_teachers: number;
        total_classes: number;
        attendance_rate: number;
    };
    todayAttendance: {
        total_students: number;
        present: number;
        absent: number;
        late: number;
        percentage: number;
    };
    classesNotRecorded: Array<{
        id: number;
        nama_lengkap: string;
    }>;
    teacherPresence: {
        total_teachers: number;
        clocked_in: number;
        late_teachers: Array<{
            id: number;
            name: string;
            late_minutes: number;
        }>;
        absent_teachers: Array<{
            id: number;
            name: string;
        }>;
    };
    pendingTeacherLeaves: number;
    financialSummary: FinancialSummary;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const handleCardClick = () => haptics.light();

const viewTeacherLeaves = () => {
    haptics.light();
    router.visit('/principal/teacher-leaves');
};

// Real-time polling state
const isRefreshing = ref(false);
const lastUpdated = ref(new Date());
const realtimeData = ref({
    todayAttendance: props.todayAttendance,
    classesNotRecorded: props.classesNotRecorded,
    teacherPresence: props.teacherPresence,
    pendingTeacherLeaves: props.pendingTeacherLeaves,
});

/**
 * Warna kolektibilitas berdasarkan persentase
 */
const collectibilityColor = computed(() => {
    const value = props.financialSummary.collectibility;
    if (value >= 80) return 'text-emerald-600 dark:text-emerald-400';
    if (value >= 60) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
});

const collectibilityBgColor = computed(() => {
    const value = props.financialSummary.collectibility;
    if (value >= 80) return 'bg-emerald-50 dark:bg-emerald-900/20';
    if (value >= 60) return 'bg-amber-50 dark:bg-amber-900/20';
    return 'bg-red-50 dark:bg-red-900/20';
});

let pollingInterval: number | null = null;

// Fetch latest attendance metrics
const fetchAttendanceMetrics = async () => {
    if (isRefreshing.value) return;

    isRefreshing.value = true;

    try {
        const response = await axios.get('/principal/dashboard/attendance-metrics');

        if (response.data) {
            realtimeData.value = {
                todayAttendance: response.data.todayAttendance,
                classesNotRecorded: response.data.classesNotRecorded,
                teacherPresence: response.data.teacherPresence,
                pendingTeacherLeaves: response.data.pendingTeacherLeaves,
            };
            lastUpdated.value = new Date();
        }
    } catch (error) {
        console.error('Failed to fetch attendance metrics:', error);
    } finally {
        isRefreshing.value = false;
    }
};

// Manual refresh
const manualRefresh = () => {
    haptics.light();
    fetchAttendanceMetrics();
};

// Start polling on mount
onMounted(() => {
    // Poll every 60 seconds (1 minute)
    pollingInterval = window.setInterval(() => {
        fetchAttendanceMetrics();
    }, 60000);
});

// Cleanup on unmount
onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});

// Format last updated time
const formatLastUpdated = () => {
    const now = new Date();
    const diff = Math.floor((now.getTime() - lastUpdated.value.getTime()) / 1000);

    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return `${Math.floor(diff / 60)} menit yang lalu`;
    return lastUpdated.value.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

/**
 * Quick action cards configuration
 */
const quickActions = [
    {
        key: 'attendance-dashboard',
        title: 'Dashboard Kehadiran',
        description: 'Monitor real-time',
        icon: LayoutDashboard,
        bgColor: 'bg-emerald-100 dark:bg-emerald-500/20',
        iconColor: 'text-emerald-600 dark:text-emerald-400',
        hoverBorder: 'hover:border-emerald-200 dark:hover:border-emerald-700',
        route: '/principal/attendance/dashboard',
    },
    {
        key: 'student-attendance',
        title: 'Absensi Siswa',
        description: 'Rekap kehadiran siswa',
        icon: Users,
        bgColor: 'bg-blue-100 dark:bg-blue-500/20',
        iconColor: 'text-blue-600 dark:text-blue-400',
        hoverBorder: 'hover:border-blue-200 dark:hover:border-blue-700',
        route: '/principal/attendance/students',
    },
    {
        key: 'teacher-attendance',
        title: 'Presensi Guru',
        description: 'Rekap clock in/out',
        icon: Clock,
        bgColor: 'bg-purple-100 dark:bg-purple-500/20',
        iconColor: 'text-purple-600 dark:text-purple-400',
        hoverBorder: 'hover:border-purple-200 dark:hover:border-purple-700',
        route: '/principal/attendance/teachers',
    },
    {
        key: 'teacher-leaves',
        title: 'Approval Izin',
        description: 'Setujui izin guru',
        icon: CheckCircle,
        bgColor: 'bg-green-100 dark:bg-green-500/20',
        iconColor: 'text-green-600 dark:text-green-400',
        hoverBorder: 'hover:border-green-200 dark:hover:border-green-700',
        route: '/principal/teacher-leaves',
    },
];
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Kepala Sekolah" />

        <div class="space-y-6">
            <!-- Refresh Control Bar -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="flex items-center justify-end gap-3">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        Diperbarui: {{ formatLastUpdated() }}
                    </span>
                    <button
                        @click="manualRefresh"
                        :disabled="isRefreshing"
                        class="p-2 rounded-xl bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors disabled:opacity-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                        aria-label="Perbarui data"
                        title="Refresh data"
                    >
                        <RefreshCw
                            :size="18"
                            :class="['text-slate-600 dark:text-slate-400', isRefreshing && 'animate-spin']"
                        />
                    </button>
                </div>
            </Motion>

            <!-- Quick Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Siswa -->
                <Motion
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <button
                        @click="router.visit('/principal/students')"
                        class="w-full text-left overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800 hover:border-blue-200 dark:hover:border-blue-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    >
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Siswa</p>
                                    <p class="mt-1.5 text-3xl font-bold text-slate-900 dark:text-white tabular-nums">
                                        {{ stats.total_students }}
                                    </p>
                                </div>
                                <div class="p-3 bg-blue-100 dark:bg-blue-500/20 rounded-xl">
                                    <GraduationCap :size="24" class="text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-center text-xs text-slate-500 dark:text-slate-400">
                                <span>Lihat data siswa</span>
                                <ChevronRight :size="14" class="ml-1" />
                            </div>
                        </div>
                    </button>
                </Motion>

                <!-- Total Guru -->
                <Motion
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <div
                        class="overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800"
                    >
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Guru</p>
                                    <p class="mt-1.5 text-3xl font-bold text-slate-900 dark:text-white tabular-nums">
                                        {{ stats.total_teachers }}
                                    </p>
                                </div>
                                <div class="p-3 bg-green-100 dark:bg-green-500/20 rounded-xl">
                                    <Users :size="24" class="text-green-600 dark:text-green-400" />
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Total Kelas -->
                <Motion
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <div
                        class="overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800"
                    >
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Kelas</p>
                                    <p class="mt-1.5 text-3xl font-bold text-slate-900 dark:text-white tabular-nums">
                                        {{ stats.total_classes }}
                                    </p>
                                </div>
                                <div class="p-3 bg-purple-100 dark:bg-purple-500/20 rounded-xl">
                                    <Building :size="24" class="text-purple-600 dark:text-purple-400" />
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Izin Pending -->
                <Motion
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                    class="relative"
                >
                    <!-- Pending Badge - Outside of overflow-hidden container -->
                    <div
                        v-if="realtimeData.pendingTeacherLeaves > 0"
                        class="absolute -top-2 -right-2 z-10 flex items-center justify-center min-w-[24px] h-6 px-1.5 bg-red-500 text-white text-xs font-bold rounded-full shadow-lg ring-2 ring-white dark:ring-zinc-900"
                    >
                        {{ realtimeData.pendingTeacherLeaves > 99 ? '99+' : realtimeData.pendingTeacherLeaves }}
                    </div>

                    <button
                        @click="viewTeacherLeaves"
                        class="w-full text-left rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800 hover:border-amber-200 dark:hover:border-amber-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    >
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Izin Pending</p>
                                    <p
                                        class="mt-1.5 text-3xl font-bold tabular-nums"
                                        :class="realtimeData.pendingTeacherLeaves > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white'"
                                    >
                                        {{ realtimeData.pendingTeacherLeaves }}
                                    </p>
                                </div>
                                <div
                                    :class="[
                                        'p-3 rounded-xl',
                                        realtimeData.pendingTeacherLeaves > 0 ? 'bg-amber-100 dark:bg-amber-500/20' : 'bg-slate-100 dark:bg-slate-500/20'
                                    ]"
                                >
                                    <Bell
                                        :size="24"
                                        :class="realtimeData.pendingTeacherLeaves > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-600 dark:text-slate-400'"
                                    />
                                </div>
                            </div>
                            <div class="mt-3 flex items-center text-xs">
                                <span v-if="realtimeData.pendingTeacherLeaves > 0" class="text-amber-600 dark:text-amber-400 font-medium">
                                    Perlu persetujuan
                                </span>
                                <span v-else class="text-slate-500 dark:text-slate-400">Tidak ada pending</span>
                                <ChevronRight :size="14" class="ml-1 text-slate-400" />
                            </div>
                        </div>
                    </button>
                </Motion>
            </div>

            <!-- Real-time Attendance Widgets -->
            <div class="grid gap-4 lg:grid-cols-2 items-stretch">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                    class="h-full"
                >
                    <AttendanceSummaryCard
                        :today-attendance="realtimeData.todayAttendance"
                        :classes-not-recorded="realtimeData.classesNotRecorded"
                        details-url="/principal/attendance/students"
                        class="h-full"
                    />
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.3 }"
                    class="h-full"
                >
                    <TeacherPresenceWidget :teacher-presence="realtimeData.teacherPresence" class="h-full" />
                </Motion>
            </div>

            <!-- Financial Summary Widget -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.35 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <div class="p-5 sm:p-6">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl">
                                    <TrendingUp :size="22" class="text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                        Ringkasan Keuangan
                                    </h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        Bulan ini
                                    </p>
                                </div>
                            </div>
                            <Link
                                :href="financialReports().url"
                                @click="haptics.light()"
                                class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1"
                            >
                                Lihat Laporan
                                <ChevronRight :size="14" />
                            </Link>
                        </div>

                        <!-- Summary Grid -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Pendapatan Bulan Ini -->
                            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-slate-600 dark:text-slate-400">Pendapatan</p>
                                    <Wallet :size="16" class="text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                                    {{ financialSummary.formatted_monthly_income }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    {{ financialSummary.transaction_count }} transaksi
                                </p>
                            </div>

                            <!-- Total Piutang -->
                            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20">
                                <p class="text-sm text-slate-600 dark:text-slate-400">Total Piutang</p>
                                <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">
                                    {{ financialSummary.formatted_piutang }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Belum terbayar
                                </p>
                            </div>

                            <!-- Kolektibilitas -->
                            <div class="p-4 rounded-xl" :class="collectibilityBgColor">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-slate-600 dark:text-slate-400">Kolektibilitas</p>
                                    <PieChart :size="16" :class="collectibilityColor" />
                                </div>
                                <p class="text-xl font-bold mt-1" :class="collectibilityColor">
                                    {{ financialSummary.collectibility }}%
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Tingkat pembayaran
                                </p>
                            </div>

                            <!-- Siswa Menunggak -->
                            <Link
                                :href="financialDelinquents().url"
                                @click="haptics.light()"
                                class="p-4 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors"
                                :class="financialSummary.overdue_students > 0 ? 'bg-red-50 dark:bg-red-900/20' : 'bg-slate-50 dark:bg-zinc-800'"
                            >
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-slate-600 dark:text-slate-400">Menunggak</p>
                                    <AlertTriangle v-if="financialSummary.overdue_students > 0" :size="16" class="text-red-500" />
                                </div>
                                <p class="text-xl font-bold mt-1" :class="financialSummary.overdue_students > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white'">
                                    {{ financialSummary.overdue_students }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                                    Siswa
                                    <ChevronRight :size="12" />
                                </p>
                            </Link>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Quick Actions -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.4 }"
            >
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Aksi Cepat</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Motion
                        v-for="(action, index) in quickActions"
                        :key="action.key"
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.45 + (index * 0.05) }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <button
                            @click="router.visit(action.route); handleCardClick()"
                            :class="[
                                'w-full text-left p-5 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2',
                                action.hoverBorder
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <div :class="['p-3 rounded-xl shrink-0', action.bgColor]">
                                    <component :is="action.icon" :size="24" :class="action.iconColor" />
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-slate-900 dark:text-white truncate">
                                        {{ action.title }}
                                    </h3>
                                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400 truncate">
                                        {{ action.description }}
                                    </p>
                                </div>
                            </div>
                        </button>
                    </Motion>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
