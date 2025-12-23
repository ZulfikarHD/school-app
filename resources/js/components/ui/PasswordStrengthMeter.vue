<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    password?: string;
    minLength?: number;
}

const props = withDefaults(defineProps<Props>(), {
    password: '',
    minLength: 8,
});

const score = computed(() => {
    let s = 0;
    const pwd = props.password;

    if (!pwd) return 0;

    // Length check
    if (pwd.length >= props.minLength) s += 1;
    if (pwd.length > 10) s += 1;

    // Complexity checks
    if (/[A-Z]/.test(pwd)) s += 1;
    if (/[a-z]/.test(pwd)) s += 1;
    if (/[0-9]/.test(pwd)) s += 1;
    if (/[^A-Za-z0-9]/.test(pwd)) s += 1;

    return s; // Max score 6
});

const strength = computed(() => {
    const s = score.value;
    if (s <= 2) return 'Lemah';
    if (s <= 4) return 'Sedang';
    return 'Kuat';
});

const colorClass = computed(() => {
    const s = score.value;
    if (s === 0) return 'bg-gray-200 dark:bg-zinc-800';
    if (s <= 2) return 'bg-red-500';
    if (s <= 4) return 'bg-yellow-500';
    return 'bg-green-500';
});

const widthPercentage = computed(() => {
    const s = score.value;
    if (s === 0) return 0;
    // Map score 1-6 to percentage 16% - 100%
    return Math.min(100, Math.max(10, (s / 6) * 100));
});
</script>

<template>
    <div class="w-full space-y-2">
        <div class="flex justify-between items-center mb-1">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kekuatan Password</span>
            <span v-if="props.password" 
                  class="text-xs font-bold transition-colors duration-300"
                  :class="{
                      'text-red-600 dark:text-red-400': score <= 2,
                      'text-yellow-600 dark:text-yellow-400': score > 2 && score <= 4,
                      'text-green-600 dark:text-green-400': score > 4
                  }"
            >
                {{ strength }}
            </span>
        </div>
        
        <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden dark:bg-zinc-800">
            <div 
                class="h-full transition-all duration-500 ease-out rounded-full"
                :class="colorClass"
                :style="{ width: `${widthPercentage}%` }"
            ></div>
        </div>

        <ul class="text-[10px] text-gray-500 dark:text-gray-400 space-y-1 mt-2 pl-1">
            <li class="flex items-center gap-1.5" :class="{ 'text-green-600 dark:text-green-400': password.length >= minLength }">
                <span class="w-1.5 h-1.5 rounded-full" :class="password.length >= minLength ? 'bg-green-500' : 'bg-gray-300 dark:bg-zinc-700'"></span>
                Minimal {{ minLength }} karakter
            </li>
            <li class="flex items-center gap-1.5" :class="{ 'text-green-600 dark:text-green-400': /[A-Z]/.test(password) }">
                <span class="w-1.5 h-1.5 rounded-full" :class="/[A-Z]/.test(password) ? 'bg-green-500' : 'bg-gray-300 dark:bg-zinc-700'"></span>
                Huruf besar (A-Z)
            </li>
            <li class="flex items-center gap-1.5" :class="{ 'text-green-600 dark:text-green-400': /[0-9]/.test(password) }">
                <span class="w-1.5 h-1.5 rounded-full" :class="/[0-9]/.test(password) ? 'bg-green-500' : 'bg-gray-300 dark:bg-zinc-700'"></span>
                Angka (0-9)
            </li>
        </ul>
    </div>
</template>

