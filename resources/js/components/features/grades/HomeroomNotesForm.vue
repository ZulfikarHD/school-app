<script setup lang="ts">
/**
 * HomeroomNotesForm - Form textarea untuk catatan wali kelas
 * dengan character counter dan template suggestions
 */
import { ref, computed } from 'vue';
import { MessageSquare, Sparkles } from 'lucide-vue-next';

interface Props {
    modelValue: string;
    maxLength?: number;
    error?: string;
    placeholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
    maxLength: 500,
    placeholder: 'Tulis catatan untuk siswa ini...'
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const showTemplates = ref(false);

/**
 * Character count
 */
const charCount = computed(() => props.modelValue.length);

/**
 * Check if over limit
 */
const isOverLimit = computed(() => charCount.value > props.maxLength);

/**
 * Template catatan yang umum digunakan
 */
const templates = [
    {
        category: 'Positif',
        items: [
            'Siswa menunjukkan perkembangan yang sangat baik dalam semester ini. Terus pertahankan prestasi dan semangat belajarnya.',
            'Siswa aktif dalam kegiatan belajar mengajar dan mampu bekerja sama dengan baik bersama teman-temannya.',
            'Siswa memiliki sikap disiplin yang baik dan selalu menyelesaikan tugas tepat waktu.'
        ]
    },
    {
        category: 'Perlu Perhatian',
        items: [
            'Siswa perlu meningkatkan kehadiran dan kedisiplinan dalam mengikuti pelajaran.',
            'Siswa perlu lebih aktif dalam kegiatan diskusi dan bertanya saat tidak memahami materi.',
            'Siswa perlu lebih fokus dalam mengerjakan tugas dan mengurangi kesalahan karena kurang teliti.'
        ]
    },
    {
        category: 'Dukungan Orang Tua',
        items: [
            'Mohon dukungan orang tua untuk mengawasi jam belajar siswa di rumah.',
            'Siswa memerlukan pendampingan lebih dalam mata pelajaran tertentu. Mohon kerja sama orang tua.',
            'Mohon orang tua membantu memotivasi siswa untuk lebih percaya diri dalam menyampaikan pendapat.'
        ]
    }
];

/**
 * Handle textarea input
 */
const handleInput = (event: Event) => {
    const target = event.target as HTMLTextAreaElement;
    emit('update:modelValue', target.value);
};

/**
 * Apply template
 */
const applyTemplate = (template: string) => {
    emit('update:modelValue', template);
    showTemplates.value = false;
};
</script>

<template>
    <div class="space-y-3">
        <!-- Textarea -->
        <div class="relative">
            <textarea
                :value="modelValue"
                @input="handleInput"
                :maxlength="maxLength"
                :placeholder="placeholder"
                rows="5"
                :class="[
                    'w-full px-4 py-3 rounded-xl border bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 transition-colors resize-none',
                    error
                        ? 'border-red-300 dark:border-red-700 focus:ring-red-500'
                        : isOverLimit
                            ? 'border-amber-300 dark:border-amber-700 focus:ring-amber-500'
                            : 'border-slate-200 dark:border-zinc-700 focus:ring-violet-500'
                ]"
            />
            
            <!-- Character Counter -->
            <div class="absolute bottom-3 right-3 flex items-center gap-2">
                <span
                    :class="[
                        'text-xs',
                        isOverLimit
                            ? 'text-red-500'
                            : charCount > maxLength * 0.8
                                ? 'text-amber-500'
                                : 'text-slate-400 dark:text-slate-500'
                    ]"
                >
                    {{ charCount }}/{{ maxLength }}
                </span>
            </div>
        </div>

        <!-- Error Message -->
        <p v-if="error" class="text-sm text-red-500 dark:text-red-400">
            {{ error }}
        </p>

        <!-- Template Suggestions -->
        <div class="relative">
            <button
                type="button"
                @click="showTemplates = !showTemplates"
                class="flex items-center gap-2 text-sm text-violet-600 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300 transition-colors"
            >
                <Sparkles class="w-4 h-4" />
                Template Catatan
                <svg
                    :class="['w-4 h-4 transition-transform', showTemplates && 'rotate-180']"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Template Dropdown -->
            <div
                v-if="showTemplates"
                class="absolute left-0 right-0 mt-2 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-700 shadow-lg z-10 max-h-80 overflow-y-auto"
            >
                <div
                    v-for="category in templates"
                    :key="category.category"
                    class="border-b border-slate-100 dark:border-zinc-800 last:border-b-0"
                >
                    <div class="px-4 py-2 bg-slate-50 dark:bg-zinc-800/50">
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            {{ category.category }}
                        </span>
                    </div>
                    <div class="py-1">
                        <button
                            v-for="(item, idx) in category.items"
                            :key="idx"
                            type="button"
                            @click="applyTemplate(item)"
                            class="w-full px-4 py-2 text-left text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors"
                        >
                            {{ item }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
