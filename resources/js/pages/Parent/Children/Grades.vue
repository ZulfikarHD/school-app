<script setup lang="ts">
/**
 * Parent Children Grades - Rekap nilai anak dengan breakdown per komponen
 * dan visualisasi chart untuk performa akademik
 */
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import {
    BookOpen,
    Award,
    ChevronDown,
    ChevronUp,
    ArrowLeft,
    User,
    TrendingUp,
    Filter
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { show as showChild, grades } from '@/routes/parent/children';

interface SubjectBreakdown {
    uh: { average: number; weight: number; weighted: number; count: number };
    uts: { score: number; weight: number; weighted: number };
    uas: { score: number; weight: number; weighted: number };
    praktik: { average: number; weight: number; weighted: number; count: number };
}

interface SubjectGrade {
    subject_id: number;
    subject_name: string;
    subject_code: string;
    final_grade: number;
    predikat: string;
    predikat_label: string;
    breakdown: SubjectBreakdown;
}

interface GradeSummary {
    subjects: SubjectGrade[];
    overall_average: number;
    rank: number | null;
    total_students: number;
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
    gradeSummary: GradeSummary | null;
    filters: {
        tahun_ajaran: string;
        semester: string;
    };
    availableTahunAjaran: string[];
    semesters: { value: string; label: string }[];
}

const props = defineProps<Props>();

const haptics = useHaptics();

const localFilters = ref({
    tahun_ajaran: props.filters.tahun_ajaran,
    semester: props.filters.semester
});

const expandedSubjects = ref<Set<number>>(new Set());

/**
 * Apply filters
 */
const applyFilters = () => {
    haptics.light();

    router.visit(grades(props.student.id, {
        tahun_ajaran: localFilters.value.tahun_ajaran,
        semester: localFilters.value.semester
    }).url, {
        preserveState: true,
        preserveScroll: true
    });
};

/**
 * Toggle subject breakdown expansion
 */
const toggleSubject = (subjectId: number) => {
    haptics.light();
    if (expandedSubjects.value.has(subjectId)) {
        expandedSubjects.value.delete(subjectId);
    } else {
        expandedSubjects.value.add(subjectId);
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
    if (average >= 70) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
};

/**
 * Get bar width percentage
 */
const getBarWidth = (score: number): string => {
    return `${Math.min(score, 100)}%`;
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
 * Compute chart data for radar-like visual
 */
const chartData = computed(() => {
    if (!props.gradeSummary?.subjects.length) return [];
    return props.gradeSummary.subjects.map(s => ({
        name: s.subject_code || s.subject_name.substring(0, 4),
        fullName: s.subject_name,
        value: s.final_grade,
        predikat: s.predikat
    }));
});
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
                                    Rekap Nilai
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ student.nama_lengkap }} - {{ student.kelas?.nama_lengkap || '-' }}
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
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 mb-3">
                        <Filter class="w-4 h-4 text-slate-500" />
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Periode</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <select
                            v-model="localFilters.tahun_ajaran"
                            @change="applyFilters"
                            class="px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option
                                v-for="ta in availableTahunAjaran"
                                :key="ta"
                                :value="ta"
                            >
                                {{ ta }}
                            </option>
                        </select>

                        <select
                            v-model="localFilters.semester"
                            @change="applyFilters"
                            class="px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
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
                </div>
            </Motion>

            <!-- Summary Cards -->
            <Motion
                v-if="gradeSummary"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                <TrendingUp class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Rata-rata</p>
                                <p :class="['text-2xl font-bold', getAverageColor(gradeSummary.overall_average)]">
                                    {{ gradeSummary.overall_average.toFixed(1) }}
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
                                <p class="text-xs text-slate-500 dark:text-slate-400">Peringkat</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    <template v-if="gradeSummary.rank">
                                        #{{ gradeSummary.rank }} <span class="text-sm font-normal text-slate-500">/ {{ gradeSummary.total_students }}</span>
                                    </template>
                                    <span v-else class="text-slate-400">-</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Visual Chart -->
            <Motion
                v-if="chartData.length > 0"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                        <BookOpen class="w-5 h-5 text-indigo-500" />
                        Performa per Mata Pelajaran
                    </h3>

                    <div class="space-y-3">
                        <div
                            v-for="item in chartData"
                            :key="item.name"
                            class="flex items-center gap-3"
                        >
                            <div class="w-16 sm:w-24 text-sm text-slate-600 dark:text-slate-400 truncate" :title="item.fullName">
                                {{ item.fullName }}
                            </div>
                            <div class="flex-1 relative">
                                <div class="h-6 bg-slate-100 dark:bg-zinc-800 rounded-lg overflow-hidden">
                                    <div
                                        :class="[
                                            'h-full rounded-lg transition-all duration-500 flex items-center justify-end pr-2',
                                            item.predikat === 'A' ? 'bg-emerald-500' :
                                            item.predikat === 'B' ? 'bg-blue-500' :
                                            item.predikat === 'C' ? 'bg-amber-500' : 'bg-red-500'
                                        ]"
                                        :style="{ width: getBarWidth(item.value) }"
                                    >
                                        <span
                                            v-if="item.value >= 30"
                                            class="text-xs font-semibold text-white"
                                        >
                                            {{ Math.round(item.value) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="w-8 text-center">
                                <span :class="['text-sm font-bold', getPredikatColor(item.predikat)]">
                                    {{ item.predikat }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 rounded bg-emerald-500" />
                            <span>A (90-100)</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 rounded bg-blue-500" />
                            <span>B (80-89)</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 rounded bg-amber-500" />
                            <span>C (70-79)</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 rounded bg-red-500" />
                            <span>D (&lt;70)</span>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Subject List with Expandable Breakdown -->
            <Motion
                v-if="gradeSummary?.subjects.length"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <div class="p-4 border-b border-slate-100 dark:border-zinc-800">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <BookOpen class="w-5 h-5 text-indigo-500" />
                            Detail Nilai per Mapel
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Ketuk untuk melihat detail komponen nilai
                        </p>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <div
                            v-for="subject in gradeSummary.subjects"
                            :key="subject.subject_id"
                        >
                            <!-- Subject Row -->
                            <button
                                type="button"
                                @click="toggleSubject(subject.subject_id)"
                                class="w-full px-4 py-3 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div :class="['w-10 h-10 rounded-lg flex items-center justify-center font-bold text-sm', getPredikatBgColor(subject.predikat), getPredikatColor(subject.predikat)]">
                                        {{ subject.predikat }}
                                    </div>
                                    <div class="text-left">
                                        <p class="font-medium text-slate-900 dark:text-slate-100">
                                            {{ subject.subject_name }}
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ subject.predikat_label }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span :class="['text-xl font-bold', getAverageColor(subject.final_grade)]">
                                        {{ Math.round(subject.final_grade) }}
                                    </span>
                                    <component
                                        :is="expandedSubjects.has(subject.subject_id) ? ChevronUp : ChevronDown"
                                        class="w-5 h-5 text-slate-400"
                                    />
                                </div>
                            </button>

                            <!-- Expandable Breakdown -->
                            <div
                                v-show="expandedSubjects.has(subject.subject_id)"
                                class="px-4 pb-4 bg-slate-50 dark:bg-zinc-800/30"
                            >
                                <div class="grid grid-cols-2 gap-3 pt-3">
                                    <!-- UH -->
                                    <div class="bg-white dark:bg-zinc-900 rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs text-slate-500 dark:text-slate-400">UH</span>
                                            <span class="text-xs text-slate-400">{{ subject.breakdown.uh.weight }}%</span>
                                        </div>
                                        <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                            {{ subject.breakdown.uh.average.toFixed(1) }}
                                        </p>
                                        <p class="text-xs text-slate-500">{{ subject.breakdown.uh.count }} penilaian</p>
                                    </div>

                                    <!-- UTS -->
                                    <div class="bg-white dark:bg-zinc-900 rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs text-slate-500 dark:text-slate-400">UTS</span>
                                            <span class="text-xs text-slate-400">{{ subject.breakdown.uts.weight }}%</span>
                                        </div>
                                        <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                            {{ subject.breakdown.uts.score.toFixed(1) }}
                                        </p>
                                    </div>

                                    <!-- UAS -->
                                    <div class="bg-white dark:bg-zinc-900 rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs text-slate-500 dark:text-slate-400">UAS</span>
                                            <span class="text-xs text-slate-400">{{ subject.breakdown.uas.weight }}%</span>
                                        </div>
                                        <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                            {{ subject.breakdown.uas.score.toFixed(1) }}
                                        </p>
                                    </div>

                                    <!-- Praktik -->
                                    <div class="bg-white dark:bg-zinc-900 rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs text-slate-500 dark:text-slate-400">Praktik</span>
                                            <span class="text-xs text-slate-400">{{ subject.breakdown.praktik.weight }}%</span>
                                        </div>
                                        <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                            {{ subject.breakdown.praktik.average.toFixed(1) }}
                                        </p>
                                        <p v-if="subject.breakdown.praktik.count > 0" class="text-xs text-slate-500">
                                            {{ subject.breakdown.praktik.count }} penilaian
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Empty State -->
            <Motion
                v-if="!gradeSummary || !gradeSummary.subjects.length"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-8 shadow-sm border border-slate-200 dark:border-zinc-800 text-center">
                    <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4">
                        <BookOpen class="w-8 h-8 text-slate-400" />
                    </div>
                    <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-2">
                        Belum Ada Nilai
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Nilai untuk semester ini belum tersedia. Silakan coba periode lain atau hubungi wali kelas.
                    </p>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
