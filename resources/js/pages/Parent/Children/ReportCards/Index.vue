<script setup lang="ts">
/**
 * Parent Children ReportCards Index - List rapor anak per semester
 * menampilkan rapor yang sudah dirilis dengan status dan aksi download
 */
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import {
    FileText,
    ArrowLeft,
    User,
    Eye,
    Download,
    Award,
    Calendar,
    CheckCircle
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { show as showChild } from '@/routes/parent/children';
import { index as reportCardsIndex, show as showReportCard, download } from '@/routes/parent/children/report-cards';

interface ReportCardEntry {
    id: number;
    tahun_ajaran: string;
    semester: string;
    semester_label: string;
    class_name: string;
    status: string;
    status_label: string;
    status_color: string;
    average_score: number | null;
    rank: number | null;
    released_at: string | null;
    has_pdf: boolean;
}

interface Props {
    title: string;
    student: {
        id: number;
        nama_lengkap: string;
        nis: string;
        foto: string | null;
        kelas: { id: number; nama_lengkap: string } | null;
    };
    reportCards: ReportCardEntry[];
}

const props = defineProps<Props>();

const haptics = useHaptics();

/**
 * Get predikat from score
 */
const getPredikat = (score: number): string => {
    if (score >= 90) return 'A';
    if (score >= 80) return 'B';
    if (score >= 70) return 'C';
    return 'D';
};

/**
 * Get predikat color
 */
const getPredikatColor = (predikat: string): string => {
    const colors: Record<string, string> = {
        'A': 'text-emerald-600 dark:text-emerald-400',
        'B': 'text-blue-600 dark:text-blue-400',
        'C': 'text-amber-600 dark:text-amber-400',
        'D': 'text-red-600 dark:text-red-400'
    };
    return colors[predikat] || colors['D'];
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <Link
                            :href="showChild(student.id)"
                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                        >
                            <ArrowLeft class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                        </Link>
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center overflow-hidden shrink-0">
                                <img
                                    v-if="student.foto"
                                    :src="student.foto"
                                    :alt="student.nama_lengkap"
                                    class="w-full h-full object-cover"
                                />
                                <User v-else class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-slate-100">
                                    Rapor
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ student.nama_lengkap }} - {{ student.kelas?.nama_lengkap || '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Report Cards List -->
            <div class="space-y-4">
                <Motion
                    v-for="(rc, idx) in reportCards"
                    :key="rc.id"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 + idx * 0.05 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                        <div class="p-4 sm:p-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <div class="w-14 h-14 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                        <FileText class="w-7 h-7 text-emerald-600 dark:text-emerald-400" />
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="font-semibold text-slate-900 dark:text-slate-100">
                                                Semester {{ rc.semester_label }}
                                            </h3>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-medium">
                                                <CheckCircle class="w-3 h-3" />
                                                Tersedia
                                            </span>
                                        </div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                            Tahun Ajaran {{ rc.tahun_ajaran }}
                                        </p>
                                        <div class="flex items-center gap-4 mt-2 text-sm">
                                            <div class="flex items-center gap-1 text-slate-600 dark:text-slate-400">
                                                <Calendar class="w-4 h-4" />
                                                <span>{{ rc.class_name }}</span>
                                            </div>
                                            <div v-if="rc.released_at" class="text-xs text-slate-500">
                                                Dirilis {{ rc.released_at }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <!-- Stats -->
                                    <div class="flex items-center gap-4 text-center">
                                        <div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Rata-rata</p>
                                            <p v-if="rc.average_score !== null" :class="['text-xl font-bold', getPredikatColor(getPredikat(rc.average_score))]">
                                                {{ rc.average_score.toFixed(1) }}
                                            </p>
                                            <p v-else class="text-xl font-bold text-slate-400">-</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Peringkat</p>
                                            <p v-if="rc.rank" class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                                #{{ rc.rank }}
                                            </p>
                                            <p v-else class="text-xl font-bold text-slate-400">-</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-100 dark:border-zinc-800">
                                <Motion :whileTap="{ scale: 0.97 }" class="flex-1 sm:flex-initial">
                                    <Link
                                        :href="showReportCard(student.id, rc.id)"
                                        @click="haptics.light()"
                                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-500 text-white rounded-xl hover:bg-indigo-600 transition-colors font-medium text-sm w-full sm:w-auto"
                                    >
                                        <Eye class="w-4 h-4" />
                                        Lihat Rapor
                                    </Link>
                                </Motion>

                                <Motion v-if="rc.has_pdf" :whileTap="{ scale: 0.97 }" class="flex-1 sm:flex-initial">
                                    <a
                                        :href="download(student.id, rc.id).url"
                                        @click="haptics.medium()"
                                        class="flex items-center justify-center gap-2 px-4 py-2.5 border border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors font-medium text-sm w-full sm:w-auto"
                                    >
                                        <Download class="w-4 h-4" />
                                        Download PDF
                                    </a>
                                </Motion>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>

            <!-- Empty State -->
            <Motion
                v-if="reportCards.length === 0"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-8 shadow-sm border border-slate-200 dark:border-zinc-800 text-center">
                    <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4">
                        <FileText class="w-8 h-8 text-slate-400" />
                    </div>
                    <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-2">
                        Belum Ada Rapor
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Rapor untuk anak Anda belum tersedia. Anda akan mendapat notifikasi saat rapor sudah dirilis.
                    </p>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
