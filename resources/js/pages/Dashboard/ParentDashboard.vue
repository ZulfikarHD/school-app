<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { Users, CreditCard, GraduationCap, FileText, ChevronRight } from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import ChildAttendanceSummaryWidget from '@/components/dashboard/ChildAttendanceSummaryWidget.vue';
import { useHaptics } from '@/composables/useHaptics';
import { index as parentChildrenIndex } from '@/routes/parent/children';
import { index as parentLeaveRequestsIndex } from '@/routes/parent/leave-requests';

/**
 * Dashboard untuk Orang Tua dengan informasi anak,
 * pembayaran, nilai, dan kehadiran
 * dengan iOS-like staggered animations dan haptic feedback
 *
 * Sprint C Enhancement:
 * - Added ChildAttendanceSummaryWidget untuk menampilkan ringkasan kehadiran per anak
 * - Warning banner untuk kehadiran di bawah 80%
 * - Quick stats cards dengan link navigasi
 */

interface AttendanceSummary {
    hadir: number;
    sakit: number;
    izin: number;
    alpha: number;
    total: number;
    percentage: number;
}

interface ChildWithAttendance {
    id: number;
    nama_lengkap: string;
    nama_panggilan?: string;
    kelas?: {
        id: number;
        nama_lengkap: string;
    };
    attendance_summary: AttendanceSummary;
}

interface Props {
    stats: {
        children: any[];
        pending_payments: number;
        recent_grades: any[];
        attendance_summary: any[];
    };
    childrenWithAttendance: ChildWithAttendance[];
    pendingLeaveRequests: number;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const handleCardClick = () => haptics.light();
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Orang Tua" />

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
                            Dashboard Orang Tua
                        </h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Pantau perkembangan anak Anda
                        </p>
                    </div>
                </div>
            </Motion>

            <div class="mx-auto max-w-7xl px-6 py-8 space-y-8">
                <!-- Quick Stats Cards -->
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Jumlah Anak Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <Link
                            :href="parentChildrenIndex().url"
                            @click="handleCardClick"
                            class="block overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800 hover:border-blue-200 dark:hover:border-blue-800 transition-colors"
                        >
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-600 dark:text-gray-400"
                                        >
                                            Jumlah Anak
                                        </p>
                                        <p
                                            class="mt-2 text-3xl font-bold text-gray-900 dark:text-white"
                                        >
                                            {{ stats.children.length }}
                                        </p>
                                    </div>
                                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-xl">
                                        <Users :size="24" class="text-blue-600 dark:text-blue-300" />
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center text-xs text-gray-500">
                                    <span>Lihat data anak</span>
                                    <ChevronRight :size="14" class="ml-1" />
                                </div>
                            </div>
                        </Link>
                    </Motion>

                    <!-- Tagihan Pending Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
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
                                        <p
                                            class="text-sm font-medium text-gray-600 dark:text-gray-400"
                                        >
                                            Tagihan Pending
                                        </p>
                                        <p
                                            class="mt-2 text-3xl font-bold text-gray-900 dark:text-white"
                                        >
                                            {{ stats.pending_payments }}
                                        </p>
                                    </div>
                                    <div class="p-3 bg-amber-100 dark:bg-amber-900 rounded-xl">
                                        <CreditCard
                                            :size="24"
                                            class="text-amber-600 dark:text-amber-300"
                                        />
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center text-xs text-gray-500">
                                    <span>Segera hadir</span>
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <!-- Nilai Terbaru Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
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
                                        <p
                                            class="text-sm font-medium text-gray-600 dark:text-gray-400"
                                        >
                                            Nilai Terbaru
                                        </p>
                                        <p
                                            class="mt-2 text-3xl font-bold text-gray-900 dark:text-white"
                                        >
                                            {{ stats.recent_grades.length }}
                                        </p>
                                    </div>
                                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-xl">
                                        <GraduationCap
                                            :size="24"
                                            class="text-green-600 dark:text-green-300"
                                        />
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center text-xs text-gray-500">
                                    <span>Segera hadir</span>
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <!-- Pengajuan Izin Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <Link
                            :href="parentLeaveRequestsIndex().url"
                            @click="handleCardClick"
                            class="block overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800 hover:border-blue-200 dark:hover:border-blue-800 transition-colors"
                        >
                            <div class="p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-600 dark:text-gray-400"
                                        >
                                            Pengajuan Izin
                                        </p>
                                        <p
                                            class="mt-2 text-3xl font-bold text-gray-900 dark:text-white"
                                        >
                                            {{ pendingLeaveRequests }}
                                        </p>
                                    </div>
                                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-xl">
                                        <FileText
                                            :size="24"
                                            class="text-purple-600 dark:text-purple-300"
                                        />
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center text-xs text-gray-500">
                                    <span>Pending diproses</span>
                                    <ChevronRight :size="14" class="ml-1" />
                                </div>
                            </div>
                        </Link>
                    </Motion>
                </div>

                <!-- Attendance Summary Widget Section -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Attendance Widget (Full Width on Mobile, Half on Desktop) -->
                    <div class="lg:col-span-2">
                        <ChildAttendanceSummaryWidget
                            :children="childrenWithAttendance"
                            :warning-threshold="80"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

