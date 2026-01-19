<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import {
    CheckCircle,
    XCircle,
    Calendar,
    User,
    X,
    Search,
    Filter,
    Image,
    Clock,
    ChevronRight,
    Inbox,
    AlertCircle
} from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import Badge from '@/components/ui/Badge.vue';
import LeaveStatusBadge from '@/components/features/attendance/LeaveStatusBadge.vue';

/**
 * Interface definitions untuk type safety
 */
interface SchoolClass {
    id: number;
    nama: string;
}

interface Student {
    id: number;
    nama_lengkap: string;
    nis: string;
    kelas?: SchoolClass;
}

interface User {
    id: number;
    name: string;
}

interface LeaveRequest {
    id: number;
    student_id: number;
    jenis: 'IZIN' | 'SAKIT';
    tanggal_mulai: string;
    tanggal_selesai: string;
    alasan: string;
    status: 'PENDING' | 'APPROVED' | 'REJECTED';
    attachment_path?: string;
    student: Student;
    submitted_by: User;
    reviewed_by?: User;
    created_at: string;
}

interface PaginatedLeaveRequests {
    data: LeaveRequest[];
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Filters {
    status: string | null;
    class_id: number | null;
    start_date: string | null;
    end_date: string | null;
    search: string | null;
}

interface Props {
    title: string;
    leaveRequests: PaginatedLeaveRequests;
    stats: {
        pending: number;
        approved: number;
        rejected: number;
    };
    classes: SchoolClass[];
    filters: Filters;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

// Local filter state
const localFilters = ref<Filters>({
    status: props.filters.status || null,
    class_id: props.filters.class_id || null,
    start_date: props.filters.start_date || null,
    end_date: props.filters.end_date || null,
    search: props.filters.search || null,
});

const showRejectModal = ref(false);
const selectedRequest = ref<LeaveRequest | null>(null);
const rejectionReason = ref('');
const isSubmitting = ref(false);
const showFilters = ref(false);

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
 * Get date range string untuk display
 */
const getDateRange = (start: string, end: string) => {
    if (start === end) {
        return formatDate(start);
    }
    return `${formatDate(start)} - ${formatDate(end)}`;
};

/**
 * Apply filters dengan debounce untuk search
 */
let searchTimeout: ReturnType<typeof setTimeout>;
const applyFilters = () => {
    haptics.light();
    router.get('/admin/leave-requests', {
        status: localFilters.value.status,
        class_id: localFilters.value.class_id,
        start_date: localFilters.value.start_date,
        end_date: localFilters.value.end_date,
        search: localFilters.value.search,
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
        class_id: null,
        start_date: null,
        end_date: null,
        search: null,
    };
    router.get('/admin/leave-requests', {}, {
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
 * Approve leave request dengan konfirmasi
 */
const approveRequest = async (requestId: number) => {
    const confirmed = await modal.confirm(
        'Setujui Izin',
        'Apakah Anda yakin ingin menyetujui pengajuan izin ini? Status kehadiran siswa akan otomatis diupdate.',
        'Ya, Setujui',
        'Batal'
    );

    if (!confirmed) return;

    haptics.light();
    isSubmitting.value = true;

    router.post(`/admin/leave-requests/${requestId}/approve`, {
        action: 'approve'
    }, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            modal.success('Izin berhasil disetujui');
        },
        onError: () => {
            haptics.error();
            modal.error('Gagal menyetujui izin');
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

/**
 * Open rejection modal
 */
const openRejectModal = (request: LeaveRequest) => {
    haptics.light();
    selectedRequest.value = request;
    rejectionReason.value = '';
    showRejectModal.value = true;
};

/**
 * Close rejection modal
 */
const closeRejectModal = () => {
    showRejectModal.value = false;
    selectedRequest.value = null;
    rejectionReason.value = '';
};

/**
 * Submit rejection dengan alasan
 */
const submitRejection = () => {
    if (!rejectionReason.value.trim()) {
        modal.error('Mohon berikan alasan penolakan');
        return;
    }

    if (rejectionReason.value.length < 10) {
        modal.error('Alasan penolakan minimal 10 karakter');
        return;
    }

    if (!selectedRequest.value) return;

    haptics.medium();
    isSubmitting.value = true;

    router.post(`/admin/leave-requests/${selectedRequest.value.id}/approve`, {
        action: 'reject',
        rejection_reason: rejectionReason.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            modal.success('Izin berhasil ditolak');
            closeRejectModal();
        },
        onError: (errors: Record<string, string[]>) => {
            haptics.error();
            const message = errors.rejection_reason?.[0] || 'Gagal menolak izin';
            modal.error(message);
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

/**
 * Get attachment URL untuk preview
 */
const getAttachmentUrl = (path: string | undefined) => {
    if (!path) return null;
    return `/storage/${path}`;
};

/**
 * Check if any filter is active
 */
const hasActiveFilters = computed(() => {
    return localFilters.value.status ||
           localFilters.value.class_id ||
           localFilters.value.start_date ||
           localFilters.value.end_date ||
           localFilters.value.search;
});
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
                        <div class="w-12 h-12 rounded-xl bg-linear-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/25 shrink-0">
                            <Calendar :size="24" class="text-white" />
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">{{ title }}</h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                Verifikasi pengajuan izin dari seluruh siswa
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Stats Cards - Interactive dengan visual affordance -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="grid gap-3 sm:gap-4 grid-cols-3">
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                    >
                        <button
                            @click="filterByStatus('PENDING')"
                            :class="[
                                'group w-full text-left rounded-2xl border shadow-sm p-3 sm:p-5 transition-all duration-200 relative overflow-hidden',
                                'focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950',
                                localFilters.status === 'PENDING'
                                    ? 'bg-amber-100 dark:bg-amber-900/50 border-amber-300 dark:border-amber-700 ring-2 ring-amber-500/30 scale-[1.02]'
                                    : 'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/40 hover:scale-[1.02] active:scale-[0.98]'
                            ]"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <Clock :size="14" class="text-amber-500 dark:text-amber-400 shrink-0" />
                                        <p class="text-[10px] sm:text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wide">Pending</p>
                                    </div>
                                    <p class="text-xl sm:text-3xl font-bold text-amber-900 dark:text-amber-100 tabular-nums">{{ stats.pending }}</p>
                                </div>
                                <ChevronRight
                                    :size="16"
                                    :class="[
                                        'text-amber-400 dark:text-amber-500 transition-transform shrink-0 mt-1',
                                        localFilters.status === 'PENDING' ? 'translate-x-0' : 'group-hover:translate-x-1'
                                    ]"
                                />
                            </div>
                            <p class="text-[9px] sm:text-[10px] text-amber-600/70 dark:text-amber-400/70 mt-1 sm:mt-2">
                                {{ localFilters.status === 'PENDING' ? 'Menampilkan' : 'Tap untuk filter' }}
                            </p>
                        </button>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                    >
                        <button
                            @click="filterByStatus('APPROVED')"
                            :class="[
                                'group w-full text-left rounded-2xl border shadow-sm p-3 sm:p-5 transition-all duration-200 relative overflow-hidden',
                                'focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950',
                                localFilters.status === 'APPROVED'
                                    ? 'bg-emerald-100 dark:bg-emerald-900/50 border-emerald-300 dark:border-emerald-700 ring-2 ring-emerald-500/30 scale-[1.02]'
                                    : 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 hover:scale-[1.02] active:scale-[0.98]'
                            ]"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <CheckCircle :size="14" class="text-emerald-500 dark:text-emerald-400 shrink-0" />
                                        <p class="text-[10px] sm:text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide">Disetujui</p>
                                    </div>
                                    <p class="text-xl sm:text-3xl font-bold text-emerald-900 dark:text-emerald-100 tabular-nums">{{ stats.approved }}</p>
                                </div>
                                <ChevronRight
                                    :size="16"
                                    :class="[
                                        'text-emerald-400 dark:text-emerald-500 transition-transform shrink-0 mt-1',
                                        localFilters.status === 'APPROVED' ? 'translate-x-0' : 'group-hover:translate-x-1'
                                    ]"
                                />
                            </div>
                            <p class="text-[9px] sm:text-[10px] text-emerald-600/70 dark:text-emerald-400/70 mt-1 sm:mt-2">
                                {{ localFilters.status === 'APPROVED' ? 'Menampilkan' : 'Tap untuk filter' }}
                            </p>
                        </button>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                    >
                        <button
                            @click="filterByStatus('REJECTED')"
                            :class="[
                                'group w-full text-left rounded-2xl border shadow-sm p-3 sm:p-5 transition-all duration-200 relative overflow-hidden',
                                'focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950',
                                localFilters.status === 'REJECTED'
                                    ? 'bg-red-100 dark:bg-red-900/50 border-red-300 dark:border-red-700 ring-2 ring-red-500/30 scale-[1.02]'
                                    : 'bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/40 hover:scale-[1.02] active:scale-[0.98]'
                            ]"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <XCircle :size="14" class="text-red-500 dark:text-red-400 shrink-0" />
                                        <p class="text-[10px] sm:text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wide">Ditolak</p>
                                    </div>
                                    <p class="text-xl sm:text-3xl font-bold text-red-900 dark:text-red-100 tabular-nums">{{ stats.rejected }}</p>
                                </div>
                                <ChevronRight
                                    :size="16"
                                    :class="[
                                        'text-red-400 dark:text-red-500 transition-transform shrink-0 mt-1',
                                        localFilters.status === 'REJECTED' ? 'translate-x-0' : 'group-hover:translate-x-1'
                                    ]"
                                />
                            </div>
                            <p class="text-[9px] sm:text-[10px] text-red-600/70 dark:text-red-400/70 mt-1 sm:mt-2">
                                {{ localFilters.status === 'REJECTED' ? 'Menampilkan' : 'Tap untuk filter' }}
                            </p>
                        </button>
                    </Motion>
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
                                    placeholder="Cari nama atau NIS siswa..."
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
                                    <!-- Class Filter -->
                                    <div>
                                        <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400 mb-1.5">
                                            Kelas
                                        </label>
                                        <select
                                            v-model="localFilters.class_id"
                                            @change="applyFilters"
                                            class="w-full h-[52px] px-4 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                   rounded-xl text-slate-900 dark:text-white
                                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900
                                                   transition-all"
                                        >
                                            <option :value="null">Semua Kelas</option>
                                            <option v-for="kelas in classes" :key="kelas.id" :value="kelas.id">
                                                {{ kelas.nama }}
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

            <!-- Leave Requests List -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
            >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Daftar Pengajuan Izin</h2>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                        {{ leaveRequests.data.length }} pengajuan ditampilkan
                                    </p>
                                </div>

                                <!-- Status Filter Tabs (Mobile) -->
                                <button
                                    v-if="localFilters.status"
                                    @click="filterByStatus(null)"
                                    class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline"
                                >
                                    Lihat Semua
                                </button>
                            </div>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <!-- Empty State - Enhanced dengan actionable guidance -->
                            <div v-if="leaveRequests.data.length === 0" class="p-8 sm:p-12 text-center">
                                <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                                    <Inbox :size="36" class="text-slate-300 dark:text-zinc-600" />
                                </div>
                                <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    {{ hasActiveFilters ? 'Tidak ada hasil' : 'Belum ada pengajuan' }}
                                </h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto mb-4">
                                    {{ hasActiveFilters
                                        ? 'Tidak ditemukan pengajuan izin dengan filter yang dipilih. Coba ubah kriteria pencarian.'
                                        : 'Pengajuan izin dari orang tua/wali siswa akan muncul di sini untuk diverifikasi.'
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
                                <div v-else class="flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                                    <AlertCircle :size="14" />
                                    <span>Orang tua dapat mengajukan izin melalui aplikasi mereka</span>
                                </div>
                            </div>

                            <!-- Leave Request Cards - Enhanced dengan better mobile UX -->
                            <div
                                v-for="(request, index) in leaveRequests.data"
                                :key="request.id"
                                class="p-4 sm:p-6 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                            >
                                <Motion
                                    :initial="{ opacity: 0, y: 10 }"
                                    :animate="{ opacity: 1, y: 0 }"
                                    :transition="{ delay: index * 0.03 }"
                                >
                                    <div class="flex flex-col gap-4">
                                        <!-- Header Row - Student Info & Status -->
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                                <!-- Avatar - Modern gradient border style -->
                                                <div class="relative shrink-0">
                                                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl overflow-hidden bg-linear-to-br from-emerald-400 to-teal-500 p-0.5 shadow-sm">
                                                        <div class="w-full h-full rounded-[8px] sm:rounded-[10px] bg-linear-to-br from-slate-50 to-slate-100 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center">
                                                            <span class="text-sm sm:text-base font-bold text-emerald-600 dark:text-emerald-400">
                                                                {{ request.student.nama_lengkap.charAt(0).toUpperCase() }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-white truncate">
                                                        {{ request.student.nama_lengkap }}
                                                    </h3>
                                                    <div class="flex items-center gap-2 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                                        <span class="font-mono">{{ request.student.nis }}</span>
                                                        <span v-if="request.student.kelas" class="hidden sm:inline">•</span>
                                                        <span v-if="request.student.kelas" class="hidden sm:inline">{{ request.student.kelas.nama }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <LeaveStatusBadge :status="request.status" />
                                        </div>

                                        <!-- Content - Leave Details -->
                                        <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-3 sm:p-4 space-y-3">
                                            <!-- Type & Date Row -->
                                            <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-sm">
                                                <Badge
                                                    :variant="request.jenis === 'SAKIT' ? 'error' : 'info'"
                                                    size="sm"
                                                    rounded="square"
                                                >
                                                    {{ request.jenis === 'SAKIT' ? 'Sakit' : 'Izin' }}
                                                </Badge>
                                                <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                                                    <Calendar :size="14" class="shrink-0" />
                                                    <span class="text-xs sm:text-sm">{{ getDateRange(request.tanggal_mulai, request.tanggal_selesai) }}</span>
                                                </div>
                                            </div>

                                            <!-- Reason -->
                                            <div>
                                                <p class="text-[11px] uppercase tracking-wide font-semibold text-slate-400 dark:text-slate-500 mb-1">Alasan</p>
                                                <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{{ request.alasan }}</p>
                                            </div>

                                            <!-- Attachment -->
                                            <div v-if="request.attachment_path" class="pt-2 border-t border-slate-200/80 dark:border-zinc-700/50">
                                                <a
                                                    :href="getAttachmentUrl(request.attachment_path)"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-2 px-3 py-2 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-sm text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"
                                                >
                                                    <Image :size="16" />
                                                    <span class="font-medium">Lihat Lampiran</span>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Footer - Meta info & Actions -->
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                            <!-- Meta Info -->
                                            <div class="text-xs text-slate-400 dark:text-slate-500">
                                                <span v-if="request.reviewed_by">
                                                    Diverifikasi oleh <span class="font-medium text-slate-500 dark:text-slate-400">{{ request.reviewed_by.name }}</span>
                                                </span>
                                                <span v-else>
                                                    Diajukan oleh <span class="font-medium text-slate-500 dark:text-slate-400">{{ request.submitted_by?.name || 'Orang Tua' }}</span>
                                                </span>
                                            </div>

                                            <!-- Action Buttons - Always visible -->
                                            <div v-if="request.status === 'PENDING'" class="flex items-center gap-2">
                                                <Motion :whileTap="{ scale: 0.97 }">
                                                    <button
                                                        @click="approveRequest(request.id)"
                                                        :disabled="isSubmitting"
                                                        class="flex-1 sm:flex-none px-4 py-2.5 min-h-[44px] bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white rounded-xl font-semibold
                                                               flex items-center justify-center gap-2 transition-colors shadow-sm shadow-emerald-500/25
                                                               disabled:opacity-50 disabled:cursor-not-allowed
                                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                                    >
                                                        <CheckCircle :size="18" />
                                                        <span>Setujui</span>
                                                    </button>
                                                </Motion>

                                                <Motion :whileTap="{ scale: 0.97 }">
                                                    <button
                                                        @click="openRejectModal(request)"
                                                        :disabled="isSubmitting"
                                                        class="flex-1 sm:flex-none px-4 py-2.5 min-h-[44px] bg-red-500 hover:bg-red-600 active:bg-red-700 text-white rounded-xl font-semibold
                                                               flex items-center justify-center gap-2 transition-colors shadow-sm shadow-red-500/25
                                                               disabled:opacity-50 disabled:cursor-not-allowed
                                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                                    >
                                                        <XCircle :size="18" />
                                                        <span>Tolak</span>
                                                    </button>
                                                </Motion>
                                            </div>
                                        </div>
                                    </div>
                                </Motion>
                            </div>
                        </div>

                    <!-- Pagination -->
                    <div v-if="leaveRequests.links.length > 3" class="border-t border-slate-200 dark:border-zinc-800 px-4 sm:px-6 py-4 bg-slate-50 dark:bg-zinc-800/50">
                        <div class="flex justify-center gap-2 flex-wrap">
                            <Link
                                v-for="link in leaveRequests.links"
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

        <!-- Rejection Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showRejectModal"
                    class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
                    @click="closeRejectModal"
                >
                    <Motion
                        :initial="{ opacity: 0, scale: 0.95, y: 20 }"
                        :animate="{ opacity: 1, scale: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                        @click.stop
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                            <!-- Header -->
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tolak Permohonan Izin</h3>
                                    <p class="text-sm text-slate-600 dark:text-zinc-400 mt-1">
                                        {{ selectedRequest?.student.nama_lengkap }}
                                    </p>
                                </div>
                                <button
                                    @click="closeRejectModal"
                                    class="p-1.5 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                >
                                    <X :size="20" class="text-slate-500" />
                                </button>
                            </div>

                            <!-- Description -->
                            <p class="text-sm text-slate-600 dark:text-zinc-400">
                                Mohon berikan alasan penolakan yang jelas (minimal 10 karakter):
                            </p>

                            <!-- Textarea -->
                            <textarea
                                v-model="rejectionReason"
                                rows="4"
                                placeholder="Contoh: Dokumen tidak lengkap, perlu konfirmasi lebih lanjut..."
                                class="w-full px-4 py-3 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                       rounded-xl text-slate-900 dark:text-white placeholder-slate-400
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                       transition-all duration-150 resize-none"
                            ></textarea>

                            <!-- Character count -->
                            <div class="text-xs text-slate-500 dark:text-zinc-500 text-right">
                                {{ rejectionReason.length }} / 500 karakter
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3 pt-2">
                                <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                                    <button
                                        @click="closeRejectModal"
                                        :disabled="isSubmitting"
                                        class="w-full px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700
                                               text-slate-700 dark:text-zinc-300 rounded-xl font-semibold
                                               transition-colors duration-150
                                               disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Batal
                                    </button>
                                </Motion>

                                <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                                    <button
                                        @click="submitRejection"
                                        :disabled="isSubmitting || !rejectionReason.trim() || rejectionReason.length < 10"
                                        class="w-full px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold
                                               flex items-center justify-center gap-2
                                               disabled:opacity-50 disabled:cursor-not-allowed
                                               transition-colors duration-150"
                                    >
                                        <XCircle :size="18" />
                                        <span>Tolak</span>
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </Motion>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
