<script setup lang="ts">
/**
 * Edit Schedule Page - Halaman untuk mengedit jadwal mengajar yang sudah ada
 * dengan form validation, real-time conflict detection, dan konfirmasi delete
 */
import { ref } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ChevronLeft, Save, X, CalendarDays, Trash2, AlertTriangle } from 'lucide-vue-next';
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

interface Schedule {
    id: number;
    teacher_id: number;
    subject_id: number;
    class_id: number;
    academic_year_id: number;
    hari: string;
    jam_mulai: string;
    jam_selesai: string;
    ruangan: string | null;
    is_active: boolean;
    teacher: Teacher;
    subject: Subject;
    school_class: SchoolClass;
    academic_year: AcademicYear;
}

interface Props {
    schedule: Schedule;
    teachers: Teacher[];
    subjects: Subject[];
    classes: SchoolClass[];
    academicYears: AcademicYear[];
    hariOptions: HariOption[];
}

const props = defineProps<Props>();

const haptics = useHaptics();

// Form dengan values dari schedule
const form = useForm({
    id: props.schedule.id,
    teacher_id: props.schedule.teacher_id,
    subject_id: props.schedule.subject_id,
    class_id: props.schedule.class_id,
    academic_year_id: props.schedule.academic_year_id,
    hari: props.schedule.hari,
    jam_mulai: props.schedule.jam_mulai,
    jam_selesai: props.schedule.jam_selesai,
    ruangan: props.schedule.ruangan ?? '',
    is_active: props.schedule.is_active,
});

// Delete confirmation state
const showDeleteModal = ref(false);
const isDeleting = ref(false);

// Submit form
const submit = () => {
    haptics.impact('medium');
    form.put(`/admin/teachers/schedules/${props.schedule.id}`, {
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

// Open delete modal
const openDeleteModal = () => {
    haptics.warning();
    showDeleteModal.value = true;
};

// Close delete modal
const closeDeleteModal = () => {
    haptics.selection();
    showDeleteModal.value = false;
};

// Confirm delete
const confirmDelete = () => {
    isDeleting.value = true;
    haptics.impact('heavy');

    router.delete(`/admin/teachers/schedules/${props.schedule.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            showDeleteModal.value = false;
        },
        onError: () => {
            haptics.error();
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

// Get schedule display info
const scheduleInfo = {
    teacher: props.schedule.teacher?.nama_lengkap ?? '-',
    subject: props.schedule.subject?.nama_mapel ?? '-',
    class: props.schedule.school_class
        ? `Kelas ${props.schedule.school_class.tingkat}${props.schedule.school_class.nama}`
        : '-',
    time: `${props.schedule.jam_mulai} - ${props.schedule.jam_selesai}`,
};
</script>

<template>
    <AppLayout title="Edit Jadwal Mengajar">
        <Head title="Edit Jadwal Mengajar" />

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
                                Edit Jadwal Mengajar
                            </h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ scheduleInfo.subject }} - {{ scheduleInfo.class }}
                            </p>
                        </div>
                    </div>

                    <!-- Desktop Actions -->
                    <div class="hidden md:flex items-center gap-2">
                        <button
                            type="button"
                            @click="openDeleteModal"
                            class="px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors flex items-center gap-2"
                        >
                            <Trash2 class="w-4 h-4" />
                            <span>Hapus</span>
                        </button>
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
                                        Edit data jadwal mengajar
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
                                mode="edit"
                            />

                            <!-- Active Status Toggle -->
                            <div class="mt-6 pt-6 border-t border-slate-200 dark:border-zinc-700">
                                <label class="flex items-center justify-between">
                                    <div>
                                        <span class="font-medium text-slate-900 dark:text-white">Status Aktif</span>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">
                                            Jadwal yang tidak aktif tidak akan ditampilkan di matrix
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        @click="form.is_active = !form.is_active"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        :class="form.is_active ? 'bg-blue-500' : 'bg-slate-200 dark:bg-zinc-700'"
                                        role="switch"
                                        :aria-checked="form.is_active"
                                    >
                                        <span
                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                            :class="form.is_active ? 'translate-x-5' : 'translate-x-0'"
                                        />
                                    </button>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Danger Zone (Mobile) -->
                    <div class="mt-6 md:hidden">
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-4">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-xl">
                                    <AlertTriangle class="w-5 h-5 text-red-600 dark:text-red-400" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-medium text-red-800 dark:text-red-300">Zona Berbahaya</h3>
                                    <p class="text-sm text-red-600 dark:text-red-400 mt-1">
                                        Menghapus jadwal tidak dapat dibatalkan.
                                    </p>
                                    <button
                                        type="button"
                                        @click="openDeleteModal"
                                        class="mt-3 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm transition-colors flex items-center gap-2"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                        <span>Hapus Jadwal</span>
                                    </button>
                                </div>
                            </div>
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

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-black/50 backdrop-blur-sm"
                    @click="closeDeleteModal"
                />

                <!-- Modal -->
                <div class="relative bg-white dark:bg-zinc-800 rounded-2xl shadow-xl max-w-md w-full overflow-hidden">
                    <div class="p-6">
                        <!-- Icon -->
                        <div class="mx-auto w-14 h-14 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mb-4">
                            <Trash2 class="w-7 h-7 text-red-600 dark:text-red-400" />
                        </div>

                        <!-- Title -->
                        <h3 class="text-xl font-semibold text-slate-900 dark:text-white text-center mb-2">
                            Hapus Jadwal?
                        </h3>

                        <!-- Description -->
                        <p class="text-slate-600 dark:text-slate-400 text-center mb-6">
                            Anda akan menghapus jadwal:
                        </p>

                        <!-- Schedule Info -->
                        <div class="bg-slate-50 dark:bg-zinc-700/50 rounded-xl p-4 mb-6 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Guru:</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ scheduleInfo.teacher }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Mapel:</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ scheduleInfo.subject }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Kelas:</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ scheduleInfo.class }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Waktu:</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ scheduleInfo.time }}</span>
                            </div>
                        </div>

                        <p class="text-sm text-red-600 dark:text-red-400 text-center mb-6">
                            Tindakan ini tidak dapat dibatalkan.
                        </p>

                        <!-- Actions -->
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="closeDeleteModal"
                                class="flex-1 px-4 py-3 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-slate-300 rounded-xl transition-colors hover:bg-slate-50 dark:hover:bg-zinc-700"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                @click="confirmDelete"
                                :disabled="isDeleting"
                                class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <Trash2 v-if="!isDeleting" class="w-4 h-4" />
                                <svg v-else class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ isDeleting ? 'Menghapus...' : 'Hapus' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
