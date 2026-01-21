<script setup lang="ts">
/**
 * Parent Payments Index Page - Halaman tagihan untuk orang tua
 * Menampilkan summary pembayaran, tagihan aktif, dan riwayat pembayaran
 * dengan download kwitansi untuk pembayaran lunas
 *
 * Fitur:
 * - Child selector untuk filter berdasarkan anak
 * - Bill detail modal untuk melihat detail tagihan
 * - Summary cards dengan total tunggakan
 * - Bulk payment selection dengan checkbox
 * - Floating action button untuk bayar tagihan terpilih
 */
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import Alert from '@/components/ui/Alert.vue';
import { useHaptics } from '@/composables/useHaptics';
import {
    CreditCard, Clock, AlertCircle, CheckCircle2, Calendar, AlertTriangle,
    Receipt, Wallet, FileText, ChevronRight, Download, History, X, User,
    ChevronDown, Hash, Building2, Check, Square, CheckSquare
} from 'lucide-vue-next';
import { Motion, AnimatePresence } from 'motion-v';
import { history as paymentsHistory, submit as paymentsSubmit } from '@/routes/parent/payments';

interface Student {
    id: number;
    nama_lengkap: string;
    nis: string;
    kelas?: {
        nama_lengkap: string;
    };
}

interface Category {
    id: number;
    nama: string;
    kode: string;
}

interface Bill {
    id: number;
    nomor_tagihan: string;
    student: {
        id: number;
        nama_lengkap: string;
        nis: string;
        kelas: string;
    };
    category: Category;
    bulan: number;
    tahun: number;
    nama_bulan: string;
    periode: string;
    nominal: number;
    nominal_terbayar: number;
    sisa_tagihan: number;
    formatted_nominal: string;
    formatted_sisa: string;
    status: 'belum_bayar' | 'sebagian' | 'lunas' | 'dibatalkan';
    status_label: string;
    is_overdue: boolean;
    has_pending_payment: boolean;
    tanggal_jatuh_tempo: string;
    formatted_due_date: string;
    // Payment info for paid bills (optional)
    last_payment_id?: number;
}

interface Summary {
    total_tunggakan: number;
    formatted_tunggakan: string;
    total_belum_bayar: number;
    total_sebagian: number;
    total_overdue: number;
    total_lunas_bulan_ini: number;
    pending_count: number;
    nearest_due_date: string | null;
    nearest_bill: {
        category: string;
        periode: string;
        nominal: string;
    } | null;
}

interface PendingPayment {
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
    tanggal_bayar: string;
    status: string;
    status_label: string;
    created_at: string;
    has_bukti: boolean;
}

interface Props {
    children: Student[];
    activeBills: Bill[];
    paidBills: Bill[];
    pendingPayments: PendingPayment[];
    summary: Summary;
    message?: string;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const page = usePage();
const activeTab = ref<'active' | 'history'>('active');
const selectedChildId = ref<number | null>(null);
const showBillDetailModal = ref(false);
const selectedBill = ref<Bill | null>(null);

// Flash message handling
const showAlert = ref(false);
const alertType = ref<'success' | 'error' | 'warning' | 'info'>('success');
const alertMessage = ref('');

// Check for flash messages on mount
const flash = computed(() => page.props.flash as { success?: string; error?: string; warning?: string; info?: string } | undefined);

watch(flash, (newFlash) => {
    if (newFlash?.success) {
        alertType.value = 'success';
        alertMessage.value = newFlash.success;
        showAlert.value = true;
    } else if (newFlash?.error) {
        alertType.value = 'error';
        alertMessage.value = newFlash.error;
        showAlert.value = true;
    } else if (newFlash?.warning) {
        alertType.value = 'warning';
        alertMessage.value = newFlash.warning;
        showAlert.value = true;
    } else if (newFlash?.info) {
        alertType.value = 'info';
        alertMessage.value = newFlash.info;
        showAlert.value = true;
    }
}, { immediate: true });

// Selection mode for bulk payment
const isSelectionMode = ref(false);
const selectedBillIds = ref<Set<number>>(new Set());

// Computed
const hasChildren = computed(() => props.children.length > 0);
const hasMultipleChildren = computed(() => props.children.length > 1);

// Filter bills by selected child
const filteredActiveBills = computed(() => {
    if (!selectedChildId.value) {
        return props.activeBills;
    }
    return props.activeBills.filter(bill => bill.student.id === selectedChildId.value);
});

const filteredPaidBills = computed(() => {
    if (!selectedChildId.value) {
        return props.paidBills;
    }
    return props.paidBills.filter(bill => bill.student.id === selectedChildId.value);
});

// Filter pending payments by selected child
const pendingPayments = computed(() => {
    if (!selectedChildId.value) {
        return props.pendingPayments || [];
    }
    return (props.pendingPayments || []).filter(payment => payment.student.id === selectedChildId.value);
});

const hasActiveBills = computed(() => filteredActiveBills.value.length > 0);
const hasPaidBills = computed(() => filteredPaidBills.value.length > 0);

// Bills that can be selected (no pending payment)
const selectableBills = computed(() => {
    return filteredActiveBills.value.filter(bill => !bill.has_pending_payment);
});

// Selection computed
const selectedCount = computed(() => selectedBillIds.value.size);
const hasSelection = computed(() => selectedCount.value > 0);
const allSelected = computed(() => {
    if (selectableBills.value.length === 0) return false;
    return selectableBills.value.every(bill => selectedBillIds.value.has(bill.id));
});
const selectedTotal = computed(() => {
    return filteredActiveBills.value
        .filter(bill => selectedBillIds.value.has(bill.id))
        .reduce((sum, bill) => sum + bill.sisa_tagihan, 0);
});
const formattedSelectedTotal = computed(() => formatCurrency(selectedTotal.value));

// Get selected child info
const selectedChild = computed(() => {
    if (!selectedChildId.value) return null;
    return props.children.find(c => c.id === selectedChildId.value) || null;
});

// Calculate filtered summary
const filteredSummary = computed(() => {
    if (!selectedChildId.value) {
        return props.summary;
    }
    // Calculate summary for selected child only
    const childBills = props.activeBills.filter(b => b.student.id === selectedChildId.value);
    const totalTunggakan = childBills.reduce((sum, bill) => sum + bill.sisa_tagihan, 0);
    const totalBelumBayar = childBills.filter(b => b.status === 'belum_bayar').length;
    const totalOverdue = childBills.filter(b => b.is_overdue && b.status !== 'lunas').length;
    const nearestBill = childBills.sort((a, b) =>
        new Date(a.tanggal_jatuh_tempo).getTime() - new Date(b.tanggal_jatuh_tempo).getTime()
    )[0] || null;

    const paidThisMonth = filteredPaidBills.value.length;

    return {
        total_tunggakan: totalTunggakan,
        formatted_tunggakan: formatCurrency(totalTunggakan),
        total_belum_bayar: totalBelumBayar,
        total_sebagian: childBills.filter(b => b.status === 'sebagian').length,
        total_overdue: totalOverdue,
        total_lunas_bulan_ini: paidThisMonth,
        nearest_due_date: nearestBill?.formatted_due_date || null,
        nearest_bill: nearestBill ? {
            category: nearestBill.category.nama,
            periode: nearestBill.periode,
            nominal: nearestBill.formatted_sisa,
        } : null,
    };
});

// Methods
const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const getStatusBadgeClass = (status: string, isOverdue: boolean): string => {
    if (isOverdue && status !== 'lunas') {
        return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
    }
    const classes: Record<string, string> = {
        belum_bayar: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        sebagian: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        lunas: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        dibatalkan: 'bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-slate-400',
    };
    return classes[status] || 'bg-slate-100 text-slate-700';
};

const getStatusLabel = (status: string, isOverdue: boolean): string => {
    if (isOverdue && status !== 'lunas') {
        return 'Jatuh Tempo';
    }
    const labels: Record<string, string> = {
        belum_bayar: 'Belum Bayar',
        sebagian: 'Sebagian',
        lunas: 'Lunas',
        dibatalkan: 'Dibatalkan',
    };
    return labels[status] || status;
};

const getStatusIcon = (status: string, isOverdue: boolean) => {
    if (isOverdue && status !== 'lunas') {
        return AlertTriangle;
    }
    const icons: Record<string, any> = {
        belum_bayar: Clock,
        sebagian: AlertCircle,
        lunas: CheckCircle2,
        dibatalkan: AlertCircle,
    };
    return icons[status] || Clock;
};

const switchTab = (tab: 'active' | 'history') => {
    haptics.light();
    activeTab.value = tab;
};

const selectChild = (childId: number | null) => {
    haptics.light();
    selectedChildId.value = childId;
};

const openBillDetail = (bill: Bill) => {
    haptics.light();
    selectedBill.value = bill;
    showBillDetailModal.value = true;
};

const closeBillDetail = () => {
    haptics.light();
    showBillDetailModal.value = false;
    selectedBill.value = null;
};

// Selection methods
const toggleSelectionMode = () => {
    haptics.light();
    isSelectionMode.value = !isSelectionMode.value;
    if (!isSelectionMode.value) {
        selectedBillIds.value.clear();
    }
};

const toggleBillSelection = (billId: number, event?: Event) => {
    event?.stopPropagation();
    haptics.light();

    if (selectedBillIds.value.has(billId)) {
        selectedBillIds.value.delete(billId);
    } else {
        selectedBillIds.value.add(billId);
    }
    // Trigger reactivity
    selectedBillIds.value = new Set(selectedBillIds.value);

    // Auto enable selection mode if first selection
    if (selectedBillIds.value.size > 0 && !isSelectionMode.value) {
        isSelectionMode.value = true;
    }
};

const toggleSelectAll = () => {
    haptics.light();
    if (allSelected.value) {
        // Deselect all
        selectedBillIds.value.clear();
    } else {
        // Select all selectable bills (those without pending payments)
        selectableBills.value.forEach(bill => {
            selectedBillIds.value.add(bill.id);
        });
    }
    selectedBillIds.value = new Set(selectedBillIds.value);
};

const proceedToPayment = () => {
    haptics.medium();
    if (selectedCount.value === 0) return;

    // Navigate to payment submit page with selected bill IDs
    const billIds = Array.from(selectedBillIds.value).join(',');
    router.visit(paymentsSubmit().url + '?bills=' + billIds);
};

const cancelSelection = () => {
    haptics.light();
    isSelectionMode.value = false;
    selectedBillIds.value.clear();
};

// Clear selection when child filter changes
watch(selectedChildId, () => {
    selectedBillIds.value.clear();
});
</script>

<template>
    <AppLayout>
        <Head title="Pembayaran" />

        <!-- Flash Message Alert -->
        <Alert
            :show="showAlert"
            :type="alertType"
            :message="alertMessage"
            :title="alertType === 'success' ? 'Berhasil' : alertType === 'error' ? 'Gagal' : ''"
            position="top"
            :duration="5000"
            @close="showAlert = false"
        />

        <div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="w-12 h-12 rounded-xl bg-linear-to-br from-violet-400 to-violet-600 flex items-center justify-center shadow-lg shadow-violet-500/25 shrink-0">
                            <CreditCard class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                Pembayaran
                            </h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                Lihat tagihan dan riwayat pembayaran
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Message if no data -->
            <div v-if="message" class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl p-4 border border-amber-200 dark:border-amber-800">
                <div class="flex items-start gap-3">
                    <AlertTriangle class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                    <p class="text-sm text-amber-700 dark:text-amber-300">{{ message }}</p>
                </div>
            </div>

            <template v-if="hasChildren">
                <!-- Child Selector (Only show when multiple children) -->
                <Motion
                    v-if="hasMultipleChildren"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.03 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">
                            Pilih Anak
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <!-- All Children Button -->
                            <button
                                @click="selectChild(null)"
                                :class="[
                                    'inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all',
                                    !selectedChildId
                                        ? 'bg-violet-500 text-white shadow-lg shadow-violet-500/25'
                                        : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700'
                                ]"
                            >
                                <User class="w-4 h-4" />
                                Semua Anak
                            </button>
                            <!-- Individual Child Buttons -->
                            <button
                                v-for="child in children"
                                :key="child.id"
                                @click="selectChild(child.id)"
                                :class="[
                                    'inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all',
                                    selectedChildId === child.id
                                        ? 'bg-violet-500 text-white shadow-lg shadow-violet-500/25'
                                        : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700'
                                ]"
                            >
                                <User class="w-4 h-4" />
                                <span class="truncate max-w-[120px]">{{ child.nama_lengkap }}</span>
                                <span class="text-xs opacity-70">({{ child.kelas?.nama_lengkap || '-' }})</span>
                            </button>
                        </div>
                    </div>
                </Motion>

                <!-- Summary Cards -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
                >
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Total Tunggakan -->
                        <div class="col-span-2 bg-linear-to-br from-violet-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg shadow-violet-500/25">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-violet-100 text-sm">Total Tunggakan{{ selectedChild ? ` - ${selectedChild.nama_lengkap}` : '' }}</p>
                                    <p class="text-3xl font-bold mt-1">{{ filteredSummary.formatted_tunggakan }}</p>
                                    <p v-if="filteredSummary.total_overdue > 0" class="text-violet-200 text-sm mt-2 flex items-center gap-1">
                                        <AlertTriangle class="w-4 h-4" />
                                        {{ filteredSummary.total_overdue }} tagihan jatuh tempo
                                    </p>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                                    <Wallet class="w-6 h-6" />
                                </div>
                            </div>
                        </div>

                        <!-- Belum Bayar -->
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                    <Clock class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ filteredSummary.total_belum_bayar }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Belum Bayar</p>
                                </div>
                            </div>
                        </div>

                        <!-- Lunas Bulan Ini -->
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                    <CheckCircle2 class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ filteredSummary.total_lunas_bulan_ini }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Lunas Bulan Ini</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Nearest Due Date Alert -->
                <Motion
                    v-if="filteredSummary.nearest_bill"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                >
                    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl p-4 border border-amber-200 dark:border-amber-800">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                                <Calendar class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Tagihan Terdekat</p>
                                <p class="text-amber-700 dark:text-amber-300 mt-0.5">
                                    {{ filteredSummary.nearest_bill.category }} - {{ filteredSummary.nearest_bill.periode }}
                                </p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="font-semibold text-amber-800 dark:text-amber-200">{{ filteredSummary.nearest_bill.nominal }}</span>
                                    <span class="text-sm text-amber-600 dark:text-amber-400">Jatuh tempo: {{ filteredSummary.nearest_due_date }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Pending Payments Section -->
                <Motion
                    v-if="pendingPayments.length > 0"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.12 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-200 dark:border-zinc-800 bg-orange-50 dark:bg-orange-900/20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Clock class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                                    <h3 class="font-semibold text-orange-900 dark:text-orange-100">Menunggu Verifikasi</h3>
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300">
                                        {{ pendingPayments.length }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="divide-y divide-slate-200 dark:divide-zinc-800">
                            <div
                                v-for="payment in pendingPayments"
                                :key="payment.id"
                                class="p-4"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="font-semibold text-slate-900 dark:text-slate-100">
                                                {{ payment.bill.category }}
                                            </p>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                                                <Clock class="w-3 h-3" />
                                                Menunggu Verifikasi
                                            </span>
                                        </div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                            {{ payment.bill.periode }} • {{ payment.student.nama_lengkap }}
                                        </p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                            Disubmit: {{ payment.created_at }}
                                        </p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="font-bold text-slate-900 dark:text-slate-100">
                                            {{ payment.formatted_nominal }}
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            {{ payment.metode_pembayaran }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Tabs -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                        <!-- Tab Headers -->
                        <div class="flex border-b border-slate-200 dark:border-zinc-800">
                            <button
                                @click="switchTab('active')"
                                :class="[
                                    'flex-1 px-4 py-3 text-sm font-medium transition-colors relative',
                                    activeTab === 'active'
                                        ? 'text-violet-600 dark:text-violet-400'
                                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'
                                ]"
                            >
                                <span class="flex items-center justify-center gap-2">
                                    <Receipt class="w-4 h-4" />
                                    Tagihan Aktif
                                    <span
                                        v-if="filteredActiveBills.length > 0"
                                        class="px-1.5 py-0.5 text-xs rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"
                                    >
                                        {{ filteredActiveBills.length }}
                                    </span>
                                </span>
                                <div
                                    v-if="activeTab === 'active'"
                                    class="absolute bottom-0 left-0 right-0 h-0.5 bg-violet-500"
                                ></div>
                            </button>
                            <button
                                @click="switchTab('history')"
                                :class="[
                                    'flex-1 px-4 py-3 text-sm font-medium transition-colors relative',
                                    activeTab === 'history'
                                        ? 'text-violet-600 dark:text-violet-400'
                                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'
                                ]"
                            >
                                <span class="flex items-center justify-center gap-2">
                                    <FileText class="w-4 h-4" />
                                    Riwayat
                                </span>
                                <div
                                    v-if="activeTab === 'history'"
                                    class="absolute bottom-0 left-0 right-0 h-0.5 bg-violet-500"
                                ></div>
                            </button>
                        </div>

                        <!-- Selection Header (appears when in active tab with bills) -->
                        <div
                            v-if="activeTab === 'active' && hasActiveBills"
                            class="flex items-center justify-between px-4 py-2 bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-800"
                        >
                            <div class="flex items-center gap-3">
                                <!-- Select All Checkbox -->
                                <button
                                    @click="toggleSelectAll"
                                    class="w-6 h-6 rounded-lg flex items-center justify-center transition-colors"
                                    :class="allSelected
                                        ? 'bg-violet-500 text-white'
                                        : 'bg-white dark:bg-zinc-700 border border-slate-300 dark:border-zinc-600 text-slate-400 hover:border-violet-400'"
                                >
                                    <Check v-if="allSelected" class="w-4 h-4" />
                                </button>
                                <span class="text-sm text-slate-600 dark:text-slate-400">
                                    <template v-if="hasSelection">
                                        {{ selectedCount }} tagihan dipilih
                                    </template>
                                    <template v-else>
                                        Pilih tagihan untuk dibayar
                                    </template>
                                </span>
                            </div>
                            <button
                                v-if="hasSelection"
                                @click="cancelSelection"
                                class="text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300"
                            >
                                Batal
                            </button>
                        </div>

                        <!-- Tab Content: Active Bills -->
                        <div v-if="activeTab === 'active'" class="divide-y divide-slate-200 dark:divide-zinc-800">
                            <template v-if="hasActiveBills">
                                <div
                                    v-for="(bill, idx) in filteredActiveBills"
                                    :key="bill.id"
                                    @click="openBillDetail(bill)"
                                    class="w-full p-4 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors text-left group cursor-pointer"
                                    :class="[
                                        selectedBillIds.has(bill.id) ? 'bg-violet-50 dark:bg-violet-900/20' : '',
                                        bill.has_pending_payment ? 'bg-orange-50/50 dark:bg-orange-900/10' : ''
                                    ]"
                                >
                                    <div class="flex items-start gap-3">
                                        <!-- Checkbox - disabled if has pending payment -->
                                        <button
                                            v-if="!bill.has_pending_payment"
                                            @click="toggleBillSelection(bill.id, $event)"
                                            class="mt-0.5 w-6 h-6 rounded-lg flex items-center justify-center shrink-0 transition-all"
                                            :class="selectedBillIds.has(bill.id)
                                                ? 'bg-violet-500 text-white shadow-md shadow-violet-500/25'
                                                : 'bg-white dark:bg-zinc-700 border-2 border-slate-300 dark:border-zinc-600 hover:border-violet-400 dark:hover:border-violet-500'"
                                        >
                                            <Check v-if="selectedBillIds.has(bill.id)" class="w-4 h-4" />
                                        </button>
                                        <!-- Pending indicator instead of checkbox -->
                                        <div
                                            v-else
                                            class="mt-0.5 w-6 h-6 rounded-lg flex items-center justify-center shrink-0 bg-orange-100 dark:bg-orange-900/30"
                                            title="Menunggu Verifikasi"
                                        >
                                            <Clock class="w-4 h-4 text-orange-600 dark:text-orange-400" />
                                        </div>

                                        <!-- Bill Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p class="font-semibold text-slate-900 dark:text-slate-100 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                                                    {{ bill.category.nama }}
                                                </p>
                                                <!-- Pending Payment Badge -->
                                                <span
                                                    v-if="bill.has_pending_payment"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400"
                                                >
                                                    <Clock class="w-3 h-3" />
                                                    Menunggu Verifikasi
                                                </span>
                                                <!-- Status Badge (only show if no pending) -->
                                                <span
                                                    v-else
                                                    :class="[
                                                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium',
                                                        getStatusBadgeClass(bill.status, bill.is_overdue)
                                                    ]"
                                                >
                                                    <component :is="getStatusIcon(bill.status, bill.is_overdue)" class="w-3 h-3" />
                                                    {{ getStatusLabel(bill.status, bill.is_overdue) }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                                {{ bill.periode }} • {{ bill.student.nama_lengkap }}
                                            </p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                                Jatuh tempo: {{ bill.formatted_due_date }}
                                            </p>
                                        </div>

                                        <!-- Amount and Arrow -->
                                        <div class="text-right shrink-0 flex items-center gap-2">
                                            <div>
                                                <p class="font-bold text-slate-900 dark:text-slate-100">
                                                    {{ bill.formatted_sisa }}
                                                </p>
                                                <p v-if="bill.nominal_terbayar > 0" class="text-xs text-emerald-600 dark:text-emerald-400">
                                                    Terbayar: {{ formatCurrency(bill.nominal_terbayar) }}
                                                </p>
                                            </div>
                                            <ChevronRight class="w-5 h-5 text-slate-400 group-hover:text-violet-500 transition-colors" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div v-else class="p-12 text-center">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                    <CheckCircle2 class="w-8 h-8 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">Tidak Ada Tagihan</h3>
                                <p class="text-slate-500 dark:text-slate-400">
                                    {{ selectedChildId ? 'Semua tagihan untuk anak ini sudah lunas!' : 'Semua tagihan sudah lunas!' }}
                                </p>
                            </div>
                        </div>

                        <!-- Tab Content: History -->
                        <div v-if="activeTab === 'history'" class="divide-y divide-slate-200 dark:divide-zinc-800">
                            <template v-if="hasPaidBills">
                                <div
                                    v-for="(bill, idx) in filteredPaidBills"
                                    :key="bill.id"
                                    @click="openBillDetail(bill)"
                                    class="p-4 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors cursor-pointer group"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p class="font-semibold text-slate-900 dark:text-slate-100 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                                                    {{ bill.category.nama }}
                                                </p>
                                                <span
                                                    :class="[
                                                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium',
                                                        getStatusBadgeClass(bill.status, false)
                                                    ]"
                                                >
                                                    <CheckCircle2 class="w-3 h-3" />
                                                    {{ bill.status_label }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                                {{ bill.periode }} • {{ bill.student.nama_lengkap }}
                                            </p>
                                        </div>
                                        <div class="text-right shrink-0 flex items-center gap-2">
                                            <p class="font-bold text-slate-900 dark:text-slate-100">
                                                {{ bill.formatted_nominal }}
                                            </p>
                                            <ChevronRight class="w-5 h-5 text-slate-400 group-hover:text-violet-500 transition-colors" />
                                        </div>
                                    </div>
                                </div>
                                <!-- Link to Full History -->
                                <div class="p-4 bg-slate-50 dark:bg-zinc-800/50">
                                    <Link
                                        :href="paymentsHistory().url"
                                        class="flex items-center justify-center gap-2 text-sm font-medium text-violet-600 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300 transition-colors"
                                        @click="haptics.light()"
                                    >
                                        <History class="w-4 h-4" />
                                        Lihat Semua Riwayat Pembayaran
                                        <ChevronRight class="w-4 h-4" />
                                    </Link>
                                </div>
                            </template>
                            <div v-else class="p-12 text-center">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                                    <FileText class="w-8 h-8 text-slate-400" />
                                </div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">Belum Ada Riwayat</h3>
                                <p class="text-slate-500 dark:text-slate-400">
                                    {{ selectedChildId ? 'Riwayat pembayaran untuk anak ini akan muncul di sini' : 'Riwayat pembayaran akan muncul di sini' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Info Card -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
                >
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-4 border border-blue-200 dark:border-blue-800">
                        <div class="flex items-start gap-3">
                            <AlertCircle class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" />
                            <div class="text-sm text-blue-700 dark:text-blue-300">
                                <p class="font-medium">Cara Pembayaran</p>
                                <p class="mt-1">Pilih tagihan yang ingin dibayar dengan mencentang checkbox, lalu klik "Bayar Sekarang" untuk upload bukti transfer. Pembayaran akan diverifikasi oleh Admin.</p>
                            </div>
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

            <!-- Spacer for floating button -->
            <div v-if="hasSelection" class="h-24"></div>
        </div>

        <!-- Floating Action Button for Payment -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0 translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-4"
            >
                <div
                    v-if="hasSelection"
                    class="fixed bottom-0 left-0 right-0 z-40 p-4 bg-linear-to-t from-white via-white to-transparent dark:from-zinc-900 dark:via-zinc-900 pb-safe"
                >
                    <div class="max-w-4xl mx-auto">
                        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-slate-200 dark:border-zinc-700 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ selectedCount }} tagihan dipilih
                                    </p>
                                    <p class="text-xl font-bold text-slate-900 dark:text-slate-100 truncate">
                                        {{ formattedSelectedTotal }}
                                    </p>
                                </div>
                                <button
                                    @click="proceedToPayment"
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-violet-500 text-white rounded-xl font-semibold hover:bg-violet-600 transition-colors shadow-lg shadow-violet-500/25 shrink-0"
                                >
                                    <Wallet class="w-5 h-5" />
                                    Bayar Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Bill Detail Modal -->
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
                        v-if="showBillDetailModal && selectedBill"
                        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
                    >
                        <!-- Backdrop -->
                        <div
                            class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                            @click="closeBillDetail"
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
                                    <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                                        <Receipt class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Detail Tagihan</h2>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ selectedBill.nomor_tagihan }}</p>
                                    </div>
                                </div>
                                <button
                                    @click="closeBillDetail"
                                    class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                                >
                                    <X class="w-5 h-5" />
                                </button>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
                                <!-- Status Badge -->
                                <div class="flex items-center justify-center gap-2 flex-wrap">
                                    <!-- Pending Payment Badge -->
                                    <span
                                        v-if="selectedBill.has_pending_payment"
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400"
                                    >
                                        <Clock class="w-4 h-4" />
                                        Menunggu Verifikasi
                                    </span>
                                    <!-- Status Badge -->
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold',
                                            getStatusBadgeClass(selectedBill.status, selectedBill.is_overdue)
                                        ]"
                                    >
                                        <component :is="getStatusIcon(selectedBill.status, selectedBill.is_overdue)" class="w-4 h-4" />
                                        {{ getStatusLabel(selectedBill.status, selectedBill.is_overdue) }}
                                    </span>
                                </div>

                                <!-- Amount Card -->
                                <div class="bg-linear-to-br from-violet-500 to-purple-600 rounded-2xl p-5 text-white text-center">
                                    <p class="text-violet-100 text-sm">Sisa Tagihan</p>
                                    <p class="text-3xl font-bold mt-1">{{ selectedBill.formatted_sisa }}</p>
                                    <p v-if="selectedBill.nominal_terbayar > 0" class="text-violet-200 text-sm mt-2">
                                        Terbayar: {{ formatCurrency(selectedBill.nominal_terbayar) }} dari {{ selectedBill.formatted_nominal }}
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
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Kategori</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ selectedBill.category.nama }}</p>
                                        </div>
                                    </div>

                                    <!-- Period -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                                            <Calendar class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Periode</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ selectedBill.periode }}</p>
                                        </div>
                                    </div>

                                    <!-- Student -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                            <User class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Siswa</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ selectedBill.student.nama_lengkap }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">NIS: {{ selectedBill.student.nis }} • {{ selectedBill.student.kelas }}</p>
                                        </div>
                                    </div>

                                    <!-- Due Date -->
                                    <div class="flex items-center gap-3">
                                        <div
                                            :class="[
                                                'w-9 h-9 rounded-lg flex items-center justify-center shrink-0',
                                                selectedBill.is_overdue && selectedBill.status !== 'lunas'
                                                    ? 'bg-red-100 dark:bg-red-900/30'
                                                    : 'bg-amber-100 dark:bg-amber-900/30'
                                            ]"
                                        >
                                            <AlertTriangle
                                                v-if="selectedBill.is_overdue && selectedBill.status !== 'lunas'"
                                                class="w-4 h-4 text-red-600 dark:text-red-400"
                                            />
                                            <Clock v-else class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Jatuh Tempo</p>
                                            <p
                                                :class="[
                                                    'font-medium',
                                                    selectedBill.is_overdue && selectedBill.status !== 'lunas'
                                                        ? 'text-red-600 dark:text-red-400'
                                                        : 'text-slate-900 dark:text-slate-100'
                                                ]"
                                            >
                                                {{ selectedBill.formatted_due_date }}
                                                <span
                                                    v-if="selectedBill.is_overdue && selectedBill.status !== 'lunas'"
                                                    class="text-xs font-normal"
                                                >
                                                    (Terlambat)
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Bill Number -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-slate-200 dark:bg-zinc-700 flex items-center justify-center shrink-0">
                                            <Hash class="w-4 h-4 text-slate-600 dark:text-slate-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-500 dark:text-slate-400">No. Tagihan</p>
                                            <p class="font-medium text-slate-900 dark:text-slate-100 font-mono text-sm">{{ selectedBill.nomor_tagihan }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pending Payment Info -->
                                <div
                                    v-if="selectedBill.has_pending_payment"
                                    class="bg-orange-50 dark:bg-orange-900/20 rounded-2xl p-4 border border-orange-200 dark:border-orange-800"
                                >
                                    <div class="flex items-start gap-3">
                                        <Clock class="w-5 h-5 text-orange-600 dark:text-orange-400 shrink-0 mt-0.5" />
                                        <div class="text-sm text-orange-700 dark:text-orange-300">
                                            <p class="font-medium">Menunggu Verifikasi</p>
                                            <p class="mt-1">Pembayaran Anda sudah kami terima dan sedang dalam proses verifikasi oleh Admin. Mohon tunggu 1x24 jam untuk konfirmasi.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Info (only show if no pending payment) -->
                                <div
                                    v-else
                                    class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-4 border border-blue-200 dark:border-blue-800"
                                >
                                    <div class="flex items-start gap-3">
                                        <Building2 class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" />
                                        <div class="text-sm text-blue-700 dark:text-blue-300">
                                            <p class="font-medium">Cara Pembayaran</p>
                                            <p class="mt-1">Silakan kunjungi kantor administrasi sekolah atau transfer ke rekening resmi sekolah. Simpan bukti pembayaran untuk verifikasi.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="p-4 sm:p-6 border-t border-slate-200 dark:border-zinc-800 shrink-0">
                                <button
                                    @click="closeBillDetail"
                                    class="w-full px-6 py-3 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                                >
                                    Tutup
                                </button>
                            </div>
                        </Motion>
                    </div>
                </Transition>
        </Teleport>
    </AppLayout>
</template>
