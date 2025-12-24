<script setup lang="ts">
/**
 * PhotoUpload - Photo upload component dengan refined aesthetic
 * yang mencakup drag & drop, preview, validation, dan haptic feedback
 * untuk memastikan UX optimal terutama di mobile dengan touch targets yang besar
 */
import { ref, onBeforeUnmount, watch } from 'vue';
import { Camera, X, ImagePlus } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';

interface Props {
    modelValue?: string | File | null;
    error?: string;
    label?: string;
    hint?: string;
    maxSize?: number; // in MB
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Foto Siswa',
    maxSize: 2,
});

const emit = defineEmits(['update:modelValue', 'change']);

const haptics = useHaptics();
const fileInput = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);
const isDragging = ref(false);

// Initialize preview from modelValue jika berupa string URL
watch(
    () => props.modelValue,
    (newVal) => {
        if (typeof newVal === 'string' && newVal) {
            previewUrl.value = newVal;
        }
    },
    { immediate: true }
);

const handleFileSelect = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        processFile(input.files[0]);
    }
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
        processFile(event.dataTransfer.files[0]);
    }
};

const processFile = (file: File) => {
    // Validate type
    if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
        haptics.error();
        alert('Hanya file gambar (JPG, PNG) yang diperbolehkan.');
        return;
    }

    // Validate size
    const maxBytes = props.maxSize * 1024 * 1024;
    if (file.size > maxBytes) {
        haptics.error();
        alert(`Ukuran file maksimal ${props.maxSize}MB.`);
        return;
    }

    haptics.success();
    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        previewUrl.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);

    emit('update:modelValue', file);
    emit('change', file);
};

const removePhoto = () => {
    haptics.light();
    previewUrl.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    emit('update:modelValue', null);
    emit('change', null);
};

const triggerBrowse = () => {
    haptics.light();
    fileInput.value?.click();
};

onBeforeUnmount(() => {
    // Clean up object URLs if needed
});
</script>

<template>
    <div class="space-y-2">
        <!-- Label -->
        <label v-if="label" class="block text-[11px] font-semibold tracking-wide uppercase text-slate-600 dark:text-slate-400 pl-1">
            {{ label }}
        </label>

        <!-- Upload Container -->
        <Motion 
            :whileTap="{ scale: previewUrl ? 1 : 0.98 }"
        >
            <div
                class="relative group cursor-pointer transition-all duration-200"
                :class="[
                    'rounded-xl overflow-hidden',
                    isDragging
                        ? 'ring-2 ring-emerald-500 ring-offset-2 dark:ring-offset-zinc-900'
                        : '',
                    error 
                        ? 'ring-2 ring-red-400 ring-offset-2 dark:ring-offset-zinc-900' 
                        : '',
                ]"
                @dragenter.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @dragover.prevent
                @drop.prevent="handleDrop"
                @click="triggerBrowse"
            >
                <input
                    ref="fileInput"
                    type="file"
                    class="hidden"
                    accept="image/png, image/jpeg, image/jpg"
                    @change="handleFileSelect"
                />

                <!-- Preview State -->
                <div v-if="previewUrl" class="relative aspect-[3/4] w-full max-w-[160px] mx-auto bg-slate-100 dark:bg-zinc-800 rounded-xl overflow-hidden">
                    <img
                        :src="previewUrl"
                        alt="Preview Foto"
                        class="w-full h-full object-cover"
                    />

                    <!-- Hover/Touch Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-0 group-hover:opacity-100 active:opacity-100 transition-opacity duration-200 flex flex-col items-center justify-end pb-4">
                        <ImagePlus class="w-5 h-5 text-white mb-1" />
                        <p class="text-white text-xs font-medium">Ganti Foto</p>
                    </div>

                    <!-- Remove Button - Increased touch target to 44px -->
                    <Motion :whileTap="{ scale: 0.9 }">
                        <button
                            @click.stop="removePhoto"
                            class="absolute -top-1 -right-1 w-11 h-11 flex items-center justify-center bg-white/95 dark:bg-zinc-900/95 rounded-xl text-slate-500 hover:text-red-500 active:text-red-600 dark:text-slate-400 dark:hover:text-red-400 transition-colors shadow-lg border border-slate-200 dark:border-zinc-700"
                            type="button"
                            aria-label="Hapus foto"
                        >
                            <X class="w-5 h-5" />
                        </button>
                    </Motion>
                </div>

                <!-- Empty State -->
                <div 
                    v-else 
                    class="py-8 px-4 flex flex-col items-center justify-center text-center border-2 border-dashed rounded-xl transition-colors"
                    :class="[
                        isDragging
                            ? 'border-emerald-400 bg-emerald-50/50 dark:bg-emerald-950/20'
                            : 'border-slate-200 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-900/50 hover:border-slate-300 dark:hover:border-zinc-600 hover:bg-slate-50 dark:hover:bg-zinc-900'
                    ]"
                >
                    <!-- Icon -->
                    <Motion
                        :animate="{ 
                            scale: isDragging ? 1.1 : 1,
                            y: isDragging ? -4 : 0
                        }"
                        :transition="{ duration: 0.2 }"
                    >
                        <div 
                            class="w-12 h-12 rounded-xl flex items-center justify-center mb-3 transition-colors"
                            :class="isDragging ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600' : 'bg-slate-100 dark:bg-zinc-800 text-slate-400 dark:text-slate-500'"
                        >
                            <Camera class="w-6 h-6" />
                        </div>
                    </Motion>

                    <!-- Text -->
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ isDragging ? 'Lepaskan di sini' : 'Upload foto' }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Klik atau drag file
                    </p>

                    <!-- File Info -->
                    <div class="flex items-center gap-1.5 mt-3 text-[10px] text-slate-400 dark:text-slate-500 font-medium uppercase tracking-wider">
                        <span>JPG, PNG</span>
                        <span class="w-0.5 h-0.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                        <span>Max {{ maxSize }}MB</span>
                    </div>
                </div>
            </div>
        </Motion>

        <!-- Hint Text -->
        <p v-if="hint && !error" class="text-xs text-slate-500 dark:text-slate-400 pl-1 font-medium">
            {{ hint }}
        </p>

        <!-- Error Message -->
        <p v-if="error" class="text-xs text-red-500 dark:text-red-400 pl-1 font-medium">
            {{ error }}
        </p>
    </div>
</template>
