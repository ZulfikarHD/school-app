<script setup lang="ts">
/**
 * PhotoUpload - Modern photo upload component dengan premium aesthetic
 * yang mencakup drag & drop, preview, validation, dan haptic feedback
 * untuk memastikan UX optimal terutama di mobile dengan touch targets yang besar
 */
import { ref, onBeforeUnmount, watch } from 'vue';
import { Camera, X, ImagePlus, Upload, User } from 'lucide-vue-next';
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

// Supress unused variable warning untuk icons yang digunakan di template
void [Camera, Upload, User];

onBeforeUnmount(() => {
    // Clean up object URLs if needed
});
</script>

<template>
    <div class="space-y-2.5">
        <!-- Label dengan unique ID untuk accessibility -->
        <label 
            v-if="label" 
            :id="`photo-upload-label-${$.uid}`"
            class="block text-[11px] font-semibold tracking-wide uppercase text-slate-600 dark:text-slate-400 pl-1"
        >
            {{ label }}
        </label>

        <!-- Upload Container dengan proper accessibility -->
        <Motion 
            :whileTap="{ scale: previewUrl ? 1 : 0.98 }"
        >
            <div
                class="relative group cursor-pointer transition-all duration-300"
                :class="[
                    'rounded-2xl overflow-hidden',
                    isDragging
                        ? 'ring-2 ring-emerald-500 ring-offset-2 dark:ring-offset-zinc-900'
                        : '',
                    error 
                        ? 'ring-2 ring-red-400 ring-offset-2 dark:ring-offset-zinc-900' 
                        : '',
                ]"
                role="button"
                tabindex="0"
                :aria-labelledby="label ? `photo-upload-label-${$.uid}` : undefined"
                :aria-describedby="hint && !error ? `photo-upload-hint-${$.uid}` : error ? `photo-upload-error-${$.uid}` : undefined"
                @dragenter.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @dragover.prevent
                @drop.prevent="handleDrop"
                @click="triggerBrowse"
                @keydown.enter="triggerBrowse"
                @keydown.space.prevent="triggerBrowse"
            >
                <input
                    ref="fileInput"
                    type="file"
                    class="hidden"
                    accept="image/png, image/jpeg, image/jpg"
                    :aria-label="label || 'Upload foto'"
                    @change="handleFileSelect"
                />

                <!-- Preview State - Modern card design -->
                <div v-if="previewUrl" class="relative aspect-3/4 w-full max-w-[180px] mx-auto">
                    <!-- Outer glow effect -->
                    <div class="absolute -inset-1 bg-linear-to-br from-emerald-400/20 via-teal-400/20 to-cyan-400/20 rounded-2xl blur-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    
                    <!-- Image container -->
                    <div class="relative h-full bg-linear-to-br from-slate-100 to-slate-200 dark:from-zinc-800 dark:to-zinc-900 rounded-2xl overflow-hidden shadow-lg shadow-slate-200/50 dark:shadow-zinc-900/50 border border-slate-200/50 dark:border-zinc-700/50">
                        <img
                            :src="previewUrl"
                            alt="Preview Foto"
                            class="w-full h-full object-cover"
                        />

                        <!-- Hover/Touch Overlay - Gradient dengan glassmorphism effect -->
                        <div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 active:opacity-100 transition-all duration-300 flex flex-col items-center justify-end pb-5">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center mb-2 border border-white/20">
                                <ImagePlus class="w-5 h-5 text-white" />
                            </div>
                            <p class="text-white text-sm font-semibold">Ganti Foto</p>
                            <p class="text-white/70 text-xs mt-0.5">Klik untuk memilih</p>
                        </div>
                    </div>

                    <!-- Remove Button - Modern floating design -->
                    <Motion :whileTap="{ scale: 0.9 }">
                        <button
                            @click.stop="removePhoto"
                            class="absolute -top-2 -right-2 w-10 h-10 flex items-center justify-center bg-white dark:bg-zinc-800 rounded-xl text-slate-400 hover:text-red-500 hover:bg-red-50 active:bg-red-100 dark:hover:text-red-400 dark:hover:bg-red-900/20 transition-all duration-200 shadow-lg shadow-slate-900/10 dark:shadow-black/20 border border-slate-200 dark:border-zinc-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50 focus-visible:ring-offset-2"
                            type="button"
                            aria-label="Hapus foto"
                        >
                            <X class="w-4 h-4" aria-hidden="true" />
                        </button>
                    </Motion>
                </div>

                <!-- Empty State - Modern premium design -->
                <div 
                    v-else 
                    class="relative aspect-3/4 w-full max-w-[180px] mx-auto"
                >
                    <!-- Background pattern -->
                    <div 
                        class="absolute inset-0 rounded-2xl transition-all duration-300 overflow-hidden"
                        :class="[
                            isDragging
                                ? 'bg-linear-to-br from-emerald-50 via-teal-50 to-cyan-50 dark:from-emerald-950/30 dark:via-teal-950/30 dark:to-cyan-950/30'
                                : 'bg-linear-to-br from-slate-50 via-slate-100 to-slate-50 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-900'
                        ]"
                    >
                        <!-- Subtle grid pattern -->
                        <div class="absolute inset-0 opacity-30 dark:opacity-20" style="background-image: radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0); background-size: 24px 24px;"></div>
                    </div>

                    <!-- Main content container -->
                    <div 
                        class="relative h-full flex flex-col items-center justify-center text-center p-4 rounded-2xl border-2 border-dashed transition-all duration-300"
                        :class="[
                            isDragging
                                ? 'border-emerald-400 dark:border-emerald-500'
                                : error
                                    ? 'border-red-300 dark:border-red-500/50'
                                    : 'border-slate-200 dark:border-zinc-700 hover:border-emerald-300 dark:hover:border-emerald-600'
                        ]"
                    >
                        <!-- Avatar placeholder dengan gradient -->
                        <Motion
                            :animate="{ 
                                scale: isDragging ? 1.05 : 1,
                                y: isDragging ? -4 : 0
                            }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 20 }"
                        >
                            <div 
                                class="relative w-16 h-16 rounded-2xl flex items-center justify-center mb-4 transition-all duration-300"
                                :class="isDragging 
                                    ? 'bg-linear-to-br from-emerald-400 to-teal-500 shadow-lg shadow-emerald-500/30' 
                                    : 'bg-linear-to-br from-slate-200 to-slate-300 dark:from-zinc-700 dark:to-zinc-600'"
                            >
                                <!-- Inner icon -->
                                <User 
                                    class="w-8 h-8 transition-colors duration-300"
                                    :class="isDragging ? 'text-white' : 'text-slate-400 dark:text-zinc-500'"
                                />
                                
                                <!-- Camera badge -->
                                <div 
                                    class="absolute -bottom-1.5 -right-1.5 w-7 h-7 rounded-lg flex items-center justify-center transition-all duration-300 shadow-md"
                                    :class="isDragging 
                                        ? 'bg-white text-emerald-600' 
                                        : 'bg-white dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 border border-slate-200 dark:border-zinc-600'"
                                >
                                    <Camera class="w-3.5 h-3.5" />
                                </div>
                            </div>
                        </Motion>

                        <!-- Text content -->
                        <div class="space-y-1">
                            <p 
                                class="text-sm font-semibold transition-colors duration-200"
                                :class="isDragging ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-300'"
                            >
                                {{ isDragging ? 'Lepaskan di sini' : 'Upload Foto' }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ isDragging ? 'File siap diupload' : 'Klik atau drag file' }}
                            </p>
                        </div>

                        <!-- File format info - Modern pill design -->
                        <div class="flex items-center gap-2 mt-4">
                            <span class="px-2 py-1 rounded-md bg-slate-100 dark:bg-zinc-800 text-[10px] font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">
                                JPG
                            </span>
                            <span class="px-2 py-1 rounded-md bg-slate-100 dark:bg-zinc-800 text-[10px] font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">
                                PNG
                            </span>
                            <span class="text-[10px] text-slate-400 dark:text-zinc-500">
                                Max {{ maxSize }}MB
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </Motion>

        <!-- Hint Text - Enhanced styling -->
        <p 
            v-if="hint && !error" 
            :id="`photo-upload-hint-${$.uid}`"
            class="text-xs text-slate-500 dark:text-slate-400 pl-1 font-medium flex items-center gap-1.5"
        >
            <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-zinc-600"></span>
            {{ hint }}
        </p>

        <!-- Error Message - Enhanced styling -->
        <p 
            v-if="error" 
            :id="`photo-upload-error-${$.uid}`"
            class="text-xs text-red-500 dark:text-red-400 pl-1 font-medium flex items-center gap-1.5"
            role="alert"
        >
            <span class="w-1 h-1 rounded-full bg-red-400"></span>
            {{ error }}
        </p>
    </div>
</template>
