<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    GraduationCap,
    Users,
    FileCheck,
    Calendar,
    FileText,
    ChevronRight,
} from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import ClockWidget from '@/components/features/attendance/ClockWidget.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import { index as teacherStudentsIndex } from '@/routes/teacher/students';
import { index as teacherLeaveRequestsIndex } from '@/routes/teacher/leave-requests';

/**
 * Dashboard untuk Guru dengan akses ke class management,
 * attendance, grades, dan jadwal mengajar
 * dengan iOS-like staggered animations dan haptic feedback
 *
 * UX Enhancement:
 * - Removed duplicate header (using AppLayout greeting)
 * - Added focus states untuk accessibility
 * - "Segera Hadir" badges untuk fitur yang belum tersedia
 * - Consistent icon styling dan backgrounds
 * - Clock Widget prominent di posisi pertama
 */

interface Props {
    stats: {
        my_classes: number;
        total_students: number;
        pending_grades: number;
        today_schedule: any[];
    };
    pendingLeaveRequests: number;
}

defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

const handleCardClick = () => haptics.light();

/**
 * Show coming soon modal untuk fitur yang belum tersedia
 */
const showComingSoon = (featureName: string) => {
    haptics.light();
    modal.info('Segera Hadir', `Fitur ${featureName} akan segera tersedia dalam pembaruan berikutnya.`);
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Guru" />

        <div class="space-y-6">
            <!-- Main Grid - Clock Widget + Stats -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <!-- Clock In/Out Widget - Prominent Position -->
                <div class="sm:col-span-2">
                    <ClockWidget :teacher-id="$page.props.auth.user.id" />
                </div>

                <!-- Kelas Saya Card (Coming Soon - No Navigation) -->
                <Motion
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <button
                        @click="showComingSoon('Kelas Saya')"
                        class="w-full text-left overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800 hover:border-blue-200 dark:hover:border-blue-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    >
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                        Kelas Saya
                                    </p>
                                    <p class="mt-1.5 text-3xl font-bold text-slate-900 dark:text-white tabular-nums">
                                        {{ stats.my_classes }}
                                    </p>
                                </div>
                                <div class="p-3 bg-blue-100 dark:bg-blue-500/20 rounded-xl">
                                    <GraduationCap :size="24" class="text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-center text-xs">
                                <span class="text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-0.5 rounded-full">
                                    Segera Hadir
                                </span>
                            </div>
                        </div>
                    </button>
                </Motion>

                <!-- Total Siswa Card -->
                <Motion
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <Link
                        :href="teacherStudentsIndex().url"
                        @click="handleCardClick"
                        class="block overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800 hover:border-green-200 dark:hover:border-green-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    >
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                        Total Siswa
                                    </p>
                                    <p class="mt-1.5 text-3xl font-bold text-slate-900 dark:text-white tabular-nums">
                                        {{ stats.total_students }}
                                    </p>
                                </div>
                                <div class="p-3 bg-green-100 dark:bg-green-500/20 rounded-xl">
                                    <Users :size="24" class="text-green-600 dark:text-green-400" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-center text-xs text-slate-500 dark:text-slate-400">
                                <span>Lihat data siswa</span>
                                <ChevronRight :size="14" class="ml-1" />
                            </div>
                        </div>
                    </Link>
                </Motion>

                <!-- Verifikasi Izin Card (dengan Badge) -->
                <Motion
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <Link
                        :href="teacherLeaveRequestsIndex().url"
                        @click="handleCardClick"
                        class="block overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800 hover:border-amber-200 dark:hover:border-amber-700 transition-colors relative focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    >
                        <!-- Pending Badge -->
                        <div
                            v-if="pendingLeaveRequests > 0"
                            class="absolute -top-1 -right-1 flex items-center justify-center min-w-[24px] h-6 px-1.5 bg-red-500 text-white text-xs font-bold rounded-full shadow-lg ring-2 ring-white dark:ring-zinc-900"
                        >
                            {{ pendingLeaveRequests > 99 ? '99+' : pendingLeaveRequests }}
                        </div>

                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                        Verifikasi Izin
                                    </p>
                                    <p
                                        class="mt-1.5 text-3xl font-bold tabular-nums"
                                        :class="pendingLeaveRequests > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white'"
                                    >
                                        {{ pendingLeaveRequests }}
                                    </p>
                                </div>
                                <div
                                    :class="[
                                        'p-3 rounded-xl',
                                        pendingLeaveRequests > 0 ? 'bg-amber-100 dark:bg-amber-500/20' : 'bg-purple-100 dark:bg-purple-500/20',
                                    ]"
                                >
                                    <FileText
                                        :size="24"
                                        :class="pendingLeaveRequests > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-purple-600 dark:text-purple-400'"
                                    />
                                </div>
                            </div>
                            <div class="mt-3 flex items-center text-xs">
                                <span v-if="pendingLeaveRequests > 0" class="text-amber-600 dark:text-amber-400 font-medium">
                                    {{ pendingLeaveRequests }} perlu diverifikasi
                                </span>
                                <span v-else class="text-slate-500 dark:text-slate-400">Tidak ada pending</span>
                                <ChevronRight :size="14" class="ml-1 text-slate-400" />
                            </div>
                        </div>
                    </Link>
                </Motion>

                <!-- Nilai Pending Card (Coming Soon) -->
                <Motion
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <button
                        @click="showComingSoon('Nilai')"
                        class="w-full text-left overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800 hover:border-red-200 dark:hover:border-red-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    >
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                        Nilai Pending
                                    </p>
                                    <p class="mt-1.5 text-3xl font-bold text-slate-900 dark:text-white tabular-nums">
                                        {{ stats.pending_grades }}
                                    </p>
                                </div>
                                <div class="p-3 bg-red-100 dark:bg-red-500/20 rounded-xl">
                                    <FileCheck :size="24" class="text-red-600 dark:text-red-400" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-center text-xs">
                                <span class="text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-0.5 rounded-full">
                                    Segera Hadir
                                </span>
                            </div>
                        </div>
                    </button>
                </Motion>

                <!-- Jadwal Hari Ini Card (Coming Soon) -->
                <Motion
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.3 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <button
                        @click="showComingSoon('Jadwal')"
                        class="w-full text-left overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800 hover:border-indigo-200 dark:hover:border-indigo-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    >
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                        Jadwal Hari Ini
                                    </p>
                                    <p class="mt-1.5 text-3xl font-bold text-slate-900 dark:text-white tabular-nums">
                                        {{ stats.today_schedule.length }}
                                    </p>
                                </div>
                                <div class="p-3 bg-indigo-100 dark:bg-indigo-500/20 rounded-xl">
                                    <Calendar :size="24" class="text-indigo-600 dark:text-indigo-400" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-center text-xs">
                                <span class="text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-0.5 rounded-full">
                                    Segera Hadir
                                </span>
                            </div>
                        </div>
                    </button>
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>
