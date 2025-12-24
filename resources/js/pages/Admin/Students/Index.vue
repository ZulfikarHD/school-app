<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Download } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import StudentTable from '@/components/features/students/StudentTable.vue';
import { create as createStudent, edit as editStudent, show as showStudent, destroy as deleteStudent } from '@/routes/admin/students';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import type { Student } from '@/types/student';

interface Props {
    students: any; // Pagination type
    filters: any;
}

defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

// Mock data for filters since backend doesn't provide them yet (Classes module pending)
const classes = [
    { id: 1, nama: 'Kelas 1' },
    { id: 2, nama: 'Kelas 2' },
    { id: 3, nama: 'Kelas 3' },
    { id: 4, nama: 'Kelas 4' },
    { id: 5, nama: 'Kelas 5' },
    { id: 6, nama: 'Kelas 6' },
];

const academicYears = [
    '2023/2024',
    '2024/2025',
    '2025/2026',
];

const handleEdit = (student: Student) => {
    router.visit(editStudent({ student: student.id }).url);
};

const handleView = (student: Student) => {
    router.visit(showStudent({ student: student.id }).url);
};

const handleDelete = async (student: Student) => {
    // Escape HTML entities untuk keamanan sebelum inject ke message
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
        allowHtml: true // Explicitly enable HTML rendering dengan sanitization
    });

    if (confirmed) {
        haptics.heavy();
        router.delete(deleteStudent({ student: id }).url, {
            preserveScroll: true,
            onSuccess: () => {
                modal.success('Siswa berhasil dihapus');
            }
        });
    }
};

const handleUpdateStatus = (student: Student) => {
    // Implement Status Modal here or navigate to show page tab
    // For now redirect to show page with status tab active intent (can be done via query param if supported)
    // Or just simple navigation
    router.visit(showStudent({ student: student.id }).url);
};

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
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100 mb-1">Data Siswa</h1>
                            <p class="text-slate-600 dark:text-slate-400 text-sm">Kelola data siswa, orang tua, dan riwayat akademik</p>
                        </div>

                        <div class="flex items-center gap-2">
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
                :classes="classes"
                :academic-years="academicYears"
                @edit="handleEdit"
                @view="handleView"
                @delete="handleDelete"
                @update-status="handleUpdateStatus"
            />
            </Motion>
        </div>
    </AppLayout>
</template>

