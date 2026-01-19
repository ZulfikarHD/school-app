<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { FileSpreadsheet, FileText, Filter, Calendar, Users, FileBarChart, AlertTriangle, X } from 'lucide-vue-next';
import AttendanceStatusBadge from '@/components/features/attendance/AttendanceStatusBadge.vue';
import { useHaptics } from '@/composables/useHaptics';
import type { StudentAttendance } from '@/types/attendance';
import { report, exportMethod } from '@/routes/admin/attendance/students';
import { pdf as exportPdf } from '@/routes/admin/attendance/students/export';

/**
 * Halaman laporan presensi siswa dengan advanced filters
 * untuk admin/principal dengan export functionality
 */

interface SchoolClass {
    id: number;
    tingkat: number;
    nama: string;
    nama_lengkap: string;
}

interface Statistics {
    total_records: number;
    hadir: number;
    izin: number;
    sakit: number;
    alpha: number;
}

interface Props {
    title: string;
    attendances: StudentAttendance[];
    statistics: Statistics;
    classes: SchoolClass[];
    filters: {
        start_date?: string;
        end_date?: string;
        class_id?: number;
        status?: string;
        student_id?: number;
    };
}

const props = defineProps<Props>();
const haptics = useHaptics();

// Filter state
const filterForm = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    class_id: props.filters.class_id || null,
    status: props.filters.status || '',
});

const isFiltering = ref(false);

/**
 * Check if filters are active
 */
const hasFilters = computed(() => {
    return filterForm.value.start_date || filterForm.value.end_date || filterForm.value.class_id || filterForm.value.status;
});

/**
 * Apply filters ke report
 */
const applyFilters = () => {
    haptics.light();
    isFiltering.value = true;
    router.get(report.url({ query: filterForm.value }), {}, {
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
        class_id: null,
        status: '',
    };
    applyFilters();
};

/**
 * Export to Excel
 */
const exportToExcel = () => {
    haptics.medium();
    window.location.href = exportMethod.url({ query: filterForm.value });
};

/**
 * Export to PDF
 */
const exportToPdf = () => {
    haptics.medium();
    window.location.href = exportPdf.url({ query: filterForm.value });
};

/**
 * Calculate percentage untuk statistics
 */
const statisticsWithPercentage = computed(() => {
    const total = props.statistics.total_records;
    if (total === 0) return props.statistics;

    return {
        ...props.statistics,
        hadir_percentage: ((props.statistics.hadir / total) * 100).toFixed(1),
        izin_percentage: ((props.statistics.izin / total) * 100).toFixed(1),
        sakit_percentage: ((props.statistics.sakit / total) * 100).toFixed(1),
        alpha_percentage: ((props.statistics.alpha / total) * 100).toFixed(1),
    };
});

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
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/25 shrink-0">
                                <FileBarChart class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ title }}
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Laporan presensi siswa dengan filter dan export
                                </p>
                            </div>
                        </div>

                        <!-- Export Buttons -->
                        <div class="flex items-center gap-2.5">
                            <Motion :whileTap="{ scale: 0.97 }">
                                <button
                                    @click="exportToExcel"
                                    class="group flex items-center gap-2 px-4 py-2.5 min-h-[44px] bg-linear-to-r from-emerald-500 to-teal-500 text-white rounded-xl text-sm font-semibold hover:from-emerald-600 hover:to-teal-600 transition-all duration-200 shadow-md shadow-emerald-500/25
                                           focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                >
                                    <FileSpreadsheet :size="18" class="transition-transform group-hover:translate-y-0.5 duration-200" />
                                    <span class="hidden sm:inline">Excel</span>
                                </button>
                            </Motion>

                            <Motion :whileTap="{ scale: 0.97 }">
                                <button
                                    @click="exportToPdf"
                                    class="group flex items-center gap-2 px-4 py-2.5 min-h-[44px] bg-linear-to-r from-red-500 to-rose-500 text-white rounded-xl text-sm font-semibold hover:from-red-600 hover:to-rose-600 transition-all duration-200 shadow-md shadow-red-500/25
                                           focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                >
                                    <FileText :size="18" class="transition-transform group-hover:translate-y-0.5 duration-200" />
                                    <span class="hidden sm:inline">PDF</span>
                                </button>
                            </Motion>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Statistics Cards -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                    <!-- Total Records -->
                    <div class="col-span-2 sm:col-span-1 bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-zinc-800 shadow-sm">
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

                    <!-- Hadir -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-5 border border-emerald-200 dark:border-emerald-800/50 shadow-sm">
                        <p class="text-xs sm:text-sm font-medium text-emerald-600 dark:text-emerald-400">Hadir</p>
                        <div class="mt-1 flex items-baseline gap-2">
                            <p class="text-2xl sm:text-3xl font-bold text-emerald-700 dark:text-emerald-300">
                                {{ statistics.hadir }}
                            </p>
                            <span class="text-sm font-semibold text-emerald-500">
                                {{ statisticsWithPercentage.hadir_percentage }}%
                            </span>
                        </div>
                    </div>

                    <!-- Izin -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-5 border border-blue-200 dark:border-blue-800/50 shadow-sm">
                        <p class="text-xs sm:text-sm font-medium text-blue-600 dark:text-blue-400">Izin</p>
                        <div class="mt-1 flex items-baseline gap-2">
                            <p class="text-2xl sm:text-3xl font-bold text-blue-700 dark:text-blue-300">
                                {{ statistics.izin }}
                            </p>
                            <span class="text-sm font-semibold text-blue-500">
                                {{ statisticsWithPercentage.izin_percentage }}%
                            </span>
                        </div>
                    </div>

                    <!-- Sakit -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-5 border border-amber-200 dark:border-amber-800/50 shadow-sm">
                        <p class="text-xs sm:text-sm font-medium text-amber-600 dark:text-amber-400">Sakit</p>
                        <div class="mt-1 flex items-baseline gap-2">
                            <p class="text-2xl sm:text-3xl font-bold text-amber-700 dark:text-amber-300">
                                {{ statistics.sakit }}
                            </p>
                            <span class="text-sm font-semibold text-amber-500">
                                {{ statisticsWithPercentage.sakit_percentage }}%
                            </span>
                        </div>
                    </div>

                    <!-- Alpha -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-5 border border-red-200 dark:border-red-800/50 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs sm:text-sm font-medium text-red-600 dark:text-red-400">Alpha</p>
                                <div class="mt-1 flex items-baseline gap-2">
                                    <p class="text-2xl sm:text-3xl font-bold text-red-700 dark:text-red-300">
                                        {{ statistics.alpha }}
                                    </p>
                                    <span class="text-sm font-semibold text-red-500">
                                        {{ statisticsWithPercentage.alpha_percentage }}%
                                    </span>
                                </div>
                            </div>
                            <AlertTriangle v-if="statistics.alpha > 0" :size="18" class="text-red-500" />
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

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
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

                        <!-- Class Filter -->
                        <div>
                            <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400 mb-1.5">
                                Kelas
                            </label>
                            <select
                                v-model="filterForm.class_id"
                                class="w-full h-[52px] px-4 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                       rounded-xl text-slate-900 dark:text-white
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900
                                       transition-all duration-150"
                            >
                                <option :value="null">Semua Kelas</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                    {{ cls.nama_lengkap }}
                                </option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400 mb-1.5">
                                Status
                            </label>
                            <select
                                v-model="filterForm.status"
                                class="w-full h-[52px] px-4 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                       rounded-xl text-slate-900 dark:text-white
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900
                                       transition-all duration-150"
                            >
                                <option value="">Semua Status</option>
                                <option value="H">Hadir</option>
                                <option value="I">Izin</option>
                                <option value="S">Sakit</option>
                                <option value="A">Alpha</option>
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
                                        Siswa
                                    </th>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Kelas
                                    </th>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Status
                                    </th>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Keterangan
                                    </th>
                                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Dicatat Oleh
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
                                            {{ attendance.student.nama_lengkap }}
                                        </div>
                                        <div class="text-slate-500 dark:text-slate-400">
                                            NIS: {{ attendance.student.nis }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 sm:px-6 py-4 text-sm text-slate-900 dark:text-white">
                                        {{ attendance.class.nama_lengkap }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 sm:px-6 py-4 text-sm">
                                        <AttendanceStatusBadge :status="attendance.status" />
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 text-sm text-slate-500 dark:text-slate-400">
                                        {{ attendance.keterangan || '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 sm:px-6 py-4 text-sm text-slate-500 dark:text-slate-400">
                                        {{ attendance.recorded_by?.name || '-' }}
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
