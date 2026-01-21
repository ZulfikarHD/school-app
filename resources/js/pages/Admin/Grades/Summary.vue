<script setup lang="ts">
/**
 * Admin Grades Summary Page - Halaman rekap nilai per kelas
 * dengan tabel lengkap dan fitur export Excel
 */
import { ref, computed, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import {
    FileSpreadsheet,
    Download,
    ChevronLeft,
    Users,
    GraduationCap,
    ArrowUpDown
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import GradeSummaryTable from '@/components/features/grades/GradeSummaryTable.vue';
import GradeDistributionChart from '@/components/features/grades/GradeDistributionChart.vue';
import { index, summary, exportMethod } from '@/routes/admin/grades';

interface SubjectGrade {
    subject_id: number;
    subject_name: string;
    subject_code: string;
    final_grade: number;
    predikat: string;
    predikat_label: string;
    breakdown: {
        uh: { average: number; weight: number; weighted: number; count: number };
        uts: { score: number; weight: number; weighted: number };
        uas: { score: number; weight: number; weighted: number };
        praktik: { average: number; weight: number; weighted: number; count: number };
    };
}

interface StudentData {
    id: number;
    nis: string;
    name: string;
    subjects: Record<number, SubjectGrade>;
    overall_average: number;
    rank: number | null;
}

interface SubjectInfo {
    id: number;
    code: string;
    name: string;
}

interface ClassStatistics {
    average: number;
    predikat: string;
    student_count: number;
    grade_distribution: {
        A: number;
        B: number;
        C: number;
        D: number;
    };
}

interface SummaryData {
    class: {
        id: number;
        name: string;
        wali_kelas: string | null;
    };
    students: StudentData[];
    subjects: SubjectInfo[];
    statistics: Record<number, ClassStatistics>;
}

interface ClassOption {
    id: number;
    name: string;
    wali_kelas: string;
    student_count: number;
}

interface Props {
    summary: SummaryData | null;
    filters: {
        tahun_ajaran: string;
        semester: string;
        class_id: number | null;
    };
    classes: ClassOption[];
    availableTahunAjaran: string[];
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();
const loading = ref(false);

const localFilters = ref({
    tahun_ajaran: props.filters.tahun_ajaran,
    semester: props.filters.semester || '1',
    class_id: props.filters.class_id?.toString() || ''
});

/**
 * Computed: Overall distribution dari semua mapel
 */
const overallDistribution = computed(() => {
    if (!props.summary?.statistics) {
        return { A: 0, B: 0, C: 0, D: 0 };
    }

    const total = { A: 0, B: 0, C: 0, D: 0 };
    Object.values(props.summary.statistics).forEach(stat => {
        total.A += stat.grade_distribution.A;
        total.B += stat.grade_distribution.B;
        total.C += stat.grade_distribution.C;
        total.D += stat.grade_distribution.D;
    });

    return total;
});

/**
 * Apply filters
 */
const applyFilters = () => {
    if (!localFilters.value.class_id) {
        modal.error('Pilih kelas terlebih dahulu');
        return;
    }

    haptics.light();
    loading.value = true;

    router.visit(summary.url({
        tahun_ajaran: localFilters.value.tahun_ajaran,
        semester: localFilters.value.semester,
        class_id: localFilters.value.class_id
    }), {
        preserveState: false,
        onFinish: () => {
            loading.value = false;
        }
    });
};

/**
 * Handle export to Excel
 */
const handleExport = () => {
    if (!props.summary) {
        modal.error('Tidak ada data untuk di-export');
        return;
    }

    haptics.medium();

    const url = exportMethod.url({
        tahun_ajaran: props.filters.tahun_ajaran,
        semester: props.filters.semester,
        class_id: props.filters.class_id
    });

    // Open in new tab for download
    window.open(url, '_blank');
};

/**
 * Watch class selection untuk auto-apply
 */
watch(() => localFilters.value.class_id, (newVal) => {
    if (newVal && newVal !== props.filters.class_id?.toString()) {
        applyFilters();
    }
});

/**
 * Format class option label
 */
const formatClassOption = (cls: ClassOption): string => {
    return `${cls.name} (${cls.student_count} siswa)`;
};
</script>

<template>
    <AppLayout>
        <Head title="Summary Nilai" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
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
                                :href="index({ tahun_ajaran: filters.tahun_ajaran })"
                                class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                            >
                                <ChevronLeft class="w-5 h-5 text-slate-500" />
                            </Link>
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-emerald-400 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/25 shrink-0">
                                <FileSpreadsheet class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Summary Nilai
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Rekap nilai per kelas dengan detail komponen
                                </p>
                            </div>
                        </div>

                        <div v-if="summary" class="flex items-center gap-2">
                            <Motion :whileTap="{ scale: 0.97 }">
                                <button
                                    type="button"
                                    @click="handleExport"
                                    class="flex items-center gap-2 px-4 py-2.5 min-h-[44px] bg-linear-to-r from-green-500 to-emerald-500 text-white rounded-xl hover:from-green-600 hover:to-emerald-600 transition-all duration-200 shadow-lg shadow-green-500/30"
                                >
                                    <Download class="w-5 h-5" />
                                    <span class="font-semibold hidden sm:inline">Export Excel</span>
                                    <span class="font-semibold sm:hidden">Export</span>
                                </button>
                            </Motion>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Filters -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                Tahun Ajaran
                            </label>
                            <select
                                v-model="localFilters.tahun_ajaran"
                                @change="applyFilters"
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                                <option
                                    v-for="ta in availableTahunAjaran"
                                    :key="ta"
                                    :value="ta"
                                >
                                    {{ ta }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                Semester
                            </label>
                            <select
                                v-model="localFilters.semester"
                                @change="applyFilters"
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                Kelas
                            </label>
                            <select
                                v-model="localFilters.class_id"
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                                <option value="">Pilih Kelas</option>
                                <option
                                    v-for="cls in classes"
                                    :key="cls.id"
                                    :value="cls.id.toString()"
                                >
                                    {{ formatClassOption(cls) }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- No Class Selected State -->
            <Motion
                v-if="!summary"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-12 shadow-sm border border-slate-200 dark:border-zinc-800 text-center">
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                            <Users class="w-8 h-8 text-slate-400 dark:text-zinc-500" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                Pilih Kelas
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                Pilih kelas untuk melihat rekap nilai siswa
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Summary Content -->
            <template v-else>
                <!-- Class Info Card -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                    <GraduationCap class="w-7 h-7 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                        Kelas {{ summary.class.name }}
                                    </h2>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        Wali Kelas: {{ summary.class.wali_kelas || '-' }}
                                    </p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        Semester {{ filters.semester }} - {{ filters.tahun_ajaran }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-6">
                                <div class="text-center">
                                    <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ summary.students.length }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Siswa</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                        {{ summary.subjects.length }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Mapel</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Distribution Chart -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-4">
                            Distribusi Predikat Keseluruhan
                        </h3>
                        <GradeDistributionChart :distribution="overallDistribution" />
                    </div>
                </Motion>

                <!-- Summary Table -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                        <div class="px-4 sm:px-6 py-4 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100">
                                Rekap Nilai Siswa
                            </h3>
                            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                <ArrowUpDown class="w-4 h-4" />
                                <span>Klik baris untuk detail</span>
                            </div>
                        </div>

                        <div class="p-4 sm:p-6">
                            <GradeSummaryTable
                                :students="summary.students"
                                :subjects="summary.subjects"
                                :loading="loading"
                            />
                        </div>
                    </div>
                </Motion>
            </template>
        </div>
    </AppLayout>
</template>
