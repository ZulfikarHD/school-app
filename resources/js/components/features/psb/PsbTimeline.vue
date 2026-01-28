<script setup lang="ts">
/**
 * PsbTimeline - Komponen timeline tracking status pendaftaran
 *
 * Komponen ini bertujuan untuk menampilkan progress pendaftaran
 * secara visual dengan tahapan-tahapan yang sudah dan belum selesai
 */
import { Check, Clock, X, AlertCircle } from 'lucide-vue-next';

interface TimelineItem {
    step: string;
    label: string;
    completed: boolean;
    current: boolean;
    date: string | null;
}

interface Props {
    timeline: TimelineItem[];
    status?: string;
    rejectionReason?: string | null;
}

const props = defineProps<Props>();

/**
 * Get icon component berdasarkan status item
 */
const getIcon = (item: TimelineItem): typeof Check => {
    if (item.completed && !item.current) return Check;
    if (item.current) return Clock;
    return Clock;
};

/**
 * Get warna berdasarkan status
 */
const getStatusColor = (status: string): string => {
    switch (status) {
        case 'approved':
            return 'emerald';
        case 'rejected':
            return 'red';
        case 'waiting_list':
            return 'purple';
        case 'pending':
        case 'document_review':
            return 'amber';
        case 're_registration':
        case 'completed':
            return 'emerald';
        default:
            return 'slate';
    }
};
</script>

<template>
    <div class="relative">
        <!-- Rejection Alert -->
        <div
            v-if="status === 'rejected' && rejectionReason"
            class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4"
        >
            <div class="flex gap-3">
                <X class="h-5 w-5 shrink-0 text-red-500" />
                <div>
                    <p class="font-medium text-red-800">Pendaftaran Ditolak</p>
                    <p class="mt-1 text-sm text-red-700">{{ rejectionReason }}</p>
                </div>
            </div>
        </div>

        <!-- Waiting List Alert -->
        <div
            v-if="status === 'waiting_list'"
            class="mb-6 rounded-xl border border-purple-200 bg-purple-50 p-4"
        >
            <div class="flex gap-3">
                <AlertCircle class="h-5 w-5 shrink-0 text-purple-500" />
                <div>
                    <p class="font-medium text-purple-800">Masuk Waiting List</p>
                    <p class="mt-1 text-sm text-purple-700">
                        Anda masuk dalam daftar tunggu. Kami akan menghubungi jika ada kuota tersedia.
                    </p>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <ol class="relative border-l-2 border-slate-200">
            <li
                v-for="(item, index) in timeline"
                :key="item.step"
                class="mb-8 ml-6 last:mb-0"
            >
                <!-- Timeline dot -->
                <span
                    class="absolute -left-[17px] flex h-8 w-8 items-center justify-center rounded-full ring-4 ring-white transition-all duration-300"
                    :class="{
                        'bg-emerald-500 text-white': item.completed && !item.current,
                        'bg-emerald-100 text-emerald-600 ring-emerald-50': item.current && status !== 'rejected',
                        'bg-red-100 text-red-600 ring-red-50': item.current && status === 'rejected',
                        'bg-slate-100 text-slate-400': !item.completed && !item.current,
                    }"
                >
                    <Check v-if="item.completed && !item.current" class="h-4 w-4" />
                    <X v-else-if="item.current && status === 'rejected'" class="h-4 w-4" />
                    <Clock v-else-if="item.current" class="h-4 w-4" />
                    <span v-else class="text-xs font-semibold">{{ index + 1 }}</span>
                </span>

                <!-- Content -->
                <div
                    class="rounded-xl p-4 transition-all"
                    :class="{
                        'bg-emerald-50 border border-emerald-100': item.current && status !== 'rejected' && status !== 'waiting_list',
                        'bg-red-50 border border-red-100': item.current && status === 'rejected',
                        'bg-purple-50 border border-purple-100': item.current && status === 'waiting_list',
                        'bg-white border border-slate-100': !item.current,
                    }"
                >
                    <h3
                        class="font-semibold"
                        :class="{
                            'text-emerald-900': item.current && status !== 'rejected' && status !== 'waiting_list',
                            'text-red-900': item.current && status === 'rejected',
                            'text-purple-900': item.current && status === 'waiting_list',
                            'text-slate-900': item.completed && !item.current,
                            'text-slate-500': !item.completed && !item.current,
                        }"
                    >
                        {{ item.label }}
                    </h3>
                    <p
                        v-if="item.date"
                        class="mt-1 text-sm"
                        :class="{
                            'text-emerald-600': item.current && status !== 'rejected',
                            'text-red-600': item.current && status === 'rejected',
                            'text-slate-500': !item.current,
                        }"
                    >
                        {{ item.date }}
                    </p>
                    <p
                        v-else-if="item.current"
                        class="mt-1 text-sm"
                        :class="{
                            'text-emerald-600': status !== 'rejected' && status !== 'waiting_list',
                            'text-red-600': status === 'rejected',
                            'text-purple-600': status === 'waiting_list',
                        }"
                    >
                        Sedang dalam proses
                    </p>
                </div>
            </li>
        </ol>
    </div>
</template>
