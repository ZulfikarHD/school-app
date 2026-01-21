<script setup lang="ts">
/**
 * TeacherPresenceWidget - Widget status presensi guru hari ini
 * untuk Admin Dashboard dengan informasi guru terlambat dan belum clock in.
 * Clickable card untuk navigasi ke detail attendance guru.
 */
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Clock, AlertTriangle, ChevronRight } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';

interface Props {
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
    detailsUrl?: string;
}

const props = withDefaults(defineProps<Props>(), {
    detailsUrl: '/admin/attendance/teachers',
});

const haptics = useHaptics();

const presencePercentage = computed(() => {
    if (props.teacherPresence.total_teachers === 0) return 0;
    return Math.round((props.teacherPresence.clocked_in / props.teacherPresence.total_teachers) * 100);
});

/**
 * Warna berdasarkan persentase presensi menggunakan design system colors
 */
const presenceColor = computed(() => {
    if (presencePercentage.value >= 95) return 'text-emerald-600 dark:text-emerald-400';
    if (presencePercentage.value >= 85) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
});

/**
 * Background color untuk stats badge
 */
const presenceBgColor = computed(() => {
    if (presencePercentage.value >= 95) return 'bg-emerald-50 dark:bg-emerald-950/30';
    if (presencePercentage.value >= 85) return 'bg-amber-50 dark:bg-amber-950/30';
    return 'bg-red-50 dark:bg-red-950/30';
});

/**
 * Status text berdasarkan persentase
 */
const statusText = computed(() => {
    if (presencePercentage.value >= 95) return 'Sangat Baik';
    if (presencePercentage.value >= 85) return 'Baik';
    return 'Perlu Perhatian';
});

const viewDetails = () => {
    haptics.light();
    router.visit(props.detailsUrl);
};
</script>

<template>
    <Motion
        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
        :animate="{ opacity: 1, y: 0, scale: 1 }"
        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
        :whileHover="{ y: -2 }"
        :whileTap="{ scale: 0.98 }"
        role="button"
        tabindex="0"
        :aria-label="`Presensi guru ${presencePercentage}%, ${teacherPresence.clocked_in} dari ${teacherPresence.total_teachers} sudah clock in, klik untuk detail`"
        class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900 transition-shadow flex flex-col"
        @click="viewDetails"
        @keydown.enter="viewDetails"
        @keydown.space.prevent="viewDetails"
    >
        <div class="p-6 flex-1 flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-violet-100 dark:bg-violet-900/30 rounded-xl">
                        <Clock :size="22" class="text-violet-600 dark:text-violet-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            Presensi Guru
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Status clock in hari ini
                        </p>
                    </div>
                </div>
                <ChevronRight :size="20" class="text-slate-400 dark:text-slate-500" />
            </div>

            <!-- Main Stats dengan visual progress -->
            <div class="mb-5">
                <div class="flex items-center gap-3 mb-2">
                    <div class="flex items-baseline gap-1">
                        <span :class="['text-4xl font-bold tabular-nums', presenceColor]">
                            {{ teacherPresence.clocked_in }}
                        </span>
                        <span class="text-xl text-slate-400 dark:text-slate-500">/</span>
                        <span class="text-xl text-slate-500 dark:text-slate-400 tabular-nums">
                            {{ teacherPresence.total_teachers }}
                        </span>
                    </div>
                    <span 
                        :class="['text-xs font-semibold px-2.5 py-1 rounded-lg', presenceBgColor, presenceColor]"
                    >
                        {{ statusText }}
                    </span>
                </div>
                
                <!-- Progress bar visual -->
                <div 
                    class="h-2 bg-slate-100 dark:bg-zinc-800 rounded-full overflow-hidden"
                    role="progressbar"
                    :aria-valuenow="presencePercentage"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    :aria-label="`${presencePercentage}% guru sudah clock in`"
                >
                    <div 
                        class="h-full rounded-full transition-all duration-500"
                        :class="presencePercentage >= 95 ? 'bg-emerald-500' : presencePercentage >= 85 ? 'bg-amber-500' : 'bg-red-500'"
                        :style="{ width: `${presencePercentage}%` }"
                    />
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
                    {{ presencePercentage }}% guru sudah clock in
                </p>
            </div>

            <!-- Late Teachers -->
            <Motion
                v-if="teacherPresence.late_teachers.length > 0"
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ delay: 0.2 }"
                class="mb-3 p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50 rounded-xl"
            >
                <div class="flex items-start gap-2.5">
                    <div class="p-1 bg-amber-100 dark:bg-amber-900/30 rounded-lg shrink-0">
                        <AlertTriangle :size="14" class="text-amber-600 dark:text-amber-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                            {{ teacherPresence.late_teachers.length }} guru terlambat
                        </p>
                        <div class="mt-1.5 space-y-1">
                            <div
                                v-for="teacher in teacherPresence.late_teachers.slice(0, 3)"
                                :key="teacher.id"
                                class="text-xs text-amber-700 dark:text-amber-400 flex items-center justify-between gap-2"
                            >
                                <span class="truncate">• {{ teacher.name }}</span>
                                <span class="font-bold shrink-0 bg-amber-100 dark:bg-amber-900/30 px-1.5 py-0.5 rounded text-amber-700 dark:text-amber-300">+{{ teacher.late_minutes }}m</span>
                            </div>
                            <p v-if="teacherPresence.late_teachers.length > 3" class="text-xs text-amber-600 dark:text-amber-400 font-medium">
                                +{{ teacherPresence.late_teachers.length - 3 }} guru lainnya
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Absent Teachers -->
            <Motion
                v-if="teacherPresence.absent_teachers.length > 0"
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ delay: 0.25 }"
                class="p-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-xl"
            >
                <div class="flex items-start gap-2.5">
                    <div class="p-1 bg-red-100 dark:bg-red-900/30 rounded-lg shrink-0">
                        <AlertTriangle :size="14" class="text-red-600 dark:text-red-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-red-800 dark:text-red-300">
                            {{ teacherPresence.absent_teachers.length }} guru belum clock in
                        </p>
                        <div class="mt-1.5 space-y-1">
                            <p
                                v-for="teacher in teacherPresence.absent_teachers.slice(0, 3)"
                                :key="teacher.id"
                                class="text-xs text-red-700 dark:text-red-400 truncate"
                            >
                                • {{ teacher.name }}
                            </p>
                            <p v-if="teacherPresence.absent_teachers.length > 3" class="text-xs text-red-600 dark:text-red-400 font-medium">
                                +{{ teacherPresence.absent_teachers.length - 3 }} guru lainnya
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>
        </div>
    </Motion>
</template>
