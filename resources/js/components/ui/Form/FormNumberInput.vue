<script setup lang="ts">
/**
 * FormNumberInput - Modern number input dengan +/- stepper buttons
 * yang mengikuti refined aesthetic dengan haptic feedback dan clear visual
 * untuk memastikan UX optimal terutama di mobile (min 44x44px touch targets)
 * dengan keyboard navigation support (arrow keys) dan ARIA accessibility
 */
import { computed, ref } from 'vue';
import { AlertCircle, Minus, Plus } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';

interface Props {
    modelValue?: number | null;
    label?: string;
    error?: string;
    required?: boolean;
    disabled?: boolean;
    loading?: boolean;
    min?: number;
    max?: number;
    step?: number;
    hint?: string;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    required: false,
    disabled: false,
    loading: false,
    min: 0,
    step: 1,
});

const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

const haptics = useHaptics();
const isFocused = ref(false);

// Generate unique ID untuk accessibility
const uniqueId = computed(() => props.id || `number-input-${Math.random().toString(36).slice(2, 9)}`);

const currentValue = computed(() => props.modelValue ?? props.min);

const canDecrement = computed(() => {
    if (props.disabled || props.loading) return false;
    return currentValue.value > props.min;
});

const canIncrement = computed(() => {
    if (props.disabled || props.loading) return false;
    if (props.max === undefined) return true;
    return currentValue.value < props.max;
});

const decrement = () => {
    if (!canDecrement.value) return;
    haptics.light();
    const newValue = Math.max(props.min, currentValue.value - props.step);
    emit('update:modelValue', newValue);
};

const increment = () => {
    if (!canIncrement.value) return;
    haptics.light();
    const maxValue = props.max ?? Infinity;
    const newValue = Math.min(maxValue, currentValue.value + props.step);
    emit('update:modelValue', newValue);
};

const handleInput = (event: Event) => {
    const value = parseInt((event.target as HTMLInputElement).value);
    if (isNaN(value)) return;

    let clampedValue = Math.max(props.min, value);
    if (props.max !== undefined) {
        clampedValue = Math.min(props.max, clampedValue);
    }

    emit('update:modelValue', clampedValue);
};

/**
 * Handle keyboard navigation untuk increment/decrement dengan arrow keys
 */
const handleKeydown = (event: KeyboardEvent) => {
    if (props.disabled || props.loading) return;

    if (event.key === 'ArrowUp') {
        event.preventDefault();
        increment();
    } else if (event.key === 'ArrowDown') {
        event.preventDefault();
        decrement();
    }
};

const handleFocus = () => {
    isFocused.value = true;
    haptics.light();
};

const handleBlur = () => {
    isFocused.value = false;
};

const buttonBaseClasses = 'w-12 h-12 rounded-xl transition-all duration-200 flex items-center justify-center border focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900';

const decrementButtonClasses = computed(() => [
    buttonBaseClasses,
    canDecrement.value
        ? 'bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800 hover:border-slate-300 active:scale-95'
        : 'bg-slate-50 dark:bg-zinc-900/50 border-slate-100 dark:border-zinc-800 text-slate-300 dark:text-zinc-600 cursor-not-allowed',
]);

const incrementButtonClasses = computed(() => [
    buttonBaseClasses,
    canIncrement.value
        ? 'bg-emerald-500 border-emerald-500 text-white hover:bg-emerald-600 hover:border-emerald-600 active:scale-95 shadow-sm shadow-emerald-500/25'
        : 'bg-slate-50 dark:bg-zinc-900/50 border-slate-100 dark:border-zinc-800 text-slate-300 dark:text-zinc-600 cursor-not-allowed',
]);

/**
 * Container classes berdasarkan focus state
 */
const containerClasses = computed(() => [
    'rounded-xl border transition-all duration-200',
    isFocused.value && !props.error
        ? 'border-emerald-500/50 ring-2 ring-emerald-500/20 bg-white dark:bg-zinc-900'
        : props.error
          ? 'border-red-400 ring-2 ring-red-400/20 bg-red-50/30 dark:bg-red-950/10'
          : 'border-slate-200 dark:border-zinc-700 bg-slate-50/80 dark:bg-zinc-900/80',
]);
</script>

<template>
    <div class="space-y-1.5">
        <!-- Loading Skeleton State -->
        <div v-if="loading" class="animate-pulse">
            <div v-if="label" class="h-3 w-20 bg-slate-200 dark:bg-zinc-700 rounded mb-2" />
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-slate-200 dark:bg-zinc-700 rounded-xl" />
                <div class="flex-1 h-12 bg-slate-200 dark:bg-zinc-700 rounded-xl" />
                <div class="w-12 h-12 bg-slate-200 dark:bg-zinc-700 rounded-xl" />
            </div>
        </div>

        <template v-else>
            <!-- Label dengan proper for association -->
            <label 
                v-if="label" 
                :for="uniqueId"
                class="block text-[11px] font-semibold tracking-wide uppercase text-slate-600 dark:text-slate-400 pl-1"
            >
                {{ label }}
                <span v-if="required" class="text-red-400 ml-0.5">*</span>
            </label>

            <!-- Number Input Container -->
            <div 
                class="flex items-center gap-3"
                role="group"
                :aria-labelledby="label ? `${uniqueId}-label` : undefined"
            >
                <!-- Decrement Button dengan ARIA -->
                <Motion :whileTap="{ scale: canDecrement ? 0.92 : 1 }">
                    <button 
                        type="button" 
                        @click="decrement" 
                        :disabled="!canDecrement" 
                        :class="decrementButtonClasses"
                        :aria-label="`Kurangi ${step}`"
                        :aria-disabled="!canDecrement"
                    >
                        <Minus class="w-4 h-4" :stroke-width="2.5" />
                    </button>
                </Motion>

                <!-- Number Display dengan ARIA -->
                <div class="flex-1 relative">
                    <div :class="containerClasses">
                        <input
                            :id="uniqueId"
                            type="number"
                            :value="currentValue"
                            :min="min"
                            :max="max"
                            :step="step"
                            :disabled="disabled"
                            :aria-valuemin="min"
                            :aria-valuemax="max"
                            :aria-valuenow="currentValue"
                            :aria-invalid="!!error"
                            :aria-describedby="error ? `${uniqueId}-error` : hint ? `${uniqueId}-hint` : undefined"
                            @input="handleInput"
                            @keydown="handleKeydown"
                            @focus="handleFocus"
                            @blur="handleBlur"
                            class="w-full h-12 text-center rounded-xl bg-transparent text-slate-900 dark:text-slate-100 text-xl font-bold tabular-nums focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                        />
                    </div>

                    <!-- Min/Max Indicator -->
                    <div
                        v-if="max !== undefined"
                        class="absolute -bottom-5 left-0 right-0 flex justify-center"
                        aria-hidden="true"
                    >
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium tabular-nums">
                            {{ min }} — {{ max }}
                        </span>
                    </div>
                </div>

                <!-- Increment Button dengan ARIA -->
                <Motion :whileTap="{ scale: canIncrement ? 0.92 : 1 }">
                    <button 
                        type="button" 
                        @click="increment" 
                        :disabled="!canIncrement" 
                        :class="incrementButtonClasses"
                        :aria-label="`Tambah ${step}`"
                        :aria-disabled="!canIncrement"
                    >
                        <Plus class="w-4 h-4" :stroke-width="2.5" />
                    </button>
                </Motion>
            </div>

            <!-- Hint Text -->
            <p 
                v-if="hint && !error" 
                :id="`${uniqueId}-hint`"
                class="text-xs text-slate-500 dark:text-slate-400 pl-1 mt-3 font-medium"
            >
                {{ hint }}
            </p>

            <!-- Error Message -->
            <Motion
                v-if="error"
                :initial="{ opacity: 0, y: -4 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.2 }"
            >
                <p 
                    :id="`${uniqueId}-error`"
                    class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1.5 pl-1 mt-3 font-medium"
                    role="alert"
                >
                    <AlertCircle class="w-3.5 h-3.5 shrink-0" />
                    {{ error }}
                </p>
            </Motion>
        </template>
    </div>
</template>
