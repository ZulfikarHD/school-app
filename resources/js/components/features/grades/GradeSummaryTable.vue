<script setup lang="ts">
/**
 * GradeSummaryTable - Tabel rekap nilai siswa per kelas
 * dengan fitur expand row untuk melihat breakdown komponen nilai
 * dan responsive design untuk mobile
 */
import { ref, computed } from 'vue';
import { ChevronDown, ChevronRight, User } from 'lucide-vue-next';
import GradePredikatBadge from './GradePredikatBadge.vue';

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

interface Props {
    students: StudentData[];
    subjects: SubjectInfo[];
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false
});

const expandedRows = ref<Set<number>>(new Set());

const toggleRow = (studentId: number) => {
    if (expandedRows.value.has(studentId)) {
        expandedRows.value.delete(studentId);
    } else {
        expandedRows.value.add(studentId);
    }
};

const isExpanded = (studentId: number) => expandedRows.value.has(studentId);

/**
 * Format nilai dengan 2 desimal
 */
const formatGrade = (value: number | null | undefined): string => {
    if (value === null || value === undefined) return '-';
    return value.toFixed(2);
};

/**
 * Mendapatkan warna background berdasarkan nilai
 */
const getGradeColor = (score: number): string => {
    if (score >= 90) return 'text-emerald-600 dark:text-emerald-400';
    if (score >= 80) return 'text-blue-600 dark:text-blue-400';
    if (score >= 70) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
};
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-zinc-800">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-zinc-800/50">
                    <th class="sticky left-0 bg-slate-50 dark:bg-zinc-800/50 px-3 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 w-8">
                        <!-- Expand toggle -->
                    </th>
                    <th class="sticky left-8 bg-slate-50 dark:bg-zinc-800/50 px-3 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 min-w-[40px]">
                        #
                    </th>
                    <th class="sticky left-16 bg-slate-50 dark:bg-zinc-800/50 px-3 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 min-w-[180px]">
                        Siswa
                    </th>
                    <th
                        v-for="subject in subjects"
                        :key="subject.id"
                        class="px-3 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 min-w-[100px]"
                        :title="subject.name"
                    >
                        {{ subject.code || subject.name.substring(0, 10) }}
                    </th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 min-w-[80px]">
                        Rata-Rata
                    </th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 min-w-[60px]">
                        Ranking
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                <!-- Loading State -->
                <template v-if="loading">
                    <tr v-for="i in 5" :key="`skeleton-${i}`">
                        <td :colspan="subjects.length + 5" class="px-4 py-4">
                            <div class="animate-pulse flex items-center gap-4">
                                <div class="h-4 w-8 bg-slate-200 dark:bg-zinc-700 rounded"></div>
                                <div class="h-4 w-32 bg-slate-200 dark:bg-zinc-700 rounded"></div>
                                <div class="flex-1 h-4 bg-slate-200 dark:bg-zinc-700 rounded"></div>
                            </div>
                        </td>
                    </tr>
                </template>

                <!-- Empty State -->
                <tr v-else-if="students.length === 0">
                    <td :colspan="subjects.length + 5" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <User class="w-12 h-12 text-slate-300 dark:text-zinc-600" />
                            <p class="text-slate-500 dark:text-slate-400">
                                Belum ada data nilai untuk ditampilkan
                            </p>
                        </div>
                    </td>
                </tr>

                <!-- Data Rows -->
                <template v-else v-for="(student, index) in students" :key="student.id">
                    <!-- Main Row -->
                    <tr
                        class="hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-colors cursor-pointer"
                        @click="toggleRow(student.id)"
                    >
                        <td class="sticky left-0 bg-white dark:bg-zinc-900 px-3 py-3">
                            <button
                                type="button"
                                class="p-1 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                                :aria-expanded="isExpanded(student.id)"
                            >
                                <ChevronDown
                                    v-if="isExpanded(student.id)"
                                    class="w-4 h-4 text-slate-500"
                                />
                                <ChevronRight
                                    v-else
                                    class="w-4 h-4 text-slate-500"
                                />
                            </button>
                        </td>
                        <td class="sticky left-8 bg-white dark:bg-zinc-900 px-3 py-3 text-slate-500 dark:text-slate-400 font-medium">
                            {{ index + 1 }}
                        </td>
                        <td class="sticky left-16 bg-white dark:bg-zinc-900 px-3 py-3">
                            <div class="flex flex-col">
                                <span class="font-medium text-slate-900 dark:text-slate-100">
                                    {{ student.name }}
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ student.nis }}
                                </span>
                            </div>
                        </td>
                        <td
                            v-for="subject in subjects"
                            :key="`${student.id}-${subject.id}`"
                            class="px-3 py-3 text-center"
                        >
                            <template v-if="student.subjects[subject.id]">
                                <div class="flex flex-col items-center gap-1">
                                    <span
                                        :class="[
                                            'font-semibold',
                                            getGradeColor(student.subjects[subject.id].final_grade)
                                        ]"
                                    >
                                        {{ formatGrade(student.subjects[subject.id].final_grade) }}
                                    </span>
                                    <GradePredikatBadge
                                        :predikat="student.subjects[subject.id].predikat"
                                        size="sm"
                                    />
                                </div>
                            </template>
                            <span v-else class="text-slate-400">-</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span
                                :class="[
                                    'font-bold text-base',
                                    getGradeColor(student.overall_average)
                                ]"
                            >
                                {{ formatGrade(student.overall_average) }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span
                                v-if="student.rank"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full font-bold"
                                :class="[
                                    student.rank <= 3
                                        ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                                        : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400'
                                ]"
                            >
                                {{ student.rank }}
                            </span>
                            <span v-else class="text-slate-400">-</span>
                        </td>
                    </tr>

                    <!-- Expanded Detail Row -->
                    <tr
                        v-if="isExpanded(student.id)"
                        class="bg-slate-50/50 dark:bg-zinc-800/20"
                    >
                        <td :colspan="subjects.length + 5" class="px-4 py-4">
                            <div class="pl-12 space-y-4">
                                <h4 class="font-semibold text-slate-700 dark:text-slate-300 text-sm">
                                    Detail Komponen Nilai
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs">
                                        <thead>
                                            <tr class="bg-slate-100 dark:bg-zinc-800">
                                                <th class="px-3 py-2 text-left font-medium text-slate-600 dark:text-slate-400">Mata Pelajaran</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-600 dark:text-slate-400">UH</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-600 dark:text-slate-400">UTS</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-600 dark:text-slate-400">UAS</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-600 dark:text-slate-400">Praktik</th>
                                                <th class="px-3 py-2 text-center font-medium text-slate-600 dark:text-slate-400">Nilai Akhir</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-700">
                                            <tr
                                                v-for="subject in subjects"
                                                :key="`detail-${student.id}-${subject.id}`"
                                                class="bg-white dark:bg-zinc-900"
                                            >
                                                <td class="px-3 py-2 font-medium text-slate-700 dark:text-slate-300">
                                                    {{ subject.name }}
                                                </td>
                                                <template v-if="student.subjects[subject.id]">
                                                    <td class="px-3 py-2 text-center text-slate-600 dark:text-slate-400">
                                                        {{ formatGrade(student.subjects[subject.id].breakdown?.uh?.average) }}
                                                        <span v-if="student.subjects[subject.id].breakdown?.uh?.count" class="text-slate-400 text-[10px]">
                                                            ({{ student.subjects[subject.id].breakdown.uh.count }}x)
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-center text-slate-600 dark:text-slate-400">
                                                        {{ formatGrade(student.subjects[subject.id].breakdown?.uts?.score) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center text-slate-600 dark:text-slate-400">
                                                        {{ formatGrade(student.subjects[subject.id].breakdown?.uas?.score) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center text-slate-600 dark:text-slate-400">
                                                        {{ formatGrade(student.subjects[subject.id].breakdown?.praktik?.average) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center font-semibold" :class="getGradeColor(student.subjects[subject.id].final_grade)">
                                                        {{ formatGrade(student.subjects[subject.id].final_grade) }}
                                                    </td>
                                                </template>
                                                <template v-else>
                                                    <td colspan="5" class="px-3 py-2 text-center text-slate-400">
                                                        Belum ada nilai
                                                    </td>
                                                </template>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>
