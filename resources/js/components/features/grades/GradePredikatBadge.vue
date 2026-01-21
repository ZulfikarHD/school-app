<script setup lang="ts">
/**
 * GradePredikatBadge - Badge untuk menampilkan predikat nilai (A/B/C/D)
 * dengan warna sesuai standar K13 dan support dark mode
 */
import { computed } from 'vue';

interface Props {
    predikat: 'A' | 'B' | 'C' | 'D' | string;
    showLabel?: boolean;
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    showLabel: false,
    size: 'md'
});

/**
 * Mapping predikat ke label dan konfigurasi visual
 * sesuai standar Kurikulum 2013 (K13)
 */
const predikatConfig = computed(() => {
    const configs: Record<string, { label: string; bgClass: string; textClass: string }> = {
        A: {
            label: 'Sangat Baik',
            bgClass: 'bg-emerald-100 dark:bg-emerald-900/30',
            textClass: 'text-emerald-700 dark:text-emerald-400'
        },
        B: {
            label: 'Baik',
            bgClass: 'bg-blue-100 dark:bg-blue-900/30',
            textClass: 'text-blue-700 dark:text-blue-400'
        },
        C: {
            label: 'Cukup',
            bgClass: 'bg-amber-100 dark:bg-amber-900/30',
            textClass: 'text-amber-700 dark:text-amber-400'
        },
        D: {
            label: 'Kurang',
            bgClass: 'bg-red-100 dark:bg-red-900/30',
            textClass: 'text-red-700 dark:text-red-400'
        }
    };

    return configs[props.predikat.toUpperCase()] || configs.D;
});

/**
 * Size classes untuk badge dengan minimum text size untuk readability
 */
const sizeClasses = computed(() => {
    const sizes = {
        sm: 'text-[11px] px-1.5 py-0.5 min-w-[1.25rem]',
        md: 'text-xs px-2 py-0.5 min-w-[1.5rem]',
        lg: 'text-sm px-2.5 py-1 min-w-[1.75rem]'
    };

    return sizes[props.size];
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center justify-center rounded-full font-bold',
            sizeClasses,
            predikatConfig.bgClass,
            predikatConfig.textClass
        ]"
        role="status"
        :aria-label="`Predikat: ${predikat} - ${predikatConfig.label}`"
    >
        {{ predikat.toUpperCase() }}
        <span v-if="showLabel" class="ml-1 font-medium">
            ({{ predikatConfig.label }})
        </span>
    </span>
</template>
