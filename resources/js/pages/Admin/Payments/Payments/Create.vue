<script setup lang="ts">
/**
 * Create Payment Page - Halaman untuk mencatat pembayaran baru
 * dengan fitur pencarian siswa, seleksi tagihan, dan konfirmasi
 */
import { ref, computed, watch } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import StudentSearchInput from '@/components/features/payments/StudentSearchInput.vue';
import UnpaidBillsTable from '@/components/features/payments/UnpaidBillsTable.vue';
import {
    ChevronLeft, Receipt, Loader2, Banknote, Calendar, CreditCard,
    FileText, CheckCircle2
} from 'lucide-vue-next';
import { index, store } from '@/routes/admin/payments/records';
import { unpaidBills } from '@/routes/admin/api/students';
import { Motion } from 'motion-v';

interface Student {
    id: number;
    nis: string;
    nisn?: string;
    nama_lengkap: string;
    kelas: string;
    kelas_id: number;
    display_label: string;
    total_tunggakan: number;
    formatted_tunggakan: string;
}

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

interface PaymentMethod {
    value: string;
    label: string;
}

interface Props {
    selectedStudent: Student | null;
    unpaidBills: Bill[];
    paymentMethods: PaymentMethod[];
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

// State
const student = ref<Student | null>(props.selectedStudent);
const bills = ref<Bill[]>(props.unpaidBills);
const selectedBillId = ref<number | null>(null);
const isLoadingBills = ref(false);

// Form
const form = useForm({
    bill_id: null as number | null,
    nominal: '',
    metode_pembayaran: 'tunai',
    tanggal_bayar: new Date().toISOString().split('T')[0],
    keterangan: '',
});

// Computed
const selectedBill = computed(() => {
    if (!selectedBillId.value) return null;
    return bills.value.find(b => b.id === selectedBillId.value) || null;
});

const canSubmit = computed(() => {
    return (
        student.value &&
        selectedBillId.value &&
        form.nominal &&
        Number(form.nominal) > 0 &&
        form.metode_pembayaran &&
        form.tanggal_bayar
    );
});

const nominalError = computed(() => {
    if (!form.nominal) return null;
    const amount = Number(form.nominal);
    if (amount <= 0) return 'Nominal harus lebih dari 0';
    if (selectedBill.value && amount > selectedBill.value.sisa_tagihan) {
        return `Nominal melebihi sisa tagihan (${formatCurrency(selectedBill.value.sisa_tagihan)})`;
    }
    return null;
});

// Watch for student selection changes
watch(student, async (newStudent) => {
    if (newStudent) {
        await loadUnpaidBills(newStudent.id);
    } else {
        bills.value = [];
        selectedBillId.value = null;
        form.bill_id = null;
        form.nominal = '';
    }
});

// Watch for bill selection
watch(selectedBillId, (billId) => {
    form.bill_id = billId;
    if (billId) {
        const bill = bills.value.find(b => b.id === billId);
        if (bill) {
            // Auto-fill nominal with remaining amount
            form.nominal = String(bill.sisa_tagihan);
        }
    } else {
        form.nominal = '';
    }
});

// Methods
const loadUnpaidBills = async (studentId: number) => {
    isLoadingBills.value = true;
    selectedBillId.value = null;
    form.bill_id = null;
    form.nominal = '';

    try {
        const response = await fetch(unpaidBills.url({ student: studentId }), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.ok) {
            const data = await response.json();
            bills.value = data.bills;
        }
    } catch (error) {
        console.error('Failed to load unpaid bills:', error);
        modal.error('Gagal memuat data tagihan');
    } finally {
        isLoadingBills.value = false;
    }
};

const handleStudentSelect = (selectedStudent: Student) => {
    student.value = selectedStudent;
};

const handleStudentClear = () => {
    student.value = null;
};

const handleBillSelect = () => {
    // Auto-select for partial payment option - placeholder for future functionality
};

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const handleSubmit = async () => {
    if (!canSubmit.value || nominalError.value) {
        haptics.error();
        return;
    }

    const confirmed = await modal.confirm(
        'Konfirmasi Pembayaran',
        `Catat pembayaran sebesar ${formatCurrency(Number(form.nominal))} untuk ${student.value?.nama_lengkap}?`,
        'Ya, Simpan',
        'Batal'
    );

    if (confirmed) {
        haptics.heavy();
        form.post(store().url, {
            onSuccess: () => {
                haptics.success();
            },
            onError: (errors) => {
                haptics.error();
                if (errors.error) {
                    modal.error(errors.error);
                }
            },
        });
    }
};

const getPaymentMethodIcon = (method: string) => {
    switch (method) {
        case 'tunai':
            return Banknote;
        case 'transfer':
            return CreditCard;
        case 'qris':
            return CreditCard;
        default:
            return Banknote;
    }
};
</script>

<template>
    <AppLayout title="Catat Pembayaran">
        <Head title="Catat Pembayaran" />

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
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                    <Receipt class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 truncate">
                                    Catat Pembayaran
                                </h1>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                Catat pembayaran manual untuk siswa
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Step 1: Select Student -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 p-4 sm:p-6">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-sm font-bold">1</span>
                            Pilih Siswa
                        </h2>
                        <StudentSearchInput
                            v-model="student"
                            @select="handleStudentSelect"
                            @clear="handleStudentClear"
                            :autofocus="!student"
                        />
                    </div>
                </Motion>

                <!-- Step 2: Select Bill -->
                <Motion
                    v-if="student"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut' }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 p-4 sm:p-6">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-sm font-bold">2</span>
                            Pilih Tagihan
                        </h2>
                        <UnpaidBillsTable
                            :bills="bills"
                            v-model:selectedBillId="selectedBillId"
                            @select="handleBillSelect"
                            :loading="isLoadingBills"
                        />
                    </div>
                </Motion>

                <!-- Step 3: Payment Details -->
                <Motion
                    v-if="selectedBill"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut' }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 p-4 sm:p-6">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-6 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-sm font-bold">3</span>
                            Detail Pembayaran
                        </h2>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <!-- Nominal -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Nominal Pembayaran <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-medium">
                                        Rp
                                    </div>
                                    <input
                                        v-model="form.nominal"
                                        type="number"
                                        min="1"
                                        :max="selectedBill.sisa_tagihan"
                                        class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 text-right font-medium text-lg focus:outline-none focus:bg-white dark:focus:bg-zinc-900 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                                        placeholder="0"
                                    />
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <p v-if="nominalError" class="text-sm text-red-500">{{ nominalError }}</p>
                                    <p v-else-if="form.errors.nominal" class="text-sm text-red-500">{{ form.errors.nominal }}</p>
                                    <p v-else class="text-sm text-slate-500 dark:text-slate-400">
                                        Sisa tagihan: {{ selectedBill.formatted_sisa }}
                                    </p>
                                    <button
                                        type="button"
                                        @click="form.nominal = String(selectedBill.sisa_tagihan)"
                                        class="text-sm text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 font-medium"
                                    >
                                        Bayar Penuh
                                    </button>
                                </div>
                            </div>

                            <!-- Metode Pembayaran -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Metode Pembayaran <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <button
                                        v-for="method in paymentMethods"
                                        :key="method.value"
                                        type="button"
                                        @click="form.metode_pembayaran = method.value; haptics.light()"
                                        :class="[
                                            'flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-medium transition-all active:scale-97',
                                            form.metode_pembayaran === method.value
                                                ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/25'
                                                : 'bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700'
                                        ]"
                                    >
                                        <component :is="getPaymentMethodIcon(method.value)" class="w-4 h-4" />
                                        {{ method.label }}
                                    </button>
                                </div>
                                <p v-if="form.errors.metode_pembayaran" class="mt-1.5 text-sm text-red-500">{{ form.errors.metode_pembayaran }}</p>
                            </div>

                            <!-- Tanggal Bayar -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Tanggal Bayar <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                        <Calendar class="w-5 h-5" />
                                    </div>
                                    <input
                                        v-model="form.tanggal_bayar"
                                        type="date"
                                        :max="new Date().toISOString().split('T')[0]"
                                        class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:bg-white dark:focus:bg-zinc-900 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                                    />
                                </div>
                                <p v-if="form.errors.tanggal_bayar" class="mt-1.5 text-sm text-red-500">{{ form.errors.tanggal_bayar }}</p>
                            </div>

                            <!-- Keterangan -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Keterangan (Opsional)
                                </label>
                                <div class="relative">
                                    <div class="absolute left-4 top-3 text-slate-400">
                                        <FileText class="w-5 h-5" />
                                    </div>
                                    <textarea
                                        v-model="form.keterangan"
                                        rows="2"
                                        class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:bg-white dark:focus:bg-zinc-900 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all resize-none"
                                        placeholder="Catatan tambahan..."
                                    ></textarea>
                                </div>
                                <p v-if="form.errors.keterangan" class="mt-1.5 text-sm text-red-500">{{ form.errors.keterangan }}</p>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Summary & Submit -->
                <Motion
                    v-if="selectedBill"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                >
                    <div class="sticky bottom-0 z-10 -mx-4 sm:mx-0 px-4 sm:px-0 pb-6 pt-4">
                        <div class="bg-white/98 dark:bg-zinc-900/98 border border-slate-200 dark:border-zinc-800 rounded-2xl p-4 sm:p-5 shadow-xl backdrop-blur-sm">
                            <!-- Summary -->
                            <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-200 dark:border-zinc-700">
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ selectedBill.category.nama }} - {{ selectedBill.periode }}
                                    </p>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">
                                        {{ student?.nama_lengkap }} ({{ student?.nis }})
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Total Pembayaran</p>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                        {{ form.nominal ? formatCurrency(Number(form.nominal)) : 'Rp 0' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-3">
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <Link
                                        :href="index()"
                                        @click="haptics.light()"
                                        class="flex-1 sm:flex-none px-5 py-3 min-h-[48px] text-slate-700 bg-slate-50 border border-slate-300 rounded-xl hover:bg-slate-100 dark:bg-zinc-800 dark:border-zinc-700 dark:text-slate-300 dark:hover:bg-zinc-700 transition-colors font-medium text-center flex items-center justify-center
                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500"
                                    >
                                        Batal
                                    </Link>
                                </Motion>
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        type="submit"
                                        :disabled="!canSubmit || !!nominalError || form.processing"
                                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-3 min-h-[48px] bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-lg shadow-emerald-500/25 font-semibold
                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                    >
                                        <Loader2 v-if="form.processing" class="w-5 h-5 animate-spin" />
                                        <CheckCircle2 v-else class="w-5 h-5" />
                                        <span v-if="form.processing">Menyimpan...</span>
                                        <span v-else>Simpan Pembayaran</span>
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </div>
                </Motion>
            </form>
        </div>
    </AppLayout>
</template>
