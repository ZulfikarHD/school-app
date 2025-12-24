<script setup lang="ts">
/**
 * PromoteWizard - 3-step wizard untuk naik kelas massal (bulk promote)
 * yang mencakup pemilihan tahun ajaran, kelas, dan preview siswa dengan checkbox
 * untuk memastikan flow yang jelas dan error handling yang proper
 */
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowRight, ArrowLeft, Check, GraduationCap, Users, Eye, Loader2 } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import FormSelect from '@/components/ui/Form/FormSelect.vue';
import FormCheckbox from '@/components/ui/Form/FormCheckbox.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import { promote as promoteRoute } from '@/routes/admin/students';
import type { Student } from '@/types/student';

interface SchoolClass {
    id: number;
    nama: string;
    nama_lengkap: string;
    tingkat: number;
    tahun_ajaran: string;
}

interface Props {
    classes: SchoolClass[];
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

// State
const currentStep = ref(1);
const isLoading = ref(false);
const previewStudents = ref<Student[]>([]);
const selectedStudentIds = ref<number[]>([]);

// Form Data
const form = ref({
    tahun_ajaran_asal: '',
    tahun_ajaran_tujuan: '',
    kelas_id_asal: null as number | null,
    kelas_id_tujuan: null as number | null,
});

// Computed
const tahunAjaranOptions = computed(() => {
    const uniqueYears = [...new Set(props.classes.map(c => c.tahun_ajaran))];
    return uniqueYears.map(year => ({ value: year, label: year }));
});

const kelasAsalOptions = computed(() => {
    if (!form.value.tahun_ajaran_asal) return [];

    const filtered = props.classes.filter(c => c.tahun_ajaran === form.value.tahun_ajaran_asal);
    return filtered.map(c => ({
        value: c.id,
        label: c.nama_lengkap,
    }));
});

const kelasTujuanOptions = computed(() => {
    if (!form.value.tahun_ajaran_tujuan || !form.value.kelas_id_asal) return [];

    // Get source class tingkat
    const sourceClass = props.classes.find(c => c.id === form.value.kelas_id_asal);
    if (!sourceClass) return [];

    const targetTingkat = sourceClass.tingkat + 1;

    // Filter classes by target year and next tingkat
    const filtered = props.classes.filter(
        c => c.tahun_ajaran === form.value.tahun_ajaran_tujuan && c.tingkat === targetTingkat
    );

    return filtered.map(c => ({
        value: c.id,
        label: c.nama_lengkap,
    }));
});

const canGoToStep2 = computed(() => {
    return form.value.tahun_ajaran_asal && form.value.tahun_ajaran_tujuan;
});

const canGoToStep3 = computed(() => {
    return form.value.kelas_id_asal && form.value.kelas_id_tujuan;
});

const canSubmit = computed(() => {
    return selectedStudentIds.value.length > 0;
});

const allStudentsSelected = computed(() => {
    return previewStudents.value.length > 0 &&
           selectedStudentIds.value.length === previewStudents.value.length;
});

const sourceClassName = computed(() => {
    const kelas = props.classes.find(c => c.id === form.value.kelas_id_asal);
    return kelas?.nama_lengkap || '';
});

const targetClassName = computed(() => {
    const kelas = props.classes.find(c => c.id === form.value.kelas_id_tujuan);
    return kelas?.nama_lengkap || '';
});

// Methods
const goToStep = (step: number) => {
    if (step === 2 && !canGoToStep2.value) return;
    if (step === 3 && !canGoToStep3.value) return;

    haptics.light();
    currentStep.value = step;

    // Load preview when entering step 3
    if (step === 3) {
        loadPreviewStudents();
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
    } else if (currentStep.value === 2 && canGoToStep3.value) {
        goToStep(3);
    }
};

const loadPreviewStudents = async () => {
    if (!form.value.kelas_id_asal) return;

    isLoading.value = true;

    try {
        // Fetch students from source class using axios
        const response = await fetch(`/admin/students?kelas_id=${form.value.kelas_id_asal}&per_page=1000`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch students');
        }

        const data = await response.json();
        previewStudents.value = data.data || [];

        // Select all by default
        selectedStudentIds.value = previewStudents.value.map(s => s.id);
    } catch {
        modal.error('Gagal memuat data siswa');
        previewStudents.value = [];
    } finally {
        isLoading.value = false;
    }
};

const toggleAllStudents = () => {
    haptics.light();
    if (allStudentsSelected.value) {
        selectedStudentIds.value = [];
    } else {
        selectedStudentIds.value = previewStudents.value.map(s => s.id);
    }
};

const toggleStudent = (studentId: number) => {
    const index = selectedStudentIds.value.indexOf(studentId);
    if (index > -1) {
        selectedStudentIds.value.splice(index, 1);
    } else {
        selectedStudentIds.value.push(studentId);
    }
};

const handleSubmit = async () => {
    if (!canSubmit.value) return;

    const confirmed = await modal.dialog({
        type: 'warning',
        icon: 'question',
        title: 'Konfirmasi Naik Kelas',
        message: `Yakin ingin menaikkan <b>${selectedStudentIds.value.length} siswa</b> dari kelas <b>${sourceClassName.value}</b> ke kelas <b>${targetClassName.value}</b>?`,
        confirmText: 'Ya, Proses',
        cancelText: 'Batal',
        showCancel: true,
        allowHtml: true,
    });

    if (!confirmed) return;

    haptics.medium();
    isLoading.value = true;

    try {
        router.post(
            promoteRoute().url,
            {
                student_ids: selectedStudentIds.value,
                kelas_id_baru: form.value.kelas_id_tujuan,
                tahun_ajaran_baru: form.value.tahun_ajaran_tujuan,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    haptics.heavy();
                    modal.success(`Berhasil menaikkan ${selectedStudentIds.value.length} siswa ke kelas ${targetClassName.value}!`);
                    resetWizard();
                },
                onError: (errors) => {
                    modal.error(errors?.message || 'Gagal memproses naik kelas');
                },
                onFinish: () => {
                    isLoading.value = false;
                },
            }
        );
    } catch {
        isLoading.value = false;
        modal.error('Terjadi kesalahan saat memproses naik kelas');
    }
};

const resetWizard = () => {
    currentStep.value = 1;
    form.value = {
        tahun_ajaran_asal: '',
        tahun_ajaran_tujuan: '',
        kelas_id_asal: null,
        kelas_id_tujuan: null,
    };
    previewStudents.value = [];
    selectedStudentIds.value = [];
};

// Watch for source class change to clear target class
watch(() => form.value.kelas_id_asal, () => {
    form.value.kelas_id_tujuan = null;
});
</script>

<template>
    <div class="max-w-4xl mx-auto">
        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <Motion
                    v-for="step in 3"
                    :key="step"
                    :whileTap="{ scale: 0.97 }"
                    class="flex-1"
                    :class="step < 3 ? 'mr-2' : ''"
                >
                    <button
                        type="button"
                        @click="goToStep(step)"
                        :disabled="
                            (step === 2 && !canGoToStep2) ||
                            (step === 3 && !canGoToStep3)
                        "
                        class="w-full flex items-center gap-3 p-4 rounded-xl transition-all duration-200"
                        :class="[
                            currentStep === step
                                ? 'bg-emerald-50 dark:bg-emerald-950/30 border-2 border-emerald-500/50'
                                : currentStep > step
                                  ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800'
                                  : 'bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800',
                            (step === 2 && !canGoToStep2) || (step === 3 && !canGoToStep3)
                                ? 'opacity-50 cursor-not-allowed'
                                : 'cursor-pointer hover:border-emerald-300 dark:hover:border-emerald-700',
                        ]"
                    >
                        <!-- Step Number/Icon -->
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full transition-all duration-200"
                            :class="
                                currentStep === step
                                    ? 'bg-emerald-500 text-white'
                                    : currentStep > step
                                      ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400'
                                      : 'bg-slate-200 dark:bg-zinc-800 text-slate-400 dark:text-slate-500'
                            "
                        >
                            <Check v-if="currentStep > step" class="w-5 h-5" />
                            <span v-else class="font-semibold text-sm">{{ step }}</span>
                        </div>

                        <!-- Step Label -->
                        <div class="flex-1 text-left">
                            <p
                                class="text-xs font-semibold uppercase tracking-wide"
                                :class="
                                    currentStep >= step
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-slate-400 dark:text-slate-500'
                                "
                            >
                                Langkah {{ step }}
                            </p>
                            <p
                                class="text-sm font-medium mt-0.5"
                                :class="
                                    currentStep >= step
                                        ? 'text-slate-900 dark:text-slate-100'
                                        : 'text-slate-500 dark:text-slate-400'
                                "
                            >
                                {{
                                    step === 1
                                        ? 'Pilih Tahun Ajaran'
                                        : step === 2
                                          ? 'Pilih Kelas'
                                          : 'Preview & Konfirmasi'
                                }}
                            </p>
                        </div>
                    </button>
                </Motion>
            </div>
        </div>

        <!-- Step Content -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden">
            <!-- Step 1: Tahun Ajaran -->
            <div v-if="currentStep === 1" class="p-6 space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/30">
                        <GraduationCap class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            Pilih Tahun Ajaran
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Tentukan tahun ajaran asal dan tahun ajaran tujuan
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <FormSelect
                        v-model="form.tahun_ajaran_asal"
                        label="Tahun Ajaran Asal"
                        :options="tahunAjaranOptions"
                        placeholder="Pilih tahun ajaran asal"
                        required
                    />

                    <FormSelect
                        v-model="form.tahun_ajaran_tujuan"
                        label="Tahun Ajaran Tujuan"
                        :options="tahunAjaranOptions"
                        placeholder="Pilih tahun ajaran tujuan"
                        required
                    />
                </div>
            </div>

            <!-- Step 2: Kelas -->
            <div v-if="currentStep === 2" class="p-6 space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/30">
                        <Users class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            Pilih Kelas
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Pilih kelas asal dan kelas tujuan untuk naik kelas
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <FormSelect
                        v-model="form.kelas_id_asal"
                        label="Kelas Asal"
                        :options="kelasAsalOptions"
                        placeholder="Pilih kelas asal"
                        required
                    />

                    <FormSelect
                        v-model="form.kelas_id_tujuan"
                        label="Kelas Tujuan"
                        :options="kelasTujuanOptions"
                        placeholder="Pilih kelas tujuan"
                        :disabled="!form.kelas_id_asal"
                        required
                    />

                    <div
                        v-if="form.kelas_id_asal && kelasTujuanOptions.length === 0"
                        class="p-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 rounded-xl"
                    >
                        <p class="text-sm text-amber-700 dark:text-amber-400">
                            Tidak ada kelas tujuan yang tersedia untuk tingkat berikutnya pada tahun ajaran tujuan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Step 3: Preview -->
            <div v-if="currentStep === 3" class="p-6 space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/30">
                        <Eye class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            Preview & Konfirmasi
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Pilih siswa yang akan naik kelas
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                            {{ selectedStudentIds.length }} dari {{ previewStudents.length }} siswa
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ sourceClassName }} → {{ targetClassName }}
                        </p>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="isLoading" class="flex items-center justify-center py-12">
                    <Loader2 class="w-8 h-8 text-emerald-500 animate-spin" />
                </div>

                <!-- Student List -->
                <div v-else-if="previewStudents.length > 0" class="space-y-3">
                    <!-- Select All -->
                    <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-zinc-800/50 rounded-xl">
                        <FormCheckbox
                            :modelValue="allStudentsSelected"
                            @update:modelValue="toggleAllStudents"
                            label="Pilih Semua Siswa"
                        />
                    </div>

                    <!-- Student Items -->
                    <div class="max-h-[400px] overflow-y-auto space-y-2 pr-2">
                        <Motion
                            v-for="student in previewStudents"
                            :key="student.id"
                            :whileTap="{ scale: 0.98 }"
                        >
                            <label
                                class="flex items-center gap-3 p-4 bg-slate-50/50 dark:bg-zinc-800/30 hover:bg-slate-50 dark:hover:bg-zinc-800/50 border border-slate-200 dark:border-zinc-800 rounded-xl cursor-pointer transition-all duration-200"
                                :class="
                                    selectedStudentIds.includes(student.id)
                                        ? 'border-emerald-500/50 bg-emerald-50/30 dark:bg-emerald-950/20'
                                        : ''
                                "
                            >
                                <input
                                    type="checkbox"
                                    :checked="selectedStudentIds.includes(student.id)"
                                    @change="toggleStudent(student.id)"
                                    class="w-5 h-5 text-emerald-500 rounded border-slate-300 focus:ring-emerald-500 focus:ring-offset-0"
                                />
                                <div class="flex-1">
                                    <p class="font-medium text-slate-900 dark:text-slate-100">
                                        {{ student.nama_lengkap }}
                                    </p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        NIS: {{ student.nis }}
                                        <span class="mx-2">•</span>
                                        {{ student.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </p>
                                </div>
                            </label>
                        </Motion>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <Users class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-700 mb-4" />
                    <p class="text-slate-500 dark:text-slate-400">Tidak ada siswa di kelas ini</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 p-6 bg-slate-50 dark:bg-zinc-900/50 border-t border-slate-200 dark:border-zinc-800">
                <Motion v-if="currentStep > 1" :whileTap="{ scale: 0.97 }" class="flex-none">
                    <button
                        type="button"
                        @click="prevStep"
                        class="flex items-center gap-2 px-4 h-[48px] bg-white dark:bg-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-700 text-slate-700 dark:text-slate-200 font-medium rounded-xl border border-slate-300 dark:border-zinc-700 transition-colors duration-200"
                    >
                        <ArrowLeft class="w-4 h-4" />
                        Kembali
                    </button>
                </Motion>

                <div class="flex-1" />

                <Motion v-if="currentStep < 3" :whileTap="{ scale: 0.97 }">
                    <button
                        type="button"
                        @click="nextStep"
                        :disabled="
                            (currentStep === 1 && !canGoToStep2) ||
                            (currentStep === 2 && !canGoToStep3)
                        "
                        class="flex items-center gap-2 px-6 h-[48px] bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-xl shadow-sm shadow-emerald-500/25 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Lanjut
                        <ArrowRight class="w-4 h-4" />
                    </button>
                </Motion>

                <Motion v-if="currentStep === 3" :whileTap="{ scale: 0.97 }">
                    <button
                        type="button"
                        @click="handleSubmit"
                        :disabled="!canSubmit || isLoading"
                        class="flex items-center gap-2 px-6 h-[48px] bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-xl shadow-sm shadow-emerald-500/25 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
                        <Check v-else class="w-4 h-4" />
                        Proses Naik Kelas
                    </button>
                </Motion>
            </div>
        </div>
    </div>
</template>
