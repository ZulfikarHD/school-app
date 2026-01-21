<script setup lang="ts">
/**
 * UnpaidBillsTable - Tabel tagihan belum lunas dengan fitur seleksi
 * untuk memilih tagihan yang akan dibayar
 */
import { computed } from 'vue';
import { useHaptics } from '@/composables/useHaptics';
import PaymentStatusBadge from './PaymentStatusBadge.vue';
import { Receipt, AlertTriangle, CheckCircle2 } from 'lucide-vue-next';

interface Bill {
    id: number;
    nomor_tagihan: string;
    category: {
        id: number;
        nama: string;
        kode: string;
    };
    bulan: number;
    tahun: number;
    periode: string;
    nominal: number;
    nominal_terbayar: number;
    sisa_tagihan: number;
    formatted_nominal: string;
    formatted_sisa: string;
    status: 'belum_bayar' | 'sebagian';
    status_label: string;
    is_overdue: boolean;
    tanggal_jatuh_tempo: string;
    formatted_due_date: string;
}

interface Props {
    bills: Bill[];
    selectedBillId: number | null;
    disabled?: boolean;
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    loading: false,
});

const emit = defineEmits<{
    'update:selectedBillId': [value: number | null];
    'select': [bill: Bill];
}>();

const haptics = useHaptics();

// Computed
const hasBills = computed(() => props.bills.length > 0);

const selectedBill = computed(() => {
    if (!props.selectedBillId) return null;
    return props.bills.find(b => b.id === props.selectedBillId) || null;
});

// Methods
const selectBill = (bill: Bill) => {
    if (props.disabled) return;

    haptics.light();

    if (props.selectedBillId === bill.id) {
        // Deselect if clicking same bill
        emit('update:selectedBillId', null);
    } else {
        emit('update:selectedBillId', bill.id);
        emit('select', bill);
    }
};

const isSelected = (billId: number): boolean => {
    return props.selectedBillId === billId;
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
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Receipt class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                    <h3 class="font-medium text-slate-900 dark:text-slate-100">
                        Tagihan Belum Lunas
                    </h3>
                </div>
                <span class="text-sm text-slate-500 dark:text-slate-400">
                    {{ bills.length }} tagihan
                </span>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-8 text-center">
            <div class="animate-pulse space-y-3">
                <div class="h-12 bg-slate-100 dark:bg-zinc-800 rounded-lg"></div>
                <div class="h-12 bg-slate-100 dark:bg-zinc-800 rounded-lg"></div>
                <div class="h-12 bg-slate-100 dark:bg-zinc-800 rounded-lg"></div>
            </div>
        </div>

        <!-- Bills List -->
        <div v-else-if="hasBills" class="divide-y divide-slate-200 dark:divide-zinc-800">
            <button
                v-for="bill in bills"
                :key="bill.id"
                type="button"
                @click="selectBill(bill)"
                :disabled="disabled"
                :class="[
                    'w-full p-4 flex items-start gap-3 text-left transition-all',
                    isSelected(bill.id)
                        ? 'bg-emerald-50 dark:bg-emerald-900/20 ring-1 ring-inset ring-emerald-500/50'
                        : 'hover:bg-slate-50 dark:hover:bg-zinc-800/50',
                    disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer active:scale-[0.99]'
                ]"
            >
                <!-- Radio indicator -->
                <div class="mt-0.5 shrink-0">
                    <div
                        :class="[
                            'w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all',
                            isSelected(bill.id)
                                ? 'border-emerald-500 bg-emerald-500'
                                : 'border-slate-300 dark:border-zinc-600'
                        ]"
                    >
                        <CheckCircle2
                            v-if="isSelected(bill.id)"
                            class="w-3 h-3 text-white"
                        />
                    </div>
                </div>

                <!-- Bill Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-medium text-slate-900 dark:text-slate-100">
                                {{ bill.category.nama }}
                            </p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ bill.periode }}
                            </p>
                        </div>
                        <PaymentStatusBadge
                            :status="bill.status"
                            :is-overdue="bill.is_overdue"
                            size="sm"
                        />
                    </div>

                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-sm text-slate-500 dark:text-slate-400">
                            <span v-if="bill.nominal_terbayar > 0" class="text-emerald-600 dark:text-emerald-400">
                                Terbayar: {{ formatCurrency(bill.nominal_terbayar) }} •
                            </span>
                            Jatuh tempo: {{ bill.formatted_due_date }}
                        </div>
                    </div>

                    <div class="mt-2 flex items-center justify-between">
                        <div v-if="bill.is_overdue" class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
                            <AlertTriangle class="w-3 h-3" />
                            <span>Sudah jatuh tempo</span>
                        </div>
                        <div v-else></div>
                        <div class="text-right">
                            <p v-if="bill.nominal_terbayar > 0" class="text-xs text-slate-400 dark:text-slate-500 line-through">
                                {{ bill.formatted_nominal }}
                            </p>
                            <p class="font-bold text-slate-900 dark:text-slate-100">
                                {{ bill.formatted_sisa }}
                            </p>
                        </div>
                    </div>
                </div>
            </button>
        </div>

        <!-- Empty State -->
        <div v-else class="p-8 text-center">
            <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                <CheckCircle2 class="w-7 h-7 text-emerald-600 dark:text-emerald-400" />
            </div>
            <h4 class="font-medium text-slate-900 dark:text-slate-100 mb-1">
                Tidak Ada Tunggakan
            </h4>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Siswa ini tidak memiliki tagihan yang belum lunas
            </p>
        </div>

        <!-- Selected Summary -->
        <div
            v-if="selectedBill"
            class="px-4 py-3 border-t border-slate-200 dark:border-zinc-800 bg-emerald-50 dark:bg-emerald-900/20"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600 dark:text-slate-400">
                    Tagihan dipilih:
                </span>
                <span class="font-bold text-emerald-700 dark:text-emerald-400">
                    {{ selectedBill.formatted_sisa }}
                </span>
            </div>
        </div>
    </div>
</template>
