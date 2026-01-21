<script setup lang="ts">
/**
 * Parent Payments History Page - Riwayat pembayaran lengkap dengan download kwitansi
 *
 * Menampilkan semua pembayaran yang telah dilakukan untuk anak-anak
 * dengan opsi download kwitansi PDF
 */
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import {
    ArrowLeft, Receipt, Download, CheckCircle2, CreditCard,
    Calendar, FileText, Wallet, Banknote, QrCode
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { index as paymentsIndex, receipt as paymentReceipt } from '@/routes/parent/payments';

interface Student {
    id: number;
    nama_lengkap: string;
    nis: string;
    kelas?: {
        nama_lengkap: string;
    };
}

interface PaymentBill {
    id: number;
    nomor_tagihan: string;
    category: string;
    periode: string;
}

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

interface PaginatedPayments {
    data: Payment[];
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
    payments: PaginatedPayments;
    children: Student[];
    message?: string;
}

const props = defineProps<Props>();

const haptics = useHaptics();

// Computed
const hasPayments = computed(() => props.payments.data.length > 0);
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

const downloadReceipt = (payment: Payment) => {
    haptics.light();
    // Open in new tab for download
    window.open(paymentReceipt({ payment: payment.id }).url, '_blank');
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

            <!-- Payments List -->
            <template v-if="hasChildren && !message">
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                        <template v-if="hasPayments">
                            <div class="divide-y divide-slate-200 dark:divide-zinc-800">
                                <div
                                    v-for="(payment, idx) in payments.data"
                                    :key="payment.id"
                                    class="p-4 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                                >
                                    <div class="flex items-start gap-4">
                                        <!-- Method Icon -->
                                        <div
                                            :class="[
                                                'w-10 h-10 rounded-xl flex items-center justify-center shrink-0',
                                                getMethodColor(payment.metode_pembayaran)
                                            ]"
                                        >
                                            <component :is="getMethodIcon(payment.metode_pembayaran)" class="w-5 h-5" />
                                        </div>

                                        <!-- Payment Details -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">
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
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                        <CheckCircle2 class="w-3 h-3" />
                                                        {{ payment.status_label }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Payment Info Row -->
                                            <div class="mt-3 flex items-center justify-between gap-4">
                                                <div class="flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                                                    <span class="flex items-center gap-1">
                                                        <Calendar class="w-3.5 h-3.5" />
                                                        {{ payment.formatted_tanggal }}
                                                    </span>
                                                    <span class="flex items-center gap-1">
                                                        <FileText class="w-3.5 h-3.5" />
                                                        {{ payment.nomor_kwitansi }}
                                                    </span>
                                                </div>

                                                <!-- Download Button -->
                                                <button
                                                    @click="downloadReceipt(payment)"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-900/20 rounded-lg hover:bg-violet-100 dark:hover:bg-violet-900/30 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-500 focus-visible:ring-offset-2"
                                                >
                                                    <Download class="w-3.5 h-3.5" />
                                                    Kwitansi
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <div v-if="payments.last_page > 1" class="px-4 py-3 border-t border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50">
                                <div class="flex items-center justify-between gap-4">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        Menampilkan {{ payments.data.length }} dari {{ payments.total }} pembayaran
                                    </p>
                                    <div class="flex items-center gap-1">
                                        <template v-for="(link, index) in payments.links" :key="index">
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
                        <div v-else class="p-12 text-center">
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
            </template>

            <!-- No Children State -->
            <div v-else-if="!message" class="bg-white dark:bg-zinc-900 rounded-2xl p-12 shadow-sm border border-slate-200 dark:border-zinc-800 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                    <CreditCard class="w-8 h-8 text-slate-400" />
                </div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">Tidak Ada Data</h3>
                <p class="text-slate-500 dark:text-slate-400">Data pembayaran tidak tersedia</p>
            </div>
        </div>
    </AppLayout>
</template>
