<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Download, Filter, Calendar, Users, GraduationCap, TrendingUp, X, AlertTriangle } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import AttendanceStatusBadge from '@/components/features/attendance/AttendanceStatusBadge.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Halaman monitoring presensi siswa dengan summary statistics
 * dan advanced filtering untuk keperluan pelaporan
 */

interface Attendance {
    id: number;
    tanggal: string;
    status: 'H' | 'I' | 'S' | 'A';
    keterangan: string | null;
    student: {
        id: number;
        nama_lengkap: string;
        nis: string;
    };
    class: {
        id: number;
        nama_lengkap: string;
    };
    recorded_by: {
        name: string;
    };
    recorded_at: string;
}

interface Props {
    title: string;
    attendances: {
        data: Attendance[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    classes: Array<{ id: number; nama_lengkap: string }>;
    filters: {
        class_id?: number;
        date?: string;
        status?: string;
        date_from?: string;
        date_to?: string;
    };
}

const props = defineProps<Props>();

const haptics = useHaptics();

// State
const filterForm = ref({
    class_id: props.filters.class_id || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    status: props.filters.status || '',
});

const showFilters = ref(false);

// Computed
const hasFilters = computed(() => {
    return filterForm.value.class_id ||
           filterForm.value.date_from ||
           filterForm.value.date_to ||
           filterForm.value.status;
});

const summary = computed(() => {
    const data = props.attendances.data;
    return {
        total: data.length,
        hadir: data.filter(a => a.status === 'H').length,
        izin: data.filter(a => a.status === 'I').length,
        sakit: data.filter(a => a.status === 'S').length,
        alpha: data.filter(a => a.status === 'A').length,
    };
});

const attendancePercentage = computed(() => {
    if (summary.value.total === 0) return 0;
    return Math.round((summary.value.hadir / summary.value.total) * 100);
});

// Methods
const applyFilters = () => {
    haptics.light();
    router.get('/admin/attendance/students', filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    haptics.light();
    filterForm.value = {
        class_id: '',
        date_from: '',
        date_to: '',
        status: '',
    };
    router.get('/admin/attendance/students', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportToExcel = () => {
    haptics.medium();
    window.location.href = `/admin/attendance/students/export?${new URLSearchParams(filterForm.value as any).toString()}`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatTime = (datetime: string) => {
    return new Date(datetime).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
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
                                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center shadow-lg shadow-blue-500/25">
                                    <GraduationCap class="w-7 h-7 text-white" />
                                </div>
                                <div>
                                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                                        {{ title }}
                                    </h1>
                                    <p class="mt-1 text-gray-600 dark:text-gray-400">
                                        Monitoring dan laporan kehadiran siswa
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
                                    <span class="hidden sm:inline">Export Excel</span>
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
                    <!-- Total Records - Featured -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                        class="col-span-2 sm:col-span-1"
                    >
                        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-5 shadow-lg shadow-blue-500/20">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs sm:text-sm font-medium text-blue-100">Total Records</p>
                                    <p class="mt-1 text-2xl sm:text-3xl font-bold text-white">
                                        {{ props.attendances.total }}
                                    </p>
                                </div>
                                <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                                    <Users :size="20" class="text-white" />
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
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs sm:text-sm font-medium text-emerald-600 dark:text-emerald-400">Hadir</p>
                                    <p class="mt-1 text-2xl sm:text-3xl font-bold text-emerald-700 dark:text-emerald-300">
                                        {{ summary.hadir }}
                                    </p>
                                </div>
                                <span class="text-sm font-semibold text-emerald-500">{{ attendancePercentage }}%</span>
                            </div>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-blue-200 dark:border-blue-800/50 shadow-sm">
                            <p class="text-xs sm:text-sm font-medium text-blue-600 dark:text-blue-400">Izin</p>
                            <p class="mt-1 text-2xl sm:text-3xl font-bold text-blue-700 dark:text-blue-300">
                                {{ summary.izin }}
                            </p>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-amber-200 dark:border-amber-800/50 shadow-sm">
                            <p class="text-xs sm:text-sm font-medium text-amber-600 dark:text-amber-400">Sakit</p>
                            <p class="mt-1 text-2xl sm:text-3xl font-bold text-amber-700 dark:text-amber-300">
                                {{ summary.sakit }}
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
                                        {{ summary.alpha }}
                                    </p>
                                </div>
                                <AlertTriangle v-if="summary.alpha > 0" :size="18" class="text-red-500" />
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

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-zinc-400 mb-1.5">
                                    Kelas
                                </label>
                                <select
                                    v-model="filterForm.class_id"
                                    class="w-full px-3 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-900 dark:text-white
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                           transition-all duration-150"
                                >
                                    <option value="">Semua Kelas</option>
                                    <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                        {{ cls.nama_lengkap }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-zinc-400 mb-1.5">
                                    Tanggal Dari
                                </label>
                                <input
                                    v-model="filterForm.date_from"
                                    type="date"
                                    class="w-full px-3 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-900 dark:text-white
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                           transition-all duration-150"
                                />
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-zinc-400 mb-1.5">
                                    Tanggal Sampai
                                </label>
                                <input
                                    v-model="filterForm.date_to"
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
                                    <option value="H">Hadir</option>
                                    <option value="I">Izin</option>
                                    <option value="S">Sakit</option>
                                    <option value="A">Alpha</option>
                                </select>
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

                <!-- Attendance Table -->
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
                                            Siswa
                                        </th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                            Kelas
                                        </th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                            Status
                                        </th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                            Keterangan
                                        </th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                            Dicatat Oleh
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                    <tr
                                        v-for="attendance in attendances.data"
                                        :key="attendance.id"
                                        class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                                            {{ formatDate(attendance.tanggal) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                    {{ attendance.student.nama_lengkap }}
                                                </div>
                                                <div class="text-sm text-slate-500 dark:text-zinc-400">
                                                    NIS: {{ attendance.student.nis }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                                            {{ attendance.class.nama_lengkap }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <AttendanceStatusBadge :status="attendance.status" />
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-zinc-400">
                                            {{ attendance.keterangan || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm text-slate-900 dark:text-white">
                                                    {{ attendance.recorded_by.name }}
                                                </div>
                                                <div class="text-xs text-slate-500 dark:text-zinc-400">
                                                    {{ formatTime(attendance.recorded_at) }}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="attendances.data.length === 0">
                                        <td colspan="6" class="px-6 py-16 text-center">
                                            <Calendar :size="48" class="mx-auto mb-4 text-slate-300 dark:text-zinc-600" />
                                            <p class="text-lg font-semibold text-slate-700 dark:text-zinc-300">Tidak ada data presensi</p>
                                            <p class="text-sm mt-1 text-slate-500 dark:text-zinc-500">Coba ubah filter atau pilih tanggal lain</p>
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
                                        @click="router.get(`/admin/attendance/students?page=${page}`, filterForm)"
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
