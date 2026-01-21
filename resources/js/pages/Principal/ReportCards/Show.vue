<script setup lang="ts">
/**
 * Principal Report Card Show - Preview dan approval rapor siswa
 * dengan aksi approve, reject, dan navigasi antar siswa dalam kelas
 */
import { ref } from 'vue';
import { Head, router, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import {
    ArrowLeft,
    CheckCircle,
    XCircle,
    User,
    GraduationCap,
    Calendar,
    Award,
    ChevronLeft,
    ChevronRight
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import ReportCardPreview from '@/components/features/grades/ReportCardPreview.vue';
import ReportCardStatusBadge from '@/components/features/grades/ReportCardStatusBadge.vue';
import { index, show, approve, reject } from '@/routes/principal/report-cards';

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

interface ClassmateReportCard {
    id: number;
    student_name: string;
    student_nis: string;
    status: string;
    status_label: string;
    is_current: boolean;
}

interface Props {
    reportCard: {
        id: number;
        status: string;
        status_label: string;
        status_color: string;
        is_approvable: boolean;
        approval_notes: string | null;
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
    classmateReportCards: ClassmateReportCard[];
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

const showRejectModal = ref(false);
const rejectForm = useForm({
    notes: ''
});

/**
 * Handle approve rapor
 */
const handleApprove = async () => {
    const confirmed = await modal.dialog({
        type: 'info',
        icon: 'question',
        title: 'Approve Rapor',
        message: `Rapor ${props.reportCard.student.nama_lengkap} akan di-approve dan dirilis ke orang tua. Lanjutkan?`,
        confirmText: 'Ya, Approve',
        cancelText: 'Batal',
        showCancel: true
    });

    if (!confirmed) return;

    haptics.medium();
    router.post(approve(props.reportCard.id).url, {}, {
        preserveScroll: true
    });
};

/**
 * Show reject modal
 */
const openRejectModal = () => {
    haptics.light();
    rejectForm.reset();
    showRejectModal.value = true;
};

/**
 * Handle reject rapor
 */
const handleReject = () => {
    if (!rejectForm.notes.trim()) {
        modal.dialog({
            type: 'warning',
            icon: 'warning',
            title: 'Catatan Diperlukan',
            message: 'Masukkan alasan penolakan untuk wali kelas.',
            confirmText: 'Mengerti'
        });
        return;
    }

    haptics.medium();
    rejectForm.post(reject(props.reportCard.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
        }
    });
};

/**
 * Find previous/next report card untuk navigasi
 */
const currentIndex = props.classmateReportCards.findIndex(rc => rc.is_current);
const prevReportCard = currentIndex > 0 ? props.classmateReportCards[currentIndex - 1] : null;
const nextReportCard = currentIndex < props.classmateReportCards.length - 1 ? props.classmateReportCards[currentIndex + 1] : null;
</script>

<template>
    <AppLayout>
        <Head :title="`Review Rapor - ${reportCard.student.nama_lengkap}`" />

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
                                    Review Rapor
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

            <!-- Approval Notes (if rejected) -->
            <Motion
                v-if="reportCard.approval_notes && reportCard.status === 'DRAFT'"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.08 }"
            >
                <div class="bg-red-50 dark:bg-red-900/20 rounded-2xl p-4 border border-red-200 dark:border-red-800/50">
                    <p class="text-sm font-medium text-red-800 dark:text-red-300 mb-2">
                        Catatan Penolakan Sebelumnya:
                    </p>
                    <p class="text-sm text-red-700 dark:text-red-400">
                        {{ reportCard.approval_notes }}
                    </p>
                </div>
            </Motion>

            <!-- Actions -->
            <Motion
                v-if="reportCard.is_approvable"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-wrap items-center gap-2">
                        <Motion :whileTap="{ scale: 0.97 }">
                            <button
                                type="button"
                                @click="handleApprove"
                                class="flex items-center gap-2 px-4 py-2.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors font-medium text-sm"
                            >
                                <CheckCircle class="w-4 h-4" />
                                Approve & Rilis
                            </button>
                        </Motion>

                        <Motion :whileTap="{ scale: 0.97 }">
                            <button
                                type="button"
                                @click="openRejectModal"
                                class="flex items-center gap-2 px-4 py-2.5 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors font-medium text-sm"
                            >
                                <XCircle class="w-4 h-4" />
                                Tolak
                            </button>
                        </Motion>
                    </div>
                </div>
            </Motion>

            <!-- Navigation -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.12 }"
            >
                <div class="flex items-center justify-between">
                    <Link
                        v-if="prevReportCard"
                        :href="show(prevReportCard.id)"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors text-sm"
                    >
                        <ChevronLeft class="w-4 h-4" />
                        <span class="hidden sm:inline">{{ prevReportCard.student_name }}</span>
                        <span class="sm:hidden">Sebelumnya</span>
                    </Link>
                    <div v-else />

                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ currentIndex + 1 }} / {{ classmateReportCards.length }}
                    </p>

                    <Link
                        v-if="nextReportCard"
                        :href="show(nextReportCard.id)"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors text-sm"
                    >
                        <span class="hidden sm:inline">{{ nextReportCard.student_name }}</span>
                        <span class="sm:hidden">Selanjutnya</span>
                        <ChevronRight class="w-4 h-4" />
                    </Link>
                    <div v-else />
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

        <!-- Reject Modal -->
        <Teleport to="body">
            <div
                v-if="showRejectModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <div
                    class="absolute inset-0 bg-black/50"
                    @click="showRejectModal = false"
                />
                <Motion
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :transition="{ duration: 0.2 }"
                    class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-xl max-w-md w-full p-6"
                >
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Tolak Rapor
                    </h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                        Masukkan alasan penolakan. Catatan ini akan dikirim ke wali kelas untuk diperbaiki.
                    </p>

                    <textarea
                        v-model="rejectForm.notes"
                        rows="4"
                        class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"
                        placeholder="Contoh: Nilai sikap belum lengkap, mohon dilengkapi deskripsi sikap sosial."
                    />
                    <p
                        v-if="rejectForm.errors.notes"
                        class="mt-1 text-xs text-red-500"
                    >
                        {{ rejectForm.errors.notes }}
                    </p>

                    <div class="flex items-center justify-end gap-2 mt-6">
                        <button
                            type="button"
                            @click="showRejectModal = false"
                            class="px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="handleReject"
                            :disabled="rejectForm.processing"
                            class="px-4 py-2.5 text-sm font-medium bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors disabled:opacity-50"
                        >
                            {{ rejectForm.processing ? 'Menolak...' : 'Tolak Rapor' }}
                        </button>
                    </div>
                </Motion>
            </div>
        </Teleport>
    </AppLayout>
</template>
