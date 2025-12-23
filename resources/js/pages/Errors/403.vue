<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { home } from '@/routes';

/**
 * Error 403 Forbidden page untuk unauthorized access
 * dengan user-friendly message, navigation options,
 * dan iOS-like animations dengan haptic feedback
 */

const haptics = useHaptics();

/**
 * Navigate back ke home dengan haptic feedback
 */
const goBack = () => {
    haptics.light();
    router.visit(home().url);
};

/**
 * Handle contact admin dengan haptic feedback
 */
const handleContactAdmin = () => {
    haptics.light();
};
</script>

<template>
    <Head title="Akses Ditolak" />

    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 dark:bg-zinc-950">
        <!-- iOS-like Background Mesh/Blur -->
        <div class="absolute top-[-20%] left-[-10%] w-[70%] h-[70%] rounded-full bg-red-400/5 blur-[100px] dark:bg-red-900/10 pointer-events-none animate-pulse-slow"></div>

        <Motion
            :initial="{ opacity: 0, scale: 0.9 }"
            :animate="{ opacity: 1, scale: 1 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            class="relative z-10 w-full max-w-md text-center"
        >
            <!-- Icon dengan bounce animation -->
            <Motion
                :initial="{ scale: 0, rotate: -180 }"
                :animate="{ scale: 1, rotate: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                class="mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-full bg-red-50 dark:bg-red-900/20"
            >
                <svg class="h-12 w-12 text-red-600 dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </Motion>

            <!-- Content Card dengan slide up animation -->
            <Motion
                :initial="{ y: 20, opacity: 0 }"
                :animate="{ y: 0, opacity: 1 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                class="overflow-hidden rounded-2xl bg-white/95 shadow-xl border border-gray-100 dark:bg-zinc-900/95 dark:border-zinc-800 dark:shadow-none"
            >
                <div class="p-8">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white">403</h1>
                    <h2 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Akses Ditolak</h2>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">
                        Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.
                        Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.
                    </p>

                    <!-- Actions dengan spring animation -->
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                        <Motion
                            :whileTap="{ scale: 0.97 }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                        >
                            <button
                                @click="goBack"
                                class="w-full rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:bg-blue-700 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-500/50 sm:w-auto"
                            >
                                Kembali ke Beranda
                            </button>
                        </Motion>
                        <Motion
                            :whileTap="{ scale: 0.97 }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                        >
                            <a
                                href="mailto:admin@sekolah.app"
                                @click="handleContactAdmin"
                                class="block w-full rounded-xl border border-gray-200 px-6 py-3 font-semibold text-gray-700 transition-all duration-200 hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-zinc-700 dark:text-gray-300 dark:hover:border-zinc-600 dark:hover:bg-zinc-800 dark:focus:ring-zinc-800 sm:w-auto"
                            >
                                Hubungi Admin
                            </a>
                        </Motion>
                    </div>
                </div>
            </Motion>

            <!-- Additional Info dengan fade in -->
            <Motion
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ delay: 0.4 }"
            >
                <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">
                    Error Code: 403 - Forbidden Access
                </p>
            </Motion>
        </Motion>
    </div>
</template>

