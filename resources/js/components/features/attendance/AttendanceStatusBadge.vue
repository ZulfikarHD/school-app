<script setup lang="ts">
import { computed } from 'vue';
import { CheckCircle2, AlertCircle, HeartPulse, XCircle } from 'lucide-vue-next';

interface Props {
    status: 'H' | 'I' | 'S' | 'A';
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md'
});

/**
 * Mapping status ke label teks dan konfigurasi visual
 * untuk memberikan feedback yang jelas kepada user
 */
const statusConfig = computed(() => {
    const configs = {
        H: {
            label: 'Hadir',
            icon: CheckCircle2,
            bgClass: 'bg-emerald-50/80 dark:bg-emerald-950/30',
            textClass: 'text-emerald-700 dark:text-emerald-400',
            iconClass: 'text-emerald-500'
        },
        I: {
            label: 'Izin',
            icon: AlertCircle,
            bgClass: 'bg-amber-50/80 dark:bg-amber-950/30',
            textClass: 'text-amber-700 dark:text-amber-400',
            iconClass: 'text-amber-500'
        },
        S: {
            label: 'Sakit',
            icon: HeartPulse,
            bgClass: 'bg-sky-50/80 dark:bg-sky-950/30',
            textClass: 'text-sky-700 dark:text-sky-400',
            iconClass: 'text-sky-500'
        },
        A: {
            label: 'Alpha',
            icon: XCircle,
            bgClass: 'bg-red-50/80 dark:bg-red-950/30',
            textClass: 'text-red-700 dark:text-red-400',
            iconClass: 'text-red-500'
        }
    };
    
    return configs[props.status];
});

const sizeClasses = computed(() => {
    const sizes = {
        sm: 'text-[10px] px-2 py-0.5 gap-1',
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
            statusConfig.textClass
        ]"
    >
        <component :is="statusConfig.icon" :size="iconSize" :class="statusConfig.iconClass" />
        {{ statusConfig.label }}
    </span>
</template>
