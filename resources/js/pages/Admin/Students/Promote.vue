<script setup lang="ts">
/**
 * Promote Page - Halaman untuk bulk promote (naik kelas) siswa
 * yang mencakup wizard 3-step dengan UI yang clean dan informative
 * untuk memastikan TU dapat memproses naik kelas dengan mudah
 */
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
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
    <AppLayout>
        <Head title="Naik Kelas Siswa" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <Motion :whileTap="{ scale: 0.97 }">
                            <Link
                                :href="studentsIndex()"
                                @click="haptics.light()"
                                class="flex items-center justify-center w-10 h-10 bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800 rounded-xl border border-slate-200 dark:border-zinc-800 transition-colors duration-200"
                            >
                                <ArrowLeft class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                            </Link>
                        </Motion>
                        <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">
                            Naik Kelas Siswa
                        </h1>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400">
                        Proses naik kelas massal untuk siswa dari satu kelas ke kelas berikutnya
                    </p>
                </div>
            </div>

            <!-- Info Card -->
            <div class="bg-sky-50 dark:bg-sky-950/20 border border-sky-200 dark:border-sky-800 rounded-xl p-4">
                <div class="flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-10 h-10 bg-sky-100 dark:bg-sky-900/40 rounded-lg">
                            <svg
                                class="w-5 h-5 text-sky-600 dark:text-sky-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-sky-900 dark:text-sky-100 mb-1">
                            Panduan Naik Kelas
                        </h3>
                        <ul class="text-sm text-sky-700 dark:text-sky-300 space-y-1">
                            <li>• Pilih tahun ajaran asal dan tahun ajaran tujuan</li>
                            <li>• Pilih kelas asal dan kelas tujuan (kelas akan otomatis naik ke tingkat berikutnya)</li>
                            <li>• Preview daftar siswa dan pilih siswa yang akan naik kelas</li>
                            <li>• Siswa yang tidak dipilih tidak akan naik kelas</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Wizard Component -->
            <PromoteWizard :classes="classes" />
        </div>
    </AppLayout>
</template>
