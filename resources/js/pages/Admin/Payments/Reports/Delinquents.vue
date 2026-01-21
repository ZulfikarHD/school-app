<script setup lang="ts">
/**
 * Admin Delinquent Students Page
 *
 * Menampilkan daftar siswa yang memiliki tunggakan pembayaran
 * dengan total tunggakan, detail tagihan, dan pagination
 */
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    ArrowLeft, Users, AlertTriangle, Wallet,
    ChevronDown, ChevronUp, Calendar, ChevronLeft, ChevronRight
} from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import { index as reportsIndex, delinquents as delinquentsRoute } from '@/routes/admin/payments/reports';

interface BillItem {
    id: number;
    category: string;
    periode: string;
    sisa: string;
    is_overdue: boolean;
}

interface Delinquent {
    student: {
        id: number;
        nama_lengkap: string;
        nis: string;
        kelas: string;
    };
    total_bills: number;
    total_tunggakan: number;
    formatted_tunggakan: string;
    overdue_count: number;
    oldest_due_date: string;
    bills: BillItem[];
}

interface PaginatedDelinquents {
    data: Delinquent[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

interface Props {
    delinquents: PaginatedDelinquents;
    totalStudents: number;
    totalTunggakan: number;
    formattedTotalTunggakan: string;
    filters: {
        sort: string;
        dir: string;
    };
}

const props = defineProps<Props>();

const haptics = useHaptics();

// Computed untuk menghindari konflik nama dengan route
const delinquentsList = computed(() => props.delinquents.data);
const pagination = computed(() => props.delinquents);

// State
const expandedStudent = ref<number | null>(null);
const sortBy = ref(props.filters.sort);
const sortDir = ref(props.filters.dir);

// Methods
const toggleExpand = (studentId: number) => {
    haptics.light();
    expandedStudent.value = expandedStudent.value === studentId ? null : studentId;
};

const changeSort = (field: string) => {
    haptics.light();

    if (sortBy.value === field) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortDir.value = 'desc';
    }

    router.get(delinquentsRoute().url, {
        sort: sortBy.value,
        dir: sortDir.value,
        page: 1, // Reset ke page 1 saat sort berubah
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const goToPage = (page: number) => {
    if (page < 1 || page > pagination.value.last_page) return;

    haptics.light();
    router.get(delinquentsRoute().url, {
        sort: sortBy.value,
        dir: sortDir.value,
        page,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getSortIcon = (field: string) => {
    if (sortBy.value !== field) return null;
    return sortDir.value === 'asc' ? ChevronUp : ChevronDown;
};
</script>

<template>
    <AppLayout>
        <Head title="Siswa Menunggak" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <Link
                            :href="reportsIndex().url"
                            @click="haptics.light()"
                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors shrink-0"
                        >
                            <ArrowLeft class="w-5 h-5" />
                        </Link>
                        <div class="w-12 h-12 rounded-xl bg-linear-to-br from-red-400 to-red-600 flex items-center justify-center shadow-lg shadow-red-500/25 shrink-0">
                            <AlertTriangle class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                Siswa Menunggak
                            </h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                Daftar siswa dengan tagihan belum lunas
                            </p>
                        </div>
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
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                <Users class="w-5 h-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ totalStudents }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Total Siswa</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <Wallet class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ formattedTotalTunggakan }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Total Tunggakan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Student List -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <!-- Sort Header (Desktop) -->
                    <div class="hidden sm:flex items-center px-4 py-3 bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-800 text-sm font-medium text-slate-600 dark:text-slate-400">
                        <button
                            @click="changeSort('nama')"
                            class="flex-1 flex items-center gap-1 hover:text-slate-900 dark:hover:text-slate-200"
                        >
                            Siswa
                            <component v-if="getSortIcon('nama')" :is="getSortIcon('nama')" class="w-4 h-4" />
                        </button>
                        <button
                            @click="changeSort('total_bills')"
                            class="w-24 text-center flex items-center justify-center gap-1 hover:text-slate-900 dark:hover:text-slate-200"
                        >
                            Tagihan
                            <component v-if="getSortIcon('total_bills')" :is="getSortIcon('total_bills')" class="w-4 h-4" />
                        </button>
                        <button
                            @click="changeSort('total_tunggakan')"
                            class="w-36 text-right flex items-center justify-end gap-1 hover:text-slate-900 dark:hover:text-slate-200"
                        >
                            Tunggakan
                            <component v-if="getSortIcon('total_tunggakan')" :is="getSortIcon('total_tunggakan')" class="w-4 h-4" />
                        </button>
                    </div>

                    <div v-if="delinquentsList.length > 0" class="divide-y divide-slate-200 dark:divide-zinc-800">
                        <div
                            v-for="item in delinquentsList"
                            :key="item.student.id"
                        >
                            <!-- Main Row -->
                            <button
                                @click="toggleExpand(item.student.id)"
                                class="w-full flex items-center p-4 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors text-left"
                            >
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">
                                            {{ item.student.nama_lengkap }}
                                        </p>
                                        <span
                                            v-if="item.overdue_count > 0"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400"
                                        >
                                            <AlertTriangle class="w-3 h-3" />
                                            {{ item.overdue_count }} jatuh tempo
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ item.student.nis }} • {{ item.student.kelas }}
                                    </p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1">
                                        <Calendar class="w-3 h-3" />
                                        Jatuh tempo terlama: {{ item.oldest_due_date }}
                                    </p>
                                </div>
                                <div class="hidden sm:block w-24 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-sm font-medium bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300">
                                        {{ item.total_bills }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-red-600 dark:text-red-400">
                                        {{ item.formatted_tunggakan }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 sm:hidden">
                                        {{ item.total_bills }} tagihan
                                    </p>
                                </div>
                                <div class="ml-3">
                                    <component
                                        :is="expandedStudent === item.student.id ? ChevronUp : ChevronDown"
                                        class="w-5 h-5 text-slate-400"
                                    />
                                </div>
                            </button>

                            <!-- Expanded Bills -->
                            <Motion
                                v-if="expandedStudent === item.student.id"
                                :initial="{ opacity: 0, height: 0 }"
                                :animate="{ opacity: 1, height: 'auto' }"
                                :exit="{ opacity: 0, height: 0 }"
                                :transition="{ duration: 0.2 }"
                            >
                                <div class="px-4 pb-4 bg-slate-50 dark:bg-zinc-800/30">
                                    <div class="space-y-2 pt-2">
                                        <div
                                            v-for="bill in item.bills"
                                            :key="bill.id"
                                            class="flex items-center justify-between p-3 bg-white dark:bg-zinc-900 rounded-xl"
                                        >
                                            <div>
                                                <p class="font-medium text-slate-900 dark:text-slate-100">
                                                    {{ bill.category }}
                                                </p>
                                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                                    {{ bill.periode }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-semibold text-slate-900 dark:text-slate-100">
                                                    {{ bill.sisa }}
                                                </p>
                                                <span
                                                    v-if="bill.is_overdue"
                                                    class="text-xs text-red-600 dark:text-red-400"
                                                >
                                                    Jatuh tempo
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Motion>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="p-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <Users class="w-8 h-8 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">
                            Tidak Ada Siswa Menunggak
                        </h3>
                        <p class="text-slate-500 dark:text-slate-400">
                            Semua pembayaran sudah lunas
                        </p>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="pagination.total > 0"
                        class="px-4 py-3 border-t border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50"
                    >
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                            <!-- Info -->
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Menampilkan {{ pagination.from }}-{{ pagination.to }} dari {{ pagination.total }} siswa
                            </p>

                            <!-- Pagination Controls -->
                            <div class="flex items-center gap-1">
                                <!-- Previous -->
                                <button
                                    @click="goToPage(pagination.current_page - 1)"
                                    :disabled="pagination.current_page === 1"
                                    class="p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    <ChevronLeft class="w-5 h-5" />
                                </button>

                                <!-- Page Numbers -->
                                <template v-for="page in pagination.last_page" :key="page">
                                    <button
                                        v-if="page === 1 || page === pagination.last_page || (page >= pagination.current_page - 1 && page <= pagination.current_page + 1)"
                                        @click="goToPage(page)"
                                        :class="[
                                            'min-w-[36px] h-9 px-3 rounded-lg text-sm font-medium transition-colors',
                                            page === pagination.current_page
                                                ? 'bg-violet-500 text-white'
                                                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-zinc-700'
                                        ]"
                                    >
                                        {{ page }}
                                    </button>
                                    <span
                                        v-else-if="page === 2 && pagination.current_page > 3"
                                        class="px-1 text-slate-400"
                                    >...</span>
                                    <span
                                        v-else-if="page === pagination.last_page - 1 && pagination.current_page < pagination.last_page - 2"
                                        class="px-1 text-slate-400"
                                    >...</span>
                                </template>

                                <!-- Next -->
                                <button
                                    @click="goToPage(pagination.current_page + 1)"
                                    :disabled="pagination.current_page === pagination.last_page"
                                    class="p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    <ChevronRight class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
