<script setup lang="ts">
/**
 * Teachers Index Page - Halaman daftar guru
 * dengan fitur search, filter, dan pagination
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Users } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import TeacherTable from '@/components/features/teachers/TeacherTable.vue';
import { index as teachersIndex, create as createTeacher, edit as editTeacher, toggleStatus } from '@/routes/admin/teachers';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';

interface Teacher {
    id: number;
    nip: string | null;
    nik: string;
    nama_lengkap: string;
    jenis_kelamin: 'L' | 'P';
    email: string;
    no_hp: string | null;
    foto: string | null;
    foto_url: string;
    status_kepegawaian: string;
    is_active: boolean;
    subjects: Array<{ id: number; kode_mapel: string; nama_mapel: string; is_primary: boolean }>;
}

interface StatusOption {
    value: string;
    label: string;
}

interface Props {
    teachers: {
        data: Teacher[];
        links: any[];
        meta?: any;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search?: string;
        status_kepegawaian?: string;
        is_active?: string;
    };
    subjects: Array<{ id: number; kode_mapel: string; nama_mapel: string }>;
    statusOptions: StatusOption[];
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

/**
 * Handle navigasi ke halaman edit guru
 */
const handleEdit = (teacher: Teacher) => {
    haptics.light();
    router.visit(editTeacher(teacher.id).url);
};

/**
 * Handle toggle status aktif/nonaktif guru
 * dengan konfirmasi dialog
 */
const handleToggleStatus = async (teacher: Teacher) => {
    const action = teacher.is_active ? 'menonaktifkan' : 'mengaktifkan';
    const escapeHtml = (text: string) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    const confirmed = await modal.dialog({
        type: teacher.is_active ? 'warning' : 'info',
        icon: teacher.is_active ? 'warning' : 'info',
        title: teacher.is_active ? 'Nonaktifkan Guru' : 'Aktifkan Guru',
        message: `Apakah Anda yakin ingin ${action} <b>${escapeHtml(teacher.nama_lengkap)}</b>?`,
        confirmText: teacher.is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan',
        cancelText: 'Batal',
        showCancel: true,
        allowHtml: true
    });

    if (confirmed) {
        haptics.medium();
        router.patch(toggleStatus(teacher.id).url, {}, {
            preserveScroll: true,
            onSuccess: () => {
                haptics.success();
                modal.success(`Guru berhasil di${action.replace('kan', '')}.`);
            },
            onError: () => {
                haptics.heavy();
                modal.error(`Gagal ${action} guru.`);
            }
        });
    }
};
</script>

<template>
    <AppLayout title="Manajemen Guru">
        <Head title="Manajemen Guru" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <!-- Left Side: Title -->
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-blue-400 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/25 shrink-0">
                                <Users class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">Data Guru</h1>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Kelola data guru dan status kepegawaian</p>
                            </div>
                        </div>

                        <!-- Right Side: Actions -->
                        <div class="flex items-center gap-2.5">
                            <!-- Add Button - Primary CTA -->
                            <Motion :whileTap="{ scale: 0.97 }">
                                <Link
                                    :href="createTeacher().url"
                                    @click="haptics.light()"
                                    class="group flex items-center gap-2 px-5 py-2.5 min-h-[44px] bg-linear-to-r from-blue-500 to-indigo-500 text-white rounded-xl hover:from-blue-600 hover:to-indigo-600 transition-all duration-200 shadow-lg shadow-blue-500/30
                                           focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                >
                                    <Plus class="w-5 h-5 transition-transform group-hover:rotate-90 duration-200" />
                                    <span class="font-semibold hidden sm:inline">Tambah Guru</span>
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
                <TeacherTable
                    :teachers="teachers"
                    :filters="filters"
                    :status-options="statusOptions"
                    :filter-url="teachersIndex().url"
                    @edit="handleEdit"
                    @toggle-status="handleToggleStatus"
                />
            </Motion>
        </div>
    </AppLayout>
</template>
