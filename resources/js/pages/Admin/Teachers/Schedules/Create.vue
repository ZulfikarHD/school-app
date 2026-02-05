<script setup lang="ts">
/**
 * Create Schedule Page - Halaman untuk menambahkan jadwal mengajar baru
 * dengan form validation dan real-time conflict detection
 */
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ChevronLeft, Save, X, CalendarDays } from 'lucide-vue-next';
import { store } from '@/routes/admin/teachers/schedules';
import AppLayout from '@/components/layouts/AppLayout.vue';
import ScheduleForm from '@/components/features/schedules/ScheduleForm.vue';
import { useHaptics } from '@/composables/useHaptics';

interface Teacher {
    id: number;
    nama_lengkap: string;
    subjects?: Array<{
        id: number;
        kode_mapel: string;
        nama_mapel: string;
        pivot?: { is_primary: boolean };
    }>;
}

interface Subject {
    id: number;
    kode_mapel: string;
    nama_mapel: string;
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
    teachers: Teacher[];
    subjects: Subject[];
    classes: SchoolClass[];
    academicYears: AcademicYear[];
    hariOptions: HariOption[];
    activeAcademicYearId?: number | null;
}

const props = defineProps<Props>();

const haptics = useHaptics();

// Form dengan default values
const form = useForm({
    teacher_id: '',
    subject_id: '',
    class_id: '',
    academic_year_id: props.activeAcademicYearId ?? '',
    hari: '',
    jam_mulai: '',
    jam_selesai: '',
    ruangan: '',
    is_active: true,
});

// Submit form
const submit = () => {
    haptics.impact('medium');
    form.post(store().url, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
        },
        onError: () => {
            haptics.error();
        },
    });
};

// Cancel
const cancel = () => {
    haptics.selection();
    router.visit('/admin/teachers/schedules');
};
</script>

<template>
    <AppLayout title="Tambah Jadwal Mengajar">
        <Head title="Tambah Jadwal Mengajar" />

        <div class="min-h-screen bg-slate-50 dark:bg-zinc-900">
            <!-- Header -->
            <header class="bg-white dark:bg-zinc-800 border-b border-slate-200 dark:border-zinc-700 sticky top-0 z-10">
                <div class="px-4 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <Link
                            href="/admin/teachers/schedules"
                            class="p-2 -ml-2 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors"
                        >
                            <ChevronLeft class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                        </Link>
                        <div>
                            <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
                                Tambah Jadwal Mengajar
                            </h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Buat jadwal baru untuk guru
                            </p>
                        </div>
                    </div>

                    <!-- Desktop Actions -->
                    <div class="hidden md:flex items-center gap-2">
                        <button
                            type="button"
                            @click="cancel"
                            class="px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-700 rounded-xl transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <Save class="w-4 h-4" />
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-4 pb-32 md:pb-8 max-w-4xl mx-auto">
                <form @submit.prevent="submit">
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-slate-200 dark:border-zinc-700 overflow-hidden">
                        <!-- Card Header -->
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800/50">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                                    <CalendarDays class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <h2 class="font-medium text-slate-900 dark:text-white">Informasi Jadwal</h2>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        Lengkapi data jadwal mengajar
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Content -->
                        <div class="p-6">
                            <ScheduleForm
                                :form="form"
                                :teachers="teachers"
                                :subjects="subjects"
                                :classes="classes"
                                :academic-years="academicYears"
                                :hari-options="hariOptions"
                                :active-academic-year-id="activeAcademicYearId"
                                mode="create"
                            />
                        </div>
                    </div>
                </form>
            </main>

            <!-- Mobile Bottom Actions -->
            <div class="fixed bottom-0 left-0 right-0 p-4 bg-white dark:bg-zinc-800 border-t border-slate-200 dark:border-zinc-700 md:hidden safe-area-bottom">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="cancel"
                        class="flex-1 px-4 py-3 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-slate-300 rounded-xl transition-colors flex items-center justify-center gap-2"
                    >
                        <X class="w-4 h-4" />
                        <span>Batal</span>
                    </button>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="flex-1 px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        <Save class="w-4 h-4" />
                        <span>{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
