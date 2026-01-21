<script setup lang="ts">
/**
 * Teacher Report Card Show Page - Preview rapor dan input catatan wali kelas
 * dengan form textarea untuk catatan dan aksi submit approval
 */
import { ref, computed } from 'vue';
import { Head, router, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import {
    FileText,
    ArrowLeft,
    Download,
    Send,
    Save,
    User,
    GraduationCap,
    Calendar,
    Award,
    MessageSquare,
    Lock
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import ReportCardPreview from '@/components/features/grades/ReportCardPreview.vue';
import ReportCardStatusBadge from '@/components/features/grades/ReportCardStatusBadge.vue';
import HomeroomNotesForm from '@/components/features/grades/HomeroomNotesForm.vue';
import { index, submit } from '@/routes/teacher/report-cards';
import { update as notesUpdate } from '@/routes/teacher/report-cards/notes';

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
    title: string;
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
    attitudeGradeId: number | null;
    currentNotes: string;
    classData: {
        id: number;
        nama_lengkap: string;
    };
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

// Form untuk catatan
const notesForm = useForm({
    homeroom_notes: props.currentNotes
});

// Track if notes changed
const notesChanged = computed(() => {
    return notesForm.homeroom_notes !== props.currentNotes;
});

/**
 * Handle save notes
 */
const handleSaveNotes = () => {
    haptics.light();
    notesForm.put(notesUpdate(props.reportCard.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
        }
    });
};

/**
 * Handle submit for approval
 */
const handleSubmitForApproval = async () => {
    // Check if notes are empty
    if (!notesForm.homeroom_notes.trim()) {
        const confirmed = await modal.dialog({
            type: 'warning',
            icon: 'warning',
            title: 'Catatan Kosong',
            message: 'Anda belum mengisi catatan wali kelas. Yakin ingin melanjutkan submit?',
            confirmText: 'Ya, Submit Tanpa Catatan',
            cancelText: 'Isi Catatan Dulu',
            showCancel: true
        });

        if (!confirmed) return;
    }

    const confirmed = await modal.dialog({
        type: 'info',
        icon: 'question',
        title: 'Submit untuk Persetujuan',
        message: `Rapor <b>${props.reportCard.student.nama_lengkap}</b> akan disubmit untuk persetujuan kepala sekolah. Setelah disubmit, rapor tidak dapat diubah lagi. Lanjutkan?`,
        confirmText: 'Ya, Submit',
        cancelText: 'Batal',
        showCancel: true
    });

    if (!confirmed) return;

    haptics.medium();
    router.post(submit(props.reportCard.id).url, {}, {
        preserveScroll: true
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

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
                                    Rapor {{ reportCard.student.nama_lengkap }}
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ reportCard.class.nama_lengkap }} - {{ reportCard.academic.semester_label }}
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
                            <div class="w-10 h-10 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                                <GraduationCap class="w-5 h-5 text-violet-600 dark:text-violet-400" />
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

            <!-- Homeroom Notes Form -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                            <MessageSquare class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                        </div>
                        <div>
                            <h2 class="font-semibold text-slate-900 dark:text-slate-100">
                                Catatan Wali Kelas
                            </h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Tulis catatan untuk orang tua/wali siswa
                            </p>
                        </div>
                    </div>

                    <div v-if="reportCard.is_editable">
                        <HomeroomNotesForm
                            v-model="notesForm.homeroom_notes"
                            :max-length="500"
                            :error="notesForm.errors.homeroom_notes"
                        />

                        <div class="flex items-center justify-between mt-4">
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ notesForm.homeroom_notes.length }}/500 karakter
                            </p>

                            <div class="flex items-center gap-2">
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        type="button"
                                        @click="handleSaveNotes"
                                        :disabled="!notesChanged || notesForm.processing"
                                        :class="[
                                            'flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium text-sm transition-colors',
                                            notesChanged && !notesForm.processing
                                                ? 'bg-violet-500 text-white hover:bg-violet-600'
                                                : 'bg-slate-100 dark:bg-zinc-800 text-slate-400 cursor-not-allowed'
                                        ]"
                                    >
                                        <Save class="w-4 h-4" />
                                        Simpan Catatan
                                    </button>
                                </Motion>

                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        type="button"
                                        @click="handleSubmitForApproval"
                                        class="flex items-center gap-2 px-4 py-2.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors font-medium text-sm"
                                    >
                                        <Send class="w-4 h-4" />
                                        Submit untuk Approval
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </div>

                    <div v-else class="p-4 bg-slate-50 dark:bg-zinc-800 rounded-xl">
                        <div class="flex items-center gap-3 mb-3">
                            <Lock class="w-5 h-5 text-slate-400" />
                            <span class="text-sm font-medium text-slate-600 dark:text-slate-400">
                                Rapor sudah disubmit dan tidak dapat diubah
                            </span>
                        </div>
                        <div
                            v-if="reportCard.attitude?.homeroom_notes"
                            class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap"
                        >
                            {{ reportCard.attitude.homeroom_notes }}
                        </div>
                        <p
                            v-else
                            class="text-slate-500 dark:text-slate-400 italic"
                        >
                            Tidak ada catatan
                        </p>
                    </div>
                </div>
            </Motion>

            <!-- Download PDF Button -->
            <Motion
                v-if="reportCard.has_pdf"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <FileText class="w-5 h-5 text-slate-400" />
                            <span class="text-sm text-slate-600 dark:text-slate-400">
                                File PDF tersedia untuk didownload
                            </span>
                        </div>
                        <Motion :whileTap="{ scale: 0.97 }">
                            <a
                                :href="`/teacher/report-cards/${reportCard.id}/download`"
                                target="_blank"
                                class="flex items-center gap-2 px-4 py-2.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors font-medium text-sm"
                            >
                                <Download class="w-4 h-4" />
                                Download PDF
                            </a>
                        </Motion>
                    </div>
                </div>
            </Motion>

            <!-- Report Card Preview -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
            >
                <ReportCardPreview :data="reportCard" />
            </Motion>
        </div>
    </AppLayout>
</template>
