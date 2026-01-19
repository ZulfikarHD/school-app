<script setup lang="ts">
/**
 * FormCheckbox - Modern checkbox dengan refined aesthetic
 * yang mencakup label, description, smooth animations, dan haptic feedback
 * untuk memastikan consistency dan optimal touch targets (min 44x44px)
 * dengan proper accessibility melalui id/for association
 */
import { computed, ref } from 'vue';
import { AlertCircle, Check } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';

interface Props {
    modelValue?: boolean;
    label?: string;
    description?: string;
    error?: string;
    disabled?: boolean;
    checkboxClass?: string;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    disabled: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

const haptics = useHaptics();

// Generate unique ID untuk accessibility jika tidak disediakan
const uniqueId = ref(props.id || `checkbox-${Math.random().toString(36).slice(2, 9)}`);

const handleChange = () => {
    if (props.disabled) return;
    haptics.selection();
    emit('update:modelValue', !props.modelValue);
};

const containerClasses = computed(() => [
    'flex items-start gap-3 p-3 rounded-xl transition-all duration-200',
    'border',
    props.modelValue && !props.error
        ? 'border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/50 dark:bg-emerald-950/20'
        : props.error
          ? 'border-red-200 dark:border-red-900/50 bg-red-50/50 dark:bg-red-950/20'
          : 'border-transparent hover:bg-slate-50 dark:hover:bg-zinc-900/50',
    props.disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
]);

const checkboxWrapperClasses = computed(() => [
    'relative flex items-center justify-center w-5 h-5 rounded-md transition-all duration-200 mt-0.5',
    'border',
    props.modelValue
        ? 'bg-emerald-500 border-emerald-500'
        : props.error
          ? 'border-red-400 bg-white dark:bg-zinc-900'
          : 'border-slate-300 dark:border-zinc-600 bg-white dark:bg-zinc-900',
    props.disabled ? 'cursor-not-allowed' : 'cursor-pointer',
    !props.modelValue && !props.disabled ? 'hover:border-emerald-400' : '',
]);
</script>

<template>
    <div class="space-y-1.5">
        <Motion :whileTap="{ scale: disabled ? 1 : 0.98 }">
            <div :class="containerClasses" @click="handleChange">
                <!-- Custom Checkbox dengan proper accessibility -->
                <div :class="checkboxWrapperClasses">
                    <!-- Hidden Native Checkbox dengan id untuk label association -->
                    <input
                        :id="uniqueId"
                        type="checkbox"
                        :checked="modelValue"
                        :disabled="disabled"
                        :aria-describedby="description ? `${uniqueId}-description` : undefined"
                        class="sr-only"
                        @change="handleChange"
                    />

                    <!-- Check Icon with Animation -->
                    <Motion
                        v-if="modelValue"
                        :initial="{ scale: 0, opacity: 0 }"
                        :animate="{ scale: 1, opacity: 1 }"
                        :exit="{ scale: 0, opacity: 0 }"
                        :transition="{ duration: 0.15 }"
                    >
                        <Check class="w-3.5 h-3.5 text-white" :stroke-width="3" />
                    </Motion>
                </div>

                <!-- Label & Description dengan proper association -->
                <div v-if="label || description" class="flex-1 select-none min-w-0">
                    <label
                        v-if="label"
                        :for="uniqueId"
                        class="block text-sm font-medium text-slate-800 dark:text-slate-200 cursor-pointer leading-snug"
                    >
                        {{ label }}
                    </label>
                    <p 
                        v-if="description" 
                        :id="`${uniqueId}-description`"
                        class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed"
                    >
                        {{ description }}
                    </p>
                </div>
            </div>
        </Motion>

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
