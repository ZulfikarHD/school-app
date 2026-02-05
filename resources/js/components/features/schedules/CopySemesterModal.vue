<script setup lang="ts">
/**
 * CopySemesterModal Component - Modal untuk menyalin jadwal dari semester sebelumnya
 * dengan pemilihan tahun ajaran asal dan tujuan
 */
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Copy, X, CalendarDays, AlertCircle, ChevronRight } from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';

interface AcademicYear {
    id: number;
    name: string;
    is_active: boolean;
}

interface Props {
    show: boolean;
    academicYears: AcademicYear[];
    currentAcademicYearId?: number | null;
}

const props = defineProps<Props>();
const emit = defineEmits(['close']);

const haptics = useHaptics();

// Form state
const fromAcademicYearId = ref<number | string>('');
const toAcademicYearId = ref<number | string>(props.currentAcademicYearId ?? '');
const isProcessing = ref(false);
const error = ref<string | null>(null);

// Get available "from" years (exclude active/current)
const fromYears = computed(() => {
    return props.academicYears.filter(y => y.id !== Number(toAcademicYearId.value));
});

// Get available "to" years
const toYears = computed(() => {
    return props.academicYears.filter(y => y.id !== Number(fromAcademicYearId.value));
});

// Check if form is valid
const isValid = computed(() => {
    return fromAcademicYearId.value && toAcademicYearId.value && fromAcademicYearId.value !== toAcademicYearId.value;
});

// Get selected year names for display
const selectedFromYear = computed(() => {
    return props.academicYears.find(y => y.id === Number(fromAcademicYearId.value));
});

const selectedToYear = computed(() => {
    return props.academicYears.find(y => y.id === Number(toAcademicYearId.value));
});

// Close modal
const close = () => {
    haptics.selection();
    error.value = null;
    emit('close');
};

// Submit copy request
const submit = () => {
    if (!isValid.value) return;

    isProcessing.value = true;
    error.value = null;
    haptics.impact('medium');

    router.post('/admin/teachers/schedules/copy-semester', {
        from_academic_year_id: fromAcademicYearId.value,
        to_academic_year_id: toAcademicYearId.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            close();
        },
        onError: (errors) => {
            haptics.error();
            error.value = errors.error || 'Gagal menyalin jadwal. Silakan coba lagi.';
        },
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};

// Reset form when modal opens
const resetForm = () => {
    fromAcademicYearId.value = '';
    toAcademicYearId.value = props.currentAcademicYearId ?? '';
    error.value = null;
};

// Watch for modal open
defineExpose({ resetForm });
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <!-- Backdrop -->
            <div
                class="fixed inset-0 bg-black/50 backdrop-blur-sm"
                @click="close"
            />

            <!-- Modal -->
            <div class="relative bg-white dark:bg-zinc-800 rounded-2xl shadow-xl max-w-md w-full overflow-hidden">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-zinc-700">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                            <Copy class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                                Salin Jadwal
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Dari semester sebelumnya
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="close"
                        class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors"
                    >
                        <X class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6 space-y-6">
                    <!-- Error Alert -->
                    <div v-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <AlertCircle class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" />
                            <p class="text-sm text-red-700 dark:text-red-300">{{ error }}</p>
                        </div>
                    </div>

                    <!-- From Year -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            <div class="flex items-center gap-2">
                                <CalendarDays class="w-4 h-4" />
                                <span>Tahun Ajaran Asal <span class="text-red-500">*</span></span>
                            </div>
                        </label>
                        <select
                            v-model="fromAcademicYearId"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-700 border border-slate-200 dark:border-zinc-600 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                            <option value="">Pilih tahun ajaran asal</option>
                            <option v-for="year in fromYears" :key="year.id" :value="year.id">
                                {{ year.name }}{{ year.is_active ? ' (Aktif)' : '' }}
                            </option>
                        </select>
                    </div>

                    <!-- Arrow -->
                    <div class="flex justify-center">
                        <div class="p-2 bg-slate-100 dark:bg-zinc-700 rounded-full">
                            <ChevronRight class="w-5 h-5 text-slate-500 dark:text-slate-400 rotate-90" />
                        </div>
                    </div>

                    <!-- To Year -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            <div class="flex items-center gap-2">
                                <CalendarDays class="w-4 h-4" />
                                <span>Tahun Ajaran Tujuan <span class="text-red-500">*</span></span>
                            </div>
                        </label>
                        <select
                            v-model="toAcademicYearId"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-700 border border-slate-200 dark:border-zinc-600 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                            <option value="">Pilih tahun ajaran tujuan</option>
                            <option v-for="year in toYears" :key="year.id" :value="year.id">
                                {{ year.name }}{{ year.is_active ? ' (Aktif)' : '' }}
                            </option>
                        </select>
                    </div>

                    <!-- Preview -->
                    <div v-if="selectedFromYear && selectedToYear" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Semua jadwal aktif dari <strong>{{ selectedFromYear.name }}</strong> akan disalin ke
                            <strong>{{ selectedToYear.name }}</strong>.
                        </p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                            Jadwal yang konflik atau sudah ada akan dilewati.
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center gap-3 px-6 py-4 border-t border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800/50">
                    <button
                        type="button"
                        @click="close"
                        class="flex-1 px-4 py-3 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-slate-300 rounded-xl transition-colors hover:bg-white dark:hover:bg-zinc-700"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="!isValid || isProcessing"
                        class="flex-1 px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        <Copy v-if="!isProcessing" class="w-4 h-4" />
                        <svg v-else class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ isProcessing ? 'Menyalin...' : 'Salin Jadwal' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
