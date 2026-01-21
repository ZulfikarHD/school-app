<script setup lang="ts">
/**
 * Bank Reconciliation Match Page - Halaman matching transaksi bank
 *
 * Menampilkan detail rekonsiliasi dengan fitur auto-matching,
 * manual matching, dan verifikasi
 */
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import Badge from '@/components/ui/Badge.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import {
    ChevronLeft, Zap, Link2, Unlink, CheckCircle2, Download,
    Search, ArrowRight, FileSpreadsheet
} from 'lucide-vue-next';
import { index, autoMatch, unmatch, verify, exportMethod } from '@/routes/admin/payments/reconciliation';
import { store as storeMatch } from '@/routes/admin/payments/reconciliation/match';
import { Motion } from 'motion-v';

interface ReconciliationItem {
    id: number;
    transaction_date: string;
    description: string | null;
    amount: number;
    formatted_amount: string;
    transaction_type: 'credit' | 'debit';
    transaction_type_label: string;
    reference: string | null;
    match_type: 'auto' | 'manual' | 'unmatched';
    match_type_label: string;
    match_confidence: number | null;
    matched_at: string | null;
    payment: {
        id: number;
        nomor_kwitansi: string;
        student_name: string;
        nominal: number;
        formatted_nominal: string;
        status: string;
    } | null;
}

interface UnmatchedPayment {
    id: number;
    nomor_kwitansi: string;
    student_name: string;
    student_nis: string;
    kelas: string;
    category: string;
    periode: string;
    nominal: number;
    formatted_nominal: string;
    tanggal_bayar: string;
    status: string;
}

interface Reconciliation {
    id: number;
    filename: string;
    bank_name: string | null;
    statement_date: string | null;
    total_transactions: number;
    total_amount: number;
    formatted_total_amount: string;
    matched_count: number;
    matched_amount: number;
    formatted_matched_amount: string;
    unmatched_count: number;
    match_rate: number;
    status: string;
    status_label: string;
    uploader: { id: number; name: string } | null;
    verified_at: string | null;
    created_at: string;
}

interface Props {
    reconciliation: Reconciliation;
    items: ReconciliationItem[];
    unmatchedPayments: UnmatchedPayment[];
    canVerify: boolean;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

// State
const activeTab = ref<'all' | 'matched' | 'unmatched'>('all');
const showMatchModal = ref(false);
const selectedItem = ref<ReconciliationItem | null>(null);
const searchQuery = ref('');

const matchForm = useForm({
    item_id: 0,
    payment_id: 0,
});

// Computed
const filteredItems = computed(() => {
    let items = props.items;

    if (activeTab.value === 'matched') {
        items = items.filter(item => item.match_type !== 'unmatched');
    } else if (activeTab.value === 'unmatched') {
        items = items.filter(item => item.match_type === 'unmatched');
    }

    return items;
});

const filteredPayments = computed(() => {
    if (!searchQuery.value) return props.unmatchedPayments;

    const query = searchQuery.value.toLowerCase();
    return props.unmatchedPayments.filter(payment =>
        payment.student_name.toLowerCase().includes(query) ||
        payment.nomor_kwitansi.toLowerCase().includes(query) ||
        payment.student_nis.toLowerCase().includes(query)
    );
});

const unmatchedItemsCount = computed(() =>
    props.items.filter(item => item.match_type === 'unmatched').length
);

const matchedItemsCount = computed(() =>
    props.items.filter(item => item.match_type !== 'unmatched').length
);

// Methods
const handleAutoMatch = async () => {
    const confirmed = await modal.confirm(
        'Auto Matching',
        'Jalankan auto-matching untuk menemukan pembayaran yang sesuai secara otomatis?',
        'Ya, Jalankan',
        'Batal'
    );

    if (confirmed) {
        haptics.heavy();
        router.post(autoMatch(props.reconciliation.id).url, {}, {
            preserveScroll: true,
            onSuccess: () => haptics.success(),
            onError: () => haptics.error(),
        });
    }
};

const openMatchModal = (item: ReconciliationItem) => {
    haptics.light();
    selectedItem.value = item;
    matchForm.item_id = item.id;
    matchForm.payment_id = 0;
    searchQuery.value = '';
    showMatchModal.value = true;
};

const selectPayment = (payment: UnmatchedPayment) => {
    haptics.selection();
    matchForm.payment_id = payment.id;
};

const handleManualMatch = () => {
    if (!matchForm.payment_id) {
        modal.error('Pilih pembayaran terlebih dahulu');
        return;
    }

    haptics.medium();
    matchForm.post(storeMatch(props.reconciliation.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            showMatchModal.value = false;
        },
        onError: () => {
            haptics.error();
        },
    });
};

const handleUnmatch = async (item: ReconciliationItem) => {
    const confirmed = await modal.confirm(
        'Batalkan Match',
        `Batalkan match untuk transaksi ${item.formatted_amount}?`,
        'Ya, Batalkan',
        'Tidak'
    );

    if (confirmed) {
        haptics.heavy();
        router.post(unmatch(props.reconciliation.id, item.id).url, {}, {
            preserveScroll: true,
            onSuccess: () => haptics.success(),
            onError: () => haptics.error(),
        });
    }
};

const handleVerify = async () => {
    const confirmed = await modal.confirm(
        'Verifikasi Rekonsiliasi',
        'Verifikasi rekonsiliasi ini? Pembayaran yang ter-match dan masih pending akan diverifikasi secara otomatis.',
        'Ya, Verifikasi',
        'Batal'
    );

    if (confirmed) {
        haptics.heavy();
        router.post(verify(props.reconciliation.id).url, {}, {
            onSuccess: () => haptics.success(),
            onError: () => haptics.error(),
        });
    }
};

const handleExport = () => {
    haptics.medium();
    window.open(exportMethod(props.reconciliation.id).url, '_blank');
};

const getMatchBadgeVariant = (matchType: string): 'default' | 'success' | 'warning' => {
    return {
        auto: 'success' as const,
        manual: 'success' as const,
        unmatched: 'warning' as const,
    }[matchType] || 'default';
};
</script>

<template>
    <AppLayout title="Detail Rekonsiliasi">
        <Head :title="`Rekonsiliasi - ${reconciliation.filename}`" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 sm:gap-4 mb-4">
                        <Motion :whileTap="{ scale: 0.95 }">
                            <Link
                                :href="index()"
                                @click="haptics.light()"
                                class="w-11 h-11 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-colors shrink-0"
                            >
                                <ChevronLeft class="w-5 h-5" />
                            </Link>
                        </Motion>
                        <div class="flex-1 min-w-0">
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 truncate">
                                {{ reconciliation.filename }}
                            </h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ reconciliation.bank_name || 'Bank tidak diketahui' }} • {{ reconciliation.created_at }}
                            </p>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-slate-50 dark:bg-zinc-800 rounded-xl p-4">
                            <p class="text-sm text-slate-500 dark:text-slate-400">Total Transaksi</p>
                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                {{ reconciliation.total_transactions }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ reconciliation.formatted_total_amount }}
                            </p>
                        </div>
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4">
                            <p class="text-sm text-emerald-600 dark:text-emerald-400">Matched</p>
                            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">
                                {{ reconciliation.matched_count }}
                            </p>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400">
                                {{ reconciliation.formatted_matched_amount }}
                            </p>
                        </div>
                        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4">
                            <p class="text-sm text-amber-600 dark:text-amber-400">Unmatched</p>
                            <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">
                                {{ reconciliation.unmatched_count }}
                            </p>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4">
                            <p class="text-sm text-blue-600 dark:text-blue-400">Match Rate</p>
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                                {{ reconciliation.match_rate }}%
                            </p>
                            <div class="w-full h-2 bg-blue-200 dark:bg-blue-800 rounded-full mt-2 overflow-hidden">
                                <div
                                    class="h-full bg-blue-500 rounded-full transition-all"
                                    :style="{ width: `${reconciliation.match_rate}%` }"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3 mt-4 pt-4 border-t border-slate-200 dark:border-zinc-700">
                        <Motion :whileTap="{ scale: 0.97 }">
                            <button
                                @click="handleAutoMatch"
                                :disabled="unmatchedItemsCount === 0"
                                class="flex items-center gap-2 px-4 py-2.5 bg-teal-500 text-white rounded-xl hover:bg-teal-600 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <Zap class="w-4 h-4" />
                                Auto Match
                            </button>
                        </Motion>
                        <Motion :whileTap="{ scale: 0.97 }">
                            <button
                                @click="handleExport"
                                class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors font-medium"
                            >
                                <Download class="w-4 h-4" />
                                Export
                            </button>
                        </Motion>
                        <Motion v-if="canVerify" :whileTap="{ scale: 0.97 }">
                            <button
                                @click="handleVerify"
                                class="flex items-center gap-2 px-4 py-2.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors font-medium shadow-lg shadow-emerald-500/25"
                            >
                                <CheckCircle2 class="w-4 h-4" />
                                Verifikasi Rekonsiliasi
                            </button>
                        </Motion>
                    </div>
                </div>
            </Motion>

            <!-- Tabs -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-xl p-1 shadow-sm border border-slate-200 dark:border-zinc-800 flex gap-1">
                    <button
                        @click="activeTab = 'all'; haptics.selection()"
                        :class="[
                            'flex-1 px-4 py-2.5 rounded-lg font-medium text-sm transition-colors',
                            activeTab === 'all'
                                ? 'bg-emerald-500 text-white'
                                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800'
                        ]"
                    >
                        Semua ({{ items.length }})
                    </button>
                    <button
                        @click="activeTab = 'matched'; haptics.selection()"
                        :class="[
                            'flex-1 px-4 py-2.5 rounded-lg font-medium text-sm transition-colors',
                            activeTab === 'matched'
                                ? 'bg-emerald-500 text-white'
                                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800'
                        ]"
                    >
                        Matched ({{ matchedItemsCount }})
                    </button>
                    <button
                        @click="activeTab = 'unmatched'; haptics.selection()"
                        :class="[
                            'flex-1 px-4 py-2.5 rounded-lg font-medium text-sm transition-colors',
                            activeTab === 'unmatched'
                                ? 'bg-emerald-500 text-white'
                                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800'
                        ]"
                    >
                        Unmatched ({{ unmatchedItemsCount }})
                    </button>
                </div>
            </Motion>

            <!-- Items List -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <div v-if="filteredItems.length === 0" class="p-12 text-center">
                        <FileSpreadsheet class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-4" />
                        <p class="text-slate-500 dark:text-slate-400">Tidak ada transaksi</p>
                    </div>

                    <div v-else class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <div
                            v-for="item in filteredItems"
                            :key="item.id"
                            class="p-4 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-lg font-bold text-slate-900 dark:text-slate-100">
                                            {{ item.formatted_amount }}
                                        </span>
                                        <Badge :variant="getMatchBadgeVariant(item.match_type)" size="sm">
                                            {{ item.match_type_label }}
                                        </Badge>
                                        <span v-if="item.match_confidence" class="text-xs text-slate-500">
                                            ({{ item.match_confidence }}% confidence)
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-600 dark:text-slate-400 truncate">
                                        {{ item.description || 'Tidak ada deskripsi' }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        {{ item.transaction_date }}
                                        <span v-if="item.reference"> • Ref: {{ item.reference }}</span>
                                    </p>

                                    <!-- Matched Payment Info -->
                                    <div v-if="item.payment" class="mt-3 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                                        <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                                            Matched dengan: {{ item.payment.nomor_kwitansi }}
                                        </p>
                                        <p class="text-xs text-emerald-600 dark:text-emerald-400">
                                            {{ item.payment.student_name }} • {{ item.payment.formatted_nominal }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2 shrink-0">
                                    <Motion v-if="item.match_type === 'unmatched'" :whileTap="{ scale: 0.95 }">
                                        <button
                                            @click="openMatchModal(item)"
                                            class="flex items-center gap-1.5 px-3 py-2 bg-teal-50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400 rounded-lg hover:bg-teal-100 dark:hover:bg-teal-900/30 transition-colors text-sm font-medium"
                                        >
                                            <Link2 class="w-4 h-4" />
                                            Match
                                        </button>
                                    </Motion>
                                    <Motion v-else :whileTap="{ scale: 0.95 }">
                                        <button
                                            @click="handleUnmatch(item)"
                                            class="flex items-center gap-1.5 px-3 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors text-sm font-medium"
                                        >
                                            <Unlink class="w-4 h-4" />
                                            Unmatch
                                        </button>
                                    </Motion>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>
        </div>

        <!-- Manual Match Modal -->
        <BaseModal
            :show="showMatchModal"
            title="Manual Match"
            size="lg"
            @close="showMatchModal = false"
        >
            <div class="p-6 space-y-4">
                <!-- Selected Transaction -->
                <div v-if="selectedItem" class="bg-slate-50 dark:bg-zinc-800 rounded-xl p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Transaksi Bank</p>
                    <p class="text-lg font-bold text-slate-900 dark:text-slate-100">
                        {{ selectedItem.formatted_amount }}
                    </p>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        {{ selectedItem.description || 'Tidak ada deskripsi' }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        {{ selectedItem.transaction_date }}
                    </p>
                </div>

                <div class="flex items-center justify-center">
                    <div class="w-10 h-10 rounded-full bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center">
                        <ArrowRight class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                    </div>
                </div>

                <!-- Search -->
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama siswa atau no. kwitansi..."
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                    />
                </div>

                <!-- Payment List -->
                <div class="max-h-[300px] overflow-y-auto space-y-2">
                    <div v-if="filteredPayments.length === 0" class="p-8 text-center text-slate-500">
                        Tidak ada pembayaran yang tersedia
                    </div>
                    <div
                        v-for="payment in filteredPayments"
                        :key="payment.id"
                        @click="selectPayment(payment)"
                        :class="[
                            'p-4 rounded-xl border-2 cursor-pointer transition-all',
                            matchForm.payment_id === payment.id
                                ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/20'
                                : 'border-slate-200 dark:border-zinc-700 hover:border-slate-300 dark:hover:border-zinc-600'
                        ]"
                    >
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-slate-900 dark:text-slate-100">
                                    {{ payment.student_name }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ payment.nomor_kwitansi }} • {{ payment.student_nis }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    {{ payment.category }} - {{ payment.periode }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-slate-900 dark:text-slate-100">
                                    {{ payment.formatted_nominal }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ payment.tanggal_bayar }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-zinc-800 flex gap-3">
                <button
                    @click="showMatchModal = false"
                    class="flex-1 px-4 py-2.5 text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-zinc-800 rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors font-medium"
                >
                    Batal
                </button>
                <button
                    @click="handleManualMatch"
                    :disabled="matchForm.processing || !matchForm.payment_id"
                    class="flex-1 px-4 py-2.5 bg-teal-500 text-white rounded-xl hover:bg-teal-600 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                    <Link2 class="w-4 h-4" />
                    Match
                </button>
            </div>
        </BaseModal>
    </AppLayout>
</template>
