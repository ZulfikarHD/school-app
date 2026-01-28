<script setup lang="ts">
/**
 * PSB Landing Page - Halaman informasi pendaftaran siswa baru
 *
 * Page ini bertujuan untuk menampilkan informasi periode pendaftaran,
 * persyaratan dokumen, dan timeline pendaftaran dengan desain iOS-like
 */
import { Head, Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    Calendar,
    FileText,
    Clock,
    CheckCircle,
    AlertCircle,
    ChevronRight,
    GraduationCap,
    Users,
    Shield,
    Sparkles,
} from 'lucide-vue-next';
import { register as psbRegister, tracking as psbTracking } from '@/routes/psb';
import { useHaptics } from '@/composables/useHaptics';

interface Props {
    settings: {
        registration_open_date: string;
        registration_close_date: string;
        announcement_date: string;
        registration_fee: number;
        formatted_fee: string;
        quota_per_class: number;
        academic_year: string | null;
    } | null;
    isOpen: boolean;
    requiredDocuments: Record<string, string>;
}

const props = defineProps<Props>();
const haptics = useHaptics();

/**
 * Timeline data untuk menampilkan tahapan pendaftaran
 */
const timeline = [
    {
        icon: FileText,
        title: 'Pendaftaran Online',
        description: 'Isi formulir dan upload dokumen',
        date: props.settings?.registration_open_date ?? '-',
    },
    {
        icon: Shield,
        title: 'Verifikasi Dokumen',
        description: 'Tim kami memverifikasi kelengkapan dokumen',
        date: 'Setelah pendaftaran',
    },
    {
        icon: CheckCircle,
        title: 'Pengumuman',
        description: 'Hasil seleksi diumumkan',
        date: props.settings?.announcement_date ?? '-',
    },
    {
        icon: Users,
        title: 'Daftar Ulang',
        description: 'Konfirmasi dan pembayaran',
        date: '7 hari setelah pengumuman',
    },
];

/**
 * Features/keunggulan sekolah untuk ditampilkan
 */
const features = [
    {
        icon: GraduationCap,
        title: 'Kurikulum Terbaru',
        description: 'Menggunakan kurikulum merdeka dengan pendekatan pembelajaran yang menyenangkan',
    },
    {
        icon: Users,
        title: 'Guru Berpengalaman',
        description: 'Tenaga pengajar profesional dan berdedikasi tinggi',
    },
    {
        icon: Sparkles,
        title: 'Fasilitas Lengkap',
        description: 'Ruang kelas nyaman, perpustakaan, dan area bermain',
    },
];

const handleButtonClick = () => {
    haptics.medium();
};
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <Head title="Pendaftaran Siswa Baru (PSB)" />

        <!-- Hero Section -->
        <section class="relative overflow-hidden bg-gradient-to-br from-emerald-500 to-emerald-600">
            <div class="absolute inset-0 bg-[url('/images/pattern.svg')] opacity-10"></div>
            <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.5 }"
                >
                    <div class="text-center">
                        <span
                            v-if="settings?.academic_year"
                            class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-1.5 text-sm font-medium text-white backdrop-blur-sm"
                        >
                            <Calendar class="h-4 w-4" />
                            Tahun Ajaran {{ settings.academic_year }}
                        </span>

                        <h1 class="mt-6 text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                            Pendaftaran Siswa Baru
                        </h1>
                        <p class="mx-auto mt-4 max-w-2xl text-lg text-emerald-50">
                            Bergabunglah bersama kami untuk masa depan anak yang lebih cerah.
                            Proses pendaftaran mudah dan cepat.
                        </p>

                        <!-- Status Badge -->
                        <div class="mt-8 flex justify-center">
                            <div
                                v-if="isOpen"
                                class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2.5 shadow-lg"
                            >
                                <span class="relative flex h-3 w-3">
                                    <span
                                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"
                                    ></span>
                                    <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-500"></span>
                                </span>
                                <span class="font-semibold text-emerald-600">Pendaftaran Dibuka</span>
                            </div>
                            <div
                                v-else
                                class="inline-flex items-center gap-2 rounded-full bg-white/90 px-6 py-2.5"
                            >
                                <AlertCircle class="h-5 w-5 text-amber-500" />
                                <span class="font-medium text-slate-700">Pendaftaran Ditutup</span>
                            </div>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                            <Link
                                v-if="isOpen"
                                :href="psbRegister()"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-8 py-4 text-base font-semibold text-emerald-600 shadow-lg transition-all active:scale-97 sm:w-auto"
                                @click="handleButtonClick"
                            >
                                Daftar Sekarang
                                <ChevronRight class="h-5 w-5" />
                            </Link>
                            <Link
                                :href="psbTracking()"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white/20 px-8 py-4 text-base font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/30 active:scale-97 sm:w-auto"
                                @click="handleButtonClick"
                            >
                                Cek Status Pendaftaran
                            </Link>
                        </div>
                    </div>
                </Motion>
            </div>
        </section>

        <!-- Info Cards -->
        <section class="mx-auto -mt-8 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-3">
                <Motion
                    v-if="settings"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.5, delay: 0.1 }"
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="flex items-start gap-4">
                        <div class="rounded-lg bg-emerald-50 p-3">
                            <Calendar class="h-6 w-6 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                Periode Pendaftaran
                            </p>
                            <p class="mt-1 font-semibold text-slate-900">
                                {{ settings.registration_open_date }}
                            </p>
                            <p class="text-sm text-slate-500">
                                s/d {{ settings.registration_close_date }}
                            </p>
                        </div>
                    </div>
                </Motion>

                <Motion
                    v-if="settings"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.5, delay: 0.2 }"
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="flex items-start gap-4">
                        <div class="rounded-lg bg-sky-50 p-3">
                            <Clock class="h-6 w-6 text-sky-600" />
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                Pengumuman
                            </p>
                            <p class="mt-1 font-semibold text-slate-900">
                                {{ settings.announcement_date }}
                            </p>
                            <p class="text-sm text-slate-500">
                                Hasil seleksi
                            </p>
                        </div>
                    </div>
                </Motion>

                <Motion
                    v-if="settings"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.5, delay: 0.3 }"
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="flex items-start gap-4">
                        <div class="rounded-lg bg-amber-50 p-3">
                            <Users class="h-6 w-6 text-amber-600" />
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                Kuota Per Kelas
                            </p>
                            <p class="mt-1 font-semibold text-slate-900">
                                {{ settings.quota_per_class }} Siswa
                            </p>
                            <p class="text-sm text-slate-500">
                                Biaya: {{ settings.registration_fee === 0 ? 'Gratis' : settings.formatted_fee }}
                            </p>
                        </div>
                    </div>
                </Motion>
            </div>
        </section>

        <!-- Timeline Section -->
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.5 }"
            >
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">
                        Tahapan Pendaftaran
                    </h2>
                    <p class="mt-2 text-slate-600">
                        Ikuti langkah-langkah berikut untuk mendaftarkan putra/putri Anda
                    </p>
                </div>
            </Motion>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Motion
                    v-for="(item, index) in timeline"
                    :key="index"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.5, delay: 0.1 * (index + 1) }"
                    class="relative"
                >
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div
                            class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"
                        >
                            <component :is="item.icon" class="h-6 w-6" />
                        </div>
                        <div class="mb-2 flex items-center gap-2">
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-xs font-bold text-white"
                            >
                                {{ index + 1 }}
                            </span>
                            <h3 class="font-semibold text-slate-900">{{ item.title }}</h3>
                        </div>
                        <p class="text-sm text-slate-600">{{ item.description }}</p>
                        <p class="mt-2 text-xs font-medium text-emerald-600">{{ item.date }}</p>
                    </div>
                    <!-- Connector line (hidden on last item) -->
                    <div
                        v-if="index < timeline.length - 1"
                        class="absolute -right-3 top-1/2 hidden h-0.5 w-6 bg-emerald-200 lg:block"
                    ></div>
                </Motion>
            </div>
        </section>

        <!-- Required Documents -->
        <section class="bg-white py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.5 }"
                >
                    <div class="text-center">
                        <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">
                            Dokumen yang Diperlukan
                        </h2>
                        <p class="mt-2 text-slate-600">
                            Siapkan dokumen-dokumen berikut sebelum mendaftar
                        </p>
                    </div>
                </Motion>

                <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Motion
                        v-for="(label, key) in requiredDocuments"
                        :key="key"
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ duration: 0.3 }"
                        class="flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                            <CheckCircle class="h-5 w-5 text-emerald-600" />
                        </div>
                        <div>
                            <p class="font-medium text-slate-900">{{ label }}</p>
                            <p class="text-xs text-slate-500">Format: PDF, JPG, PNG (maks 5MB)</p>
                        </div>
                    </Motion>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.5 }"
            >
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">
                        Mengapa Memilih Kami?
                    </h2>
                    <p class="mt-2 text-slate-600">
                        Kami berkomitmen memberikan pendidikan terbaik untuk putra/putri Anda
                    </p>
                </div>
            </Motion>

            <div class="mt-12 grid gap-6 sm:grid-cols-3">
                <Motion
                    v-for="(feature, index) in features"
                    :key="index"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.5, delay: 0.1 * (index + 1) }"
                    class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm transition-all hover:scale-101"
                >
                    <div
                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50"
                    >
                        <component :is="feature.icon" class="h-7 w-7 text-emerald-600" />
                    </div>
                    <h3 class="font-semibold text-slate-900">{{ feature.title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ feature.description }}</p>
                </Motion>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="bg-emerald-500 py-16">
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.5 }"
                >
                    <h2 class="text-2xl font-bold text-white sm:text-3xl">
                        Siap Mendaftarkan Putra/Putri Anda?
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-emerald-50">
                        Proses pendaftaran hanya membutuhkan waktu beberapa menit.
                        Isi formulir dan upload dokumen yang diperlukan.
                    </p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <Link
                            v-if="isOpen"
                            :href="psbRegister()"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-8 py-4 text-base font-semibold text-emerald-600 shadow-lg transition-all active:scale-97 sm:w-auto"
                            @click="handleButtonClick"
                        >
                            Mulai Pendaftaran
                            <ChevronRight class="h-5 w-5" />
                        </Link>
                        <Link
                            :href="psbTracking()"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-white/30 px-8 py-4 text-base font-semibold text-white transition-all hover:bg-white/10 active:scale-97 sm:w-auto"
                            @click="handleButtonClick"
                        >
                            Cek Status
                        </Link>
                    </div>
                </Motion>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white py-8">
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <p class="text-sm text-slate-500">
                    &copy; {{ new Date().getFullYear() }} Sekolah Dasar. Hak Cipta Dilindungi.
                </p>
            </div>
        </footer>
    </div>
</template>
