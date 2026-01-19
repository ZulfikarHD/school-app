<script setup lang="ts">
/**
 * FormSelect - Modern select dropdown dengan refined aesthetic
 * yang mencakup floating label, subtle animations, dan custom chevron
 * untuk memastikan consistency dan premium UX di seluruh aplikasi
 */
import { computed, ref } from 'vue';
import { AlertCircle, ChevronDown } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import type { Component } from 'vue';

interface Option {
    value: string | number;
    label: string;
    disabled?: boolean;
}

interface Props {
    modelValue?: string | number | null;
    label?: string;
    options: Option[] | string[];
    placeholder?: string;
    error?: string;
    required?: boolean;
    disabled?: boolean;
    loading?: boolean;
    icon?: Component;
    hint?: string;
    selectClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
    required: false,
    disabled: false,
    loading: false,
    placeholder: 'Pilih opsi',
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number | null];
    'change': [value: string | number | null];
}>();

const haptics = useHaptics();
const isFocused = ref(false);

const normalizedOptions = computed<Option[]>(() => {
    if (!props.options || !Array.isArray(props.options)) {
        return [];
    }

    return props.options
        .map(opt => {
            if (typeof opt === 'string') {
                return { value: opt, label: opt };
            }
            if (opt && typeof opt === 'object' && 'value' in opt && 'label' in opt) {
                return opt;
            }
            return null;
        })
        .filter((opt): opt is Option => opt !== null);
});

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

const selectClasses = computed(() => [
    'w-full h-[52px] rounded-xl transition-all duration-200',
    'bg-transparent',
    'text-slate-900 dark:text-slate-100 text-[15px]',
    'focus:outline-none focus-visible:outline-none',
    'disabled:cursor-not-allowed',
    'appearance-none cursor-pointer',
    props.icon ? 'pl-11 pr-10' : 'pl-4 pr-10',
    props.label ? 'pt-5 pb-1.5' : 'py-3.5',
    props.selectClass || '',
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

const handleChange = (event: Event) => {
    const selectElement = event.target as HTMLSelectElement;
    const stringValue = selectElement.value;

    // If empty string, emit null
    if (stringValue === '') {
        haptics.light();
        emit('update:modelValue', null);
        emit('change', null);
        return;
    }

    // Find matching option and preserve type
    const selectedOption = normalizedOptions.value.find(opt => String(opt.value) === stringValue);

    if (selectedOption) {
        // Preserve the original type from option
        const value = typeof selectedOption.value === 'number'
            ? selectedOption.value
            : stringValue;

        haptics.light();
        emit('update:modelValue', value);
        emit('change', value);
    } else {
        // Fallback: try to convert to number if it looks like a number
        const numValue = Number(stringValue);
        const value = !isNaN(numValue) && stringValue !== '' ? numValue : stringValue;

        haptics.light();
        emit('update:modelValue', value);
        emit('change', value);
    }
};

const handleFocus = () => {
    isFocused.value = true;
    haptics.light();
};

const handleBlur = () => {
    isFocused.value = false;
};
</script>

<template>
    <div class="space-y-1.5">
        <!-- Loading Skeleton State -->
        <div v-if="loading" class="animate-pulse">
            <div v-if="label" class="h-3 w-20 bg-slate-200 dark:bg-zinc-700 rounded mb-2" />
            <div class="h-[52px] bg-slate-200 dark:bg-zinc-700 rounded-xl" />
        </div>

        <!-- Select Container -->
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

                <!-- Select Field -->
                <select
                    :value="modelValue === null || modelValue === undefined ? '' : modelValue"
                    :disabled="disabled"
                    :class="selectClasses"
                    @change="handleChange"
                    @focus="handleFocus"
                    @blur="handleBlur"
                >
                    <option value="" disabled>{{ placeholder }}</option>
                    <option
                        v-for="option in normalizedOptions"
                        :key="String(option.value)"
                        :value="option.value"
                        :disabled="option.disabled"
                    >
                        {{ option.label || String(option.value) }}
                    </option>
                </select>

                <!-- Chevron Icon -->
                <Motion
                    :animate="{ rotate: isFocused ? 180 : 0 }"
                    :transition="{ duration: 0.2 }"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                >
                    <ChevronDown
                        class="w-4 h-4 transition-colors duration-200"
                        :class="
                            isFocused
                                ? 'text-emerald-500'
                                : error
                                  ? 'text-red-400'
                                  : 'text-slate-400 dark:text-slate-500'
                        "
                    />
                </Motion>
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
