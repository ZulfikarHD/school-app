<script setup lang="ts">
/**
 * GradeWeights Page - Halaman konfigurasi bobot nilai K13
 * untuk mengatur persentase UH, UTS, UAS, dan Praktik
 * dengan validasi total = 100%
 */
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import { Settings, Save, RotateCcw, History, AlertCircle, CheckCircle } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { index, update } from '@/routes/admin/settings/grade-weights';

interface WeightConfig {
    id: number;
    tahun_ajaran: string;
    uh_weight: number;
    uts_weight: number;
    uas_weight: number;
    praktik_weight: number;
    total_weight: number;
    is_valid: boolean;
    is_default: boolean;
    updated_at: string | null;
}

interface HistoryItem {
    id: number;
    action: string;
    user_name: string;
    changes: {
        old: { uh_weight: number; uts_weight: number; uas_weight: number; praktik_weight: number } | null;
        new: { uh_weight: number; uts_weight: number; uas_weight: number; praktik_weight: number };
    };
    created_at: string;
}

interface Props {
    config: WeightConfig;
    availableTahunAjaran: string[];
    currentTahunAjaran: string;
    history: HistoryItem[];
    defaultWeights: {
        uh: number;
        uts: number;
        uas: number;
        praktik: number;
    };
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

const form = useForm({
    tahun_ajaran: props.currentTahunAjaran,
    uh_weight: props.config.uh_weight,
    uts_weight: props.config.uts_weight,
    uas_weight: props.config.uas_weight,
    praktik_weight: props.config.praktik_weight,
});

/**
 * Computed: Total bobot dan validasi
 */
const totalWeight = computed(() => {
    return form.uh_weight + form.uts_weight + form.uas_weight + form.praktik_weight;
});

const isValid = computed(() => totalWeight.value === 100);

const weightDiff = computed(() => 100 - totalWeight.value);

/**
 * Watch perubahan tahun ajaran untuk reload data
 */
watch(() => form.tahun_ajaran, (newVal) => {
    if (newVal !== props.currentTahunAjaran) {
        router.visit(index.url({ tahun_ajaran: newVal }), {
            preserveState: false,
        });
    }
});

/**
 * Update form ketika props berubah
 */
watch(() => props.config, (newConfig) => {
    form.uh_weight = newConfig.uh_weight;
    form.uts_weight = newConfig.uts_weight;
    form.uas_weight = newConfig.uas_weight;
    form.praktik_weight = newConfig.praktik_weight;
}, { deep: true });

/**
 * Handler: Submit form
 */
const handleSubmit = async () => {
    if (!isValid.value) {
        haptics.error();
        modal.error('Total bobot harus 100%. Saat ini: ' + totalWeight.value + '%');
        return;
    }

    const confirmed = await modal.confirm(
        'Simpan Perubahan',
        'Apakah Anda yakin ingin menyimpan konfigurasi bobot nilai ini?',
        'Ya, Simpan',
        'Batal'
    );

    if (confirmed) {
        haptics.medium();
        form.put(update().url, {
            onSuccess: () => {
                modal.success('Konfigurasi bobot nilai berhasil disimpan.');
                haptics.success();
            },
            onError: () => {
                haptics.error();
            }
        });
    }
};

/**
 * Handler: Reset ke default
 */
const handleReset = async () => {
    const confirmed = await modal.confirm(
        'Reset ke Default',
        'Apakah Anda yakin ingin mereset bobot nilai ke pengaturan default K13?',
        'Ya, Reset',
        'Batal'
    );

    if (confirmed) {
        haptics.medium();
        form.uh_weight = props.defaultWeights.uh;
        form.uts_weight = props.defaultWeights.uts;
        form.uas_weight = props.defaultWeights.uas;
        form.praktik_weight = props.defaultWeights.praktik;
    }
};

/**
 * Weight items untuk iterasi
 */
const weightItems = computed(() => [
    {
        key: 'uh_weight',
        label: 'Ulangan Harian (UH)',
        description: 'Rata-rata nilai ulangan harian',
        color: 'from-blue-500 to-blue-600',
        bgColor: 'bg-blue-100 dark:bg-blue-900/30',
        textColor: 'text-blue-700 dark:text-blue-400'
    },
    {
        key: 'uts_weight',
        label: 'Ujian Tengah Semester (UTS)',
        description: 'Nilai ujian tengah semester',
        color: 'from-violet-500 to-violet-600',
        bgColor: 'bg-violet-100 dark:bg-violet-900/30',
        textColor: 'text-violet-700 dark:text-violet-400'
    },
    {
        key: 'uas_weight',
        label: 'Ujian Akhir Semester (UAS)',
        description: 'Nilai ujian akhir semester',
        color: 'from-emerald-500 to-emerald-600',
        bgColor: 'bg-emerald-100 dark:bg-emerald-900/30',
        textColor: 'text-emerald-700 dark:text-emerald-400'
    },
    {
        key: 'praktik_weight',
        label: 'Praktik/Proyek',
        description: 'Rata-rata nilai praktikum dan proyek',
        color: 'from-amber-500 to-amber-600',
        bgColor: 'bg-amber-100 dark:bg-amber-900/30',
        textColor: 'text-amber-700 dark:text-amber-400'
    }
]);
</script>

<template>
    <AppLayout>
        <Head title="Konfigurasi Bobot Nilai" />

        <div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-violet-400 to-violet-600 flex items-center justify-center shadow-lg shadow-violet-500/25 shrink-0">
                                <Settings class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Konfigurasi Bobot Nilai
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Atur bobot komponen nilai sesuai Kurikulum 2013
                                </p>
                            </div>
                        </div>

                        <!-- Tahun Ajaran Selector -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-400">
                                Tahun Ajaran:
                            </label>
                            <select
                                v-model="form.tahun_ajaran"
                                class="px-3 py-2 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"
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
                    </div>
                </div>
            </Motion>

            <!-- Form Card -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <form @submit.prevent="handleSubmit" class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <!-- Weight Inputs -->
                    <div class="p-4 sm:p-6 space-y-4">
                        <div
                            v-for="item in weightItems"
                            :key="item.key"
                            class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 rounded-xl border border-slate-100 dark:border-zinc-800"
                        >
                            <div class="flex-1">
                                <h3 class="font-semibold text-slate-900 dark:text-slate-100">
                                    {{ item.label }}
                                </h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ item.description }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <input
                                    :id="item.key"
                                    v-model.number="form[item.key as keyof typeof form]"
                                    type="number"
                                    min="0"
                                    max="100"
                                    class="w-20 px-3 py-2 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-center font-semibold focus:outline-none focus:ring-2 focus:ring-violet-500"
                                />
                                <span class="text-slate-500 dark:text-slate-400 font-medium">%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total Weight Indicator -->
                    <div class="px-4 sm:px-6 pb-4">
                        <div
                            :class="[
                                'p-4 rounded-xl flex items-center justify-between',
                                isValid
                                    ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800'
                                    : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <CheckCircle
                                    v-if="isValid"
                                    class="w-5 h-5 text-emerald-500"
                                />
                                <AlertCircle
                                    v-else
                                    class="w-5 h-5 text-red-500"
                                />
                                <span
                                    :class="[
                                        'font-medium',
                                        isValid
                                            ? 'text-emerald-700 dark:text-emerald-400'
                                            : 'text-red-700 dark:text-red-400'
                                    ]"
                                >
                                    Total Bobot
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    :class="[
                                        'text-2xl font-bold',
                                        isValid
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-red-600 dark:text-red-400'
                                    ]"
                                >
                                    {{ totalWeight }}%
                                </span>
                                <span
                                    v-if="!isValid"
                                    :class="[
                                        'text-sm',
                                        weightDiff > 0 ? 'text-amber-600' : 'text-red-600'
                                    ]"
                                >
                                    ({{ weightDiff > 0 ? '+' : '' }}{{ weightDiff }}%)
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Form Errors -->
                    <div v-if="form.errors.total_weight || form.errors.error" class="px-4 sm:px-6 pb-4">
                        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                            <p class="text-sm text-red-700 dark:text-red-400">
                                {{ form.errors.total_weight || form.errors.error }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="px-4 sm:px-6 py-4 bg-slate-50 dark:bg-zinc-800/50 border-t border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row gap-3 sm:justify-between">
                        <Motion :whileTap="{ scale: 0.97 }">
                            <button
                                type="button"
                                @click="handleReset"
                                class="flex items-center justify-center gap-2 px-4 py-2.5 min-h-[44px] rounded-xl border border-slate-200 dark:border-zinc-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                            >
                                <RotateCcw class="w-4 h-4" />
                                <span>Reset ke Default</span>
                            </button>
                        </Motion>

                        <Motion :whileTap="{ scale: 0.97 }">
                            <button
                                type="submit"
                                :disabled="!isValid || form.processing"
                                class="flex items-center justify-center gap-2 px-6 py-2.5 min-h-[44px] bg-linear-to-r from-violet-500 to-purple-500 text-white rounded-xl hover:from-violet-600 hover:to-purple-600 transition-all duration-200 shadow-lg shadow-violet-500/30 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <Save class="w-4 h-4" />
                                <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                            </button>
                        </Motion>
                    </div>
                </form>
            </Motion>

            <!-- History Card -->
            <Motion
                v-if="history.length > 0"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <div class="px-4 sm:px-6 py-4 border-b border-slate-100 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <History class="w-5 h-5 text-slate-500" />
                            <h2 class="font-semibold text-slate-900 dark:text-slate-100">
                                Riwayat Perubahan
                            </h2>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <div
                            v-for="item in history"
                            :key="item.id"
                            class="px-4 sm:px-6 py-3 flex items-start gap-3"
                        >
                            <div class="w-2 h-2 mt-2 rounded-full bg-violet-500 shrink-0" />
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-medium text-slate-900 dark:text-slate-100">
                                        {{ item.user_name }}
                                    </span>
                                    <span class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ item.action === 'created' ? 'membuat' : 'mengubah' }} konfigurasi
                                    </span>
                                </div>
                                <div v-if="item.changes.new" class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    UH: {{ item.changes.new.uh_weight }}%,
                                    UTS: {{ item.changes.new.uts_weight }}%,
                                    UAS: {{ item.changes.new.uas_weight }}%,
                                    Praktik: {{ item.changes.new.praktik_weight }}%
                                </div>
                                <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                    {{ item.created_at }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
