<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import { Save, ChevronLeft, ChevronRight, AlertCircle, Users, BookOpen, Calendar, CheckCircle } from 'lucide-vue-next';
import { store, index } from '@/routes/teacher/grades';

/**
 * Form input nilai baru dengan wizard-style UI
 * Step 1: Pilih kelas, mapel, semester, jenis, judul, tanggal
 * Step 2: Input nilai per siswa dengan table
 */

interface SchoolClass {
    id: number;
    tingkat: number;
    nama: string;
    nama_lengkap: string;
    tahun_ajaran: string;
    jumlah_siswa: number;
    is_wali_kelas: boolean;
}

interface Subject {
    id: number;
    kode_mapel: string;
    nama_mapel: string;
}

interface Student {
    id: number;
    nis: string;
    nama_lengkap: string;
}

interface Props {
    title: string;
    classes: SchoolClass[];
    subjects: Subject[];
    assessmentTypes: Record<string, string>;
    tahunAjaran: string;
    semesters: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

const currentStep = ref(1);
const students = ref<Student[]>([]);
const loadingStudents = ref(false);
const filteredSubjects = ref<Subject[]>([]);

/**
 * Form data untuk submission
 */
const form = useForm({
    class_id: null as number | null,
    subject_id: null as number | null,
    tahun_ajaran: props.tahunAjaran,
    semester: '1',
    assessment_type: 'UH',
    assessment_number: 1 as number | null,
    title: '',
    assessment_date: new Date().toISOString().split('T')[0],
    grades: [] as Array<{
        student_id: number;
        score: number | null;
        notes: string;
    }>,
});

/**
 * Computed untuk validasi step 1
 */
const step1Valid = computed(() => {
    return form.class_id && form.subject_id && form.semester && form.assessment_type && form.title && form.assessment_date;
});

/**
 * Computed untuk validasi step 2 (semua nilai harus diisi 0-100)
 */
const step2Valid = computed(() => {
    return form.grades.length > 0 && form.grades.every(g => g.score !== null && g.score >= 0 && g.score <= 100);
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
 * Fetch students ketika class dipilih
 */
const fetchStudents = async (classId: number) => {
    loadingStudents.value = true;
    
    try {
        const response = await fetch(`/teacher/grades/classes/${classId}/students`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) throw new Error('Failed to fetch students');

        const data = await response.json();
        students.value = data.data || [];

        // Initialize grades array dengan nilai default
        form.grades = students.value.map(student => ({
            student_id: student.id,
            score: null,
            notes: '',
        }));
    } catch (error) {
        console.error('Error fetching students:', error);
        modal.error('Gagal memuat daftar siswa');
        students.value = [];
        form.grades = [];
    } finally {
        loadingStudents.value = false;
    }
};

/**
 * Fetch subjects berdasarkan class yang dipilih
 */
const fetchSubjects = async (classId: number) => {
    try {
        const response = await fetch(`/teacher/grades/classes/${classId}/subjects?tahun_ajaran=${props.tahunAjaran}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) throw new Error('Failed to fetch subjects');

        const data = await response.json();
        filteredSubjects.value = data.data || [];
        
        // Reset subject selection jika subject sebelumnya tidak ada di list baru
        if (form.subject_id && !filteredSubjects.value.find(s => s.id === form.subject_id)) {
            form.subject_id = null;
        }
    } catch (error) {
        console.error('Error fetching subjects:', error);
        filteredSubjects.value = props.subjects;
    }
};

/**
 * Watch class selection
 */
watch(() => form.class_id, (newClassId) => {
    if (newClassId) {
        fetchSubjects(newClassId);
        if (currentStep.value === 2) {
            fetchStudents(newClassId);
        }
    } else {
        filteredSubjects.value = [];
        students.value = [];
        form.grades = [];
    }
});

/**
 * Generate title otomatis berdasarkan assessment type dan number
 */
const generateTitle = () => {
    const typeLabels = props.assessmentTypes;
    const typeLabel = typeLabels[form.assessment_type] || form.assessment_type;
    
    if (form.assessment_type === 'UH' && form.assessment_number) {
        return `${typeLabel} ${form.assessment_number}`;
    }
    return typeLabel;
};

watch([() => form.assessment_type, () => form.assessment_number], () => {
    if (!form.title || form.title.startsWith('UH') || form.title.startsWith('Ulangan') || form.title.startsWith('Ujian') || form.title.startsWith('Praktik') || form.title.startsWith('Proyek')) {
        form.title = generateTitle();
    }
});

/**
 * Navigate ke step berikutnya
 */
const nextStep = () => {
    haptics.light();
    if (currentStep.value === 1 && step1Valid.value && form.class_id) {
        fetchStudents(form.class_id);
        currentStep.value = 2;
    }
};

/**
 * Navigate ke step sebelumnya
 */
const prevStep = () => {
    haptics.light();
    currentStep.value = 1;
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
    if (!step2Valid.value) {
        modal.error('Mohon isi semua nilai dengan angka 0-100');
        return;
    }

    haptics.medium();

    form.post(store().url, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            modal.success('Nilai berhasil disimpan');
        },
        onError: (errors) => {
            haptics.error();
            const message = Object.values(errors).flat().join(', ') || 'Gagal menyimpan nilai';
            modal.error(message);
        },
    });
};

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
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Input nilai UH, UTS, UAS, Praktik, atau Proyek</p>

                        <!-- Step Indicator -->
                        <div class="mt-6 flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <div :class="[
                                    'w-8 h-8 rounded-full flex items-center justify-center font-semibold text-sm transition-colors',
                                    currentStep >= 1 ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-500'
                                ]">
                                    <CheckCircle v-if="currentStep > 1" :size="16" />
                                    <span v-else>1</span>
                                </div>
                                <span :class="['text-sm font-medium', currentStep >= 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500']">
                                    Detail Penilaian
                                </span>
                            </div>

                            <div class="flex-1 h-0.5 bg-gray-200 dark:bg-zinc-700">
                                <div :class="['h-full bg-emerald-500 transition-all', currentStep >= 2 ? 'w-full' : 'w-0']" />
                            </div>

                            <div class="flex items-center gap-2">
                                <div :class="[
                                    'w-8 h-8 rounded-full flex items-center justify-center font-semibold text-sm transition-colors',
                                    currentStep >= 2 ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-500'
                                ]">
                                    2
                                </div>
                                <span :class="['text-sm font-medium', currentStep >= 2 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500']">
                                    Input Nilai
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <div class="mx-auto max-w-4xl px-6 py-8">
                <!-- Step 1: Detail Penilaian -->
                <Motion
                    v-if="currentStep === 1"
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Detail Penilaian</h2>

                        <div class="grid gap-6 md:grid-cols-2">
                            <!-- Kelas -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">
                                    Kelas *
                                </label>
                                <select
                                    v-model="form.class_id"
                                    class="w-full h-[52px] px-4 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-900 dark:text-white
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                           transition-all duration-150"
                                >
                                    <option :value="null">-- Pilih Kelas --</option>
                                    <option v-for="kelas in classes" :key="kelas.id" :value="kelas.id">
                                        {{ kelas.nama_lengkap }} ({{ kelas.jumlah_siswa }} siswa)
                                    </option>
                                </select>
                                <p v-if="form.errors.class_id" class="mt-1 text-sm text-red-500">{{ form.errors.class_id }}</p>
                            </div>

                            <!-- Mata Pelajaran -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">
                                    Mata Pelajaran *
                                </label>
                                <select
                                    v-model="form.subject_id"
                                    :disabled="!form.class_id"
                                    class="w-full h-[52px] px-4 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-900 dark:text-white disabled:opacity-50
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                           transition-all duration-150"
                                >
                                    <option :value="null">-- Pilih Mata Pelajaran --</option>
                                    <option v-for="subject in filteredSubjects" :key="subject.id" :value="subject.id">
                                        {{ subject.nama_mapel }}
                                    </option>
                                </select>
                                <p v-if="!form.class_id" class="mt-1 text-xs text-gray-500">Pilih kelas terlebih dahulu</p>
                                <p v-if="form.errors.subject_id" class="mt-1 text-sm text-red-500">{{ form.errors.subject_id }}</p>
                            </div>

                            <!-- Semester -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">
                                    Semester *
                                </label>
                                <select
                                    v-model="form.semester"
                                    class="w-full h-[52px] px-4 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-900 dark:text-white
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                           transition-all duration-150"
                                >
                                    <option v-for="sem in semesters" :key="sem.value" :value="sem.value">
                                        {{ sem.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Jenis Penilaian -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">
                                    Jenis Penilaian *
                                </label>
                                <select
                                    v-model="form.assessment_type"
                                    class="w-full h-[52px] px-4 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-900 dark:text-white
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                           transition-all duration-150"
                                >
                                    <option v-for="(label, type) in assessmentTypes" :key="type" :value="type">
                                        {{ label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Nomor (untuk UH) -->
                            <div v-if="form.assessment_type === 'UH'">
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">
                                    Nomor UH
                                </label>
                                <input
                                    v-model.number="form.assessment_number"
                                    type="number"
                                    min="1"
                                    max="10"
                                    class="w-full h-[52px] px-4 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-900 dark:text-white
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                           transition-all duration-150"
                                />
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
                                        class="w-full h-[52px] pl-12 pr-4 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                               rounded-xl text-slate-900 dark:text-white
                                               focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                               transition-all duration-150"
                                    />
                                </div>
                                <p v-if="form.errors.assessment_date" class="mt-1 text-sm text-red-500">{{ form.errors.assessment_date }}</p>
                            </div>

                            <!-- Judul -->
                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">
                                    Judul Penilaian *
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    placeholder="Contoh: UH 1 - Perkalian"
                                    class="w-full h-[52px] px-4 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-900 dark:text-white placeholder-slate-400
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                           transition-all duration-150"
                                />
                                <p v-if="form.errors.title" class="mt-1 text-sm text-red-500">{{ form.errors.title }}</p>
                            </div>
                        </div>

                        <!-- Error Summary -->
                        <div v-if="Object.keys(form.errors).length > 0" class="mt-6 p-4 bg-red-50/80 border border-red-200/50 rounded-xl">
                            <div class="flex items-start gap-2">
                                <AlertCircle :size="20" class="text-red-500 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-sm font-medium text-red-800">Terdapat kesalahan:</p>
                                    <ul class="mt-1 text-sm text-red-600 list-disc list-inside">
                                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <div class="mt-8 flex items-center justify-between">
                            <button
                                @click="router.get(index().url)"
                                class="px-6 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700
                                       text-gray-700 dark:text-gray-300 rounded-xl font-semibold
                                       flex items-center gap-2 transition-colors duration-150"
                            >
                                <ChevronLeft :size="20" />
                                Batal
                            </button>

                            <Motion :whileTap="{ scale: 0.97 }">
                                <button
                                    @click="nextStep"
                                    :disabled="!step1Valid"
                                    class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold
                                           flex items-center gap-2 shadow-sm shadow-emerald-500/25
                                           disabled:opacity-50 disabled:cursor-not-allowed
                                           transition-colors duration-150"
                                >
                                    Lanjut ke Input Nilai
                                    <ChevronRight :size="20" />
                                </button>
                            </Motion>
                        </div>
                    </div>
                </Motion>

                <!-- Step 2: Input Nilai -->
                <Motion
                    v-if="currentStep === 2"
                    :initial="{ opacity: 0, x: 20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                >
                    <div class="space-y-6">
                        <!-- Summary Cards -->
                        <div class="grid gap-4 grid-cols-2 md:grid-cols-4">
                            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 shadow-sm p-4">
                                <p class="text-xs font-medium text-slate-600 dark:text-zinc-400">Terisi</p>
                                <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ gradeSummary.count }} / {{ students.length }}</p>
                            </div>
                            <div class="bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800 p-4">
                                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Rata-rata</p>
                                <p class="mt-1 text-2xl font-bold text-emerald-900 dark:text-emerald-100">{{ gradeSummary.average.toFixed(1) }}</p>
                            </div>
                            <div class="bg-blue-50 dark:bg-blue-950/30 rounded-xl border border-blue-200 dark:border-blue-800 p-4">
                                <p class="text-xs font-medium text-blue-600 dark:text-blue-400">Tertinggi</p>
                                <p class="mt-1 text-2xl font-bold text-blue-900 dark:text-blue-100">{{ gradeSummary.max || '-' }}</p>
                            </div>
                            <div class="bg-red-50 dark:bg-red-950/30 rounded-xl border border-red-200 dark:border-red-800 p-4">
                                <p class="text-xs font-medium text-red-600 dark:text-red-400">Terendah</p>
                                <p class="mt-1 text-2xl font-bold text-red-900 dark:text-red-100">{{ gradeSummary.min || '-' }}</p>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-4">
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

                        <!-- Loading -->
                        <div v-if="loadingStudents" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                            <div class="space-y-3">
                                <div v-for="i in 5" :key="i" class="h-16 bg-slate-100 dark:bg-zinc-800 rounded-xl animate-pulse" />
                            </div>
                        </div>

                        <!-- Student Table -->
                        <div v-else class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-slate-200 dark:border-zinc-800">
                                <div class="flex items-center gap-2">
                                    <Users :size="20" class="text-slate-600 dark:text-zinc-400" />
                                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                        Input Nilai - {{ students.length }} Siswa
                                    </h2>
                                </div>
                                <p class="mt-1 text-sm text-slate-600 dark:text-zinc-400">
                                    {{ form.title }} | {{ classes.find(c => c.id === form.class_id)?.nama_lengkap }}
                                </p>
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
                                        <tr v-for="(student, index) in students" :key="student.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-zinc-400">{{ index + 1 }}</td>
                                            <td class="px-6 py-4">
                                                <p class="font-medium text-slate-900 dark:text-white">{{ student.nama_lengkap }}</p>
                                                <p class="text-sm text-slate-500 dark:text-zinc-400">NIS: {{ student.nis }}</p>
                                            </td>
                                            <td class="px-6 py-4">
                                                <input
                                                    v-model.number="form.grades[index].score"
                                                    type="number"
                                                    min="0"
                                                    max="100"
                                                    placeholder="0-100"
                                                    class="w-24 mx-auto block px-3 py-2 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                           rounded-lg text-center text-slate-900 dark:text-white
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
                                                    class="w-full px-3 py-2 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                           rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-400
                                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                                           transition-all duration-150"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Navigation -->
                            <div class="p-6 border-t border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-800/30">
                                <div class="flex items-center justify-between">
                                    <button
                                        @click="prevStep"
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
                                            :disabled="!step2Valid || form.processing"
                                            class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold
                                                   flex items-center gap-2 shadow-sm shadow-emerald-500/25
                                                   disabled:opacity-50 disabled:cursor-not-allowed
                                                   transition-colors duration-150"
                                        >
                                            <Save :size="20" />
                                            {{ form.processing ? 'Menyimpan...' : 'Simpan Nilai' }}
                                        </button>
                                    </Motion>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>
