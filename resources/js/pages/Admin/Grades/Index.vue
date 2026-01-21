<script setup lang="ts">
/**
 * Admin Grades Index Page - Halaman rekap semua nilai
 * dengan filter berdasarkan tahun ajaran, semester, kelas, dan mapel
 */
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import {
    GraduationCap,
    Filter,
    Search,
    FileSpreadsheet,
    TrendingUp,
    Users,
    BookOpen,
    BarChart3,
    X
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import GradePredikatBadge from '@/components/features/grades/GradePredikatBadge.vue';
import GradeDistributionChart from '@/components/features/grades/GradeDistributionChart.vue';
import { index, summary } from '@/routes/admin/grades';

interface GradeEntry {
    class_id: number;
    class_name: string;
    subject_id: number;
    subject_name: string;
    teacher_name: string;
    semester: string;
    assessment_type: string;
    assessment_type_label: string;
    title: string;
    student_count: number;
    average_score: number;
    last_updated: string;
}

interface PaginatedGrades {
    data: GradeEntry[];
    links: any[];
    meta?: any;
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface ClassOption {
    id: number;
    name: string;
}

interface SubjectOption {
    id: number;
    name: string;
}

interface Statistics {
    total_grades: number;
    average_score: number;
    class_count: number;
    subject_count: number;
    distribution: {
        A: number;
        B: number;
        C: number;
        D: number;
    };
}

interface Props {
    grades: PaginatedGrades;
    filters: {
        tahun_ajaran: string;
        semester: string | null;
        class_id: number | null;
        subject_id: number | null;
        search: string | null;
    };
    classes: ClassOption[];
    subjects: SubjectOption[];
    assessmentTypes: Record<string, string>;
    statistics: Statistics;
    availableTahunAjaran: string[];
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

const showFilters = ref(false);
const localFilters = ref({
    tahun_ajaran: props.filters.tahun_ajaran,
    semester: props.filters.semester || '',
    class_id: props.filters.class_id?.toString() || '',
    subject_id: props.filters.subject_id?.toString() || '',
    search: props.filters.search || ''
});

/**
 * Apply filters
 */
const applyFilters = () => {
    haptics.light();

    const params: Record<string, any> = {
        tahun_ajaran: localFilters.value.tahun_ajaran
    };

    if (localFilters.value.semester) params.semester = localFilters.value.semester;
    if (localFilters.value.class_id) params.class_id = localFilters.value.class_id;
    if (localFilters.value.subject_id) params.subject_id = localFilters.value.subject_id;
    if (localFilters.value.search) params.search = localFilters.value.search;

    router.visit(index.url(params), {
        preserveState: true,
        preserveScroll: true
    });
};

/**
 * Reset filters
 */
const resetFilters = () => {
    haptics.light();
    localFilters.value = {
        tahun_ajaran: props.filters.tahun_ajaran,
        semester: '',
        class_id: '',
        subject_id: '',
        search: ''
    };
    router.visit(index.url({ tahun_ajaran: localFilters.value.tahun_ajaran }), {
        preserveState: true,
        preserveScroll: true
    });
};

/**
 * Check if any filter is active
 */
const hasActiveFilters = computed(() => {
    return localFilters.value.semester ||
        localFilters.value.class_id ||
        localFilters.value.subject_id ||
        localFilters.value.search;
});

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
 * Handle pagination
 */
const goToPage = (url: string | null) => {
    if (!url) return;
    haptics.light();
    router.visit(url, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <AppLayout>
        <Head title="Rekap Nilai" />

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
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/25 shrink-0">
                                <GraduationCap class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Rekap Nilai
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Lihat semua penilaian yang telah diinput guru
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Motion :whileTap="{ scale: 0.97 }">
                                <Link
                                    :href="summary({ tahun_ajaran: filters.tahun_ajaran, semester: filters.semester || '1' })"
                                    @click="haptics.light()"
                                    class="flex items-center gap-2 px-4 py-2.5 min-h-[44px] bg-linear-to-r from-emerald-500 to-teal-500 text-white rounded-xl hover:from-emerald-600 hover:to-teal-600 transition-all duration-200 shadow-lg shadow-emerald-500/30"
                                >
                                    <FileSpreadsheet class="w-5 h-5" />
                                    <span class="font-semibold hidden sm:inline">Lihat Summary</span>
                                    <span class="font-semibold sm:hidden">Summary</span>
                                </Link>
                            </Motion>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Statistics Cards -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
            >
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <BarChart3 class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ statistics.total_grades.toLocaleString() }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Total Nilai</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <TrendingUp class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ statistics.average_score.toFixed(1) }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Rata-rata</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                                <Users class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ statistics.class_count }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Kelas</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <BookOpen class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ statistics.subject_count }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Mapel</p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Filters -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <!-- Filter Toggle -->
                    <button
                        type="button"
                        @click="showFilters = !showFilters"
                        class="w-full px-4 sm:px-6 py-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <Filter class="w-5 h-5 text-slate-500" />
                            <span class="font-medium text-slate-700 dark:text-slate-300">Filter</span>
                            <span
                                v-if="hasActiveFilters"
                                class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400"
                            >
                                Aktif
                            </span>
                        </div>
                        <svg
                            :class="['w-5 h-5 text-slate-400 transition-transform', showFilters && 'rotate-180']"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Filter Content -->
                    <div v-show="showFilters" class="px-4 sm:px-6 pb-4 border-t border-slate-100 dark:border-zinc-800 pt-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                    Tahun Ajaran
                                </label>
                                <select
                                    v-model="localFilters.tahun_ajaran"
                                    class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                                    class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">Semua Semester</option>
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
                                    class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">Semua Kelas</option>
                                    <option
                                        v-for="cls in classes"
                                        :key="cls.id"
                                        :value="cls.id.toString()"
                                    >
                                        {{ cls.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                    Mata Pelajaran
                                </label>
                                <select
                                    v-model="localFilters.subject_id"
                                    class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">Semua Mapel</option>
                                    <option
                                        v-for="subj in subjects"
                                        :key="subj.id"
                                        :value="subj.id.toString()"
                                    >
                                        {{ subj.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="flex items-end gap-2">
                                <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                                    <button
                                        type="button"
                                        @click="applyFilters"
                                        class="w-full px-4 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors font-medium text-sm"
                                    >
                                        <Search class="w-4 h-4 inline-block mr-1" />
                                        Cari
                                    </button>
                                </Motion>
                                <Motion v-if="hasActiveFilters" :whileTap="{ scale: 0.97 }">
                                    <button
                                        type="button"
                                        @click="resetFilters"
                                        class="p-2.5 border border-slate-200 dark:border-zinc-700 rounded-lg hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors"
                                        title="Reset Filter"
                                    >
                                        <X class="w-4 h-4 text-slate-500" />
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Grade Distribution Chart -->
            <Motion
                v-if="statistics.total_grades > 0"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 p-4 sm:p-6">
                    <h2 class="font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Distribusi Predikat
                    </h2>
                    <GradeDistributionChart :distribution="statistics.distribution" />
                </div>
            </Motion>

            <!-- Table -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-zinc-800/50">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Kelas
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Mata Pelajaran
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Guru
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Semester
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Jenis
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Judul
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Siswa
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Rata-rata
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Terakhir Update
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                <tr v-if="grades.data.length === 0">
                                    <td colspan="9" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <GraduationCap class="w-12 h-12 text-slate-300 dark:text-zinc-600" />
                                            <p class="text-slate-500 dark:text-slate-400">
                                                Belum ada data nilai untuk ditampilkan
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                                <tr
                                    v-for="grade in grades.data"
                                    :key="`${grade.class_id}-${grade.subject_id}-${grade.assessment_type}-${grade.title}`"
                                    class="hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-colors"
                                >
                                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                        {{ grade.class_name }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                        {{ grade.subject_name }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                        {{ grade.teacher_name }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400 text-xs font-medium">
                                            {{ grade.semester }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs font-medium">
                                            {{ grade.assessment_type_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400 max-w-[200px] truncate">
                                        {{ grade.title }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-400">
                                        {{ grade.student_count }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="font-semibold text-slate-900 dark:text-slate-100">
                                                {{ grade.average_score }}
                                            </span>
                                            <GradePredikatBadge :predikat="getPredikat(grade.average_score)" size="sm" />
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                                        {{ grade.last_updated }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="grades.last_page > 1"
                        class="px-4 py-3 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between"
                    >
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Menampilkan {{ grades.data.length }} dari {{ grades.total }} data
                        </p>
                        <div class="flex items-center gap-1">
                            <button
                                v-for="link in grades.links"
                                :key="link.label"
                                @click="goToPage(link.url)"
                                :disabled="!link.url"
                                :class="[
                                    'px-3 py-1.5 text-sm rounded-lg transition-colors',
                                    link.active
                                        ? 'bg-blue-500 text-white'
                                        : link.url
                                            ? 'hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-600 dark:text-slate-400'
                                            : 'text-slate-300 dark:text-zinc-600 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
