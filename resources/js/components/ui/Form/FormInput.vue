<script setup lang="ts">
/**
 * FormInput - Modern form input dengan refined aesthetic
 * yang mencakup floating label, subtle animations, dan premium visual
 * untuk memastikan consistency dan elevated UX di seluruh aplikasi
 */
import { computed, ref } from 'vue';
import { AlertCircle } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import type { Component } from 'vue';

interface Props {
    modelValue?: string | number | null;
    label?: string;
    type?: 'text' | 'email' | 'tel' | 'number' | 'date' | 'password' | 'url';
    placeholder?: string;
    error?: string;
    required?: boolean;
    disabled?: boolean;
    readonly?: boolean;
    loading?: boolean;
    icon?: Component;
    hint?: string;
    maxlength?: number;
    min?: number;
    max?: number;
    step?: number;
    autocomplete?: string;
    inputClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    required: false,
    disabled: false,
    readonly: false,
    loading: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number | null];
    'blur': [event: FocusEvent];
    'focus': [event: FocusEvent];
}>();

const haptics = useHaptics();
const isFocused = ref(false);

const hasValue = computed(() => {
    return props.modelValue !== null && props.modelValue !== undefined && props.modelValue !== '';
});

const showFloatingLabel = computed(() => {
    return isFocused.value || hasValue.value;
});

const containerClasses = computed(() => [
    'relative rounded-xl transition-all duration-200',
    'bg-slate-50/80 dark:bg-zinc-900/80',
    'border',
    isFocused.value && !props.error
        ? 'border-emerald-500/50 ring-2 ring-emerald-500/20 bg-white dark:bg-zinc-900'
        : props.error
          ? 'border-red-400 ring-2 ring-red-400/20 bg-red-50/30 dark:bg-red-950/10'
          : 'border-slate-200 dark:border-zinc-800 hover:border-slate-300 dark:hover:border-zinc-700',
    props.disabled ? 'opacity-50 cursor-not-allowed' : '',
]);

const inputClasses = computed(() => [
    'w-full h-[52px] rounded-xl transition-all duration-200',
    'bg-transparent',
    'text-slate-900 dark:text-slate-100 text-[15px]',
    'placeholder:text-transparent',
    'focus:outline-none focus-visible:outline-none',
    'disabled:cursor-not-allowed',
    props.icon ? 'pl-11 pr-4' : 'px-4',
    props.label ? 'pt-5 pb-1.5' : 'py-3.5',
    props.inputClass || '',
]);

const labelClasses = computed(() => [
    'absolute transition-all duration-200 pointer-events-none',
    showFloatingLabel.value
        ? 'top-1.5 text-[11px] font-semibold tracking-wide uppercase'
        : 'top-1/2 -translate-y-1/2 text-[15px] font-normal',
    props.icon && !showFloatingLabel.value ? 'left-11' : 'left-4',
    isFocused.value && !props.error
        ? 'text-emerald-600 dark:text-emerald-400'
        : props.error
          ? 'text-red-500 dark:text-red-400'
          : 'text-slate-500 dark:text-slate-400',
]);

const handleFocus = (event: FocusEvent) => {
    isFocused.value = true;
    haptics.light();
    emit('focus', event);
};

const handleBlur = (event: FocusEvent) => {
    isFocused.value = false;
    emit('blur', event);
};
</script>

<template>
    <div class="space-y-1.5">
        <!-- Loading Skeleton State -->
        <div v-if="loading" class="animate-pulse">
            <div v-if="label" class="h-3 w-20 bg-slate-200 dark:bg-zinc-700 rounded mb-2" />
            <div class="h-[52px] bg-slate-200 dark:bg-zinc-700 rounded-xl" />
        </div>

        <!-- Input Container -->
        <Motion v-else :whileTap="{ scale: disabled ? 1 : 0.995 }">
            <div :class="containerClasses">
                <!-- Icon -->
                <component
                    v-if="icon"
                    :is="icon"
                    class="absolute left-3.5 top-1/2 -translate-y-1/2 w-[18px] h-[18px] transition-colors duration-200 pointer-events-none z-10"
                    :class="
                        isFocused
                            ? 'text-emerald-500'
                            : error
                              ? 'text-red-400'
                              : 'text-slate-400 dark:text-slate-500'
                    "
                />

                <!-- Floating Label -->
                <label v-if="label" :class="labelClasses">
                    {{ label }}
                    <span v-if="required" class="text-red-400 ml-0.5 text-xs">*</span>
                </label>

                <!-- Input Field -->
                <input
                    :type="type"
                    :value="modelValue"
                    :placeholder="placeholder"
                    :disabled="disabled"
                    :readonly="readonly"
                    :maxlength="maxlength"
                    :min="min"
                    :max="max"
                    :step="step"
                    :autocomplete="autocomplete"
                    :class="inputClasses"
                    @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
                    @blur="handleBlur"
                    @focus="handleFocus"
                />
            </div>
        </Motion>

        <!-- Hint Text -->
        <p v-if="hint && !error" class="text-xs text-slate-500 dark:text-slate-400 pl-1 font-medium">
            {{ hint }}
        </p>

        <!-- Error Message -->
        <Motion
            v-if="error"
            :initial="{ opacity: 0, y: -4 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.2 }"
        >
            <p class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1.5 pl-1 font-medium">
                <AlertCircle class="w-3.5 h-3.5 shrink-0" />
                {{ error }}
            </p>
        </Motion>
    </div>
</template>
