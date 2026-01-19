<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { Bell, FileText, RefreshCw } from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import AttendanceSummaryCard from '@/components/dashboard/AttendanceSummaryCard.vue';
import TeacherPresenceWidget from '@/components/dashboard/TeacherPresenceWidget.vue';
import { useHaptics } from '@/composables/useHaptics';
import axios from 'axios';

/**
 * Dashboard untuk Kepala Sekolah dengan overview sistem
 * dan akses ke laporan serta monitoring
 * dengan iOS-like staggered animations, haptic feedback, dan real-time polling
 */

interface Props {
    stats: {
        total_students: number;
        total_teachers: number;
        total_classes: number;
        attendance_rate: number;
    };
    todayAttendance: {
        total_students: number;
        present: number;
        absent: number;
        late: number;
        percentage: number;
    };
    classesNotRecorded: Array<{
        id: number;
        nama_lengkap: string;
    }>;
    teacherPresence: {
        total_teachers: number;
        clocked_in: number;
        late_teachers: Array<{
            id: number;
            name: string;
            late_minutes: number;
        }>;
        absent_teachers: Array<{
            id: number;
            name: string;
        }>;
    };
    pendingTeacherLeaves: number;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const handleCardClick = () => haptics.light();

const viewTeacherLeaves = () => {
    haptics.light();
    router.visit('/principal/teacher-leaves');
};

// Real-time polling state
const isRefreshing = ref(false);
const lastUpdated = ref(new Date());
const realtimeData = ref({
    todayAttendance: props.todayAttendance,
    classesNotRecorded: props.classesNotRecorded,
    teacherPresence: props.teacherPresence,
    pendingTeacherLeaves: props.pendingTeacherLeaves,
});

let pollingInterval: number | null = null;

// Fetch latest attendance metrics
const fetchAttendanceMetrics = async () => {
    if (isRefreshing.value) return;
    
    isRefreshing.value = true;
    
    try {
        const response = await axios.get('/principal/dashboard/attendance-metrics');
        
        if (response.data) {
            realtimeData.value = {
                todayAttendance: response.data.todayAttendance,
                classesNotRecorded: response.data.classesNotRecorded,
                teacherPresence: response.data.teacherPresence,
                pendingTeacherLeaves: response.data.pendingTeacherLeaves,
            };
            lastUpdated.value = new Date();
        }
    } catch (error) {
        console.error('Failed to fetch attendance metrics:', error);
    } finally {
        isRefreshing.value = false;
    }
};

// Manual refresh
const manualRefresh = () => {
    haptics.light();
    fetchAttendanceMetrics();
};

// Start polling on mount
onMounted(() => {
    // Poll every 60 seconds (1 minute)
    pollingInterval = window.setInterval(() => {
        fetchAttendanceMetrics();
    }, 60000);
});

// Cleanup on unmount
onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});

// Format last updated time
const formatLastUpdated = () => {
    const now = new Date();
    const diff = Math.floor((now.getTime() - lastUpdated.value.getTime()) / 1000);
    
    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return `${Math.floor(diff / 60)} menit yang lalu`;
    return lastUpdated.value.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Kepala Sekolah" />

        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="bg-white px-6 py-8 border-b border-gray-100 dark:bg-zinc-900 dark:border-zinc-800">
                    <div class="mx-auto max-w-7xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard Kepala Sekolah</h1>
                                <p class="mt-2 text-gray-600 dark:text-gray-400">Monitoring dan laporan sistem sekolah</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ formatLastUpdated() }}
                                </span>
                                <button
                                    @click="manualRefresh"
                                    :disabled="isRefreshing"
                                    class="p-2 rounded-lg bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 transition-colors disabled:opacity-50"
                                    title="Refresh data"
                                >
                                    <RefreshCw
                                        :size="20"
                                        :class="['text-gray-600 dark:text-gray-400', isRefreshing && 'animate-spin']"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <div class="mx-auto max-w-7xl px-6 py-8 space-y-6">
                <!-- Quick Stats -->
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800" @click="handleCardClick">
                            <div class="p-6">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Siswa</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_students }}</p>
                            </div>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800" @click="handleCardClick">
                            <div class="p-6">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Guru</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_teachers }}</p>
                            </div>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800" @click="handleCardClick">
                            <div class="p-6">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Kelas</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_classes }}</p>
                            </div>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800" @click="viewTeacherLeaves">
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Izin Pending</p>
                                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ realtimeData.pendingTeacherLeaves }}</p>
                                    </div>
                                    <div v-if="realtimeData.pendingTeacherLeaves > 0" class="p-2 bg-red-100 dark:bg-red-900 rounded-full">
                                        <Bell :size="20" class="text-red-600 dark:text-red-300" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Motion>
                </div>

                <!-- Real-time Attendance Widgets -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <AttendanceSummaryCard
                        :today-attendance="realtimeData.todayAttendance"
                        :classes-not-recorded="realtimeData.classesNotRecorded"
                        details-url="/principal/attendance/students"
                    />

                    <TeacherPresenceWidget
                        :teacher-presence="realtimeData.teacherPresence"
                    />
                </div>

                <!-- Quick Actions -->
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.3 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <button
                            @click="router.visit('/principal/attendance/dashboard')"
                            class="p-6 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 text-left hover:border-emerald-300 dark:hover:border-emerald-700 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-emerald-100 dark:bg-emerald-900 rounded-xl">
                                    <FileText :size="24" class="text-emerald-600 dark:text-emerald-300" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">Dashboard Kehadiran</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Monitor real-time</p>
                                </div>
                            </div>
                        </button>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.35 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <button
                            @click="router.visit('/principal/attendance/students')"
                            class="p-6 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 text-left hover:border-blue-300 dark:hover:border-blue-700 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-xl">
                                    <FileText :size="24" class="text-blue-600 dark:text-blue-300" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">Absensi Siswa</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Rekap kehadiran siswa</p>
                                </div>
                            </div>
                        </button>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.4 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <button
                            @click="router.visit('/principal/attendance/teachers')"
                            class="p-6 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 text-left hover:border-purple-300 dark:hover:border-purple-700 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-xl">
                                    <FileText :size="24" class="text-purple-600 dark:text-purple-300" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">Presensi Guru</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Rekap clock in/out</p>
                                </div>
                            </div>
                        </button>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.45 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <button
                            @click="viewTeacherLeaves"
                            class="p-6 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 text-left hover:border-green-300 dark:hover:border-green-700 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-xl">
                                    <Bell :size="24" class="text-green-600 dark:text-green-300" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">Approval Izin</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Setujui izin guru</p>
                                </div>
                            </div>
                        </button>
                    </Motion>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

