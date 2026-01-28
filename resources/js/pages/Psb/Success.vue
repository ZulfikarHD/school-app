<script setup lang="ts">
/**
 * PSB Success Page - Halaman sukses setelah pendaftaran berhasil
 *
 * Page ini bertujuan untuk menampilkan konfirmasi pendaftaran berhasil
 * dengan nomor registrasi yang dapat dicopy dan informasi langkah selanjutnya
 */
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Motion } from 'motion-v';
import {
    CheckCircle,
    Copy,
    Check,
    FileText,
    Clock,
    ChevronRight,
    Home,
    Search,
} from 'lucide-vue-next';
import { landing as psbLanding, tracking as psbTracking } from '@/routes/psb';
import { useHaptics } from '@/composables/useHaptics';

interface Props {
    registration: {
        registration_number: string;
        student_name: string;
        created_at: string;
    };
}

const props = defineProps<Props>();
const haptics = useHaptics();

/**
 * State untuk menandakan nomor sudah di-copy
 */
const copied = ref(false);

/**
 * Copy nomor pendaftaran ke clipboard
 */
const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(props.registration.registration_number);
        copied.value = true;
        haptics.success();

        // Reset setelah 2 detik
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (err) {
        haptics.heavy();
        console.error('Failed to copy:', err);
    }
};

/**
 * Langkah selanjutnya setelah pendaftaran
 */
const nextSteps = [
    {
        icon: FileText,
        title: 'Verifikasi Dokumen',
        description: 'Tim kami akan memeriksa kelengkapan dokumen yang Anda upload.',
    },
    {
        icon: Clock,
        title: 'Tunggu Pengumuman',
        description: 'Hasil seleksi akan diumumkan sesuai jadwal yang tertera.',
    },
    {
        icon: Search,
        title: 'Cek Status',
        description: 'Gunakan nomor pendaftaran untuk mengecek status secara berkala.',
    },
];
</script>

<template>
    <div class="flex min-h-screen flex-col bg-slate-50">
        <Head title="Pendaftaran Berhasil - PSB" />

        <!-- Main Content -->
        <main class="flex flex-1 flex-col items-center justify-center px-4 py-12">
            <Motion
                :initial="{ opacity: 0, scale: 0.9 }"
                :animate="{ opacity: 1, scale: 1 }"
                :transition="{ duration: 0.5, type: 'spring', stiffness: 300, damping: 28 }"
                class="w-full max-w-lg"
            >
                <!-- Success Card -->
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                    <!-- Success Icon -->
                    <div class="mb-6 flex justify-center">
                        <Motion
                            :initial="{ scale: 0 }"
                            :animate="{ scale: 1 }"
                            :transition="{ duration: 0.5, delay: 0.2, type: 'spring', stiffness: 200 }"
                        >
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
                                <CheckCircle class="h-10 w-10 text-emerald-500" />
                            </div>
                        </Motion>
                    </div>

                    <!-- Title -->
                    <div class="mb-8 text-center">
                        <h1 class="text-2xl font-bold text-slate-900">Pendaftaran Berhasil!</h1>
                        <p class="mt-2 text-slate-600">
                            Terima kasih, <span class="font-medium">{{ registration.student_name }}</span>
                            telah terdaftar.
                        </p>
                    </div>

                    <!-- Registration Number -->
                    <div class="mb-8 rounded-xl bg-emerald-50 p-6">
                        <p class="mb-2 text-center text-sm font-medium text-emerald-700">
                            Nomor Pendaftaran
                        </p>
                        <div class="flex items-center justify-center gap-3">
                            <p class="text-2xl font-bold tracking-wide text-emerald-900">
                                {{ registration.registration_number }}
                            </p>
                            <button
                                type="button"
                                class="rounded-lg bg-emerald-100 p-2 text-emerald-600 transition-all hover:bg-emerald-200 active:scale-95"
                                :title="copied ? 'Tersalin!' : 'Salin'"
                                @click="copyToClipboard"
                            >
                                <Check v-if="copied" class="h-5 w-5" />
                                <Copy v-else class="h-5 w-5" />
                            </button>
                        </div>
                        <p class="mt-3 text-center text-xs text-emerald-600">
                            Simpan nomor ini untuk mengecek status pendaftaran
                        </p>
                    </div>

                    <!-- Registration Info -->
                    <div class="mb-8 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Waktu Pendaftaran</span>
                            <span class="font-medium text-slate-900">{{ registration.created_at }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-3">
                        <Link
                            :href="psbTracking()"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-6 py-3.5 font-semibold text-white shadow-sm transition-all hover:bg-emerald-600 active:scale-97"
                        >
                            <Search class="h-5 w-5" />
                            Cek Status Pendaftaran
                        </Link>
                        <Link
                            :href="psbLanding()"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3.5 font-semibold text-slate-700 transition-all hover:bg-slate-50 active:scale-97"
                        >
                            <Home class="h-5 w-5" />
                            Kembali ke Halaman PSB
                        </Link>
                    </div>
                </div>

                <!-- Next Steps -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.5, delay: 0.3 }"
                    class="mt-8"
                >
                    <h2 class="mb-4 text-center text-lg font-semibold text-slate-900">
                        Langkah Selanjutnya
                    </h2>
                    <div class="space-y-3">
                        <div
                            v-for="(step, index) in nextSteps"
                            :key="index"
                            class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-4"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100"
                            >
                                <component :is="step.icon" class="h-5 w-5 text-slate-600" />
                            </div>
                            <div>
                                <h3 class="font-medium text-slate-900">{{ step.title }}</h3>
                                <p class="text-sm text-slate-500">{{ step.description }}</p>
                            </div>
                        </div>
                    </div>
                </Motion>
            </Motion>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white py-6">
            <div class="text-center">
                <p class="text-sm text-slate-500">
                    Butuh bantuan? Hubungi kami di
                    <a href="tel:+6281234567890" class="font-medium text-emerald-600 hover:underline">
                        0812-3456-7890
                    </a>
                </p>
            </div>
        </footer>
    </div>
</template>
