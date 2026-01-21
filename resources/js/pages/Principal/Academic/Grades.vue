<script setup lang="ts">
/**
 * Principal Academic Grades - Rekap nilai semua kelas
 * dengan overview dan drill-down ke detail per siswa
 */
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import {
    BookOpen,
    Users,
    Award,
    Filter,
    ChevronDown,
    ChevronRight,
    GraduationCap,
    ArrowLeft
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { grades, dashboard } from '@/routes/principal/academic';

interface PredikatDistribution {
    A: number;
    B: number;
    C: number;
    D: number;
}

interface ClassOverview {
    class_id: number;
    class_name: string;
    wali_kelas: string;
    student_count: number;
    average: number;
    highest: number;
    lowest: number;
    predikat_distribution: PredikatDistribution;
}

interface StudentGrade {
    subject_id: number;
    subject_name: string;
    final_grade: number;
    predikat: string;
}

interface StudentData {
    id: number;
    nis: string;
    name: string;
    subjects: Record<number, StudentGrade>;
    overall_average: number;
    rank: number | null;
}

interface ClassDetailSummary {
    class: {
        id: number;
        name: string;
        wali_kelas: string | null;
    };
    students: StudentData[];
    subjects: { id: number; code: string; name: string }[];
    statistics: Record<number, any>;
}

interface ClassOption {
    id: number;
    name: string;
    tingkat: number;
    student_count: number;
    wali_kelas: string;
}

interface Props {
    title: string;
    filters: {
        tahun_ajaran: string;
        semester: string;
        class_id: number | null;
    };
    classes: ClassOption[];
    classSummaries: (ClassOverview | ClassDetailSummary)[];
    isDetailView: boolean;
    availableTahunAjaran: string[];
    semesters: { value: string; label: string }[];
}

const props = defineProps<Props>();

const haptics = useHaptics();

const localFilters = ref({
    tahun_ajaran: props.filters.tahun_ajaran,
    semester: props.filters.semester,
    class_id: props.filters.class_id?.toString() || ''
});

const expandedClasses = ref<Set<number>>(new Set());

/**
 * Apply filters
 */
const applyFilters = () => {
    haptics.light();

    const params: Record<string, any> = {
        tahun_ajaran: localFilters.value.tahun_ajaran,
        semester: localFilters.value.semester
    };

    if (localFilters.value.class_id) params.class_id = localFilters.value.class_id;

    router.visit(grades.url(params), {
        preserveState: true,
        preserveScroll: true
    });
};

/**
 * Toggle class expansion
 */
const toggleClass = (classId: number) => {
    haptics.light();
    if (expandedClasses.value.has(classId)) {
        expandedClasses.value.delete(classId);
    } else {
        expandedClasses.value.add(classId);
    }
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

/**
 * Get predikat bg color
 */
const getPredikatBgColor = (predikat: string): string => {
    const colors: Record<string, string> = {
        'A': 'bg-emerald-100 dark:bg-emerald-900/30',
        'B': 'bg-blue-100 dark:bg-blue-900/30',
        'C': 'bg-amber-100 dark:bg-amber-900/30',
        'D': 'bg-red-100 dark:bg-red-900/30'
    };
    return colors[predikat] || colors['D'];
};

/**
 * Get average color
 */
const getAverageColor = (average: number): string => {
    if (average >= 90) return 'text-emerald-600 dark:text-emerald-400';
    if (average >= 80) return 'text-blue-600 dark:text-blue-400';
    if (average >= 75) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
};

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
 * Check if summary is detail view
 */
const isClassDetail = (summary: ClassOverview | ClassDetailSummary): summary is ClassDetailSummary => {
    return 'students' in summary;
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

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
                                v-if="isDetailView"
                                :href="grades({ tahun_ajaran: filters.tahun_ajaran, semester: filters.semester })"
                                class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                            >
                                <ArrowLeft class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                            </Link>
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-blue-400 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/25 shrink-0">
                                <BookOpen class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Rekap Nilai
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ isDetailView ? 'Detail nilai per siswa' : 'Overview semua kelas' }}
                                </p>
                            </div>
                        </div>

                        <Link
                            :href="dashboard({ tahun_ajaran: filters.tahun_ajaran, semester: filters.semester })"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors"
                        >
                            Lihat Dashboard
                        </Link>
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
                    <div class="flex items-center gap-3 mb-4">
                        <Filter class="w-5 h-5 text-slate-500" />
                        <span class="font-medium text-slate-700 dark:text-slate-300">Filter</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                Tahun Ajaran
                            </label>
                            <select
                                v-model="localFilters.tahun_ajaran"
                                @change="applyFilters"
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
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
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <option
                                    v-for="sem in semesters"
                                    :key="sem.value"
                                    :value="sem.value"
                                >
                                    {{ sem.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                Kelas
                            </label>
                            <select
                                v-model="localFilters.class_id"
                                @change="applyFilters"
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="">Semua Kelas (Overview)</option>
                                <option
                                    v-for="cls in classes"
                                    :key="cls.id"
                                    :value="cls.id.toString()"
                                >
                                    {{ cls.name }} - {{ cls.wali_kelas }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Overview Mode: Class Cards -->
            <template v-if="!isDetailView">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Motion
                        v-for="(summary, idx) in classSummaries"
                        :key="(summary as ClassOverview).class_id"
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 + idx * 0.05 }"
                    >
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors cursor-pointer"
                            @click="localFilters.class_id = (summary as ClassOverview).class_id.toString(); applyFilters()"
                        >
                            <div class="p-4 sm:p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                            <GraduationCap class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-slate-900 dark:text-slate-100">
                                                {{ (summary as ClassOverview).class_name }}
                                            </h3>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                                {{ (summary as ClassOverview).wali_kelas }}
                                            </p>
                                        </div>
                                    </div>
                                    <ChevronRight class="w-5 h-5 text-slate-400" />
                                </div>

                                <div class="grid grid-cols-3 gap-4 mb-4">
                                    <div class="text-center">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Siswa</p>
                                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100">
                                            {{ (summary as ClassOverview).student_count }}
                                        </p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Rata-rata</p>
                                        <p :class="['text-lg font-bold', getAverageColor((summary as ClassOverview).average)]">
                                            {{ (summary as ClassOverview).average.toFixed(1) }}
                                        </p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Range</p>
                                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            {{ (summary as ClassOverview).lowest.toFixed(0) }} - {{ (summary as ClassOverview).highest.toFixed(0) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Predikat Distribution Mini -->
                                <div class="flex items-center gap-2">
                                    <div
                                        v-for="pred in ['A', 'B', 'C', 'D']"
                                        :key="pred"
                                        class="flex items-center gap-1"
                                    >
                                        <span :class="['w-5 h-5 rounded flex items-center justify-center text-xs font-bold', getPredikatBgColor(pred), getPredikatColor(pred)]">
                                            {{ pred }}
                                        </span>
                                        <span class="text-xs text-slate-600 dark:text-slate-400">
                                            {{ (summary as ClassOverview).predikat_distribution[pred as keyof PredikatDistribution] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Motion>
                </div>

                <div v-if="classSummaries.length === 0" class="text-center py-12">
                    <BookOpen class="w-12 h-12 text-slate-300 dark:text-zinc-600 mx-auto mb-4" />
                    <p class="text-slate-500 dark:text-slate-400">Belum ada data nilai</p>
                </div>
            </template>

            <!-- Detail Mode: Student Table -->
            <template v-else>
                <Motion
                    v-for="summary in classSummaries"
                    :key="(summary as ClassDetailSummary).class.id"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                        <div class="p-4 sm:p-6 border-b border-slate-100 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                    <GraduationCap class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900 dark:text-slate-100">
                                        {{ (summary as ClassDetailSummary).class.name }}
                                    </h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        Wali Kelas: {{ (summary as ClassDetailSummary).class.wali_kelas || '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-zinc-800/50">
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 sticky left-0 bg-slate-50 dark:bg-zinc-800/50">
                                            #
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 sticky left-8 bg-slate-50 dark:bg-zinc-800/50 min-w-[150px]">
                                            Siswa
                                        </th>
                                        <th
                                            v-for="subject in (summary as ClassDetailSummary).subjects"
                                            :key="subject.id"
                                            class="px-3 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 min-w-[80px]"
                                            :title="subject.name"
                                        >
                                            {{ subject.code || subject.name.substring(0, 4) }}
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">
                                            Rata-rata
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                    <tr
                                        v-for="student in (summary as ClassDetailSummary).students"
                                        :key="student.id"
                                        class="hover:bg-slate-50 dark:hover:bg-zinc-800/30"
                                    >
                                        <td class="px-4 py-3 text-slate-500 sticky left-0 bg-white dark:bg-zinc-900">
                                            {{ student.rank || '-' }}
                                        </td>
                                        <td class="px-4 py-3 sticky left-8 bg-white dark:bg-zinc-900">
                                            <p class="font-medium text-slate-900 dark:text-slate-100">
                                                {{ student.name }}
                                            </p>
                                            <p class="text-xs text-slate-500">{{ student.nis }}</p>
                                        </td>
                                        <td
                                            v-for="subject in (summary as ClassDetailSummary).subjects"
                                            :key="subject.id"
                                            class="px-3 py-3 text-center"
                                        >
                                            <template v-if="student.subjects[subject.id]">
                                                <span class="font-semibold text-slate-900 dark:text-slate-100">
                                                    {{ Math.round(student.subjects[subject.id].final_grade) }}
                                                </span>
                                                <span :class="['text-xs ml-1', getPredikatColor(student.subjects[subject.id].predikat)]">
                                                    ({{ student.subjects[subject.id].predikat }})
                                                </span>
                                            </template>
                                            <span v-else class="text-slate-400">-</span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span :class="['font-bold', getAverageColor(student.overall_average)]">
                                                {{ student.overall_average.toFixed(1) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="(summary as ClassDetailSummary).students.length === 0">
                                        <td :colspan="(summary as ClassDetailSummary).subjects.length + 3" class="px-4 py-8 text-center text-slate-500">
                                            Belum ada data siswa
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </Motion>
            </template>
        </div>
    </AppLayout>
</template>
