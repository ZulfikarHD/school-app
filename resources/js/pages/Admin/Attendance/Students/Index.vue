<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Download, Filter, Calendar, Users } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import AttendanceStatusBadge from '@/components/features/attendance/AttendanceStatusBadge.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';

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

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ title }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Monitoring dan laporan kehadiran siswa
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
                        Export Excel
                    </Motion>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Records</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ props.attendances.total }}
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-xl">
                            <Users :size="24" class="text-blue-600 dark:text-blue-300" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Hadir</p>
                            <p class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">
                                {{ summary.hadir }}
                            </p>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ attendancePercentage }}%
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Izin</p>
                            <p class="mt-1 text-2xl font-semibold text-blue-600 dark:text-blue-400">
                                {{ summary.izin }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Sakit</p>
                            <p class="mt-1 text-2xl font-semibold text-yellow-600 dark:text-yellow-400">
                                {{ summary.sakit }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Alpha</p>
                            <p class="mt-1 text-2xl font-semibold text-red-600 dark:text-red-400">
                                {{ summary.alpha }}
                            </p>
                        </div>
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
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Kelas
                        </label>
                        <select
                            v-model="filterForm.class_id"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Semua Kelas</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                {{ cls.nama_lengkap }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal Dari
                        </label>
                        <input
                            v-model="filterForm.date_from"
                            type="date"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal Sampai
                        </label>
                        <input
                            v-model="filterForm.date_to"
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
                            <option value="H">Hadir</option>
                            <option value="I">Izin</option>
                            <option value="S">Sakit</option>
                            <option value="A">Alpha</option>
                        </select>
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

            <!-- Attendance Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Tanggal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Siswa
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Kelas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Keterangan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Dicatat Oleh
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="attendance in attendances.data"
                                :key="attendance.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ formatDate(attendance.tanggal) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ attendance.student.nama_lengkap }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            NIS: {{ attendance.student.nis }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ attendance.class.nama_lengkap }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <AttendanceStatusBadge :status="attendance.status" />
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ attendance.keterangan || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ attendance.recorded_by.name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ formatTime(attendance.recorded_at) }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="attendances.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <Calendar :size="48" class="mx-auto mb-3 opacity-50" />
                                    <p class="text-lg font-medium">Tidak ada data presensi</p>
                                    <p class="text-sm mt-1">Coba ubah filter atau pilih tanggal lain</p>
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
                                @click="router.get(`/admin/attendance/students?page=${page}`, filterForm)"
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
