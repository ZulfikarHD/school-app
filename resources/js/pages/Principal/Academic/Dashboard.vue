<script setup lang="ts">
/**
 * Principal Academic Dashboard - Dashboard akademik dengan analytics
 * menampilkan rata-rata nilai per mapel, distribusi predikat, dan alert KKM
 */
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import {
    BarChart3,
    BookOpen,
    Users,
    Award,
    AlertTriangle,
    Filter,
    Search,
    TrendingUp,
    FileText,
    Clock
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { dashboard } from '@/routes/principal/academic';

interface SubjectAverage {
    subject_id: number;
    subject_name: string;
    subject_code: string;
    average: number;
    predikat: string;
    is_below_kkm: boolean;
}

interface PredikatDistribution {
    A: number;
    B: number;
    C: number;
    D: number;
    total: number;
}

interface OverallStats {
    total_students: number;
    total_grades: number;
    overall_average: number;
}

interface ReportCardStats {
    total: number;
    draft: number;
    pending: number;
    approved: number;
    released: number;
}

interface ClassOption {
    id: number;
    name: string;
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
    subjects: { id: number; name: string; code: string }[];
    subjectAverages: SubjectAverage[];
    predikatDistribution: PredikatDistribution;
    belowKKMSubjects: SubjectAverage[];
    overallStats: OverallStats;
    reportCardStats: ReportCardStats;
    kkm: number;
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

    router.visit(dashboard.url(params), {
        preserveState: true,
        preserveScroll: true
    });
};

/**
 * Get predikat percentage
 */
const getPredikatPercentage = (count: number): string => {
    if (props.predikatDistribution.total === 0) return '0';
    return ((count / props.predikatDistribution.total) * 100).toFixed(1);
};

/**
 * Get bar width for chart
 */
const getBarWidth = (average: number): string => {
    if (average === 0) return '0%';
    return `${Math.min(average, 100)}%`;
};

/**
 * Get predikat color
 */
const getPredikatColor = (predikat: string): string => {
    const colors: Record<string, string> = {
        'A': 'bg-emerald-500',
        'B': 'bg-blue-500',
        'C': 'bg-amber-500',
        'D': 'bg-red-500'
    };
    return colors[predikat] || colors['D'];
};

/**
 * Get average color
 */
const getAverageColor = (average: number, kkm: number): string => {
    if (average >= 90) return 'text-emerald-600 dark:text-emerald-400';
    if (average >= 80) return 'text-blue-600 dark:text-blue-400';
    if (average >= kkm) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
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
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-indigo-400 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/25 shrink-0">
                                <BarChart3 class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Dashboard Akademik
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Analisis performa nilai siswa
                                </p>
                            </div>
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
                    <div class="flex items-center gap-3 mb-4">
                        <Filter class="w-5 h-5 text-slate-500" />
                        <span class="font-medium text-slate-700 dark:text-slate-300">Filter Data</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
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

                        <div class="flex items-end">
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                KKM: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ kkm }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Overview Stats -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <Users class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ overallStats.total_students }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Siswa</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                <BookOpen class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ overallStats.total_grades }}
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
                                <p :class="['text-2xl font-bold', getAverageColor(overallStats.overall_average, kkm)]">
                                    {{ overallStats.overall_average.toFixed(1) }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Rata-rata</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <Clock class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">
                                    {{ reportCardStats.pending }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pending Approval</p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Predikat Distribution -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                            <Award class="w-5 h-5 text-indigo-500" />
                            Distribusi Predikat
                        </h3>

                        <div v-if="predikatDistribution.total > 0" class="space-y-4">
                            <div
                                v-for="(predikat, key) in ['A', 'B', 'C', 'D']"
                                :key="key"
                                class="flex items-center gap-3"
                            >
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white" :class="getPredikatColor(predikat)">
                                    {{ predikat }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between text-sm mb-1">
                                        <span class="text-slate-600 dark:text-slate-400">
                                            {{ predikat === 'A' ? 'Sangat Baik' : predikat === 'B' ? 'Baik' : predikat === 'C' ? 'Cukup' : 'Kurang' }}
                                        </span>
                                        <span class="font-medium text-slate-900 dark:text-slate-100">
                                            {{ predikatDistribution[predikat as keyof PredikatDistribution] }} siswa ({{ getPredikatPercentage(predikatDistribution[predikat as keyof PredikatDistribution] as number) }}%)
                                        </span>
                                    </div>
                                    <div class="h-2 bg-slate-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                        <div
                                            :class="[getPredikatColor(predikat), 'h-full rounded-full transition-all duration-500']"
                                            :style="{ width: `${getPredikatPercentage(predikatDistribution[predikat as keyof PredikatDistribution] as number)}%` }"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-slate-500 dark:text-slate-400">
                            Belum ada data nilai
                        </div>
                    </div>
                </Motion>

                <!-- Below KKM Alert -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                            <AlertTriangle class="w-5 h-5 text-red-500" />
                            Mapel di Bawah KKM
                        </h3>

                        <div v-if="belowKKMSubjects.length > 0" class="space-y-3">
                            <div
                                v-for="subject in belowKKMSubjects"
                                :key="subject.subject_id"
                                class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800/50"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/50 flex items-center justify-center">
                                        <BookOpen class="w-4 h-4 text-red-600 dark:text-red-400" />
                                    </div>
                                    <span class="font-medium text-slate-900 dark:text-slate-100">
                                        {{ subject.subject_name }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-red-600 dark:text-red-400">
                                        {{ subject.average.toFixed(1) }}
                                    </p>
                                    <p class="text-xs text-red-500">
                                        -{{ (kkm - subject.average).toFixed(1) }} dari KKM
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mx-auto mb-3">
                                <Award class="w-8 h-8 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <p class="text-emerald-600 dark:text-emerald-400 font-medium">
                                Semua mapel di atas KKM!
                            </p>
                        </div>
                    </div>
                </Motion>
            </div>

            <!-- Subject Averages Chart -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.25 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                        <BarChart3 class="w-5 h-5 text-indigo-500" />
                        Rata-rata Nilai per Mata Pelajaran
                    </h3>

                    <div v-if="subjectAverages.length > 0" class="space-y-3">
                        <div
                            v-for="subject in subjectAverages"
                            :key="subject.subject_id"
                            class="flex items-center gap-3"
                        >
                            <div class="w-32 sm:w-40 truncate text-sm text-slate-700 dark:text-slate-300">
                                {{ subject.subject_name }}
                            </div>
                            <div class="flex-1 relative">
                                <div class="h-8 bg-slate-100 dark:bg-zinc-800 rounded-lg overflow-hidden">
                                    <div
                                        :class="[
                                            'h-full rounded-lg transition-all duration-500',
                                            subject.is_below_kkm ? 'bg-red-500' : subject.average >= 80 ? 'bg-emerald-500' : 'bg-blue-500'
                                        ]"
                                        :style="{ width: getBarWidth(subject.average) }"
                                    />
                                </div>
                                <!-- KKM Line -->
                                <div
                                    class="absolute top-0 bottom-0 w-0.5 bg-amber-500"
                                    :style="{ left: `${kkm}%` }"
                                    title="KKM"
                                />
                            </div>
                            <div class="w-16 text-right">
                                <span :class="['font-semibold', getAverageColor(subject.average, kkm)]">
                                    {{ subject.average.toFixed(1) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-slate-500 dark:text-slate-400">
                        Belum ada data nilai
                    </div>

                    <div class="mt-4 flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 rounded bg-emerald-500" />
                            <span>≥80</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 rounded bg-blue-500" />
                            <span>≥KKM</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 rounded bg-red-500" />
                            <span>&lt;KKM</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-0.5 bg-amber-500" />
                            <span>KKM ({{ kkm }})</span>
                        </div>
                    </div>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
