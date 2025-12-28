<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import LeaveStatusBadge from '@/components/features/attendance/LeaveStatusBadge.vue';
import { Plus, Calendar, FileText, Filter, X, Image as ImageIcon } from 'lucide-vue-next';
import type { LeaveRequest } from '@/types/attendance';

/**
 * Halaman riwayat permohonan izin untuk orang tua
 * dengan filter status dan detail modal
 */

interface Props {
    title: string;
    leaveRequests: LeaveRequest[];
}

const props = defineProps<Props>();

const haptics = useHaptics();

const statusFilter = ref<'ALL' | 'PENDING' | 'APPROVED' | 'REJECTED'>('ALL');
const showDetailModal = ref(false);
const selectedRequest = ref<LeaveRequest | null>(null);
const showAttachmentLightbox = ref(false);

/**
 * Filtered leave requests based on status
 */
const filteredRequests = computed(() => {
    if (statusFilter.value === 'ALL') {
        return props.leaveRequests;
    }
    return props.leaveRequests.filter(req => req.status === statusFilter.value);
});

/**
 * Summary counts
 */
const summary = computed(() => ({
    total: props.leaveRequests.length,
    pending: props.leaveRequests.filter(r => r.status === 'PENDING').length,
    approved: props.leaveRequests.filter(r => r.status === 'APPROVED').length,
    rejected: props.leaveRequests.filter(r => r.status === 'REJECTED').length
}));

/**
 * Format date range
 */
const formatDateRange = (start: string, end: string) => {
    const startDate = new Date(start);
    const endDate = new Date(end);
    
    const options: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'short', year: 'numeric' };
    
    if (start === end) {
        return startDate.toLocaleDateString('id-ID', options);
    }
    
    const startStr = startDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    const endStr = endDate.toLocaleDateString('id-ID', options);
    
    return `${startStr} - ${endStr}`;
};

/**
 * Get attachment URL
 */
const getAttachmentUrl = (path?: string) => {
    if (!path) return null;
    return `/storage/${path}`;
};

/**
 * Open detail modal
 */
const openDetail = (request: LeaveRequest) => {
    haptics.light();
    selectedRequest.value = request;
    showDetailModal.value = true;
};

/**
 * Open attachment lightbox
 */
const openAttachmentLightbox = () => {
    haptics.light();
    showAttachmentLightbox.value = true;
};
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
                    <div class="mx-auto max-w-7xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">Pantau status permohonan izin anak Anda</p>
                        </div>
                        
                        <Motion :whileTap="{ scale: 0.97 }">
                            <Link
                                href="/parent/leave-requests/create"
                                @click="haptics.light()"
                                class="px-5 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold
                                       flex items-center gap-2 shadow-sm shadow-emerald-500/25
                                       transition-colors duration-150"
                            >
                                <Plus :size="20" />
                                <span>Ajukan Izin</span>
                            </Link>
                        </Motion>
                    </div>
                </div>
            </Motion>
            
            <div class="mx-auto max-w-7xl px-6 py-8 space-y-6">
                <!-- Summary Cards -->
                <div class="grid gap-4 grid-cols-2 md:grid-cols-4">
                    <Motion
                        v-for="(item, index) in [
                            { label: 'Total', value: summary.total, color: 'slate', filter: 'ALL' },
                            { label: 'Menunggu', value: summary.pending, color: 'amber', filter: 'PENDING' },
                            { label: 'Disetujui', value: summary.approved, color: 'emerald', filter: 'APPROVED' },
                            { label: 'Ditolak', value: summary.rejected, color: 'red', filter: 'REJECTED' }
                        ]"
                        :key="item.label"
                        :initial="{ opacity: 0, scale: 0.9 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 + index * 0.02 }"
                        :whileHover="{ scale: 1.02 }"
                        :whileTap="{ scale: 0.98 }"
                    >
                        <button
                            @click="statusFilter = item.filter as any; haptics.light()"
                            :class="[
                                'bg-white dark:bg-zinc-900 rounded-xl border shadow-sm p-4 text-left transition-all duration-150',
                                statusFilter === item.filter
                                    ? 'border-emerald-500 ring-2 ring-emerald-500/20'
                                    : 'border-slate-200 dark:border-zinc-800 hover:border-slate-300'
                            ]"
                        >
                            <p class="text-xs font-medium text-slate-600 dark:text-zinc-400">{{ item.label }}</p>
                            <p class="mt-1 text-2xl font-bold" :class="`text-${item.color}-600`">{{ item.value }}</p>
                        </button>
                    </Motion>
                </div>
                
                <!-- Requests List -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-200 dark:border-zinc-800">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                    Daftar Permohonan
                                    <span class="text-sm font-normal text-slate-500 dark:text-zinc-400 ml-2">
                                        ({{ filteredRequests.length }})
                                    </span>
                                </h2>
                                
                                <div v-if="statusFilter !== 'ALL'" class="flex items-center gap-2">
                                    <Filter :size="16" class="text-slate-400" />
                                    <span class="text-sm text-slate-600 dark:text-zinc-400">Filter: {{ statusFilter }}</span>
                                    <button
                                        @click="statusFilter = 'ALL'; haptics.light()"
                                        class="p-1 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                    >
                                        <X :size="14" class="text-slate-500" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Empty State -->
                        <div v-if="filteredRequests.length === 0" class="p-12 text-center">
                            <FileText :size="48" class="mx-auto text-slate-300 dark:text-zinc-700 mb-4" />
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                                Belum Ada Permohonan
                            </h3>
                            <p class="text-slate-600 dark:text-zinc-400 mb-6">
                                {{ statusFilter === 'ALL' ? 'Anda belum pernah mengajukan permohonan izin' : `Tidak ada permohonan dengan status ${statusFilter}` }}
                            </p>
                            <Motion :whileTap="{ scale: 0.97 }">
                                <Link
                                    href="/parent/leave-requests/create"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold
                                           transition-colors duration-150"
                                >
                                    <Plus :size="18" />
                                    <span>Ajukan Izin Sekarang</span>
                                </Link>
                            </Motion>
                        </div>
                        
                        <!-- Requests Grid -->
                        <div v-else class="p-6">
                            <div class="grid gap-4 md:grid-cols-2">
                                <Motion
                                    v-for="request in filteredRequests"
                                    :key="request.id"
                                    :initial="{ opacity: 0, y: 10 }"
                                    :animate="{ opacity: 1, y: 0 }"
                                    :whileHover="{ y: -2 }"
                                    :whileTap="{ scale: 0.98 }"
                                >
                                    <button
                                        @click="openDetail(request)"
                                        class="w-full p-5 bg-slate-50 dark:bg-zinc-800 rounded-xl border border-slate-200 dark:border-zinc-700
                                               hover:border-slate-300 dark:hover:border-zinc-600 text-left
                                               transition-all duration-150"
                                    >
                                        <div class="flex items-start justify-between gap-4 mb-3">
                                            <div>
                                                <h3 class="font-semibold text-slate-900 dark:text-white">
                                                    {{ request.student?.nama_lengkap }}
                                                </h3>
                                                <p class="text-sm text-slate-500 dark:text-zinc-400">
                                                    {{ request.student?.kelas?.nama_lengkap }}
                                                </p>
                                            </div>
                                            <LeaveStatusBadge :status="request.status" size="sm" />
                                        </div>
                                        
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-zinc-400">
                                                <Calendar :size="14" />
                                                <span>{{ formatDateRange(request.tanggal_mulai, request.tanggal_selesai) }}</span>
                                            </div>
                                            
                                            <div>
                                                <span
                                                    :class="[
                                                        'inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold',
                                                        request.jenis === 'SAKIT'
                                                            ? 'bg-sky-50 text-sky-700 border border-sky-200/50'
                                                            : 'bg-amber-50 text-amber-700 border border-amber-200/50'
                                                    ]"
                                                >
                                                    {{ request.jenis }}
                                                </span>
                                            </div>
                                            
                                            <p class="text-sm text-slate-700 dark:text-zinc-300 line-clamp-2">
                                                {{ request.alasan }}
                                            </p>
                                            
                                            <div v-if="request.attachment_path" class="flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400">
                                                <ImageIcon :size="12" />
                                                <span>Ada lampiran</span>
                                            </div>
                                        </div>
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>
        </div>
    </AppLayout>
    
    <!-- Detail Modal -->
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
                v-if="showDetailModal && selectedRequest"
                class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 overflow-y-auto"
                @click="showDetailModal = false"
            >
                <Motion
                    :initial="{ opacity: 0, scale: 0.95, y: 20 }"
                    :animate="{ opacity: 1, scale: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                    @click.stop
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <!-- Header -->
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 border-b border-slate-200 dark:border-zinc-800 p-6 flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Detail Permohonan</h3>
                                <p class="text-sm text-slate-600 dark:text-zinc-400 mt-1">
                                    Diajukan pada {{ new Date(selectedRequest.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                                </p>
                            </div>
                            <button
                                @click="showDetailModal = false"
                                class="p-2 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6 space-y-6">
                            <!-- Status -->
                            <div>
                                <p class="text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">Status</p>
                                <LeaveStatusBadge :status="selectedRequest.status" size="lg" />
                            </div>
                            
                            <!-- Student Info -->
                            <div>
                                <p class="text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">Siswa</p>
                                <p class="font-semibold text-slate-900 dark:text-white">{{ selectedRequest.student?.nama_lengkap }}</p>
                                <p class="text-sm text-slate-600 dark:text-zinc-400">{{ selectedRequest.student?.kelas?.nama_lengkap }}</p>
                            </div>
                            
                            <!-- Type -->
                            <div>
                                <p class="text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">Jenis Izin</p>
                                <span
                                    :class="[
                                        'inline-flex items-center px-3 py-1.5 rounded-lg font-semibold',
                                        selectedRequest.jenis === 'SAKIT'
                                            ? 'bg-sky-50 text-sky-700 border border-sky-200/50'
                                            : 'bg-amber-50 text-amber-700 border border-amber-200/50'
                                    ]"
                                >
                                    {{ selectedRequest.jenis }}
                                </span>
                            </div>
                            
                            <!-- Date Range -->
                            <div>
                                <p class="text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">Rentang Tanggal</p>
                                <div class="flex items-center gap-2">
                                    <Calendar :size="18" class="text-slate-400" />
                                    <span class="text-slate-900 dark:text-white font-medium">
                                        {{ formatDateRange(selectedRequest.tanggal_mulai, selectedRequest.tanggal_selesai) }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Reason -->
                            <div>
                                <p class="text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">Alasan</p>
                                <p class="text-slate-900 dark:text-white whitespace-pre-wrap">{{ selectedRequest.alasan }}</p>
                            </div>
                            
                            <!-- Attachment -->
                            <div v-if="selectedRequest.attachment_path">
                                <p class="text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">Lampiran</p>
                                <button
                                    @click="openAttachmentLightbox"
                                    class="relative w-full h-48 rounded-xl overflow-hidden bg-slate-100 dark:bg-zinc-800
                                           hover:ring-2 hover:ring-emerald-500/50 transition-all group"
                                >
                                    <img
                                        :src="getAttachmentUrl(selectedRequest.attachment_path)!"
                                        alt="Lampiran"
                                        class="w-full h-full object-cover"
                                    />
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                                        <ImageIcon :size="24" class="text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                                    </div>
                                </button>
                            </div>
                            
                            <!-- Rejection Reason -->
                            <div v-if="selectedRequest.status === 'REJECTED' && selectedRequest.rejection_reason" class="p-4 bg-red-50/80 dark:bg-red-950/30 border border-red-200/50 rounded-xl">
                                <p class="text-xs font-semibold text-red-700 dark:text-red-400 uppercase tracking-wide mb-2">Alasan Penolakan</p>
                                <p class="text-sm text-red-600 dark:text-red-300">{{ selectedRequest.rejection_reason }}</p>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>
        </Transition>
    </Teleport>
    
    <!-- Attachment Lightbox -->
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
                v-if="showAttachmentLightbox && selectedRequest?.attachment_path"
                class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4"
                @click="showAttachmentLightbox = false"
            >
                <button
                    @click="showAttachmentLightbox = false"
                    class="absolute top-4 right-4 p-2 bg-white/10 hover:bg-white/20 rounded-full text-white transition-colors"
                >
                    <X :size="24" />
                </button>
                
                <img
                    :src="getAttachmentUrl(selectedRequest.attachment_path)!"
                    alt="Lampiran"
                    class="max-w-full max-h-full rounded-2xl"
                    @click.stop
                />
            </div>
        </Transition>
    </Teleport>
</template>
