<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    Calendar,
    Clock,
    FileText,
    CheckCircle2,
    XCircle,
    AlertCircle,
    User,
    Paperclip,
    X,
} from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import Badge from '@/components/ui/Badge.vue';
import { useHaptics } from '@/composables/useHaptics';
import { index as indexRoute, approve as approveRoute, reject as rejectRoute } from '@/routes/principal/teacher-leaves';

/**
 * Halaman kelola permohonan izin/cuti guru untuk Principal
 * dengan desain modern, dark mode support, dan animasi yang konsisten
 */

interface TeacherLeave {
    id: number;
    teacher: {
        name: string;
        email: string;
    };
    type: 'IZIN' | 'SAKIT' | 'CUTI';
    start_date: string | null;
    end_date: string | null;
    reason: string;
    status: 'PENDING' | 'APPROVED' | 'REJECTED';
    attachment_path: string | null;
    approved_by?: {
        name: string;
    } | null;
    approved_at?: string | null;
    rejection_reason?: string | null;
    created_at: string;
}

interface PaginatedLeaves {
    data: TeacherLeave[];
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Props {
    title: string;
    leaves: PaginatedLeaves;
    filters: {
        status?: string;
    };
}

const props = defineProps<Props>();

const haptics = useHaptics();
const showRejectModal = ref(false);
const selectedLeave = ref<TeacherLeave | null>(null);

const rejectForm = useForm({
    rejection_reason: '',
});

// Computed values untuk tab counts
const pendingCount = computed(() => {
    if (!props.filters.status || props.filters.status === 'PENDING') {
        return props.leaves.data.length;
    }
    return 0;
});

// Tab definitions dengan styling modern
const tabs = computed(() => [
    {
        key: 'PENDING',
        label: 'Pending',
        href: indexRoute().url,
        isActive: !props.filters.status || props.filters.status === 'PENDING',
        count: pendingCount.value,
        icon: Clock,
        color: 'amber',
    },
    {
        key: 'APPROVED',
        label: 'Disetujui',
        href: indexRoute({ query: { status: 'APPROVED' } }).url,
        isActive: props.filters.status === 'APPROVED',
        icon: CheckCircle2,
        color: 'emerald',
    },
    {
        key: 'REJECTED',
        label: 'Ditolak',
        href: indexRoute({ query: { status: 'REJECTED' } }).url,
        isActive: props.filters.status === 'REJECTED',
        icon: XCircle,
        color: 'red',
    },
]);

// Helper functions
const formatDate = (date: string | null | undefined) => {
    if (!date) return 'N/A';

    try {
        const parsedDate = new Date(date);
        if (isNaN(parsedDate.getTime())) return 'Invalid date';

        return parsedDate.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return 'Invalid date';
    }
};

const formatDateTime = (datetime: string | null | undefined) => {
    if (!datetime) return 'N/A';

    try {
        const parsedDate = new Date(datetime);
        if (isNaN(parsedDate.getTime())) return 'Invalid date';

        return parsedDate.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return 'Invalid date';
    }
};

const calculateDays = (startDate: string | null, endDate: string | null) => {
    if (!startDate || !endDate) return 0;

    try {
        const start = new Date(startDate);
        const end = new Date(endDate);

        if (isNaN(start.getTime()) || isNaN(end.getTime())) return 0;

        const diffTime = Math.abs(end.getTime() - start.getTime());
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays + 1;
    } catch {
        return 0;
    }
};

const getTypeVariant = (type: string) => {
    switch (type) {
        case 'IZIN':
            return 'info';
        case 'SAKIT':
            return 'error';
        case 'CUTI':
            return 'secondary';
        default:
            return 'secondary';
    }
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'PENDING':
            return 'warning';
        case 'APPROVED':
            return 'success';
        case 'REJECTED':
            return 'error';
        default:
            return 'secondary';
    }
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();
};

// Actions
const approveLeave = (leaveId: number) => {
    haptics.light();
    if (confirm('Apakah Anda yakin ingin menyetujui permohonan izin ini?')) {
        router.post(
            approveRoute(leaveId).url,
            {},
            {
                preserveScroll: true,
            }
        );
    }
};

const openRejectModal = (leave: TeacherLeave) => {
    haptics.light();
    selectedLeave.value = leave;
    showRejectModal.value = true;
    rejectForm.reset();
};

const closeRejectModal = () => {
    showRejectModal.value = false;
    selectedLeave.value = null;
    rejectForm.reset();
};

const submitReject = () => {
    if (!selectedLeave.value) return;

    haptics.light();
    rejectForm.post(rejectRoute(selectedLeave.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            closeRejectModal();
        },
    });
};
</script>

<template>
    <AppLayout :title="title">
        <Head :title="title" />

        <div class="space-y-6">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                            {{ title }}
                        </h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Kelola permohonan izin/cuti dari guru
                        </p>
                    </div>
                </div>
            </Motion>

            <!-- Filter Tabs -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
            >
                <div class="flex flex-wrap gap-2">
                    <Link
                        v-for="tab in tabs"
                        :key="tab.key"
                        :href="tab.href"
                        @click="haptics.light()"
                        :class="[
                            'inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2',
                            tab.isActive
                                ? 'bg-emerald-600 text-white shadow-sm'
                                : 'bg-white dark:bg-zinc-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800',
                        ]"
                    >
                        <component :is="tab.icon" :size="16" />
                        {{ tab.label }}
                        <span
                            v-if="tab.count && tab.count > 0"
                            :class="[
                                'px-2 py-0.5 rounded-full text-xs font-semibold',
                                tab.isActive
                                    ? 'bg-white/20 text-white'
                                    : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                            ]"
                        >
                            {{ tab.count }}
                        </span>
                    </Link>
                </div>
            </Motion>

            <!-- Leave Requests List -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <!-- Empty State -->
                    <div v-if="leaves.data.length === 0" class="p-12 text-center">
                        <div class="p-4 bg-slate-100 dark:bg-zinc-800 rounded-full w-fit mx-auto mb-4">
                            <FileText :size="32" class="text-slate-400 dark:text-slate-500" />
                        </div>
                        <p class="text-lg font-medium text-slate-900 dark:text-white">
                            Tidak ada permohonan izin
                        </p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Belum ada permohonan yang perlu ditinjau
                        </p>
                    </div>

                    <!-- Leave Items -->
                    <div v-else class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <Motion
                            v-for="(leave, index) in leaves.data"
                            :key="leave.id"
                            :initial="{ opacity: 0, x: -20 }"
                            :animate="{ opacity: 1, x: 0 }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 + index * 0.05 }"
                        >
                            <div class="p-5 sm:p-6 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                                    <!-- Teacher Avatar -->
                                    <div class="shrink-0">
                                        <div
                                            class="h-12 w-12 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 dark:from-emerald-500 dark:to-emerald-700 flex items-center justify-center shadow-sm"
                                        >
                                            <span class="text-sm font-bold text-white">
                                                {{ getInitials(leave.teacher.name) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Leave Details -->
                                    <div class="flex-1 min-w-0">
                                        <!-- Name & Badges -->
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <p class="text-base font-semibold text-slate-900 dark:text-white">
                                                {{ leave.teacher.name }}
                                            </p>
                                            <Badge :variant="getTypeVariant(leave.type)" size="sm">
                                                {{ leave.type }}
                                            </Badge>
                                            <Badge :variant="getStatusVariant(leave.status)" size="sm" dot>
                                                {{ leave.status }}
                                            </Badge>
                                        </div>

                                        <!-- Date Range -->
                                        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 mb-2">
                                            <Calendar :size="14" class="shrink-0" />
                                            <span>
                                                {{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}
                                            </span>
                                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-zinc-800 rounded-md text-xs font-medium">
                                                {{ calculateDays(leave.start_date, leave.end_date) }} hari
                                            </span>
                                        </div>

                                        <!-- Reason -->
                                        <p class="text-sm text-slate-700 dark:text-slate-300 mb-3 line-clamp-2">
                                            {{ leave.reason }}
                                        </p>

                                        <!-- Attachment -->
                                        <div v-if="leave.attachment_path" class="mb-3">
                                            <a
                                                :href="`/storage/${leave.attachment_path}`"
                                                target="_blank"
                                                class="inline-flex items-center gap-1.5 text-sm text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors"
                                            >
                                                <Paperclip :size="14" />
                                                Lihat Lampiran
                                            </a>
                                        </div>

                                        <!-- Meta info -->
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                            <Clock :size="12" />
                                            Diajukan {{ formatDateTime(leave.created_at) }}
                                        </div>

                                        <!-- Approval/Rejection Info -->
                                        <div
                                            v-if="leave.status === 'APPROVED' && leave.approved_by"
                                            class="mt-3 flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-2 rounded-lg"
                                        >
                                            <CheckCircle2 :size="14" />
                                            <span>
                                                Disetujui oleh {{ leave.approved_by.name }} pada
                                                {{ formatDateTime(leave.approved_at) }}
                                            </span>
                                        </div>

                                        <div
                                            v-if="leave.status === 'REJECTED'"
                                            class="mt-3 flex items-start gap-2 text-xs text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-3 py-2 rounded-lg"
                                        >
                                            <XCircle :size="14" class="shrink-0 mt-0.5" />
                                            <span>Ditolak: {{ leave.rejection_reason || 'Tidak ada alasan' }}</span>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div v-if="leave.status === 'PENDING'" class="shrink-0 flex gap-2 sm:flex-col">
                                        <Motion :whileHover="{ scale: 1.02 }" :whileTap="{ scale: 0.98 }">
                                            <button
                                                @click="approveLeave(leave.id)"
                                                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                                            >
                                                <CheckCircle2 :size="16" />
                                                Setujui
                                            </button>
                                        </Motion>
                                        <Motion :whileHover="{ scale: 1.02 }" :whileTap="{ scale: 0.98 }">
                                            <button
                                                @click="openRejectModal(leave)"
                                                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"
                                            >
                                                <XCircle :size="16" />
                                                Tolak
                                            </button>
                                        </Motion>
                                    </div>
                                </div>
                            </div>
                        </Motion>
                    </div>

                    <!-- Pagination -->
                    <div v-if="leaves.links.length > 3" class="border-t border-slate-100 dark:border-zinc-800 px-6 py-4">
                        <div class="flex flex-wrap justify-center gap-2">
                            <Link
                                v-for="link in leaves.links"
                                :key="link.label"
                                :href="link.url || ''"
                                :class="[
                                    'px-3 py-1.5 text-sm rounded-lg font-medium transition-colors',
                                    link.active
                                        ? 'bg-emerald-600 text-white'
                                        : 'bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700',
                                    !link.url && 'opacity-50 cursor-not-allowed pointer-events-none',
                                ]"
                            >
                                <span v-html="link.label" />
                            </Link>
                        </div>
                    </div>
                </div>
            </Motion>
        </div>

        <!-- Reject Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showRejectModal"
                    class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                    @click.self="closeRejectModal"
                >
                    <Transition
                        enter-active-class="duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="showRejectModal"
                            class="bg-white dark:bg-zinc-900 rounded-2xl shadow-xl max-w-md w-full overflow-hidden border border-slate-200 dark:border-zinc-800"
                        >
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-zinc-800">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-xl">
                                        <AlertCircle :size="20" class="text-red-600 dark:text-red-400" />
                                    </div>
                                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                                        Tolak Permohonan
                                    </h3>
                                </div>
                                <button
                                    @click="closeRejectModal"
                                    class="p-2 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                >
                                    <X :size="20" class="text-slate-500 dark:text-slate-400" />
                                </button>
                            </div>

                            <!-- Modal Body -->
                            <div class="p-6">
                                <div v-if="selectedLeave" class="flex items-center gap-3 mb-4 p-3 bg-slate-50 dark:bg-zinc-800 rounded-xl">
                                    <div
                                        class="h-10 w-10 rounded-lg bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center"
                                    >
                                        <span class="text-xs font-bold text-white">
                                            {{ getInitials(selectedLeave.teacher.name) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900 dark:text-white">
                                            {{ selectedLeave.teacher.name }}
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ selectedLeave.type }} - {{ calculateDays(selectedLeave.start_date, selectedLeave.end_date) }} hari
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">
                                        Alasan Penolakan
                                    </label>
                                    <textarea
                                        v-model="rejectForm.rejection_reason"
                                        rows="4"
                                        placeholder="Tuliskan alasan penolakan..."
                                        class="w-full px-4 py-3 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-red-500/20 focus:border-red-500/50 transition-all resize-none"
                                    />
                                    <p v-if="rejectForm.errors.rejection_reason" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        {{ rejectForm.errors.rejection_reason }}
                                    </p>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-zinc-800/50 border-t border-slate-100 dark:border-zinc-800">
                                <button
                                    @click="closeRejectModal"
                                    class="px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    @click="submitReject"
                                    :disabled="rejectForm.processing"
                                    class="px-4 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ rejectForm.processing ? 'Memproses...' : 'Tolak Permohonan' }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
