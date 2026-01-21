<script setup lang="ts">
/**
 * Payment Show Page - Detail pembayaran dengan preview kwitansi
 * dan opsi untuk cetak atau batalkan pembayaran
 */
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import BaseModal from '@/components/ui/BaseModal.vue';
import {
    ChevronLeft, Receipt, Printer, XCircle, CheckCircle2, Clock,
    User, Banknote, Calendar, FileText, CreditCard, Shield, Download
} from 'lucide-vue-next';
import { index, receipt, verify, cancel } from '@/routes/admin/payments/records';
import { Motion } from 'motion-v';

interface Payment {
    id: number;
    nomor_kwitansi: string;
    bill: {
        id: number;
        nomor_tagihan: string;
        category: string;
        periode: string;
    };
    student: {
        id: number;
        nama_lengkap: string;
        nis: string;
        kelas: string;
    };
    nominal: number;
    formatted_nominal: string;
    metode_pembayaran: string;
    metode_label: string;
    tanggal_bayar: string;
    formatted_tanggal: string;
    waktu_bayar: string;
    status: 'pending' | 'verified' | 'cancelled';
    status_label: string;
    keterangan: string | null;
    creator: { id: number; name: string } | null;
    verifier: { id: number; name: string } | null;
    verified_at: string | null;
    // Cancellation fields
    canceller: { id: number; name: string } | null;
    cancelled_at: string | null;
    cancellation_reason: string | null;
    created_at: string;
}

interface ReceiptData {
    nomor_kwitansi: string;
    tanggal: string;
    waktu: string;
    student: {
        nama: string;
        nis: string;
        kelas: string;
    };
    bill: {
        nomor: string;
        kategori: string;
        periode: string;
        nominal_tagihan: string;
        sisa_sebelum: string;
        sisa_sesudah: string;
    };
    pembayaran: {
        nominal: string;
        metode: string;
    };
    petugas: string;
    school: {
        name: string;
        address: string;
    };
}

interface Props {
    payment: Payment;
    receiptData: ReceiptData;
    canVerify: boolean;
    canCancel: boolean;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

// State
const showCancelModal = ref(false);
const cancelForm = useForm({
    reason: '',
});

// Methods
const downloadReceipt = () => {
    haptics.medium();
    window.open(receipt(props.payment.id).url, '_blank');
};

const printReceipt = () => {
    haptics.medium();
    // Menggunakan iframe tersembunyi untuk print PDF
    // Ini memastikan PDF ter-load sepenuhnya sebelum print dialog muncul
    const printUrl = `/admin/payments/records/${props.payment.id}/receipt/stream`;

    // Buat iframe tersembunyi
    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = 'none';
    iframe.src = printUrl;

    // Tambahkan ke DOM
    document.body.appendChild(iframe);

    // Tunggu iframe load lalu print
    iframe.onload = () => {
        setTimeout(() => {
            iframe.contentWindow?.focus();
            iframe.contentWindow?.print();
            // Hapus iframe setelah print dialog ditutup
            setTimeout(() => {
                document.body.removeChild(iframe);
            }, 1000);
        }, 500); // Delay untuk memastikan PDF ter-render
    };
};

const handleVerify = async () => {
    const confirmed = await modal.confirm(
        'Verifikasi Pembayaran',
        `Verifikasi pembayaran ${props.payment.nomor_kwitansi}?`,
        'Ya, Verifikasi',
        'Batal'
    );

    if (confirmed) {
        haptics.heavy();
        router.post(verify(props.payment.id).url, {}, {
            preserveScroll: true,
            onSuccess: () => {
                haptics.success();
            },
            onError: () => {
                haptics.error();
            },
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
        modal.error('Alasan pembatalan harus diisi');
        return;
    }

    haptics.heavy();
    cancelForm.post(cancel(props.payment.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            showCancelModal.value = false;
        },
        onError: () => {
            haptics.error();
        },
    });
};

const getStatusConfig = (status: string) => {
    const configs: Record<string, { icon: typeof CheckCircle2; bgClass: string; textClass: string; borderClass: string }> = {
        pending: {
            icon: Clock,
            bgClass: 'bg-amber-50 dark:bg-amber-900/20',
            textClass: 'text-amber-700 dark:text-amber-400',
            borderClass: 'border-amber-200 dark:border-amber-800',
        },
        verified: {
            icon: CheckCircle2,
            bgClass: 'bg-emerald-50 dark:bg-emerald-900/20',
            textClass: 'text-emerald-700 dark:text-emerald-400',
            borderClass: 'border-emerald-200 dark:border-emerald-800',
        },
        cancelled: {
            icon: XCircle,
            bgClass: 'bg-slate-50 dark:bg-zinc-800/50',
            textClass: 'text-slate-500 dark:text-slate-400',
            borderClass: 'border-slate-200 dark:border-zinc-700',
        },
    };
    return configs[status] || configs.pending;
};

const statusConfig = getStatusConfig(props.payment.status);
</script>

<template>
    <AppLayout title="Detail Pembayaran">
        <Head :title="`Pembayaran ${payment.nomor_kwitansi}`" />

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
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                            >
                                <ChevronLeft class="w-5 h-5" />
                            </Link>
                        </Motion>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ payment.nomor_kwitansi }}
                                </h1>
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium',
                                        statusConfig.bgClass,
                                        statusConfig.textClass
                                    ]"
                                >
                                    <component :is="statusConfig.icon" class="w-4 h-4" />
                                    {{ payment.status_label }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                {{ payment.formatted_tanggal }} {{ payment.waktu_bayar }}
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Main Content Grid -->
            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Left Column - Payment Details -->
                <div class="space-y-6">
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
                                    <p class="font-medium text-slate-900 dark:text-slate-100">{{ payment.student.nama_lengkap }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">NIS</p>
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ payment.student.nis }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Kelas</p>
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ payment.student.kelas }}</p>
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
                                    <p class="font-mono font-medium text-slate-900 dark:text-slate-100">{{ payment.bill.nomor_tagihan }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Kategori</p>
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ payment.bill.category }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Periode</p>
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ payment.bill.periode }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <!-- Payment Details -->
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
                                    <span class="text-slate-500 dark:text-slate-400">Nominal</span>
                                    <span class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ payment.formatted_nominal }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Metode</p>
                                        <p class="font-medium text-slate-900 dark:text-slate-100 flex items-center gap-1">
                                            <CreditCard class="w-4 h-4" />
                                            {{ payment.metode_label }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Tanggal Bayar</p>
                                        <p class="font-medium text-slate-900 dark:text-slate-100 flex items-center gap-1">
                                            <Calendar class="w-4 h-4" />
                                            {{ payment.formatted_tanggal }}
                                        </p>
                                    </div>
                                </div>
                                <div v-if="payment.keterangan">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Keterangan</p>
                                    <p class="text-slate-900 dark:text-slate-100">{{ payment.keterangan }}</p>
                                </div>
                                <div v-if="payment.creator" class="pt-3 border-t border-slate-200 dark:border-zinc-700">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Dicatat oleh</p>
                                    <p class="font-medium text-slate-900 dark:text-slate-100">{{ payment.creator.name }}</p>
                                </div>
                                <div v-if="payment.verifier" class="pt-3 border-t border-slate-200 dark:border-zinc-700">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Diverifikasi oleh</p>
                                    <p class="font-medium text-slate-900 dark:text-slate-100">
                                        {{ payment.verifier.name }}
                                        <span class="text-sm text-slate-500 dark:text-slate-400"> • {{ payment.verified_at }}</span>
                                    </p>
                                </div>
                                <!-- Cancellation Details Section -->
                                <div v-if="payment.canceller" class="pt-3 border-t border-red-200 dark:border-red-800/50">
                                    <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4 -mx-2">
                                        <p class="text-sm font-medium text-red-600 dark:text-red-400 mb-2 flex items-center gap-1.5">
                                            <XCircle class="w-4 h-4" />
                                            Pembayaran Dibatalkan
                                        </p>
                                        <div class="space-y-2">
                                            <div>
                                                <p class="text-xs text-red-500/70 dark:text-red-400/70">Dibatalkan oleh</p>
                                                <p class="text-sm font-medium text-red-700 dark:text-red-300">
                                                    {{ payment.canceller.name }}
                                                    <span class="text-red-500/70 dark:text-red-400/70"> • {{ payment.cancelled_at }}</span>
                                                </p>
                                            </div>
                                            <div v-if="payment.cancellation_reason">
                                                <p class="text-xs text-red-500/70 dark:text-red-400/70">Alasan Pembatalan</p>
                                                <p class="text-sm text-red-700 dark:text-red-300">{{ payment.cancellation_reason }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                            <span class="text-slate-500 dark:text-slate-400">No. Kwitansi</span>
                                            <span class="font-mono font-medium text-slate-900 dark:text-slate-100">{{ receiptData.nomor_kwitansi }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-500 dark:text-slate-400">Tanggal</span>
                                            <span class="text-slate-900 dark:text-slate-100">{{ receiptData.tanggal }}</span>
                                        </div>
                                    </div>

                                    <div class="my-4 py-4 border-t border-b border-dashed border-slate-300 dark:border-zinc-600 space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-slate-500 dark:text-slate-400">Nama</span>
                                            <span class="text-slate-900 dark:text-slate-100">{{ receiptData.student.nama }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-500 dark:text-slate-400">NIS</span>
                                            <span class="text-slate-900 dark:text-slate-100">{{ receiptData.student.nis }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-500 dark:text-slate-400">Kelas</span>
                                            <span class="text-slate-900 dark:text-slate-100">{{ receiptData.student.kelas }}</span>
                                        </div>
                                    </div>

                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-slate-500 dark:text-slate-400">Pembayaran</span>
                                            <span class="text-slate-900 dark:text-slate-100">{{ receiptData.bill.kategori }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-500 dark:text-slate-400">Periode</span>
                                            <span class="text-slate-900 dark:text-slate-100">{{ receiptData.bill.periode }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-500 dark:text-slate-400">Metode</span>
                                            <span class="text-slate-900 dark:text-slate-100">{{ receiptData.pembayaran.metode }}</span>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-4 border-t border-dashed border-slate-300 dark:border-zinc-600">
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium text-slate-700 dark:text-slate-300">TOTAL BAYAR</span>
                                            <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ receiptData.pembayaran.nominal }}</span>
                                        </div>
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
                            <div v-if="payment.status === 'verified'" class="flex gap-3">
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
                                    Verifikasi Pembayaran
                                </button>
                            </Motion>

                            <!-- Cancel Action -->
                            <Motion v-if="canCancel" :whileTap="{ scale: 0.97 }">
                                <button
                                    @click="openCancelModal"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors font-medium"
                                >
                                    <XCircle class="w-5 h-5" />
                                    Batalkan Pembayaran
                                </button>
                            </Motion>
                        </div>
                    </Motion>
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <BaseModal
            :show="showCancelModal"
            title="Batalkan Pembayaran"
            @close="showCancelModal = false"
        >
            <div class="p-6">
                <p class="text-slate-600 dark:text-slate-400 mb-4">
                    Anda akan membatalkan pembayaran <strong>{{ payment.nomor_kwitansi }}</strong>.
                    Tindakan ini akan mengembalikan status tagihan.
                </p>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Alasan Pembatalan <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="cancelForm.reason"
                        rows="3"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all resize-none"
                        placeholder="Jelaskan alasan pembatalan..."
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
                    Batalkan Pembayaran
                </button>
            </div>
        </BaseModal>
    </AppLayout>
</template>
