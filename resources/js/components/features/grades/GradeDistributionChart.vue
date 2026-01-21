<script setup lang="ts">
/**
 * GradeDistributionChart - Chart untuk menampilkan distribusi predikat nilai
 * dalam bentuk bar chart horizontal dengan animasi dan responsive design
 */
import { computed } from 'vue';

interface Props {
    distribution: {
        A: number;
        B: number;
        C: number;
        D: number;
    };
    showLabels?: boolean;
    showPercentage?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showLabels: true,
    showPercentage: true
});

const total = computed(() => {
    return props.distribution.A + props.distribution.B + props.distribution.C + props.distribution.D;
});

const chartData = computed(() => {
    return [
        {
            label: 'A',
            value: props.distribution.A,
            percentage: total.value > 0 ? (props.distribution.A / total.value * 100).toFixed(1) : '0',
            color: 'bg-emerald-500',
            bgColor: 'bg-emerald-100 dark:bg-emerald-900/30',
            textColor: 'text-emerald-700 dark:text-emerald-400',
            description: 'Sangat Baik (90-100)'
        },
        {
            label: 'B',
            value: props.distribution.B,
            percentage: total.value > 0 ? (props.distribution.B / total.value * 100).toFixed(1) : '0',
            color: 'bg-blue-500',
            bgColor: 'bg-blue-100 dark:bg-blue-900/30',
            textColor: 'text-blue-700 dark:text-blue-400',
            description: 'Baik (80-89)'
        },
        {
            label: 'C',
            value: props.distribution.C,
            percentage: total.value > 0 ? (props.distribution.C / total.value * 100).toFixed(1) : '0',
            color: 'bg-amber-500',
            bgColor: 'bg-amber-100 dark:bg-amber-900/30',
            textColor: 'text-amber-700 dark:text-amber-400',
            description: 'Cukup (70-79)'
        },
        {
            label: 'D',
            value: props.distribution.D,
            percentage: total.value > 0 ? (props.distribution.D / total.value * 100).toFixed(1) : '0',
            color: 'bg-red-500',
            bgColor: 'bg-red-100 dark:bg-red-900/30',
            textColor: 'text-red-700 dark:text-red-400',
            description: 'Kurang (<70)'
        }
    ];
});

const maxValue = computed(() => {
    return Math.max(props.distribution.A, props.distribution.B, props.distribution.C, props.distribution.D, 1);
});
</script>

<template>
    <div class="space-y-3">
        <div
            v-for="item in chartData"
            :key="item.label"
            class="flex items-center gap-3"
        >
            <!-- Predikat Label -->
            <div class="w-8 text-center">
                <span
                    :class="[
                        'inline-flex items-center justify-center w-7 h-7 rounded-full font-bold text-sm',
                        item.bgColor,
                        item.textColor
                    ]"
                >
                    {{ item.label }}
                </span>
            </div>

            <!-- Bar -->
            <div class="flex-1">
                <div class="h-6 bg-slate-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                    <div
                        :class="[item.color, 'h-full rounded-full transition-all duration-500 ease-out']"
                        :style="{ width: `${(item.value / maxValue) * 100}%` }"
                    />
                </div>
            </div>

            <!-- Value & Percentage -->
            <div class="w-24 text-right">
                <span class="font-semibold text-slate-700 dark:text-slate-300">
                    {{ item.value }}
                </span>
                <span v-if="showPercentage" class="text-xs text-slate-500 dark:text-slate-400 ml-1">
                    ({{ item.percentage }}%)
                </span>
            </div>
        </div>

        <!-- Legend -->
        <div v-if="showLabels" class="pt-2 border-t border-slate-100 dark:border-zinc-800">
            <div class="grid grid-cols-2 gap-2 text-xs text-slate-500 dark:text-slate-400">
                <div v-for="item in chartData" :key="`legend-${item.label}`" class="flex items-center gap-2">
                    <span :class="[item.color, 'w-2 h-2 rounded-full']"></span>
                    <span>{{ item.description }}</span>
                </div>
            </div>
        </div>

        <!-- Total -->
        <div class="text-center text-sm text-slate-600 dark:text-slate-400 pt-2">
            Total: <span class="font-semibold">{{ total }}</span> siswa
        </div>
    </div>
</template>
