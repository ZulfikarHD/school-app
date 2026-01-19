<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import {
    FileText,
    CheckCircle,
    XCircle,
    Calendar,
    User,
    X,
    Search,
    Filter,
    Image,
    Building2
} from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
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

        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="bg-white px-6 py-8 border-b border-gray-100 dark:bg-zinc-900 dark:border-zinc-800">
                    <div class="mx-auto max-w-7xl">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
                                <p class="mt-2 text-gray-600 dark:text-gray-400">
                                    Verifikasi pengajuan izin dari seluruh siswa
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <div class="mx-auto max-w-7xl px-6 py-8">
                <!-- Stats Cards -->
                <div class="grid gap-4 sm:gap-6 grid-cols-3 mb-6">
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                    >
                        <button
                            @click="filterByStatus('PENDING')"
                            :class="[
                                'w-full text-left rounded-2xl border shadow-sm p-4 sm:p-6 transition-all',
                                localFilters.status === 'PENDING'
                                    ? 'bg-amber-100 dark:bg-amber-900/50 border-amber-300 dark:border-amber-700 ring-2 ring-amber-500/30'
                                    : 'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/40'
                            ]"
                        >
                            <p class="text-xs sm:text-sm font-medium text-amber-600 dark:text-amber-400">Pending</p>
                            <p class="mt-1 sm:mt-2 text-2xl sm:text-3xl font-bold text-amber-900 dark:text-amber-100">{{ stats.pending }}</p>
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
                                'w-full text-left rounded-2xl border shadow-sm p-4 sm:p-6 transition-all',
                                localFilters.status === 'APPROVED'
                                    ? 'bg-emerald-100 dark:bg-emerald-900/50 border-emerald-300 dark:border-emerald-700 ring-2 ring-emerald-500/30'
                                    : 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/40'
                            ]"
                        >
                            <p class="text-xs sm:text-sm font-medium text-emerald-600 dark:text-emerald-400">Disetujui</p>
                            <p class="mt-1 sm:mt-2 text-2xl sm:text-3xl font-bold text-emerald-900 dark:text-emerald-100">{{ stats.approved }}</p>
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
                                'w-full text-left rounded-2xl border shadow-sm p-4 sm:p-6 transition-all',
                                localFilters.status === 'REJECTED'
                                    ? 'bg-red-100 dark:bg-red-900/50 border-red-300 dark:border-red-700 ring-2 ring-red-500/30'
                                    : 'bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/40'
                            ]"
                        >
                            <p class="text-xs sm:text-sm font-medium text-red-600 dark:text-red-400">Ditolak</p>
                            <p class="mt-1 sm:mt-2 text-2xl sm:text-3xl font-bold text-red-900 dark:text-red-100">{{ stats.rejected }}</p>
                        </button>
                    </Motion>
                </div>

                <!-- Filters Section -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm mb-6">
                        <div class="p-4 sm:p-6">
                            <!-- Search and Toggle Filter -->
                            <div class="flex flex-col sm:flex-row gap-4">
                                <!-- Search Input -->
                                <div class="flex-1 relative">
                                    <Search :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                                    <input
                                        v-model="localFilters.search"
                                        @input="handleSearch"
                                        type="text"
                                        placeholder="Cari nama atau NIS siswa..."
                                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                               rounded-xl text-slate-900 dark:text-white placeholder-slate-400
                                               focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                               transition-all duration-150"
                                    />
                                </div>

                                <!-- Toggle Filters Button -->
                                <button
                                    @click="showFilters = !showFilters"
                                    class="flex items-center gap-2 px-4 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-700
                                           transition-colors"
                                >
                                    <Filter :size="18" />
                                    <span>Filter</span>
                                    <span v-if="hasActiveFilters" class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                                </button>

                                <!-- Reset Button -->
                                <button
                                    v-if="hasActiveFilters"
                                    @click="resetFilters"
                                    class="flex items-center gap-2 px-4 py-2.5 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800
                                           rounded-xl text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40
                                           transition-colors"
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
                                            <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-zinc-400 mb-1.5">
                                                Kelas
                                            </label>
                                            <select
                                                v-model="localFilters.class_id"
                                                @change="applyFilters"
                                                class="w-full px-3 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                       rounded-xl text-slate-900 dark:text-white
                                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                                       transition-all duration-150"
                                            >
                                                <option :value="null">Semua Kelas</option>
                                                <option v-for="kelas in classes" :key="kelas.id" :value="kelas.id">
                                                    {{ kelas.nama }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Start Date -->
                                        <div>
                                            <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-zinc-400 mb-1.5">
                                                Dari Tanggal
                                            </label>
                                            <input
                                                v-model="localFilters.start_date"
                                                @change="applyFilters"
                                                type="date"
                                                class="w-full px-3 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                       rounded-xl text-slate-900 dark:text-white
                                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                                       transition-all duration-150"
                                            />
                                        </div>

                                        <!-- End Date -->
                                        <div>
                                            <label class="block text-[11px] font-semibold tracking-wide uppercase text-slate-500 dark:text-zinc-400 mb-1.5">
                                                Sampai Tanggal
                                            </label>
                                            <input
                                                v-model="localFilters.end_date"
                                                @change="applyFilters"
                                                type="date"
                                                class="w-full px-3 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                       rounded-xl text-slate-900 dark:text-white
                                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                                       transition-all duration-150"
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
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-200 dark:border-zinc-800">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Daftar Pengajuan Izin</h2>
                                    <p class="text-sm text-slate-600 dark:text-zinc-400 mt-1">
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

                        <div class="divide-y divide-gray-200 dark:divide-zinc-800">
                            <!-- Empty State -->
                            <div v-if="leaveRequests.data.length === 0" class="p-12 text-center">
                                <FileText :size="48" class="mx-auto text-gray-300 dark:text-zinc-700 mb-3" />
                                <p class="text-gray-500 dark:text-gray-400">Tidak ada pengajuan izin</p>
                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                                    {{ hasActiveFilters ? 'Coba ubah filter pencarian' : 'Pengajuan izin dari orang tua akan muncul di sini' }}
                                </p>
                            </div>

                            <!-- Leave Request Cards -->
                            <div
                                v-for="request in leaveRequests.data"
                                :key="request.id"
                                class="p-6 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                            >
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <!-- Student Info -->
                                        <div class="flex items-center gap-3 mb-3">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                                {{ request.student.nama_lengkap }}
                                            </h3>
                                            <LeaveStatusBadge :status="request.status" />
                                        </div>

                                        <!-- Details -->
                                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                            <div class="flex items-center gap-2">
                                                <User :size="16" class="shrink-0" />
                                                <span>NIS: {{ request.student.nis }}</span>
                                            </div>
                                            <div v-if="request.student.kelas" class="flex items-center gap-2">
                                                <Building2 :size="16" class="shrink-0" />
                                                <span>Kelas: {{ request.student.kelas.nama }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <Calendar :size="16" class="shrink-0" />
                                                <span>{{ getDateRange(request.tanggal_mulai, request.tanggal_selesai) }}</span>
                                            </div>
                                            <div class="flex items-start gap-2">
                                                <FileText :size="16" class="shrink-0 mt-0.5" />
                                                <div class="flex-1">
                                                    <span class="font-medium text-slate-700 dark:text-zinc-300">{{ request.jenis }}:</span>
                                                    <p class="mt-1 text-slate-600 dark:text-zinc-400">{{ request.alasan }}</p>
                                                </div>
                                            </div>
                                            <div v-if="request.attachment_path" class="flex items-center gap-2 pl-6">
                                                <Image :size="14" class="text-blue-500" />
                                                <a
                                                    :href="getAttachmentUrl(request.attachment_path)"
                                                    target="_blank"
                                                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 underline"
                                                >
                                                    Lihat Lampiran
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Reviewed By Info -->
                                        <div v-if="request.reviewed_by" class="mt-3 text-xs text-slate-500 dark:text-zinc-500">
                                            Diverifikasi oleh: {{ request.reviewed_by.name }}
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div v-if="request.status === 'PENDING'" class="flex items-center gap-2 sm:ml-4 shrink-0">
                                        <Motion :whileTap="{ scale: 0.97 }">
                                            <button
                                                @click="approveRequest(request.id)"
                                                :disabled="isSubmitting"
                                                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-medium
                                                       flex items-center gap-2 transition-colors shadow-sm shadow-emerald-500/25
                                                       disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <CheckCircle :size="18" />
                                                <span>Setujui</span>
                                            </button>
                                        </Motion>

                                        <Motion :whileTap="{ scale: 0.97 }">
                                            <button
                                                @click="openRejectModal(request)"
                                                :disabled="isSubmitting"
                                                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl font-medium
                                                       flex items-center gap-2 transition-colors shadow-sm shadow-red-500/25
                                                       disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <XCircle :size="18" />
                                                <span>Tolak</span>
                                            </button>
                                        </Motion>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="leaveRequests.links.length > 3" class="border-t border-gray-200 dark:border-zinc-800 px-6 py-4 bg-gray-50 dark:bg-zinc-800/50">
                            <div class="flex justify-center gap-2 flex-wrap">
                                <Link
                                    v-for="link in leaveRequests.links"
                                    :key="link.label"
                                    :href="link.url || ''"
                                    preserve-scroll
                                    preserve-state
                                    :class="[
                                        'px-3 py-1.5 text-sm rounded-lg transition-colors',
                                        link.active
                                            ? 'bg-emerald-500 text-white font-semibold'
                                            : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 border border-gray-300 dark:border-zinc-700',
                                        !link.url && 'opacity-50 cursor-not-allowed',
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>
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
