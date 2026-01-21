<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { Users, CreditCard, GraduationCap, FileText, ChevronRight, AlertTriangle } from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import ChildAttendanceSummaryWidget from '@/components/dashboard/ChildAttendanceSummaryWidget.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import { index as parentChildrenIndex } from '@/routes/parent/children';
import { index as parentLeaveRequestsIndex } from '@/routes/parent/leave-requests';
import { index as parentPaymentsIndex } from '@/routes/parent/payments';

/**
 * Dashboard untuk Orang Tua dengan informasi anak,
 * pembayaran, nilai, dan kehadiran
 * dengan iOS-like staggered animations dan haptic feedback
 *
 * UX Enhancement:
 * - Removed duplicate header (using AppLayout greeting)
 * - Added focus states untuk accessibility
 * - "Segera Hadir" badges untuk fitur yang belum tersedia
 * - Consistent icon styling dan backgrounds
 * - ChildAttendanceSummaryWidget untuk ringkasan kehadiran
 */

interface AttendanceSummary {
    hadir: number;
    sakit: number;
    izin: number;
    alpha: number;
    total: number;
    percentage: number;
}

interface ChildWithAttendance {
    id: number;
    nama_lengkap: string;
    nama_panggilan?: string;
    kelas?: {
        id: number;
        nama_lengkap: string;
    };
    attendance_summary: AttendanceSummary;
}

interface NearestBill {
    id: number;
    category: string;
    periode: string;
    student_name: string;
    amount: number;
    formatted_amount: string;
    due_date: string;
    is_overdue: boolean;
}

interface PaymentSummary {
    total_unpaid: number;
    total_tunggakan: number;
    formatted_tunggakan: string;
    total_overdue: number;
    nearest_bill: NearestBill | null;
}

interface Props {
    stats: {
        children: any[];
        pending_payments: number;
        recent_grades: any[];
        attendance_summary: any[];
    };
    childrenWithAttendance: ChildWithAttendance[];
    pendingLeaveRequests: number;
    paymentSummary: PaymentSummary;
}

defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

const handleCardClick = () => haptics.light();

/**
 * Show coming soon modal untuk fitur yang belum tersedia
 */
const showComingSoon = (featureName: string) => {
    haptics.light();
    modal.info('Segera Hadir', `Fitur ${featureName} akan segera tersedia dalam pembaruan berikutnya.`);
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Orang Tua" />

        <div class="space-y-6">
            <!-- Quick Stats Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Jumlah Anak Card -->
                <Motion
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <Link
                        :href="parentChildrenIndex().url"
                        @click="handleCardClick"
                        class="block overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800 hover:border-blue-200 dark:hover:border-blue-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    >
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                        Jumlah Anak
                                    </p>
                                    <p class="mt-1.5 text-3xl font-bold text-slate-900 dark:text-white tabular-nums">
                                        {{ stats.children.length }}
                                    </p>
                                </div>
                                <div class="p-3 bg-blue-100 dark:bg-blue-500/20 rounded-xl">
                                    <Users :size="24" class="text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-center text-xs text-slate-500 dark:text-slate-400">
                                <span>Lihat data anak</span>
                                <ChevronRight :size="14" class="ml-1" />
                            </div>
                        </div>
                    </Link>
                </Motion>

                <!-- Tagihan Pending Card -->
                <Motion
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <Link
                        :href="parentPaymentsIndex().url"
                        @click="handleCardClick"
                        class="block overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                        :class="paymentSummary.total_overdue > 0 ? 'hover:border-red-200 dark:hover:border-red-700 border-red-100 dark:border-red-900/50' : 'hover:border-amber-200 dark:hover:border-amber-700'"
                    >
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                        Tagihan Belum Bayar
                                    </p>
                                    <p class="mt-1.5 text-3xl font-bold tabular-nums" :class="paymentSummary.total_overdue > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white'">
                                        {{ paymentSummary.total_unpaid }}
                                    </p>
                                </div>
                                <div class="p-3 rounded-xl" :class="paymentSummary.total_overdue > 0 ? 'bg-red-100 dark:bg-red-500/20' : 'bg-amber-100 dark:bg-amber-500/20'">
                                    <CreditCard :size="24" :class="paymentSummary.total_overdue > 0 ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400'" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-center text-xs text-slate-500 dark:text-slate-400">
                                <template v-if="paymentSummary.total_overdue > 0">
                                    <AlertTriangle :size="14" class="mr-1 text-red-500" />
                                    <span class="text-red-600 dark:text-red-400">{{ paymentSummary.total_overdue }} jatuh tempo</span>
                                </template>
                                <template v-else-if="paymentSummary.total_unpaid > 0">
                                    <span>{{ paymentSummary.formatted_tunggakan }}</span>
                                </template>
                                <template v-else>
                                    <span class="text-emerald-600 dark:text-emerald-400">Semua lunas</span>
                                </template>
                                <ChevronRight :size="14" class="ml-1" />
                            </div>
                        </div>
                    </Link>
                </Motion>

                <!-- Nilai Terbaru Card (Coming Soon) -->
                <Motion
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <button
                        @click="showComingSoon('Nilai')"
                        class="w-full text-left overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800 hover:border-green-200 dark:hover:border-green-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    >
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                        Nilai Terbaru
                                    </p>
                                    <p class="mt-1.5 text-3xl font-bold text-slate-900 dark:text-white tabular-nums">
                                        {{ stats.recent_grades.length }}
                                    </p>
                                </div>
                                <div class="p-3 bg-green-100 dark:bg-green-500/20 rounded-xl">
                                    <GraduationCap :size="24" class="text-green-600 dark:text-green-400" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-center text-xs">
                                <span class="text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-0.5 rounded-full">
                                    Segera Hadir
                                </span>
                            </div>
                        </div>
                    </button>
                </Motion>

                <!-- Pengajuan Izin Card -->
                <Motion
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <Link
                        :href="parentLeaveRequestsIndex().url"
                        @click="handleCardClick"
                        class="block overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800 hover:border-purple-200 dark:hover:border-purple-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    >
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                        Pengajuan Izin
                                    </p>
                                    <p class="mt-1.5 text-3xl font-bold text-slate-900 dark:text-white tabular-nums">
                                        {{ pendingLeaveRequests }}
                                    </p>
                                </div>
                                <div class="p-3 bg-purple-100 dark:bg-purple-500/20 rounded-xl">
                                    <FileText :size="24" class="text-purple-600 dark:text-purple-400" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-center text-xs text-slate-500 dark:text-slate-400">
                                <span>{{ pendingLeaveRequests > 0 ? 'Pending diproses' : 'Buat pengajuan baru' }}</span>
                                <ChevronRight :size="14" class="ml-1" />
                            </div>
                        </div>
                    </Link>
                </Motion>
            </div>

            <!-- SPP Status Widget - Show nearest bill if exists -->
            <Motion
                v-if="paymentSummary.nearest_bill"
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
            >
                <Link
                    :href="parentPaymentsIndex().url"
                    @click="handleCardClick"
                    class="block bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border overflow-hidden transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    :class="paymentSummary.nearest_bill.is_overdue ? 'border-red-200 dark:border-red-800 hover:border-red-300 dark:hover:border-red-700' : 'border-slate-200 dark:border-zinc-800 hover:border-amber-200 dark:hover:border-amber-700'"
                >
                    <div class="p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2.5 rounded-xl" :class="paymentSummary.nearest_bill.is_overdue ? 'bg-red-100 dark:bg-red-900/30' : 'bg-amber-100 dark:bg-amber-900/30'">
                                <AlertTriangle v-if="paymentSummary.nearest_bill.is_overdue" :size="20" class="text-red-600 dark:text-red-400" />
                                <CreditCard v-else :size="20" class="text-amber-600 dark:text-amber-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-slate-900 dark:text-white">
                                    {{ paymentSummary.nearest_bill.is_overdue ? 'Tagihan Jatuh Tempo' : 'Tagihan Terdekat' }}
                                </h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ paymentSummary.nearest_bill.student_name }}
                                </p>
                            </div>
                            <ChevronRight :size="20" class="text-slate-400 shrink-0" />
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl" :class="paymentSummary.nearest_bill.is_overdue ? 'bg-red-50 dark:bg-red-900/20' : 'bg-amber-50 dark:bg-amber-900/20'">
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">
                                    {{ paymentSummary.nearest_bill.category }}
                                </p>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ paymentSummary.nearest_bill.periode }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold" :class="paymentSummary.nearest_bill.is_overdue ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400'">
                                    {{ paymentSummary.nearest_bill.formatted_amount }}
                                </p>
                                <p class="text-xs" :class="paymentSummary.nearest_bill.is_overdue ? 'text-red-500' : 'text-slate-500 dark:text-slate-400'">
                                    {{ paymentSummary.nearest_bill.is_overdue ? 'Lewat jatuh tempo' : `Jatuh tempo: ${paymentSummary.nearest_bill.due_date}` }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Link>
            </Motion>

            <!-- Attendance Summary Widget Section -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: paymentSummary.nearest_bill ? 0.3 : 0.25 }"
            >
                <ChildAttendanceSummaryWidget
                    :children="childrenWithAttendance"
                    :warning-threshold="80"
                />
            </Motion>
        </div>
    </AppLayout>
</template>
