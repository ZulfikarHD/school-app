<script setup lang="ts">
import { computed, watch, onMounted, onUnmounted, ref, nextTick } from 'vue';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { useTransition } from '@/composables/useTransition';
import { X } from 'lucide-vue-next';

/**
 * Base Modal Component dengan iOS-like design dan spring animations
 * Support multiple sizes, ARIA accessibility, focus trap, dan backdrop blur
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
    /** ID untuk aria-labelledby, auto-generated jika tidak disediakan */
    titleId?: string;
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

// Refs untuk focus management
const modalRef = ref<HTMLElement | null>(null);
const previousActiveElement = ref<HTMLElement | null>(null);

// Generate unique ID untuk aria-labelledby
const uniqueTitleId = computed(() => props.titleId || `modal-title-${Math.random().toString(36).slice(2, 9)}`);

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
 * Focus trap - keep focus within modal
 */
const handleTabKey = (e: KeyboardEvent) => {
    if (e.key !== 'Tab' || !props.show || !modalRef.value) return;

    const focusableElements = modalRef.value.querySelectorAll<HTMLElement>(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    
    if (focusableElements.length === 0) return;

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    if (e.shiftKey && document.activeElement === firstElement) {
        e.preventDefault();
        lastElement.focus();
    } else if (!e.shiftKey && document.activeElement === lastElement) {
        e.preventDefault();
        firstElement.focus();
    }
};

/**
 * Focus first focusable element when modal opens
 */
const focusFirstElement = () => {
    if (!modalRef.value) return;
    
    const focusableElements = modalRef.value.querySelectorAll<HTMLElement>(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    
    if (focusableElements.length > 0) {
        focusableElements[0].focus();
    }
};

/**
 * Prevent body scroll saat modal open dan manage focus
 */
watch(
    () => props.show,
    async (isOpen) => {
        if (isOpen) {
            // Store previous active element untuk restore nanti
            previousActiveElement.value = document.activeElement as HTMLElement;
            
            if (props.preventScroll) {
                document.body.style.overflow = 'hidden';
            }
            haptics.light();
            emit('open');
            
            // Focus first element setelah modal render
            await nextTick();
            focusFirstElement();
        } else {
            if (props.preventScroll) {
                document.body.style.overflow = '';
            }
            
            // Restore focus ke element sebelumnya
            if (previousActiveElement.value) {
                previousActiveElement.value.focus();
            }
        }
    },
);

onMounted(() => {
    document.addEventListener('keydown', handleEscape);
    document.addEventListener('keydown', handleTabKey);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape);
    document.removeEventListener('keydown', handleTabKey);
    document.body.style.overflow = '';
});
</script>

<template>
    <!-- Modal Overlay dengan ARIA support -->
    <Teleport to="body">
        <Transition
            :css="false"
            @enter="onTransitionEnd()"
            @leave="onTransitionEnd()"
        >
            <div
                v-if="show"
                ref="modalRef"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="title || $slots.header ? uniqueTitleId : undefined"
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
                        class="fixed inset-0 bg-black/50 backdrop-blur-sm"
                        aria-hidden="true"
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
                        class="overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800"
                    >
                        <!-- Header -->
                        <div
                            v-if="title || showCloseButton || $slots.header"
                            class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-zinc-800"
                        >
                            <!-- Title atau Custom Header -->
                            <slot name="header">
                                <h3 
                                    :id="uniqueTitleId"
                                    class="text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    {{ title }}
                                </h3>
                            </slot>

                            <!-- Close Button dengan press feedback -->
                            <Motion
                                v-if="showCloseButton"
                                :whileTap="{ scale: 0.95 }"
                                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                                class="shrink-0 ml-4"
                            >
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="rounded-xl p-2 text-slate-500 transition-all duration-200 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 focus-visible:ring-offset-2 dark:text-slate-400 dark:hover:bg-zinc-800 dark:hover:text-slate-200 dark:focus-visible:ring-offset-zinc-900"
                                    aria-label="Tutup modal"
                                >
                                    <X class="h-5 w-5" />
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
                            class="border-t border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/30"
                        >
                            <slot name="footer" />
                        </div>
                    </div>
                </Motion>
            </div>
        </Transition>
    </Teleport>
</template>

