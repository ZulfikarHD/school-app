<script setup lang="ts">
/**
 * Parent Payment Submit Page - Halaman untuk upload bukti transfer
 *
 * Menampilkan ringkasan tagihan yang dipilih dan form untuk upload
 * bukti pembayaran dengan informasi rekening bank sekolah
 */
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import {
    ArrowLeft, Receipt, Upload, Calendar, Building2, AlertCircle,
    FileText, Image, X, CheckCircle2, Loader2, User, CreditCard
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { index as paymentsIndex } from '@/routes/parent/payments';
import { store as submitPaymentStore } from '@/routes/parent/payments/submit';

interface Bill {
    id: number;
    nomor_tagihan: string;
    student: {
        id: number;
        nama_lengkap: string;
        nis: string;
        kelas: string;
    };
    category: {
        id: number;
        nama: string;
        kode: string;
    };
    periode: string;
    sisa_tagihan: number;
    formatted_sisa: string;
    formatted_due_date: string;
}

interface BankInfo {
    bank_name: string;
    account_number: string;
    account_name: string;
    note: string;
}

interface Props {
    bills: Bill[];
    totalAmount: number;
    formattedTotal: string;
    bankInfo: BankInfo;
}

const props = defineProps<Props>();

const haptics = useHaptics();

// Form
const form = useForm({
    bill_ids: props.bills.map(b => b.id),
    bukti_transfer: null as File | null,
    tanggal_bayar: new Date().toISOString().split('T')[0],
    catatan: '',
});

// File upload state
const filePreview = ref<string | null>(null);
const isDragging = ref(false);

// Computed
const hasFile = computed(() => form.bukti_transfer !== null);

// Error handling - collect all bill_ids.* errors
const billIdErrors = computed(() => {
    const errors: string[] = [];
    if (form.errors) {
        Object.keys(form.errors).forEach((key) => {
            if (key.startsWith('bill_ids.')) {
                errors.push((form.errors as Record<string, string>)[key]);
            }
        });
    }
    return errors;
});

const hasErrors = computed(() => {
    return form.errors.error || form.errors.bill_ids || billIdErrors.value.length > 0;
});
const isImage = computed(() => {
    if (!form.bukti_transfer) return false;
    return form.bukti_transfer.type.startsWith('image/');
});
const isPdf = computed(() => {
    if (!form.bukti_transfer) return false;
    return form.bukti_transfer.type === 'application/pdf';
});
const fileName = computed(() => form.bukti_transfer?.name || '');
const fileSize = computed(() => {
    if (!form.bukti_transfer) return '';
    const size = form.bukti_transfer.size;
    if (size < 1024) return `${size} B`;
    if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;
    return `${(size / (1024 * 1024)).toFixed(1)} MB`;
});

// Methods
const handleFileSelect = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        processFile(input.files[0]);
    }
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
        processFile(event.dataTransfer.files[0]);
    }
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = () => {
    isDragging.value = false;
};

const processFile = (file: File) => {
    haptics.light();

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    if (!allowedTypes.includes(file.type)) {
        alert('File harus berupa JPG, PNG, atau PDF');
        return;
    }

    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran file maksimal 5MB');
        return;
    }

    form.bukti_transfer = file;

    // Create preview for images
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            filePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    } else {
        filePreview.value = null;
    }
};

const removeFile = () => {
    haptics.light();
    form.bukti_transfer = null;
    filePreview.value = null;
};

const submitPayment = () => {
    haptics.medium();

    form.post(submitPaymentStore().url, {
        forceFormData: true,
        onSuccess: () => {
            haptics.success();
        },
        onError: () => {
            haptics.error();
        },
    });
};

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <AppLayout>
        <Head title="Konfirmasi Pembayaran" />

        <div class="max-w-2xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <Link
                            :href="paymentsIndex().url"
                            @click="haptics.light()"
                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors shrink-0"
                        >
                            <ArrowLeft class="w-5 h-5" />
                        </Link>
                        <div class="w-12 h-12 rounded-xl bg-linear-to-br from-violet-400 to-violet-600 flex items-center justify-center shadow-lg shadow-violet-500/25 shrink-0">
                            <CreditCard class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                Konfirmasi Pembayaran
                            </h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                Upload bukti transfer untuk verifikasi
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Total Amount Card -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
            >
                <div class="bg-linear-to-br from-violet-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg shadow-violet-500/25">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-violet-100 text-sm">Total Pembayaran</p>
                            <p class="text-3xl font-bold mt-1">{{ formattedTotal }}</p>
                            <p class="text-violet-200 text-sm mt-2">{{ bills.length }} tagihan</p>
                        </div>
                        <div class="w-14 h-14 rounded-xl bg-white/20 flex items-center justify-center">
                            <Receipt class="w-7 h-7" />
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Bills Summary -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200 dark:border-zinc-800">
                        <h2 class="font-semibold text-slate-900 dark:text-slate-100">Tagihan yang Dibayar</h2>
                    </div>
                    <div class="divide-y divide-slate-200 dark:divide-zinc-800">
                        <div
                            v-for="bill in bills"
                            :key="bill.id"
                            class="p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-900 dark:text-slate-100">
                                        {{ bill.category.nama }}
                                    </p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ bill.periode }} • {{ bill.student.nama_lengkap }}
                                    </p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                        {{ bill.nomor_tagihan }}
                                    </p>
                                </div>
                                <p class="font-bold text-slate-900 dark:text-slate-100 shrink-0">
                                    {{ bill.formatted_sisa }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Bank Info -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
            >
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-4 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                            <Building2 class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-blue-900 dark:text-blue-100">Transfer ke Rekening</p>
                            <div class="mt-2 space-y-1">
                                <p class="text-blue-800 dark:text-blue-200">
                                    <span class="font-semibold">{{ bankInfo.bank_name }}</span>
                                </p>
                                <p class="text-blue-800 dark:text-blue-200 font-mono text-lg font-bold">
                                    {{ bankInfo.account_number }}
                                </p>
                                <p class="text-blue-700 dark:text-blue-300 text-sm">
                                    a.n. {{ bankInfo.account_name }}
                                </p>
                            </div>
                            <p v-if="bankInfo.note" class="text-blue-600 dark:text-blue-400 text-sm mt-3 flex items-start gap-2">
                                <AlertCircle class="w-4 h-4 shrink-0 mt-0.5" />
                                {{ bankInfo.note }}
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Upload Form -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
            >
                <form @submit.prevent="submitPayment" class="space-y-4">
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-200 dark:border-zinc-800">
                            <h2 class="font-semibold text-slate-900 dark:text-slate-100">Upload Bukti Transfer</h2>
                        </div>
                        <div class="p-4 space-y-4">
                            <!-- File Upload Area -->
                            <div>
                                <div
                                    v-if="!hasFile"
                                    @drop="handleDrop"
                                    @dragover="handleDragOver"
                                    @dragleave="handleDragLeave"
                                    :class="[
                                        'relative border-2 border-dashed rounded-xl p-8 text-center transition-colors',
                                        isDragging
                                            ? 'border-violet-400 bg-violet-50 dark:bg-violet-900/20'
                                            : 'border-slate-300 dark:border-zinc-700 hover:border-violet-400'
                                    ]"
                                >
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/jpg,image/png,application/pdf"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        @change="handleFileSelect"
                                    />
                                    <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                                        <Upload class="w-8 h-8 text-violet-600 dark:text-violet-400" />
                                    </div>
                                    <p class="font-medium text-slate-900 dark:text-slate-100">
                                        Klik atau seret file ke sini
                                    </p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                        JPG, PNG, atau PDF (maks. 5MB)
                                    </p>
                                </div>

                                <!-- File Preview -->
                                <div
                                    v-else
                                    class="relative border border-slate-200 dark:border-zinc-700 rounded-xl p-4"
                                >
                                    <div class="flex items-start gap-4">
                                        <!-- Image Preview -->
                                        <div
                                            v-if="isImage && filePreview"
                                            class="w-24 h-24 rounded-lg overflow-hidden bg-slate-100 dark:bg-zinc-800 shrink-0"
                                        >
                                            <img :src="filePreview" alt="Preview" class="w-full h-full object-cover" />
                                        </div>
                                        <!-- PDF Icon -->
                                        <div
                                            v-else-if="isPdf"
                                            class="w-24 h-24 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0"
                                        >
                                            <FileText class="w-10 h-10 text-red-600 dark:text-red-400" />
                                        </div>

                                        <!-- File Info -->
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-slate-900 dark:text-slate-100 truncate">
                                                {{ fileName }}
                                            </p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                                {{ fileSize }}
                                            </p>
                                            <div class="flex items-center gap-1 mt-2 text-emerald-600 dark:text-emerald-400 text-sm">
                                                <CheckCircle2 class="w-4 h-4" />
                                                <span>File siap diupload</span>
                                            </div>
                                        </div>

                                        <!-- Remove Button -->
                                        <button
                                            type="button"
                                            @click="removeFile"
                                            class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-colors"
                                        >
                                            <X class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                                <p v-if="form.errors.bukti_transfer" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.bukti_transfer }}
                                </p>
                            </div>

                            <!-- Payment Date -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Tanggal Bayar
                                </label>
                                <div class="relative">
                                    <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                                    <input
                                        type="date"
                                        v-model="form.tanggal_bayar"
                                        :max="new Date().toISOString().split('T')[0]"
                                        class="w-full pl-10 pr-4 py-3 border border-slate-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-colors"
                                    />
                                </div>
                                <p v-if="form.errors.tanggal_bayar" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.tanggal_bayar }}
                                </p>
                            </div>

                            <!-- Notes (optional) -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Catatan <span class="text-slate-400">(opsional)</span>
                                </label>
                                <textarea
                                    v-model="form.catatan"
                                    rows="3"
                                    placeholder="Tambahkan catatan jika diperlukan..."
                                    class="w-full px-4 py-3 border border-slate-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-colors resize-none"
                                ></textarea>
                                <p v-if="form.errors.catatan" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.catatan }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="hasErrors" class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4 border border-red-200 dark:border-red-800">
                        <div class="flex items-start gap-3">
                            <AlertCircle class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                            <div class="space-y-1">
                                <p v-if="form.errors.error" class="text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.error }}
                                </p>
                                <p v-if="form.errors.bill_ids" class="text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.bill_ids }}
                                </p>
                                <template v-for="(error, key) in billIdErrors" :key="key">
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing || !hasFile"
                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-4 bg-violet-500 text-white rounded-xl font-semibold hover:bg-violet-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-lg shadow-violet-500/25"
                    >
                        <Loader2 v-if="form.processing" class="w-5 h-5 animate-spin" />
                        <Upload v-else class="w-5 h-5" />
                        {{ form.processing ? 'Mengirim...' : 'Submit Pembayaran' }}
                    </button>

                    <!-- Info -->
                    <p class="text-center text-sm text-slate-500 dark:text-slate-400">
                        Pembayaran akan diverifikasi oleh Admin dalam 1x24 jam
                    </p>
                </form>
            </Motion>
        </div>
    </AppLayout>
</template>
