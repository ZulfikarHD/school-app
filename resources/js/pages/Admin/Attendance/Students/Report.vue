<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { Download, FileSpreadsheet, FileText, Filter, Calendar, Users } from 'lucide-vue-next';
import AttendanceStatusBadge from '@/components/features/attendance/AttendanceStatusBadge.vue';
import type { StudentAttendance } from '@/types/attendance';

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

// Filter state
const filterForm = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    class_id: props.filters.class_id || null,
    status: props.filters.status || '',
});

const isFiltering = ref(false);

/**
 * Apply filters ke report
 */
const applyFilters = () => {
    isFiltering.value = true;
    router.get(route('admin.attendance.students.report'), filterForm.value, {
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
    window.location.href = route('admin.attendance.students.export', filterForm.value);
};

/**
 * Export to PDF
 */
const exportToPdf = () => {
    window.location.href = route('admin.attendance.students.export.pdf', filterForm.value);
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
        <div class="min-h-screen bg-gray-50 px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <Motion
                tag="div"
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                class="mb-6"
            >
                <h1 class="text-2xl font-semibold text-gray-900">{{ title }}</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Laporan presensi siswa dengan filter dan export
                </p>
            </Motion>

            <!-- Statistics Cards -->
            <Motion
                tag="div"
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ delay: 0.1 }"
                class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5"
            >
                <!-- Total Records -->
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <div class="text-sm font-medium text-gray-600">Total Records</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">
                        {{ statistics.total_records }}
                    </div>
                </div>

                <!-- Hadir -->
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <div class="text-sm font-medium text-gray-600">Hadir</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <div class="text-2xl font-semibold text-green-600">
                            {{ statistics.hadir }}
                        </div>
                        <div class="text-sm text-gray-500">
                            ({{ statisticsWithPercentage.hadir_percentage }}%)
                        </div>
                    </div>
                </div>

                <!-- Izin -->
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <div class="text-sm font-medium text-gray-600">Izin</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <div class="text-2xl font-semibold text-blue-600">
                            {{ statistics.izin }}
                        </div>
                        <div class="text-sm text-gray-500">
                            ({{ statisticsWithPercentage.izin_percentage }}%)
                        </div>
                    </div>
                </div>

                <!-- Sakit -->
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <div class="text-sm font-medium text-gray-600">Sakit</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <div class="text-2xl font-semibold text-yellow-600">
                            {{ statistics.sakit }}
                        </div>
                        <div class="text-sm text-gray-500">
                            ({{ statisticsWithPercentage.sakit_percentage }}%)
                        </div>
                    </div>
                </div>

                <!-- Alpha -->
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <div class="text-sm font-medium text-gray-600">Alpha</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <div class="text-2xl font-semibold text-red-600">
                            {{ statistics.alpha }}
                        </div>
                        <div class="text-sm text-gray-500">
                            ({{ statisticsWithPercentage.alpha_percentage }}%)
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Filters Panel -->
            <Motion
                tag="div"
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ delay: 0.2 }"
                class="mb-6 rounded-lg bg-white p-6 shadow-sm"
            >
                <div class="mb-4 flex items-center gap-2">
                    <Filter class="h-5 w-5 text-gray-600" />
                    <h2 class="text-lg font-medium text-gray-900">Filter Laporan</h2>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Start Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Tanggal Mulai
                        </label>
                        <div class="relative mt-1">
                            <Calendar class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                            <input
                                v-model="filterForm.start_date"
                                type="date"
                                class="block w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Tanggal Selesai
                        </label>
                        <div class="relative mt-1">
                            <Calendar class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                            <input
                                v-model="filterForm.end_date"
                                type="date"
                                class="block w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <!-- Class Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Kelas
                        </label>
                        <div class="relative mt-1">
                            <Users class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                            <select
                                v-model="filterForm.class_id"
                                class="block w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option :value="null">Semua Kelas</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                    {{ cls.nama_lengkap }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Status
                        </label>
                        <select
                            v-model="filterForm.status"
                            class="mt-1 block w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-blue-500 focus:ring-blue-500"
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
                <div class="mt-4 flex gap-3">
                    <button
                        @click="applyFilters"
                        :disabled="isFiltering"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <Filter class="h-4 w-4" />
                        {{ isFiltering ? 'Memfilter...' : 'Terapkan Filter' }}
                    </button>

                    <button
                        @click="resetFilters"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Reset
                    </button>
                </div>
            </Motion>

            <!-- Export Buttons -->
            <Motion
                tag="div"
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ delay: 0.3 }"
                class="mb-6 flex gap-3"
            >
                <button
                    @click="exportToExcel"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                >
                    <FileSpreadsheet class="h-4 w-4" />
                    Export Excel
                </button>

                <button
                    @click="exportToPdf"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                >
                    <FileText class="h-4 w-4" />
                    Export PDF
                </button>
            </Motion>

            <!-- Data Table -->
            <Motion
                tag="div"
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ delay: 0.4 }"
                class="overflow-hidden rounded-lg bg-white shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Tanggal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Siswa
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Kelas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Keterangan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Dicatat Oleh
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-if="attendances.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                    Tidak ada data presensi. Silakan sesuaikan filter.
                                </td>
                            </tr>
                            <tr v-for="attendance in attendances" :key="attendance.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                    {{ formatDate(attendance.tanggal) }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-medium text-gray-900">
                                        {{ attendance.student.nama_lengkap }}
                                    </div>
                                    <div class="text-gray-500">
                                        NIS: {{ attendance.student.nis }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                    {{ attendance.class.nama_lengkap }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <AttendanceStatusBadge :status="attendance.status" />
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ attendance.keterangan || '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ attendance.recorded_by?.name || '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
