<script setup lang="ts">
/**
 * Admin PSB Dashboard Page
 *
 * Halaman dashboard untuk mengelola dan memantau pendaftaran siswa baru,
 * yaitu: statistik, quick actions, dan recent registrations
 */
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import Badge from '@/components/ui/Badge.vue';
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
    Trophy,
    Megaphone,
    CreditCard,
    Settings,
    Download,
    Calendar,
    ArrowRight
} from 'lucide-vue-next';
import { index as registrationsIndex } from '@/routes/admin/psb/registrations';
import { index as settingsIndex } from '@/routes/admin/psb/settings';
import { index as announcementsIndex } from '@/routes/admin/psb/announcements';
import { index as paymentsIndex } from '@/routes/admin/psb/payments';
import { exportMethod as exportData } from '@/routes/admin/psb';

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

/**
 * Interface untuk recent registration
 */
interface RecentRegistration {
    id: number;
    registration_number: string;
    student_name: string;
    status: string;
    status_label: string;
    created_at: string;
}

/**
 * Interface untuk active settings
 */
interface ActiveSettings {
    id: number;
    academic_year: string;
    registration_open_date: string;
    registration_close_date: string;
    announcement_date: string;
    is_registration_open: boolean;
    formatted_fee: string;
    quota_per_class: number;
}

interface Props {
    title: string;
    stats: Stats;
    recentRegistrations: RecentRegistration[];
    activeSettings: ActiveSettings | null;
}

const props = defineProps<Props>();

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
 * Quick actions untuk navigasi cepat
 */
const quickActions = [
    {
        label: 'Verifikasi Pending',
        icon: Clock,
        href: () => `${registrationsIndex().url}?status=pending`,
        color: 'amber',
        badge: () => props.stats.pending,
    },
    {
        label: 'Pengumuman',
        icon: Megaphone,
        href: announcementsIndex,
        color: 'violet',
        badge: () => props.stats.approved,
    },
    {
        label: 'Verifikasi Bayar',
        icon: CreditCard,
        href: paymentsIndex,
        color: 'blue',
        badge: null,
    },
    {
        label: 'Pengaturan',
        icon: Settings,
        href: settingsIndex,
        color: 'slate',
        badge: null,
    },
    {
        label: 'Export Data',
        icon: Download,
        href: exportData,
        color: 'green',
        badge: null,
        isDownload: true,
    },
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
        violet: {
            bg: 'bg-violet-50 dark:bg-violet-950/30',
            text: 'text-violet-900 dark:text-violet-100',
            icon: 'text-violet-500 dark:text-violet-400',
            border: 'border-violet-200 dark:border-violet-800',
        },
        slate: {
            bg: 'bg-slate-50 dark:bg-zinc-800/50',
            text: 'text-slate-900 dark:text-slate-100',
            icon: 'text-slate-500 dark:text-slate-400',
            border: 'border-slate-200 dark:border-zinc-700',
        },
        green: {
            bg: 'bg-green-50 dark:bg-green-950/30',
            text: 'text-green-900 dark:text-green-100',
            icon: 'text-green-500 dark:text-green-400',
            border: 'border-green-200 dark:border-green-800',
        },
    };
    return colors[color] || colors.emerald;
};

/**
 * Get status badge variant
 */
const getStatusVariant = (status: string): string => {
    const variants: Record<string, string> = {
        pending: 'amber',
        document_review: 'blue',
        approved: 'emerald',
        rejected: 'red',
        waiting_list: 'orange',
        re_registration: 'purple',
        completed: 'teal',
    };
    return variants[status] || 'gray';
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

            <!-- Active Period Info -->
            <Motion
                v-if="activeSettings"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <Calendar :size="20" class="text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Periode {{ activeSettings.academic_year }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ activeSettings.registration_open_date }} - {{ activeSettings.registration_close_date }}
                                </p>
                            </div>
                        </div>
                        <Badge
                            :variant="activeSettings.is_registration_open ? 'emerald' : 'amber'"
                            size="sm"
                            dot
                        >
                            {{ activeSettings.is_registration_open ? 'Pendaftaran Dibuka' : 'Pendaftaran Ditutup' }}
                        </Badge>
                    </div>
                </div>
            </Motion>

            <!-- Quick Actions -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.08 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-4">Aksi Cepat</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        <component
                            v-for="action in quickActions"
                            :key="action.label"
                            :is="action.isDownload ? 'a' : Link"
                            :href="typeof action.href === 'function' ? action.href().url : action.href"
                            :download="action.isDownload"
                            :class="[
                                'relative flex flex-col items-center gap-2 p-4 rounded-xl border transition-all hover:scale-[1.02] active:scale-[0.98]',
                                getColorClasses(action.color).bg,
                                getColorClasses(action.color).border,
                            ]"
                        >
                            <div
                                :class="[
                                    'w-10 h-10 rounded-xl flex items-center justify-center',
                                    action.color === 'slate' ? 'bg-slate-200/50 dark:bg-zinc-700' : `bg-white/50 dark:bg-white/10`,
                                ]"
                            >
                                <component
                                    :is="action.icon"
                                    :size="20"
                                    :class="getColorClasses(action.color).icon"
                                />
                            </div>
                            <span
                                class="text-xs font-medium text-center"
                                :class="getColorClasses(action.color).text"
                            >
                                {{ action.label }}
                            </span>
                            <!-- Badge -->
                            <span
                                v-if="action.badge && action.badge() > 0"
                                class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 flex items-center justify-center text-[10px] font-bold text-white bg-red-500 rounded-full"
                            >
                                {{ action.badge() }}
                            </span>
                        </component>
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

            <!-- Recent Registrations -->
            <Motion
                v-if="recentRegistrations && recentRegistrations.length > 0"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.4 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <h2 class="font-semibold text-slate-900 dark:text-slate-100">Pendaftaran Terbaru</h2>
                            <Link
                                :href="registrationsIndex().url"
                                class="text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:underline inline-flex items-center gap-1"
                            >
                                <span>Lihat Semua</span>
                                <ArrowRight :size="14" />
                            </Link>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <Link
                            v-for="reg in recentRegistrations"
                            :key="reg.id"
                            :href="`${registrationsIndex().url}/${reg.id}`"
                            class="flex items-center justify-between p-4 sm:px-6 hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-slate-900 dark:text-slate-100 truncate">
                                    {{ reg.student_name }}
                                </p>
                                <div class="flex items-center gap-2 mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    <span class="font-mono">{{ reg.registration_number }}</span>
                                    <span>•</span>
                                    <span>{{ reg.created_at }}</span>
                                </div>
                            </div>
                            <Badge
                                :variant="getStatusVariant(reg.status) as any"
                                size="xs"
                            >
                                {{ reg.status_label }}
                            </Badge>
                        </Link>
                    </div>
                </div>
            </Motion>

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
