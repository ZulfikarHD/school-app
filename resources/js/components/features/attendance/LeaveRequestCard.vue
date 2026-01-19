<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Calendar, FileText, User, CheckCircle2, XCircle, Image as ImageIcon, X } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import LeaveStatusBadge from './LeaveStatusBadge.vue';
import type { LeaveRequest } from '@/types/attendance';

/**
 * Card component untuk menampilkan leave request dengan action approve/reject
 * Digunakan di halaman verifikasi izin guru
 */

interface Props {
    leaveRequest: LeaveRequest;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'approved', id: number): void;
    (e: 'rejected', id: number): void;
}>();

const haptics = useHaptics();
const modal = useModal();

const showLightbox = ref(false);
const showRejectModal = ref(false);
const rejectionReason = ref('');
const loading = ref(false);

/**
 * Format date range untuk display
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
    
    // Calculate days
    const diffTime = Math.abs(endDate.getTime() - startDate.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    
    return `${startStr} - ${endStr} (${diffDays} hari)`;
};

/**
 * Get attachment URL
 */
const getAttachmentUrl = (path?: string) => {
    if (!path) return null;
    return `/storage/${path}`;
};

/**
 * Handle approve action
 */
const handleApprove = async () => {
    const confirmed = await modal.confirm(
        'Setujui Permohonan Izin',
        `Permohonan ini akan disetujui dan otomatis tercatat sebagai ${props.leaveRequest.jenis} di absensi siswa untuk tanggal ${formatDateRange(props.leaveRequest.tanggal_mulai, props.leaveRequest.tanggal_selesai)}.`,
        'Ya, Setujui',
        'Batal'
    );
    
    if (!confirmed) return;
    
    haptics.medium();
    loading.value = true;
    
    router.post(`/teacher/leave-requests/${props.leaveRequest.id}/approve`, {
        action: 'approve'
    }, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            modal.success('Permohonan izin berhasil disetujui');
            emit('approved', props.leaveRequest.id);
        },
        onError: (errors: any) => {
            haptics.error();
            const message = errors.message || 'Gagal menyetujui permohonan izin';
            modal.error(message);
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

/**
 * Handle reject action
 */
const handleReject = async () => {
    if (!rejectionReason.value.trim()) {
        modal.error('Mohon berikan alasan penolakan');
        return;
    }
    
    haptics.medium();
    loading.value = true;
    
    router.post(`/teacher/leave-requests/${props.leaveRequest.id}/approve`, {
        action: 'reject',
        rejection_reason: rejectionReason.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            modal.success('Permohonan izin berhasil ditolak');
            showRejectModal.value = false;
            rejectionReason.value = '';
            emit('rejected', props.leaveRequest.id);
        },
        onError: (errors: any) => {
            haptics.error();
            const message = errors.message || 'Gagal menolak permohonan izin';
            modal.error(message);
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

/**
 * Open reject modal
 */
const openRejectModal = () => {
    haptics.light();
    showRejectModal.value = true;
};

/**
 * Open attachment lightbox
 */
const openLightbox = () => {
    if (props.leaveRequest.attachment_path) {
        haptics.light();
        showLightbox.value = true;
    }
};
</script>

<template>
    <Motion
        :initial="{ opacity: 0, y: 10 }"
        :animate="{ opacity: 1, y: 0 }"
        :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
        :whileHover="{ y: -2 }"
    >
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
            <div class="p-6 space-y-4">
                <!-- Header: Student Info & Status -->
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3 flex-1">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center flex-shrink-0">
                            <User :size="20" class="text-slate-600 dark:text-zinc-400" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white">
                                {{ leaveRequest.student?.nama_lengkap }}
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-zinc-400">
                                {{ leaveRequest.student?.kelas?.nama_lengkap }}
                            </p>
                        </div>
                    </div>
                    
                    <LeaveStatusBadge :status="leaveRequest.status" size="md" />
                </div>
                
                <!-- Date Range -->
                <div class="flex items-center gap-2 text-sm">
                    <Calendar :size="16" class="text-slate-400" />
                    <span class="text-slate-700 dark:text-zinc-300">
                        {{ formatDateRange(leaveRequest.tanggal_mulai, leaveRequest.tanggal_selesai) }}
                    </span>
                </div>
                
                <!-- Leave Type Badge -->
                <div>
                    <span
                        :class="[
                            'inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold',
                            leaveRequest.jenis === 'SAKIT'
                                ? 'bg-sky-50/80 text-sky-700 border border-sky-200/50'
                                : 'bg-amber-50/80 text-amber-700 border border-amber-200/50'
                        ]"
                    >
                        {{ leaveRequest.jenis }}
                    </span>
                </div>
                
                <!-- Reason -->
                <div class="flex items-start gap-2">
                    <FileText :size="16" class="text-slate-400 mt-0.5 flex-shrink-0" />
                    <p class="text-sm text-slate-600 dark:text-zinc-400">
                        {{ leaveRequest.alasan }}
                    </p>
                </div>
                
                <!-- Attachment Preview -->
                <div v-if="leaveRequest.attachment_path">
                    <Motion
                        :whileTap="{ scale: 0.97 }"
                    >
                        <button
                            @click="openLightbox"
                            class="relative w-full h-40 rounded-xl overflow-hidden bg-slate-100 dark:bg-zinc-800
                                   hover:ring-2 hover:ring-emerald-500/50 transition-all group"
                        >
                            <img
                                :src="getAttachmentUrl(leaveRequest.attachment_path)!"
                                :alt="`Lampiran izin ${leaveRequest.student?.nama_lengkap}`"
                                class="w-full h-full object-cover"
                            />
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                                <ImageIcon :size="24" class="text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                            </div>
                        </button>
                    </Motion>
                </div>
                
                <!-- Action Buttons (Only for PENDING status) dengan accessible focus states -->
                <div v-if="leaveRequest.status === 'PENDING'" class="flex gap-3 pt-2" role="group" aria-label="Aksi permohonan izin">
                    <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                        <button
                            @click="handleApprove"
                            :disabled="loading"
                            :aria-busy="loading"
                            class="w-full px-4 py-2.5 min-h-[44px] bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold
                                   flex items-center justify-center gap-2
                                   disabled:opacity-50 disabled:cursor-not-allowed
                                   focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900
                                   transition-colors duration-150"
                        >
                            <CheckCircle2 :size="18" aria-hidden="true" />
                            <span>Setujui</span>
                        </button>
                    </Motion>
                    
                    <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                        <button
                            @click="openRejectModal"
                            :disabled="loading"
                            class="w-full px-4 py-2.5 min-h-[44px] bg-red-50 hover:bg-red-100 text-red-600 rounded-xl font-semibold
                                   border border-red-200/50
                                   flex items-center justify-center gap-2
                                   disabled:opacity-50 disabled:cursor-not-allowed
                                   focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900
                                   transition-colors duration-150"
                        >
                            <XCircle :size="18" aria-hidden="true" />
                            <span>Tolak</span>
                        </button>
                    </Motion>
                </div>
                
                <!-- Rejection Reason (if rejected) -->
                <div v-if="leaveRequest.status === 'REJECTED' && leaveRequest.rejection_reason" class="p-3 bg-red-50/80 dark:bg-red-950/30 border border-red-200/50 rounded-lg">
                    <p class="text-xs font-semibold text-red-700 dark:text-red-400 mb-1">Alasan Penolakan:</p>
                    <p class="text-sm text-red-600 dark:text-red-300">{{ leaveRequest.rejection_reason }}</p>
                </div>
            </div>
        </div>
    </Motion>
    
    <!-- Lightbox for Attachment dengan proper accessibility -->
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
                v-if="showLightbox"
                class="fixed inset-0 z-50 bg-black/90 backdrop-blur-sm flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                :aria-label="`Lampiran izin ${leaveRequest.student?.nama_lengkap}`"
                @click="showLightbox = false"
                @keydown.escape="showLightbox = false"
            >
                <button
                    @click="showLightbox = false"
                    class="absolute top-4 right-4 w-11 h-11 bg-white/10 hover:bg-white/20 rounded-full text-white transition-colors flex items-center justify-center focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50"
                    aria-label="Tutup lightbox"
                >
                    <X :size="24" aria-hidden="true" />
                </button>
                
                <img
                    :src="getAttachmentUrl(leaveRequest.attachment_path)!"
                    :alt="`Lampiran izin ${leaveRequest.student?.nama_lengkap}`"
                    class="max-w-full max-h-full rounded-2xl"
                    @click.stop
                />
            </div>
        </Transition>
    </Teleport>
    
    <!-- Reject Modal dengan proper accessibility -->
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
                class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                aria-labelledby="reject-modal-title"
                @click="showRejectModal = false"
                @keydown.escape="showRejectModal = false"
            >
                <Motion
                    :initial="{ opacity: 0, scale: 0.95, y: 20 }"
                    :animate="{ opacity: 1, scale: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                    @click.stop
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 border border-slate-200 dark:border-zinc-800">
                        <h3 id="reject-modal-title" class="text-lg font-bold text-slate-900 dark:text-white">Tolak Permohonan Izin</h3>
                        
                        <p class="text-sm text-slate-600 dark:text-zinc-400">
                            Mohon berikan alasan penolakan untuk {{ leaveRequest.student?.nama_lengkap }}:
                        </p>
                        
                        <div>
                            <label for="rejection-reason" class="sr-only">Alasan penolakan</label>
                            <textarea
                                id="rejection-reason"
                                v-model="rejectionReason"
                                rows="4"
                                placeholder="Contoh: Dokumen tidak lengkap, perlu konfirmasi lebih lanjut..."
                                class="w-full px-4 py-3 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                       rounded-xl text-slate-900 dark:text-white placeholder-slate-400
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 focus:bg-white dark:focus:bg-zinc-900
                                       transition-all duration-150 resize-none"
                                :aria-invalid="!rejectionReason.trim()"
                            ></textarea>
                        </div>
                        
                        <div class="flex gap-3">
                            <button
                                @click="showRejectModal = false"
                                class="flex-1 px-4 py-2.5 min-h-[44px] bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700
                                       text-slate-700 dark:text-zinc-300 rounded-xl font-semibold
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500/50 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900
                                       transition-colors duration-150"
                            >
                                Batal
                            </button>
                            
                            <button
                                @click="handleReject"
                                :disabled="loading || !rejectionReason.trim()"
                                :aria-busy="loading"
                                class="flex-1 px-4 py-2.5 min-h-[44px] bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold
                                       flex items-center justify-center gap-2
                                       disabled:opacity-50 disabled:cursor-not-allowed
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900
                                       transition-colors duration-150"
                            >
                                <XCircle :size="18" aria-hidden="true" />
                                <span>Tolak</span>
                            </button>
                        </div>
                    </div>
                </Motion>
            </div>
        </Transition>
    </Teleport>
</template>
