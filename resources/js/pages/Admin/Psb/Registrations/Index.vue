<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import {
    UserPlus,
    Search,
    Filter,
    X,
    Clock,
    FileCheck,
    CheckCircle,
    XCircle,
    AlertTriangle,
    RefreshCw,
    Trophy,
    Eye,
    ChevronRight,
    Inbox,
    Calendar
} from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';
import Badge from '@/components/ui/Badge.vue';
import { index as registrationsIndex, show } from '@/routes/admin/psb/registrations';

/**
 * Interface definitions untuk type safety
 */
interface PsbRegistration {
    id: number;
    registration_number: string;
    student_name: string;
    student_nik: string;
    status: string;
    created_at: string;
    verified_at: string | null;
    academic_year?: { name: string };
    verifier?: { name: string };
}

interface Pagination {
    data: PsbRegistration[];
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
}

interface Stats {
    total: number;
    pending: number;
    document_review: number;
    approved: number;
    rejected: number;
    waiting_list: number;
    re_registration: number;
    completed: number;
}

interface Filters {
    status: string | null;
    search: string | null;
    start_date: string | null;
    end_date: string | null;
}

interface Props {
    title: string;
    registrations: Pagination;
    stats: Stats;
    filters: Filters;
    statuses: Record<string, string>;
}

const props = defineProps<Props>();
const haptics = useHaptics();

// Local filter state
const localFilters = ref<Filters>({
    status: props.filters.status || null,
    search: props.filters.search || null,
    start_date: props.filters.start_date || null,
    end_date: props.filters.end_date || null,
});

const showFilters = ref(false);

/**
 * Status config untuk badge dan styling
 */
const statusConfig: Record<string, { label: string; variant: string; icon: any }> = {
    pending: { label: 'Menunggu', variant: 'warning', icon: Clock },
    document_review: { label: 'Review Dokumen', variant: 'info', icon: FileCheck },
    approved: { label: 'Diterima', variant: 'success', icon: CheckCircle },
    rejected: { label: 'Ditolak', variant: 'error', icon: XCircle },
    waiting_list: { label: 'Waiting List', variant: 'warning', icon: AlertTriangle },
    re_registration: { label: 'Daftar Ulang', variant: 'info', icon: RefreshCw },
    completed: { label: 'Selesai', variant: 'success', icon: Trophy },
};

/**
 * Format tanggal ke format Indonesia
 */
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

/**
 * Apply filters dengan debounce untuk search
 */
let searchTimeout: ReturnType<typeof setTimeout>;
const applyFilters = () => {
    haptics.light();
    router.get(registrationsIndex().url, {
        status: localFilters.value.status,
        search: localFilters.value.search,
        start_date: localFilters.value.start_date,
        end_date: localFilters.value.end_date,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

/**
 * Handle search input dengan debounce
 */
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

/**
 * Reset semua filters
 */
const resetFilters = () => {
    haptics.light();
    localFilters.value = {
        status: null,
        search: null,
        start_date: null,
        end_date: null,
    };
    router.get(registrationsIndex().url, {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

/**
 * Filter by status via tab click
 */
const filterByStatus = (status: string | null) => {
    haptics.light();
    localFilters.value.status = status;
    applyFilters();
};

/**
 * Check if any filter is active
 */
const hasActiveFilters = computed(() => {
    return localFilters.value.status ||
           localFilters.value.search ||
           localFilters.value.start_date ||
           localFilters.value.end_date;
});

/**
 * Get status badge config
 */
const getStatusConfig = (status: string) => {
    return statusConfig[status] || { label: status, variant: 'default', icon: Clock };
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="w-12 h-12 rounded-xl bg-linear-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/25 shrink-0">
                            <UserPlus :size="24" class="text-white" />
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">{{ title }}</h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ registrations.total }} pendaftaran ditemukan
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Stats Cards - Quick Filter -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="grid gap-3 grid-cols-3 sm:grid-cols-4 lg:grid-cols-7">
                    <button
                        v-for="(config, status) in statusConfig"
                        :key="status"
                        @click="filterByStatus(status)"
                        :class="[
                            'text-left rounded-xl border shadow-sm p-3 transition-all duration-200',
                            'hover:scale-[1.02] active:scale-[0.98]',
                            localFilters.status === status
                                ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-300 dark:border-emerald-700 ring-2 ring-emerald-500/30'
                                : 'bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-800',
                        ]"
                    >
                        <div class="flex items-center gap-1.5 mb-1">
                            <component :is="config.icon" :size="12" class="text-slate-500 dark:text-slate-400" />
                            <span class="text-[9px] sm:text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 truncate">
                                {{ config.label }}
                            </span>
                        </div>
                        <p class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white tabular-nums">
                            {{ stats[status as keyof Stats] }}
                        </p>
                    </button>
                </div>
            </Motion>

            <!-- Filters Section -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm">
                    <div class="p-4 sm:p-6">
                        <!-- Search and Toggle Filter -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <!-- Search Input -->
                            <div class="flex-1 relative">
                                <Search :size="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                                <input
                                    v-model="localFilters.search"
                                    @input="handleSearch"
                                    type="text"
                                    placeholder="Cari nama atau nomor pendaftaran..."
                                    class="w-full h-[52px] pl-11 pr-4 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-900 dark:text-white placeholder-slate-400
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900
                                           transition-all"
                                />
                            </div>

                            <!-- Toggle Filters Button -->
                            <button
                                @click="showFilters = !showFilters"
                                class="flex items-center gap-2 px-4 py-2.5 min-h-[52px] bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                       rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-700
                                       active:scale-97 transition-all"
                            >
                                <Filter :size="18" />
                                <span>Filter</span>
                                <span v-if="hasActiveFilters" class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                            </button>

                            <!-- Reset Button -->
                            <button
                                v-if="hasActiveFilters"
                                @click="resetFilters"
                                class="flex items-center gap-2 px-4 py-2.5 min-h-[52px] bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800
                                       rounded-xl text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40
                                       active:scale-97 transition-all"
                            >
                                <X :size="18" />
                                <span class="hidden sm:inline">Reset</span>
                            </button>
                        </div>

                        <!-- Expanded Filters -->
                        <Transition
                            enter-active-class="transition-all duration-200 ease-out"
                            enter-from-class="opacity-0 max-h-0"
                            enter-to-class="opacity-100 max-h-48"
                            leave-active-class="transition-all duration-200 ease-in"
                            leave-from-class="opacity-100 max-h-48"
                            leave-to-class="opacity-0 max-h-0"
                        >
                            <div v-if="showFilters" class="mt-4 pt-4 border-t border-slate-200 dark:border-zinc-700 overflow-hidden">
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <!-- Status Filter -->
                                    <div>
                                        <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400 mb-1.5">
                                            Status
                                        </label>
                                        <select
                                            v-model="localFilters.status"
                                            @change="applyFilters"
                                            class="w-full h-[52px] px-4 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                   rounded-xl text-slate-900 dark:text-white
                                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900
                                                   transition-all"
                                        >
                                            <option :value="null">Semua Status</option>
                                            <option v-for="(label, status) in statuses" :key="status" :value="status">
                                                {{ label }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Start Date -->
                                    <div>
                                        <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400 mb-1.5">
                                            Dari Tanggal
                                        </label>
                                        <input
                                            v-model="localFilters.start_date"
                                            @change="applyFilters"
                                            type="date"
                                            class="w-full h-[52px] px-4 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                   rounded-xl text-slate-900 dark:text-white
                                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900
                                                   transition-all"
                                        />
                                    </div>

                                    <!-- End Date -->
                                    <div>
                                        <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400 mb-1.5">
                                            Sampai Tanggal
                                        </label>
                                        <input
                                            v-model="localFilters.end_date"
                                            @change="applyFilters"
                                            type="date"
                                            class="w-full h-[52px] px-4 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                   rounded-xl text-slate-900 dark:text-white
                                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900
                                                   transition-all"
                                        />
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Motion>

            <!-- Registrations List -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                    <!-- Desktop Table -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50">
                                    <th class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 px-6 py-4">
                                        No. Registrasi
                                    </th>
                                    <th class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 px-6 py-4">
                                        Nama Siswa
                                    </th>
                                    <th class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 px-6 py-4">
                                        Tanggal Daftar
                                    </th>
                                    <th class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 px-6 py-4">
                                        Status
                                    </th>
                                    <th class="text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 px-6 py-4">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                <tr
                                    v-for="registration in registrations.data"
                                    :key="registration.id"
                                    class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-sm font-medium text-slate-900 dark:text-white">
                                            {{ registration.registration_number }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-white">{{ registration.student_name }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ registration.student_nik }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-slate-600 dark:text-slate-400">
                                            {{ formatDate(registration.created_at) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <Badge
                                            :variant="getStatusConfig(registration.status).variant as any"
                                            size="sm"
                                            rounded="square"
                                        >
                                            <component :is="getStatusConfig(registration.status).icon" :size="12" class="mr-1" />
                                            {{ getStatusConfig(registration.status).label }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link
                                            :href="show(registration.id).url"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors"
                                        >
                                            <Eye :size="16" />
                                            <span>Detail</span>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="lg:hidden divide-y divide-slate-100 dark:divide-zinc-800">
                        <!-- Empty State -->
                        <div v-if="registrations.data.length === 0" class="p-8 sm:p-12 text-center">
                            <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                                <Inbox :size="36" class="text-slate-300 dark:text-zinc-600" />
                            </div>
                            <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                {{ hasActiveFilters ? 'Tidak ada hasil' : 'Belum ada pendaftaran' }}
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto mb-4">
                                {{ hasActiveFilters
                                    ? 'Tidak ditemukan pendaftaran dengan filter yang dipilih.'
                                    : 'Pendaftaran siswa baru akan muncul di sini.'
                                }}
                            </p>
                            <button
                                v-if="hasActiveFilters"
                                @click="resetFilters"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-medium transition-colors shadow-sm"
                            >
                                <X :size="16" />
                                <span>Reset Filter</span>
                            </button>
                        </div>

                        <!-- Registration Cards -->
                        <Link
                            v-for="(registration, index) in registrations.data"
                            :key="registration.id"
                            :href="show(registration.id).url"
                            class="block p-4 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                        >
                            <Motion
                                :initial="{ opacity: 0, y: 10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ delay: index * 0.03 }"
                            >
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-slate-900 dark:text-white truncate">{{ registration.student_name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ registration.registration_number }}</p>
                                    </div>
                                    <Badge
                                        :variant="getStatusConfig(registration.status).variant as any"
                                        size="sm"
                                        rounded="square"
                                    >
                                        {{ getStatusConfig(registration.status).label }}
                                    </Badge>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                        <Calendar :size="12" />
                                        <span>{{ formatDate(registration.created_at) }}</span>
                                    </div>
                                    <ChevronRight :size="16" class="text-slate-400" />
                                </div>
                            </Motion>
                        </Link>
                    </div>

                    <!-- Desktop Empty State -->
                    <div v-if="registrations.data.length === 0" class="hidden lg:block p-8 sm:p-12 text-center">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                            <Inbox :size="36" class="text-slate-300 dark:text-zinc-600" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            {{ hasActiveFilters ? 'Tidak ada hasil' : 'Belum ada pendaftaran' }}
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto mb-4">
                            {{ hasActiveFilters
                                ? 'Tidak ditemukan pendaftaran dengan filter yang dipilih.'
                                : 'Pendaftaran siswa baru akan muncul di sini.'
                            }}
                        </p>
                        <button
                            v-if="hasActiveFilters"
                            @click="resetFilters"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-medium transition-colors shadow-sm"
                        >
                            <X :size="16" />
                            <span>Reset Filter</span>
                        </button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="registrations.links.length > 3" class="border-t border-slate-200 dark:border-zinc-800 px-4 sm:px-6 py-4 bg-slate-50 dark:bg-zinc-800/50">
                        <div class="flex justify-center gap-2 flex-wrap">
                            <Link
                                v-for="link in registrations.links"
                                :key="link.label"
                                :href="link.url || ''"
                                preserve-scroll
                                preserve-state
                                :class="[
                                    'px-3 py-1.5 text-sm rounded-lg active:scale-97 transition-all',
                                    link.active
                                        ? 'bg-emerald-500 text-white font-semibold shadow-sm shadow-emerald-500/25'
                                        : 'bg-white dark:bg-zinc-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-700 border border-slate-300 dark:border-zinc-700',
                                    !link.url && 'opacity-50 cursor-not-allowed',
                                ]"
                            ><span v-html="link.label" /></Link>
                        </div>
                    </div>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
