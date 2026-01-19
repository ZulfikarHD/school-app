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

/**
 * Status badge classes menggunakan design system colors
 * untuk visual consistency di seluruh aplikasi
 */
const getStatusBadgeClass = (status: string) => {
    const classes: Record<string, string> = {
        aktif: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
        mutasi: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200 dark:border-amber-800',
        do: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border-red-200 dark:border-red-800',
        lulus: 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400 border-sky-200 dark:border-sky-800',
    };
    return classes[status] || 'bg-slate-100 text-slate-600 border-slate-200';
};
</script>

<template>
    <Motion
        :whileTap="{ scale: 0.98 }"
    >
        <div 
            class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-zinc-800 cursor-pointer hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-700 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950"
            role="button"
            tabindex="0"
            :aria-label="`Lihat detail ${student.nama_lengkap}, status ${student.status}, NIS ${student.nis}`"
            @click="handleView"
            @keydown.enter="handleView"
            @keydown.space.prevent="handleView"
        >
            <div class="flex items-start gap-4 mb-5">
                <div class="w-16 h-16 rounded-2xl overflow-hidden bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-sm">
                    <img
                        v-if="student.foto"
                        :src="student.foto"
                        :alt="student.nama_lengkap"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    />
                    <User v-else class="w-8 h-8" aria-hidden="true" />
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 truncate mb-1">
                        {{ student.nama_lengkap }}
                    </h3>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span :class="getStatusBadgeClass(student.status)" class="px-2.5 py-1 rounded-lg text-xs font-semibold border">
                            {{ student.status.toUpperCase() }}
                        </span>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300">
                            {{ student.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center gap-3 text-sm">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-900/20 flex items-center justify-center">
                        <GraduationCap class="w-4 h-4 text-sky-600 dark:text-sky-400" aria-hidden="true" />
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Kelas</p>
                        <p class="font-semibold text-slate-900 dark:text-slate-100">
                            {{ student.kelas_id ? `Kelas ${student.kelas_id}` : 'Belum ditentukan' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <div class="w-8 h-8 rounded-lg bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center">
                        <Calendar class="w-4 h-4 text-violet-600 dark:text-violet-400" aria-hidden="true" />
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tahun Ajaran</p>
                        <p class="font-semibold text-slate-900 dark:text-slate-100">
                            {{ student.tahun_ajaran_masuk }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-5 border-t border-slate-100 dark:border-zinc-800">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 dark:text-slate-400">NIS</span>
                    <span class="font-mono font-semibold text-slate-900 dark:text-slate-100">{{ student.nis }}</span>
                </div>
            </div>
        </div>
    </Motion>
</template>
