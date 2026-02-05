<script setup lang="ts">
/**
 * Teachers Edit Page - Halaman edit data guru
 * dengan form pre-filled dan tracking perubahan
 */
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Save, UserCog } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import TeacherForm from '@/components/features/teachers/TeacherForm.vue';
import { index as teachersIndex, update as updateTeacher } from '@/routes/admin/teachers';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';

interface Teacher {
    id: number;
    nip: string | null;
    nik: string;
    nama_lengkap: string;
    tempat_lahir: string | null;
    tanggal_lahir: string | null;
    jenis_kelamin: 'L' | 'P';
    alamat: string | null;
    no_hp: string | null;
    email: string;
    foto: string | null;
    status_kepegawaian: string;
    tanggal_mulai_kerja: string | null;
    tanggal_berakhir_kontrak: string | null;
    kualifikasi_pendidikan: string | null;
    is_active: boolean;
    subjects: Array<{ id: number; kode_mapel: string; nama_mapel: string; pivot?: { is_primary: boolean } }>;
}

interface StatusOption {
    value: string;
    label: string;
}

interface Props {
    teacher: Teacher;
    subjects: Array<{ id: number; kode_mapel: string; nama_mapel: string }>;
    statusOptions: StatusOption[];
    currentAcademicYear: string;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

// Extract subject IDs from teacher's subjects
const teacherSubjectIds = computed(() =>
    props.teacher.subjects?.map(s => s.id) || []
);

const form = useForm({
    // Biodata
    nik: props.teacher.nik || '',
    nama_lengkap: props.teacher.nama_lengkap || '',
    tempat_lahir: props.teacher.tempat_lahir || '',
    tanggal_lahir: props.teacher.tanggal_lahir || '',
    jenis_kelamin: props.teacher.jenis_kelamin || '',
    alamat: props.teacher.alamat || '',
    no_hp: props.teacher.no_hp || '',
    email: props.teacher.email || '',
    foto: null as File | null,

    // Kepegawaian
    nip: props.teacher.nip || '',
    status_kepegawaian: props.teacher.status_kepegawaian || 'honorer',
    tanggal_mulai_kerja: props.teacher.tanggal_mulai_kerja || '',
    tanggal_berakhir_kontrak: props.teacher.tanggal_berakhir_kontrak || '',

    // Akademik
    kualifikasi_pendidikan: props.teacher.kualifikasi_pendidikan || '',
    subjects: teacherSubjectIds.value,
    tahun_ajaran: props.currentAcademicYear,
});

const submit = () => {
    haptics.medium();
    form.post(updateTeacher(props.teacher.id).url, {
        // Use POST with _method for file upload support
        forceFormData: true,
        onSuccess: () => {
            haptics.success();
            modal.success('Data guru berhasil diperbarui.');
        },
        onError: () => {
            haptics.heavy();
            modal.error('Gagal memperbarui data. Periksa kembali input Anda.');
        }
    });
};
</script>

<template>
    <AppLayout title="Edit Guru">
        <Head :title="`Edit Guru - ${teacher.nama_lengkap}`" />

        <div class="max-w-5xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <Motion :whileTap="{ scale: 0.95 }">
                            <Link
                                :href="teachersIndex().url"
                                @click="haptics.light()"
                                class="w-11 h-11 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 active:bg-slate-200 rounded-xl transition-colors shrink-0
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                            >
                                <ChevronLeft class="w-5 h-5" />
                            </Link>
                        </Motion>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                                    <UserCog class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                </div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 truncate">
                                    Edit Data Guru
                                </h1>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                {{ teacher.nama_lengkap }}
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <form @submit.prevent="submit" class="space-y-6">
                <TeacherForm
                    :form="form"
                    mode="edit"
                    :subjects="props.subjects"
                    :status-options="props.statusOptions"
                    :current-foto="teacher.foto"
                />

                <!-- Actions - Sticky bottom -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
                >
                    <div class="sticky bottom-0 z-10 -mx-4 sm:mx-0 px-4 sm:px-0 pb-6 pt-4">
                        <div class="bg-white/98 dark:bg-zinc-900/98 border border-slate-200 dark:border-zinc-800 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl backdrop-blur-sm">
                            <p class="text-sm text-slate-600 dark:text-slate-400 hidden sm:block">
                                <span v-if="form.isDirty" class="text-amber-600 dark:text-amber-400">
                                    Ada perubahan yang belum disimpan
                                </span>
                                <span v-else>
                                    Belum ada perubahan
                                </span>
                            </p>
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <Link
                                        :href="teachersIndex().url"
                                        @click="haptics.light()"
                                        class="flex-1 sm:flex-none px-5 py-3 min-h-[48px] text-slate-700 bg-slate-50 border border-slate-300 rounded-xl hover:bg-slate-100 dark:bg-zinc-800 dark:border-zinc-700 dark:text-slate-300 dark:hover:bg-zinc-700 transition-colors font-medium text-center flex items-center justify-center
                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500"
                                    >
                                        Batal
                                    </Link>
                                </Motion>
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        type="submit"
                                        :disabled="form.processing || !form.isDirty"
                                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-3 min-h-[48px] bg-blue-500 text-white rounded-xl hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-lg shadow-blue-500/25 font-semibold
                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                    >
                                        <Save class="w-5 h-5" />
                                        <span v-if="form.processing">Menyimpan...</span>
                                        <span v-else>Simpan Perubahan</span>
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </div>
                </Motion>
            </form>
        </div>
    </AppLayout>
</template>
