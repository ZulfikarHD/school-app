<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Dashboard untuk Admin/TU dengan akses ke Student Management,
 * Payment Management, PSB Registration, dan User Management
 * dengan iOS-like staggered animations dan haptic feedback
 */

interface Props {
    stats: {
        total_students: number;
        total_payments: number;
        pending_psb: number;
        total_users: number;
    };
}

defineProps<Props>();

const haptics = useHaptics();

/**
 * Handle card click dengan haptic feedback
 */
const handleCardClick = () => {
    haptics.light();
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Admin" />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <!-- Header Section dengan fade in animation -->
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-8 shadow-lg">
                    <h1 class="text-3xl font-bold text-white">Dashboard Admin</h1>
                    <p class="mt-2 text-blue-100">Selamat datang di sistem manajemen sekolah</p>
                </div>
            </Motion>

            <!-- Stats Grid dengan staggered animations -->
            <div class="mx-auto max-w-7xl px-6 py-8">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Students Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                        :whileHover="{ y: -4, scale: 1.02 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-lg transition-all duration-200 hover:shadow-xl dark:bg-gray-800" @click="handleCardClick">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Siswa</p>
                                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_students }}</p>
                                </div>
                                <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/30">
                                    <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        </div>
                    </Motion>

                    <!-- Total Payments Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                        :whileHover="{ y: -4, scale: 1.02 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-lg transition-all duration-200 hover:shadow-xl dark:bg-gray-800" @click="handleCardClick">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Pembayaran</p>
                                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_payments }}</p>
                                </div>
                                <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/30">
                                    <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        </div>
                    </Motion>

                    <!-- Pending PSB Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                        :whileHover="{ y: -4, scale: 1.02 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-lg transition-all duration-200 hover:shadow-xl dark:bg-gray-800" @click="handleCardClick">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">PSB Pending</p>
                                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.pending_psb }}</p>
                                </div>
                                <div class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-900/30">
                                    <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        </div>
                    </Motion>

                    <!-- Total Users Card -->
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                        :whileHover="{ y: -4, scale: 1.02 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-lg transition-all duration-200 hover:shadow-xl dark:bg-gray-800" @click="handleCardClick">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total User</p>
                                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_users }}</p>
                                </div>
                                <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/30">
                                    <svg class="h-8 w-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        </div>
                    </Motion>
                </div>

                <!-- Quick Actions dengan staggered animation -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.3 }"
                    class="mt-8"
                >
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Aksi Cepat</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <Motion
                            :initial="{ opacity: 0, scale: 0.95 }"
                            :animate="{ opacity: 1, scale: 1 }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.35 }"
                            :whileHover="{ y: -2, scale: 1.02 }"
                            :whileTap="{ scale: 0.97 }"
                        >
                            <div class="rounded-xl bg-white p-6 shadow transition-all duration-200 hover:shadow-lg dark:bg-gray-800" @click="handleCardClick">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Manajemen Siswa</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Kelola data siswa dan akademik</p>
                            </div>
                        </Motion>
                        <Motion
                            :initial="{ opacity: 0, scale: 0.95 }"
                            :animate="{ opacity: 1, scale: 1 }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.4 }"
                            :whileHover="{ y: -2, scale: 1.02 }"
                            :whileTap="{ scale: 0.97 }"
                        >
                            <div class="rounded-xl bg-white p-6 shadow transition-all duration-200 hover:shadow-lg dark:bg-gray-800" @click="handleCardClick">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Pembayaran</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Kelola pembayaran SPP dan lainnya</p>
                            </div>
                        </Motion>
                        <Motion
                            :initial="{ opacity: 0, scale: 0.95 }"
                            :animate="{ opacity: 1, scale: 1 }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.45 }"
                            :whileHover="{ y: -2, scale: 1.02 }"
                            :whileTap="{ scale: 0.97 }"
                        >
                            <div class="rounded-xl bg-white p-6 shadow transition-all duration-200 hover:shadow-lg dark:bg-gray-800" @click="handleCardClick">
                            <h3 class="font-semibold text-gray-900 dark:text-white">PSB</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Penerimaan Siswa Baru</p>
                            </div>
                        </Motion>
                    </div>
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>

