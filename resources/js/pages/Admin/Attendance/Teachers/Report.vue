<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { Download, FileSpreadsheet, Filter, Calendar, Clock } from 'lucide-vue-next';
import type { TeacherAttendance } from '@/types/attendance';

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

// Filter state
const filterForm = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    teacher_id: props.filters.teacher_id || null,
});

const isFiltering = ref(false);

/**
 * Apply filters ke report
 */
const applyFilters = () => {
    isFiltering.value = true;
    router.get(route('admin.attendance.teachers.report'), filterForm.value, {
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
        teacher_id: null,
    };
    applyFilters();
};

/**
 * Export to Excel for payroll
 */
const exportToPayroll = () => {
    window.location.href = route('admin.attendance.teachers.export.payroll', filterForm.value);
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
                    Laporan presensi guru untuk payroll processing
                </p>
            </Motion>

            <!-- Statistics Cards -->
            <Motion
                tag="div"
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ delay: 0.1 }"
                class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
            >
                <!-- Total Records -->
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <div class="text-sm font-medium text-gray-600">Total Records</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">
                        {{ statistics.total_records }}
                    </div>
                </div>

                <!-- Total Present -->
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <div class="text-sm font-medium text-gray-600">Total Hadir</div>
                    <div class="mt-1 text-2xl font-semibold text-green-600">
                        {{ statistics.total_present }}
                    </div>
                </div>

                <!-- Total Late -->
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <div class="text-sm font-medium text-gray-600">Total Terlambat</div>
                    <div class="mt-1 text-2xl font-semibold text-yellow-600">
                        {{ statistics.total_late }}
                    </div>
                </div>

                <!-- Average Hours -->
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <div class="text-sm font-medium text-gray-600">Rata-rata Jam Kerja</div>
                    <div class="mt-1 text-2xl font-semibold text-blue-600">
                        {{ statistics.average_hours ? statistics.average_hours.toFixed(1) : '0' }}j
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

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
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

                    <!-- Teacher Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Guru
                        </label>
                        <select
                            v-model="filterForm.teacher_id"
                            class="mt-1 block w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option :value="null">Semua Guru</option>
                            <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                {{ teacher.name }}
                            </option>
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

            <!-- Export Button -->
            <Motion
                tag="div"
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ delay: 0.3 }"
                class="mb-6"
            >
                <button
                    @click="exportToPayroll"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                >
                    <FileSpreadsheet class="h-4 w-4" />
                    Export untuk Payroll
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
                                    Guru
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Clock In
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Clock Out
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Jam Kerja
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
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
                                        {{ attendance.teacher.name }}
                                    </div>
                                    <div class="text-gray-500">
                                        {{ attendance.teacher.email }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <Clock class="h-4 w-4 text-gray-400" />
                                        <span class="text-gray-900">{{ formatTime(attendance.clock_in) }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <Clock class="h-4 w-4 text-gray-400" />
                                        <span class="text-gray-900">{{ formatTime(attendance.clock_out) }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ calculateWorkHours(attendance.clock_in, attendance.clock_out) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span
                                        v-if="attendance.is_late"
                                        class="inline-flex rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-800"
                                    >
                                        Terlambat
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800"
                                    >
                                        Tepat Waktu
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
