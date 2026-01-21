<script setup lang="ts">
/**
 * Parent Payments Index Page - Halaman tagihan untuk orang tua
 * Menampilkan summary pembayaran, tagihan aktif, dan riwayat pembayaran
 */
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import {
    CreditCard, Clock, AlertCircle, CheckCircle2, Calendar, AlertTriangle,
    Receipt, Wallet, FileText, ChevronRight
} from 'lucide-vue-next';
import { Motion } from 'motion-v';

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
    tanggal_jatuh_tempo: string;
    formatted_due_date: string;
}

interface Summary {
    total_tunggakan: number;
    formatted_tunggakan: string;
    total_belum_bayar: number;
    total_sebagian: number;
    total_overdue: number;
    total_lunas_bulan_ini: number;
    nearest_due_date: string | null;
    nearest_bill: {
        category: string;
        periode: string;
        nominal: string;
    } | null;
}

interface Props {
    children: Student[];
    activeBills: Bill[];
    paidBills: Bill[];
    summary: Summary;
    message?: string;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const activeTab = ref<'active' | 'history'>('active');

// Computed
const hasChildren = computed(() => props.children.length > 0);
const hasActiveBills = computed(() => props.activeBills.length > 0);
const hasPaidBills = computed(() => props.paidBills.length > 0);

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
</script>

<template>
    <AppLayout>
        <Head title="Pembayaran" />

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
                                    <p class="text-violet-100 text-sm">Total Tunggakan</p>
                                    <p class="text-3xl font-bold mt-1">{{ summary.formatted_tunggakan }}</p>
                                    <p v-if="summary.total_overdue > 0" class="text-violet-200 text-sm mt-2 flex items-center gap-1">
                                        <AlertTriangle class="w-4 h-4" />
                                        {{ summary.total_overdue }} tagihan jatuh tempo
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
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ summary.total_belum_bayar }}</p>
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
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ summary.total_lunas_bulan_ini }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Lunas Bulan Ini</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Nearest Due Date Alert -->
                <Motion
                    v-if="summary.nearest_bill"
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
                                    {{ summary.nearest_bill.category }} - {{ summary.nearest_bill.periode }}
                                </p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="font-semibold text-amber-800 dark:text-amber-200">{{ summary.nearest_bill.nominal }}</span>
                                    <span class="text-sm text-amber-600 dark:text-amber-400">Jatuh tempo: {{ summary.nearest_due_date }}</span>
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
                                        v-if="activeBills.length > 0"
                                        class="px-1.5 py-0.5 text-xs rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"
                                    >
                                        {{ activeBills.length }}
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

                        <!-- Tab Content: Active Bills -->
                        <div v-if="activeTab === 'active'" class="divide-y divide-slate-200 dark:divide-zinc-800">
                            <template v-if="hasActiveBills">
                                <div
                                    v-for="(bill, idx) in activeBills"
                                    :key="bill.id"
                                    class="p-4 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p class="font-semibold text-slate-900 dark:text-slate-100">
                                                    {{ bill.category.nama }}
                                                </p>
                                                <span
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
                                        <div class="text-right shrink-0">
                                            <p class="font-bold text-slate-900 dark:text-slate-100">
                                                {{ bill.formatted_sisa }}
                                            </p>
                                            <p v-if="bill.nominal_terbayar > 0" class="text-xs text-emerald-600 dark:text-emerald-400">
                                                Terbayar: {{ formatCurrency(bill.nominal_terbayar) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div v-else class="p-12 text-center">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                    <CheckCircle2 class="w-8 h-8 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">Tidak Ada Tagihan</h3>
                                <p class="text-slate-500 dark:text-slate-400">Semua tagihan sudah lunas!</p>
                            </div>
                        </div>

                        <!-- Tab Content: History -->
                        <div v-if="activeTab === 'history'" class="divide-y divide-slate-200 dark:divide-zinc-800">
                            <template v-if="hasPaidBills">
                                <div
                                    v-for="(bill, idx) in paidBills"
                                    :key="bill.id"
                                    class="p-4 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p class="font-semibold text-slate-900 dark:text-slate-100">
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
                                        <div class="text-right shrink-0">
                                            <p class="font-bold text-slate-900 dark:text-slate-100">
                                                {{ bill.formatted_nominal }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div v-else class="p-12 text-center">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                                    <FileText class="w-8 h-8 text-slate-400" />
                                </div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">Belum Ada Riwayat</h3>
                                <p class="text-slate-500 dark:text-slate-400">Riwayat pembayaran akan muncul di sini</p>
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
                                <p class="font-medium">Informasi Pembayaran</p>
                                <p class="mt-1">Untuk melakukan pembayaran, silakan kunjungi kantor administrasi sekolah atau transfer ke rekening sekolah. Hubungi bagian TU untuk informasi lebih lanjut.</p>
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
        </div>
    </AppLayout>
</template>
