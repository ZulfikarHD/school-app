<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Dashboard untuk Orang Tua dengan informasi anak,
 * pembayaran, nilai, dan kehadiran
 * dengan iOS-like staggered animations dan haptic feedback
 */

interface Props {
    stats: {
        children: any[];
        pending_payments: number;
        recent_grades: any[];
        attendance_summary: any[];
    };
}

defineProps<Props>();

const haptics = useHaptics();
const handleCardClick = () => haptics.light();
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Orang Tua" />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="bg-gradient-to-r from-orange-600 to-red-600 px-6 py-8 shadow-lg">
                    <h1 class="text-3xl font-bold text-white">Dashboard Orang Tua</h1>
                    <p class="mt-2 text-orange-100">Pantau perkembangan anak Anda</p>
                </div>
            </Motion>

            <div class="mx-auto max-w-7xl px-6 py-8">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                        :whileHover="{ y: -4, scale: 1.02 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-lg dark:bg-gray-800" @click="handleCardClick">
                            <div class="p-6">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Jumlah Anak</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.children.length }}</p>
                            </div>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                        :whileHover="{ y: -4, scale: 1.02 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-lg dark:bg-gray-800" @click="handleCardClick">
                            <div class="p-6">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tagihan Pending</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.pending_payments }}</p>
                            </div>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                        :whileHover="{ y: -4, scale: 1.02 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-lg dark:bg-gray-800" @click="handleCardClick">
                            <div class="p-6">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Nilai Terbaru</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.recent_grades.length }}</p>
                            </div>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                        :whileHover="{ y: -4, scale: 1.02 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-lg dark:bg-gray-800" @click="handleCardClick">
                            <div class="p-6">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ringkasan Absensi</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.attendance_summary.length }}</p>
                            </div>
                        </div>
                    </Motion>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

