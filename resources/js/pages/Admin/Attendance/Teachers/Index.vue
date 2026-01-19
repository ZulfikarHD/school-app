<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Download, Filter, Calendar, Clock, AlertTriangle, Users, TrendingUp, X } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Halaman monitoring presensi guru dengan summary statistics
 * dan filter tanggal untuk keperluan payroll processing
 */

interface TeacherAttendance {
    id: number;
    tanggal: string;
    clock_in: string | null;
    clock_out: string | null;
    status: 'HADIR' | 'TERLAMBAT' | 'IZIN' | 'SAKIT' | 'ALPHA';
    is_late: boolean;
    late_minutes: number | null;
    teacher: {
        id: number;
        name: string;
        nip: string;
    };
}

interface Props {
    title: string;
    attendances: {
        data: TeacherAttendance[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    summary: {
        total_hadir: number;
        total_terlambat: number;
        total_izin: number;
        total_alpha: number;
    };
    filters: {
        date?: string;
        status?: string;
        is_late?: boolean;
    };
}

const props = defineProps<Props>();

const haptics = useHaptics();

// State
const filterForm = ref({
    date: props.filters.date || new Date().toISOString().split('T')[0],
    status: props.filters.status || '',
    is_late: props.filters.is_late || false,
});

const showFilters = ref(false);

// Computed
const hasFilters = computed(() => {
    return filterForm.value.status || filterForm.value.is_late;
});

const totalPresent = computed(() => {
    return props.summary.total_hadir + props.summary.total_terlambat;
});

const attendanceRate = computed(() => {
    const total = props.summary.total_hadir + props.summary.total_terlambat + 
                  props.summary.total_izin + props.summary.total_alpha;
    if (total === 0) return 0;
    return Math.round((totalPresent.value / total) * 100);
});

// Methods
const applyFilters = () => {
    haptics.light();
    router.get('/admin/attendance/teachers', filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    haptics.light();
    filterForm.value = {
        date: new Date().toISOString().split('T')[0],
        status: '',
        is_late: false,
    };
    router.get('/admin/attendance/teachers', { date: filterForm.value.date }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportToExcel = () => {
    haptics.medium();
    window.location.href = `/admin/attendance/teachers/export?${new URLSearchParams(filterForm.value as any).toString()}`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatTime = (time: string | null) => {
    if (!time) return '-';
    return new Date(time).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const calculateDuration = (clockIn: string | null, clockOut: string | null) => {
    if (!clockIn || !clockOut) return '-';
    
    const start = new Date(clockIn);
    const end = new Date(clockOut);
    const diff = end.getTime() - start.getTime();
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    
    return `${hours}j ${minutes}m`;
};

const getStatusColor = (status: string) => {
    const colors = {
        'HADIR': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'TERLAMBAT': 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'IZIN': 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'SAKIT': 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        'ALPHA': 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300';
};

const getRowColor = (attendance: TeacherAttendance) => {
    if (attendance.status === 'ALPHA') return 'bg-red-50 dark:bg-red-900/10';
    if (attendance.is_late) return 'bg-amber-50 dark:bg-amber-900/10';
    return '';
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
            <!-- Header Section -->
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="bg-white px-6 py-8 border-b border-gray-100 dark:bg-zinc-900 dark:border-zinc-800">
                    <div class="mx-auto max-w-7xl">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <!-- Icon Container -->
                                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                                    <Users class="w-7 h-7 text-white" />
                                </div>
                                <div>
                                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                                        {{ title }}
                                    </h1>
                                    <p class="mt-1 text-gray-600 dark:text-gray-400">
                                        Monitoring kehadiran dan keterlambatan guru untuk payroll
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <Motion
                                    tag="button"
                                    :whileTap="{ scale: 0.97 }"
                                    @click="showFilters = !showFilters"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors shadow-sm"
                                >
                                    <Filter :size="18" />
                                    Filter
                                    <span v-if="hasFilters" class="ml-1 w-2 h-2 bg-emerald-500 rounded-full"></span>
                                </Motion>

                                <Motion
                                    tag="button"
                                    :whileTap="{ scale: 0.97 }"
                                    @click="exportToExcel"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm shadow-emerald-500/25"
                                >
                                    <Download :size="18" />
                                    <span class="hidden sm:inline">Export Payroll</span>
                                    <span class="sm:hidden">Export</span>
                                </Motion>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <div class="mx-auto max-w-7xl px-6 py-8 space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    <!-- Attendance Rate Card - Featured -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                        class="col-span-2 sm:col-span-1"
                    >
                        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-5 shadow-lg shadow-blue-500/20">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-blue-100">Tingkat Kehadiran</p>
                                    <p class="mt-1 text-3xl font-bold text-white">
                                        {{ attendanceRate }}%
                                    </p>
                                </div>
                                <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                                    <TrendingUp :size="24" class="text-white" />
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-emerald-200 dark:border-emerald-800/50 shadow-sm">
                            <p class="text-xs sm:text-sm font-medium text-emerald-600 dark:text-emerald-400">Hadir</p>
                            <p class="mt-1 text-2xl sm:text-3xl font-bold text-emerald-700 dark:text-emerald-300">
                                {{ summary.total_hadir }}
                            </p>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-amber-200 dark:border-amber-800/50 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs sm:text-sm font-medium text-amber-600 dark:text-amber-400">Terlambat</p>
                                    <p class="mt-1 text-2xl sm:text-3xl font-bold text-amber-700 dark:text-amber-300">
                                        {{ summary.total_terlambat }}
                                    </p>
                                </div>
                                <AlertTriangle v-if="summary.total_terlambat > 0" :size="18" class="text-amber-500" />
                            </div>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-blue-200 dark:border-blue-800/50 shadow-sm">
                            <p class="text-xs sm:text-sm font-medium text-blue-600 dark:text-blue-400">Izin</p>
                            <p class="mt-1 text-2xl sm:text-3xl font-bold text-blue-700 dark:text-blue-300">
                                {{ summary.total_izin }}
                            </p>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-red-200 dark:border-red-800/50 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs sm:text-sm font-medium text-red-600 dark:text-red-400">Alpha</p>
                                    <p class="mt-1 text-2xl sm:text-3xl font-bold text-red-700 dark:text-red-300">
                                        {{ summary.total_alpha }}
                                    </p>
                                </div>
                                <AlertTriangle v-if="summary.total_alpha > 0" :size="18" class="text-red-500" />
                            </div>
                        </div>
                    </Motion>
                </div>

            <!-- Filters Panel -->
            <Motion
                v-if="showFilters"
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-slate-200 dark:border-zinc-800 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-zinc-300 uppercase tracking-wide">Filter Data</h3>
                        <button
                            v-if="hasFilters"
                            @click="clearFilters"
                            class="flex items-center gap-1 text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"
                        >
                            <X :size="14" />
                            Reset
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-zinc-400 mb-1.5">
                                Tanggal
                            </label>
                            <input
                                v-model="filterForm.date"
                                type="date"
                                class="w-full px-3 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                       rounded-xl text-slate-900 dark:text-white
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                       transition-all duration-150"
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-zinc-400 mb-1.5">
                                Status
                            </label>
                            <select
                                v-model="filterForm.status"
                                class="w-full px-3 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                       rounded-xl text-slate-900 dark:text-white
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                       transition-all duration-150"
                            >
                                <option value="">Semua Status</option>
                                <option value="HADIR">Hadir</option>
                                <option value="TERLAMBAT">Terlambat</option>
                                <option value="IZIN">Izin</option>
                                <option value="SAKIT">Sakit</option>
                                <option value="ALPHA">Alpha</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-zinc-400 mb-1.5">
                                Filter Keterlambatan
                            </label>
                            <label class="flex items-center gap-3 px-3 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl cursor-pointer hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors">
                                <input
                                    v-model="filterForm.is_late"
                                    type="checkbox"
                                    class="w-4 h-4 text-emerald-600 border-slate-300 dark:border-zinc-600 rounded focus:ring-emerald-500"
                                />
                                <span class="text-sm text-slate-700 dark:text-zinc-300">Hanya yang terlambat</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-200 dark:border-zinc-700">
                        <Motion
                            tag="button"
                            :whileTap="{ scale: 0.97 }"
                            @click="applyFilters"
                            class="w-full sm:w-auto px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm shadow-emerald-500/25"
                        >
                            Terapkan Filter
                        </Motion>
                    </div>
                </div>
            </Motion>

            <!-- Teacher Attendance Table -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.3 }"
            >
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-100 dark:border-zinc-800">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                    Tanggal
                                </th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                    Guru
                                </th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                    Clock In
                                </th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                    Clock Out
                                </th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                    Durasi
                                </th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                    Status
                                </th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                    Terlambat
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <tr
                                v-for="attendance in attendances.data"
                                :key="attendance.id"
                                :class="[
                                    'hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors',
                                    getRowColor(attendance)
                                ]"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                                    {{ formatDate(attendance.tanggal) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                            {{ attendance.teacher.name }}
                                        </div>
                                        <div class="text-sm text-slate-500 dark:text-zinc-400">
                                            NIP: {{ attendance.teacher.nip }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2 text-sm text-slate-900 dark:text-white">
                                        <Clock :size="14" class="text-slate-400" />
                                        {{ formatTime(attendance.clock_in) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2 text-sm text-slate-900 dark:text-white">
                                        <Clock :size="14" class="text-slate-400" />
                                        {{ formatTime(attendance.clock_out) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">
                                    {{ calculateDuration(attendance.clock_in, attendance.clock_out) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-semibold',
                                        getStatusColor(attendance.status)
                                    ]">
                                        {{ attendance.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span v-if="attendance.is_late" class="text-amber-600 dark:text-amber-400 font-semibold">
                                        {{ attendance.late_minutes }} menit
                                    </span>
                                    <span v-else class="text-slate-400 dark:text-zinc-500">-</span>
                                </td>
                            </tr>
                            <tr v-if="attendances.data.length === 0">
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <Calendar :size="48" class="mx-auto mb-4 text-slate-300 dark:text-zinc-600" />
                                    <p class="text-lg font-semibold text-slate-700 dark:text-zinc-300">Tidak ada data presensi guru</p>
                                    <p class="text-sm mt-1 text-slate-500 dark:text-zinc-500">Pilih tanggal lain atau ubah filter</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="attendances.last_page > 1" class="px-6 py-4 border-t border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/30">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-slate-500 dark:text-zinc-400">
                            Menampilkan {{ (attendances.current_page - 1) * attendances.per_page + 1 }} - 
                            {{ Math.min(attendances.current_page * attendances.per_page, attendances.total) }} 
                            dari {{ attendances.total }} data
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap justify-center">
                            <button
                                v-for="page in attendances.last_page"
                                :key="page"
                                @click="router.get(`/admin/attendance/teachers?page=${page}`, filterForm)"
                                :class="[
                                    'min-w-[36px] h-9 px-3 rounded-lg text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50',
                                    page === attendances.current_page
                                        ? 'bg-emerald-500 text-white shadow-sm shadow-emerald-500/25'
                                        : 'bg-white dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-700 border border-slate-200 dark:border-zinc-700'
                                ]"
                            >
                                {{ page }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </Motion>
            </div>
        </div>
    </AppLayout>
</template>
