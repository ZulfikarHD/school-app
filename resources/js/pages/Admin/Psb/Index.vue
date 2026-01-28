<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import {
    UserPlus,
    Clock,
    FileCheck,
    CheckCircle,
    XCircle,
    Users,
    ChevronRight,
    AlertTriangle,
    RefreshCw,
    Trophy
} from 'lucide-vue-next';
import { index as registrationsIndex } from '@/routes/admin/psb/registrations';

/**
 * Interface untuk statistik PSB
 */
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

interface Props {
    title: string;
    stats: Stats;
}

defineProps<Props>();

/**
 * Konfigurasi stat cards untuk dashboard
 */
const statCards = [
    { key: 'pending', label: 'Menunggu Verifikasi', icon: Clock, color: 'amber', status: 'pending' },
    { key: 'document_review', label: 'Verifikasi Dokumen', icon: FileCheck, color: 'blue', status: 'document_review' },
    { key: 'approved', label: 'Diterima', icon: CheckCircle, color: 'emerald', status: 'approved' },
    { key: 'rejected', label: 'Ditolak', icon: XCircle, color: 'red', status: 'rejected' },
    { key: 'waiting_list', label: 'Waiting List', icon: AlertTriangle, color: 'orange', status: 'waiting_list' },
    { key: 're_registration', label: 'Daftar Ulang', icon: RefreshCw, color: 'purple', status: 're_registration' },
    { key: 'completed', label: 'Selesai', icon: Trophy, color: 'teal', status: 'completed' },
];

/**
 * Get color classes berdasarkan nama warna
 */
const getColorClasses = (color: string) => {
    const colors: Record<string, { bg: string; text: string; icon: string; border: string }> = {
        amber: {
            bg: 'bg-amber-50 dark:bg-amber-950/30',
            text: 'text-amber-900 dark:text-amber-100',
            icon: 'text-amber-500 dark:text-amber-400',
            border: 'border-amber-200 dark:border-amber-800',
        },
        blue: {
            bg: 'bg-blue-50 dark:bg-blue-950/30',
            text: 'text-blue-900 dark:text-blue-100',
            icon: 'text-blue-500 dark:text-blue-400',
            border: 'border-blue-200 dark:border-blue-800',
        },
        emerald: {
            bg: 'bg-emerald-50 dark:bg-emerald-950/30',
            text: 'text-emerald-900 dark:text-emerald-100',
            icon: 'text-emerald-500 dark:text-emerald-400',
            border: 'border-emerald-200 dark:border-emerald-800',
        },
        red: {
            bg: 'bg-red-50 dark:bg-red-950/30',
            text: 'text-red-900 dark:text-red-100',
            icon: 'text-red-500 dark:text-red-400',
            border: 'border-red-200 dark:border-red-800',
        },
        orange: {
            bg: 'bg-orange-50 dark:bg-orange-950/30',
            text: 'text-orange-900 dark:text-orange-100',
            icon: 'text-orange-500 dark:text-orange-400',
            border: 'border-orange-200 dark:border-orange-800',
        },
        purple: {
            bg: 'bg-purple-50 dark:bg-purple-950/30',
            text: 'text-purple-900 dark:text-purple-100',
            icon: 'text-purple-500 dark:text-purple-400',
            border: 'border-purple-200 dark:border-purple-800',
        },
        teal: {
            bg: 'bg-teal-50 dark:bg-teal-950/30',
            text: 'text-teal-900 dark:text-teal-100',
            icon: 'text-teal-500 dark:text-teal-400',
            border: 'border-teal-200 dark:border-teal-800',
        },
    };
    return colors[color] || colors.emerald;
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
                        <div class="flex-1">
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">{{ title }}</h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                Kelola dan verifikasi pendaftaran siswa baru
                            </p>
                        </div>
                        <Link
                            :href="registrationsIndex().url"
                            class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-medium transition-colors shadow-sm active:scale-97"
                        >
                            <Users :size="18" />
                            <span>Lihat Semua</span>
                        </Link>
                    </div>
                </div>
            </Motion>

            <!-- Total Card -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <Link
                    :href="registrationsIndex().url"
                    class="block bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-zinc-800 hover:scale-[1.01] transition-transform"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Pendaftaran</p>
                            <p class="text-4xl font-bold text-slate-900 dark:text-white mt-1">{{ stats.total }}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">Tahun ajaran aktif</p>
                        </div>
                        <div class="w-16 h-16 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <Users :size="32" class="text-emerald-600 dark:text-emerald-400" />
                        </div>
                    </div>
                </Link>
            </Motion>

            <!-- Stats Grid -->
            <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
                <Motion
                    v-for="(card, index) in statCards"
                    :key="card.key"
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 + (index * 0.05) }"
                >
                    <Link
                        :href="`${registrationsIndex().url}?status=${card.status}`"
                        :class="[
                            'group block rounded-2xl border shadow-sm p-4 sm:p-5 transition-all duration-200',
                            'hover:scale-[1.02] active:scale-[0.98]',
                            getColorClasses(card.color).bg,
                            getColorClasses(card.color).border,
                        ]"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <component
                                        :is="card.icon"
                                        :size="14"
                                        :class="getColorClasses(card.color).icon"
                                    />
                                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wide"
                                       :class="getColorClasses(card.color).icon">
                                        {{ card.label }}
                                    </p>
                                </div>
                                <p class="text-2xl sm:text-3xl font-bold tabular-nums"
                                   :class="getColorClasses(card.color).text">
                                    {{ stats[card.key as keyof Stats] }}
                                </p>
                            </div>
                            <ChevronRight
                                :size="16"
                                :class="[
                                    'transition-transform shrink-0 mt-1 group-hover:translate-x-1',
                                    getColorClasses(card.color).icon,
                                ]"
                            />
                        </div>
                    </Link>
                </Motion>
            </div>

            <!-- Mobile CTA -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.5 }"
                class="sm:hidden"
            >
                <Link
                    :href="registrationsIndex().url"
                    class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-medium transition-colors shadow-sm active:scale-97"
                >
                    <Users :size="18" />
                    <span>Lihat Semua Pendaftaran</span>
                </Link>
            </Motion>
        </div>
    </AppLayout>
</template>
