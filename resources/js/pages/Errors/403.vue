<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { ShieldAlert, ArrowLeft } from 'lucide-vue-next';
import { dashboard } from '@/routes';
</script>

<template>
    <Head title="Akses Ditolak" />

    <div class="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-zinc-950 overflow-hidden relative">
        <!-- Background Mesh -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-red-400/10 rounded-full blur-[100px] pointer-events-none animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-blue-400/10 rounded-full blur-[100px] pointer-events-none animate-pulse-slow animation-delay-2000"></div>

        <Motion
            :initial="{ opacity: 0, scale: 0.9 }"
            :animate="{ opacity: 1, scale: 1 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            class="relative z-10 max-w-md w-full text-center"
        >
            <div class="mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-3xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 shadow-sm">
                <ShieldAlert class="h-12 w-12 text-red-500 dark:text-red-400" />
            </div>

            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight mb-4">
                Akses Ditolak
            </h1>

            <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">
                Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.
            </p>

            <Link
                :href="dashboard()"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95"
            >
                <ArrowLeft class="w-4 h-4" />
                Kembali ke Dashboard
            </Link>
        </Motion>
    </div>
</template>
