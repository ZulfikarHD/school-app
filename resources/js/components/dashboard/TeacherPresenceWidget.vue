<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Clock, AlertTriangle, CheckCircle } from 'lucide-vue-next';
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
}

const props = defineProps<Props>();

const haptics = useHaptics();

const presencePercentage = computed(() => {
    if (props.teacherPresence.total_teachers === 0) return 0;
    return Math.round((props.teacherPresence.clocked_in / props.teacherPresence.total_teachers) * 100);
});

const presenceColor = computed(() => {
    if (presencePercentage.value >= 95) return 'text-green-600 dark:text-green-400';
    if (presencePercentage.value >= 85) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
});

const viewDetails = () => {
    haptics.light();
    router.visit('/admin/attendance/teachers');
};
</script>

<template>
    <Motion
        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
        :animate="{ opacity: 1, y: 0, scale: 1 }"
        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
        :whileHover="{ y: -2, scale: 1.01 }"
        class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden cursor-pointer"
        @click="viewDetails"
    >
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-xl">
                        <Clock :size="24" class="text-purple-600 dark:text-purple-300" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Presensi Guru
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Status clock in hari ini
                        </p>
                    </div>
                </div>
                <CheckCircle :size="20" class="text-gray-400" />
            </div>

            <!-- Main Stats -->
            <div class="mb-4">
                <div class="flex items-baseline gap-2">
                    <span :class="['text-4xl font-bold', presenceColor]">
                        {{ teacherPresence.clocked_in }}
                    </span>
                    <span class="text-2xl text-gray-400">/</span>
                    <span class="text-2xl text-gray-500 dark:text-gray-400">
                        {{ teacherPresence.total_teachers }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ presencePercentage }}% guru sudah clock in
                </p>
            </div>

            <!-- Late Teachers -->
            <div v-if="teacherPresence.late_teachers.length > 0" class="mb-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl">
                <div class="flex items-start gap-2">
                    <AlertTriangle :size="16" class="text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
                    <div class="flex-1">
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
                            {{ teacherPresence.late_teachers.length }} guru terlambat
                        </p>
                        <div class="mt-1 space-y-1">
                            <div
                                v-for="teacher in teacherPresence.late_teachers.slice(0, 3)"
                                :key="teacher.id"
                                class="text-xs text-yellow-700 dark:text-yellow-400 flex items-center justify-between"
                            >
                                <span>• {{ teacher.name }}</span>
                                <span class="font-medium">+{{ teacher.late_minutes }}m</span>
                            </div>
                            <p v-if="teacherPresence.late_teachers.length > 3" class="text-xs text-yellow-700 dark:text-yellow-400">
                                dan {{ teacherPresence.late_teachers.length - 3 }} lainnya...
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Absent Teachers -->
            <div v-if="teacherPresence.absent_teachers.length > 0" class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                <div class="flex items-start gap-2">
                    <AlertTriangle :size="16" class="text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                    <div class="flex-1">
                        <p class="text-sm font-medium text-red-800 dark:text-red-300">
                            {{ teacherPresence.absent_teachers.length }} guru belum clock in
                        </p>
                        <div class="mt-1 space-y-1">
                            <p
                                v-for="teacher in teacherPresence.absent_teachers.slice(0, 3)"
                                :key="teacher.id"
                                class="text-xs text-red-700 dark:text-red-400"
                            >
                                • {{ teacher.name }}
                            </p>
                            <p v-if="teacherPresence.absent_teachers.length > 3" class="text-xs text-red-700 dark:text-red-400">
                                dan {{ teacherPresence.absent_teachers.length - 3 }} lainnya...
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action hint -->
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-zinc-800">
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                    Klik untuk melihat detail →
                </p>
            </div>
        </div>
    </Motion>
</template>
