<script setup lang="ts">
/**
 * PaymentStatusBadge - Badge untuk menampilkan status pembayaran
 * dengan support untuk status: belum_bayar, sebagian, lunas, dibatalkan, dan overdue
 */
import { computed } from 'vue';
import { Clock, AlertCircle, CheckCircle2, XCircle, AlertTriangle } from 'lucide-vue-next';

interface Props {
    status: 'belum_bayar' | 'sebagian' | 'lunas' | 'dibatalkan';
    isOverdue?: boolean;
    size?: 'sm' | 'md' | 'lg';
    showIcon?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isOverdue: false,
    size: 'md',
    showIcon: true
});

/**
 * Mapping status ke label teks dan konfigurasi visual
 * untuk memberikan feedback yang jelas kepada user
 */
const statusConfig = computed(() => {
    // If overdue and not paid, show overdue status
    if (props.isOverdue && props.status !== 'lunas' && props.status !== 'dibatalkan') {
        return {
            label: 'Jatuh Tempo',
            icon: AlertTriangle,
            bgClass: 'bg-red-100 dark:bg-red-900/30',
            textClass: 'text-red-700 dark:text-red-400',
            iconClass: 'text-red-500 dark:text-red-400'
        };
    }

    const configs = {
        belum_bayar: {
            label: 'Belum Bayar',
            icon: Clock,
            bgClass: 'bg-amber-100 dark:bg-amber-900/30',
            textClass: 'text-amber-700 dark:text-amber-400',
            iconClass: 'text-amber-500 dark:text-amber-400'
        },
        sebagian: {
            label: 'Sebagian',
            icon: AlertCircle,
            bgClass: 'bg-blue-100 dark:bg-blue-900/30',
            textClass: 'text-blue-700 dark:text-blue-400',
            iconClass: 'text-blue-500 dark:text-blue-400'
        },
        lunas: {
            label: 'Lunas',
            icon: CheckCircle2,
            bgClass: 'bg-emerald-100 dark:bg-emerald-900/30',
            textClass: 'text-emerald-700 dark:text-emerald-400',
            iconClass: 'text-emerald-500 dark:text-emerald-400'
        },
        dibatalkan: {
            label: 'Dibatalkan',
            icon: XCircle,
            bgClass: 'bg-slate-100 dark:bg-zinc-800',
            textClass: 'text-slate-500 dark:text-slate-400',
            iconClass: 'text-slate-400 dark:text-slate-500'
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
            'inline-flex items-center rounded-full font-medium',
            sizeClasses,
            statusConfig.bgClass,
            statusConfig.textClass
        ]"
        role="status"
        :aria-label="`Status pembayaran: ${statusConfig.label}`"
    >
        <component
            v-if="showIcon"
            :is="statusConfig.icon"
            :size="iconSize"
            :class="statusConfig.iconClass"
            aria-hidden="true"
        />
        {{ statusConfig.label }}
    </span>
</template>
