<script setup lang="ts">
/**
 * Unified Payment Show Page - Detail pembayaran untuk Payment (legacy) dan PaymentTransaction
 * Menangani kedua tipe record dengan satu view untuk maintainability yang lebih baik
 */
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import BaseModal from '@/components/ui/BaseModal.vue';
import {
    ChevronLeft, Receipt, Printer, XCircle, CheckCircle2, Clock,
    User, Banknote, Calendar, FileText, CreditCard, Shield, Download,
    Layers, Image as ImageIcon
} from 'lucide-vue-next';
import { index } from '@/routes/admin/payments/records';
import { Motion } from 'motion-v';

// ============================================================================
// Type Definitions - Unified structure untuk kedua tipe record
// ============================================================================

interface Student {
    id: number;
    nama_lengkap: string;
    nis: string;
    kelas: string;
}

interface Bill {
    id: number;
    nomor_tagihan: string;
    category: string;
    periode: string;
    nominal?: number;
    formatted_nominal?: string;
}

interface PaymentItem {
    id: number;
    bill_id: number;
    student_id: number;
    amount: number;
    formatted_amount: string;
    bill: Bill;
    student: Student;
}

interface PersonInfo {
    id: number;
    name?: string;
    nama_lengkap?: string;
}

/**
 * Unified Record interface - dapat berupa Payment atau PaymentTransaction
 */
interface UnifiedRecord {
    id: number;
    type: 'payment' | 'transaction';
    reference_number: string; // nomor_kwitansi atau transaction_number
    total_amount: number;
    formatted_amount: string;
    payment_method: string;
    method_label: string;
    payment_date: string;
    formatted_date: string;
    payment_time: string | null;
    status: 'pending' | 'verified' | 'cancelled';
    status_label: string;
    notes: string | null;
    proof_file: string | null;

    // Relations
    creator: PersonInfo | null;
    verifier: PersonInfo | null;
    verified_at: string | null;
    canceller: PersonInfo | null;
    cancelled_at: string | null;
    cancellation_reason: string | null;

    // Single payment fields (legacy)
    student?: Student;
    bill?: Bill;

    // Transaction fields (combined payment)
    bill_count?: number;
    items?: PaymentItem[];
    guardian?: PersonInfo | null;
}

interface ReceiptItem {
    student: { nama: string; nis: string; kelas: string };
    bill: { kategori: string; periode: string; nomor?: string };
    nominal: string;
}

interface ReceiptData {
    reference_number: string;
    tanggal: string;
    waktu: string | null;
    items: ReceiptItem[];
    total: string;
    metode: string;
    petugas: string;
    school: { name: string; address: string };
}

interface Props {
    record: UnifiedRecord;
    receiptData: ReceiptData;
    canVerify: boolean;
    canCancel: boolean;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

// ============================================================================
// Computed Properties
// ============================================================================

const isTransaction = computed(() => props.record.type === 'transaction');
const isMultiBill = computed(() => isTransaction.value && (props.record.bill_count ?? 0) > 1);

// Group items by student untuk transaction
const itemsByStudent = computed(() => {
    if (!isTransaction.value || !props.record.items) return {};

    return props.record.items.reduce((acc, item) => {
        const studentId = item.student_id;
        if (!acc[studentId]) {
            acc[studentId] = {
                student: item.student,
                items: [],
                total: 0,
            };
        }
        acc[studentId].items.push(item);
        acc[studentId].total += item.amount;
        return acc;
    }, {} as Record<number, { student: Student; items: PaymentItem[]; total: number }>);
});

const statusConfig = computed(() => {
    const configs: Record<string, { icon: typeof CheckCircle2; bgClass: string; textClass: string }> = {
        pending: {
            icon: Clock,
            bgClass: 'bg-amber-50 dark:bg-amber-900/20',
            textClass: 'text-amber-700 dark:text-amber-400',
        },
        verified: {
            icon: CheckCircle2,
            bgClass: 'bg-emerald-50 dark:bg-emerald-900/20',
            textClass: 'text-emerald-700 dark:text-emerald-400',
        },
        cancelled: {
            icon: XCircle,
            bgClass: 'bg-slate-50 dark:bg-zinc-800/50',
            textClass: 'text-slate-500 dark:text-slate-400',
        },
    };
    return configs[props.record.status] || configs.pending;
});

// ============================================================================
// State
// ============================================================================

const showCancelModal = ref(false);
const showProofModal = ref(false);
const cancelForm = useForm({
    reason: '',
});

// ============================================================================
// Methods
// ============================================================================

const getReceiptUrl = () => {
    if (isTransaction.value) {
        return `/admin/payments/transactions/${props.record.id}/receipt`;
    }
    return `/admin/payments/records/${props.record.id}/receipt`;
};

const getStreamUrl = () => {
    if (isTransaction.value) {
        return `/admin/payments/transactions/${props.record.id}/receipt/stream`;
    }
    return `/admin/payments/records/${props.record.id}/receipt/stream`;
};

const getVerifyUrl = () => {
    if (isTransaction.value) {
        return `/admin/payments/transactions/${props.record.id}/verify`;
    }
    return `/admin/payments/records/${props.record.id}/verify`;
};

const getCancelUrl = () => {
    if (isTransaction.value) {
        return `/admin/payments/transactions/${props.record.id}/reject`;
    }
    return `/admin/payments/records/${props.record.id}/cancel`;
};

const downloadReceipt = () => {
    haptics.medium();
    window.open(getReceiptUrl(), '_blank');
};

const printReceipt = () => {
    haptics.medium();
    const printUrl = getStreamUrl();

    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = 'none';
    iframe.src = printUrl;

    document.body.appendChild(iframe);

    iframe.onload = () => {
        setTimeout(() => {
            iframe.contentWindow?.focus();
            iframe.contentWindow?.print();
            setTimeout(() => {
                document.body.removeChild(iframe);
            }, 1000);
        }, 500);
    };
};

const handleVerify = async () => {
    const label = isTransaction.value ? 'Transaksi' : 'Pembayaran';
    const confirmed = await modal.confirm(
        `Verifikasi ${label}`,
        `Verifikasi ${props.record.reference_number}?`,
        'Ya, Verifikasi',
        'Batal'
    );

    if (confirmed) {
        haptics.heavy();
        router.post(getVerifyUrl(), {}, {
            preserveScroll: true,
            onSuccess: () => haptics.success(),
            onError: () => haptics.error(),
        });
    }
};

const openCancelModal = () => {
    haptics.light();
    cancelForm.reason = '';
    showCancelModal.value = true;
};

const handleCancel = () => {
    if (!cancelForm.reason) {
        modal.error('Alasan harus diisi');
        return;
    }

    haptics.heavy();
    cancelForm.post(getCancelUrl(), {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            showCancelModal.value = false;
        },
        onError: () => haptics.error(),
    });
};

const openProofModal = () => {
    haptics.light();
    showProofModal.value = true;
};

const getPersonName = (person: PersonInfo | null) => {
    if (!person) return '-';
    return person.name || person.nama_lengkap || '-';
};
</script>

<template>
    <AppLayout title="Detail Pembayaran">
        <Head :title="`${isTransaction ? 'Transaksi' : 'Pembayaran'} ${record.reference_number}`" />

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
                                class="w-11 h-11 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 active:bg-slate-200 rounded-xl transition-colors shrink-0"
                            >
                                <ChevronLeft class="w-5 h-5" />
                            </Link>
                        </Motion>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ record.reference_number }}
                                </h1>
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium',
                                        statusConfig.bgClass,
                                        statusConfig.textClass
                                    ]"
                                >
                                    <component :is="statusConfig.icon" class="w-4 h-4" />
                                    {{ record.status_label }}
                                </span>
                                <span
                                    v-if="isMultiBill"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400"
                                >
                                    <Layers class="w-3 h-3" />
                                    {{ record.bill_count }} tagihan
                                </span>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                {{ record.formatted_date }} {{ record.payment_time }}
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Main Content Grid -->
            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Left Column - Payment Details -->
                <div class="space-y-6">
                    <!-- SINGLE PAYMENT: Student & Bill Info -->
                    <template v-if="!isTransaction && record.student && record.bill">
                        <!-- Student Info -->
                        <Motion
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
                        >
                            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 p-6">
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                                    <User class="w-5 h-5 text-slate-400" />
                                    Informasi Siswa
                                </h2>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Nama Lengkap</p>
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ record.student.nama_lengkap }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">NIS</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ record.student.nis }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">Kelas</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ record.student.kelas }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Motion>

                        <!-- Bill Info -->
                        <Motion
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                        >
                            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 p-6">
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                                    <Receipt class="w-5 h-5 text-slate-400" />
                                    Informasi Tagihan
                                </h2>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">No. Tagihan</p>
                                        <p class="font-mono font-medium text-slate-900 dark:text-slate-100">{{ record.bill.nomor_tagihan }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">Kategori</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ record.bill.category }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">Periode</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ record.bill.periode }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Motion>
                    </template>

                    <!-- TRANSACTION: Bills grouped by student -->
                    <template v-else-if="isTransaction">
                        <Motion
                            v-for="(group, studentId, idx) in itemsByStudent"
                            :key="studentId"
                            :initial="{ opacity: 0, y: 10 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 + (Number(idx) * 0.05) }"
                        >
                            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 p-6">
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                                    <User class="w-5 h-5 text-slate-400" />
                                    {{ group.student.nama_lengkap }}
                                </h2>
                                <div class="space-y-3 mb-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">NIS</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ group.student.nis }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">Kelas</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ group.student.kelas }}</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- Bills for this student -->
                                <div class="border-t border-slate-200 dark:border-zinc-700 pt-4">
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-2">
                                        <Receipt class="w-4 h-4" />
                                        Tagihan Dibayar
                                    </p>
                                    <div class="space-y-2">
                                        <div
                                            v-for="item in group.items"
                                            :key="item.id"
                                            class="flex items-center justify-between py-2 px-3 bg-slate-50 dark:bg-zinc-800/50 rounded-lg"
                                        >
                                            <div>
                                                <p class="font-medium text-slate-900 dark:text-slate-100">{{ item.bill.category }}</p>
                                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ item.bill.periode }}</p>
                                            </div>
                                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ item.formatted_amount }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-200 dark:border-zinc-700">
                                        <span class="text-sm text-slate-500 dark:text-slate-400">Subtotal</span>
                                        <span class="font-bold text-slate-900 dark:text-slate-100">Rp {{ group.total.toLocaleString('id-ID') }}</span>
                                    </div>
                                </div>
                            </div>
                        </Motion>
                    </template>

                    <!-- Payment Details (shared) -->
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 p-6">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                                <Banknote class="w-5 h-5 text-slate-400" />
                                Detail Pembayaran
                            </h2>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between py-3 border-b border-slate-200 dark:border-zinc-700">
                                    <span class="text-slate-500 dark:text-slate-400">{{ isTransaction ? 'Total Pembayaran' : 'Nominal' }}</span>
                                    <span class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ record.formatted_amount }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Metode</p>
                                        <p class="font-medium text-slate-900 dark:text-slate-100 flex items-center gap-1">
                                            <CreditCard class="w-4 h-4" />
                                            {{ record.method_label }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Tanggal Bayar</p>
                                        <p class="font-medium text-slate-900 dark:text-slate-100 flex items-center gap-1">
                                            <Calendar class="w-4 h-4" />
                                            {{ record.formatted_date }}
                                        </p>
                                    </div>
                                </div>
                                <div v-if="record.notes">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ isTransaction ? 'Catatan' : 'Keterangan' }}</p>
                                    <p class="text-slate-900 dark:text-slate-100">{{ record.notes }}</p>
                                </div>
                                <div v-if="isTransaction && record.guardian" class="pt-3 border-t border-slate-200 dark:border-zinc-700">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Wali Murid</p>
                                    <p class="font-medium text-slate-900 dark:text-slate-100">{{ getPersonName(record.guardian) }}</p>
                                </div>
                                <div v-if="record.creator" class="pt-3 border-t border-slate-200 dark:border-zinc-700">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ isTransaction ? 'Disubmit oleh' : 'Dicatat oleh' }}</p>
                                    <p class="font-medium text-slate-900 dark:text-slate-100">{{ getPersonName(record.creator) }}</p>
                                </div>
                                <div v-if="record.verifier" class="pt-3 border-t border-slate-200 dark:border-zinc-700">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Diverifikasi oleh</p>
                                    <p class="font-medium text-slate-900 dark:text-slate-100">
                                        {{ getPersonName(record.verifier) }}
                                        <span class="text-sm text-slate-500 dark:text-slate-400"> • {{ record.verified_at }}</span>
                                    </p>
                                </div>
                                <!-- Cancellation Details -->
                                <div v-if="record.canceller" class="pt-3 border-t border-red-200 dark:border-red-800/50">
                                    <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4 -mx-2">
                                        <p class="text-sm font-medium text-red-600 dark:text-red-400 mb-2 flex items-center gap-1.5">
                                            <XCircle class="w-4 h-4" />
                                            {{ isTransaction ? 'Transaksi Ditolak' : 'Pembayaran Dibatalkan' }}
                                        </p>
                                        <div class="space-y-2">
                                            <div>
                                                <p class="text-xs text-red-500/70 dark:text-red-400/70">{{ isTransaction ? 'Ditolak oleh' : 'Dibatalkan oleh' }}</p>
                                                <p class="text-sm font-medium text-red-700 dark:text-red-300">
                                                    {{ getPersonName(record.canceller) }}
                                                    <span class="text-red-500/70 dark:text-red-400/70"> • {{ record.cancelled_at }}</span>
                                                </p>
                                            </div>
                                            <div v-if="record.cancellation_reason">
                                                <p class="text-xs text-red-500/70 dark:text-red-400/70">Alasan</p>
                                                <p class="text-sm text-red-700 dark:text-red-300">{{ record.cancellation_reason }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <!-- Proof Image (if exists) -->
                    <Motion
                        v-if="record.proof_file"
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 p-6">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                                <ImageIcon class="w-5 h-5 text-slate-400" />
                                Bukti Pembayaran
                            </h2>
                            <button
                                @click="openProofModal"
                                class="w-full relative rounded-xl overflow-hidden group cursor-pointer"
                            >
                                <img
                                    :src="`/storage/${record.proof_file}`"
                                    :alt="'Bukti pembayaran ' + record.reference_number"
                                    class="w-full max-h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                                    <span class="opacity-0 group-hover:opacity-100 transition-opacity text-white font-medium bg-black/50 px-4 py-2 rounded-lg">
                                        Klik untuk memperbesar
                                    </span>
                                </div>
                            </button>
                        </div>
                    </Motion>
                </div>

                <!-- Right Column - Receipt Preview & Actions -->
                <div class="space-y-6">
                    <!-- Receipt Preview -->
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50">
                                <h2 class="font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                    <FileText class="w-5 h-5 text-slate-400" />
                                    Preview Kwitansi
                                </h2>
                            </div>
                            <div class="p-6">
                                <!-- Simulated Receipt -->
                                <div class="bg-slate-50 dark:bg-zinc-800 rounded-xl p-6 border border-slate-200 dark:border-zinc-700">
                                    <!-- School Header -->
                                    <div class="text-center mb-4 pb-4 border-b border-dashed border-slate-300 dark:border-zinc-600">
                                        <p class="font-bold text-slate-900 dark:text-slate-100">{{ receiptData.school.name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ receiptData.school.address }}</p>
                                    </div>

                                    <p class="text-center font-bold text-slate-900 dark:text-slate-100 mb-4">KWITANSI PEMBAYARAN</p>

                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-slate-500 dark:text-slate-400">{{ isTransaction ? 'No. Transaksi' : 'No. Kwitansi' }}</span>
                                            <span class="font-mono font-medium text-slate-900 dark:text-slate-100">{{ receiptData.reference_number }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-500 dark:text-slate-400">Tanggal</span>
                                            <span class="text-slate-900 dark:text-slate-100">{{ receiptData.tanggal }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-500 dark:text-slate-400">Metode</span>
                                            <span class="text-slate-900 dark:text-slate-100">{{ receiptData.metode }}</span>
                                        </div>
                                    </div>

                                    <!-- Items -->
                                    <div class="my-4 py-4 border-t border-b border-dashed border-slate-300 dark:border-zinc-600 space-y-3">
                                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Rincian Pembayaran</p>
                                        <div
                                            v-for="(item, idx) in receiptData.items"
                                            :key="idx"
                                            class="text-sm"
                                        >
                                            <div class="flex justify-between">
                                                <span class="text-slate-900 dark:text-slate-100">{{ item.bill.kategori }}</span>
                                                <span class="font-medium text-slate-900 dark:text-slate-100">{{ item.nominal }}</span>
                                            </div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ item.student.nama }} • {{ item.bill.periode }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex justify-between items-center">
                                        <span class="font-medium text-slate-700 dark:text-slate-300">TOTAL BAYAR</span>
                                        <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ receiptData.total }}</span>
                                    </div>

                                    <div class="mt-4 pt-4 border-t border-dashed border-slate-300 dark:border-zinc-600 text-xs text-slate-500 dark:text-slate-400 text-center">
                                        <p>Petugas: {{ receiptData.petugas }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <!-- Actions -->
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 p-6 space-y-3">
                            <!-- Print/Download Actions -->
                            <div v-if="record.status === 'verified'" class="flex gap-3">
                                <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                                    <button
                                        @click="downloadReceipt"
                                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors font-medium shadow-lg shadow-emerald-500/25"
                                    >
                                        <Download class="w-5 h-5" />
                                        Download PDF
                                    </button>
                                </Motion>
                                <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                                    <button
                                        @click="printReceipt"
                                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors font-medium"
                                    >
                                        <Printer class="w-5 h-5" />
                                        Cetak
                                    </button>
                                </Motion>
                            </div>

                            <!-- Verify Action -->
                            <Motion v-if="canVerify" :whileTap="{ scale: 0.97 }">
                                <button
                                    @click="handleVerify"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors font-medium shadow-lg shadow-emerald-500/25"
                                >
                                    <Shield class="w-5 h-5" />
                                    {{ isTransaction ? 'Verifikasi Transaksi' : 'Verifikasi Pembayaran' }}
                                </button>
                            </Motion>

                            <!-- Cancel Action -->
                            <Motion v-if="canCancel" :whileTap="{ scale: 0.97 }">
                                <button
                                    @click="openCancelModal"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors font-medium"
                                >
                                    <XCircle class="w-5 h-5" />
                                    {{ isTransaction ? 'Tolak Transaksi' : 'Batalkan Pembayaran' }}
                                </button>
                            </Motion>
                        </div>
                    </Motion>
                </div>
            </div>
        </div>

        <!-- Cancel/Reject Modal -->
        <BaseModal
            :show="showCancelModal"
            :title="isTransaction ? 'Tolak Transaksi' : 'Batalkan Pembayaran'"
            @close="showCancelModal = false"
        >
            <div class="p-6">
                <p class="text-slate-600 dark:text-slate-400 mb-4">
                    Anda akan {{ isTransaction ? 'menolak transaksi' : 'membatalkan pembayaran' }} <strong>{{ record.reference_number }}</strong>.
                    <span v-if="!isTransaction"> Tindakan ini akan mengembalikan status tagihan.</span>
                    <span v-else-if="record.bill_count"> Transaksi ini memiliki {{ record.bill_count }} tagihan.</span>
                </p>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Alasan {{ isTransaction ? 'Penolakan' : 'Pembatalan' }} <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="cancelForm.reason"
                        rows="3"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all resize-none"
                        :placeholder="`Jelaskan alasan ${isTransaction ? 'penolakan' : 'pembatalan'}...`"
                    ></textarea>
                    <p v-if="cancelForm.errors.reason" class="mt-1.5 text-sm text-red-500">{{ cancelForm.errors.reason }}</p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-zinc-800 flex gap-3">
                <button
                    @click="showCancelModal = false"
                    class="flex-1 px-4 py-2.5 text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-zinc-800 rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors font-medium"
                >
                    Batal
                </button>
                <button
                    @click="handleCancel"
                    :disabled="cancelForm.processing || !cancelForm.reason"
                    class="flex-1 px-4 py-2.5 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ isTransaction ? 'Tolak Transaksi' : 'Batalkan Pembayaran' }}
                </button>
            </div>
        </BaseModal>

        <!-- Proof Image Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showProofModal && record.proof_file"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    @click.self="showProofModal = false"
                >
                    <div class="absolute inset-0 bg-black/80" @click="showProofModal = false"></div>
                    <div class="relative max-w-4xl max-h-[90vh] overflow-auto">
                        <button
                            @click="showProofModal = false"
                            class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/70 transition-colors"
                        >
                            <XCircle class="w-6 h-6" />
                        </button>
                        <img
                            :src="`/storage/${record.proof_file}`"
                            :alt="'Bukti pembayaran ' + record.reference_number"
                            class="max-w-full max-h-[85vh] object-contain rounded-lg"
                        />
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
