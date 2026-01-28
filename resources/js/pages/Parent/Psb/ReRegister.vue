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
    status_label: string;
    announced_at: string;
    academic_year: string;
    can_re_register: boolean;
    can_upload_payment: boolean;
}

interface PaymentInfo {
    registration_fee: number;
    formatted_fee: string;
    re_registration_deadline_days: number;
}

interface Payment {
    id: number;
    payment_type: string;
    payment_type_label: string;
    amount: number;
    formatted_amount: string;
    payment_method: string;
    payment_method_label: string;
    status: string;
    status_label: string;
    proof_url: string | null;
    notes: string | null;
    created_at: string;
    verified_at: string | null;
}

interface TimelineItem {
    step: string;
    label: string;
    description: string;
    completed: boolean;
    current: boolean;
    date: string | null;
}

interface Props {
    registration: Registration;
    payments: Payment[];
    timeline: TimelineItem[];
    paymentInfo: PaymentInfo | null;
    paymentTypes: Record<string, string>;
    paymentMethods: Record<string, string>;
    schoolName?: string;
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
 * Get latest payment untuk display
 */
const latestPayment = computed(() => {
    if (props.payments.length === 0) return null;
    return props.payments[0]; // Sudah di-order desc by created_at dari backend
});

/**
 * Check apakah ada payment rejected yang perlu re-upload
 */
const hasRejectedPayment = computed(() => {
    return latestPayment.value?.status === 'rejected';
});

/**
 * Timeline items dari backend dengan icon mapping
 */
const timelineItems = computed(() => {
    return props.timeline.map(item => ({
        ...item,
        icon: item.completed ? CheckCircle : item.current && hasRejectedPayment.value ? AlertCircle : item.current ? Clock : Clock,
    }));
});

/**
 * Check apakah bisa upload pembayaran baru
 */
const canUpload = computed(() => {
    // Bisa upload jika belum ada payment atau payment terakhir ditolak
    return props.registration.can_upload_payment && (props.payments.length === 0 || hasRejectedPayment.value);
});

/**
 * Get status badge variant untuk payment terakhir
 */
const getPaymentStatusVariant = computed((): 'success' | 'warning' | 'error' | 'default' => {
    if (!latestPayment.value) return 'default';
    const variants: Record<string, 'success' | 'warning' | 'error' | 'default'> = {
        verified: 'success',
        pending: 'warning',
        rejected: 'error',
    };
    return variants[latestPayment.value.status] || 'default';
});

/**
 * Get status label untuk payment terakhir
 */
const getPaymentStatusLabel = computed((): string => {
    if (!latestPayment.value) return 'Belum Upload';
    return latestPayment.value.status_label;
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
                                    diterima - Tahun Ajaran {{ registration.academic_year }}
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

                            <div v-if="paymentInfo" class="space-y-4">
                                <!-- Amount -->
                                <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800">
                                    <p class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">Biaya Daftar Ulang</p>
                                    <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-300 mt-1">
                                        {{ paymentInfo.formatted_fee }}
                                    </p>
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2">
                                        Batas waktu: {{ paymentInfo.re_registration_deadline_days }} hari setelah pengumuman
                                    </p>
                                </div>

                                <!-- Payment Methods -->
                                <div class="p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-xl">
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">Metode Pembayaran</p>
                                    <div class="space-y-2">
                                        <div v-for="(label, key) in paymentMethods" :key="key" class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                            <CheckCircle :size="14" class="text-emerald-500" />
                                            {{ label }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Note -->
                                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-100 dark:border-amber-800">
                                    <p class="text-sm text-amber-700 dark:text-amber-400">
                                        <strong>Catatan:</strong> Hubungi admin sekolah untuk mendapatkan informasi rekening pembayaran.
                                    </p>
                                </div>
                            </div>
                            <div v-else class="p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-xl text-center">
                                <p class="text-slate-500 dark:text-slate-400">
                                    Informasi pembayaran belum tersedia. Hubungi admin sekolah.
                                </p>
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
                                <Badge v-if="latestPayment" :variant="getPaymentStatusVariant" size="sm" dot>
                                    {{ getPaymentStatusLabel }}
                                </Badge>
                            </div>

                            <!-- Rejection Alert -->
                            <div
                                v-if="latestPayment?.status === 'rejected' && latestPayment.notes"
                                class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl"
                            >
                                <div class="flex items-start gap-3">
                                    <AlertCircle :size="20" class="text-red-500 shrink-0 mt-0.5" />
                                    <div>
                                        <p class="font-medium text-red-800 dark:text-red-300">Pembayaran Ditolak</p>
                                        <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ latestPayment.notes }}</p>
                                        <p class="text-sm text-red-600 dark:text-red-400 mt-2">Silakan upload ulang bukti pembayaran yang valid.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Existing Payment Proof -->
                            <div v-if="latestPayment && latestPayment.status !== 'rejected'" class="space-y-4">
                                <div class="relative rounded-xl overflow-hidden border border-slate-200 dark:border-zinc-700">
                                    <img
                                        v-if="latestPayment.proof_url"
                                        :src="latestPayment.proof_url"
                                        alt="Bukti Pembayaran"
                                        class="w-full h-auto max-h-64 object-contain bg-slate-100 dark:bg-zinc-800"
                                    />
                                    <div v-else class="p-8 text-center text-slate-500 dark:text-slate-400">
                                        <FileImage :size="48" class="mx-auto mb-2 text-slate-300 dark:text-zinc-600" />
                                        <p>Bukti pembayaran tidak tersedia</p>
                                    </div>
                                </div>
                                <div class="text-sm text-slate-500 dark:text-slate-400 text-center space-y-1">
                                    <p>{{ latestPayment.payment_type_label }} - {{ latestPayment.formatted_amount }}</p>
                                    <p>Diupload pada {{ latestPayment.created_at }}</p>
                                </div>
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
                                            'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400': item.current && !hasRejectedPayment,
                                            'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400': item.current && hasRejectedPayment,
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
                                                'text-amber-700 dark:text-amber-400': item.current && !hasRejectedPayment,
                                                'text-red-700 dark:text-red-400': item.current && hasRejectedPayment,
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
                                            :class="hasRejectedPayment ? 'text-red-500' : 'text-amber-500'"
                                        >
                                            {{ hasRejectedPayment ? 'Upload ulang diperlukan' : item.description || 'Sedang diproses' }}
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
