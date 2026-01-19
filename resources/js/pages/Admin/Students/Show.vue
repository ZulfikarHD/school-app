<script setup lang="ts">
/**
 * Students Show Page - Halaman detail siswa
 * dengan tabs untuk biodata, orang tua/wali, riwayat kelas, dan status
 */
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronLeft, Edit, Trash2, ArrowRightLeft } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import StudentDetailTabs from '@/components/features/students/StudentDetailTabs.vue';
import AssignClassModal from '@/components/features/students/AssignClassModal.vue';
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
    classes?: Array<{ id: number; nama: string; nama_lengkap: string; tahun_ajaran: string }>;
}

const props = defineProps<Props>();
const modal = useModal();
const haptics = useHaptics();

const showAssignModal = ref(false);

// Fallback if classes not passed (TODO: ensure controller passes classes)
const classesList = computed(() => props.classes || []);

const handleEdit = () => {
    haptics.light();
    router.visit(editStudent(props.student.id).url);
};

const handleAssignClass = () => {
    haptics.light();
    showAssignModal.value = true;
};

const handleAssignSuccess = () => {
    router.reload();
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

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header - Enhanced dengan student info preview -->
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
                                    class="w-11 h-11 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 active:bg-slate-200 rounded-xl transition-colors shrink-0
                                           focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                                >
                                    <ChevronLeft class="w-5 h-5" />
                                </Link>
                            </Motion>
                            
                            <!-- Student Avatar & Info -->
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-linear-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-emerald-500/25 shrink-0">
                                    {{ student.nama_lengkap.charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0">
                                    <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-slate-100 truncate">
                                        {{ student.nama_lengkap }}
                                    </h1>
                                    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                                        <span class="font-mono">{{ student.nis }}</span>
                                        <span v-if="student.kelas" class="hidden sm:inline">•</span>
                                        <span v-if="student.kelas" class="hidden sm:inline">{{ student.kelas.nama_lengkap || student.kelas.nama }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons - Modern outlined style -->
                        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                            <Motion :whileTap="{ scale: 0.97 }" class="flex-1 sm:flex-none">
                                <button
                                    @click="handleAssignClass"
                                    class="group w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2.5 min-h-[44px] bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded-xl font-medium hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-all duration-200 border border-emerald-200/80 dark:border-emerald-800/50 hover:border-emerald-300 dark:hover:border-emerald-700
                                           focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                                >
                                    <ArrowRightLeft class="w-4 h-4 transition-transform group-hover:rotate-180 duration-300" />
                                    <span>Pindah Kelas</span>
                                </button>
                            </Motion>

                            <Motion :whileTap="{ scale: 0.97 }" class="flex-1 sm:flex-none">
                                <button
                                    @click="handleEdit"
                                    class="group w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2.5 min-h-[44px] bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-xl font-medium hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-all duration-200 border border-amber-200/80 dark:border-amber-800/50 hover:border-amber-300 dark:hover:border-amber-700
                                           focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
                                >
                                    <Edit class="w-4 h-4 transition-transform group-hover:rotate-12 duration-200" />
                                    <span>Edit</span>
                                </button>
                            </Motion>

                            <Motion :whileTap="{ scale: 0.97 }" class="flex-1 sm:flex-none">
                                <button
                                    @click="handleDelete"
                                    class="group w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2.5 min-h-[44px] bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 rounded-xl font-medium hover:bg-red-100 dark:hover:bg-red-900/40 transition-all duration-200 border border-red-200/80 dark:border-red-800/50 hover:border-red-300 dark:hover:border-red-700
                                           focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500"
                                >
                                    <Trash2 class="w-4 h-4 transition-transform group-hover:scale-110 duration-200" />
                                    <span>Hapus</span>
                                </button>
                            </Motion>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Student Detail Tabs -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <StudentDetailTabs :student="student" />
            </Motion>
        </div>

        <!-- Assign Class Modal -->
        <AssignClassModal
            :show="showAssignModal"
            :students="[student]"
            :classes="classesList"
            :current-class-id="student.kelas_id"
            @close="showAssignModal = false"
            @success="handleAssignSuccess"
        />
    </AppLayout>
</template>
