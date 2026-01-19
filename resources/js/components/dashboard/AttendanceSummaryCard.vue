<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Users, AlertCircle, TrendingUp } from 'lucide-vue-next';
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

const attendanceColor = computed(() => {
    if (props.todayAttendance.percentage >= 95) return 'text-green-600 dark:text-green-400';
    if (props.todayAttendance.percentage >= 85) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
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
        :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
        :whileHover="{ y: -2, scale: 1.01 }"
        class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden cursor-pointer"
        @click="viewDetails"
    >
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-xl">
                        <Users :size="24" class="text-blue-600 dark:text-blue-300" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Kehadiran Hari Ini
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) }}
                        </p>
                    </div>
                </div>
                <TrendingUp :size="20" class="text-gray-400" />
            </div>

            <!-- Main Stats -->
            <div class="mb-4">
                <div class="flex items-baseline gap-2">
                    <span :class="['text-4xl font-bold', attendanceColor]">
                        {{ todayAttendance.percentage }}%
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        tingkat kehadiran
                    </span>
                </div>
            </div>

            <!-- Breakdown -->
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Hadir</p>
                    <p class="text-lg font-semibold text-green-600 dark:text-green-400">
                        {{ todayAttendance.present }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Terlambat</p>
                    <p class="text-lg font-semibold text-yellow-600 dark:text-yellow-400">
                        {{ todayAttendance.late }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tidak Hadir</p>
                    <p class="text-lg font-semibold text-red-600 dark:text-red-400">
                        {{ todayAttendance.absent }}
                    </p>
                </div>
            </div>

            <!-- Warning for unrecorded classes -->
            <div v-if="hasUnrecorded" class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                <div class="flex items-start gap-2">
                    <AlertCircle :size="16" class="text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                    <div class="flex-1">
                        <p class="text-sm font-medium text-red-800 dark:text-red-300">
                            {{ classesNotRecorded.length }} kelas belum input absensi
                        </p>
                        <div class="mt-1 space-y-1">
                            <p
                                v-for="cls in classesNotRecorded.slice(0, 3)"
                                :key="cls.id"
                                class="text-xs text-red-700 dark:text-red-400"
                            >
                                • {{ cls.nama_lengkap }}
                            </p>
                            <p v-if="classesNotRecorded.length > 3" class="text-xs text-red-700 dark:text-red-400">
                                dan {{ classesNotRecorded.length - 3 }} lainnya...
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
