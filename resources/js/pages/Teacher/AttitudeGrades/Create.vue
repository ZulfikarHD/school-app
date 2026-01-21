<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import Badge from '@/components/ui/Badge.vue';
import { Save, ChevronLeft, AlertCircle, Users, Heart, Sparkles, FileText } from 'lucide-vue-next';
import { store, index } from '@/routes/teacher/attitude-grades';

/**
 * Form input nilai sikap spiritual dan sosial
 * dengan bulk input untuk semua siswa dalam kelas
 * Hanya bisa diakses oleh wali kelas
 */

interface Student {
    id: number;
    nis: string;
    nama_lengkap: string;
    spiritual_grade: string;
    spiritual_description: string;
    social_grade: string;
    social_description: string;
    homeroom_notes: string;
    has_existing: boolean;
}

interface SchoolClass {
    id: number;
    nama_lengkap: string;
    tahun_ajaran: string;
}

interface Props {
    title: string;
    class: SchoolClass;
    students: Student[];
    tahunAjaran: string;
    semester: string;
    gradeOptions: Record<string, string>;
    spiritualTemplates: Record<string, string>;
    socialTemplates: Record<string, string>;
    semesters: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

const selectedSemester = ref(props.semester);

/**
 * Form data untuk submission
 */
const form = useForm({
    class_id: props.class.id,
    tahun_ajaran: props.tahunAjaran,
    semester: props.semester,
    grades: props.students.map(s => ({
        student_id: s.id,
        spiritual_grade: s.spiritual_grade,
        spiritual_description: s.spiritual_description,
        social_grade: s.social_grade,
        social_description: s.social_description,
        homeroom_notes: s.homeroom_notes,
    })),
});

/**
 * Computed untuk validasi form (semua grade harus dipilih)
 */
const formValid = computed(() => {
    return form.grades.every(g => g.spiritual_grade && g.social_grade);
});

/**
 * Summary nilai sikap
 */
const gradeSummary = computed(() => {
    const spiritual = { A: 0, B: 0, C: 0, D: 0 };
    const social = { A: 0, B: 0, C: 0, D: 0 };
    
    form.grades.forEach(g => {
        if (g.spiritual_grade in spiritual) {
            spiritual[g.spiritual_grade as keyof typeof spiritual]++;
        }
        if (g.social_grade in social) {
            social[g.social_grade as keyof typeof social]++;
        }
    });
    
    return { spiritual, social };
});

/**
 * Get badge color berdasarkan grade
 */
const getGradeColor = (grade: string) => {
    const colors: Record<string, string> = {
        A: 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800',
        B: 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800',
        C: 'text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-950/30 border-yellow-200 dark:border-yellow-800',
        D: 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-800',
    };
    return colors[grade] || 'text-gray-600 bg-gray-50 border-gray-200';
};

/**
 * Set semua grade spiritual menjadi nilai tertentu
 */
const setAllSpiritualGrades = (grade: string) => {
    haptics.light();
    form.grades.forEach(g => {
        g.spiritual_grade = grade;
        g.spiritual_description = props.spiritualTemplates[grade] || '';
    });
};

/**
 * Set semua grade social menjadi nilai tertentu
 */
const setAllSocialGrades = (grade: string) => {
    haptics.light();
    form.grades.forEach(g => {
        g.social_grade = grade;
        g.social_description = props.socialTemplates[grade] || '';
    });
};

/**
 * Apply template description ketika grade berubah
 */
const applyTemplate = (index: number, type: 'spiritual' | 'social') => {
    const grade = type === 'spiritual' ? form.grades[index].spiritual_grade : form.grades[index].social_grade;
    const templates = type === 'spiritual' ? props.spiritualTemplates : props.socialTemplates;
    
    if (type === 'spiritual') {
        form.grades[index].spiritual_description = templates[grade] || '';
    } else {
        form.grades[index].social_description = templates[grade] || '';
    }
};

/**
 * Watch semester change dan reload page
 */
watch(selectedSemester, (newSemester) => {
    if (newSemester !== props.semester) {
        router.get(window.location.pathname, { semester: newSemester }, { preserveState: false });
    }
});

/**
 * Submit form
 */
const submitForm = () => {
    if (!formValid.value) {
        modal.error('Mohon pilih nilai sikap untuk semua siswa');
        return;
    }

    haptics.medium();

    form.semester = selectedSemester.value;
    form.post(store().url, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            modal.success('Nilai sikap berhasil disimpan');
        },
        onError: (errors) => {
            haptics.error();
            const message = Object.values(errors).flat().join(', ') || 'Gagal menyimpan nilai sikap';
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
                    <div class="mx-auto max-w-6xl">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Kelas {{ props.class.nama_lengkap }} | {{ props.class.tahun_ajaran }}
                        </p>
                    </div>
                </div>
            </Motion>

            <div class="mx-auto max-w-6xl px-6 py-8 space-y-6">
                <!-- Semester Selection & Summary -->
                <div class="grid gap-6 md:grid-cols-4">
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Semester
                            </label>
                            <select
                                v-model="selectedSemester"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl
                                       text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            >
                                <option v-for="sem in semesters" :key="sem.value" :value="sem.value">
                                    {{ sem.label }}
                                </option>
                            </select>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-slate-600 dark:text-zinc-400">Jumlah Siswa</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ students.length }}</p>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                    >
                        <div class="bg-pink-50 dark:bg-pink-950/30 rounded-2xl border border-pink-200 dark:border-pink-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-pink-600 dark:text-pink-400 flex items-center gap-1">
                                <Sparkles :size="14" />
                                Spiritual (A/B/C/D)
                            </p>
                            <div class="mt-2 flex items-center gap-1 text-lg font-bold">
                                <span class="text-emerald-600">{{ gradeSummary.spiritual.A }}</span>/
                                <span class="text-blue-600">{{ gradeSummary.spiritual.B }}</span>/
                                <span class="text-yellow-600">{{ gradeSummary.spiritual.C }}</span>/
                                <span class="text-red-600">{{ gradeSummary.spiritual.D }}</span>
                            </div>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                    >
                        <div class="bg-violet-50 dark:bg-violet-950/30 rounded-2xl border border-violet-200 dark:border-violet-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-violet-600 dark:text-violet-400 flex items-center gap-1">
                                <Heart :size="14" />
                                Sosial (A/B/C/D)
                            </p>
                            <div class="mt-2 flex items-center gap-1 text-lg font-bold">
                                <span class="text-emerald-600">{{ gradeSummary.social.A }}</span>/
                                <span class="text-blue-600">{{ gradeSummary.social.B }}</span>/
                                <span class="text-yellow-600">{{ gradeSummary.social.C }}</span>/
                                <span class="text-red-600">{{ gradeSummary.social.D }}</span>
                            </div>
                        </div>
                    </Motion>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-slate-600 dark:text-zinc-400">Set semua Spiritual:</span>
                            <button
                                v-for="(label, grade) in gradeOptions"
                                :key="`spiritual-${grade}`"
                                @click="setAllSpiritualGrades(grade)"
                                class="px-3 py-1.5 rounded-lg text-sm font-medium border transition-colors"
                                :class="getGradeColor(grade)"
                            >
                                {{ grade }}
                            </button>
                        </div>
                        <div class="h-6 w-px bg-slate-200 dark:bg-zinc-700 hidden md:block" />
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-slate-600 dark:text-zinc-400">Set semua Sosial:</span>
                            <button
                                v-for="(label, grade) in gradeOptions"
                                :key="`social-${grade}`"
                                @click="setAllSocialGrades(grade)"
                                class="px-3 py-1.5 rounded-lg text-sm font-medium border transition-colors"
                                :class="getGradeColor(grade)"
                            >
                                {{ grade }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Student Table -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-200 dark:border-zinc-800">
                            <div class="flex items-center gap-2">
                                <Users :size="20" class="text-slate-600 dark:text-zinc-400" />
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                    Input Nilai Sikap
                                </h2>
                            </div>
                            <p class="mt-1 text-sm text-slate-600 dark:text-zinc-400">
                                Pilih predikat dan isi deskripsi untuk setiap siswa. Deskripsi akan otomatis terisi berdasarkan template.
                            </p>
                        </div>

                        <div class="divide-y divide-slate-200 dark:divide-zinc-800">
                            <div
                                v-for="(student, index) in students"
                                :key="student.id"
                                class="p-6 hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors"
                            >
                                <!-- Student Header -->
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ student.nama_lengkap }}</p>
                                        <p class="text-sm text-slate-500 dark:text-zinc-400">NIS: {{ student.nis }}</p>
                                    </div>
                                    <Badge v-if="student.has_existing" variant="info">Sudah ada data</Badge>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <!-- Spiritual Grade -->
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-1 text-sm font-medium text-pink-600 dark:text-pink-400">
                                            <Sparkles :size="14" />
                                            Nilai Spiritual
                                        </label>
                                        <div class="flex gap-2">
                                            <button
                                                v-for="(label, grade) in gradeOptions"
                                                :key="`${student.id}-spiritual-${grade}`"
                                                @click="form.grades[index].spiritual_grade = grade; applyTemplate(index, 'spiritual')"
                                                class="flex-1 py-2 rounded-lg text-sm font-bold border-2 transition-all"
                                                :class="[
                                                    form.grades[index].spiritual_grade === grade
                                                        ? getGradeColor(grade) + ' ring-2 ring-offset-2 ring-pink-500/50'
                                                        : 'bg-slate-50 dark:bg-zinc-800 border-slate-200 dark:border-zinc-700 text-slate-600 dark:text-zinc-400 hover:border-slate-300'
                                                ]"
                                            >
                                                {{ grade }}
                                            </button>
                                        </div>
                                        <textarea
                                            v-model="form.grades[index].spiritual_description"
                                            rows="2"
                                            placeholder="Deskripsi sikap spiritual..."
                                            class="w-full px-3 py-2 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                   rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-400
                                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                                   transition-all duration-150 resize-none"
                                        />
                                    </div>

                                    <!-- Social Grade -->
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-1 text-sm font-medium text-violet-600 dark:text-violet-400">
                                            <Heart :size="14" />
                                            Nilai Sosial
                                        </label>
                                        <div class="flex gap-2">
                                            <button
                                                v-for="(label, grade) in gradeOptions"
                                                :key="`${student.id}-social-${grade}`"
                                                @click="form.grades[index].social_grade = grade; applyTemplate(index, 'social')"
                                                class="flex-1 py-2 rounded-lg text-sm font-bold border-2 transition-all"
                                                :class="[
                                                    form.grades[index].social_grade === grade
                                                        ? getGradeColor(grade) + ' ring-2 ring-offset-2 ring-violet-500/50'
                                                        : 'bg-slate-50 dark:bg-zinc-800 border-slate-200 dark:border-zinc-700 text-slate-600 dark:text-zinc-400 hover:border-slate-300'
                                                ]"
                                            >
                                                {{ grade }}
                                            </button>
                                        </div>
                                        <textarea
                                            v-model="form.grades[index].social_description"
                                            rows="2"
                                            placeholder="Deskripsi sikap sosial..."
                                            class="w-full px-3 py-2 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                   rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-400
                                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                                   transition-all duration-150 resize-none"
                                        />
                                    </div>

                                    <!-- Homeroom Notes -->
                                    <div class="md:col-span-2 space-y-2">
                                        <label class="flex items-center gap-1 text-sm font-medium text-slate-600 dark:text-zinc-400">
                                            <FileText :size="14" />
                                            Catatan Wali Kelas (Opsional)
                                        </label>
                                        <textarea
                                            v-model="form.grades[index].homeroom_notes"
                                            rows="2"
                                            placeholder="Catatan khusus untuk siswa ini..."
                                            class="w-full px-3 py-2 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                   rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-400
                                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                                   transition-all duration-150 resize-none"
                                        />
                                    </div>
                                </div>
                            </div>
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

                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        @click="submitForm"
                                        :disabled="!formValid || form.processing"
                                        class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold
                                               flex items-center gap-2 shadow-sm shadow-emerald-500/25
                                               disabled:opacity-50 disabled:cursor-not-allowed
                                               transition-colors duration-150"
                                    >
                                        <Save :size="20" />
                                        {{ form.processing ? 'Menyimpan...' : 'Simpan Nilai Sikap' }}
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
