<script setup lang="ts">
/**
 * Badge Component - Reusable badge untuk menampilkan status, label, dan tags
 * dengan modern clean design yang mengikuti iOS-like design system
 * Support multiple variants, sizes, dan customization options
 */
import { computed } from 'vue';

interface Props {
    /** Variant styling untuk badge */
    variant?: 'default' | 'success' | 'error' | 'warning' | 'info' | 'primary' | 'secondary';
    /** Ukuran badge */
    size?: 'xs' | 'sm' | 'md' | 'lg';
    /** Rounded style - pill untuk fully rounded, square untuk slight rounded */
    rounded?: 'pill' | 'square';
    /** Menampilkan dot indicator di sebelah kiri text */
    dot?: boolean;
    /** Outline variant - hanya border tanpa background fill */
    outline?: boolean;
    /** Soft variant - background dengan opacity rendah */
    soft?: boolean;
    /** Menampilkan badge sebagai removable dengan close button */
    removable?: boolean;
    /** Disabled state */
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
    size: 'md',
    rounded: 'pill',
    dot: false,
    outline: false,
    soft: true,
    removable: false,
    disabled: false,
});

const emit = defineEmits<{
    remove: [];
}>();

/**
 * Size configuration untuk padding, font-size, dan dot size
 * disesuaikan untuk mobile-first responsive design
 */
const sizeClasses = {
    xs: 'px-1.5 py-0.5 text-[10px] leading-tight',
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-1 text-xs',
    lg: 'px-3 py-1.5 text-sm',
};

const dotSizeClasses = {
    xs: 'h-1 w-1',
    sm: 'h-1.5 w-1.5',
    md: 'h-2 w-2',
    lg: 'h-2 w-2',
};

const roundedClasses = {
    pill: 'rounded-full',
    square: 'rounded-md',
};

/**
 * Variant configuration untuk styling
 * menggunakan soft colors untuk modern clean look
 */
const variantClasses = {
    default: {
        solid: 'bg-slate-500 text-white',
        soft: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        outline: 'border border-slate-300 text-slate-600 dark:border-slate-600 dark:text-slate-400',
        dot: 'bg-slate-500',
    },
    primary: {
        solid: 'bg-blue-500 text-white',
        soft: 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        outline: 'border border-blue-300 text-blue-600 dark:border-blue-700 dark:text-blue-400',
        dot: 'bg-blue-500',
    },
    secondary: {
        solid: 'bg-violet-500 text-white',
        soft: 'bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
        outline: 'border border-violet-300 text-violet-600 dark:border-violet-700 dark:text-violet-400',
        dot: 'bg-violet-500',
    },
    success: {
        solid: 'bg-emerald-500 text-white',
        soft: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        outline: 'border border-emerald-300 text-emerald-600 dark:border-emerald-700 dark:text-emerald-400',
        dot: 'bg-emerald-500',
    },
    error: {
        solid: 'bg-red-500 text-white',
        soft: 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        outline: 'border border-red-300 text-red-600 dark:border-red-700 dark:text-red-400',
        dot: 'bg-red-500',
    },
    warning: {
        solid: 'bg-amber-500 text-white',
        soft: 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        outline: 'border border-amber-300 text-amber-600 dark:border-amber-700 dark:text-amber-400',
        dot: 'bg-amber-500',
    },
    info: {
        solid: 'bg-sky-500 text-white',
        soft: 'bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400',
        outline: 'border border-sky-300 text-sky-600 dark:border-sky-700 dark:text-sky-400',
        dot: 'bg-sky-500',
    },
};

/**
 * Computed class untuk badge styling berdasarkan props
 */
const badgeClasses = computed(() => {
    const variant = variantClasses[props.variant];
    let styleClass: string;

    if (props.outline) {
        styleClass = variant.outline;
    } else if (props.soft) {
        styleClass = variant.soft;
    } else {
        styleClass = variant.solid;
    }

    return [
        // Base styles
        'inline-flex items-center gap-1.5 font-medium transition-all duration-200',
        // Size
        sizeClasses[props.size],
        // Rounded
        roundedClasses[props.rounded],
        // Variant style
        styleClass,
        // Disabled state
        props.disabled && 'opacity-50 cursor-not-allowed',
    ];
});

/**
 * Computed class untuk dot indicator
 */
const dotClasses = computed(() => [
    'rounded-full shrink-0',
    dotSizeClasses[props.size],
    variantClasses[props.variant].dot,
]);

/**
 * Handle remove badge
 */
const handleRemove = () => {
    if (!props.disabled) {
        emit('remove');
    }
};
</script>

<template>
    <span :class="badgeClasses">
        <!-- Dot Indicator -->
        <span
            v-if="dot"
            :class="dotClasses"
            aria-hidden="true"
        />

        <!-- Left Icon Slot -->
        <slot name="icon-left" />

        <!-- Content -->
        <slot />

        <!-- Right Icon Slot -->
        <slot name="icon-right" />

        <!-- Remove Button -->
        <button
            v-if="removable && !disabled"
            type="button"
            @click.stop="handleRemove"
            class="-mr-0.5 ml-0.5 inline-flex shrink-0 items-center justify-center rounded-full p-0.5 transition-colors hover:bg-black/10 focus:outline-none focus:ring-1 focus:ring-current dark:hover:bg-white/10"
            aria-label="Hapus"
        >
            <svg
                class="h-3 w-3"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.5"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>
    </span>
</template>
