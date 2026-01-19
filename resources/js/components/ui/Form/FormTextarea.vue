<script setup lang="ts">
/**
 * FormTextarea - Modern textarea dengan refined aesthetic
 * yang mencakup floating label, character counter, dan auto-resize option
 * untuk input text panjang seperti alamat atau deskripsi
 */
import { computed, ref } from 'vue';
import { AlertCircle } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';

interface Props {
    modelValue?: string | null;
    label?: string;
    placeholder?: string;
    error?: string;
    required?: boolean;
    disabled?: boolean;
    readonly?: boolean;
    rows?: number;
    maxlength?: number;
    hint?: string;
    showCounter?: boolean;
    textareaClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
    required: false,
    disabled: false,
    readonly: false,
    rows: 4,
    showCounter: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | null];
}>();

const haptics = useHaptics();
const isFocused = ref(false);

const hasValue = computed(() => {
    return props.modelValue !== null && props.modelValue !== undefined && props.modelValue !== '';
});

const showFloatingLabel = computed(() => {
    return isFocused.value || hasValue.value;
});

const characterCount = computed(() => {
    return props.modelValue?.length || 0;
});

const counterColor = computed(() => {
    if (!props.maxlength) return 'text-slate-500 dark:text-slate-400';
    const percentage = (characterCount.value / props.maxlength) * 100;
    if (percentage >= 100) return 'text-red-500 dark:text-red-400';
    if (percentage >= 90) return 'text-amber-500 dark:text-amber-400';
    if (percentage >= 80) return 'text-yellow-500 dark:text-yellow-400';
    return 'text-slate-500 dark:text-slate-400';
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

const textareaClasses = computed(() => [
    'w-full rounded-xl transition-all duration-200',
    'bg-transparent',
    'text-slate-900 dark:text-slate-100 text-[15px] leading-relaxed',
    'placeholder:text-transparent',
    'focus:outline-none focus-visible:outline-none',
    'disabled:cursor-not-allowed',
    'resize-none px-4',
    props.label ? 'pt-6 pb-3' : 'py-4',
    props.textareaClass || '',
]);

const labelClasses = computed(() => [
    'absolute left-4 transition-all duration-200 pointer-events-none',
    showFloatingLabel.value
        ? 'top-2 text-[11px] font-semibold tracking-wide uppercase'
        : 'top-4 text-[15px] font-normal',
    isFocused.value && !props.error
        ? 'text-emerald-600 dark:text-emerald-400'
        : props.error
          ? 'text-red-500 dark:text-red-400'
          : 'text-slate-500 dark:text-slate-400',
]);

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
        <!-- Counter Badge (Top Right) -->
        <div v-if="showCounter && maxlength" class="flex justify-end">
            <span 
                class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-zinc-800 tabular-nums" 
                :class="counterColor"
            >
                {{ characterCount }}/{{ maxlength }}
            </span>
        </div>

        <!-- Textarea Container -->
        <Motion :whileTap="{ scale: disabled ? 1 : 0.995 }">
            <div :class="containerClasses">
                <!-- Floating Label -->
                <label v-if="label" :class="labelClasses">
                    {{ label }}
                    <span v-if="required" class="text-red-400 ml-0.5 text-xs">*</span>
                </label>

                <!-- Textarea Field -->
                <textarea
                    :value="modelValue"
                    :placeholder="placeholder"
                    :disabled="disabled"
                    :readonly="readonly"
                    :rows="rows"
                    :maxlength="maxlength"
                    :class="textareaClasses"
                    @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
                    @focus="handleFocus"
                    @blur="handleBlur"
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
