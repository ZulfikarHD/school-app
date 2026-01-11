<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { Calendar, Download, ChevronLeft, ChevronRight, TrendingUp } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import AttendanceStatusBadge from '@/components/features/attendance/AttendanceStatusBadge.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';

interface Student {
    id: number;
    nama_lengkap: string;
    nis: string;
    kelas: {
        nama_lengkap: string;
    };
}

interface Attendance {
    id: number;
    tanggal: string;
    status: 'H' | 'I' | 'S' | 'A';
    keterangan: string | null;
}

interface Props {
    title: string;
    student: Student;
    attendances: Attendance[];
    summary: {
        hadir: number;
        izin: number;
        sakit: number;
        alpha: number;
        total: number;
    };
    filters: {
        start_date: string;
        end_date: string;
    };
}

const props = defineProps<Props>();

const haptics = useHaptics();

// State
const currentMonth = ref(new Date(props.filters.start_date));

// Computed
const attendancePercentage = computed(() => {
    if (props.summary.total === 0) return 0;
    return Math.round((props.summary.hadir / props.summary.total) * 100);
});

const attendanceColor = computed(() => {
    if (attendancePercentage.value >= 95) return 'text-green-600 dark:text-green-400';
    if (attendancePercentage.value >= 85) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
});

const calendarDays = computed(() => {
    const year = currentMonth.value.getFullYear();
    const month = currentMonth.value.getMonth();
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startDayOfWeek = firstDay.getDay();
    
    const days = [];
    
    // Add empty cells for days before month starts
    for (let i = 0; i < startDayOfWeek; i++) {
        days.push(null);
    }
    
    // Add days of month
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        const dateStr = date.toISOString().split('T')[0];
        const attendance = props.attendances.find(a => a.tanggal === dateStr);
        
        days.push({
            day,
            date: dateStr,
            attendance,
            isToday: dateStr === new Date().toISOString().split('T')[0],
            isWeekend: date.getDay() === 0 || date.getDay() === 6,
        });
    }
    
    return days;
});

const monthName = computed(() => {
    return currentMonth.value.toLocaleDateString('id-ID', {
        month: 'long',
        year: 'numeric',
    });
});

// Methods
const previousMonth = () => {
    haptics.light();
    const newDate = new Date(currentMonth.value);
    newDate.setMonth(newDate.getMonth() - 1);
    currentMonth.value = newDate;
    loadMonth();
};

const nextMonth = () => {
    haptics.light();
    const newDate = new Date(currentMonth.value);
    newDate.setMonth(newDate.getMonth() + 1);
    currentMonth.value = newDate;
    loadMonth();
};

const loadMonth = () => {
    const year = currentMonth.value.getFullYear();
    const month = currentMonth.value.getMonth();
    const startDate = new Date(year, month, 1).toISOString().split('T')[0];
    const endDate = new Date(year, month + 1, 0).toISOString().split('T')[0];
    
    router.get(`/parent/children/${props.student.id}/attendance`, {
        start_date: startDate,
        end_date: endDate,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportPDF = () => {
    haptics.medium();
    window.location.href = `/parent/children/${props.student.id}/attendance/export?start_date=${props.filters.start_date}&end_date=${props.filters.end_date}`;
};

const getDayColor = (day: any) => {
    if (!day || !day.attendance) return '';
    
    const colors = {
        'H': 'bg-green-100 dark:bg-green-900 border-green-300 dark:border-green-700',
        'I': 'bg-blue-100 dark:bg-blue-900 border-blue-300 dark:border-blue-700',
        'S': 'bg-yellow-100 dark:bg-yellow-900 border-yellow-300 dark:border-yellow-700',
        'A': 'bg-red-100 dark:bg-red-900 border-red-300 dark:border-red-700',
    };
    
    return colors[day.attendance.status] || '';
};

const getDayTextColor = (day: any) => {
    if (!day || !day.attendance) return 'text-gray-900 dark:text-white';
    
    const colors = {
        'H': 'text-green-700 dark:text-green-300',
        'I': 'text-blue-700 dark:text-blue-300',
        'S': 'text-yellow-700 dark:text-yellow-300',
        'A': 'text-red-700 dark:text-red-300',
    };
    
    return colors[day.attendance.status] || 'text-gray-900 dark:text-white';
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <Link
                            :href="`/parent/children/${student.id}`"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <ChevronLeft :size="24" />
                        </Link>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ title }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ student.nama_lengkap }} • {{ student.kelas.nama_lengkap }}
                            </p>
                        </div>
                    </div>
                </div>

                <Motion
                    tag="button"
                    :whileTap="{ scale: 0.95 }"
                    @click="exportPDF"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors"
                >
                    <Download :size="18" />
                    Download Laporan
                </Motion>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tingkat Kehadiran</p>
                            <p :class="['mt-1 text-3xl font-semibold', attendanceColor]">
                                {{ attendancePercentage }}%
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-xl">
                            <TrendingUp :size="24" class="text-blue-600 dark:text-blue-300" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Hadir</p>
                    <p class="mt-1 text-3xl font-semibold text-green-600 dark:text-green-400">
                        {{ summary.hadir }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Izin</p>
                    <p class="mt-1 text-3xl font-semibold text-blue-600 dark:text-blue-400">
                        {{ summary.izin }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sakit</p>
                    <p class="mt-1 text-3xl font-semibold text-yellow-600 dark:text-yellow-400">
                        {{ summary.sakit }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Alpha</p>
                    <p class="mt-1 text-3xl font-semibold text-red-600 dark:text-red-400">
                        {{ summary.alpha }}
                    </p>
                </div>
            </div>

            <!-- Calendar -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Calendar Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                        Kalender Kehadiran
                    </h2>
                    <div class="flex items-center gap-3">
                        <button
                            @click="previousMonth"
                            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                        >
                            <ChevronLeft :size="20" class="text-gray-600 dark:text-gray-400" />
                        </button>
                        <span class="text-sm font-medium text-gray-900 dark:text-white min-w-[140px] text-center">
                            {{ monthName }}
                        </span>
                        <button
                            @click="nextMonth"
                            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                        >
                            <ChevronRight :size="20" class="text-gray-600 dark:text-gray-400" />
                        </button>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="p-6">
                    <!-- Day Headers -->
                    <div class="grid grid-cols-7 gap-2 mb-2">
                        <div
                            v-for="day in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']"
                            :key="day"
                            class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 py-2"
                        >
                            {{ day }}
                        </div>
                    </div>

                    <!-- Calendar Days -->
                    <div class="grid grid-cols-7 gap-2">
                        <div
                            v-for="(day, index) in calendarDays"
                            :key="index"
                            :class="[
                                'aspect-square p-2 rounded-xl border-2 transition-all',
                                day ? 'cursor-pointer hover:scale-105' : '',
                                day?.isToday ? 'ring-2 ring-blue-500' : '',
                                day?.isWeekend && !day?.attendance ? 'bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700' : '',
                                !day?.isWeekend && !day?.attendance ? 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700' : '',
                                getDayColor(day)
                            ]"
                        >
                            <div v-if="day" class="h-full flex flex-col items-center justify-center">
                                <span :class="['text-sm font-medium', getDayTextColor(day)]">
                                    {{ day.day }}
                                </span>
                                <div v-if="day.attendance" class="mt-1">
                                    <span class="text-xs font-bold">
                                        {{ day.attendance.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Keterangan:</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-green-100 dark:bg-green-900 border-2 border-green-300 dark:border-green-700"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Hadir (H)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-blue-100 dark:bg-blue-900 border-2 border-blue-300 dark:border-blue-700"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Izin (I)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-yellow-100 dark:bg-yellow-900 border-2 border-yellow-300 dark:border-yellow-700"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Sakit (S)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-red-100 dark:bg-red-900 border-2 border-red-300 dark:border-red-700"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Alpha (A)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Details List -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Detail Kehadiran
                    </h3>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="attendance in attendances"
                        :key="attendance.id"
                        class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <Calendar :size="18" class="text-gray-400" />
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ new Date(attendance.tanggal).toLocaleDateString('id-ID', {
                                            weekday: 'long',
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric'
                                        }) }}
                                    </p>
                                    <p v-if="attendance.keterangan" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ attendance.keterangan }}
                                    </p>
                                </div>
                            </div>
                            <AttendanceStatusBadge :status="attendance.status" />
                        </div>
                    </div>

                    <div v-if="attendances.length === 0" class="px-6 py-12 text-center">
                        <Calendar :size="48" class="mx-auto mb-3 text-gray-400 opacity-50" />
                        <p class="text-lg font-medium text-gray-500 dark:text-gray-400">Belum ada data kehadiran</p>
                        <p class="text-sm text-gray-400 mt-1">Pilih bulan lain untuk melihat riwayat</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
