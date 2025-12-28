<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import { Send, Calendar, FileText, Upload, X, AlertCircle } from 'lucide-vue-next';
import type { Student } from '@/types/student';

/**
 * Form pengajuan permohonan izin untuk orang tua
 * dengan file upload dan date range selection
 */

interface Props {
    title: string;
    children: Student[];
}

defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

const attachmentPreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

/**
 * Form data untuk submission
 */
const form = useForm({
    student_id: null as number | null,
    jenis: 'SAKIT' as 'IZIN' | 'SAKIT',
    tanggal_mulai: new Date().toISOString().split('T')[0],
    tanggal_selesai: new Date().toISOString().split('T')[0],
    alasan: '',
    attachment: null as File | null
});

/**
 * Calculated duration dalam hari
 */
const duration = computed(() => {
    if (!form.tanggal_mulai || !form.tanggal_selesai) return 0;
    
    const start = new Date(form.tanggal_mulai);
    const end = new Date(form.tanggal_selesai);
    
    const diffTime = Math.abs(end.getTime() - start.getTime());
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
});

/**
 * Handle file selection
 */
const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (!file) return;
    
    // Validate file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
        modal.error('Ukuran file maksimal 2MB');
        target.value = '';
        return;
    }
    
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    if (!allowedTypes.includes(file.type)) {
        modal.error('Format file harus JPG, PNG, atau PDF');
        target.value = '';
        return;
    }
    
    form.attachment = file;
    
    // Create preview for images
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            attachmentPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    } else {
        attachmentPreview.value = 'pdf';
    }
    
    haptics.light();
};

/**
 * Remove attachment
 */
const removeAttachment = () => {
    form.attachment = null;
    attachmentPreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    haptics.light();
};

/**
 * Submit leave request
 */
const submitLeaveRequest = () => {
    if (!form.student_id) {
        modal.error('Mohon pilih anak terlebih dahulu');
        return;
    }
    
    if (!form.alasan.trim()) {
        modal.error('Mohon isi alasan permohonan izin');
        return;
    }
    
    haptics.medium();
    
    form.post('/parent/leave-requests', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            haptics.success();
            modal.success('Permohonan izin berhasil diajukan. Menunggu persetujuan wali kelas.');
        },
        onError: (errors) => {
            haptics.error();
            const message = errors.message || 'Gagal mengajukan permohonan izin';
            modal.error(message);
        }
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />
        
        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="bg-white px-6 py-8 border-b border-gray-100 dark:bg-zinc-900 dark:border-zinc-800">
                    <div class="mx-auto max-w-3xl">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Ajukan permohonan izin untuk anak Anda</p>
                    </div>
                </div>
            </Motion>
            
            <div class="mx-auto max-w-3xl px-6 py-8">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                >
                    <form @submit.prevent="submitLeaveRequest" class="space-y-6">
                        <!-- Child Selection -->
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-3">
                                Pilih Anak <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.student_id"
                                required
                                class="w-full h-[52px] px-4 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                       rounded-xl text-slate-900 dark:text-white
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 focus:bg-white
                                       transition-all duration-150"
                            >
                                <option :value="null">-- Pilih Anak --</option>
                                <option v-for="child in children" :key="child.id" :value="child.id">
                                    {{ child.nama_lengkap }} - {{ child.kelas?.nama_lengkap }}
                                </option>
                            </select>
                            <p v-if="form.errors.student_id" class="mt-2 text-sm text-red-600">{{ form.errors.student_id }}</p>
                        </div>
                        
                        <!-- Leave Type -->
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-3">
                                Jenis Izin <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <label
                                        :class="[
                                            'relative flex items-center justify-center p-4 rounded-xl border-2 cursor-pointer transition-all duration-150',
                                            form.jenis === 'SAKIT'
                                                ? 'border-sky-500 bg-sky-50/80 dark:bg-sky-950/30'
                                                : 'border-slate-200 dark:border-zinc-700 bg-slate-50/80 dark:bg-zinc-800 hover:border-slate-300'
                                        ]"
                                    >
                                        <input
                                            type="radio"
                                            v-model="form.jenis"
                                            value="SAKIT"
                                            class="sr-only"
                                            @change="haptics.selection()"
                                        />
                                        <span :class="[
                                            'font-semibold',
                                            form.jenis === 'SAKIT' ? 'text-sky-700 dark:text-sky-400' : 'text-slate-700 dark:text-zinc-300'
                                        ]">
                                            Sakit
                                        </span>
                                    </label>
                                </Motion>
                                
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <label
                                        :class="[
                                            'relative flex items-center justify-center p-4 rounded-xl border-2 cursor-pointer transition-all duration-150',
                                            form.jenis === 'IZIN'
                                                ? 'border-amber-500 bg-amber-50/80 dark:bg-amber-950/30'
                                                : 'border-slate-200 dark:border-zinc-700 bg-slate-50/80 dark:bg-zinc-800 hover:border-slate-300'
                                        ]"
                                    >
                                        <input
                                            type="radio"
                                            v-model="form.jenis"
                                            value="IZIN"
                                            class="sr-only"
                                            @change="haptics.selection()"
                                        />
                                        <span :class="[
                                            'font-semibold',
                                            form.jenis === 'IZIN' ? 'text-amber-700 dark:text-amber-400' : 'text-slate-700 dark:text-zinc-300'
                                        ]">
                                            Izin
                                        </span>
                                    </label>
                                </Motion>
                            </div>
                            <p v-if="form.errors.jenis" class="mt-2 text-sm text-red-600">{{ form.errors.jenis }}</p>
                        </div>
                        
                        <!-- Date Range -->
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-3">
                                Rentang Tanggal <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-slate-600 dark:text-zinc-400 mb-2">Tanggal Mulai</label>
                                    <div class="relative">
                                        <Calendar class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                                        <input
                                            v-model="form.tanggal_mulai"
                                            type="date"
                                            required
                                            :min="new Date().toISOString().split('T')[0]"
                                            class="w-full h-[52px] pl-12 pr-4 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                   rounded-xl text-slate-900 dark:text-white
                                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 focus:bg-white
                                                   transition-all duration-150"
                                        />
                                    </div>
                                    <p v-if="form.errors.tanggal_mulai" class="mt-2 text-sm text-red-600">{{ form.errors.tanggal_mulai }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-xs text-slate-600 dark:text-zinc-400 mb-2">Tanggal Selesai</label>
                                    <div class="relative">
                                        <Calendar class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                                        <input
                                            v-model="form.tanggal_selesai"
                                            type="date"
                                            required
                                            :min="form.tanggal_mulai"
                                            class="w-full h-[52px] pl-12 pr-4 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                   rounded-xl text-slate-900 dark:text-white
                                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 focus:bg-white
                                                   transition-all duration-150"
                                        />
                                    </div>
                                    <p v-if="form.errors.tanggal_selesai" class="mt-2 text-sm text-red-600">{{ form.errors.tanggal_selesai }}</p>
                                </div>
                            </div>
                            
                            <div v-if="duration > 0" class="mt-3 p-3 bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200/50 rounded-lg">
                                <p class="text-sm text-emerald-700 dark:text-emerald-400">
                                    Durasi: <strong>{{ duration }} hari</strong>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Reason -->
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-3">
                                Alasan <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <FileText class="absolute left-4 top-4 text-slate-400" :size="18" />
                                <textarea
                                    v-model="form.alasan"
                                    required
                                    rows="4"
                                    placeholder="Contoh: Demam tinggi 39°C, perlu istirahat di rumah..."
                                    class="w-full pl-12 pr-4 py-3 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-900 dark:text-white placeholder-slate-400
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 focus:bg-white
                                           transition-all duration-150 resize-none"
                                ></textarea>
                            </div>
                            <p v-if="form.errors.alasan" class="mt-2 text-sm text-red-600">{{ form.errors.alasan }}</p>
                        </div>
                        
                        <!-- Attachment Upload -->
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-3">
                                Lampiran (Opsional)
                            </label>
                            <p class="text-sm text-slate-600 dark:text-zinc-400 mb-4">
                                Upload surat dokter atau dokumen pendukung lainnya (Max. 2MB, Format: JPG, PNG, PDF)
                            </p>
                            
                            <div v-if="!form.attachment">
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <label
                                        class="flex items-center justify-center gap-3 p-6 border-2 border-dashed border-slate-300 dark:border-zinc-700
                                               rounded-xl cursor-pointer hover:border-emerald-500/50 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/20
                                               transition-all duration-150"
                                    >
                                        <Upload :size="24" class="text-slate-400" />
                                        <span class="font-medium text-slate-700 dark:text-zinc-300">Pilih File</span>
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            accept="image/jpeg,image/jpg,image/png,application/pdf"
                                            class="sr-only"
                                            @change="handleFileSelect"
                                        />
                                    </label>
                                </Motion>
                            </div>
                            
                            <div v-else class="relative">
                                <div v-if="attachmentPreview === 'pdf'" class="p-6 bg-slate-50 dark:bg-zinc-800 rounded-xl border border-slate-200 dark:border-zinc-700">
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ form.attachment.name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">{{ (form.attachment.size / 1024).toFixed(2) }} KB</p>
                                </div>
                                <div v-else class="relative rounded-xl overflow-hidden">
                                    <img :src="attachmentPreview!" alt="Preview" class="w-full h-48 object-cover" />
                                </div>
                                
                                <button
                                    type="button"
                                    @click="removeAttachment"
                                    class="absolute top-2 right-2 p-2 bg-red-500 hover:bg-red-600 text-white rounded-full
                                           transition-colors duration-150"
                                >
                                    <X :size="16" />
                                </button>
                            </div>
                            
                            <p v-if="form.errors.attachment" class="mt-2 text-sm text-red-600">{{ form.errors.attachment }}</p>
                        </div>
                        
                        <!-- Global Error -->
                        <div v-if="form.errors.message" class="p-4 bg-red-50/80 border border-red-200/50 rounded-xl flex items-start gap-3">
                            <AlertCircle :size="20" class="text-red-500 flex-shrink-0 mt-0.5" />
                            <p class="text-sm text-red-600">{{ form.errors.message }}</p>
                        </div>
                        
                        <!-- Submit Button -->
                        <Motion :whileTap="{ scale: 0.97 }">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full px-6 py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold text-lg
                                       flex items-center justify-center gap-3 shadow-lg shadow-emerald-500/25
                                       disabled:opacity-50 disabled:cursor-not-allowed
                                       transition-all duration-150"
                            >
                                <Send :size="20" />
                                <span>{{ form.processing ? 'Mengirim...' : 'Kirim Permohonan' }}</span>
                            </button>
                        </Motion>
                    </form>
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>
