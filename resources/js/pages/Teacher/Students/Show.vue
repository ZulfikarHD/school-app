<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import StudentDetailTabs from '@/components/features/students/StudentDetailTabs.vue';
import { useHaptics } from '@/composables/useHaptics';
import { index as studentsIndex } from '@/routes/teacher/students';
import type { Student, Guardian, ClassHistory } from '@/types/student';

interface Props {
    student: Student & {
        guardians: Guardian[];
        primaryGuardian?: Guardian;
        classHistory?: ClassHistory[];
    };
    canEdit?: boolean;
    hidePayment?: boolean;
}

defineProps<Props>();
const haptics = useHaptics();
</script>

<template>
    <AppLayout title="Detail Siswa">
        <Head :title="`Detail - ${student.nama_lengkap}`" />

        <div class="max-w-7xl mx-auto space-y-6">
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <Motion :whileTap="{ scale: 0.97 }">
                                <Link
                                    :href="studentsIndex().url"
                                    @click="haptics.light()"
                                    class="w-11 h-11 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 active:bg-slate-200 rounded-xl transition-colors"
                                >
                                    <ChevronLeft class="w-5 h-5" />
                                </Link>
                            </Motion>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Detail Siswa
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Informasi lengkap siswa di kelas Anda
                                </p>
                            </div>
                        </div>
                        
                        <!-- Read-only badge for Teacher -->
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-zinc-800 rounded-lg">
                            <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Mode Hanya Lihat</span>
                        </div>
                    </div>
                </div>
            </Motion>

            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <StudentDetailTabs 
                    :student="student" 
                    :read-only="true"
                    :show-payment="false"
                />
            </Motion>
        </div>
    </AppLayout>
</template>
