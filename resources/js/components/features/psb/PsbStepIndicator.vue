<script setup lang="ts">
/**
 * PsbStepIndicator - Komponen indikator langkah form multi-step
 *
 * Komponen ini bertujuan untuk menampilkan progress pendaftaran
 * dengan visualisasi langkah-langkah yang sudah dan belum selesai
 */
import { Check } from 'lucide-vue-next';

interface Step {
    id: number;
    title: string;
    description?: string;
}

interface Props {
    steps: Step[];
    currentStep: number;
}

defineProps<Props>();

/**
 * Determine step status berdasarkan posisi current step
 */
const getStepStatus = (stepId: number, currentStep: number): 'completed' | 'current' | 'upcoming' => {
    if (stepId < currentStep) return 'completed';
    if (stepId === currentStep) return 'current';
    return 'upcoming';
};
</script>

<template>
    <div class="w-full">
        <!-- Mobile: Simplified indicator -->
        <div class="flex items-center justify-between sm:hidden">
            <span class="text-sm font-medium text-slate-900">
                Langkah {{ currentStep }} dari {{ steps.length }}
            </span>
            <div class="flex gap-1.5">
                <div
                    v-for="step in steps"
                    :key="step.id"
                    class="h-2 w-8 rounded-full transition-all duration-300"
                    :class="{
                        'bg-emerald-500': step.id <= currentStep,
                        'bg-slate-200': step.id > currentStep,
                    }"
                />
            </div>
        </div>

        <!-- Desktop: Full stepper -->
        <nav aria-label="Progress" class="hidden sm:block">
            <ol role="list" class="flex items-center">
                <li
                    v-for="(step, stepIdx) in steps"
                    :key="step.id"
                    class="relative"
                    :class="stepIdx !== steps.length - 1 ? 'flex-1 pr-8' : ''"
                >
                    <!-- Connector line -->
                    <div
                        v-if="stepIdx !== steps.length - 1"
                        class="absolute left-0 top-4 h-0.5 w-full"
                        aria-hidden="true"
                    >
                        <div
                            class="h-full transition-all duration-300"
                            :class="{
                                'bg-emerald-500': getStepStatus(step.id, currentStep) === 'completed',
                                'bg-slate-200': getStepStatus(step.id, currentStep) !== 'completed',
                            }"
                        />
                    </div>

                    <div class="group relative flex flex-col items-start">
                        <!-- Step circle -->
                        <span class="flex h-9 items-center" aria-hidden="true">
                            <span
                                class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full transition-all duration-300"
                                :class="{
                                    'bg-emerald-500 text-white': getStepStatus(step.id, currentStep) === 'completed',
                                    'border-2 border-emerald-500 bg-white': getStepStatus(step.id, currentStep) === 'current',
                                    'border-2 border-slate-300 bg-white': getStepStatus(step.id, currentStep) === 'upcoming',
                                }"
                            >
                                <Check
                                    v-if="getStepStatus(step.id, currentStep) === 'completed'"
                                    class="h-5 w-5"
                                />
                                <span
                                    v-else
                                    class="text-sm font-semibold"
                                    :class="{
                                        'text-emerald-600': getStepStatus(step.id, currentStep) === 'current',
                                        'text-slate-500': getStepStatus(step.id, currentStep) === 'upcoming',
                                    }"
                                >
                                    {{ step.id }}
                                </span>
                            </span>
                        </span>

                        <!-- Step label -->
                        <span class="mt-2 flex min-w-0 flex-col">
                            <span
                                class="text-sm font-semibold transition-colors"
                                :class="{
                                    'text-emerald-600': getStepStatus(step.id, currentStep) === 'current',
                                    'text-slate-900': getStepStatus(step.id, currentStep) === 'completed',
                                    'text-slate-500': getStepStatus(step.id, currentStep) === 'upcoming',
                                }"
                            >
                                {{ step.title }}
                            </span>
                            <span
                                v-if="step.description"
                                class="text-xs text-slate-500"
                            >
                                {{ step.description }}
                            </span>
                        </span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</template>
