<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Download, Filter, Calendar, Clock, AlertTriangle } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';

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
        'HADIR': 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
        'TERLAMBAT': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
        'IZIN': 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
        'SAKIT': 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300',
        'ALPHA': 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300';
};

const getRowColor = (attendance: TeacherAttendance) => {
    if (attendance.status === 'ALPHA') return 'bg-red-50 dark:bg-red-900/10';
    if (attendance.is_late) return 'bg-yellow-50 dark:bg-yellow-900/10';
    return '';
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ title }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Monitoring kehadiran dan keterlambatan guru untuk payroll
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Motion
                        tag="button"
                        :animate="{ scale: showFilters ? 0.95 : 1 }"
                        @click="showFilters = !showFilters"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <Filter :size="18" />
                        Filter
                        <span v-if="hasFilters" class="ml-1 px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full text-xs">
                            Aktif
                        </span>
                    </Motion>

                    <Motion
                        tag="button"
                        :whileTap="{ scale: 0.95 }"
                        @click="exportToExcel"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-medium transition-colors"
                    >
                        <Download :size="18" />
                        Export Payroll
                    </Motion>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tingkat Kehadiran</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ attendanceRate }}%
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-xl">
                            <Clock :size="24" class="text-blue-600 dark:text-blue-300" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Hadir</p>
                            <p class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">
                                {{ summary.total_hadir }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Terlambat</p>
                            <p class="mt-1 text-2xl font-semibold text-yellow-600 dark:text-yellow-400">
                                {{ summary.total_terlambat }}
                            </p>
                        </div>
                        <AlertTriangle v-if="summary.total_terlambat > 0" :size="20" class="text-yellow-600 dark:text-yellow-400" />
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Izin</p>
                            <p class="mt-1 text-2xl font-semibold text-blue-600 dark:text-blue-400">
                                {{ summary.total_izin }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Alpha</p>
                            <p class="mt-1 text-2xl font-semibold text-red-600 dark:text-red-400">
                                {{ summary.total_alpha }}
                            </p>
                        </div>
                        <AlertTriangle v-if="summary.total_alpha > 0" :size="20" class="text-red-600 dark:text-red-400" />
                    </div>
                </div>
            </div>

            <!-- Filters Panel -->
            <Motion
                v-if="showFilters"
                :initial="{ opacity: 0, height: 0 }"
                :animate="{ opacity: 1, height: 'auto' }"
                :exit="{ opacity: 0, height: 0 }"
                class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700"
            >
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal
                        </label>
                        <input
                            v-model="filterForm.date"
                            type="date"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status
                        </label>
                        <select
                            v-model="filterForm.status"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Filter Keterlambatan
                        </label>
                        <label class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer">
                            <input
                                v-model="filterForm.is_late"
                                type="checkbox"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                            <span class="text-sm text-gray-900 dark:text-white">Hanya yang terlambat</span>
                        </label>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors"
                    >
                        Terapkan Filter
                    </button>
                    <button
                        v-if="hasFilters"
                        @click="clearFilters"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium transition-colors"
                    >
                        Reset Filter
                    </button>
                </div>
            </Motion>

            <!-- Teacher Attendance Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Tanggal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Guru
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Clock In
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Clock Out
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Durasi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Terlambat
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="attendance in attendances.data"
                                :key="attendance.id"
                                :class="[
                                    'hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors',
                                    getRowColor(attendance)
                                ]"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ formatDate(attendance.tanggal) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ attendance.teacher.name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            NIP: {{ attendance.teacher.nip }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ formatTime(attendance.clock_in) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ formatTime(attendance.clock_out) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ calculateDuration(attendance.clock_in, attendance.clock_out) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-medium',
                                        getStatusColor(attendance.status)
                                    ]">
                                        {{ attendance.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span v-if="attendance.is_late" class="text-yellow-600 dark:text-yellow-400 font-medium">
                                        {{ attendance.late_minutes }} menit
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                            </tr>
                            <tr v-if="attendances.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <Calendar :size="48" class="mx-auto mb-3 opacity-50" />
                                    <p class="text-lg font-medium">Tidak ada data presensi guru</p>
                                    <p class="text-sm mt-1">Pilih tanggal lain atau ubah filter</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="attendances.last_page > 1" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan {{ (attendances.current_page - 1) * attendances.per_page + 1 }} - 
                            {{ Math.min(attendances.current_page * attendances.per_page, attendances.total) }} 
                            dari {{ attendances.total }} data
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                v-for="page in attendances.last_page"
                                :key="page"
                                @click="router.get(`/admin/attendance/teachers?page=${page}`, filterForm)"
                                :class="[
                                    'px-3 py-1 rounded-lg text-sm font-medium transition-colors',
                                    page === attendances.current_page
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                {{ page }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
