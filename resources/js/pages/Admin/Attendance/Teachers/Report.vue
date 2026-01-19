<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { FileSpreadsheet, Filter, Calendar, Clock, FileBarChart, Users, TrendingUp, AlertTriangle, X } from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';
import Badge from '@/components/ui/Badge.vue';
import type { TeacherAttendance } from '@/types/attendance';
import { report } from '@/routes/admin/attendance/teachers';
import { payroll } from '@/routes/admin/attendance/teachers/export';

/**
 * Halaman laporan presensi guru dengan work hours calculation
 * untuk admin/principal dengan payroll export
 */

interface Teacher {
    id: number;
    name: string;
    email: string;
}

interface Statistics {
    total_records: number;
    total_present: number;
    total_late: number;
    average_hours: number;
}

interface Props {
    title: string;
    attendances: TeacherAttendance[];
    statistics: Statistics;
    teachers: Teacher[];
    filters: {
        start_date?: string;
        end_date?: string;
        teacher_id?: number;
    };
}

const props = defineProps<Props>();
const haptics = useHaptics();

// Filter state
const filterForm = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    teacher_id: props.filters.teacher_id || null,
});

const isFiltering = ref(false);

/**
 * Check if filters are active
 */
const hasFilters = computed(() => {
    return filterForm.value.start_date || filterForm.value.end_date || filterForm.value.teacher_id;
});

/**
 * Apply filters ke report
 */
const applyFilters = () => {
    haptics.light();
    isFiltering.value = true;
    router.get(report({ query: filterForm.value }).url, {}, {
        preserveState: true,
        onFinish: () => {
            isFiltering.value = false;
        },
    });
};

/**
 * Reset semua filters
 */
const resetFilters = () => {
    haptics.light();
    filterForm.value = {
        start_date: '',
        end_date: '',
        teacher_id: null,
    };
    applyFilters();
};

/**
 * Export to Excel for payroll
 */
const exportToPayroll = () => {
    haptics.medium();
    window.location.href = payroll({ query: filterForm.value }).url;
};

/**
 * Format tanggal untuk display
 */
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
};

/**
 * Format waktu untuk display
 */
const formatTime = (timeString: string | null) => {
    if (!timeString) return '-';
    const time = new Date(timeString);
    return new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        timeZone: 'Asia/Jakarta',
    }).format(time);
};

/**
 * Calculate work hours
 */
const calculateWorkHours = (clockIn: string | null, clockOut: string | null) => {
    if (!clockIn || !clockOut) return '-';

    const start = new Date(clockIn);
    const end = new Date(clockOut);
    const diffMs = end.getTime() - start.getTime();
    const diffHours = diffMs / (1000 * 60 * 60);

    const hours = Math.floor(diffHours);
    const minutes = Math.round((diffHours - hours) * 60);

    return `${hours}j ${minutes}m`;
};
</script>

<template>
    <Head :title="title" />

    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header Section -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <!-- Icon Container -->
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/25 shrink-0">
                                <FileBarChart class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ title }}
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Laporan presensi guru untuk payroll processing
                                </p>
                            </div>
                        </div>

                        <!-- Export Button -->
                        <Motion :whileTap="{ scale: 0.97 }">
                            <button
                                @click="exportToPayroll"
                                class="group flex items-center gap-2 px-5 py-2.5 min-h-[44px] bg-linear-to-r from-emerald-500 to-teal-500 text-white rounded-xl text-sm font-semibold hover:from-emerald-600 hover:to-teal-600 transition-all duration-200 shadow-lg shadow-emerald-500/30
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                            >
                                <FileSpreadsheet :size="18" class="transition-transform group-hover:translate-y-0.5 duration-200" />
                                <span class="hidden sm:inline">Export untuk Payroll</span>
                                <span class="sm:hidden">Export</span>
                            </button>
                        </Motion>
                    </div>
                </div>
            </Motion>

            <!-- Statistics Cards -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <!-- Total Records -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-zinc-800 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">Total Records</p>
                                <p class="mt-1 text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">
                                    {{ statistics.total_records }}
                                </p>
                            </div>
                            <div class="p-2.5 sm:p-3 bg-slate-100 dark:bg-zinc-800 rounded-xl">
                                <Users :size="20" class="text-slate-600 dark:text-slate-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Total Present -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-5 border border-emerald-200 dark:border-emerald-800/50 shadow-sm">
                        <p class="text-xs sm:text-sm font-medium text-emerald-600 dark:text-emerald-400">Total Hadir</p>
                        <p class="mt-1 text-2xl sm:text-3xl font-bold text-emerald-700 dark:text-emerald-300">
                            {{ statistics.total_present }}
                        </p>
                    </div>

                    <!-- Total Late -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-5 border border-amber-200 dark:border-amber-800/50 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs sm:text-sm font-medium text-amber-600 dark:text-amber-400">Total Terlambat</p>
                                <p class="mt-1 text-2xl sm:text-3xl font-bold text-amber-700 dark:text-amber-300">
                                    {{ statistics.total_late }}
                                </p>
                            </div>
                            <AlertTriangle v-if="statistics.total_late > 0" :size="18" class="text-amber-500" />
                        </div>
                    </div>

                    <!-- Average Hours -->
                    <div class="bg-linear-to-br from-blue-500 to-indigo-600 rounded-2xl p-4 sm:p-5 shadow-lg shadow-blue-500/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs sm:text-sm font-medium text-blue-100">Rata-rata Jam Kerja</p>
                                <p class="mt-1 text-2xl sm:text-3xl font-bold text-white">
                                    {{ statistics.average_hours ? statistics.average_hours.toFixed(1) : '0' }}j
                                </p>
                            </div>
                            <div class="p-2.5 sm:p-3 bg-white/20 rounded-xl">
                                <TrendingUp :size="20" class="text-white" />
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Filters Panel -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 border border-slate-200 dark:border-zinc-800 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <Filter :size="18" class="text-slate-600 dark:text-slate-400" />
                            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">Filter Laporan</h2>
                        </div>
                        <button
                            v-if="hasFilters"
                            @click="resetFilters"
                            class="flex items-center gap-1 text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 active:scale-97 transition-all"
                        >
                            <X :size="14" />
                            Reset
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <!-- Start Date -->
                        <div>
                            <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400 mb-1.5">
                                Tanggal Mulai
                            </label>
                            <input
                                v-model="filterForm.start_date"
                                type="date"
                                class="w-full h-[52px] px-4 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                       rounded-xl text-slate-900 dark:text-white
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900
                                       transition-all duration-150"
                            />
                        </div>

                        <!-- End Date -->
                        <div>
                            <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400 mb-1.5">
                                Tanggal Selesai
                            </label>
                            <input
                                v-model="filterForm.end_date"
                                type="date"
                                class="w-full h-[52px] px-4 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                       rounded-xl text-slate-900 dark:text-white
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900
                                       transition-all duration-150"
                            />
                        </div>

                        <!-- Teacher Filter -->
                        <div>
                            <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400 mb-1.5">
                                Guru
                            </label>
                            <select
                                v-model="filterForm.teacher_id"
                                class="w-full h-[52px] px-4 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                       rounded-xl text-slate-900 dark:text-white
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900
                                       transition-all duration-150"
                            >
                                <option :value="null">Semua Guru</option>
                                <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                    {{ teacher.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Filter Actions -->
                    <div class="mt-4 pt-4 border-t border-slate-200 dark:border-zinc-700">
                        <Motion :whileTap="{ scale: 0.97 }">
                            <button
                                @click="applyFilters"
                                :disabled="isFiltering"
                                class="w-full sm:w-auto px-6 py-3 min-h-[48px] bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 text-white rounded-xl text-sm font-semibold active:scale-97 transition-all shadow-sm shadow-emerald-500/25
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                            >
                                {{ isFiltering ? 'Memfilter...' : 'Terapkan Filter' }}
                            </button>
                        </Motion>
                    </div>
                </div>
            </Motion>

            <!-- Data Table -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-100 dark:border-zinc-800">
                                <tr>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Tanggal
                                    </th>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Guru
                                    </th>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Clock In
                                    </th>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Clock Out
                                    </th>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Jam Kerja
                                    </th>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                <tr v-if="attendances.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <Calendar :size="48" class="mx-auto mb-4 text-slate-300 dark:text-zinc-600" />
                                        <p class="text-lg font-semibold text-slate-700 dark:text-slate-300">Tidak ada data presensi</p>
                                        <p class="text-sm mt-1 text-slate-500 dark:text-slate-400">Silakan sesuaikan filter untuk melihat data</p>
                                    </td>
                                </tr>
                                <tr v-for="attendance in attendances" :key="attendance.id" class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="whitespace-nowrap px-4 sm:px-6 py-4 text-sm text-slate-900 dark:text-white">
                                        {{ formatDate(attendance.tanggal) }}
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 text-sm">
                                        <div class="font-medium text-slate-900 dark:text-white">
                                            {{ attendance.teacher.name }}
                                        </div>
                                        <div class="text-slate-500 dark:text-slate-400">
                                            {{ attendance.teacher.email }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 sm:px-6 py-4 text-sm">
                                        <div class="flex items-center gap-2 text-slate-900 dark:text-white">
                                            <Clock :size="14" class="text-slate-400 dark:text-slate-500" />
                                            {{ formatTime(attendance.clock_in) }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 sm:px-6 py-4 text-sm">
                                        <div class="flex items-center gap-2 text-slate-900 dark:text-white">
                                            <Clock :size="14" class="text-slate-400 dark:text-slate-500" />
                                            {{ formatTime(attendance.clock_out) }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 sm:px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ calculateWorkHours(attendance.clock_in, attendance.clock_out) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 sm:px-6 py-4 text-sm">
                                        <Badge
                                            v-if="attendance.is_late"
                                            variant="warning"
                                            size="sm"
                                            dot
                                        >
                                            Terlambat
                                        </Badge>
                                        <Badge
                                            v-else
                                            variant="success"
                                            size="sm"
                                            dot
                                        >
                                            Tepat Waktu
                                        </Badge>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
