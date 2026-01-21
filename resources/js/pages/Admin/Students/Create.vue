<script setup lang="ts">
/**
 * Students Create Page - Halaman pembuatan siswa baru
 * dengan form lengkap untuk biodata, alamat, akademik, dan data orang tua/wali
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Save, UserPlus } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import StudentForm from '@/components/features/students/StudentForm.vue';
import { index as studentsIndex, store as storeStudent } from '@/routes/admin/students';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';

interface Props {
    classes: Array<{ id: number; nama: string }>;
    currentAcademicYear: string;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

const form = useForm({
    // Biodata
    nama_lengkap: '',
    nama_panggilan: '',
    nik: '',
    nisn: '',
    jenis_kelamin: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    agama: '',
    anak_ke: 1,
    jumlah_saudara: 0,
    status_keluarga: 'Anak Kandung',
    foto: null as File | null,

    // Alamat
    alamat: '',
    rt_rw: '',
    kelurahan: '',
    kecamatan: '',
    kota: '',
    provinsi: '',
    kode_pos: '',
    no_hp: '',
    email: '',

    // Akademik
    kelas_id: '',
    tahun_ajaran_masuk: props.currentAcademicYear,
    tanggal_masuk: new Date().toISOString().split('T')[0],

    // Data Ayah
    ayah: {
        nama_lengkap: '',
        nik: '',
        pekerjaan: '',
        pendidikan: '',
        penghasilan: '',
        no_hp: '',
        email: '',
        alamat: '', // Optional, usually same as student
        is_primary_contact: true
    },

    // Data Ibu
    ibu: {
        nama_lengkap: '',
        nik: '',
        pekerjaan: '',
        pendidikan: '',
        penghasilan: '',
        no_hp: '',
        email: '',
        alamat: '',
        is_primary_contact: false
    },

    // Data Wali
    wali: {
        nama_lengkap: '',
        nik: '',
        pekerjaan: '',
        pendidikan: '',
        penghasilan: '',
        no_hp: '',
        email: '',
        alamat: '',
        is_primary_contact: false
    }
});

const submit = () => {
    haptics.medium();
    form.post(storeStudent().url, {
        onSuccess: () => {
            haptics.success();
            modal.success('Data siswa berhasil disimpan.');
        },
        onError: () => {
            haptics.heavy();
            modal.error('Gagal menyimpan data. Periksa kembali input Anda.');
        }
    });
};
</script>

<template>
    <AppLayout title="Tambah Siswa">
        <Head title="Tambah Siswa Baru" />

        <div class="max-w-6xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header - Enhanced dengan icon -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <Motion :whileTap="{ scale: 0.95 }">
                            <Link
                                :href="studentsIndex().url"
                                @click="haptics.light()"
                                class="w-11 h-11 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 active:bg-slate-200 rounded-xl transition-colors shrink-0
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                            >
                                <ChevronLeft class="w-5 h-5" />
                            </Link>
                        </Motion>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                    <UserPlus class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 truncate">
                                    Tambah Siswa Baru
                                </h1>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                Lengkapi formulir pendaftaran siswa dengan data yang valid
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <form @submit.prevent="submit" class="space-y-6">
                <StudentForm
                    :form="form"
                    mode="create"
                    :classes="props.classes"
                />

                <!-- Actions - Sticky bottom dengan backdrop blur -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
                >
                    <div class="sticky bottom-0 z-10 -mx-4 sm:mx-0 px-4 sm:px-0 pb-6 pt-4">
                        <div class="bg-white/98 dark:bg-zinc-900/98 border border-slate-200 dark:border-zinc-800 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl backdrop-blur-sm">
                            <p class="text-sm text-slate-600 dark:text-slate-400 hidden sm:block">
                                Pastikan semua data sudah benar sebelum menyimpan
                            </p>
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <Link
                                        :href="studentsIndex().url"
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
                                        :disabled="form.processing"
                                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-3 min-h-[48px] bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-lg shadow-emerald-500/25 font-semibold
                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                    >
                                        <Save class="w-5 h-5" />
                                        <span v-if="form.processing">Menyimpan...</span>
                                        <span v-else>Simpan Data</span>
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

