<script setup lang="ts">
/**
 * ReportCardStatusBadge - Badge untuk menampilkan status rapor
 * dengan warna yang sesuai untuk setiap status
 */

interface Props {
    status: string;
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    size: 'sm'
});

/**
 * Mapping status ke label Indonesia
 */
const statusLabels: Record<string, string> = {
    'DRAFT': 'Draft',
    'PENDING_APPROVAL': 'Menunggu Persetujuan',
    'APPROVED': 'Disetujui',
    'RELEASED': 'Dirilis'
};

/**
 * Mapping status ke warna badge
 */
const statusColors: Record<string, string> = {
    'DRAFT': 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-slate-300',
    'PENDING_APPROVAL': 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    'APPROVED': 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    'RELEASED': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
};

/**
 * Mapping size ke class tailwind
 */
const sizeClasses: Record<string, string> = {
    'sm': 'px-2 py-0.5 text-xs',
    'md': 'px-2.5 py-1 text-sm',
    'lg': 'px-3 py-1.5 text-sm'
};

const label = statusLabels[props.status] || props.status;
const colorClass = statusColors[props.status] || statusColors['DRAFT'];
const sizeClass = sizeClasses[props.size];
</script>

<template>
    <span
        :class="[
            'inline-flex items-center font-medium rounded-full',
            colorClass,
            sizeClass
        ]"
    >
        {{ label }}
    </span>
</template>
