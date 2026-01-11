<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CheckCircle, XCircle, Calendar, FileText, Filter } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import LeaveStatusBadge from '@/components/features/attendance/LeaveStatusBadge.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';

interface TeacherLeave {
    id: number;
    jenis: 'IZIN' | 'SAKIT' | 'CUTI';
    tanggal_mulai: string;
    tanggal_selesai: string;
    jumlah_hari: number;
    alasan: string;
    attachment_path: string | null;
    status: 'PENDING' | 'APPROVED' | 'REJECTED';
    rejection_reason: string | null;
    teacher: {
        id: number;
        name: string;
        nip: string;
    };
    created_at: string;
}

interface Props {
    title: string;
    leaves: {
        data: TeacherLeave[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        status?: string;
    };
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

// State
const filterForm = ref({
    status: props.filters.status || 'PENDING',
});

const showFilters = ref(false);
const rejectForm = useForm({
    rejection_reason: '',
});

// Methods
const applyFilters = () => {
    haptics.light();
    router.get('/principal/teacher-leaves', filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const approveLeave = async (leave: TeacherLeave) => {
    const confirmed = await modal.dialog({
        type: 'success',
        icon: 'check',
        title: 'Setujui Izin Guru',
        message: `Apakah Anda yakin ingin menyetujui pengajuan izin <b>${leave.teacher.name}</b> selama ${leave.jumlah_hari} hari?`,
        confirmText: 'Ya, Setujui',
        cancelText: 'Batal',
        showCancel: true,
        allowHtml: true
    });

    if (confirmed) {
        haptics.medium();
        router.post(`/principal/teacher-leaves/${leave.id}/approve`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                modal.success('Pengajuan izin berhasil disetujui');
            }
        });
    }
};

const rejectLeave = async (leave: TeacherLeave) => {
    const reason = await modal.prompt({
        title: 'Tolak Izin Guru',
        message: `Masukkan alasan penolakan untuk <b>${leave.teacher.name}</b>:`,
        placeholder: 'Alasan penolakan...',
        confirmText: 'Tolak',
        cancelText: 'Batal',
        allowHtml: true
    });

    if (reason) {
        haptics.medium();
        rejectForm.rejection_reason = reason;
        rejectForm.post(`/principal/teacher-leaves/${leave.id}/reject`, {
            preserveScroll: true,
            onSuccess: () => {
                modal.success('Pengajuan izin ditolak');
                rejectForm.reset();
            }
        });
    }
};

const viewAttachment = (url: string) => {
    haptics.light();
    window.open(url, '_blank');
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const getJenisColor = (jenis: string) => {
    const colors = {
        'IZIN': 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
        'SAKIT': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
        'CUTI': 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300',
    };
    return colors[jenis as keyof typeof colors] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ title }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Kelola pengajuan izin dan cuti guru
                    </p>
                </div>

                <Motion
                    tag="button"
                    :animate="{ scale: showFilters ? 0.95 : 1 }"
                    @click="showFilters = !showFilters"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                    <Filter :size="18" />
                    Filter
                </Motion>
            </div>

            <!-- Filters Panel -->
            <Motion
                v-if="showFilters"
                :initial="{ opacity: 0, height: 0 }"
                :animate="{ opacity: 1, height: 'auto' }"
                :exit="{ opacity: 0, height: 0 }"
                class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700"
            >
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status
                        </label>
                        <select
                            v-model="filterForm.status"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Semua Status</option>
                            <option value="PENDING">Pending</option>
                            <option value="APPROVED">Disetujui</option>
                            <option value="REJECTED">Ditolak</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors"
                    >
                        Terapkan Filter
                    </button>
                </div>
            </Motion>

            <!-- Leave Requests List -->
            <div class="space-y-4">
                <Motion
                    v-for="leave in leaves.data"
                    :key="leave.id"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ leave.teacher.name }}
                                </h3>
                                <span :class="[
                                    'px-3 py-1 rounded-full text-xs font-medium',
                                    getJenisColor(leave.jenis)
                                ]">
                                    {{ leave.jenis }}
                                </span>
                                <LeaveStatusBadge :status="leave.status" />
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                NIP: {{ leave.teacher.nip }}
                            </p>

                            <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                <div class="flex items-center gap-2">
                                    <Calendar :size="16" />
                                    <span>{{ formatDate(leave.tanggal_mulai) }} - {{ formatDate(leave.tanggal_selesai) }}</span>
                                </div>
                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs font-medium">
                                    {{ leave.jumlah_hari }} hari
                                </span>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-3">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alasan:</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ leave.alasan }}</p>
                            </div>

                            <div v-if="leave.attachment_path" class="mb-3">
                                <button
                                    @click="viewAttachment(leave.attachment_path)"
                                    class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg text-sm hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors"
                                >
                                    <FileText :size="16" />
                                    Lihat Lampiran
                                </button>
                            </div>

                            <div v-if="leave.status === 'REJECTED' && leave.rejection_reason" class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <p class="text-sm font-medium text-red-700 dark:text-red-300 mb-1">Alasan Penolakan:</p>
                                <p class="text-sm text-red-600 dark:text-red-400">{{ leave.rejection_reason }}</p>
                            </div>

                            <p class="text-xs text-gray-400 mt-3">
                                Diajukan pada {{ formatDate(leave.created_at) }}
                            </p>
                        </div>

                        <div v-if="leave.status === 'PENDING'" class="flex items-center gap-2 ml-4">
                            <Motion
                                tag="button"
                                :whileTap="{ scale: 0.95 }"
                                @click="approveLeave(leave)"
                                class="p-3 bg-green-600 hover:bg-green-700 text-white rounded-xl transition-colors"
                                title="Setujui"
                            >
                                <CheckCircle :size="20" />
                            </Motion>
                            <Motion
                                tag="button"
                                :whileTap="{ scale: 0.95 }"
                                @click="rejectLeave(leave)"
                                class="p-3 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors"
                                title="Tolak"
                            >
                                <XCircle :size="20" />
                            </Motion>
                        </div>
                    </div>
                </Motion>

                <div v-if="leaves.data.length === 0" class="bg-white dark:bg-gray-800 rounded-xl p-12 border border-gray-200 dark:border-gray-700 text-center">
                    <Calendar :size="48" class="mx-auto mb-3 text-gray-400 opacity-50" />
                    <p class="text-lg font-medium text-gray-500 dark:text-gray-400">Tidak ada pengajuan izin</p>
                    <p class="text-sm text-gray-400 mt-1">Semua pengajuan sudah diproses</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="leaves.last_page > 1" class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Menampilkan {{ (leaves.current_page - 1) * leaves.per_page + 1 }} - 
                        {{ Math.min(leaves.current_page * leaves.per_page, leaves.total) }} 
                        dari {{ leaves.total }} data
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            v-for="page in leaves.last_page"
                            :key="page"
                            @click="router.get(`/principal/teacher-leaves?page=${page}`, filterForm)"
                            :class="[
                                'px-3 py-1 rounded-lg text-sm font-medium transition-colors',
                                page === leaves.current_page
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                            ]"
                        >
                            {{ page }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
