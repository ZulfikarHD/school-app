<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    Users,
    Clock,
    Calendar,
    Filter,
    ArrowLeft,
    TrendingUp,
    AlertTriangle,
} from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import Badge from '@/components/ui/Badge.vue';

/**
 * Halaman laporan presensi guru untuk Principal
 * dengan statistik jam kerja dan kehadiran bulanan
 */

interface TeacherAttendance {
    id: number;
    tanggal: string;
    clock_in: string | null;
    clock_out: string | null;
    is_late: boolean;
    late_minutes: number;
    status: string;
    teacher: {
        id: number;
        name: string;
        email: string;
    };
}

interface Teacher {
    id: number;
    name: string;
    email: string;
}

interface Props {
    title: string;
    attendances: TeacherAttendance[];
    statistics: {
        total_records: number;
        total_present: number;
        total_late: number;
        total_hours: number;
        average_hours: number;
        work_days: number;
        attendance_percentage: number;
    };
    teachers: Teacher[];
    filters: {
        start_date: string;
        end_date: string;
        teacher_id?: number;
    };
}

const props = defineProps<Props>();

const haptics = useHaptics();

// State
const filterForm = ref({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    teacher_id: props.filters.teacher_id || '',
});

const showFilters = ref(false);

// Computed
const hasFilters = computed(() => {
    return !!filterForm.value.teacher_id;
});

// Group attendances by teacher
const attendancesByTeacher = computed(() => {
    const grouped: Record<number, { teacher: Teacher; attendances: TeacherAttendance[]; stats: { present: number; late: number; totalHours: number } }> = {};

    props.attendances.forEach((att) => {
        if (!att.teacher) return;

        if (!grouped[att.teacher.id]) {
            grouped[att.teacher.id] = {
                teacher: att.teacher,
                attendances: [],
                stats: { present: 0, late: 0, totalHours: 0 },
            };
        }

        grouped[att.teacher.id].attendances.push(att);
        grouped[att.teacher.id].stats.present++;

        if (att.is_late) {
            grouped[att.teacher.id].stats.late++;
        }

        if (att.clock_in && att.clock_out) {
            const start = new Date(att.clock_in);
            const end = new Date(att.clock_out);
            const hours = (end.getTime() - start.getTime()) / (1000 * 60 * 60);
            grouped[att.teacher.id].stats.totalHours += hours;
        }
    });

    return Object.values(grouped);
});

// Methods
const applyFilters = () => {
    haptics.light();
    router.get('/principal/attendance/teachers/report', filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    haptics.light();
    const today = new Date();
    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

    filterForm.value = {
        start_date: startOfMonth.toISOString().split('T')[0],
        end_date: today.toISOString().split('T')[0],
        teacher_id: '',
    };
    router.get('/principal/attendance/teachers/report', {
        start_date: filterForm.value.start_date,
        end_date: filterForm.value.end_date,
    }, {
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
        day: 'numeric',
        month: 'short',
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
                            class="p-2 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                        >
                            <ArrowLeft :size="20" class="text-slate-600 dark:text-slate-400" />
                        </button>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                                {{ title }}
                            </h1>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ formatDate(filters.start_date) }} - {{ formatDate(filters.end_date) }}
                            </p>
                        </div>
                    </div>

                    <Motion
                        tag="button"
                        :animate="{ scale: showFilters ? 0.95 : 1 }"
                        @click="showFilters = !showFilters"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors"
                    >
                        <Filter :size="18" />
                        Filter
                        <Badge
                            v-if="hasFilters"
                            variant="success"
                            size="xs"
                            class="ml-1"
                        >
                            Aktif
                        </Badge>
                    </Motion>
                </div>
            </Motion>

            <!-- Filters Panel -->
            <Motion
                v-if="showFilters"
                :initial="{ opacity: 0, height: 0 }"
                :animate="{ opacity: 1, height: 'auto' }"
                :exit="{ opacity: 0, height: 0 }"
                class="bg-white dark:bg-zinc-900 rounded-xl p-6 border border-slate-200 dark:border-zinc-800"
            >
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">
                            Tanggal Mulai
                        </label>
                        <input
                            v-model="filterForm.start_date"
                            type="date"
                            class="w-full px-4 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 transition-all"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">
                            Tanggal Selesai
                        </label>
                        <input
                            v-model="filterForm.end_date"
                            type="date"
                            class="w-full px-4 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 transition-all"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">
                            Guru
                        </label>
                        <select
                            v-model="filterForm.teacher_id"
                            class="w-full px-4 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 transition-all"
                        >
                            <option value="">Semua Guru</option>
                            <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                {{ teacher.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition-colors"
                    >
                        Terapkan Filter
                    </button>
                    <button
                        v-if="hasFilters"
                        @click="clearFilters"
                        class="px-4 py-2 bg-slate-200 dark:bg-zinc-700 hover:bg-slate-300 dark:hover:bg-zinc-600 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-medium transition-colors"
                    >
                        Reset Filter
                    </button>
                </div>
            </Motion>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                                <Calendar :size="20" class="text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-bold text-slate-900 dark:text-white">
                            {{ statistics.work_days }}
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Hari Kerja</p>
                    </div>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div class="p-2 bg-green-100 dark:bg-green-900/50 rounded-lg">
                                <Users :size="20" class="text-green-600 dark:text-green-400" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ statistics.total_present }}
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Total Hadir</p>
                    </div>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div class="p-2 bg-yellow-100 dark:bg-yellow-900/50 rounded-lg">
                                <AlertTriangle :size="20" class="text-yellow-600 dark:text-yellow-400" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                            {{ statistics.total_late }}
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Terlambat</p>
                    </div>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/50 rounded-lg">
                                <Clock :size="20" class="text-purple-600 dark:text-purple-400" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-bold text-purple-600 dark:text-purple-400">
                            {{ statistics.total_hours }}
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Total Jam</p>
                    </div>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.3 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg">
                                <TrendingUp :size="20" class="text-emerald-600 dark:text-emerald-400" />
                            </div>
                        </div>
                        <p class="mt-3 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                            {{ statistics.average_hours }}
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Rata-rata/hari</p>
                    </div>
                </Motion>
            </div>

            <!-- Teachers Summary Table -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.35 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-zinc-800">
                        <h3 class="font-semibold text-slate-900 dark:text-white">
                            Ringkasan per Guru
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 dark:bg-zinc-800 border-b border-slate-200 dark:border-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Guru
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Hari Hadir
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Terlambat
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Total Jam
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Rata-rata/Hari
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                <tr
                                    v-for="item in attendancesByTeacher"
                                    :key="item.teacher.id"
                                    class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                {{ item.teacher.name }}
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ item.teacher.email }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                            {{ item.stats.present }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <Badge
                                            :variant="item.stats.late > 0 ? 'warning' : 'success'"
                                            size="sm"
                                            rounded="square"
                                        >
                                            {{ item.stats.late }}x
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-900 dark:text-white">
                                        {{ item.stats.totalHours.toFixed(1) }} jam
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-500 dark:text-slate-400">
                                        {{ item.stats.present > 0 ? (item.stats.totalHours / item.stats.present).toFixed(1) : 0 }} jam
                                    </td>
                                </tr>
                                <tr v-if="attendancesByTeacher.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                        <Users :size="48" class="mx-auto mb-3 opacity-50" />
                                        <p class="text-lg font-medium">Tidak ada data</p>
                                        <p class="text-sm mt-1">Coba ubah filter periode</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </Motion>

            <!-- Detailed Attendance List -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.4 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-zinc-800">
                        <h3 class="font-semibold text-slate-900 dark:text-white">
                            Detail Presensi
                        </h3>
                    </div>

                    <div class="overflow-x-auto max-h-96">
                        <table class="w-full">
                            <thead class="bg-slate-50 dark:bg-zinc-800 border-b border-slate-200 dark:border-zinc-700 sticky top-0">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Tanggal
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Guru
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Jam Masuk
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Jam Keluar
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                <tr
                                    v-for="attendance in attendances"
                                    :key="attendance.id"
                                    class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                                >
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                                        {{ formatDate(attendance.tanggal) }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                                        {{ attendance.teacher?.name || '-' }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-center text-sm text-slate-900 dark:text-white">
                                        {{ formatTime(attendance.clock_in) }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-center text-sm text-slate-900 dark:text-white">
                                        {{ formatTime(attendance.clock_out) }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-center">
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
