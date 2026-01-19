<script setup lang="ts">
/**
 * Promote Page - Halaman untuk bulk promote (naik kelas) siswa
 * yang mencakup wizard 3-step dengan UI yang clean dan informative
 * untuk memastikan TU dapat memproses naik kelas dengan mudah
 */
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, GraduationCap, Info } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import PromoteWizard from '@/components/features/students/PromoteWizard.vue';
import { index as studentsIndex } from '@/routes/admin/students';
import { useHaptics } from '@/composables/useHaptics';

interface Props {
    classes: Array<{
        id: number;
        nama: string;
        nama_lengkap: string;
        tingkat: number;
        tahun_ajaran: string;
    }>;
}

defineProps<Props>();

const haptics = useHaptics();
</script>

<template>
    <AppLayout title="Naik Kelas">
        <Head title="Naik Kelas Siswa" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
            <!-- Header - Enhanced dengan icon dan card wrapper -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <Motion :whileTap="{ scale: 0.95 }">
                            <Link
                                :href="studentsIndex()"
                                @click="haptics.light()"
                                class="w-11 h-11 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 active:bg-slate-200 rounded-xl transition-colors shrink-0
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                            >
                                <ChevronLeft class="w-5 h-5" />
                            </Link>
                        </Motion>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center shrink-0">
                                    <GraduationCap class="w-4 h-4 text-sky-600 dark:text-sky-400" />
                                </div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 truncate">
                                    Naik Kelas Siswa
                                </h1>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                Proses naik kelas massal dari satu kelas ke kelas berikutnya
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Info Card - Enhanced dengan step indicators -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-sky-50 dark:bg-sky-950/20 border border-sky-200 dark:border-sky-800 rounded-2xl p-4 sm:p-5">
                    <div class="flex gap-3 sm:gap-4">
                        <div class="shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 bg-sky-100 dark:bg-sky-900/40 rounded-xl">
                                <Info class="w-5 h-5 text-sky-600 dark:text-sky-400" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-sky-900 dark:text-sky-100 mb-3">
                                Panduan Naik Kelas
                            </h3>

                            <!-- Step indicators -->
                            <div class="grid gap-2 sm:grid-cols-3 sm:gap-4">
                                <div class="flex items-start gap-2 p-2.5 bg-white/60 dark:bg-sky-900/20 rounded-xl">
                                    <div class="w-6 h-6 rounded-full bg-sky-500 text-white flex items-center justify-center text-xs font-bold shrink-0">1</div>
                                    <div>
                                        <p class="text-xs font-medium text-sky-900 dark:text-sky-100">Pilih Tahun & Kelas</p>
                                        <p class="text-[11px] text-sky-700 dark:text-sky-300 mt-0.5">Tentukan asal dan tujuan</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 p-2.5 bg-white/60 dark:bg-sky-900/20 rounded-xl">
                                    <div class="w-6 h-6 rounded-full bg-sky-500 text-white flex items-center justify-center text-xs font-bold shrink-0">2</div>
                                    <div>
                                        <p class="text-xs font-medium text-sky-900 dark:text-sky-100">Pilih Siswa</p>
                                        <p class="text-[11px] text-sky-700 dark:text-sky-300 mt-0.5">Centang yang naik kelas</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 p-2.5 bg-white/60 dark:bg-sky-900/20 rounded-xl">
                                    <div class="w-6 h-6 rounded-full bg-sky-500 text-white flex items-center justify-center text-xs font-bold shrink-0">3</div>
                                    <div>
                                        <p class="text-xs font-medium text-sky-900 dark:text-sky-100">Konfirmasi</p>
                                        <p class="text-[11px] text-sky-700 dark:text-sky-300 mt-0.5">Review dan proses</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Wizard Component -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
            >
                <PromoteWizard :classes="classes" />
            </Motion>
        </div>
    </AppLayout>
</template>
