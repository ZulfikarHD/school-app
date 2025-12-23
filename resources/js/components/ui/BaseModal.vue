<script setup lang="ts">
import { computed, watch, onMounted, onUnmounted } from 'vue';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { useTransition } from '@/composables/useTransition';

/**
 * Base Modal Component dengan iOS-like design dan spring animations
 * Support multiple sizes, glass effect, dan backdrop blur
 * dengan haptic feedback untuk better user experience
 */

interface Props {
    show?: boolean;
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'full';
    closeOnBackdrop?: boolean;
    closeOnEscape?: boolean;
    showCloseButton?: boolean;
    title?: string;
    preventScroll?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    show: false,
    size: 'md',
    closeOnBackdrop: true,
    closeOnEscape: true,
    showCloseButton: true,
    preventScroll: true,
});

const emit = defineEmits<{
    close: [];
    open: [];
}>();

const haptics = useHaptics();
const { onTransitionEnd } = useTransition();

/**
 * Computed class untuk responsive modal size
 * dengan mobile-first approach
 */
const modalSizeClass = computed(() => {
    const sizes = {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl',
        full: 'max-w-full mx-4',
    };
    return sizes[props.size];
});

/**
 * Handle close modal dengan haptic feedback
 */
const closeModal = () => {
    haptics.light();
    emit('close');
};

/**
 * Handle backdrop click dengan validation
 */
const handleBackdropClick = () => {
    if (props.closeOnBackdrop) {
        closeModal();
    }
};

/**
 * Handle escape key press untuk close modal
 */
const handleEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.closeOnEscape && props.show) {
        closeModal();
    }
};

/**
 * Prevent body scroll saat modal open
 * untuk better mobile UX
 */
watch(
    () => props.show,
    (isOpen) => {
        if (props.preventScroll) {
            if (isOpen) {
                document.body.style.overflow = 'hidden';
                haptics.light();
                emit('open');
            } else {
                document.body.style.overflow = '';
            }
        }
    },
);

onMounted(() => {
    document.addEventListener('keydown', handleEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape);
    document.body.style.overflow = '';
});
</script>

<template>
    <!-- Modal Overlay -->
    <Teleport to="body">
        <Transition
            :css="false"
            @enter="onTransitionEnd()"
            @leave="onTransitionEnd()"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
            >
                <!-- Backdrop dengan fade animation -->
                <Motion
                    :initial="{ opacity: 0 }"
                    :animate="{ opacity: 1 }"
                    :exit="{ opacity: 0 }"
                    :transition="{ duration: 0.2 }"
                >
                    <div
                        class="fixed inset-0 bg-black/50"
                        @click="handleBackdropClick"
                    />
                </Motion>

                <!-- Modal Content dengan spring animation -->
                <Motion
                    :initial="{ opacity: 0, scale: 0.95, y: 20 }"
                    :animate="{ opacity: 1, scale: 1, y: 0 }"
                    :exit="{ opacity: 0, scale: 0.95, y: 20 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
                    :class="['relative z-10 w-full', modalSizeClass]"
                >
                    <div
                        class="overflow-hidden rounded-3xl bg-white/98 shadow-xl dark:bg-zinc-900/98 border border-gray-100 dark:border-zinc-800"
                    >
                        <!-- Header dengan glass effect -->
                        <div
                            v-if="title || showCloseButton || $slots.header"
                            class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-zinc-800"
                        >
                            <!-- Title atau Custom Header -->
                            <slot name="header">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ title }}
                                </h3>
                            </slot>

                            <!-- Close Button dengan press feedback -->
                            <Motion
                                v-if="showCloseButton"
                                :whileTap="{ scale: 0.97 }"
                                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                            >
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300/50 dark:text-gray-400 dark:hover:bg-zinc-800 dark:hover:text-gray-200"
                                    aria-label="Tutup modal"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </Motion>
                        </div>

                        <!-- Body Content -->
                        <div class="px-6 py-6">
                            <slot />
                        </div>

                        <!-- Footer dengan actions -->
                        <div
                            v-if="$slots.footer"
                            class="border-t border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/50"
                        >
                            <slot name="footer" />
                        </div>
                    </div>
                </Motion>
            </div>
        </Transition>
    </Teleport>
</template>

