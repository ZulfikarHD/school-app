<script setup lang="ts">
/**
 * Generate Bills Page - Halaman untuk generate tagihan bulk
 * dengan fitur preview, filter kelas, dan konfirmasi sebelum generate
 */
import { ref, computed, watch } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import { ChevronLeft, Receipt, Eye, Loader2, AlertTriangle, CheckCircle2, Users, Banknote } from 'lucide-vue-next';
import { index, store, preview as previewRoute } from '@/routes/admin/payments/bills';
import { Motion } from 'motion-v';

interface PaymentCategory {
    id: number;
    nama: string;
    kode: string;
    tipe: string;
    nominal_default: number;
    due_day: number | null;
}

interface SchoolClass {
    id: number;
    tingkat: number;
    nama: string;
    tahun_ajaran: string;
}

interface PreviewStudent {
    student_id: number;
    nis: string;
    nama_lengkap: string;
    kelas: string;
    kelas_id: number;
    nominal: number;
    formatted_nominal: string;
    is_duplicate: boolean;
}

interface PreviewSummary {
    total_students: number;
    total_nominal: number;
    formatted_total: string;
    duplicate_count: number;
    category_name: string;
    category_type: string;
    bulan: number;
    tahun: number;
}

interface Props {
    categories: PaymentCategory[];
    classes: SchoolClass[];
    currentMonth: number;
    currentYear: number;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

// Form state
const form = useForm({
    payment_category_id: '',
    bulan: String(props.currentMonth),
    tahun: String(props.currentYear),
    class_ids: [] as number[],
    skip_duplicates: true,
});

// Preview state
const isLoadingPreview = ref(false);
const previewData = ref<PreviewStudent[]>([]);
const previewSummary = ref<PreviewSummary | null>(null);
const hasPreview = ref(false);
const showAllStudents = ref(false);

// Computed
const selectedCategory = computed(() => {
    return props.categories.find(c => c.id === Number(form.payment_category_id));
});

const months = [
    { value: '1', label: 'Januari' },
    { value: '2', label: 'Februari' },
    { value: '3', label: 'Maret' },
    { value: '4', label: 'April' },
    { value: '5', label: 'Mei' },
    { value: '6', label: 'Juni' },
    { value: '7', label: 'Juli' },
    { value: '8', label: 'Agustus' },
    { value: '9', label: 'September' },
    { value: '10', label: 'Oktober' },
    { value: '11', label: 'November' },
    { value: '12', label: 'Desember' },
];

const years = computed(() => {
    const currentYear = props.currentYear;
    return Array.from({ length: 5 }, (_, i) => ({
        value: String(currentYear - 2 + i),
        label: String(currentYear - 2 + i),
    }));
});

const canPreview = computed(() => {
    return form.payment_category_id && form.bulan && form.tahun;
});

const visibleStudents = computed(() => {
    if (showAllStudents.value) {
        return previewData.value;
    }
    return previewData.value.slice(0, 10);
});

const newStudentsCount = computed(() => {
    return previewData.value.filter(s => !s.is_duplicate).length;
});

const duplicateStudentsCount = computed(() => {
    return previewData.value.filter(s => s.is_duplicate).length;
});

// Watch for form changes to reset preview
watch(
    () => [form.payment_category_id, form.bulan, form.tahun, form.class_ids],
    () => {
        hasPreview.value = false;
        previewData.value = [];
        previewSummary.value = null;
    },
    { deep: true }
);

// Methods
const getClassName = (kelas: SchoolClass): string => {
    return `Kelas ${kelas.tingkat}${kelas.nama}`;
};

const toggleClassSelection = (classId: number) => {
    haptics.light();
    const index = form.class_ids.indexOf(classId);
    if (index > -1) {
        form.class_ids.splice(index, 1);
    } else {
        form.class_ids.push(classId);
    }
};

const selectAllClasses = () => {
    haptics.light();
    if (form.class_ids.length === props.classes.length) {
        form.class_ids = [];
    } else {
        form.class_ids = props.classes.map(c => c.id);
    }
};

const handlePreview = async () => {
    if (!canPreview.value) return;

    haptics.medium();
    isLoadingPreview.value = true;

    try {
        const response = await fetch(previewRoute().url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                payment_category_id: Number(form.payment_category_id),
                bulan: Number(form.bulan),
                tahun: Number(form.tahun),
                class_ids: form.class_ids.length > 0 ? form.class_ids : null,
            }),
        });

        if (!response.ok) {
            throw new Error('Failed to load preview');
        }

        const data = await response.json();
        previewData.value = data.students;
        previewSummary.value = data.summary;
        hasPreview.value = true;
        haptics.success();
    } catch (error) {
        haptics.error();
        modal.error('Gagal memuat preview. Silakan coba lagi.');
    } finally {
        isLoadingPreview.value = false;
    }
};

const handleGenerate = async () => {
    if (!hasPreview.value || newStudentsCount.value === 0) {
        modal.error('Tidak ada tagihan baru yang bisa di-generate.');
        return;
    }

    const confirmed = await modal.confirm(
        'Konfirmasi Generate Tagihan',
        `Anda akan membuat ${newStudentsCount.value} tagihan baru dengan total ${previewSummary.value?.formatted_total}. Lanjutkan?`,
        'Ya, Generate',
        'Batal'
    );

    if (confirmed) {
        haptics.heavy();
        form.post(store().url, {
            onSuccess: () => {
                haptics.success();
            },
            onError: (errors) => {
                haptics.error();
                modal.error(errors.error || 'Gagal generate tagihan');
            },
        });
    }
};

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const getMonthName = (bulan: number): string => {
    return months.find(m => m.value === String(bulan))?.label || '';
};
</script>

<template>
    <AppLayout title="Generate Tagihan">
        <Head title="Generate Tagihan" />

        <div class="max-w-5xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <Motion :whileTap="{ scale: 0.95 }">
                            <Link
                                :href="index()"
                                @click="haptics.light()"
                                class="w-11 h-11 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 active:bg-slate-200 rounded-xl transition-colors shrink-0
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-500"
                            >
                                <ChevronLeft class="w-5 h-5" />
                            </Link>
                        </Motion>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center shrink-0">
                                    <Receipt class="w-4 h-4 text-violet-600 dark:text-violet-400" />
                                </div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 truncate">
                                    Generate Tagihan
                                </h1>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                Generate tagihan bulk untuk siswa berdasarkan kategori
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <form @submit.prevent="handleGenerate" class="space-y-6">
                <!-- Form Card -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 p-4 sm:p-6 md:p-8">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-6">Konfigurasi Tagihan</h2>

                        <div class="grid gap-6 sm:grid-cols-3">
                            <!-- Kategori Pembayaran -->
                            <div class="sm:col-span-3">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Kategori Pembayaran <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.payment_category_id"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                >
                                    <option value="">Pilih Kategori Pembayaran</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.nama }} ({{ cat.kode }}) - {{ formatCurrency(cat.nominal_default) }}
                                    </option>
                                </select>
                                <p v-if="form.errors.payment_category_id" class="mt-1.5 text-sm text-red-500">{{ form.errors.payment_category_id }}</p>
                            </div>

                            <!-- Bulan -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Bulan <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.bulan"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                >
                                    <option v-for="month in months" :key="month.value" :value="month.value">
                                        {{ month.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.bulan" class="mt-1.5 text-sm text-red-500">{{ form.errors.bulan }}</p>
                            </div>

                            <!-- Tahun -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Tahun <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.tahun"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                >
                                    <option v-for="year in years" :key="year.value" :value="year.value">
                                        {{ year.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.tahun" class="mt-1.5 text-sm text-red-500">{{ form.errors.tahun }}</p>
                            </div>

                            <!-- Skip Duplicates -->
                            <div class="flex items-center">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input
                                        v-model="form.skip_duplicates"
                                        type="checkbox"
                                        class="w-5 h-5 rounded border-slate-300 dark:border-zinc-600 text-violet-500 focus:ring-violet-500 dark:bg-zinc-800"
                                    />
                                    <span class="text-sm text-slate-700 dark:text-slate-300">Skip duplikat</span>
                                </label>
                            </div>

                            <!-- Kelas Filter -->
                            <div class="sm:col-span-3">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Filter Kelas (Opsional)
                                    </label>
                                    <button
                                        type="button"
                                        @click="selectAllClasses"
                                        class="text-sm text-violet-600 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300"
                                    >
                                        {{ form.class_ids.length === classes.length ? 'Hapus Semua' : 'Pilih Semua' }}
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="kelas in classes"
                                        :key="kelas.id"
                                        type="button"
                                        @click="toggleClassSelection(kelas.id)"
                                        :class="[
                                            'px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
                                            form.class_ids.includes(kelas.id)
                                                ? 'bg-violet-500 text-white'
                                                : 'bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700'
                                        ]"
                                    >
                                        {{ getClassName(kelas) }}
                                    </button>
                                </div>
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                    {{ form.class_ids.length === 0 ? 'Kosongkan untuk generate semua kelas' : `${form.class_ids.length} kelas dipilih` }}
                                </p>
                            </div>
                        </div>

                        <!-- Preview Button -->
                        <div class="mt-6 flex justify-end">
                            <Motion :whileTap="{ scale: 0.97 }">
                                <button
                                    type="button"
                                    @click="handlePreview"
                                    :disabled="!canPreview || isLoadingPreview"
                                    class="flex items-center gap-2 px-5 py-2.5 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
                                >
                                    <Loader2 v-if="isLoadingPreview" class="w-5 h-5 animate-spin" />
                                    <Eye v-else class="w-5 h-5" />
                                    {{ isLoadingPreview ? 'Memuat...' : 'Preview' }}
                                </button>
                            </Motion>
                        </div>
                    </div>
                </Motion>

                <!-- Preview Results -->
                <Motion
                    v-if="hasPreview"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut' }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                        <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-zinc-800">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Preview Tagihan</h2>

                            <!-- Summary Cards -->
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="bg-slate-50 dark:bg-zinc-800 rounded-xl p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                            <Users class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                        </div>
                                        <div>
                                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ newStudentsCount }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Siswa Baru</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-slate-50 dark:bg-zinc-800 rounded-xl p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                            <AlertTriangle class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                        </div>
                                        <div>
                                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ duplicateStudentsCount }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Duplikat</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-slate-50 dark:bg-zinc-800 rounded-xl p-4 col-span-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                                            <Banknote class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                                        </div>
                                        <div>
                                            <p class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ previewSummary?.formatted_total }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Total Tagihan Baru</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Duplicate Warning -->
                            <div v-if="duplicateStudentsCount > 0" class="mt-4 flex items-start gap-2 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl text-sm text-amber-700 dark:text-amber-300">
                                <AlertTriangle class="w-5 h-5 shrink-0 mt-0.5" />
                                <p>{{ duplicateStudentsCount }} siswa sudah memiliki tagihan untuk periode ini dan akan dilewati.</p>
                            </div>
                        </div>

                        <!-- Students Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50">
                                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">NIS</th>
                                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Nama</th>
                                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Kelas</th>
                                        <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Nominal</th>
                                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-zinc-800">
                                    <tr
                                        v-for="student in visibleStudents"
                                        :key="student.student_id"
                                        :class="[
                                            'transition-colors',
                                            student.is_duplicate
                                                ? 'bg-amber-50/50 dark:bg-amber-900/10 text-slate-400 dark:text-slate-500'
                                                : 'hover:bg-slate-50 dark:hover:bg-zinc-800/50'
                                        ]"
                                    >
                                        <td class="px-6 py-3 text-sm">{{ student.nis }}</td>
                                        <td class="px-6 py-3 text-sm font-medium" :class="student.is_duplicate ? '' : 'text-slate-900 dark:text-slate-100'">
                                            {{ student.nama_lengkap }}
                                        </td>
                                        <td class="px-6 py-3 text-sm">{{ student.kelas }}</td>
                                        <td class="px-6 py-3 text-sm text-right">{{ student.formatted_nominal }}</td>
                                        <td class="px-6 py-3 text-center">
                                            <span
                                                v-if="student.is_duplicate"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"
                                            >
                                                <AlertTriangle class="w-3 h-3" />
                                                Duplikat
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400"
                                            >
                                                <CheckCircle2 class="w-3 h-3" />
                                                Baru
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Show More -->
                        <div v-if="previewData.length > 10" class="p-4 border-t border-slate-200 dark:border-zinc-800 text-center">
                            <button
                                type="button"
                                @click="showAllStudents = !showAllStudents"
                                class="text-sm text-violet-600 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300 font-medium"
                            >
                                {{ showAllStudents ? 'Tampilkan lebih sedikit' : `Tampilkan semua (${previewData.length} siswa)` }}
                            </button>
                        </div>
                    </div>
                </Motion>

                <!-- Sticky Action Bar -->
                <Motion
                    v-if="hasPreview && newStudentsCount > 0"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                >
                    <div class="sticky bottom-0 z-10 -mx-4 sm:mx-0 px-4 sm:px-0 pb-6 pt-4">
                        <div class="bg-white/98 dark:bg-zinc-900/98 border border-slate-200 dark:border-zinc-800 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl backdrop-blur-sm">
                            <div class="text-center sm:text-left">
                                <p class="text-sm text-slate-600 dark:text-slate-400">
                                    <strong class="text-slate-900 dark:text-slate-100">{{ newStudentsCount }}</strong> tagihan akan di-generate
                                </p>
                                <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ previewSummary?.formatted_total }}</p>
                            </div>
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <Link
                                        :href="index()"
                                        @click="haptics.light()"
                                        class="flex-1 sm:flex-none px-5 py-3 min-h-[48px] text-slate-700 bg-slate-50 border border-slate-300 rounded-xl hover:bg-slate-100 dark:bg-zinc-800 dark:border-zinc-700 dark:text-slate-300 dark:hover:bg-zinc-700 transition-colors font-medium text-center flex items-center justify-center
                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500"
                                    >
                                        Batal
                                    </Link>
                                </Motion>
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-3 min-h-[48px] bg-violet-500 text-white rounded-xl hover:bg-violet-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-lg shadow-violet-500/25 font-semibold
                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                    >
                                        <Loader2 v-if="form.processing" class="w-5 h-5 animate-spin" />
                                        <Receipt v-else class="w-5 h-5" />
                                        <span v-if="form.processing">Generating...</span>
                                        <span v-else>Generate Tagihan</span>
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </div>
                </Motion>
            </form>
        </div>
    </AppLayout>
</template>
