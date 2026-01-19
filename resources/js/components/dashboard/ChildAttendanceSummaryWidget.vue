<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { AlertTriangle, TrendingDown, CheckCircle, Calendar, ChevronRight } from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Widget untuk menampilkan ringkasan kehadiran anak di Parent Dashboard
 * dengan warning banner jika tingkat kehadiran di bawah 80%
 *
 * Komponen ini menampilkan:
 * - Persentase kehadiran per anak
 * - Breakdown status (hadir, sakit, izin, alpha)
 * - Warning banner untuk kehadiran rendah
 * - Quick link ke detail attendance
 */

interface AttendanceSummary {
    hadir: number;
    sakit: number;
    izin: number;
    alpha: number;
    total: number;
    percentage: number;
}

interface Child {
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
    children: Child[];
    /**
     * Threshold untuk warning (default 80%)
     */
    warningThreshold?: number;
}

const props = withDefaults(defineProps<Props>(), {
    warningThreshold: 80,
});

const haptics = useHaptics();

/**
 * Check apakah ada anak dengan kehadiran di bawah threshold
 */
const hasLowAttendance = computed(() => {
    return props.children.some(
        (child) => child.attendance_summary.percentage < props.warningThreshold
    );
});

/**
 * Mendapatkan warna berdasarkan persentase kehadiran
 */
const getAttendanceColor = (percentage: number) => {
    if (percentage >= 95) return 'text-green-600 dark:text-green-400';
    if (percentage >= 80) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
};

/**
 * Mendapatkan background color untuk progress bar
 */
const getProgressBgColor = (percentage: number) => {
    if (percentage >= 95) return 'bg-green-500';
    if (percentage >= 80) return 'bg-yellow-500';
    return 'bg-red-500';
};

const handleClick = () => {
    haptics.light();
};
</script>

<template>
    <Motion
        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
        :animate="{ opacity: 1, y: 0, scale: 1 }"
        :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
    >
        <div
            class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden"
        >
            <!-- Header -->
            <div class="p-6 pb-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-xl">
                            <Calendar :size="24" class="text-blue-600 dark:text-blue-300" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Kehadiran Anak
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Bulan
                                {{
                                    new Date().toLocaleDateString('id-ID', {
                                        month: 'long',
                                        year: 'numeric',
                                    })
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Warning Icon jika ada kehadiran rendah -->
                    <div
                        v-if="hasLowAttendance"
                        class="p-2 bg-red-100 dark:bg-red-900/30 rounded-full"
                    >
                        <AlertTriangle :size="20" class="text-red-600 dark:text-red-400" />
                    </div>
                    <div v-else class="p-2 bg-green-100 dark:bg-green-900/30 rounded-full">
                        <CheckCircle :size="20" class="text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>

            <!-- Warning Banner -->
            <div
                v-if="hasLowAttendance"
                class="mx-6 mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl"
            >
                <div class="flex items-start gap-2">
                    <TrendingDown
                        :size="16"
                        class="text-red-600 dark:text-red-400 shrink-0 mt-0.5"
                    />
                    <div>
                        <p class="text-sm font-medium text-red-800 dark:text-red-300">
                            Perhatian! Ada anak dengan kehadiran di bawah {{ warningThreshold }}%
                        </p>
                        <p class="text-xs text-red-700 dark:text-red-400 mt-1">
                            Kehadiran rendah dapat mempengaruhi kenaikan kelas. Silakan hubungi wali
                            kelas.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Children List -->
            <div class="px-6 pb-6 space-y-4">
                <div v-if="children.length === 0" class="py-8 text-center">
                    <p class="text-gray-500 dark:text-gray-400">
                        Belum ada data kehadiran tersedia.
                    </p>
                </div>

                <Link
                    v-for="child in children"
                    :key="child.id"
                    :href="`/parent/children/${child.id}/attendance`"
                    @click="handleClick"
                    class="block"
                >
                    <Motion
                        :whileHover="{ scale: 1.01, x: 2 }"
                        :whileTap="{ scale: 0.98 }"
                        class="p-4 bg-gray-50 dark:bg-zinc-800 rounded-xl border border-gray-100 dark:border-zinc-700 hover:border-blue-200 dark:hover:border-blue-800 transition-colors"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">
                                    {{ child.nama_panggilan || child.nama_lengkap }}
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ child.kelas?.nama_lengkap || 'Kelas belum diset' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    :class="[
                                        'text-2xl font-bold',
                                        getAttendanceColor(child.attendance_summary.percentage),
                                    ]"
                                >
                                    {{ child.attendance_summary.percentage }}%
                                </span>
                                <ChevronRight
                                    :size="20"
                                    class="text-gray-400 dark:text-gray-500"
                                />
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="h-2 bg-gray-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                            <div
                                :class="[
                                    'h-full rounded-full transition-all duration-500',
                                    getProgressBgColor(child.attendance_summary.percentage),
                                ]"
                                :style="{
                                    width: `${child.attendance_summary.percentage}%`,
                                }"
                            ></div>
                        </div>

                        <!-- Breakdown Stats -->
                        <div class="grid grid-cols-4 gap-2 mt-3">
                            <div class="text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Hadir</p>
                                <p class="text-sm font-semibold text-green-600 dark:text-green-400">
                                    {{ child.attendance_summary.hadir }}
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Sakit</p>
                                <p class="text-sm font-semibold text-yellow-600 dark:text-yellow-400">
                                    {{ child.attendance_summary.sakit }}
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Izin</p>
                                <p class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                    {{ child.attendance_summary.izin }}
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Alpha</p>
                                <p class="text-sm font-semibold text-red-600 dark:text-red-400">
                                    {{ child.attendance_summary.alpha }}
                                </p>
                            </div>
                        </div>

                        <!-- Low Attendance Warning per Child -->
                        <div
                            v-if="child.attendance_summary.percentage < warningThreshold"
                            class="mt-3 pt-3 border-t border-red-200 dark:border-red-800/50"
                        >
                            <p class="text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                <AlertTriangle :size="12" />
                                Kehadiran di bawah batas minimal ({{ warningThreshold }}%)
                            </p>
                        </div>
                    </Motion>
                </Link>
            </div>

            <!-- Footer Link -->
            <div
                class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-900/50"
            >
                <Link
                    href="/parent/children"
                    @click="handleClick"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center justify-center gap-1"
                >
                    Lihat Semua Data Anak
                    <ChevronRight :size="16" />
                </Link>
            </div>
        </div>
    </Motion>
</template>
