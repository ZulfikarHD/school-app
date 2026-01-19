<script setup lang="ts">
import { computed } from 'vue';
import { Clock, CheckCircle2, XCircle } from 'lucide-vue-next';

interface Props {
    status: 'PENDING' | 'APPROVED' | 'REJECTED';
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md'
});

/**
 * Mapping status permohonan izin ke visual feedback
 * dengan color coding yang konsisten dengan design system
 */
const statusConfig = computed(() => {
    const configs = {
        PENDING: {
            label: 'Menunggu',
            icon: Clock,
            bgClass: 'bg-amber-50/80 dark:bg-amber-950/30',
            textClass: 'text-amber-700 dark:text-amber-400',
            iconClass: 'text-amber-500',
            pulseClass: 'animate-pulse'
        },
        APPROVED: {
            label: 'Disetujui',
            icon: CheckCircle2,
            bgClass: 'bg-emerald-50/80 dark:bg-emerald-950/30',
            textClass: 'text-emerald-700 dark:text-emerald-400',
            iconClass: 'text-emerald-500',
            pulseClass: ''
        },
        REJECTED: {
            label: 'Ditolak',
            icon: XCircle,
            bgClass: 'bg-red-50/80 dark:bg-red-950/30',
            textClass: 'text-red-700 dark:text-red-400',
            iconClass: 'text-red-500',
            pulseClass: ''
        }
    };
    
    return configs[props.status];
});

/**
 * Size classes untuk badge dengan minimum text size 11px
 * untuk memastikan readability di berbagai device
 */
const sizeClasses = computed(() => {
    const sizes = {
        sm: 'text-[11px] px-2 py-0.5 gap-1',
        md: 'text-xs px-2.5 py-1 gap-1.5',
        lg: 'text-sm px-3 py-1.5 gap-2'
    };
    
    return sizes[props.size];
});

const iconSize = computed(() => {
    const sizes = {
        sm: 12,
        md: 14,
        lg: 16
    };
    
    return sizes[props.size];
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center rounded-lg font-semibold tracking-wide uppercase',
            'border border-slate-200/50 dark:border-zinc-800',
            sizeClasses,
            statusConfig.bgClass,
            statusConfig.textClass,
            statusConfig.pulseClass
        ]"
        role="status"
        :aria-label="`Status permohonan: ${statusConfig.label}`"
    >
        <component :is="statusConfig.icon" :size="iconSize" :class="statusConfig.iconClass" aria-hidden="true" />
        {{ statusConfig.label }}
    </span>
</template>
