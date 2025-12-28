<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import LeaveRequestCard from '@/components/features/attendance/LeaveRequestCard.vue';
import { Motion } from 'motion-v';
import { ClipboardList, Clock, CheckCircle2, XCircle, Filter, X } from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';
import type { LeaveRequest } from '@/types/attendance';

/**
 * Halaman verifikasi permohonan izin untuk guru
 * dengan approval workflow dan auto-sync ke attendance
 */

interface Props {
    title: string;
    leaveRequests: LeaveRequest[];
}

const props = defineProps<Props>();

const haptics = useHaptics();

const statusFilter = ref<'ALL' | 'PENDING' | 'APPROVED' | 'REJECTED'>('PENDING');
const localRequests = ref<LeaveRequest[]>([...props.leaveRequests]);

/**
 * Filtered leave requests based on status
 */
const filteredRequests = computed(() => {
    if (statusFilter.value === 'ALL') {
        return localRequests.value;
    }
    return localRequests.value.filter(req => req.status === statusFilter.value);
});

/**
 * Summary counts
 */
const summary = computed(() => ({
    total: localRequests.value.length,
    pending: localRequests.value.filter(r => r.status === 'PENDING').length,
    approved: localRequests.value.filter(r => r.status === 'APPROVED').length,
    rejected: localRequests.value.filter(r => r.status === 'REJECTED').length
}));

/**
 * Handle approved event (optimistic UI update)
 */
const handleApproved = (id: number) => {
    const request = localRequests.value.find(r => r.id === id);
    if (request) {
        request.status = 'APPROVED';
    }
};

/**
 * Handle rejected event (optimistic UI update)
 */
const handleRejected = (id: number) => {
    const request = localRequests.value.find(r => r.id === id);
    if (request) {
        request.status = 'REJECTED';
    }
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
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Tinjau dan proses permohonan izin siswa
                        </p>
                    </div>
                </div>
            </Motion>
            
            <div class="mx-auto max-w-7xl px-6 py-8 space-y-6">
                <!-- Summary Cards -->
                <div class="grid gap-4 grid-cols-2 md:grid-cols-4">
                    <Motion
                        v-for="(item, index) in [
                            { label: 'Total', value: summary.total, color: 'slate', filter: 'ALL', icon: ClipboardList },
                            { label: 'Menunggu', value: summary.pending, color: 'amber', filter: 'PENDING', icon: Clock },
                            { label: 'Disetujui', value: summary.approved, color: 'emerald', filter: 'APPROVED', icon: CheckCircle2 },
                            { label: 'Ditolak', value: summary.rejected, color: 'red', filter: 'REJECTED', icon: XCircle }
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
                            <div class="flex items-center gap-2 mb-2">
                                <component :is="item.icon" :size="16" :class="`text-${item.color}-500`" />
                                <p class="text-xs font-medium text-slate-600 dark:text-zinc-400">{{ item.label }}</p>
                            </div>
                            <p class="text-2xl font-bold" :class="`text-${item.color}-600`">{{ item.value }}</p>
                        </button>
                    </Motion>
                </div>
                
                <!-- Requests Grid -->
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
                                
                                <div v-if="statusFilter !== 'PENDING'" class="flex items-center gap-2">
                                    <Filter :size="16" class="text-slate-400" />
                                    <span class="text-sm text-slate-600 dark:text-zinc-400">Filter: {{ statusFilter }}</span>
                                    <button
                                        @click="statusFilter = 'PENDING'; haptics.light()"
                                        class="p-1 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                    >
                                        <X :size="14" class="text-slate-500" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Empty State -->
                        <div v-if="filteredRequests.length === 0" class="p-12 text-center">
                            <component
                                :is="statusFilter === 'PENDING' ? Clock : ClipboardList"
                                :size="48"
                                class="mx-auto text-slate-300 dark:text-zinc-700 mb-4"
                            />
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                                {{ statusFilter === 'PENDING' ? 'Tidak Ada Permohonan Pending' : 'Tidak Ada Data' }}
                            </h3>
                            <p class="text-slate-600 dark:text-zinc-400">
                                {{ statusFilter === 'PENDING'
                                    ? 'Semua permohonan izin sudah diproses'
                                    : `Tidak ada permohonan dengan status ${statusFilter}` }}
                            </p>
                        </div>
                        
                        <!-- Requests Cards -->
                        <div v-else class="p-6">
                            <div class="grid gap-4 md:grid-cols-2">
                                <LeaveRequestCard
                                    v-for="request in filteredRequests"
                                    :key="request.id"
                                    :leave-request="request"
                                    @approved="handleApproved"
                                    @rejected="handleRejected"
                                />
                            </div>
                        </div>
                    </div>
                </Motion>
                
                <!-- Info Card -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                >
                    <div class="bg-sky-50 dark:bg-sky-950/30 border border-sky-200 dark:border-sky-800 rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <CheckCircle2 :size="24" class="text-sky-600 dark:text-sky-400 flex-shrink-0 mt-0.5" />
                            <div>
                                <h3 class="text-lg font-semibold text-sky-900 dark:text-sky-100 mb-2">
                                    Informasi Persetujuan Izin
                                </h3>
                                <div class="space-y-2 text-sm text-sky-700 dark:text-sky-300">
                                    <p>
                                        <strong>Persetujuan otomatis mencatat presensi:</strong> Saat Anda menyetujui permohonan izin, 
                                        sistem akan otomatis mencatat presensi siswa sesuai jenis izin (Izin/Sakit) untuk rentang tanggal yang diajukan.
                                    </p>
                                    <p>
                                        <strong>Tinjau lampiran dengan cermat:</strong> Pastikan untuk melihat lampiran (jika ada) sebelum memberikan keputusan.
                                    </p>
                                    <p>
                                        <strong>Berikan alasan jika menolak:</strong> Saat menolak permohonan, mohon berikan alasan yang jelas 
                                        agar orang tua memahami keputusan Anda.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>
