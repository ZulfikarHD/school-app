<script setup lang="ts">
/**
 * Parent PSB Re-registration Page
 *
 * Halaman daftar ulang untuk orang tua yang anaknya sudah diterima,
 * yaitu: menampilkan info pembayaran, upload bukti bayar, dan tracking status
 */
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    PartyPopper,
    User,
    CreditCard,
    Building2,
    Copy,
    Check,
    Upload,
    FileImage,
    X,
    Clock,
    CheckCircle,
    AlertCircle
} from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import Badge from '@/components/ui/Badge.vue';
import { useHaptics } from '@/composables/useHaptics';
// TODO: Uncomment setelah backend routes dibuat dan Wayfinder generate routes
// import { uploadPayment } from '@/routes/parent/psb';

// Temporary route function sampai Wayfinder generate
const uploadPayment = () => ({ url: '/parent/psb/payment' });

/**
 * Interface untuk data registration
 */
interface Registration {
    id: number;
    registration_number: string;
    student_name: string;
    status: string;
    announced_at: string;
}

interface PaymentInfo {
    amount: number;
    formatted_amount: string;
    bank_name: string;
    account_number: string;
    account_name: string;
}

interface Payment {
    id: number;
    status: 'pending' | 'approved' | 'rejected';
    proof_url: string;
    uploaded_at: string;
    rejection_reason: string | null;
}

interface Props {
    registration: Registration;
    paymentInfo: PaymentInfo;
    payment: Payment | null;
    schoolName: string;
}

const props = defineProps<Props>();
const haptics = useHaptics();

// Form state untuk upload
const form = useForm({
    proof: null as File | null,
});

// Local state
const isDragging = ref(false);
const previewUrl = ref<string | null>(null);
const copiedField = ref<string | null>(null);

/**
 * Handle file drop
 */
const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = false;

    const files = event.dataTransfer?.files;
    if (files && files.length > 0) {
        handleFileSelect(files[0]);
    }
};

/**
 * Handle file select dari input
 */
const handleFileInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;
    if (files && files.length > 0) {
        handleFileSelect(files[0]);
    }
};

/**
 * Process selected file
 */
const handleFileSelect = (file: File) => {
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        haptics.error();
        alert('Format file tidak didukung. Gunakan JPG, PNG, atau WebP.');
        return;
    }

    // Validate file size (max 5MB)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        haptics.error();
        alert('Ukuran file terlalu besar. Maksimal 5MB.');
        return;
    }

    haptics.light();
    form.proof = file;

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        previewUrl.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
};

/**
 * Remove selected file
 */
const removeFile = () => {
    haptics.light();
    form.proof = null;
    previewUrl.value = null;
};

/**
 * Submit payment proof
 */
const submitPayment = () => {
    if (!form.proof) return;

    form.post(uploadPayment().url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            form.reset();
            previewUrl.value = null;
        },
        onError: () => {
            haptics.error();
        },
    });
};

/**
 * Copy text to clipboard
 */
const copyToClipboard = async (text: string, field: string) => {
    try {
        await navigator.clipboard.writeText(text);
        haptics.light();
        copiedField.value = field;
        setTimeout(() => {
            copiedField.value = null;
        }, 2000);
    } catch {
        haptics.error();
    }
};

/**
 * Format date
 */
const formatDate = (dateString: string): string => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

/**
 * Timeline items berdasarkan status
 */
const timelineItems = computed(() => {
    const status = props.payment?.status;

    return [
        {
            step: 'announced',
            label: 'Diterima',
            completed: true,
            current: false,
            date: formatDate(props.registration.announced_at),
            icon: CheckCircle,
        },
        {
            step: 'payment',
            label: 'Upload Bukti Bayar',
            completed: !!props.payment,
            current: !props.payment,
            date: props.payment ? formatDate(props.payment.uploaded_at) : null,
            icon: props.payment ? CheckCircle : Clock,
        },
        {
            step: 'verification',
            label: 'Verifikasi Pembayaran',
            completed: status === 'approved',
            current: status === 'pending',
            date: null,
            icon: status === 'approved' ? CheckCircle : status === 'rejected' ? AlertCircle : Clock,
        },
        {
            step: 'completed',
            label: 'Selesai',
            completed: status === 'approved',
            current: false,
            date: null,
            icon: CheckCircle,
        },
    ];
});

/**
 * Check apakah bisa upload
 */
const canUpload = computed(() => {
    return !props.payment || props.payment.status === 'rejected';
});

/**
 * Get status badge variant
 */
const getPaymentStatusVariant = computed((): 'success' | 'warning' | 'error' | 'default' => {
    if (!props.payment) return 'default';
    const variants: Record<string, 'success' | 'warning' | 'error' | 'default'> = {
        approved: 'success',
        pending: 'warning',
        rejected: 'error',
    };
    return variants[props.payment.status] || 'default';
});

const getPaymentStatusLabel = computed((): string => {
    if (!props.payment) return 'Belum Upload';
    const labels: Record<string, string> = {
        approved: 'Disetujui',
        pending: 'Menunggu Verifikasi',
        rejected: 'Ditolak',
    };
    return labels[props.payment.status] || props.payment.status;
});
</script>

<template>
    <AppLayout>
        <Head title="Daftar Ulang PSB" />

        <div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Congratulations Banner -->
            <Motion
                :initial="{ opacity: 0, y: -20, scale: 0.95 }"
                :animate="{ opacity: 1, y: 0, scale: 1 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="relative overflow-hidden bg-linear-to-br from-emerald-500 via-emerald-600 to-teal-600 rounded-3xl p-6 sm:p-8 shadow-xl shadow-emerald-500/25">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full translate-y-1/2 -translate-x-1/2"></div>
                    </div>

                    <div class="relative flex flex-col sm:flex-row items-center gap-4 sm:gap-6 text-white">
                        <Motion
                            :initial="{ scale: 0, rotate: -180 }"
                            :animate="{ scale: 1, rotate: 0 }"
                            :transition="{ type: 'spring', stiffness: 200, damping: 15, delay: 0.2 }"
                        >
                            <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                <PartyPopper :size="40" class="text-white" />
                            </div>
                        </Motion>

                        <div class="text-center sm:text-left">
                            <Motion
                                :initial="{ opacity: 0, y: 10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ delay: 0.3 }"
                            >
                                <h1 class="text-2xl sm:text-3xl font-bold">
                                    Selamat! 🎉
                                </h1>
                            </Motion>
                            <Motion
                                :initial="{ opacity: 0, y: 10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ delay: 0.4 }"
                            >
                                <p class="text-emerald-100 mt-2 text-lg">
                                    <span class="font-semibold text-white">{{ registration.student_name }}</span>
                                    diterima di {{ schoolName }}
                                </p>
                            </Motion>
                            <Motion
                                :initial="{ opacity: 0, y: 10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ delay: 0.5 }"
                            >
                                <p class="text-emerald-200 text-sm mt-1">
                                    No. Pendaftaran: <span class="font-mono">{{ registration.registration_number }}</span>
                                </p>
                            </Motion>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Main Content Grid -->
            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Left Column: Payment Info & Upload -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Payment Information -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, delay: 0.1 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                    <CreditCard :size="20" class="text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <h2 class="font-semibold text-slate-900 dark:text-slate-100">Informasi Pembayaran</h2>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Transfer ke rekening berikut</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- Amount -->
                                <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800">
                                    <p class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">Jumlah yang harus dibayar</p>
                                    <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-300 mt-1">
                                        {{ paymentInfo.formatted_amount }}
                                    </p>
                                </div>

                                <!-- Bank Info -->
                                <div class="space-y-3">
                                    <!-- Bank Name -->
                                    <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-zinc-800/50 rounded-xl">
                                        <div class="flex items-center gap-3">
                                            <Building2 :size="18" class="text-slate-400" />
                                            <div>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">Bank</p>
                                                <p class="font-medium text-slate-900 dark:text-slate-100">{{ paymentInfo.bank_name }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Account Number -->
                                    <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-zinc-800/50 rounded-xl">
                                        <div class="flex items-center gap-3">
                                            <CreditCard :size="18" class="text-slate-400" />
                                            <div>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">Nomor Rekening</p>
                                                <p class="font-mono font-medium text-slate-900 dark:text-slate-100">{{ paymentInfo.account_number }}</p>
                                            </div>
                                        </div>
                                        <button
                                            @click="copyToClipboard(paymentInfo.account_number, 'account')"
                                            class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors"
                                        >
                                            <Check v-if="copiedField === 'account'" :size="18" class="text-emerald-600" />
                                            <Copy v-else :size="18" />
                                        </button>
                                    </div>

                                    <!-- Account Name -->
                                    <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-zinc-800/50 rounded-xl">
                                        <div class="flex items-center gap-3">
                                            <User :size="18" class="text-slate-400" />
                                            <div>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">Atas Nama</p>
                                                <p class="font-medium text-slate-900 dark:text-slate-100">{{ paymentInfo.account_name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <!-- Upload Section -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, delay: 0.2 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                                        <Upload :size="20" class="text-violet-600 dark:text-violet-400" />
                                    </div>
                                    <div>
                                        <h2 class="font-semibold text-slate-900 dark:text-slate-100">Bukti Pembayaran</h2>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Upload bukti transfer</p>
                                    </div>
                                </div>
                                <Badge v-if="payment" :variant="getPaymentStatusVariant" size="sm" dot>
                                    {{ getPaymentStatusLabel }}
                                </Badge>
                            </div>

                            <!-- Rejection Alert -->
                            <div
                                v-if="payment?.status === 'rejected' && payment.rejection_reason"
                                class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl"
                            >
                                <div class="flex items-start gap-3">
                                    <AlertCircle :size="20" class="text-red-500 shrink-0 mt-0.5" />
                                    <div>
                                        <p class="font-medium text-red-800 dark:text-red-300">Pembayaran Ditolak</p>
                                        <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ payment.rejection_reason }}</p>
                                        <p class="text-sm text-red-600 dark:text-red-400 mt-2">Silakan upload ulang bukti pembayaran yang valid.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Existing Payment Proof -->
                            <div v-if="payment && payment.status !== 'rejected'" class="space-y-4">
                                <div class="relative rounded-xl overflow-hidden border border-slate-200 dark:border-zinc-700">
                                    <img
                                        :src="payment.proof_url"
                                        alt="Bukti Pembayaran"
                                        class="w-full h-auto max-h-64 object-contain bg-slate-100 dark:bg-zinc-800"
                                    />
                                </div>
                                <p class="text-sm text-slate-500 dark:text-slate-400 text-center">
                                    Diupload pada {{ formatDate(payment.uploaded_at) }}
                                </p>
                            </div>

                            <!-- Upload Area -->
                            <div v-else-if="canUpload">
                                <!-- Preview -->
                                <div v-if="previewUrl" class="relative mb-4">
                                    <div class="relative rounded-xl overflow-hidden border border-slate-200 dark:border-zinc-700">
                                        <img
                                            :src="previewUrl"
                                            alt="Preview"
                                            class="w-full h-auto max-h-64 object-contain bg-slate-100 dark:bg-zinc-800"
                                        />
                                        <button
                                            @click="removeFile"
                                            class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
                                        >
                                            <X :size="16" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Dropzone -->
                                <div
                                    v-else
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop="handleDrop"
                                    class="relative border-2 border-dashed rounded-xl p-8 text-center transition-colors cursor-pointer"
                                    :class="isDragging
                                        ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/20'
                                        : 'border-slate-300 dark:border-zinc-700 hover:border-emerald-400 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/10'"
                                >
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/jpg,image/png,image/webp"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        @change="handleFileInput"
                                    />
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                                            <FileImage :size="24" class="text-slate-400" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-700 dark:text-slate-300">
                                                Drag & drop atau klik untuk upload
                                            </p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                                JPG, PNG, WebP (Maks. 5MB)
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        @click="submitPayment"
                                        :disabled="!form.proof || form.processing"
                                        class="w-full mt-4 px-6 py-3 bg-emerald-500 hover:bg-emerald-600 disabled:bg-slate-300 disabled:dark:bg-zinc-700 text-white disabled:text-slate-500 disabled:dark:text-zinc-500 rounded-xl font-semibold transition-colors shadow-lg shadow-emerald-500/25 disabled:shadow-none disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                    >
                                        <svg
                                            v-if="form.processing"
                                            class="w-5 h-5 animate-spin"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                        </svg>
                                        <Upload v-else :size="20" />
                                        {{ form.processing ? 'Mengupload...' : 'Upload Bukti Bayar' }}
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </Motion>
                </div>

                <!-- Right Column: Timeline -->
                <div class="lg:col-span-1">
                    <Motion
                        :initial="{ opacity: 0, x: 20 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ duration: 0.3, delay: 0.3 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-zinc-800 sticky top-24">
                            <h2 class="font-semibold text-slate-900 dark:text-slate-100 mb-6">
                                Progress Daftar Ulang
                            </h2>

                            <ol class="relative border-l-2 border-slate-200 dark:border-zinc-700 ml-3">
                                <li
                                    v-for="(item, index) in timelineItems"
                                    :key="item.step"
                                    class="mb-8 ml-6 last:mb-0"
                                >
                                    <!-- Timeline dot -->
                                    <span
                                        class="absolute -left-[13px] flex h-6 w-6 items-center justify-center rounded-full ring-4 ring-white dark:ring-zinc-900 transition-all duration-300"
                                        :class="{
                                            'bg-emerald-500 text-white': item.completed,
                                            'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400': item.current && payment?.status !== 'rejected',
                                            'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400': item.current && payment?.status === 'rejected',
                                            'bg-slate-100 text-slate-400 dark:bg-zinc-800 dark:text-zinc-500': !item.completed && !item.current,
                                        }"
                                    >
                                        <component :is="item.icon" :size="14" />
                                    </span>

                                    <!-- Content -->
                                    <div>
                                        <h3
                                            class="font-medium"
                                            :class="{
                                                'text-emerald-700 dark:text-emerald-400': item.completed,
                                                'text-amber-700 dark:text-amber-400': item.current && payment?.status !== 'rejected',
                                                'text-red-700 dark:text-red-400': item.current && payment?.status === 'rejected',
                                                'text-slate-500 dark:text-slate-500': !item.completed && !item.current,
                                            }"
                                        >
                                            {{ item.label }}
                                        </h3>
                                        <p
                                            v-if="item.date"
                                            class="text-xs mt-0.5"
                                            :class="item.completed ? 'text-slate-500 dark:text-slate-400' : 'text-slate-400 dark:text-slate-500'"
                                        >
                                            {{ item.date }}
                                        </p>
                                        <p
                                            v-else-if="item.current"
                                            class="text-xs mt-0.5"
                                            :class="payment?.status === 'rejected' ? 'text-red-500' : 'text-amber-500'"
                                        >
                                            {{ payment?.status === 'rejected' ? 'Upload ulang diperlukan' : 'Sedang diproses' }}
                                        </p>
                                    </div>
                                </li>
                            </ol>
                        </div>
                    </Motion>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
