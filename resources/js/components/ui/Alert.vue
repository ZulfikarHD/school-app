<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Alert/Toast Component untuk notifications dan feedback messages
 * dengan iOS-like design, auto-dismiss, dan position customization
 * Support multiple types: success, error, warning, info
 */

interface Props {
    show?: boolean;
    type?: 'success' | 'error' | 'warning' | 'info';
    title?: string;
    message: string;
    duration?: number; // dalam milliseconds, 0 = no auto dismiss
    position?: 'top' | 'top-right' | 'top-left' | 'bottom' | 'bottom-right' | 'bottom-left';
    dismissible?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    show: false,
    type: 'info',
    duration: 3000,
    position: 'top-right',
    dismissible: true,
});

const emit = defineEmits<{
    close: [];
}>();

const haptics = useHaptics();
const isVisible = ref(props.show);
const timer = ref<number | null>(null);

/**
 * Position class mapping untuk responsive positioning
 */
const positionClasses = {
    top: 'top-4 left-1/2 -translate-x-1/2',
    'top-right': 'top-4 right-4',
    'top-left': 'top-4 left-4',
    bottom: 'bottom-4 left-1/2 -translate-x-1/2',
    'bottom-right': 'bottom-4 right-4',
    'bottom-left': 'bottom-4 left-4',
};

/**
 * Type configuration untuk styling dan icons
 */
const typeConfig = {
    success: {
        bg: 'from-green-500 to-green-600',
        icon: 'M5 13l4 4L19 7',
        iconBg: 'bg-green-400/30',
    },
    error: {
        bg: 'from-red-500 to-red-600',
        icon: 'M6 18L18 6M6 6l12 12',
        iconBg: 'bg-red-400/30',
    },
    warning: {
        bg: 'from-yellow-500 to-yellow-600',
        icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        iconBg: 'bg-yellow-400/30',
    },
    info: {
        bg: 'from-blue-500 to-blue-600',
        icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        iconBg: 'bg-blue-400/30',
    },
};

/**
 * Handle close alert dengan haptic feedback
 */
const closeAlert = () => {
    haptics.light();
    isVisible.value = false;
    if (timer.value) {
        clearTimeout(timer.value);
        timer.value = null;
    }
    setTimeout(() => {
        emit('close');
    }, 300);
};

/**
 * Auto dismiss setelah duration tertentu
 */
const startAutoClose = () => {
    if (props.duration > 0 && isVisible.value) {
        timer.value = window.setTimeout(() => {
            closeAlert();
        }, props.duration);
    }
};

/**
 * Watch show prop untuk sync dengan internal state
 */
watch(
    () => props.show,
    (newVal) => {
        isVisible.value = newVal;
        if (newVal) {
            startAutoClose();
            // Trigger haptic berdasarkan type
            if (props.type === 'success') {
                haptics.success();
            } else if (props.type === 'error') {
                haptics.error();
            } else {
                haptics.light();
            }
        }
    },
);

onMounted(() => {
    if (props.show) {
        startAutoClose();
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            :css="false"
            @enter="(el: Element, done: () => void) => setTimeout(done, 300)"
            @leave="(el: Element, done: () => void) => setTimeout(done, 300)"
        >
            <Motion
                v-if="isVisible"
                :initial="{ opacity: 0, y: position.includes('bottom') ? 20 : -20, scale: 0.95 }"
                :animate="{ opacity: 1, y: 0, scale: 1 }"
                :exit="{ opacity: 0, y: position.includes('bottom') ? 20 : -20, scale: 0.95 }"
                :transition="{ type: 'spring', stiffness: 400, damping: 30 }"
                :class="['fixed z-50 w-full max-w-md px-4 sm:px-0', positionClasses[position]]"
            >
                <div
                    :class="[
                        'overflow-hidden rounded-2xl bg-gradient-to-r shadow-2xl backdrop-blur-xl',
                        typeConfig[type].bg,
                    ]"
                >
                    <div class="flex items-start gap-4 p-4">
                        <!-- Icon dengan bounce animation -->
                        <Motion
                            :initial="{ scale: 0, rotate: -90 }"
                            :animate="{ scale: 1, rotate: 0 }"
                            :transition="{ type: 'spring', stiffness: 500, damping: 25, delay: 0.1 }"
                        >
                            <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-full', typeConfig[type].iconBg]">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        :d="typeConfig[type].icon"
                                    />
                                </svg>
                            </div>
                        </Motion>

                        <!-- Content -->
                        <div class="flex-1 pt-0.5">
                            <h4 v-if="title" class="font-bold text-white">
                                {{ title }}
                            </h4>
                            <p class="text-sm text-white/90" :class="{ 'mt-1': title }">
                                {{ message }}
                            </p>
                        </div>

                        <!-- Close Button dengan press feedback -->
                        <Motion
                            v-if="dismissible"
                            :whileTap="{ scale: 0.97 }"
                            :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                        >
                            <button
                                type="button"
                                @click="closeAlert"
                                class="shrink-0 rounded-lg p-1.5 text-white/80 transition-colors hover:bg-white/20 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/50"
                                aria-label="Tutup notifikasi"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </Motion>
                    </div>

                    <!-- Progress bar untuk auto-dismiss -->
                    <Motion
                        v-if="duration > 0"
                        :initial="{ scaleX: 1 }"
                        :animate="{ scaleX: 0 }"
                        :transition="{ duration: duration / 1000, ease: 'linear' }"
                        class="h-1 origin-left bg-white/30"
                    />
                </div>
            </Motion>
        </Transition>
    </Teleport>
</template>

