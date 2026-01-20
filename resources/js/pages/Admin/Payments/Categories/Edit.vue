<script setup lang="ts">
/**
 * Payment Category Edit Page - Halaman edit kategori pembayaran
 * dengan form validation dan support untuk harga khusus per kelas
 */
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import { ChevronLeft, Wallet, Save, Plus, Trash2, Info } from 'lucide-vue-next';
import { index, update } from '@/routes/admin/payment-categories';
import { Motion } from 'motion-v';

interface SchoolClass {
    id: number;
    tingkat: number;
    nama: string;
    tahun_ajaran: string;
}

interface ClassPrice {
    id?: number;
    class_id: number;
    nominal: number;
    school_class?: SchoolClass;
}

interface PaymentCategory {
    id: number;
    nama: string;
    kode: string;
    deskripsi: string | null;
    tipe: 'bulanan' | 'tahunan' | 'insidental';
    nominal_default: number;
    is_active: boolean;
    is_mandatory: boolean;
    due_day: number | null;
    tahun_ajaran: string | null;
    class_prices?: ClassPrice[];
}

interface Props {
    category: PaymentCategory;
    classes: SchoolClass[];
}

const props = defineProps<Props>();

// Initialize form with existing data
const form = useForm({
    nama: props.category.nama,
    kode: props.category.kode,
    deskripsi: props.category.deskripsi || '',
    tipe: props.category.tipe,
    nominal_default: props.category.nominal_default,
    is_active: props.category.is_active,
    is_mandatory: props.category.is_mandatory,
    due_day: props.category.due_day || 10,
    tahun_ajaran: props.category.tahun_ajaran || '',
    class_prices: (props.category.class_prices || []).map(cp => ({
        class_id: cp.class_id,
        nominal: cp.nominal,
    })) as Array<{ class_id: number | null; nominal: number }>,
});

const haptics = useHaptics();
const modal = useModal();
const showClassPrices = ref((props.category.class_prices || []).length > 0);

// Format currency input
const formatCurrencyInput = (value: number): string => {
    return new Intl.NumberFormat('id-ID').format(value);
};

// Add class price row
const addClassPrice = () => {
    form.class_prices.push({ class_id: null, nominal: 0 });
};

// Remove class price row
const removeClassPrice = (index: number) => {
    form.class_prices.splice(index, 1);
};

// Get class name
const getClassName = (classItem: SchoolClass): string => {
    return `Kelas ${classItem.tingkat}${classItem.nama} - ${classItem.tahun_ajaran}`;
};

// Available classes (excluding already selected)
const availableClasses = computed(() => {
    const selectedIds = form.class_prices.map(p => p.class_id).filter(Boolean);
    return props.classes.filter(c => !selectedIds.includes(c.id));
});

const submit = () => {
    haptics.medium();
    
    // Filter out empty class prices
    const cleanedClassPrices = form.class_prices.filter(p => p.class_id && p.nominal > 0);
    
    form.transform((data) => ({
        ...data,
        class_prices: showClassPrices.value ? cleanedClassPrices : [],
    })).put(update(props.category.id).url, {
        onSuccess: () => {
            haptics.success();
            modal.success('Kategori pembayaran berhasil diupdate.');
        },
        onError: () => {
            haptics.error();
            modal.error('Gagal mengupdate kategori pembayaran. Periksa kembali input Anda.');
        },
    });
};
</script>

<template>
    <AppLayout title="Edit Kategori Pembayaran">
        <Head :title="`Edit ${category.nama}`" />

        <div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
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
                                    <Wallet class="w-4 h-4 text-violet-600 dark:text-violet-400" />
                                </div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 truncate">
                                    Edit Kategori
                                </h1>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                {{ category.nama }} ({{ category.kode }})
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info Card -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 p-4 sm:p-6 md:p-8">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-6">Informasi Dasar</h2>
                        
                        <div class="grid gap-6 sm:grid-cols-2">
                            <!-- Nama -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Nama Kategori <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.nama"
                                    type="text"
                                    placeholder="contoh: SPP Bulanan"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                />
                                <p v-if="form.errors.nama" class="mt-1.5 text-sm text-red-500">{{ form.errors.nama }}</p>
                            </div>

                            <!-- Kode -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Kode <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.kode"
                                    type="text"
                                    placeholder="contoh: SPP_BULANAN"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all font-mono uppercase"
                                />
                                <p v-if="form.errors.kode" class="mt-1.5 text-sm text-red-500">{{ form.errors.kode }}</p>
                            </div>

                            <!-- Tipe -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Tipe Pembayaran <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.tipe"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                >
                                    <option value="bulanan">Bulanan</option>
                                    <option value="tahunan">Tahunan</option>
                                    <option value="insidental">Insidental</option>
                                </select>
                                <p v-if="form.errors.tipe" class="mt-1.5 text-sm text-red-500">{{ form.errors.tipe }}</p>
                            </div>

                            <!-- Nominal Default -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Nominal Default <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400">Rp</span>
                                    <input
                                        v-model.number="form.nominal_default"
                                        type="number"
                                        min="0"
                                        step="1000"
                                        placeholder="0"
                                        class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                    />
                                </div>
                                <p v-if="form.errors.nominal_default" class="mt-1.5 text-sm text-red-500">{{ form.errors.nominal_default }}</p>
                            </div>

                            <!-- Due Day -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Tanggal Jatuh Tempo (1-28)
                                </label>
                                <input
                                    v-model.number="form.due_day"
                                    type="number"
                                    min="1"
                                    max="28"
                                    placeholder="10"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                />
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Tanggal jatuh tempo setiap bulan</p>
                                <p v-if="form.errors.due_day" class="mt-1.5 text-sm text-red-500">{{ form.errors.due_day }}</p>
                            </div>

                            <!-- Tahun Ajaran -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Tahun Ajaran
                                </label>
                                <input
                                    v-model="form.tahun_ajaran"
                                    type="text"
                                    placeholder="2025/2026"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                />
                                <p v-if="form.errors.tahun_ajaran" class="mt-1.5 text-sm text-red-500">{{ form.errors.tahun_ajaran }}</p>
                            </div>

                            <!-- Deskripsi -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Deskripsi
                                </label>
                                <textarea
                                    v-model="form.deskripsi"
                                    rows="3"
                                    placeholder="Deskripsi kategori pembayaran (opsional)"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all resize-none"
                                />
                                <p v-if="form.errors.deskripsi" class="mt-1.5 text-sm text-red-500">{{ form.errors.deskripsi }}</p>
                            </div>

                            <!-- Toggles -->
                            <div class="sm:col-span-2 flex flex-wrap gap-6">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="w-5 h-5 rounded border-slate-300 dark:border-zinc-600 text-violet-500 focus:ring-violet-500 dark:bg-zinc-800"
                                    />
                                    <span class="text-sm text-slate-700 dark:text-slate-300">Aktif</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input
                                        v-model="form.is_mandatory"
                                        type="checkbox"
                                        class="w-5 h-5 rounded border-slate-300 dark:border-zinc-600 text-violet-500 focus:ring-violet-500 dark:bg-zinc-800"
                                    />
                                    <span class="text-sm text-slate-700 dark:text-slate-300">Wajib Bayar</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Class-specific Pricing (Optional) -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 p-4 sm:p-6 md:p-8">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Harga per Kelas</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Opsional: atur harga berbeda untuk kelas tertentu</p>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="showClassPrices"
                                    type="checkbox"
                                    class="w-5 h-5 rounded border-slate-300 dark:border-zinc-600 text-violet-500 focus:ring-violet-500 dark:bg-zinc-800"
                                />
                                <span class="text-sm text-slate-700 dark:text-slate-300">Aktifkan</span>
                            </label>
                        </div>

                        <div v-if="showClassPrices" class="space-y-4">
                            <div class="flex items-start gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-sm text-blue-700 dark:text-blue-300">
                                <Info class="w-5 h-5 shrink-0 mt-0.5" />
                                <p>Jika kelas tidak didefinisikan, nominal default akan digunakan.</p>
                            </div>

                            <!-- Class Price Rows -->
                            <div v-for="(classPrice, idx) in form.class_prices" :key="idx" class="flex items-center gap-3">
                                <select
                                    v-model="classPrice.class_id"
                                    class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent"
                                >
                                    <option :value="null">Pilih Kelas</option>
                                    <option v-for="kelas in availableClasses" :key="kelas.id" :value="kelas.id">
                                        {{ getClassName(kelas) }}
                                    </option>
                                    <!-- Include currently selected class -->
                                    <option v-if="classPrice.class_id && !availableClasses.find(c => c.id === classPrice.class_id)" :value="classPrice.class_id">
                                        {{ getClassName(classes.find(c => c.id === classPrice.class_id)!) }}
                                    </option>
                                </select>
                                <div class="relative w-40">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 text-sm">Rp</span>
                                    <input
                                        v-model.number="classPrice.nominal"
                                        type="number"
                                        min="0"
                                        step="1000"
                                        class="w-full pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-violet-500"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="removeClassPrice(idx)"
                                    class="p-2 text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                >
                                    <Trash2 class="w-5 h-5" />
                                </button>
                            </div>

                            <!-- Add Button -->
                            <button
                                v-if="availableClasses.length > 0"
                                type="button"
                                @click="addClassPrice"
                                class="flex items-center gap-2 px-4 py-2 text-violet-600 dark:text-violet-400 hover:bg-violet-50 dark:hover:bg-violet-900/20 rounded-xl transition-colors text-sm font-medium"
                            >
                                <Plus class="w-4 h-4" />
                                Tambah Kelas
                            </button>
                        </div>
                    </div>
                </Motion>

                <!-- Sticky Action Bar -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
                >
                    <div class="sticky bottom-0 z-10 -mx-4 sm:mx-0 px-4 sm:px-0 pb-6 pt-4">
                        <div class="bg-white/98 dark:bg-zinc-900/98 border border-slate-200 dark:border-zinc-800 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl backdrop-blur-sm">
                            <p class="text-sm text-slate-600 dark:text-slate-400 hidden sm:block">
                                Pastikan semua data sudah benar sebelum menyimpan
                            </p>
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
                                        <Save class="w-5 h-5" />
                                        <span v-if="form.processing">Menyimpan...</span>
                                        <span v-else>Update</span>
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
