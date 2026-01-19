<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { FileText, CheckCircle, XCircle, Calendar, User, X } from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import LeaveStatusBadge from '@/components/features/attendance/LeaveStatusBadge.vue';

interface Student {
    id: number;
    nama: string;
    nis: string;
}

interface LeaveRequest {
    id: number;
    student_id: number;
    jenis: 'IZIN' | 'SAKIT';
    tanggal_mulai: string;
    tanggal_selesai: string;
    alasan: string;
    status: 'PENDING' | 'APPROVED_TEACHER' | 'REJECTED';
    attachment_url?: string;
    student: Student;
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

interface Props {
    title: string;
    leaveRequests: PaginatedLeaveRequests;
    stats: {
        pending: number;
        approved: number;
        rejected: number;
    };
}

defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

const showRejectModal = ref(false);
const selectedRequest = ref<LeaveRequest | null>(null);
const rejectionReason = ref('');
const isSubmitting = ref(false);

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const getDateRange = (start: string, end: string) => {
    if (start === end) {
        return formatDate(start);
    }
    return `${formatDate(start)} - ${formatDate(end)}`;
};

const approveRequest = async (requestId: number) => {
    const confirmed = await modal.confirm(
        'Approve Izin',
        'Apakah Anda yakin ingin menyetujui pengajuan izin ini?',
        'Ya, Setujui',
        'Batal'
    );

    if (!confirmed) return;

    haptics.light();
    isSubmitting.value = true;

    router.post(`/teacher/leave-requests/${requestId}/approve`, {
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

const openRejectModal = (request: LeaveRequest) => {
    haptics.light();
    selectedRequest.value = request;
    rejectionReason.value = '';
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    showRejectModal.value = false;
    selectedRequest.value = null;
    rejectionReason.value = '';
};

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

    router.post(`/teacher/leave-requests/${selectedRequest.value.id}/approve`, {
        action: 'reject',
        rejection_reason: rejectionReason.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            modal.success('Izin berhasil ditolak');
            closeRejectModal();
        },
        onError: (errors: any) => {
            haptics.error();
            const message = errors.rejection_reason?.[0] || errors.message || 'Gagal menolak izin';
            modal.error(message);
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
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
                    <div class="mx-auto max-w-7xl">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">Verifikasi pengajuan izin siswa di kelas Anda</p>
                        </div>
                    </div>
                </div>
            </Motion>

            <div class="mx-auto max-w-7xl px-6 py-8">
                <!-- Stats -->
                <div class="grid gap-6 sm:grid-cols-3 mb-6">
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                    >
                        <div class="bg-amber-50 dark:bg-amber-950/30 rounded-2xl border border-amber-200 dark:border-amber-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-amber-600 dark:text-amber-400">Pending</p>
                            <p class="mt-2 text-3xl font-bold text-amber-900 dark:text-amber-100">{{ stats.pending }}</p>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                    >
                        <div class="bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Disetujui</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-900 dark:text-emerald-100">{{ stats.approved }}</p>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                    >
                        <div class="bg-red-50 dark:bg-red-950/30 rounded-2xl border border-red-200 dark:border-red-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">Ditolak</p>
                            <p class="mt-2 text-3xl font-bold text-red-900 dark:text-red-100">{{ stats.rejected }}</p>
                        </div>
                    </Motion>
                </div>

                <!-- Leave Requests List -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-200 dark:border-zinc-800">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Pengajuan Izin</h2>
                            <p class="text-sm text-slate-600 dark:text-zinc-400 mt-1">
                                Daftar pengajuan izin dari siswa di kelas Anda
                            </p>
                        </div>

                        <div class="divide-y divide-gray-200 dark:divide-zinc-800">
                            <div v-if="leaveRequests.data.length === 0" class="p-12 text-center">
                                <FileText :size="48" class="mx-auto text-gray-300 dark:text-zinc-700 mb-3" />
                                <p class="text-gray-500 dark:text-gray-400">Tidak ada pengajuan izin</p>
                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                                    Pengajuan izin dari siswa akan muncul di sini
                                </p>
                            </div>

                            <div
                                v-for="request in leaveRequests.data"
                                :key="request.id"
                                class="p-6 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                {{ request.student.nama }}
                                            </h3>
                                            <LeaveStatusBadge :status="request.status" />
                                        </div>

                                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                            <div class="flex items-center gap-2">
                                                <User :size="16" />
                                                <span>NIS: {{ request.student.nis }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <Calendar :size="16" />
                                                <span>{{ getDateRange(request.tanggal_mulai, request.tanggal_selesai) }}</span>
                                            </div>
                                            <div class="flex items-start gap-2">
                                                <FileText :size="16" class="mt-0.5" />
                                                <div class="flex-1">
                                                    <span class="font-medium">{{ request.jenis }}:</span>
                                                    <p class="mt-1">{{ request.alasan }}</p>
                                                </div>
                                            </div>
                                            <div v-if="request.attachment_url" class="pl-6">
                                                <a
                                                    :href="request.attachment_url"
                                                    target="_blank"
                                                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 underline"
                                                >
                                                    Lihat Lampiran
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="request.status === 'PENDING'" class="flex items-center gap-2 ml-4">
                                        <Motion :whileTap="{ scale: 0.97 }">
                                            <button
                                                @click="approveRequest(request.id)"
                                                :disabled="isSubmitting"
                                                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-medium
                                                       flex items-center gap-2 transition-colors
                                                       disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <CheckCircle :size="18" />
                                                <span class="hidden sm:inline">Setujui</span>
                                            </button>
                                        </Motion>

                                        <Motion :whileTap="{ scale: 0.97 }">
                                            <button
                                                @click="openRejectModal(request)"
                                                :disabled="isSubmitting"
                                                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium
                                                       flex items-center gap-2 transition-colors
                                                       disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <XCircle :size="18" />
                                                <span class="hidden sm:inline">Tolak</span>
                                            </button>
                                        </Motion>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="leaveRequests.links.length > 3" class="border-t border-gray-200 dark:border-zinc-800 px-6 py-4 bg-gray-50 dark:bg-zinc-800/50">
                            <div class="flex justify-center gap-2">
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
                                ><span v-html="link.label" /></Link>
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
                                        {{ selectedRequest?.student.nama }}
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
