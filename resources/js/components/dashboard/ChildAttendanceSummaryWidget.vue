<script setup lang="ts">
/**
 * ChildAttendanceSummaryWidget - Widget ringkasan kehadiran anak
 * untuk Parent Dashboard dengan warning banner untuk kehadiran rendah,
 * progress bars dengan ARIA support, dan clickable cards per anak
 */
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { AlertTriangle, TrendingDown, CheckCircle, Calendar, ChevronRight, Users } from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';

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
 * Format bulan dan tahun saat ini dalam Bahasa Indonesia
 */
const currentMonth = computed(() => {
    return new Date().toLocaleDateString('id-ID', {
        month: 'long',
        year: 'numeric',
    });
});

/**
 * Mendapatkan warna berdasarkan persentase kehadiran (design system colors)
 */
const getAttendanceColor = (percentage: number) => {
    if (percentage >= 95) return 'text-emerald-600 dark:text-emerald-400';
    if (percentage >= 80) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
};

/**
 * Mendapatkan background color untuk progress bar
 */
const getProgressBgColor = (percentage: number) => {
    if (percentage >= 95) return 'bg-emerald-500';
    if (percentage >= 80) return 'bg-amber-500';
    return 'bg-red-500';
};

/**
 * Mendapatkan status label berdasarkan persentase
 */
const getStatusLabel = (percentage: number) => {
    if (percentage >= 95) return 'Sangat Baik';
    if (percentage >= 80) return 'Baik';
    return 'Perlu Perhatian';
};

/**
 * Mendapatkan badge background color
 */
const getStatusBgColor = (percentage: number) => {
    if (percentage >= 95) return 'bg-emerald-50 dark:bg-emerald-950/30';
    if (percentage >= 80) return 'bg-amber-50 dark:bg-amber-950/30';
    return 'bg-red-50 dark:bg-red-950/30';
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
            class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden"
        >
            <!-- Header -->
            <div class="p-6 pb-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-sky-100 dark:bg-sky-900/30 rounded-xl">
                            <Calendar :size="22" class="text-sky-600 dark:text-sky-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                Kehadiran Anak
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Bulan {{ currentMonth }}
                            </p>
                        </div>
                    </div>

                    <!-- Status Icon -->
                    <div
                        v-if="hasLowAttendance"
                        class="p-2 bg-red-100 dark:bg-red-900/30 rounded-full"
                    >
                        <AlertTriangle :size="20" class="text-red-600 dark:text-red-400" />
                    </div>
                    <div v-else class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-full">
                        <CheckCircle :size="20" class="text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
            </div>

            <!-- Warning Banner -->
            <Motion
                v-if="hasLowAttendance"
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ delay: 0.1 }"
                class="mx-6 mb-4 p-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-xl"
            >
                <div class="flex items-start gap-2.5">
                    <div class="p-1 bg-red-100 dark:bg-red-900/30 rounded-lg shrink-0">
                        <TrendingDown :size="14" class="text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-red-800 dark:text-red-300">
                            Perhatian! Ada anak dengan kehadiran di bawah {{ warningThreshold }}%
                        </p>
                        <p class="text-xs text-red-700 dark:text-red-400 mt-1">
                            Kehadiran rendah dapat mempengaruhi kenaikan kelas. Silakan hubungi wali kelas.
                        </p>
                    </div>
                </div>
            </Motion>

            <!-- Children List -->
            <div class="px-6 pb-6 space-y-4">
                <!-- Empty State dengan icon dan guidance -->
                <div v-if="children.length === 0" class="py-10 text-center">
                    <div class="mx-auto w-16 h-16 bg-slate-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mb-4">
                        <Users :size="28" class="text-slate-400 dark:text-slate-500" />
                    </div>
                    <p class="text-slate-700 dark:text-slate-300 font-medium mb-1">
                        Belum ada data kehadiran
                    </p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Data kehadiran akan muncul setelah absensi diinput oleh wali kelas
                    </p>
                </div>

                <!-- Child Cards -->
                <Link
                    v-for="(child, index) in children"
                    :key="child.id"
                    :href="`/parent/children/${child.id}/attendance`"
                    @click="handleClick"
                    class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900 rounded-xl"
                >
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.1 + index * 0.05 }"
                        :whileHover="{ scale: 1.01, x: 2 }"
                        :whileTap="{ scale: 0.98 }"
                        class="p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-xl border border-slate-100 dark:border-zinc-700 hover:border-emerald-200 dark:hover:border-emerald-800/50 transition-colors"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white">
                                    {{ child.nama_panggilan || child.nama_lengkap }}
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ child.kelas?.nama_lengkap || 'Kelas belum diset' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="text-right">
                                    <span
                                        :class="[
                                            'text-2xl font-bold tabular-nums',
                                            getAttendanceColor(child.attendance_summary.percentage),
                                        ]"
                                    >
                                        {{ child.attendance_summary.percentage }}%
                                    </span>
                                    <span 
                                        :class="['block text-[10px] font-semibold px-2 py-0.5 rounded mt-0.5', getStatusBgColor(child.attendance_summary.percentage), getAttendanceColor(child.attendance_summary.percentage)]"
                                    >
                                        {{ getStatusLabel(child.attendance_summary.percentage) }}
                                    </span>
                                </div>
                                <ChevronRight
                                    :size="20"
                                    class="text-slate-400 dark:text-slate-500"
                                />
                            </div>
                        </div>

                        <!-- Progress Bar dengan ARIA -->
                        <div 
                            class="h-2 bg-slate-200 dark:bg-zinc-700 rounded-full overflow-hidden"
                            role="progressbar"
                            :aria-valuenow="child.attendance_summary.percentage"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            :aria-label="`Kehadiran ${child.nama_panggilan || child.nama_lengkap}: ${child.attendance_summary.percentage}%`"
                        >
                            <div
                                :class="[
                                    'h-full rounded-full transition-all duration-500',
                                    getProgressBgColor(child.attendance_summary.percentage),
                                ]"
                                :style="{
                                    width: `${child.attendance_summary.percentage}%`,
                                }"
                            />
                        </div>

                        <!-- Breakdown Stats -->
                        <div class="grid grid-cols-4 gap-3 mt-3 p-2 bg-white dark:bg-zinc-900/50 rounded-lg">
                            <div class="text-center">
                                <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400 mb-0.5">Hadir</p>
                                <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                                    {{ child.attendance_summary.hadir }}
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400 mb-0.5">Sakit</p>
                                <p class="text-sm font-bold text-amber-600 dark:text-amber-400 tabular-nums">
                                    {{ child.attendance_summary.sakit }}
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400 mb-0.5">Izin</p>
                                <p class="text-sm font-bold text-sky-600 dark:text-sky-400 tabular-nums">
                                    {{ child.attendance_summary.izin }}
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400 mb-0.5">Alpha</p>
                                <p class="text-sm font-bold text-red-600 dark:text-red-400 tabular-nums">
                                    {{ child.attendance_summary.alpha }}
                                </p>
                            </div>
                        </div>

                        <!-- Low Attendance Warning per Child -->
                        <div
                            v-if="child.attendance_summary.percentage < warningThreshold"
                            class="mt-3 pt-3 border-t border-red-200 dark:border-red-800/50"
                        >
                            <p class="text-xs text-red-600 dark:text-red-400 flex items-center gap-1.5 font-medium">
                                <AlertTriangle :size="12" />
                                Kehadiran di bawah batas minimal ({{ warningThreshold }}%)
                            </p>
                        </div>
                    </Motion>
                </Link>
            </div>

            <!-- Footer Link -->
            <div
                class="px-6 py-4 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50"
            >
                <Link
                    href="/parent/children"
                    @click="handleClick"
                    class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 flex items-center justify-center gap-1 transition-colors"
                >
                    Lihat Semua Data Anak
                    <ChevronRight :size="16" />
                </Link>
            </div>
        </div>
    </Motion>
</template>
