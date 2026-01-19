<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    Users,
    Clock,
    AlertCircle,
    Calendar,
    FileText,
    ArrowLeft,
    CheckCircle,
    XCircle,
} from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Halaman rekap presensi guru untuk Principal (read-only)
 * dengan tampilan clock in/out dan status kehadiran
 */

interface TeacherAttendance {
    id: number;
    tanggal: string;
    clock_in: string | null;
    clock_out: string | null;
    is_late: boolean;
    late_minutes: number;
    status: string;
    keterangan: string | null;
    teacher: {
        id: number;
        name: string;
        email: string;
    };
}

interface AbsentTeacher {
    id: number;
    name: string;
    email: string;
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
    absentTeachers: AbsentTeacher[];
    summary: {
        total_guru: number;
        hadir: number;
        terlambat: number;
        belum_hadir: number;
    };
    filters: {
        date: string;
        is_late?: string;
    };
}

const props = defineProps<Props>();

const haptics = useHaptics();

// State
const selectedDate = ref(props.filters.date);
const filterLate = ref(props.filters.is_late || '');

// Computed
const attendancePercentage = computed(() => {
    if (props.summary.total_guru === 0) return 0;
    return Math.round((props.summary.hadir / props.summary.total_guru) * 100);
});

// Methods
const applyFilters = () => {
    haptics.light();
    const params: Record<string, string> = { date: selectedDate.value };
    if (filterLate.value) {
        params.is_late = filterLate.value;
    }
    router.get('/principal/attendance/teachers', params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const goToPage = (page: number) => {
    haptics.light();
    const params: Record<string, string | number> = {
        date: selectedDate.value,
        page,
    };
    if (filterLate.value) {
        params.is_late = filterLate.value;
    }
    router.get('/principal/attendance/teachers', params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const navigateTo = (path: string) => {
    haptics.light();
    router.visit(path);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const formatTime = (datetime: string | null) => {
    if (!datetime) return '-';
    return new Date(datetime).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const calculateDuration = (clockIn: string | null, clockOut: string | null) => {
    if (!clockIn || !clockOut) return '-';
    const start = new Date(clockIn);
    const end = new Date(clockOut);
    const diff = Math.floor((end.getTime() - start.getTime()) / (1000 * 60));
    const hours = Math.floor(diff / 60);
    const minutes = diff % 60;
    return `${hours}j ${minutes}m`;
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="space-y-6">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <button
                            @click="navigateTo('/principal/attendance/dashboard')"
                            class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                        >
                            <ArrowLeft :size="20" class="text-gray-600 dark:text-gray-400" />
                        </button>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ title }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ formatDate(filters.date) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <Calendar :size="18" class="text-gray-400" />
                            <input
                                v-model="selectedDate"
                                type="date"
                                @change="applyFilters"
                                class="px-3 py-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            />
                        </div>

                        <select
                            v-model="filterLate"
                            @change="applyFilters"
                            class="px-3 py-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        >
                            <option value="">Semua Status</option>
                            <option value="1">Terlambat</option>
                            <option value="0">Tepat Waktu</option>
                        </select>

                        <button
                            @click="navigateTo('/principal/attendance/teachers/report')"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition-colors"
                        >
                            <FileText :size="18" />
                            Lihat Laporan
                        </button>
                    </div>
                </div>
            </Motion>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-gray-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                                <Users :size="20" class="text-blue-600 dark:text-blue-400" />
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Total</span>
                        </div>
                        <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ summary.total_guru }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Guru</p>
                    </div>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-gray-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div class="p-2 bg-green-100 dark:bg-green-900/50 rounded-lg">
                                <CheckCircle :size="20" class="text-green-600 dark:text-green-400" />
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ attendancePercentage }}%</span>
                        </div>
                        <p class="mt-3 text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ summary.hadir }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Hadir</p>
                    </div>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-gray-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div class="p-2 bg-yellow-100 dark:bg-yellow-900/50 rounded-lg">
                                <Clock :size="20" class="text-yellow-600 dark:text-yellow-400" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                            {{ summary.terlambat }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Terlambat</p>
                    </div>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-gray-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div class="p-2 bg-red-100 dark:bg-red-900/50 rounded-lg">
                                <XCircle :size="20" class="text-red-600 dark:text-red-400" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-bold text-red-600 dark:text-red-400">
                            {{ summary.belum_hadir }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum Hadir</p>
                    </div>
                </Motion>
            </div>

            <!-- Main Content Grid -->
            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Attendance Table -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.3 }"
                    class="lg:col-span-2"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                Daftar Presensi Guru
                            </h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-zinc-800 border-b border-gray-100 dark:border-zinc-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Guru
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Jam Masuk
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Jam Keluar
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Durasi
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                    <tr
                                        v-for="attendance in attendances.data"
                                        :key="attendance.id"
                                        class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ attendance.teacher?.name || '-' }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ attendance.teacher?.email || '-' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm text-gray-900 dark:text-white">
                                                {{ formatTime(attendance.clock_in) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm text-gray-900 dark:text-white">
                                                {{ formatTime(attendance.clock_out) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ calculateDuration(attendance.clock_in, attendance.clock_out) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span
                                                v-if="attendance.is_late"
                                                class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300 text-xs font-medium rounded-lg"
                                            >
                                                Terlambat {{ attendance.late_minutes }}m
                                            </span>
                                            <span
                                                v-else
                                                class="px-2 py-1 bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 text-xs font-medium rounded-lg"
                                            >
                                                Tepat Waktu
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="attendances.data.length === 0">
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <Clock :size="48" class="mx-auto mb-3 opacity-50" />
                                            <p class="text-lg font-medium">Belum ada data presensi</p>
                                            <p class="text-sm mt-1">Belum ada guru yang clock in</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="attendances.last_page > 1" class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Halaman {{ attendances.current_page }} dari {{ attendances.last_page }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        v-for="page in attendances.last_page"
                                        :key="page"
                                        @click="goToPage(page)"
                                        :class="[
                                            'px-3 py-1 rounded-lg text-sm font-medium transition-colors',
                                            page === attendances.current_page
                                                ? 'bg-emerald-600 text-white'
                                                : 'bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-zinc-600',
                                        ]"
                                    >
                                        {{ page }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Absent Teachers List -->
                <Motion
                    :initial="{ opacity: 0, x: 20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.35 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-red-100 dark:bg-red-900/50 rounded-lg">
                                    <AlertCircle :size="20" class="text-red-600 dark:text-red-400" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        Belum Clock In
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ absentTeachers.length }} guru
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-zinc-800 max-h-96 overflow-y-auto">
                            <div
                                v-for="teacher in absentTeachers"
                                :key="teacher.id"
                                class="px-6 py-3 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ teacher.name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ teacher.email }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 text-xs font-medium rounded-lg">
                                        Absent
                                    </span>
                                </div>
                            </div>
                            <div v-if="absentTeachers.length === 0" class="px-6 py-8 text-center">
                                <CheckCircle :size="40" class="mx-auto text-green-500 mb-2" />
                                <p class="text-gray-500 dark:text-gray-400">
                                    Semua guru sudah hadir
                                </p>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>
