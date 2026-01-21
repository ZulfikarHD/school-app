<script setup lang="ts">
/**
 * Admin Report Card Show Page - Preview rapor siswa
 * dengan aksi download PDF, unlock, dan regenerate
 */
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import {
    FileText,
    ArrowLeft,
    Download,
    Unlock,
    RefreshCw,
    User,
    GraduationCap,
    Calendar,
    Award
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import ReportCardPreview from '@/components/features/grades/ReportCardPreview.vue';
import ReportCardStatusBadge from '@/components/features/grades/ReportCardStatusBadge.vue';
import { index, unlock, regenerate } from '@/routes/admin/report-cards';

interface SubjectGrade {
    subject_id: number;
    subject_name: string;
    subject_code: string;
    final_grade: number;
    predikat: string;
    predikat_label: string;
    breakdown: {
        uh: { average: number; weight: number; count: number };
        uts: { score: number; weight: number };
        uas: { score: number; weight: number };
        praktik: { average: number; weight: number; count: number };
    };
}

interface Props {
    reportCard: {
        id: number;
        status: string;
        status_label: string;
        status_color: string;
        is_editable: boolean;
        has_pdf: boolean;
        student: {
            nama_lengkap: string;
            nis: string;
            nisn: string;
            jenis_kelamin: string;
            tempat_lahir: string;
            tanggal_lahir: string;
            agama: string;
            alamat: string;
            nama_ayah: string;
            nama_ibu: string;
        };
        class: {
            nama_lengkap: string;
            tingkat: number;
            wali_kelas: string;
        };
        academic: {
            tahun_ajaran: string;
            semester: string;
            semester_label: string;
        };
        grades: SubjectGrade[];
        overall: {
            average: number;
            rank: number;
            total_students: number;
        };
        attitude: {
            spiritual_grade: string;
            spiritual_label: string;
            spiritual_description: string;
            social_grade: string;
            social_label: string;
            social_description: string;
            homeroom_notes: string;
        } | null;
        attendance: {
            hadir: number;
            sakit: number;
            izin: number;
            alpha: number;
            total_days: number;
        };
        generated_at: string;
    };
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

/**
 * Handle unlock rapor untuk koreksi
 */
const handleUnlock = async () => {
    const confirmed = await modal.dialog({
        type: 'warning',
        icon: 'warning',
        title: 'Unlock Rapor',
        message: 'Unlock rapor akan menghapus PDF dan membuka kembali nilai untuk dikoreksi. Lanjutkan?',
        confirmText: 'Ya, Unlock',
        cancelText: 'Batal',
        showCancel: true
    });

    if (!confirmed) return;

    haptics.medium();
    router.post(unlock(props.reportCard.id).url, {}, {
        preserveScroll: true
    });
};

/**
 * Handle regenerate PDF
 */
const handleRegenerate = async () => {
    const confirmed = await modal.dialog({
        type: 'info',
        icon: 'question',
        title: 'Regenerate PDF',
        message: 'PDF rapor akan di-generate ulang dengan data terbaru. Lanjutkan?',
        confirmText: 'Ya, Regenerate',
        cancelText: 'Batal',
        showCancel: true
    });

    if (!confirmed) return;

    haptics.medium();
    router.post(regenerate(props.reportCard.id).url, {}, {
        preserveScroll: true
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="`Rapor ${reportCard.student.nama_lengkap}`" />

        <div class="max-w-5xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <Link
                                :href="index()"
                                class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                            >
                                <ArrowLeft class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                            </Link>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Preview Rapor
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ reportCard.student.nama_lengkap }} - {{ reportCard.class.nama_lengkap }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <ReportCardStatusBadge :status="reportCard.status" size="md" />
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Quick Info Cards -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
            >
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <User class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">NIS</p>
                                <p class="font-semibold text-slate-900 dark:text-slate-100">
                                    {{ reportCard.student.nis }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                <GraduationCap class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Kelas</p>
                                <p class="font-semibold text-slate-900 dark:text-slate-100">
                                    {{ reportCard.class.nama_lengkap }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <Calendar class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Semester</p>
                                <p class="font-semibold text-slate-900 dark:text-slate-100">
                                    {{ reportCard.academic.semester_label }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <Award class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Ranking</p>
                                <p class="font-semibold text-slate-900 dark:text-slate-100">
                                    #{{ reportCard.overall.rank }} / {{ reportCard.overall.total_students }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Actions -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-wrap items-center gap-2">
                        <Motion v-if="reportCard.has_pdf" :whileTap="{ scale: 0.97 }">
                            <a
                                :href="`/admin/report-cards/${reportCard.id}/download`"
                                target="_blank"
                                class="flex items-center gap-2 px-4 py-2.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors font-medium text-sm"
                            >
                                <Download class="w-4 h-4" />
                                Download PDF
                            </a>
                        </Motion>

                        <Motion v-if="reportCard.is_editable" :whileTap="{ scale: 0.97 }">
                            <button
                                type="button"
                                @click="handleRegenerate"
                                class="flex items-center gap-2 px-4 py-2.5 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-colors font-medium text-sm"
                            >
                                <RefreshCw class="w-4 h-4" />
                                Regenerate PDF
                            </button>
                        </Motion>

                        <Motion v-if="reportCard.is_editable" :whileTap="{ scale: 0.97 }">
                            <button
                                type="button"
                                @click="handleUnlock"
                                class="flex items-center gap-2 px-4 py-2.5 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors font-medium text-sm"
                            >
                                <Unlock class="w-4 h-4" />
                                Unlock untuk Koreksi
                            </button>
                        </Motion>
                    </div>
                </div>
            </Motion>

            <!-- Report Card Preview -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
            >
                <ReportCardPreview :data="reportCard" />
            </Motion>
        </div>
    </AppLayout>
</template>
