<script setup lang="ts">
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Eye } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import StudentTable from '@/components/features/students/StudentTable.vue';
import { show as showStudent } from '@/routes/principal/students';
import { useHaptics } from '@/composables/useHaptics';
import type { Student } from '@/types/student';

interface Props {
    students: any; // Pagination type
    filters: any;
    classes: Array<{ id: number; nama: string; nama_lengkap: string; tahun_ajaran: string }>;
}

const props = defineProps<Props>();

const haptics = useHaptics();

// Computed
const classesList = computed(() => props.classes || []);

const academicYears = [
    '2023/2024',
    '2024/2025',
    '2025/2026',
];

// Methods - Principal hanya bisa view (read-only)
const handleView = (student: Student) => {
    haptics.light();
    router.visit(showStudent(student.id).url);
};
</script>

<template>
    <AppLayout title="Data Siswa">
        <Head title="Data Siswa" />

        <div class="space-y-6">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100 mb-1">Data Siswa</h1>
                            <p class="text-slate-600 dark:text-slate-400 text-sm">Lihat data siswa, orang tua, dan riwayat akademik</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-zinc-800 rounded-lg">
                                <Eye class="w-4 h-4 text-slate-500" />
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Mode Hanya Lihat</span>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Table -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <StudentTable
                    :students="students"
                    :filters="filters"
                    :classes="classesList"
                    :academic-years="academicYears"
                    :read-only="true"
                    :hide-selection="true"
                    @view="handleView"
                />
            </Motion>
        </div>
    </AppLayout>
</template>
