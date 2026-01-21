<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Download, ArrowRightLeft, X, GraduationCap } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import StudentTable from '@/components/features/students/StudentTable.vue';
import AssignClassModal from '@/components/features/students/AssignClassModal.vue';
import { index as studentsIndex, create as createStudent, edit as editStudent, show as showStudent, destroy as deleteStudent, exportMethod as exportStudents } from '@/routes/admin/students';
import { page as promotePage } from '@/routes/admin/students/promote';
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

/**
 * Handle export to Excel dengan current filters
 * untuk download file .xlsx dengan data siswa
 */
const handleExport = () => {
    haptics.light();
    
    // Build export URL with current filters
    const exportUrl = exportStudents({
        query: {
            search: props.filters.search || undefined,
            kelas_id: props.filters.kelas_id || undefined,
            status: props.filters.status || undefined,
            tahun_ajaran: props.filters.tahun_ajaran || undefined,
            jenis_kelamin: props.filters.jenis_kelamin || undefined,
        }
    }).url;
    
    // Open in new tab to trigger download
    window.open(exportUrl, '_blank');
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

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header - Enhanced dengan icon dan visual consistency -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <!-- Left Side: Title or Selection Info -->
                        <div v-if="hasSelection" class="flex items-center gap-3 animate-in fade-in slide-in-from-left-4 duration-200">
                            <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shadow-sm">
                                <span class="font-bold text-lg">{{ selectedStudents.length }}</span>
                            </div>
                            <div>
                                <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Siswa Dipilih</h1>
                                <button @click="clearSelection" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 flex items-center gap-1 transition-colors">
                                    Batal Memilih <X class="w-3 h-3" />
                                </button>
                            </div>
                        </div>

                        <div v-else class="flex items-center gap-3 sm:gap-4">
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-emerald-400 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/25 shrink-0">
                                <GraduationCap class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">Data Siswa</h1>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Kelola data siswa dan riwayat akademik</p>
                            </div>
                        </div>

                        <!-- Right Side: Actions -->
                        <div class="flex items-center gap-2.5">
                            <template v-if="hasSelection">
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        @click="openAssignModal"
                                        class="group flex items-center gap-2.5 px-5 py-2.5 min-h-[44px] bg-linear-to-r from-emerald-500 to-teal-500 text-white rounded-xl hover:from-emerald-600 hover:to-teal-600 active:from-emerald-700 active:to-teal-700 transition-all duration-200 shadow-lg shadow-emerald-500/30 animate-in zoom-in-95 duration-200"
                                    >
                                        <ArrowRightLeft class="w-5 h-5 transition-transform group-hover:rotate-180 duration-300" />
                                        <span class="font-semibold">Pindah Kelas</span>
                                    </button>
                                </Motion>
                            </template>

                            <template v-else>
                                <!-- Promote Button -->
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <Link
                                        :href="promotePage()"
                                        @click="haptics.light()"
                                        class="group flex items-center gap-2 px-4 py-2.5 min-h-[44px] bg-linear-to-r from-sky-500 to-blue-500 text-white rounded-xl hover:from-sky-600 hover:to-blue-600 transition-all duration-200 shadow-md shadow-sky-500/25
                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                        title="Naik Kelas"
                                    >
                                        <GraduationCap class="w-5 h-5 transition-transform group-hover:-translate-y-0.5 duration-200" />
                                        <span class="font-semibold hidden md:inline">Naik Kelas</span>
                                    </Link>
                                </Motion>

                                <!-- Export Button -->
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        @click="handleExport"
                                        class="group w-11 h-11 flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-50 dark:hover:bg-zinc-700 hover:border-slate-300 dark:hover:border-zinc-600 transition-all duration-200 shadow-sm
                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500"
                                        title="Export Excel"
                                    >
                                        <Download class="w-5 h-5 transition-transform group-hover:translate-y-0.5 duration-200" />
                                    </button>
                                </Motion>

                                <!-- Add Button - Primary CTA -->
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <Link
                                        :href="createStudent().url"
                                        @click="haptics.light()"
                                        class="group flex items-center gap-2 px-5 py-2.5 min-h-[44px] bg-linear-to-r from-emerald-500 to-teal-500 text-white rounded-xl hover:from-emerald-600 hover:to-teal-600 transition-all duration-200 shadow-lg shadow-emerald-500/30
                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                    >
                                        <Plus class="w-5 h-5 transition-transform group-hover:rotate-90 duration-200" />
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
                    :filter-url="studentsIndex().url"
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
