<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Download, ArrowRightLeft, X } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import StudentTable from '@/components/features/students/StudentTable.vue';
import AssignClassModal from '@/components/features/students/AssignClassModal.vue';
import { create as createStudent, edit as editStudent, show as showStudent, destroy as deleteStudent } from '@/routes/admin/students';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import type { Student } from '@/types/student';

interface Props {
    students: any; // Pagination type
    filters: any;
    classes: Array<{ id: number; nama: string; nama_lengkap: string; tahun_ajaran: string }>;
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

// State
const selectedStudents = ref<Student[]>([]);
const showAssignModal = ref(false);
const tableRef = ref();

// Computed
const hasSelection = computed(() => selectedStudents.value.length > 0);

// Methods
const handleSelectionChange = (students: Student[]) => {
    selectedStudents.value = students;
};

const clearSelection = () => {
    haptics.light();
    selectedStudents.value = [];
    if (tableRef.value) {
        tableRef.value.resetSelection();
    }
};

const openAssignModal = () => {
    if (!hasSelection.value) return;
    haptics.light();
    showAssignModal.value = true;
};

const handleEdit = (student: Student) => {
    router.visit(editStudent(student.id).url);
};

const handleView = (student: Student) => {
    router.visit(showStudent(student.id).url);
};

const handleDelete = async (student: Student) => {
    // Escape HTML entities
    const escapeHtml = (text: string) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };
    
    const confirmed = await modal.dialog({
        type: 'danger',
        icon: 'warning',
        title: 'Hapus Siswa',
        message: `Apakah Anda yakin ingin menghapus data siswa <b>${escapeHtml(student.nama_lengkap)}</b>? Data yang dihapus dapat dikembalikan.`,
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal',
        showCancel: true,
        allowHtml: true
    });

    if (confirmed) {
        haptics.heavy();
        router.delete(deleteStudent(student.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                modal.success('Siswa berhasil dihapus');
            }
        });
    }
};

const handleUpdateStatus = (student: Student) => {
    router.visit(showStudent(student.id).url);
};

const handleAssignSuccess = () => {
    clearSelection();
    router.reload({ only: ['students'] });
};

// Mock mock classes if empty (fallback)
const classesList = computed(() => props.classes || []);

const academicYears = [
    '2023/2024',
    '2024/2025',
    '2025/2026',
];
</script>

<template>
    <AppLayout title="Manajemen Siswa">
        <Head title="Manajemen Siswa" />

        <div class="space-y-6">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-zinc-800 sticky top-[72px] z-20 transition-all duration-300">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <!-- Left Side: Title or Selection Info -->
                        <div v-if="hasSelection" class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4 duration-200">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <span class="font-bold">{{ selectedStudents.length }}</span>
                            </div>
                            <div>
                                <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Siswa Dipilih</h1>
                                <button @click="clearSelection" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 flex items-center gap-1">
                                    Batal Memilih <X class="w-3 h-3" />
                                </button>
                            </div>
                        </div>

                        <div v-else>
                            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100 mb-1">Data Siswa</h1>
                            <p class="text-slate-600 dark:text-slate-400 text-sm">Kelola data siswa, orang tua, dan riwayat akademik</p>
                        </div>

                        <!-- Right Side: Actions -->
                        <div class="flex items-center gap-2">
                            <template v-if="hasSelection">
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        @click="openAssignModal"
                                        class="flex items-center gap-2 px-4 py-2.5 min-h-[44px] bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 active:bg-emerald-700 transition-all shadow-lg shadow-emerald-500/25 animate-in zoom-in-95 duration-200"
                                    >
                                        <ArrowRightLeft class="w-5 h-5" />
                                        <span class="font-semibold">Pindah Kelas</span>
                                    </button>
                                </Motion>
                            </template>

                            <template v-else>
                                <!-- Export Button -->
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        @click="haptics.light()"
                                        class="w-11 h-11 flex items-center justify-center text-slate-700 dark:text-slate-300 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-50 dark:hover:bg-zinc-700 active:bg-slate-100 transition-colors shadow-sm"
                                        title="Export Excel"
                                    >
                                        <Download class="w-5 h-5" />
                                    </button>
                                </Motion>

                                <!-- Add Button -->
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <Link
                                        :href="createStudent().url"
                                        @click="haptics.light()"
                                        class="flex items-center gap-2 px-4 py-2.5 min-h-[44px] bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 active:bg-emerald-700 transition-all shadow-lg shadow-emerald-500/25"
                                    >
                                        <Plus class="w-5 h-5" />
                                        <span class="font-semibold hidden sm:inline">Tambah Siswa</span>
                                    </Link>
                                </Motion>
                            </template>
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
                    ref="tableRef"
                    :students="students"
                    :filters="filters"
                    :classes="classesList"
                    :academic-years="academicYears"
                    @edit="handleEdit"
                    @view="handleView"
                    @delete="handleDelete"
                    @update-status="handleUpdateStatus"
                    @selection-change="handleSelectionChange"
                />
            </Motion>
        </div>

        <!-- Assign Class Modal -->
        <AssignClassModal
            :show="showAssignModal"
            :students="selectedStudents"
            :classes="classesList"
            @close="showAssignModal = false"
            @success="handleAssignSuccess"
        />
    </AppLayout>
</template>
