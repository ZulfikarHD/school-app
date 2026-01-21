<script setup lang="ts">
/**
 * Admin Report Cards Generate Page - Wizard untuk generate rapor bulk
 * dengan 4 langkah: Selection, Validation, Progress, dan Result
 */
import { ref, computed, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import {
    FileText,
    ArrowRight,
    ArrowLeft,
    Check,
    X,
    AlertTriangle,
    Loader2,
    Download,
    CheckCircle,
    XCircle,
    Users,
    GraduationCap
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { index, processGenerate, validate as validateRoute, downloadZip } from '@/routes/admin/report-cards';

interface ClassOption {
    id: number;
    name: string;
    tingkat: number;
    wali_kelas: string;
    student_count: number;
}

interface StudentValidation {
    student_id: number;
    student_name: string;
    nis: string;
    is_complete: boolean;
    missing: string[];
}

interface ValidationResult {
    is_complete: boolean;
    students: StudentValidation[];
    missing_count: number;
    complete_count: number;
    total_students: number;
}

interface GenerateResult {
    class_id: number;
    class_name: string;
    generated_count: number;
    failed_count: number;
    success: boolean;
}

interface Props {
    classes: ClassOption[];
    defaultFilters: {
        tahun_ajaran: string;
        semester: string;
    };
    availableTahunAjaran: string[];
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

// State
const currentStep = ref(1);
const isLoading = ref(false);
const isGenerating = ref(false);
const validationResults = ref<Record<number, ValidationResult>>({});
const generateResults = ref<GenerateResult[]>([]);
const totalGenerated = ref(0);
const totalFailed = ref(0);

// Form Data
const form = ref({
    tahun_ajaran: props.defaultFilters.tahun_ajaran,
    semester: props.defaultFilters.semester,
    class_ids: [] as number[]
});

// Computed
const selectedClasses = computed(() => {
    return props.classes.filter(c => form.value.class_ids.includes(c.id));
});

const canGoToStep2 = computed(() => {
    return form.value.tahun_ajaran &&
           form.value.semester &&
           form.value.class_ids.length > 0;
});

const allValidationComplete = computed(() => {
    if (form.value.class_ids.length === 0) return false;

    return form.value.class_ids.every(id => validationResults.value[id]?.is_complete);
});

const hasAnyInvalidStudents = computed(() => {
    return Object.values(validationResults.value).some(v => v.missing_count > 0);
});

const totalStudentsToGenerate = computed(() => {
    return Object.values(validationResults.value).reduce((sum, v) => sum + v.complete_count, 0);
});

// Step definitions
const steps = [
    { number: 1, title: 'Pilih Kelas', icon: GraduationCap },
    { number: 2, title: 'Validasi Data', icon: Check },
    { number: 3, title: 'Generate', icon: Loader2 },
    { number: 4, title: 'Selesai', icon: CheckCircle },
];

// Methods
const goToStep = (step: number) => {
    haptics.light();
    currentStep.value = step;

    if (step === 2) {
        loadValidation();
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        haptics.light();
        currentStep.value--;
    }
};

const nextStep = () => {
    if (currentStep.value === 1 && canGoToStep2.value) {
        goToStep(2);
    } else if (currentStep.value === 2 && allValidationComplete.value) {
        handleGenerate();
    }
};

const toggleClass = (classId: number) => {
    haptics.light();
    const index = form.value.class_ids.indexOf(classId);
    if (index > -1) {
        form.value.class_ids.splice(index, 1);
    } else {
        form.value.class_ids.push(classId);
    }
};

const selectAllClasses = () => {
    haptics.light();
    if (form.value.class_ids.length === props.classes.length) {
        form.value.class_ids = [];
    } else {
        form.value.class_ids = props.classes.map(c => c.id);
    }
};

const loadValidation = async () => {
    isLoading.value = true;
    validationResults.value = {};

    try {
        // Validate each selected class
        for (const classId of form.value.class_ids) {
            const response = await fetch(validateRoute.url(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    class_id: classId,
                    tahun_ajaran: form.value.tahun_ajaran,
                    semester: form.value.semester
                })
            });

            if (response.ok) {
                const data = await response.json();
                validationResults.value[classId] = data;
            } else {
                const className = props.classes.find(c => c.id === classId)?.name || 'Unknown';
                modal.error(`Gagal memvalidasi kelas ${className}`);
            }
        }
    } catch (error) {
        modal.error('Terjadi kesalahan saat memvalidasi data');
    } finally {
        isLoading.value = false;
    }
};

const handleGenerate = async () => {
    if (!allValidationComplete.value) {
        const confirmed = await modal.dialog({
            type: 'warning',
            icon: 'warning',
            title: 'Data Tidak Lengkap',
            message: 'Beberapa siswa memiliki data nilai yang belum lengkap. Hanya siswa dengan data lengkap yang akan di-generate. Lanjutkan?',
            confirmText: 'Ya, Lanjutkan',
            cancelText: 'Batal',
            showCancel: true
        });

        if (!confirmed) return;
    }

    haptics.medium();
    currentStep.value = 3;
    isGenerating.value = true;
    generateResults.value = [];
    totalGenerated.value = 0;
    totalFailed.value = 0;

    try {
        const response = await fetch(processGenerate.url(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                class_ids: form.value.class_ids,
                tahun_ajaran: form.value.tahun_ajaran,
                semester: form.value.semester
            })
        });

        if (response.ok) {
            const data = await response.json();
            generateResults.value = data.results || [];
            totalGenerated.value = data.total_generated || 0;
            totalFailed.value = data.total_failed || 0;
            currentStep.value = 4;

            if (data.success) {
                haptics.success();
            } else {
                haptics.warning();
            }
        } else {
            throw new Error('Generate failed');
        }
    } catch (error) {
        modal.error('Terjadi kesalahan saat generate rapor');
        currentStep.value = 2;
    } finally {
        isGenerating.value = false;
    }
};

const handleDownloadZip = (classId: number) => {
    haptics.medium();
    window.open(downloadZip.url({
        class_id: classId.toString(),
        tahun_ajaran: form.value.tahun_ajaran,
        semester: form.value.semester
    }), '_blank');
};

const handleFinish = () => {
    haptics.light();
    router.visit(index());
};

const resetWizard = () => {
    haptics.light();
    currentStep.value = 1;
    form.value.class_ids = [];
    validationResults.value = {};
    generateResults.value = [];
    totalGenerated.value = 0;
    totalFailed.value = 0;
};
</script>

<template>
    <AppLayout>
        <Head title="Generate Rapor" />

        <div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="w-12 h-12 rounded-xl bg-linear-to-br from-indigo-400 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/25 shrink-0">
                            <FileText class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                Generate Rapor
                            </h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                Generate rapor siswa secara bulk per kelas
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Step Indicator -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center justify-between">
                        <template v-for="(step, idx) in steps" :key="step.number">
                            <div class="flex items-center gap-2">
                                <div
                                    :class="[
                                        'w-10 h-10 rounded-full flex items-center justify-center transition-colors',
                                        currentStep >= step.number
                                            ? 'bg-indigo-500 text-white'
                                            : 'bg-slate-100 dark:bg-zinc-800 text-slate-400'
                                    ]"
                                >
                                    <component
                                        :is="currentStep > step.number ? Check : step.icon"
                                        class="w-5 h-5"
                                    />
                                </div>
                                <span
                                    :class="[
                                        'text-sm font-medium hidden sm:inline',
                                        currentStep >= step.number
                                            ? 'text-slate-900 dark:text-slate-100'
                                            : 'text-slate-400'
                                    ]"
                                >
                                    {{ step.title }}
                                </span>
                            </div>
                            <div
                                v-if="idx < steps.length - 1"
                                :class="[
                                    'flex-1 h-1 mx-2 rounded-full',
                                    currentStep > step.number
                                        ? 'bg-indigo-500'
                                        : 'bg-slate-200 dark:bg-zinc-700'
                                ]"
                            />
                        </template>
                    </div>
                </div>
            </Motion>

            <!-- Step Content -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <!-- Step 1: Selection -->
                    <div v-if="currentStep === 1" class="p-4 sm:p-6">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                            Pilih Tahun Ajaran, Semester, dan Kelas
                        </h2>

                        <!-- Filters -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                    Tahun Ajaran
                                </label>
                                <select
                                    v-model="form.tahun_ajaran"
                                    class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
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
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                    Semester
                                </label>
                                <select
                                    v-model="form.semester"
                                    class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="1">Semester 1 (Ganjil)</option>
                                    <option value="2">Semester 2 (Genap)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Class Selection -->
                        <div class="mb-4 flex items-center justify-between">
                            <label class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                Pilih Kelas ({{ form.class_ids.length }} dipilih)
                            </label>
                            <button
                                type="button"
                                @click="selectAllClasses"
                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
                            >
                                {{ form.class_ids.length === classes.length ? 'Batalkan Semua' : 'Pilih Semua' }}
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <button
                                v-for="cls in classes"
                                :key="cls.id"
                                type="button"
                                @click="toggleClass(cls.id)"
                                :class="[
                                    'p-4 rounded-xl border-2 text-left transition-all',
                                    form.class_ids.includes(cls.id)
                                        ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
                                        : 'border-slate-200 dark:border-zinc-700 hover:border-slate-300 dark:hover:border-zinc-600'
                                ]"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">
                                        {{ cls.name }}
                                    </span>
                                    <div
                                        :class="[
                                            'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                                            form.class_ids.includes(cls.id)
                                                ? 'border-indigo-500 bg-indigo-500'
                                                : 'border-slate-300 dark:border-zinc-600'
                                        ]"
                                    >
                                        <Check
                                            v-if="form.class_ids.includes(cls.id)"
                                            class="w-3 h-3 text-white"
                                        />
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ cls.student_count }} siswa • {{ cls.wali_kelas }}
                                </p>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Validation -->
                    <div v-if="currentStep === 2" class="p-4 sm:p-6">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                            Validasi Kelengkapan Data
                        </h2>

                        <div v-if="isLoading" class="flex flex-col items-center justify-center py-12">
                            <Loader2 class="w-12 h-12 text-indigo-500 animate-spin mb-4" />
                            <p class="text-slate-500 dark:text-slate-400">Memvalidasi data nilai...</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="cls in selectedClasses"
                                :key="cls.id"
                                class="border border-slate-200 dark:border-zinc-700 rounded-xl overflow-hidden"
                            >
                                <div
                                    :class="[
                                        'px-4 py-3 flex items-center justify-between',
                                        validationResults[cls.id]?.is_complete
                                            ? 'bg-emerald-50 dark:bg-emerald-900/20'
                                            : 'bg-amber-50 dark:bg-amber-900/20'
                                    ]"
                                >
                                    <div class="flex items-center gap-3">
                                        <component
                                            :is="validationResults[cls.id]?.is_complete ? CheckCircle : AlertTriangle"
                                            :class="[
                                                'w-5 h-5',
                                                validationResults[cls.id]?.is_complete
                                                    ? 'text-emerald-500'
                                                    : 'text-amber-500'
                                            ]"
                                        />
                                        <span class="font-semibold text-slate-900 dark:text-slate-100">
                                            {{ cls.name }}
                                        </span>
                                    </div>
                                    <span
                                        :class="[
                                            'text-sm font-medium',
                                            validationResults[cls.id]?.is_complete
                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                : 'text-amber-600 dark:text-amber-400'
                                        ]"
                                    >
                                        {{ validationResults[cls.id]?.complete_count || 0 }}/{{ validationResults[cls.id]?.total_students || 0 }} lengkap
                                    </span>
                                </div>

                                <!-- Show incomplete students -->
                                <div
                                    v-if="validationResults[cls.id]?.missing_count > 0"
                                    class="px-4 py-3 space-y-2 max-h-48 overflow-y-auto"
                                >
                                    <div
                                        v-for="student in validationResults[cls.id]?.students.filter(s => !s.is_complete)"
                                        :key="student.student_id"
                                        class="flex items-start gap-2 text-sm"
                                    >
                                        <XCircle class="w-4 h-4 text-red-500 shrink-0 mt-0.5" />
                                        <div>
                                            <span class="font-medium text-slate-700 dark:text-slate-300">
                                                {{ student.student_name }} ({{ student.nis }})
                                            </span>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ student.missing.slice(0, 3).join(', ') }}
                                                <span v-if="student.missing.length > 3">
                                                    dan {{ student.missing.length - 3 }} lainnya
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary -->
                            <div class="p-4 bg-slate-50 dark:bg-zinc-800 rounded-xl">
                                <p class="text-sm text-slate-600 dark:text-slate-400">
                                    Total siswa yang akan di-generate:
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">
                                        {{ totalStudentsToGenerate }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Progress -->
                    <div v-if="currentStep === 3" class="p-4 sm:p-6">
                        <div class="flex flex-col items-center justify-center py-12">
                            <Loader2 class="w-16 h-16 text-indigo-500 animate-spin mb-4" />
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">
                                Generating Rapor...
                            </h2>
                            <p class="text-slate-500 dark:text-slate-400 text-center">
                                Mohon tunggu, proses ini mungkin memakan waktu beberapa menit
                            </p>
                        </div>
                    </div>

                    <!-- Step 4: Result -->
                    <div v-if="currentStep === 4" class="p-4 sm:p-6">
                        <div class="text-center mb-6">
                            <div
                                :class="[
                                    'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4',
                                    totalFailed === 0
                                        ? 'bg-emerald-100 dark:bg-emerald-900/30'
                                        : 'bg-amber-100 dark:bg-amber-900/30'
                                ]"
                            >
                                <component
                                    :is="totalFailed === 0 ? CheckCircle : AlertTriangle"
                                    :class="[
                                        'w-8 h-8',
                                        totalFailed === 0
                                            ? 'text-emerald-500'
                                            : 'text-amber-500'
                                    ]"
                                />
                            </div>
                            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-2">
                                {{ totalFailed === 0 ? 'Generate Berhasil!' : 'Generate Selesai dengan Peringatan' }}
                            </h2>
                            <p class="text-slate-500 dark:text-slate-400">
                                {{ totalGenerated }} rapor berhasil di-generate
                                <span v-if="totalFailed > 0">, {{ totalFailed }} gagal</span>
                            </p>
                        </div>

                        <!-- Results per class -->
                        <div class="space-y-3 mb-6">
                            <div
                                v-for="result in generateResults"
                                :key="result.class_id"
                                :class="[
                                    'p-4 rounded-xl flex items-center justify-between',
                                    result.success
                                        ? 'bg-emerald-50 dark:bg-emerald-900/20'
                                        : 'bg-amber-50 dark:bg-amber-900/20'
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <component
                                        :is="result.success ? CheckCircle : AlertTriangle"
                                        :class="[
                                            'w-5 h-5',
                                            result.success ? 'text-emerald-500' : 'text-amber-500'
                                        ]"
                                    />
                                    <span class="font-medium text-slate-900 dark:text-slate-100">
                                        {{ result.class_name }}
                                    </span>
                                    <span class="text-sm text-slate-500 dark:text-slate-400">
                                        ({{ result.generated_count }} rapor)
                                    </span>
                                </div>
                                <Motion :whileTap="{ scale: 0.95 }">
                                    <button
                                        type="button"
                                        @click="handleDownloadZip(result.class_id)"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors"
                                    >
                                        <Download class="w-4 h-4" />
                                        Download ZIP
                                    </button>
                                </Motion>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-center gap-3">
                            <Motion :whileTap="{ scale: 0.97 }">
                                <button
                                    type="button"
                                    @click="resetWizard"
                                    class="px-4 py-2.5 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors font-medium"
                                >
                                    Generate Lagi
                                </button>
                            </Motion>
                            <Motion :whileTap="{ scale: 0.97 }">
                                <button
                                    type="button"
                                    @click="handleFinish"
                                    class="px-4 py-2.5 bg-indigo-500 text-white rounded-xl hover:bg-indigo-600 transition-colors font-medium"
                                >
                                    Selesai
                                </button>
                            </Motion>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div
                        v-if="currentStep < 3"
                        class="px-4 sm:px-6 py-4 bg-slate-50 dark:bg-zinc-800/50 border-t border-slate-200 dark:border-zinc-800 flex items-center justify-between"
                    >
                        <Motion :whileTap="{ scale: 0.97 }">
                            <button
                                v-if="currentStep > 1"
                                type="button"
                                @click="prevStep"
                                class="flex items-center gap-2 px-4 py-2.5 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-zinc-800 transition-colors font-medium"
                            >
                                <ArrowLeft class="w-4 h-4" />
                                Kembali
                            </button>
                            <Link
                                v-else
                                :href="index()"
                                class="flex items-center gap-2 px-4 py-2.5 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-zinc-800 transition-colors font-medium"
                            >
                                <ArrowLeft class="w-4 h-4" />
                                Batal
                            </Link>
                        </Motion>

                        <Motion :whileTap="{ scale: 0.97 }">
                            <button
                                type="button"
                                @click="nextStep"
                                :disabled="(currentStep === 1 && !canGoToStep2) || (currentStep === 2 && isLoading)"
                                :class="[
                                    'flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium transition-colors',
                                    (currentStep === 1 && !canGoToStep2) || (currentStep === 2 && isLoading)
                                        ? 'bg-slate-200 dark:bg-zinc-700 text-slate-400 cursor-not-allowed'
                                        : 'bg-indigo-500 text-white hover:bg-indigo-600'
                                ]"
                            >
                                <span v-if="currentStep === 2">
                                    {{ allValidationComplete ? 'Generate Rapor' : 'Generate (Data Tidak Lengkap)' }}
                                </span>
                                <span v-else>Lanjut</span>
                                <ArrowRight class="w-4 h-4" />
                            </button>
                        </Motion>
                    </div>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
