<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { Plus, Calendar } from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Halaman riwayat presensi siswa untuk guru
 * dengan filter berdasarkan kelas dan tanggal
 * 
 * TODO P2: Implement attendance history table dengan edit capability
 * Untuk Sprint 2, fokus pada input form dulu (Create.vue)
 */

interface SchoolClass {
    id: number;
    tingkat: number;
    nama: string;
    nama_lengkap: string;
    tahun_ajaran: string;
    jumlah_siswa: number;
}

interface Props {
    title: string;
    classes: SchoolClass[];
}

defineProps<Props>();

const haptics = useHaptics();
</script>

<template>
    <AppLayout>
        <Head :title="title" />
        
        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="bg-white px-6 py-8 border-b border-gray-100 dark:bg-zinc-900 dark:border-zinc-800">
                    <div class="mx-auto max-w-7xl flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">Lihat dan kelola riwayat presensi siswa</p>
                        </div>
                        
                        <Motion :whileTap="{ scale: 0.97 }">
                            <Link
                                href="/teacher/attendance/daily"
                                @click="haptics.light()"
                                class="px-5 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold
                                       flex items-center gap-2 shadow-sm shadow-emerald-500/25
                                       transition-colors duration-150"
                            >
                                <Plus :size="20" />
                                <span class="hidden sm:inline">Input Presensi</span>
                            </Link>
                        </Motion>
                    </div>
                </div>
            </Motion>
            
            <div class="mx-auto max-w-7xl px-6 py-8">
                <!-- Under Construction Notice -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                >
                    <div class="bg-sky-50 dark:bg-sky-950/30 border border-sky-200 dark:border-sky-800 rounded-2xl p-6 mb-6">
                        <div class="flex items-start gap-4">
                            <Calendar :size="24" class="text-sky-600 dark:text-sky-400 flex-shrink-0 mt-0.5" />
                            <div>
                                <h3 class="text-lg font-semibold text-sky-900 dark:text-sky-100 mb-2">
                                    Fitur Riwayat Presensi (Dalam Pengembangan)
                                </h3>
                                <p class="text-sm text-sky-700 dark:text-sky-300 mb-4">
                                    Halaman ini akan menampilkan riwayat presensi siswa dengan fitur filter berdasarkan kelas dan tanggal, 
                                    serta kemampuan untuk mengedit presensi yang sudah terinput.
                                </p>
                                <div class="space-y-2 text-sm text-sky-600 dark:text-sky-400">
                                    <p><strong>Fitur yang akan tersedia:</strong></p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>Tabel riwayat presensi dengan pagination</li>
                                        <li>Filter berdasarkan kelas dan rentang tanggal</li>
                                        <li>Edit presensi yang sudah terinput</li>
                                        <li>Summary statistik presensi per kelas</li>
                                        <li>Export data presensi ke Excel</li>
                                    </ul>
                                </div>
                                <div class="mt-4 pt-4 border-t border-sky-200 dark:border-sky-800">
                                    <p class="text-sm text-sky-600 dark:text-sky-400">
                                        <strong>Sementara ini:</strong> Anda dapat input presensi harian melalui tombol "Input Presensi" di atas.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>
                
                <!-- Quick Stats -->
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-6">
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-slate-600 dark:text-zinc-400">Total Kelas Anda</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ classes.length }}</p>
                        </div>
                    </Motion>
                    
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-slate-600 dark:text-zinc-400">Total Siswa</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                                {{ classes.reduce((sum, c) => sum + c.jumlah_siswa, 0) }}
                            </p>
                        </div>
                    </Motion>
                    
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-slate-600 dark:text-zinc-400">Presensi Hari Ini</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-600">-</p>
                            <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">Akan tersedia di P2</p>
                        </div>
                    </Motion>
                </div>
                
                <!-- Classes List -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-200 dark:border-zinc-800">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Kelas Anda</h2>
                            <p class="text-sm text-slate-600 dark:text-zinc-400 mt-1">Daftar kelas yang Anda ampu</p>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <Motion
                                    v-for="kelas in classes"
                                    :key="kelas.id"
                                    :initial="{ opacity: 0, scale: 0.95 }"
                                    :animate="{ opacity: 1, scale: 1 }"
                                    :whileHover="{ scale: 1.02 }"
                                    :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                                >
                                    <div class="p-4 bg-slate-50 dark:bg-zinc-800 rounded-xl border border-slate-200 dark:border-zinc-700">
                                        <h3 class="font-semibold text-slate-900 dark:text-white">{{ kelas.nama_lengkap }}</h3>
                                        <p class="text-sm text-slate-600 dark:text-zinc-400 mt-1">
                                            {{ kelas.jumlah_siswa }} Siswa
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-zinc-500 mt-2">
                                            TA {{ kelas.tahun_ajaran }}
                                        </p>
                                    </div>
                                </Motion>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>
