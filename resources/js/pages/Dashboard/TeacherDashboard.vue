<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Dashboard untuk Guru dengan akses ke class management,
 * attendance, grades, dan jadwal mengajar
 * dengan iOS-like staggered animations dan haptic feedback
 */

interface Props {
    stats: {
        my_classes: number;
        total_students: number;
        pending_grades: number;
        today_schedule: any[];
    };
}

defineProps<Props>();

const haptics = useHaptics();
const handleCardClick = () => haptics.light();
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Guru" />

        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="bg-white px-6 py-8 border-b border-gray-100 dark:bg-zinc-900 dark:border-zinc-800">
                    <div class="mx-auto max-w-7xl">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard Guru</h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Kelola kelas dan nilai siswa</p>
                    </div>
                </div>
            </Motion>

            <div class="mx-auto max-w-7xl px-6 py-8">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800" @click="handleCardClick">
                            <div class="p-6">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Kelas Saya</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.my_classes }}</p>
                            </div>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800" @click="handleCardClick">
                            <div class="p-6">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Siswa</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_students }}</p>
                            </div>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800" @click="handleCardClick">
                            <div class="p-6">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Nilai Pending</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.pending_grades }}</p>
                            </div>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800" @click="handleCardClick">
                            <div class="p-6">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Jadwal Hari Ini</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.today_schedule.length }}</p>
                            </div>
                        </div>
                    </Motion>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

