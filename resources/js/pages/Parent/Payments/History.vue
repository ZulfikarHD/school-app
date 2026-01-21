<script setup lang="ts">
/**
 * Parent Payments History Page - Riwayat pembayaran lengkap dengan download kwitansi
 *
 * Menampilkan semua pembayaran (transactions) yang telah dilakukan untuk anak-anak
 * dengan opsi download kwitansi PDF dan detail modal sebelum download.
 * UPDATED: Support combined payment transactions (1 transaction : N bills)
 */
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import {
    ArrowLeft, Receipt, Download, CheckCircle2, CreditCard,
    Calendar, FileText, Wallet, Banknote, QrCode, X, User,
    Hash, ChevronRight, Clock, Printer, Layers
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { index as paymentsIndex, receipt as paymentReceipt } from '@/routes/parent/payments';
import { receipt as transactionReceipt } from '@/routes/parent/payments/transactions';

interface Student {
    id: number;
    nama_lengkap: string;
    nis: string;
    kelas?: string;
}

interface PaymentBill {
    id: number;
    nomor_tagihan: string;
    category: string;
    periode: string;
}

// Legacy Payment interface
interface Payment {
    id: number;
    nomor_kwitansi: string;
    student: {
        id: number;
        nama_lengkap: string;
        nis: string;
        kelas: string;
    };
    bill: PaymentBill;
    nominal: number;
    formatted_nominal: string;
    metode_pembayaran: 'tunai' | 'transfer' | 'qris';
    metode_label: string;
    tanggal_bayar: string;
    formatted_tanggal: string;
    waktu_bayar: string;
    status: string;
    status_label: string;
    created_at: string;
}

// Combined Transaction interface
interface Transaction {
    id: number;
    transaction_number: string;
    total_amount: number;
    formatted_amount: string;
    payment_method: string;
    method_label: string;
    payment_date: string;
    formatted_date: string;
    status: string;
    status_label: string;
    bill_count: number;
    students: Student[];
    has_proof: boolean;
    created_at: string;
}

interface PaginatedTransactions {
    data: Transaction[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Props {
    transactions: PaginatedTransactions;
    payments?: Payment[]; // Legacy support
    children: Student[];
    message?: string;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const showTransactionDetailModal = ref(false);
const selectedTransaction = ref<Transaction | null>(null);
const showPaymentDetailModal = ref(false);
const selectedPayment = ref<Payment | null>(null);

// Computed
const hasTransactions = computed(() => props.transactions?.data?.length > 0);
const hasLegacyPayments = computed(() => (props.payments?.length ?? 0) > 0);
const hasChildren = computed(() => props.children.length > 0);

// Methods
const getMethodIcon = (method: string) => {
    const icons: Record<string, any> = {
        tunai: Banknote,
        transfer: Wallet,
        qris: QrCode,
    };
    return icons[method] || Wallet;
};

const getMethodColor = (method: string): string => {
    const colors: Record<string, string> = {
        tunai: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
        transfer: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
        qris: 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
    };
    return colors[method] || 'bg-slate-100 text-slate-600';
};

// Transaction methods
const openTransactionDetail = (transaction: Transaction) => {
    haptics.light();
    selectedTransaction.value = transaction;
    showTransactionDetailModal.value = true;
};

const closeTransactionDetail = () => {
    haptics.light();
    showTransactionDetailModal.value = false;
    selectedTransaction.value = null;
};

const downloadTransactionReceipt = (transaction: Transaction) => {
    haptics.light();
    window.open(transactionReceipt({ transaction: transaction.id }).url, '_blank');
};

// Legacy payment methods
const openPaymentDetail = (payment: Payment) => {
    haptics.light();
    selectedPayment.value = payment;
    showPaymentDetailModal.value = true;
};

const closePaymentDetail = () => {
    haptics.light();
    showPaymentDetailModal.value = false;
    selectedPayment.value = null;
};

const downloadReceipt = (payment: Payment) => {
    haptics.light();
    // Open in new tab for download
    window.open(paymentReceipt({ payment: payment.id }).url, '_blank');
};

// Format currency helper
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
        <Head title="Riwayat Pembayaran" />

        <div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
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
                            <Receipt class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                Riwayat Pembayaran
                            </h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                Semua pembayaran dengan kwitansi
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Message if error -->
            <div v-if="message" class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl p-4 border border-amber-200 dark:border-amber-800">
                <p class="text-sm text-amber-700 dark:text-amber-300">{{ message }}</p>
            </div>

            <!-- Transactions List (Combined Payment) -->
            <template v-if="hasChildren && !message">
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                        <template v-if="hasTransactions">
                            <div class="divide-y divide-slate-200 dark:divide-zinc-800">
                                <button
                                    v-for="transaction in transactions.data"
                                    :key="transaction.id"
                                    @click="openTransactionDetail(transaction)"
                                    class="w-full p-4 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors text-left group"
                                >
                                    <div class="flex items-start gap-4">
                                        <!-- Method Icon -->
                                        <div
                                            :class="[
                                                'w-10 h-10 rounded-xl flex items-center justify-center shrink-0',
                                                getMethodColor(transaction.payment_method)
                                            ]"
                                        >
                                            <component :is="getMethodIcon(transaction.payment_method)" class="w-5 h-5" />
                                        </div>

                                        <!-- Transaction Details -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <p class="font-semibold text-slate-900 dark:text-slate-100 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                                                            {{ transaction.transaction_number }}
                                                        </p>
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400">
                                                            <Layers class="w-3 h-3" />
                                                            {{ transaction.bill_count }} tagihan
                                                        </span>
                                                    </div>
                                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                                        {{ transaction.students.map(s => s.nama_lengkap).join(', ') }}
                                                    </p>
                                                </div>
                                                <div class="text-right shrink-0 flex items-center gap-2">
                                                    <div>
                                                        <p class="font-bold text-slate-900 dark:text-slate-100">
                                                            {{ transaction.formatted_amount }}
                                                        </p>
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                            <CheckCircle2 class="w-3 h-3" />
                                                            {{ transaction.status_label }}
                                                        </span>
                                                    </div>
                                                    <ChevronRight class="w-5 h-5 text-slate-400 group-hover:text-violet-500 transition-colors" />
                                                </div>
                                            </div>

                                            <!-- Transaction Info Row -->
                                            <div class="mt-3 flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                                                <span class="flex items-center gap-1">
                                                    <Calendar class="w-3.5 h-3.5" />
                                                    {{ transaction.formatted_date }}
                                                </span>
                                                <span class="flex items-center gap-1">
                                                    <Wallet class="w-3.5 h-3.5" />
                                                    {{ transaction.method_label }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </div>

                            <!-- Pagination -->
                            <div v-if="transactions.last_page > 1" class="px-4 py-3 border-t border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50">
                                <div class="flex items-center justify-between gap-4">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        Menampilkan {{ transactions.data.length }} dari {{ transactions.total }} transaksi
                                    </p>
                                    <div class="flex items-center gap-1">
                                        <template v-for="(link, index) in transactions.links" :key="index">
                                            <Link
                                                v-if="link.url"
                                                :href="link.url"
                                                :class="[
                                                    'px-3 py-1.5 text-sm rounded-lg transition-colors',
                                                    link.active
                                                        ? 'bg-violet-500 text-white'
                                                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-zinc-700'
                                                ]"
                                                v-html="link.label"
                                            />
                                            <span
                                                v-else
                                                class="px-3 py-1.5 text-sm text-slate-400 dark:text-slate-600"
                                                v-html="link.label"
                                            />
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <div v-else-if="!hasLegacyPayments" class="p-12 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                                <Receipt class="w-8 h-8 text-slate-400" />
                            </div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">
                                Belum Ada Riwayat Pembayaran
                            </h3>
                            <p class="text-slate-500 dark:text-slate-400">
                                Pembayaran yang telah selesai akan muncul di sini
                            </p>
                        </div>
                    </div>
                </Motion>

                <!-- Legacy Payments (if any) -->
                <Motion
                    v-if="hasLegacyPayments"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50">
                            <h3 class="font-medium text-slate-700 dark:text-slate-300 text-sm">Pembayaran Sebelumnya</h3>
                        </div>
                        <div class="divide-y divide-slate-200 dark:divide-zinc-800">
                            <button
                                v-for="payment in payments"
                                :key="payment.id"
                                @click="openPaymentDetail(payment)"
                                class="w-full p-4 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors text-left group"
                            >
                                <div class="flex items-start gap-4">
                                    <div
                                        :class="[
                                            'w-10 h-10 rounded-xl flex items-center justify-center shrink-0',
                                            getMethodColor(payment.metode_pembayaran)
                                        ]"
                                    >
                                        <component :is="getMethodIcon(payment.metode_pembayaran)" class="w-5 h-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="font-semibold text-slate-900 dark:text-slate-100 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                                                    {{ payment.bill.category }}
                                                </p>
                                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                                    {{ payment.bill.periode }} • {{ payment.student.nama_lengkap }}
                                                </p>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <p class="font-bold text-slate-900 dark:text-slate-100">
                                                    {{ payment.formatted_nominal }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </Motion>
            </template>

            <!-- No Children State -->
            <div v-else-if="!message" class="bg-white dark:bg-zinc-900 rounded-2xl p-12 shadow-sm border border-slate-200 dark:border-zinc-800 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                    <CreditCard class="w-8 h-8 text-slate-400" />
                </div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">Tidak Ada Data</h3>
                <p class="text-slate-500 dark:text-slate-400">Data pembayaran tidak tersedia</p>
            </div>

            <!-- Transaction Detail Modal -->
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
                        v-if="showTransactionDetailModal && selectedTransaction"
                        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
                    >
                        <!-- Backdrop -->
                        <div
                            class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                            @click="closeTransactionDetail"
                        ></div>

                        <!-- Modal Content -->
                        <Motion
                            :initial="{ opacity: 0, y: 50, scale: 0.95 }"
                            :animate="{ opacity: 1, y: 0, scale: 1 }"
                            :exit="{ opacity: 0, y: 50, scale: 0.95 }"
                            :transition="{ duration: 0.25, ease: 'easeOut' }"
                            class="relative w-full sm:max-w-lg bg-white dark:bg-zinc-900 rounded-t-3xl sm:rounded-2xl shadow-2xl max-h-[85vh] overflow-hidden flex flex-col"
                        >
                            <!-- Header -->
                            <div class="flex items-center justify-between p-4 sm:p-6 border-b border-slate-200 dark:border-zinc-800 shrink-0">
                                <div class="flex items-center gap-3">
                                    <div
                                        :class="[
                                            'w-10 h-10 rounded-xl flex items-center justify-center',
                                            getMethodColor(selectedTransaction.payment_method)
                                        ]"
                                    >
                                        <component :is="getMethodIcon(selectedTransaction.payment_method)" class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Detail Transaksi</h2>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ selectedTransaction.transaction_number }}</p>
                                    </div>
                                </div>
                                <button
                                    @click="closeTransactionDetail"
                                    class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                                >
                                    <X class="w-5 h-5" />
                                </button>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
                                <!-- Status and Bill Count -->
                                <div class="flex items-center justify-center gap-2">
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        <CheckCircle2 class="w-4 h-4" />
                                        {{ selectedTransaction.status_label }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-3 py-2 rounded-full text-sm font-medium bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400">
                                        <Layers class="w-4 h-4" />
                                        {{ selectedTransaction.bill_count }} tagihan
                                    </span>
                                </div>

                                <!-- Amount Card -->
                                <div class="bg-linear-to-br from-emerald-500 to-green-600 rounded-2xl p-5 text-white text-center">
                                    <p class="text-emerald-100 text-sm">Total Pembayaran</p>
                                    <p class="text-3xl font-bold mt-1">{{ selectedTransaction.formatted_amount }}</p>
                                    <p class="text-emerald-200 text-sm mt-2">
                                        {{ selectedTransaction.method_label }}
                                    </p>
                                </div>

                                <!-- Students Info -->
                                <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-2xl p-4 space-y-3">
                                    <div class="section-title text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        Siswa
                                    </div>
                                    <div
                                        v-for="student in selectedTransaction.students"
                                        :key="student.id"
                                        class="flex items-center gap-3"
                                    >
                                        <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                            <User class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ student.nama_lengkap }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">NIS: {{ student.nis }} • {{ student.kelas }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transaction Info -->
                                <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-2xl p-4 space-y-3">
                                    <!-- Payment Date -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                                            <Clock class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Tanggal Pembayaran</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">
                                                {{ selectedTransaction.formatted_date }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Transaction Number -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-slate-200 dark:bg-zinc-700 flex items-center justify-center shrink-0">
                                            <Hash class="w-4 h-4 text-slate-600 dark:text-slate-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">No. Transaksi</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100 font-mono text-sm">{{ selectedTransaction.transaction_number }}</p>
                                        </div>
                                    </div>

                                    <!-- Created At -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-slate-200 dark:bg-zinc-700 flex items-center justify-center shrink-0">
                                            <Calendar class="w-4 h-4 text-slate-600 dark:text-slate-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Disubmit</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ selectedTransaction.created_at }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="p-4 sm:p-6 border-t border-slate-200 dark:border-zinc-800 shrink-0 space-y-3">
                                <!-- Download Receipt Button -->
                                <button
                                    @click="downloadTransactionReceipt(selectedTransaction)"
                                    class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-violet-500 text-white rounded-xl font-medium hover:bg-violet-600 transition-colors shadow-lg shadow-violet-500/25"
                                >
                                    <Download class="w-5 h-5" />
                                    Download Kwitansi PDF
                                </button>
                                <!-- Close Button -->
                                <button
                                    @click="closeTransactionDetail"
                                    class="w-full px-6 py-3 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                                >
                                    Tutup
                                </button>
                            </div>
                        </Motion>
                    </div>
                </Transition>
            </Teleport>

            <!-- Payment Detail Modal (Legacy) -->
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
                        v-if="showPaymentDetailModal && selectedPayment"
                        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
                    >
                        <!-- Backdrop -->
                        <div
                            class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                            @click="closePaymentDetail"
                        ></div>

                        <!-- Modal Content -->
                        <Motion
                            :initial="{ opacity: 0, y: 50, scale: 0.95 }"
                            :animate="{ opacity: 1, y: 0, scale: 1 }"
                            :exit="{ opacity: 0, y: 50, scale: 0.95 }"
                            :transition="{ duration: 0.25, ease: 'easeOut' }"
                            class="relative w-full sm:max-w-lg bg-white dark:bg-zinc-900 rounded-t-3xl sm:rounded-2xl shadow-2xl max-h-[85vh] overflow-hidden flex flex-col"
                        >
                            <!-- Header -->
                            <div class="flex items-center justify-between p-4 sm:p-6 border-b border-slate-200 dark:border-zinc-800 shrink-0">
                                <div class="flex items-center gap-3">
                                    <div
                                        :class="[
                                            'w-10 h-10 rounded-xl flex items-center justify-center',
                                            getMethodColor(selectedPayment.metode_pembayaran)
                                        ]"
                                    >
                                        <component :is="getMethodIcon(selectedPayment.metode_pembayaran)" class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Detail Pembayaran</h2>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ selectedPayment.nomor_kwitansi }}</p>
                                    </div>
                                </div>
                                <button
                                    @click="closePaymentDetail"
                                    class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                                >
                                    <X class="w-5 h-5" />
                                </button>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
                                <!-- Status Badge -->
                                <div class="flex items-center justify-center">
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        <CheckCircle2 class="w-4 h-4" />
                                        {{ selectedPayment.status_label }}
                                    </span>
                                </div>

                                <!-- Amount Card -->
                                <div class="bg-linear-to-br from-emerald-500 to-green-600 rounded-2xl p-5 text-white text-center">
                                    <p class="text-emerald-100 text-sm">Nominal Pembayaran</p>
                                    <p class="text-3xl font-bold mt-1">{{ selectedPayment.formatted_nominal }}</p>
                                    <p class="text-emerald-200 text-sm mt-2">
                                        {{ selectedPayment.metode_label }}
                                    </p>
                                </div>

                                <!-- Info Grid -->
                                <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-2xl p-4 space-y-3">
                                    <!-- Category -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center shrink-0">
                                            <Receipt class="w-4 h-4 text-violet-600 dark:text-violet-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Kategori Pembayaran</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ selectedPayment.bill.category }}</p>
                                        </div>
                                    </div>

                                    <!-- Period -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                                            <Calendar class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Periode Tagihan</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ selectedPayment.bill.periode }}</p>
                                        </div>
                                    </div>

                                    <!-- Student -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                            <User class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Siswa</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ selectedPayment.student.nama_lengkap }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">NIS: {{ selectedPayment.student.nis }} • {{ selectedPayment.student.kelas }}</p>
                                        </div>
                                    </div>

                                    <!-- Payment Date -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                                            <Clock class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Tanggal Bayar</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">
                                                {{ selectedPayment.formatted_tanggal }}
                                                <span v-if="selectedPayment.waktu_bayar" class="text-slate-500 dark:text-slate-400">
                                                    • {{ selectedPayment.waktu_bayar }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Receipt Number -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-slate-200 dark:bg-zinc-700 flex items-center justify-center shrink-0">
                                            <Hash class="w-4 h-4 text-slate-600 dark:text-slate-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">No. Kwitansi</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100 font-mono text-sm">{{ selectedPayment.nomor_kwitansi }}</p>
                                        </div>
                                    </div>

                                    <!-- Bill Number -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-slate-200 dark:bg-zinc-700 flex items-center justify-center shrink-0">
                                            <FileText class="w-4 h-4 text-slate-600 dark:text-slate-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">No. Tagihan</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100 font-mono text-sm">{{ selectedPayment.bill.nomor_tagihan }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="p-4 sm:p-6 border-t border-slate-200 dark:border-zinc-800 shrink-0 space-y-3">
                                <!-- Download Receipt Button -->
                                <button
                                    @click="downloadReceipt(selectedPayment)"
                                    class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-violet-500 text-white rounded-xl font-medium hover:bg-violet-600 transition-colors shadow-lg shadow-violet-500/25"
                                >
                                    <Download class="w-5 h-5" />
                                    Download Kwitansi PDF
                                </button>
                                <!-- Close Button -->
                                <button
                                    @click="closePaymentDetail"
                                    class="w-full px-6 py-3 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                                >
                                    Tutup
                                </button>
                            </div>
                        </Motion>
                    </div>
                </Transition>
            </Teleport>
        </div>
    </AppLayout>
</template>
