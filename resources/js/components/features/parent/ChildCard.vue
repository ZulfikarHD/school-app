<script setup lang="ts">
import { User, GraduationCap, Calendar } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import type { Student } from '@/types/student';

interface Props {
    student: Student;
}

const props = defineProps<Props>();
const emit = defineEmits(['view']);
const haptics = useHaptics();

const handleView = () => {
    haptics.light();
    emit('view', props.student);
};

const getStatusBadgeClass = (status: string) => {
    const classes: Record<string, string> = {
        aktif: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-200',
        mutasi: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200',
        do: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border-red-200',
        lulus: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 border-purple-200',
    };
    return classes[status] || 'bg-gray-100 text-gray-600 border-gray-200';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Motion
        :whileTap="{ scale: 0.98 }"
        @click="handleView"
    >
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-zinc-800 cursor-pointer hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition-all">
            <div class="flex items-start gap-4 mb-5">
                <div class="w-16 h-16 rounded-2xl overflow-hidden bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white shadow-sm">
                    <img
                        v-if="student.foto"
                        :src="student.foto"
                        :alt="student.nama_lengkap"
                        class="w-full h-full object-cover"
                    />
                    <User v-else class="w-8 h-8" />
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 truncate mb-1">
                        {{ student.nama_lengkap }}
                    </h3>
                    <div class="flex items-center gap-2">
                        <span :class="getStatusBadgeClass(student.status)" class="px-2.5 py-1 rounded-lg text-xs font-semibold border">
                            {{ student.status.toUpperCase() }}
                        </span>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300">
                            {{ student.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center gap-3 text-sm">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center">
                        <GraduationCap class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Kelas</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ student.kelas_id ? `Kelas ${student.kelas_id}` : 'Belum ditentukan' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center">
                        <Calendar class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tahun Ajaran</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ student.tahun_ajaran_masuk }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-5 border-t border-gray-100 dark:border-zinc-800">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">NIS</span>
                    <span class="font-mono font-semibold text-gray-900 dark:text-gray-100">{{ student.nis }}</span>
                </div>
            </div>
        </div>
    </Motion>
</template>
