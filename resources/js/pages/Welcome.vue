<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { LayoutDashboard, ShieldCheck, ArrowRight, Users, School } from 'lucide-vue-next';
import { login } from '@/routes';

/**
 * Welcome / Landing Page
 * Entry point aplikasi dengan design modern iOS-like,
 * animations, dan haptic feedback.
 */

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    laravelVersion?: string;
    phpVersion?: string;
}>();

const haptics = useHaptics();

const handleTap = () => {
    haptics.medium();
};
</script>

<template>
    <Head title="Selamat Datang" />

    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950 overflow-hidden relative selection:bg-blue-500 selection:text-white">
        <!-- iOS-like Background Mesh/Blur -->
        <div class="absolute top-[-20%] left-[-10%] w-[70%] h-[70%] rounded-full bg-blue-400/10 blur-[120px] dark:bg-blue-600/10 pointer-events-none animate-pulse-slow"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[70%] h-[70%] rounded-full bg-indigo-400/10 blur-[120px] dark:bg-indigo-600/10 pointer-events-none animate-pulse-slow animation-delay-2000"></div>

        <!-- Navbar -->
        <Motion
            :initial="{ y: -20, opacity: 0 }"
            :animate="{ y: 0, opacity: 1 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            class="fixed top-0 left-0 right-0 z-50 px-6 py-4"
        >
            <div class="mx-auto max-w-7xl">
                <nav class="flex items-center justify-between rounded-2xl bg-white/95 px-6 py-3 shadow-sm border border-gray-100 dark:bg-zinc-900/95 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white shadow-lg shadow-blue-500/30">
                            <School class="h-5 w-5" />
                        </div>
                        <span class="font-bold text-gray-900 dark:text-white">SIS Sekolah</span>
                    </div>

                    <div class="flex items-center gap-4">
                        <Motion :whileTap="{ scale: 0.95 }">
                            <Link
                                :href="login()"
                                @click="handleTap"
                                class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                            >
                                Masuk
                            </Link>
                        </Motion>
                    </div>
                </nav>
            </div>
        </Motion>

        <!-- Hero Section -->
        <main class="relative z-10 flex min-h-screen flex-col items-center justify-center px-6 pt-24 pb-12 text-center">
            <Motion
                :initial="{ opacity: 0, y: 30 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
                class="mx-auto max-w-4xl"
            >
                <!-- Badge -->
                <Motion
                    :initial="{ scale: 0.9, opacity: 0 }"
                    :animate="{ scale: 1, opacity: 1 }"
                    :transition="{ delay: 0.2 }"
                    class="mb-8 inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-1.5 text-sm font-medium text-blue-600 ring-1 ring-blue-500/20 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-500/30"
                >
                    <span class="flex h-2 w-2 rounded-full bg-blue-500"></span>
                    Sistem Informasi Sekolah Terpadu
                </Motion>

                <h1 class="text-5xl font-extrabold tracking-tight text-gray-900 sm:text-7xl dark:text-white mb-6">
                    Manajemen Sekolah <br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400">
                        Jadi Lebih Mudah
                    </span>
                </h1>

                <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-600 dark:text-gray-400 leading-relaxed">
                    Platform digital modern untuk mengelola akademik, kesiswaan, dan administrasi sekolah Anda.
                    Efisien, aman, dan dapat diakses dari mana saja.
                </p>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: 0.3 }"
                    class="flex flex-col sm:flex-row items-center justify-center gap-4"
                >
                    <Motion :whileTap="{ scale: 0.97 }" :whileHover="{ scale: 1.02 }">
                        <Link
                            :href="login()"
                            @click="handleTap"
                            class="group relative flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-8 py-4 text-base font-bold text-white shadow-lg shadow-blue-500/30 transition-all hover:bg-blue-700 hover:shadow-blue-500/40"
                        >
                            Mulai Sekarang
                            <ArrowRight class="h-5 w-5 transition-transform group-hover:translate-x-1" />
                        </Link>
                    </Motion>

                    <Motion :whileTap="{ scale: 0.97 }" :whileHover="{ scale: 1.02 }">
                        <a
                            href="#features"
                            @click="handleTap"
                            class="flex items-center justify-center gap-2 rounded-xl bg-white px-8 py-4 text-base font-bold text-gray-700 shadow-sm border border-gray-200 transition-all hover:bg-gray-50 hover:text-gray-900 dark:bg-zinc-800 dark:text-gray-200 dark:border-zinc-700 dark:hover:bg-zinc-700"
                        >
                            Pelajari Lebih Lanjut
                        </a>
                    </Motion>
                </Motion>
            </Motion>

            <!-- Features Grid -->
            <div id="features" class="mt-32 w-full max-w-7xl mx-auto">
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :whileInView="{ opacity: 1, y: 0 }"
                        :viewport="{ once: true }"
                        :transition="{ delay: 0.1 }"
                        class="group rounded-3xl bg-white p-8 shadow-xl border border-gray-100 transition-all hover:shadow-2xl hover:shadow-blue-500/10 dark:bg-zinc-900 dark:border-zinc-800 dark:shadow-none"
                    >
                        <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 group-hover:scale-110 transition-transform duration-300 dark:bg-blue-900/30 dark:text-blue-400">
                            <LayoutDashboard class="h-7 w-7" />
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">Dashboard Intuitif</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            Pantau semua aktivitas sekolah dalam satu tampilan yang mudah dipahami dan responsive.
                        </p>
                    </Motion>

                    <!-- Feature 2 -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :whileInView="{ opacity: 1, y: 0 }"
                        :viewport="{ once: true }"
                        :transition="{ delay: 0.2 }"
                        class="group rounded-3xl bg-white p-8 shadow-xl border border-gray-100 transition-all hover:shadow-2xl hover:shadow-purple-500/10 dark:bg-zinc-900 dark:border-zinc-800 dark:shadow-none"
                    >
                        <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-50 text-purple-600 group-hover:scale-110 transition-transform duration-300 dark:bg-purple-900/30 dark:text-purple-400">
                            <Users class="h-7 w-7" />
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">Manajemen Siswa</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            Kelola data siswa, absensi, dan nilai akademik dengan sistem yang terintegrasi penuh.
                        </p>
                    </Motion>

                    <!-- Feature 3 -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :whileInView="{ opacity: 1, y: 0 }"
                        :viewport="{ once: true }"
                        :transition="{ delay: 0.3 }"
                        class="group rounded-3xl bg-white p-8 shadow-xl border border-gray-100 transition-all hover:shadow-2xl hover:shadow-green-500/10 dark:bg-zinc-900 dark:border-zinc-800 dark:shadow-none"
                    >
                        <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-green-50 text-green-600 group-hover:scale-110 transition-transform duration-300 dark:bg-green-900/30 dark:text-green-400">
                            <ShieldCheck class="h-7 w-7" />
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">Aman & Terpercaya</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            Data sekolah Anda aman dengan enkripsi tingkat lanjut dan backup otomatis berkala.
                        </p>
                    </Motion>
                </div>
            </div>

            <!-- Footer -->
            <footer class="mt-32 w-full text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    &copy; {{ new Date().getFullYear() }} Sistem Informasi Sekolah. Hak Cipta Dilindungi.
                </p>
                <div class="mt-4 flex justify-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                        Laravel v{{ laravelVersion }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                        PHP v{{ phpVersion }}
                    </span>
                </div>
            </footer>
        </main>
    </div>
</template>
