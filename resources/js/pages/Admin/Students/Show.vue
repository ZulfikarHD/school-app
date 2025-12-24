<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronLeft, Edit, Trash2 } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import StudentDetailTabs from '@/components/features/students/StudentDetailTabs.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import { index as studentsIndex, edit as editStudent, destroy as deleteStudent } from '@/routes/admin/students';
import type { Student, Guardian, ClassHistory, StatusHistory } from '@/types/student';

interface Props {
    student: Student & {
        guardians: Guardian[];
        primaryGuardian?: Guardian;
        classHistory?: ClassHistory[];
        statusHistory?: StatusHistory[];
    };
}

const props = defineProps<Props>();
const modal = useModal();
const haptics = useHaptics();

const handleEdit = () => {
    haptics.light();
    router.visit(editStudent(props.student.id).url);
};

const handleDelete = async () => {
    haptics.medium();
    const confirmed = await modal.dialog({
        type: 'danger',
        icon: 'warning',
        title: 'Konfirmasi Hapus',
        message: `Apakah Anda yakin ingin menghapus siswa "${props.student.nama_lengkap}"?`,
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal',
        showCancel: true
    });

    if (confirmed) {
        router.delete(deleteStudent(props.student.id).url, {
            onSuccess: () => {
                haptics.success();
                router.visit(studentsIndex().url);
            }
        });
    }
};
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
                                    Informasi lengkap data siswa
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <Motion :whileTap="{ scale: 0.97 }" class="flex-1 sm:flex-none">
                                <button
                                    @click="handleEdit"
                                    class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2.5 min-h-[44px] bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 rounded-xl font-medium hover:bg-amber-100 dark:hover:bg-amber-900/30 active:bg-amber-200 transition-all border border-amber-200 dark:border-amber-800"
                                >
                                    <Edit class="w-4 h-4" />
                                    <span>Edit</span>
                                </button>
                            </Motion>
                            <Motion :whileTap="{ scale: 0.97 }" class="flex-1 sm:flex-none">
                                <button
                                    @click="handleDelete"
                                    class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2.5 min-h-[44px] bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl font-medium hover:bg-red-100 dark:hover:bg-red-900/30 active:bg-red-200 transition-all border border-red-200 dark:border-red-800"
                                >
                                    <Trash2 class="w-4 h-4" />
                                    <span>Hapus</span>
                                </button>
                            </Motion>
                        </div>
                    </div>
                </div>
            </Motion>

            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <StudentDetailTabs :student="student" />
            </Motion>
        </div>
    </AppLayout>
</template>
