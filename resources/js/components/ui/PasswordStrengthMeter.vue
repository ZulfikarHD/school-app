<script setup lang="ts">
/**
 * PasswordStrengthMeter - Visual indicator untuk kekuatan password
 * dengan progress bar, accessibility support (ARIA), dan requirement checklist
 * untuk membantu user membuat password yang aman
 */
import { computed } from 'vue';
import { Check, X } from 'lucide-vue-next';

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

const strengthLabel = computed(() => {
    const s = score.value;
    if (s === 0) return 'Belum ada input';
    if (s <= 2) return 'Password lemah - mudah ditebak';
    if (s <= 4) return 'Password sedang - cukup aman';
    return 'Password kuat - sangat aman';
});

const colorClass = computed(() => {
    const s = score.value;
    if (s === 0) return 'bg-slate-200 dark:bg-zinc-700';
    if (s <= 2) return 'bg-red-500';
    if (s <= 4) return 'bg-amber-500';
    return 'bg-emerald-500';
});

const widthPercentage = computed(() => {
    const s = score.value;
    if (s === 0) return 0;
    // Map score 1-6 to percentage 16% - 100%
    return Math.min(100, Math.max(10, (s / 6) * 100));
});

// Requirement checks untuk checklist
const requirements = computed(() => [
    {
        met: props.password.length >= props.minLength,
        label: `Minimal ${props.minLength} karakter`,
    },
    {
        met: /[A-Z]/.test(props.password),
        label: 'Huruf besar (A-Z)',
    },
    {
        met: /[a-z]/.test(props.password),
        label: 'Huruf kecil (a-z)',
    },
    {
        met: /[0-9]/.test(props.password),
        label: 'Angka (0-9)',
    },
    {
        met: /[^A-Za-z0-9]/.test(props.password),
        label: 'Karakter khusus (!@#$%)',
    },
]);
</script>

<template>
    <div class="w-full space-y-3">
        <!-- Header dengan label dan strength indicator -->
        <div class="flex justify-between items-center">
            <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Kekuatan Password</span>
            <span 
                v-if="props.password" 
                class="text-xs font-bold transition-colors duration-300 px-2 py-0.5 rounded-md"
                :class="{
                    'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/30': score <= 2,
                    'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30': score > 2 && score <= 4,
                    'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30': score > 4
                }"
            >
                {{ strength }}
            </span>
        </div>
        
        <!-- Progress bar dengan ARIA untuk accessibility -->
        <div 
            role="progressbar"
            :aria-valuenow="score"
            aria-valuemin="0"
            aria-valuemax="6"
            :aria-label="strengthLabel"
            class="h-2 w-full bg-slate-100 rounded-full overflow-hidden dark:bg-zinc-800"
        >
            <div 
                class="h-full transition-all duration-500 ease-out rounded-full"
                :class="colorClass"
                :style="{ width: `${widthPercentage}%` }"
            />
        </div>

        <!-- Requirements checklist dengan readable text size -->
        <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1.5 mt-3">
            <li 
                v-for="(req, index) in requirements" 
                :key="index"
                class="flex items-center gap-2 transition-colors duration-200"
                :class="req.met ? 'text-emerald-600 dark:text-emerald-400' : ''"
            >
                <span 
                    class="flex items-center justify-center w-4 h-4 rounded-full transition-all duration-200"
                    :class="req.met 
                        ? 'bg-emerald-500 text-white' 
                        : 'bg-slate-200 dark:bg-zinc-700'"
                >
                    <Check v-if="req.met" class="w-2.5 h-2.5" :stroke-width="3" />
                    <span v-else class="w-1 h-1 rounded-full bg-slate-400 dark:bg-zinc-500" />
                </span>
                {{ req.label }}
            </li>
        </ul>
    </div>
</template>

