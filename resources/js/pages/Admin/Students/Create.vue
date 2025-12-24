<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Save } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import StudentForm from '@/components/features/students/StudentForm.vue';
import { index as studentsIndex, store as storeStudent } from '@/routes/admin/students';
import { useHaptics } from '@/composables/useHaptics';

const haptics = useHaptics();

// Mock classes
const classes = [
    { id: 1, nama: 'Kelas 1' },
    { id: 2, nama: 'Kelas 2' },
    { id: 3, nama: 'Kelas 3' },
    { id: 4, nama: 'Kelas 4' },
    { id: 5, nama: 'Kelas 5' },
    { id: 6, nama: 'Kelas 6' },
];

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
    tahun_ajaran_masuk: '2024/2025',
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
        },
        onError: () => {
            haptics.heavy();
        }
    });
};
</script>

<template>
    <AppLayout title="Tambah Siswa">
        <Head title="Tambah Siswa Baru" />

        <div class="max-w-6xl mx-auto space-y-8 px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-8 shadow-sm border border-gray-200 dark:border-zinc-800">
                    <div class="flex items-center gap-4">
                        <Motion :whileTap="{ scale: 0.95 }">
                            <Link
                                :href="studentsIndex().url"
                                @click="haptics.light()"
                                class="p-3 text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-xl transition-all duration-200"
                            >
                                <ChevronLeft class="w-6 h-6" />
                            </Link>
                        </Motion>
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Tambah Siswa Baru</h1>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1.5">Lengkapi formulir pendaftaran siswa dengan data yang valid dan lengkap</p>
                        </div>
                    </div>
                </div>
            </Motion>

            <form @submit.prevent="submit" class="space-y-8">
                <StudentForm
                    :form="form"
                    mode="create"
                    :classes="classes"
                />

                <!-- Actions - Fixed bottom with safe area -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
                >
                    <div class="sticky bottom-0 z-10 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 pb-6 pt-4 md:pb-6">
                        <div class="bg-white/98 dark:bg-zinc-900/98 border border-slate-200 dark:border-zinc-800 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl backdrop-blur-sm">
                            <p class="text-sm text-slate-600 dark:text-slate-400 hidden sm:block">
                                Pastikan semua data sudah benar sebelum menyimpan
                            </p>
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <Link
                                        :href="studentsIndex().url"
                                        @click="haptics.light()"
                                        class="flex-1 sm:flex-none px-5 py-3 min-h-[48px] text-slate-700 bg-slate-50 border border-slate-300 rounded-xl hover:bg-slate-100 dark:bg-zinc-800 dark:border-zinc-700 dark:text-slate-300 dark:hover:bg-zinc-700 transition-all duration-200 font-medium text-center flex items-center justify-center"
                                    >
                                        Batal
                                    </Link>
                                </Motion>
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-3 min-h-[48px] bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg shadow-emerald-500/25 font-semibold"
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

