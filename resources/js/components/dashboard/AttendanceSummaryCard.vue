<script setup lang="ts">
/**
 * AttendanceSummaryCard - Widget ringkasan kehadiran siswa hari ini
 * untuk Admin Dashboard dengan visual indicators dan warning untuk kelas
 * yang belum input absensi. Clickable card untuk navigasi ke detail.
 */
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Users, AlertCircle, ChevronRight } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';

interface Props {
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
    detailsUrl?: string;
}

const props = withDefaults(defineProps<Props>(), {
    detailsUrl: '/admin/attendance/students',
});

const haptics = useHaptics();

const hasUnrecorded = computed(() => props.classesNotRecorded.length > 0);

/**
 * Warna berdasarkan persentase kehadiran menggunakan design system colors
 */
const attendanceColor = computed(() => {
    if (props.todayAttendance.percentage >= 95) return 'text-emerald-600 dark:text-emerald-400';
    if (props.todayAttendance.percentage >= 85) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
});

/**
 * Background color untuk percentage badge
 */
const attendanceBgColor = computed(() => {
    if (props.todayAttendance.percentage >= 95) return 'bg-emerald-50 dark:bg-emerald-950/30';
    if (props.todayAttendance.percentage >= 85) return 'bg-amber-50 dark:bg-amber-950/30';
    return 'bg-red-50 dark:bg-red-950/30';
});

const viewDetails = () => {
    haptics.light();
    router.visit(props.detailsUrl);
};

/**
 * Format tanggal hari ini dalam Bahasa Indonesia
 */
const formattedDate = computed(() => {
    return new Date().toLocaleDateString('id-ID', { 
        weekday: 'long', 
        day: 'numeric', 
        month: 'long' 
    });
});
</script>

<template>
    <Motion
        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
        :animate="{ opacity: 1, y: 0, scale: 1 }"
        :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
        :whileHover="{ y: -2 }"
        :whileTap="{ scale: 0.98 }"
        role="button"
        tabindex="0"
        :aria-label="`Kehadiran hari ini ${todayAttendance.percentage}%, klik untuk detail`"
        class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900 transition-shadow flex flex-col"
        @click="viewDetails"
        @keydown.enter="viewDetails"
        @keydown.space.prevent="viewDetails"
    >
        <div class="p-6 flex-1 flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-sky-100 dark:bg-sky-900/30 rounded-xl">
                        <Users :size="22" class="text-sky-600 dark:text-sky-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            Kehadiran Hari Ini
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ formattedDate }}
                        </p>
                    </div>
                </div>
                <ChevronRight :size="20" class="text-slate-400 dark:text-slate-500" />
            </div>

            <!-- Main Stats dengan Badge -->
            <div class="mb-5">
                <div class="flex items-center gap-3">
                    <span 
                        :class="['text-4xl font-bold tabular-nums', attendanceColor]"
                        :aria-label="`Persentase kehadiran ${todayAttendance.percentage} persen`"
                    >
                        {{ todayAttendance.percentage }}%
                    </span>
                    <span 
                        :class="['text-xs font-semibold px-2.5 py-1 rounded-lg', attendanceBgColor, attendanceColor]"
                    >
                        {{ todayAttendance.percentage >= 95 ? 'Sangat Baik' : todayAttendance.percentage >= 85 ? 'Baik' : 'Perlu Perhatian' }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    dari {{ todayAttendance.total_students }} total siswa
                </p>
            </div>

            <!-- Breakdown Stats -->
            <div class="grid grid-cols-3 gap-3 p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-xl mt-auto">
                <div class="text-center">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Hadir</p>
                    <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                        {{ todayAttendance.present }}
                    </p>
                </div>
                <div class="text-center border-x border-slate-200 dark:border-zinc-700">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Terlambat</p>
                    <p class="text-xl font-bold text-amber-600 dark:text-amber-400 tabular-nums">
                        {{ todayAttendance.late }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Tidak Hadir</p>
                    <p class="text-xl font-bold text-red-600 dark:text-red-400 tabular-nums">
                        {{ todayAttendance.absent }}
                    </p>
                </div>
            </div>

            <!-- Warning for unrecorded classes -->
            <Motion
                v-if="hasUnrecorded"
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ delay: 0.2 }"
                class="mt-4 p-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-xl"
            >
                <div class="flex items-start gap-2.5">
                    <div class="p-1 bg-red-100 dark:bg-red-900/30 rounded-lg shrink-0">
                        <AlertCircle :size="14" class="text-red-600 dark:text-red-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-red-800 dark:text-red-300">
                            {{ classesNotRecorded.length }} kelas belum input absensi
                        </p>
                        <div class="mt-1.5 space-y-1">
                            <p
                                v-for="cls in classesNotRecorded.slice(0, 3)"
                                :key="cls.id"
                                class="text-xs text-red-700 dark:text-red-400 truncate"
                            >
                                • {{ cls.nama_lengkap }}
                            </p>
                            <p v-if="classesNotRecorded.length > 3" class="text-xs text-red-600 dark:text-red-400 font-medium">
                                +{{ classesNotRecorded.length - 3 }} kelas lainnya
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>
        </div>
    </Motion>
</template>
