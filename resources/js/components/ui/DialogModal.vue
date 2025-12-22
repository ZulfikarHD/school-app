<script setup lang="ts">
import { computed } from 'vue';
import { Motion } from 'motion-v';
import BaseModal from './BaseModal.vue';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Dialog Modal Component untuk confirmations dan questions
 * dengan iOS-like design, icon support, dan action buttons
 * Support different types: success, warning, danger, info
 */

interface Props {
    show?: boolean;
    type?: 'success' | 'warning' | 'danger' | 'info';
    title: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    showCancel?: boolean;
    loading?: boolean;
    icon?: 'check' | 'warning' | 'error' | 'info' | 'question';
}

const props = withDefaults(defineProps<Props>(), {
    show: false,
    type: 'info',
    confirmText: 'Konfirmasi',
    cancelText: 'Batal',
    showCancel: true,
    loading: false,
});

const emit = defineEmits<{
    confirm: [];
    cancel: [];
    close: [];
}>();

const haptics = useHaptics();

/**
 * Computed styling berdasarkan dialog type
 */
const typeConfig = computed(() => {
    const configs = {
        success: {
            bgColor: 'bg-green-100 dark:bg-green-900/30',
            iconColor: 'text-green-600 dark:text-green-400',
            buttonColor: 'from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-green-500/50',
        },
        warning: {
            bgColor: 'bg-yellow-100 dark:bg-yellow-900/30',
            iconColor: 'text-yellow-600 dark:text-yellow-400',
            buttonColor: 'from-yellow-600 to-yellow-700 hover:from-yellow-700 hover:to-yellow-800 focus:ring-yellow-500/50',
        },
        danger: {
            bgColor: 'bg-red-100 dark:bg-red-900/30',
            iconColor: 'text-red-600 dark:text-red-400',
            buttonColor: 'from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:ring-red-500/50',
        },
        info: {
            bgColor: 'bg-blue-100 dark:bg-blue-900/30',
            iconColor: 'text-blue-600 dark:text-blue-400',
            buttonColor: 'from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:ring-blue-500/50',
        },
    };
    return configs[props.type];
});

/**
 * Handle confirm action dengan haptic feedback
 */
const handleConfirm = () => {
    haptics.medium();
    emit('confirm');
};

/**
 * Handle cancel action dengan haptic feedback
 */
const handleCancel = () => {
    haptics.light();
    emit('cancel');
    emit('close');
};
</script>

<template>
    <BaseModal
        :show="show"
        size="sm"
        :close-on-backdrop="!loading"
        :show-close-button="false"
        @close="handleCancel"
    >
        <div class="text-center">
            <!-- Icon dengan bounce animation -->
            <Motion
                :initial="{ scale: 0, rotate: -180 }"
                :animate="{ scale: 1, rotate: 0 }"
                :transition="{ type: 'spring', stiffness: 400, damping: 20, delay: 0.1 }"
            >
                <div :class="['mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full', typeConfig.bgColor]">
                    <!-- Success Icon -->
                    <svg
                        v-if="icon === 'check' || type === 'success'"
                        :class="['h-8 w-8', typeConfig.iconColor]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>

                    <!-- Warning Icon -->
                    <svg
                        v-else-if="icon === 'warning' || type === 'warning'"
                        :class="['h-8 w-8', typeConfig.iconColor]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                    </svg>

                    <!-- Error Icon -->
                    <svg
                        v-else-if="icon === 'error' || type === 'danger'"
                        :class="['h-8 w-8', typeConfig.iconColor]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>

                    <!-- Question Icon -->
                    <svg
                        v-else-if="icon === 'question'"
                        :class="['h-8 w-8', typeConfig.iconColor]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>

                    <!-- Info Icon (default) -->
                    <svg
                        v-else
                        :class="['h-8 w-8', typeConfig.iconColor]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>
            </Motion>

            <!-- Title dengan fade in -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ delay: 0.2 }"
            >
                <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">
                    {{ title }}
                </h3>
            </Motion>

            <!-- Message dengan fade in -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ delay: 0.25 }"
            >
                <p class="mb-6 text-gray-600 dark:text-gray-400">
                    {{ message }}
                </p>
            </Motion>

            <!-- Action Buttons dengan staggered animation -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ delay: 0.3 }"
            >
                <div class="flex flex-col gap-3 sm:flex-row-reverse">
                    <!-- Confirm Button -->
                    <Motion
                        :whileTap="{ scale: 0.97 }"
                        :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                        class="flex-1"
                    >
                        <button
                            type="button"
                            :disabled="loading"
                            @click="handleConfirm"
                            :class="[
                                'flex w-full items-center justify-center rounded-xl bg-gradient-to-r px-6 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:shadow-xl focus:outline-none focus:ring-4 disabled:cursor-not-allowed disabled:opacity-60',
                                typeConfig.buttonColor,
                            ]"
                        >
                            <svg
                                v-if="loading"
                                class="mr-2 h-5 w-5 animate-spin"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                />
                            </svg>
                            {{ loading ? 'Memproses...' : confirmText }}
                        </button>
                    </Motion>

                    <!-- Cancel Button -->
                    <Motion
                        v-if="showCancel"
                        :whileTap="{ scale: 0.97 }"
                        :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                        class="flex-1"
                    >
                        <button
                            type="button"
                            :disabled="loading"
                            @click="handleCancel"
                            class="w-full rounded-xl border-2 border-gray-300 px-6 py-3 font-semibold text-gray-700 transition-all duration-200 hover:border-gray-400 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-300/50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-600 dark:text-gray-300 dark:hover:border-gray-500 dark:hover:bg-gray-700"
                        >
                            {{ cancelText }}
                        </button>
                    </Motion>
                </div>
            </Motion>
        </div>
    </BaseModal>
</template>

