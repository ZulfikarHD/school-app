<script setup lang="ts">
/**
 * Teaching Schedules Index Page - Halaman daftar jadwal mengajar
 * dengan fitur search, filter, pagination, dan navigasi ke matrix views
 */
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Calendar, Grid3X3, Users, GraduationCap, Copy, FileDown } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import ScheduleTable from '@/components/features/schedules/ScheduleTable.vue';
import CopySemesterModal from '@/components/features/schedules/CopySemesterModal.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import {
    index as schedulesIndex,
    create as createSchedule,
    edit as editSchedule,
    destroy as deleteSchedule
} from '@/routes/admin/teachers/schedules';

interface Schedule {
    id: number;
    teacher_id: number;
    subject_id: number;
    class_id: number;
    academic_year_id: number;
    hari: { value: string; label?: string };
    jam_mulai: string;
    jam_selesai: string;
    ruangan: string | null;
    is_active: boolean;
    time_range: string;
    full_class_name: string;
    teacher: {
        id: number;
        nama_lengkap: string;
    };
    subject: {
        id: number;
        kode_mapel: string;
        nama_mapel: string;
    };
    school_class: {
        id: number;
        tingkat: number;
        nama: string;
    };
    academic_year: {
        id: number;
        name: string;
    };
}

interface Teacher {
    id: number;
    nama_lengkap: string;
}

interface SchoolClass {
    id: number;
    tingkat: number;
    nama: string;
}

interface AcademicYear {
    id: number;
    name: string;
    is_active: boolean;
}

interface HariOption {
    value: string;
    label: string;
}

interface Props {
    schedules: {
        data: Schedule[];
        links: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        search?: string;
        teacher_id?: string;
        class_id?: string;
        hari?: string;
        academic_year_id?: string;
        is_active?: string;
    };
    teachers: Teacher[];
    classes: SchoolClass[];
    academicYears: AcademicYear[];
    hariOptions: HariOption[];
    currentAcademicYearId: number | null;
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

// Copy semester modal state
const showCopySemesterModal = ref(false);
const copySemesterModalRef = ref<InstanceType<typeof CopySemesterModal> | null>(null);

/**
 * Handle navigasi ke halaman edit jadwal
 */
const handleEdit = (schedule: Schedule) => {
    haptics.light();
    router.visit(editSchedule(schedule.id).url);
};

/**
 * Handle hapus jadwal dengan konfirmasi dialog
 */
const handleDelete = async (schedule: Schedule) => {
    const escapeHtml = (text: string) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    const confirmed = await modal.dialog({
        type: 'warning',
        icon: 'warning',
        title: 'Hapus Jadwal',
        message: `Apakah Anda yakin ingin menghapus jadwal <b>${escapeHtml(schedule.subject?.nama_mapel || '')}</b> untuk <b>${escapeHtml(schedule.teacher?.nama_lengkap || '')}</b>?`,
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal',
        showCancel: true,
        allowHtml: true
    });

    if (confirmed) {
        haptics.medium();
        router.delete(deleteSchedule(schedule.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                haptics.success();
                modal.success('Jadwal berhasil dihapus.');
            },
            onError: () => {
                haptics.heavy();
                modal.error('Gagal menghapus jadwal.');
            }
        });
    }
};

/**
 * Handle copy semester dengan modal
 */
const handleCopySemester = () => {
    if (props.academicYears.length < 2) {
        modal.error('Minimal harus ada 2 tahun ajaran untuk menyalin jadwal.');
        return;
    }

    haptics.light();
    copySemesterModalRef.value?.resetForm();
    showCopySemesterModal.value = true;
};

/**
 * Handle close copy semester modal
 */
const handleCloseCopySemesterModal = () => {
    showCopySemesterModal.value = false;
};
</script>

<template>
    <AppLayout title="Jadwal Mengajar">
        <Head title="Jadwal Mengajar" />

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
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-emerald-400 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/25 shrink-0">
                                <Calendar class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">Jadwal Mengajar</h1>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Kelola jadwal mengajar guru</p>
                            </div>
                        </div>

                        <!-- Right Side: Actions -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <!-- Matrix View Buttons -->
                            <div class="hidden sm:flex items-center gap-2 mr-2">
                                <Link
                                    v-if="teachers.length > 0"
                                    :href="`/admin/teachers/schedules/by-teacher/${teachers[0].id}`"
                                    @click="haptics.light()"
                                    class="flex items-center gap-1.5 px-3 py-2 text-sm text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded-lg transition-colors"
                                >
                                    <Users class="w-4 h-4" />
                                    <span>Per Guru</span>
                                </Link>
                                <Link
                                    v-if="classes.length > 0"
                                    :href="`/admin/teachers/schedules/by-class/${classes[0].id}`"
                                    @click="haptics.light()"
                                    class="flex items-center gap-1.5 px-3 py-2 text-sm text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded-lg transition-colors"
                                >
                                    <GraduationCap class="w-4 h-4" />
                                    <span>Per Kelas</span>
                                </Link>
                            </div>

                            <!-- Copy Semester Button -->
                            <Motion :whileTap="{ scale: 0.97 }">
                                <button
                                    @click="handleCopySemester"
                                    class="flex items-center gap-1.5 px-3 py-2.5 text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded-xl transition-colors"
                                >
                                    <Copy class="w-4 h-4" />
                                    <span class="hidden sm:inline">Salin Semester</span>
                                </button>
                            </Motion>

                            <!-- Add Button - Primary CTA -->
                            <Motion :whileTap="{ scale: 0.97 }">
                                <Link
                                    :href="createSchedule().url"
                                    @click="haptics.light()"
                                    class="group flex items-center gap-2 px-5 py-2.5 min-h-[44px] bg-linear-to-r from-emerald-500 to-teal-500 text-white rounded-xl hover:from-emerald-600 hover:to-teal-600 transition-all duration-200 shadow-lg shadow-emerald-500/30
                                           focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                >
                                    <Plus class="w-5 h-5 transition-transform group-hover:rotate-90 duration-200" />
                                    <span class="font-semibold hidden sm:inline">Tambah Jadwal</span>
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
                <ScheduleTable
                    :schedules="schedules"
                    :filters="filters"
                    :teachers="teachers"
                    :classes="classes"
                    :academic-years="academicYears"
                    :hari-options="hariOptions"
                    :filter-url="schedulesIndex().url"
                    @edit="handleEdit"
                    @delete="handleDelete"
                />
            </Motion>
        </div>

        <!-- Copy Semester Modal -->
        <CopySemesterModal
            ref="copySemesterModalRef"
            :show="showCopySemesterModal"
            :academic-years="academicYears"
            :current-academic-year-id="currentAcademicYearId"
            @close="handleCloseCopySemesterModal"
        />
    </AppLayout>
</template>
