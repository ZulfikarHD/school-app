<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { Users, CreditCard, FileText, UserCog, GraduationCap, ChevronRight } from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import { index as adminUsersIndex } from '@/routes/admin/users';
import { index as adminStudentsIndex } from '@/routes/admin/students';

/**
 * Dashboard untuk Admin/TU dengan akses ke Student Management,
 * Payment Management, PSB Registration, dan User Management
 * dengan iOS-like staggered animations dan haptic feedback
 *
 * UX Enhancement:
 * - Standardized Lucide icons
 * - Quick actions dengan navigasi aktual atau "Segera Hadir" state
 * - Removed duplicate header (using AppLayout slot)
 * - Stat cards dengan link ke halaman terkait
 * - Focus states untuk accessibility
 */

interface Props {
    stats: {
        total_students: number;
        total_payments: number;
        pending_psb: number;
        total_users: number;
    };
}

defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

/**
 * Handle card click dengan haptic feedback
 */
const handleCardClick = () => {
    haptics.light();
};

/**
 * Show coming soon modal untuk fitur yang belum tersedia
 */
const showComingSoon = (featureName: string) => {
    haptics.light();
    modal.info('Segera Hadir', `Fitur ${featureName} akan segera tersedia dalam pembaruan berikutnya.`);
};

/**
 * Stat cards configuration untuk konsistensi dan maintainability
 */
const statCards = [
    {
        key: 'students',
        label: 'Total Siswa',
        statKey: 'total_students',
        icon: GraduationCap,
        bgColor: 'bg-blue-100 dark:bg-blue-500/20',
        iconColor: 'text-blue-600 dark:text-blue-400',
        route: adminStudentsIndex,
        linkText: 'Lihat data siswa',
    },
    {
        key: 'payments',
        label: 'Total Pembayaran',
        statKey: 'total_payments',
        icon: CreditCard,
        bgColor: 'bg-green-100 dark:bg-green-500/20',
        iconColor: 'text-green-600 dark:text-green-400',
        route: null, // Coming soon
        linkText: 'Segera hadir',
    },
    {
        key: 'psb',
        label: 'PSB Pending',
        statKey: 'pending_psb',
        icon: FileText,
        bgColor: 'bg-amber-100 dark:bg-amber-500/20',
        iconColor: 'text-amber-600 dark:text-amber-400',
        route: null, // Coming soon
        linkText: 'Segera hadir',
    },
    {
        key: 'users',
        label: 'Total User',
        statKey: 'total_users',
        icon: UserCog,
        bgColor: 'bg-purple-100 dark:bg-purple-500/20',
        iconColor: 'text-purple-600 dark:text-purple-400',
        route: adminUsersIndex,
        linkText: 'Kelola user',
    },
];

/**
 * Quick action cards configuration
 */
const quickActions = [
    {
        key: 'student-management',
        title: 'Manajemen Siswa',
        description: 'Kelola data siswa dan akademik',
        icon: GraduationCap,
        bgColor: 'bg-blue-100 dark:bg-blue-500/20',
        iconColor: 'text-blue-600 dark:text-blue-400',
        hoverBorder: 'hover:border-blue-200 dark:hover:border-blue-700',
        route: adminStudentsIndex,
    },
    {
        key: 'user-management',
        title: 'Manajemen User',
        description: 'Kelola akun dan akses pengguna',
        icon: Users,
        bgColor: 'bg-purple-100 dark:bg-purple-500/20',
        iconColor: 'text-purple-600 dark:text-purple-400',
        hoverBorder: 'hover:border-purple-200 dark:hover:border-purple-700',
        route: adminUsersIndex,
    },
    {
        key: 'payment',
        title: 'Pembayaran',
        description: 'Kelola pembayaran SPP dan lainnya',
        icon: CreditCard,
        bgColor: 'bg-green-100 dark:bg-green-500/20',
        iconColor: 'text-green-600 dark:text-green-400',
        hoverBorder: 'hover:border-green-200 dark:hover:border-green-700',
        route: null, // Coming soon
        comingSoon: true,
    },
    {
        key: 'psb',
        title: 'PSB',
        description: 'Penerimaan Siswa Baru',
        icon: FileText,
        bgColor: 'bg-amber-100 dark:bg-amber-500/20',
        iconColor: 'text-amber-600 dark:text-amber-400',
        hoverBorder: 'hover:border-amber-200 dark:hover:border-amber-700',
        route: null, // Coming soon
        comingSoon: true,
    },
];
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Admin" />

        <div class="space-y-6">
            <!-- Stats Grid dengan staggered animations -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Motion
                    v-for="(card, index) in statCards"
                    :key="card.key"
                    :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: index * 0.05 }"
                    :whileHover="{ y: -2, scale: 1.01 }"
                    :whileTap="{ scale: 0.97 }"
                >
                    <!-- Card dengan navigasi -->
                    <Link
                        v-if="card.route"
                        :href="card.route().url"
                        @click="handleCardClick"
                        class="block overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800 hover:border-emerald-200 dark:hover:border-emerald-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    >
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                        {{ card.label }}
                                    </p>
                                    <p class="mt-1.5 text-3xl font-bold text-slate-900 dark:text-white tabular-nums">
                                        {{ (stats as any)[card.statKey] }}
                                    </p>
                                </div>
                                <div :class="['p-3 rounded-xl', card.bgColor]">
                                    <component :is="card.icon" :size="24" :class="card.iconColor" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-center text-xs text-slate-500 dark:text-slate-400">
                                <span>{{ card.linkText }}</span>
                                <ChevronRight :size="14" class="ml-1" />
                            </div>
                        </div>
                    </Link>

                    <!-- Card tanpa navigasi (coming soon) -->
                    <div
                        v-else
                        class="overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 dark:bg-zinc-900 dark:border-zinc-800"
                    >
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                        {{ card.label }}
                                    </p>
                                    <p class="mt-1.5 text-3xl font-bold text-slate-900 dark:text-white tabular-nums">
                                        {{ (stats as any)[card.statKey] }}
                                    </p>
                                </div>
                                <div :class="['p-3 rounded-xl', card.bgColor]">
                                    <component :is="card.icon" :size="24" :class="card.iconColor" />
                                </div>
                            </div>
                            <div class="mt-3 flex items-center text-xs">
                                <span class="text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-0.5 rounded-full">
                                    Segera Hadir
                                </span>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>

            <!-- Quick Actions -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
            >
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Aksi Cepat</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Motion
                        v-for="(action, index) in quickActions"
                        :key="action.key"
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 + (index * 0.05) }"
                        :whileHover="{ y: -2, scale: 1.01 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <!-- Action dengan navigasi -->
                        <Link
                            v-if="action.route"
                            :href="action.route().url"
                            @click="handleCardClick"
                            :class="[
                                'block rounded-xl bg-white p-5 shadow-sm border border-slate-200 transition-colors dark:bg-zinc-900 dark:border-zinc-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2',
                                action.hoverBorder
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <div :class="['p-3 rounded-xl shrink-0', action.bgColor]">
                                    <component :is="action.icon" :size="24" :class="action.iconColor" />
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-slate-900 dark:text-white truncate">
                                        {{ action.title }}
                                    </h3>
                                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400 truncate">
                                        {{ action.description }}
                                    </p>
                                </div>
                            </div>
                        </Link>

                        <!-- Action coming soon -->
                        <button
                            v-else
                            @click="showComingSoon(action.title)"
                            :class="[
                                'w-full text-left rounded-xl bg-white p-5 shadow-sm border border-slate-200 transition-colors dark:bg-zinc-900 dark:border-zinc-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2',
                                action.hoverBorder
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <div :class="['p-3 rounded-xl shrink-0', action.bgColor]">
                                    <component :is="action.icon" :size="24" :class="action.iconColor" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-slate-900 dark:text-white truncate">
                                            {{ action.title }}
                                        </h3>
                                        <span class="text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-0.5 rounded-full shrink-0">
                                            Segera
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400 truncate">
                                        {{ action.description }}
                                    </p>
                                </div>
                            </div>
                        </button>
                    </Motion>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
