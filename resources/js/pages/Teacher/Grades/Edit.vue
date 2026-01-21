<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import Badge from '@/components/ui/Badge.vue';
import { Save, ChevronLeft, AlertCircle, Users, Lock, Calendar } from 'lucide-vue-next';
import { update, index } from '@/routes/teacher/grades';

/**
 * Form edit nilai yang sudah ada
 * dengan data siswa dan nilai yang sudah diinput sebelumnya
 */

interface GradeData {
    id: number;
    student_id: number;
    student_nama: string;
    student_nis: string;
    score: number;
    notes: string | null;
}

interface Assessment {
    id: number;
    class_id: number;
    subject_id: number;
    tahun_ajaran: string;
    semester: string;
    assessment_type: string;
    assessment_number: number | null;
    title: string;
    assessment_date: string;
    is_locked: boolean;
}

interface Props {
    title: string;
    assessment: Assessment;
    grades: GradeData[];
    class: {
        id: number;
        nama_lengkap: string;
    };
    subject: {
        id: number;
        nama_mapel: string;
    };
    assessmentTypes: Record<string, string>;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

/**
 * Form data untuk submission
 */
const form = useForm({
    title: props.assessment.title,
    assessment_date: props.assessment.assessment_date,
    grades: props.grades.map(g => ({
        id: g.id,
        score: g.score,
        notes: g.notes || '',
    })),
});

/**
 * Computed untuk validasi form (semua nilai harus diisi 0-100)
 */
const formValid = computed(() => {
    return form.title && form.assessment_date && form.grades.every(g => g.score !== null && g.score >= 0 && g.score <= 100);
});

/**
 * Summary nilai yang sudah diinput
 */
const gradeSummary = computed(() => {
    const scores = form.grades.filter(g => g.score !== null).map(g => g.score as number);
    if (scores.length === 0) return { count: 0, average: 0, min: 0, max: 0 };
    
    return {
        count: scores.length,
        average: scores.reduce((a, b) => a + b, 0) / scores.length,
        min: Math.min(...scores),
        max: Math.max(...scores),
    };
});

/**
 * Get predikat berdasarkan nilai
 */
const getPredikat = (score: number | null) => {
    if (score === null) return { grade: '-', color: 'text-gray-400' };
    if (score >= 90) return { grade: 'A', color: 'text-emerald-600 dark:text-emerald-400' };
    if (score >= 80) return { grade: 'B', color: 'text-blue-600 dark:text-blue-400' };
    if (score >= 70) return { grade: 'C', color: 'text-yellow-600 dark:text-yellow-400' };
    return { grade: 'D', color: 'text-red-600 dark:text-red-400' };
};

/**
 * Get badge variant berdasarkan assessment type
 */
const getAssessmentTypeBadge = (type: string) => {
    const badges: Record<string, { variant: string; label: string }> = {
        UH: { variant: 'info', label: 'UH' },
        UTS: { variant: 'warning', label: 'UTS' },
        UAS: { variant: 'danger', label: 'UAS' },
        PRAKTIK: { variant: 'success', label: 'Praktik' },
        PROYEK: { variant: 'secondary', label: 'Proyek' },
    };
    return badges[type] || { variant: 'secondary', label: type };
};

/**
 * Set semua nilai menjadi nilai tertentu (bulk action)
 */
const setAllScores = (score: number) => {
    haptics.light();
    form.grades.forEach(g => {
        g.score = score;
    });
};

/**
 * Submit form
 */
const submitForm = () => {
    if (!formValid.value) {
        modal.error('Mohon isi semua nilai dengan angka 0-100');
        return;
    }

    haptics.medium();

    form.put(update(props.assessment.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            modal.success('Nilai berhasil diupdate');
        },
        onError: (errors) => {
            haptics.error();
            const message = Object.values(errors).flat().join(', ') || 'Gagal mengupdate nilai';
            modal.error(message);
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="bg-white px-6 py-8 border-b border-gray-100 dark:bg-zinc-900 dark:border-zinc-800">
                    <div class="mx-auto max-w-4xl">
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
                            <Badge v-if="assessment.is_locked" variant="warning" class="flex items-center gap-1">
                                <Lock :size="12" />
                                Terkunci
                            </Badge>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ props.class.nama_lengkap }} - {{ props.subject.nama_mapel }}
                        </p>
                    </div>
                </div>
            </Motion>

            <div class="mx-auto max-w-4xl px-6 py-8 space-y-6">
                <!-- Locked Warning -->
                <div v-if="assessment.is_locked" class="bg-yellow-50 dark:bg-yellow-950/30 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <Lock :size="20" class="text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
                        <div>
                            <p class="font-medium text-yellow-800 dark:text-yellow-200">Nilai Sudah Dikunci</p>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                Nilai ini sudah dikunci dan tidak dapat diedit. Hubungi admin jika perlu melakukan perubahan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Assessment Info -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Detail Penilaian</h2>

                        <div class="grid gap-6 md:grid-cols-2">
                            <!-- Judul -->
                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">
                                    Judul Penilaian *
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    :disabled="assessment.is_locked"
                                    class="w-full h-[52px] px-4 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-900 dark:text-white placeholder-slate-400
                                           disabled:opacity-50 disabled:cursor-not-allowed
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                           transition-all duration-150"
                                />
                                <p v-if="form.errors.title" class="mt-1 text-sm text-red-500">{{ form.errors.title }}</p>
                            </div>

                            <!-- Tanggal -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">
                                    Tanggal Penilaian *
                                </label>
                                <div class="relative">
                                    <Calendar class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="20" />
                                    <input
                                        v-model="form.assessment_date"
                                        type="date"
                                        :max="new Date().toISOString().split('T')[0]"
                                        :disabled="assessment.is_locked"
                                        class="w-full h-[52px] pl-12 pr-4 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                               rounded-xl text-slate-900 dark:text-white
                                               disabled:opacity-50 disabled:cursor-not-allowed
                                               focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                               transition-all duration-150"
                                    />
                                </div>
                                <p v-if="form.errors.assessment_date" class="mt-1 text-sm text-red-500">{{ form.errors.assessment_date }}</p>
                            </div>

                            <!-- Info (Read-only) -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">
                                    Jenis Penilaian
                                </label>
                                <div class="h-[52px] px-4 bg-slate-100 dark:bg-zinc-800/50 border border-slate-200 dark:border-zinc-700
                                            rounded-xl flex items-center gap-2">
                                    <Badge :variant="(getAssessmentTypeBadge(assessment.assessment_type).variant as any)">
                                        {{ getAssessmentTypeBadge(assessment.assessment_type).label }}
                                        {{ assessment.assessment_number ? assessment.assessment_number : '' }}
                                    </Badge>
                                    <span class="text-slate-600 dark:text-zinc-400">| Semester {{ assessment.semester }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Summary Cards -->
                <div class="grid gap-4 grid-cols-2 md:grid-cols-4">
                    <Motion
                        :initial="{ opacity: 0, scale: 0.9 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 shadow-sm p-4">
                            <p class="text-xs font-medium text-slate-600 dark:text-zinc-400">Jumlah Siswa</p>
                            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ grades.length }}</p>
                        </div>
                    </Motion>
                    <Motion
                        :initial="{ opacity: 0, scale: 0.9 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                    >
                        <div class="bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800 p-4">
                            <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Rata-rata</p>
                            <p class="mt-1 text-2xl font-bold text-emerald-900 dark:text-emerald-100">{{ gradeSummary.average.toFixed(1) }}</p>
                        </div>
                    </Motion>
                    <Motion
                        :initial="{ opacity: 0, scale: 0.9 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                    >
                        <div class="bg-blue-50 dark:bg-blue-950/30 rounded-xl border border-blue-200 dark:border-blue-800 p-4">
                            <p class="text-xs font-medium text-blue-600 dark:text-blue-400">Tertinggi</p>
                            <p class="mt-1 text-2xl font-bold text-blue-900 dark:text-blue-100">{{ gradeSummary.max || '-' }}</p>
                        </div>
                    </Motion>
                    <Motion
                        :initial="{ opacity: 0, scale: 0.9 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                    >
                        <div class="bg-red-50 dark:bg-red-950/30 rounded-xl border border-red-200 dark:border-red-800 p-4">
                            <p class="text-xs font-medium text-red-600 dark:text-red-400">Terendah</p>
                            <p class="mt-1 text-2xl font-bold text-red-900 dark:text-red-100">{{ gradeSummary.min || '-' }}</p>
                        </div>
                    </Motion>
                </div>

                <!-- Quick Actions -->
                <div v-if="!assessment.is_locked" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-sm text-slate-600 dark:text-zinc-400">Set semua nilai:</span>
                        <button
                            v-for="score in [100, 90, 80, 70, 60, 50]"
                            :key="score"
                            @click="setAllScores(score)"
                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700
                                   text-slate-700 dark:text-zinc-300 rounded-lg text-sm font-medium transition-colors"
                        >
                            {{ score }}
                        </button>
                    </div>
                </div>

                <!-- Student Table -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.3 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-200 dark:border-zinc-800">
                            <div class="flex items-center gap-2">
                                <Users :size="20" class="text-slate-600 dark:text-zinc-400" />
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                    Daftar Nilai Siswa
                                </h2>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50/80 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide">No</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide">Nama Siswa</th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide">Nilai (0-100)</th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide">Predikat</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-zinc-800">
                                    <tr v-for="(grade, index) in grades" :key="grade.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-zinc-400">{{ index + 1 }}</td>
                                        <td class="px-6 py-4">
                                            <p class="font-medium text-slate-900 dark:text-white">{{ grade.student_nama }}</p>
                                            <p class="text-sm text-slate-500 dark:text-zinc-400">NIS: {{ grade.student_nis }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input
                                                v-model.number="form.grades[index].score"
                                                type="number"
                                                min="0"
                                                max="100"
                                                :disabled="assessment.is_locked"
                                                class="w-24 mx-auto block px-3 py-2 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                       rounded-lg text-center text-slate-900 dark:text-white
                                                       disabled:opacity-50 disabled:cursor-not-allowed
                                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                                       transition-all duration-150"
                                            />
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-lg font-bold" :class="getPredikat(form.grades[index].score).color">
                                                {{ getPredikat(form.grades[index].score).grade }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input
                                                v-model="form.grades[index].notes"
                                                type="text"
                                                placeholder="Catatan (opsional)"
                                                :disabled="assessment.is_locked"
                                                class="w-full px-3 py-2 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                       rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-400
                                                       disabled:opacity-50 disabled:cursor-not-allowed
                                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                                       transition-all duration-150"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Error Summary -->
                        <div v-if="Object.keys(form.errors).length > 0" class="p-6 border-t border-slate-200 dark:border-zinc-800 bg-red-50/50 dark:bg-red-950/20">
                            <div class="flex items-start gap-2">
                                <AlertCircle :size="20" class="text-red-500 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-sm font-medium text-red-800 dark:text-red-200">Terdapat kesalahan:</p>
                                    <ul class="mt-1 text-sm text-red-600 dark:text-red-300 list-disc list-inside">
                                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <div class="p-6 border-t border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-800/30">
                            <div class="flex items-center justify-between">
                                <button
                                    @click="router.get(index().url)"
                                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700
                                           text-gray-700 dark:text-gray-300 rounded-xl font-semibold
                                           flex items-center gap-2 transition-colors duration-150"
                                >
                                    <ChevronLeft :size="20" />
                                    Kembali
                                </button>

                                <Motion v-if="!assessment.is_locked" :whileTap="{ scale: 0.97 }">
                                    <button
                                        @click="submitForm"
                                        :disabled="!formValid || form.processing"
                                        class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold
                                               flex items-center gap-2 shadow-sm shadow-emerald-500/25
                                               disabled:opacity-50 disabled:cursor-not-allowed
                                               transition-colors duration-150"
                                    >
                                        <Save :size="20" />
                                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>
