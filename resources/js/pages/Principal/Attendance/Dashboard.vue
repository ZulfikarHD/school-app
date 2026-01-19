<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    Users,
    GraduationCap,
    CheckCircle,
    XCircle,
    Clock,
    AlertCircle,
    Calendar,
    FileText,
    RefreshCw,
} from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Dashboard Kehadiran untuk Principal dengan overview real-time
 * status absensi siswa dan guru per kelas
 */

interface ClassItem {
    id: number;
    tingkat: string;
    nama: string;
    nama_lengkap: string;
    jumlah_siswa: number;
    has_attendance: boolean;
    wali_kelas?: {
        name: string;
    };
    attendance_summary?: {
        total_siswa: number;
        hadir: number;
        izin: number;
        sakit: number;
        alpha: number;
        belum_diabsen: number;
    };
}

interface Props {
    title: string;
    summary: {
        total_students: number;
        total_present: number;
        total_absent: number;
        attendance_rate: number;
        by_status: {
            hadir: number;
            izin: number;
            sakit: number;
            alpha: number;
        };
    };
    classes: ClassItem[];
    classesWithoutAttendance: ClassItem[];
    teacherPresence: {
        total: number;
        clocked_in: number;
        late: number;
        absent: number;
    };
    filters: {
        date: string;
    };
}

const props = defineProps<Props>();

const haptics = useHaptics();
const selectedDate = ref(props.filters.date);
const isLoading = ref(false);

// Computed
const attendanceRate = computed(() => {
    return props.summary.attendance_rate || 0;
});

const rateColor = computed(() => {
    if (attendanceRate.value >= 95) return 'text-green-600 dark:text-green-400';
    if (attendanceRate.value >= 85) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
});

const classesWithAttendance = computed(() => {
    return props.classes.filter((c) => c.has_attendance);
});

const classesWithoutAttendanceList = computed(() => {
    return props.classes.filter((c) => !c.has_attendance);
});

// Methods
const changeDate = () => {
    haptics.light();
    isLoading.value = true;
    router.get(
        '/principal/attendance/dashboard',
        { date: selectedDate.value },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        }
    );
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

const getAttendancePercentage = (classItem: ClassItem) => {
    if (!classItem.attendance_summary || classItem.attendance_summary.total_siswa === 0) return 0;
    return Math.round(
        (classItem.attendance_summary.hadir / classItem.attendance_summary.total_siswa) * 100
    );
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
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ title }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDate(filters.date) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <Calendar :size="18" class="text-gray-400" />
                            <input
                                v-model="selectedDate"
                                type="date"
                                @change="changeDate"
                                class="px-3 py-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            />
                        </div>
                        <button
                            @click="changeDate"
                            :disabled="isLoading"
                            class="p-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors disabled:opacity-50"
                        >
                            <RefreshCw
                                :size="18"
                                :class="['text-gray-600 dark:text-gray-400', isLoading && 'animate-spin']"
                            />
                        </button>
                    </div>
                </div>
            </Motion>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Siswa -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-gray-100 dark:border-zinc-800 cursor-pointer"
                        @click="navigateTo('/principal/attendance/students')"
                    >
                        <div class="flex items-center justify-between">
                            <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-xl">
                                <GraduationCap :size="24" class="text-blue-600 dark:text-blue-400" />
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Total</span>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">
                            {{ summary.total_students }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Siswa Aktif</p>
                    </div>
                </Motion>

                <!-- Hadir -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-gray-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div class="p-3 bg-green-100 dark:bg-green-900/50 rounded-xl">
                                <CheckCircle :size="24" class="text-green-600 dark:text-green-400" />
                            </div>
                            <span :class="['text-lg font-semibold', rateColor]">
                                {{ attendanceRate }}%
                            </span>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-green-600 dark:text-green-400">
                            {{ summary.by_status.hadir }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Siswa Hadir</p>
                    </div>
                </Motion>

                <!-- Alpha -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-gray-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div class="p-3 bg-red-100 dark:bg-red-900/50 rounded-xl">
                                <XCircle :size="24" class="text-red-600 dark:text-red-400" />
                            </div>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-red-600 dark:text-red-400">
                            {{ summary.by_status.alpha }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Alpha</p>
                    </div>
                </Motion>

                <!-- Guru Hadir -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-gray-100 dark:border-zinc-800 cursor-pointer"
                        @click="navigateTo('/principal/attendance/teachers')"
                    >
                        <div class="flex items-center justify-between">
                            <div class="p-3 bg-purple-100 dark:bg-purple-900/50 rounded-xl">
                                <Users :size="24" class="text-purple-600 dark:text-purple-400" />
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ teacherPresence.clocked_in }}/{{ teacherPresence.total }}
                            </span>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-purple-600 dark:text-purple-400">
                            {{ teacherPresence.clocked_in }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Guru Hadir
                            <span v-if="teacherPresence.late > 0" class="text-yellow-600 dark:text-yellow-400">
                                ({{ teacherPresence.late }} terlambat)
                            </span>
                        </p>
                    </div>
                </Motion>
            </div>

            <!-- Status Breakdown -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 border border-green-200 dark:border-green-800">
                    <p class="text-sm font-medium text-green-800 dark:text-green-300">Hadir</p>
                    <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ summary.by_status.hadir }}
                    </p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Izin</p>
                    <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ summary.by_status.izin }}
                    </p>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-4 border border-yellow-200 dark:border-yellow-800">
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Sakit</p>
                    <p class="mt-1 text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                        {{ summary.by_status.sakit }}
                    </p>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4 border border-red-200 dark:border-red-800">
                    <p class="text-sm font-medium text-red-800 dark:text-red-300">Alpha</p>
                    <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">
                        {{ summary.by_status.alpha }}
                    </p>
                </div>
            </div>

            <!-- Classes Status Grid -->
            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Classes Without Attendance -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.3 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-red-100 dark:bg-red-900/50 rounded-lg">
                                    <AlertCircle :size="20" class="text-red-600 dark:text-red-400" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        Belum Input Absensi
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ classesWithoutAttendanceList.length }} kelas
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-zinc-800 max-h-80 overflow-y-auto">
                            <div
                                v-for="cls in classesWithoutAttendanceList"
                                :key="cls.id"
                                class="px-6 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors"
                            >
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ cls.nama_lengkap }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ cls.jumlah_siswa }} siswa
                                        <span v-if="cls.wali_kelas">
                                            • Wali: {{ cls.wali_kelas.name }}
                                        </span>
                                    </p>
                                </div>
                                <span class="px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 text-xs font-medium rounded-lg">
                                    Belum
                                </span>
                            </div>
                            <div v-if="classesWithoutAttendanceList.length === 0" class="px-6 py-8 text-center">
                                <CheckCircle :size="40" class="mx-auto text-green-500 mb-2" />
                                <p class="text-gray-500 dark:text-gray-400">
                                    Semua kelas sudah input absensi
                                </p>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Classes With Attendance -->
                <Motion
                    :initial="{ opacity: 0, x: 20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.35 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-100 dark:bg-green-900/50 rounded-lg">
                                    <CheckCircle :size="20" class="text-green-600 dark:text-green-400" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        Sudah Input Absensi
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ classesWithAttendance.length }} kelas
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-zinc-800 max-h-80 overflow-y-auto">
                            <div
                                v-for="cls in classesWithAttendance"
                                :key="cls.id"
                                class="px-6 py-3 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ cls.nama_lengkap }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ cls.jumlah_siswa }} siswa
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            :class="[
                                                'text-lg font-semibold',
                                                getAttendancePercentage(cls) >= 90
                                                    ? 'text-green-600 dark:text-green-400'
                                                    : getAttendancePercentage(cls) >= 80
                                                      ? 'text-yellow-600 dark:text-yellow-400'
                                                      : 'text-red-600 dark:text-red-400',
                                            ]"
                                        >
                                            {{ getAttendancePercentage(cls) }}%
                                        </p>
                                    </div>
                                </div>
                                <div v-if="cls.attendance_summary" class="mt-2 flex items-center gap-4 text-xs">
                                    <span class="text-green-600 dark:text-green-400">
                                        H: {{ cls.attendance_summary.hadir }}
                                    </span>
                                    <span class="text-blue-600 dark:text-blue-400">
                                        I: {{ cls.attendance_summary.izin }}
                                    </span>
                                    <span class="text-yellow-600 dark:text-yellow-400">
                                        S: {{ cls.attendance_summary.sakit }}
                                    </span>
                                    <span class="text-red-600 dark:text-red-400">
                                        A: {{ cls.attendance_summary.alpha }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="classesWithAttendance.length === 0" class="px-6 py-8 text-center">
                                <Clock :size="40" class="mx-auto text-gray-400 mb-2" />
                                <p class="text-gray-500 dark:text-gray-400">
                                    Belum ada kelas yang input absensi
                                </p>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>

            <!-- Quick Actions -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.4 }"
                    :whileHover="{ y: -2 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <button
                        @click="navigateTo('/principal/attendance/students')"
                        class="w-full p-4 bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 text-left hover:border-blue-300 dark:hover:border-blue-700 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                                <Users :size="20" class="text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Absensi Siswa</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Lihat detail</p>
                            </div>
                        </div>
                    </button>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.45 }"
                    :whileHover="{ y: -2 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <button
                        @click="navigateTo('/principal/attendance/students/report')"
                        class="w-full p-4 bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 text-left hover:border-emerald-300 dark:hover:border-emerald-700 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg">
                                <FileText :size="20" class="text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Laporan Siswa</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Rekap bulanan</p>
                            </div>
                        </div>
                    </button>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.5 }"
                    :whileHover="{ y: -2 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <button
                        @click="navigateTo('/principal/attendance/teachers')"
                        class="w-full p-4 bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 text-left hover:border-purple-300 dark:hover:border-purple-700 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900/50 rounded-lg">
                                <Clock :size="20" class="text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Presensi Guru</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Clock in/out</p>
                            </div>
                        </div>
                    </button>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.55 }"
                    :whileHover="{ y: -2 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <button
                        @click="navigateTo('/principal/attendance/teachers/report')"
                        class="w-full p-4 bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 text-left hover:border-orange-300 dark:hover:border-orange-700 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-orange-100 dark:bg-orange-900/50 rounded-lg">
                                <FileText :size="20" class="text-orange-600 dark:text-orange-400" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Laporan Guru</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Rekap presensi</p>
                            </div>
                        </div>
                    </button>
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>
