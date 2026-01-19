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
import { index as teacherStudentsIndex } from '@/routes/teacher/students';
import { index as teacherLeaveRequestsIndex } from '@/routes/teacher/leave-requests';

/**
 * Dashboard untuk Guru dengan akses ke class management,
 * attendance, grades, dan jadwal mengajar
 * dengan iOS-like staggered animations dan haptic feedback
 *
 * Sprint C Enhancement:
 * - Added pending leave requests card dengan badge count
 * - Improved card navigation dengan icons
 * - Clock Widget sudah prominent di posisi pertama
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

const props = defineProps<Props>();

const haptics = useHaptics();
const handleCardClick = () => haptics.light();
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Guru" />

        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div
                    class="bg-white px-6 py-8 border-b border-gray-100 dark:bg-zinc-900 dark:border-zinc-800"
                >
                    <div class="mx-auto max-w-7xl">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Dashboard Guru
                        </h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Kelola kelas dan nilai siswa
                        </p>
                    </div>
                </div>
            </Motion>

            <div class="mx-auto max-w-7xl px-6 py-8">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                    <!-- Clock In/Out Widget -->
                    <div class="sm:col-span-2">
                        <ClockWidget :teacher-id="$page.props.auth.user.id" />
                    </div>

                    <!-- Kelas Saya Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div
                            class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800"
                            @click="handleCardClick"
                        >
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                            Kelas Saya
                                        </p>
                                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                            {{ stats.my_classes }}
                                        </p>
                                    </div>
                                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-xl">
                                        <GraduationCap :size="24" class="text-blue-600 dark:text-blue-300" />
                                    </div>
                                </div>
                            </div>
                        </div>
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
                            class="block overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800 hover:border-blue-200 dark:hover:border-blue-800 transition-colors"
                        >
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                            Total Siswa
                                        </p>
                                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                            {{ stats.total_students }}
                                        </p>
                                    </div>
                                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-xl">
                                        <Users :size="24" class="text-green-600 dark:text-green-300" />
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center text-xs text-gray-500">
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
                            class="block overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800 hover:border-blue-200 dark:hover:border-blue-800 transition-colors relative"
                        >
                            <!-- Pending Badge -->
                            <div
                                v-if="pendingLeaveRequests > 0"
                                class="absolute -top-1 -right-1 flex items-center justify-center w-6 h-6 bg-red-500 text-white text-xs font-bold rounded-full shadow-lg ring-2 ring-white dark:ring-zinc-900"
                            >
                                {{ pendingLeaveRequests > 99 ? '99+' : pendingLeaveRequests }}
                            </div>

                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                            Verifikasi Izin
                                        </p>
                                        <p
                                            class="mt-2 text-3xl font-bold"
                                            :class="pendingLeaveRequests > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white'"
                                        >
                                            {{ pendingLeaveRequests }}
                                        </p>
                                    </div>
                                    <div
                                        :class="[
                                            'p-3 rounded-xl',
                                            pendingLeaveRequests > 0 ? 'bg-amber-100 dark:bg-amber-900' : 'bg-purple-100 dark:bg-purple-900',
                                        ]"
                                    >
                                        <FileText
                                            :size="24"
                                            :class="pendingLeaveRequests > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-purple-600 dark:text-purple-300'"
                                        />
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center text-xs text-gray-500">
                                    <span v-if="pendingLeaveRequests > 0" class="text-amber-600">
                                        {{ pendingLeaveRequests }} perlu diverifikasi
                                    </span>
                                    <span v-else>Tidak ada pending</span>
                                    <ChevronRight :size="14" class="ml-1" />
                                </div>
                            </div>
                        </Link>
                    </Motion>

                    <!-- Nilai Pending Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div
                            class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800"
                            @click="handleCardClick"
                        >
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                            Nilai Pending
                                        </p>
                                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                            {{ stats.pending_grades }}
                                        </p>
                                    </div>
                                    <div class="p-3 bg-red-100 dark:bg-red-900 rounded-xl">
                                        <FileCheck :size="24" class="text-red-600 dark:text-red-300" />
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center text-xs text-gray-500">
                                    <span>Segera hadir</span>
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <!-- Jadwal Hari Ini Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.3 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div
                            class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800"
                            @click="handleCardClick"
                        >
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                            Jadwal Hari Ini
                                        </p>
                                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                            {{ stats.today_schedule.length }}
                                        </p>
                                    </div>
                                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-xl">
                                        <Calendar :size="24" class="text-indigo-600 dark:text-indigo-300" />
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center text-xs text-gray-500">
                                    <span>Segera hadir</span>
                                </div>
                            </div>
                        </div>
                    </Motion>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
