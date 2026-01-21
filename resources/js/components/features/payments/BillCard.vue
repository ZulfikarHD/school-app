<script setup lang="ts">
/**
 * BillCard - Card component untuk menampilkan tagihan pembayaran
 * dengan layout mobile-friendly dan informasi lengkap
 */
import { computed } from 'vue';
import { Calendar, User, ChevronRight } from 'lucide-vue-next';
import PaymentStatusBadge from './PaymentStatusBadge.vue';

interface Student {
    id: number;
    nama_lengkap: string;
    nis: string;
    kelas: string;
}

interface Category {
    id: number;
    nama: string;
    kode: string;
}

interface Bill {
    id: number;
    nomor_tagihan: string;
    student: Student;
    category: Category;
    bulan: number;
    tahun: number;
    nama_bulan?: string;
    periode: string;
    nominal: number;
    nominal_terbayar: number;
    sisa_tagihan: number;
    formatted_nominal: string;
    formatted_sisa: string;
    status: 'belum_bayar' | 'sebagian' | 'lunas' | 'dibatalkan';
    status_label: string;
    is_overdue: boolean;
    tanggal_jatuh_tempo: string;
    formatted_due_date: string;
}

interface Props {
    bill: Bill;
    showStudent?: boolean;
    clickable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showStudent: true,
    clickable: false
});

const emit = defineEmits<{
    (e: 'click', bill: Bill): void;
}>();

// Computed
const hasPartialPayment = computed(() => {
    return props.bill.nominal_terbayar > 0 && props.bill.status !== 'lunas';
});

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const handleClick = () => {
    if (props.clickable) {
        emit('click', props.bill);
    }
};
</script>

<template>
    <div
        :class="[
            'bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 p-4 transition-all',
            clickable ? 'cursor-pointer hover:shadow-md hover:border-violet-200 dark:hover:border-violet-800' : ''
        ]"
        @click="handleClick"
    >
        <!-- Header: Category & Status -->
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="font-semibold text-slate-900 dark:text-slate-100 truncate">
                        {{ bill.category.nama }}
                    </h3>
                    <PaymentStatusBadge
                        :status="bill.status"
                        :is-overdue="bill.is_overdue"
                        size="sm"
                    />
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ bill.periode }}
                </p>
            </div>
            <ChevronRight
                v-if="clickable"
                class="w-5 h-5 text-slate-400 shrink-0"
            />
        </div>

        <!-- Student Info (optional) -->
        <div v-if="showStudent" class="mt-3 flex items-center gap-2 text-sm">
            <User class="w-4 h-4 text-slate-400" />
            <span class="text-slate-600 dark:text-slate-400">
                {{ bill.student.nama_lengkap }}
            </span>
            <span class="text-slate-400 dark:text-slate-500">•</span>
            <span class="text-slate-500 dark:text-slate-500">
                {{ bill.student.kelas }}
            </span>
        </div>

        <!-- Due Date -->
        <div class="mt-2 flex items-center gap-2 text-sm">
            <Calendar class="w-4 h-4 text-slate-400" />
            <span
                :class="[
                    bill.is_overdue && bill.status !== 'lunas'
                        ? 'text-red-600 dark:text-red-400 font-medium'
                        : 'text-slate-500 dark:text-slate-400'
                ]"
            >
                Jatuh tempo: {{ bill.formatted_due_date }}
            </span>
        </div>

        <!-- Amount -->
        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-zinc-800 flex items-end justify-between">
            <div>
                <p class="text-xs text-slate-400 dark:text-slate-500 uppercase tracking-wide">
                    {{ hasPartialPayment ? 'Sisa Tagihan' : 'Total Tagihan' }}
                </p>
                <p class="text-lg font-bold text-slate-900 dark:text-slate-100 mt-0.5">
                    {{ hasPartialPayment ? bill.formatted_sisa : bill.formatted_nominal }}
                </p>
            </div>
            <div v-if="hasPartialPayment" class="text-right">
                <p class="text-xs text-slate-400 dark:text-slate-500">Terbayar</p>
                <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">
                    {{ formatCurrency(bill.nominal_terbayar) }}
                </p>
            </div>
        </div>

        <!-- Invoice Number -->
        <div class="mt-3 pt-3 border-t border-slate-100 dark:border-zinc-800">
            <code class="text-xs font-mono text-slate-400 dark:text-slate-500">
                {{ bill.nomor_tagihan }}
            </code>
        </div>
    </div>
</template>
