<script setup lang="ts">
/**
 * StatusChangeModal - Modal untuk mengubah status siswa
 * dengan form validation, BaseModal integration, dan reusable Form components
 * untuk memastikan consistency dengan design system aplikasi
 */
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { UserCheck, Calendar, FileText, School, AlertTriangle } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import BaseModal from './BaseModal.vue';
import { FormSelect, FormInput, FormTextarea } from './Form';

interface Props {
    show: boolean;
    studentId: number;
    currentStatus: string;
    updateUrl: string;
}

const props = defineProps<Props>();
const emit = defineEmits(['close', 'success']);
const haptics = useHaptics();

const form = useForm({
    status: props.currentStatus,
    tanggal: new Date().toISOString().split('T')[0],
    alasan: '',
    keterangan: '',
    sekolah_tujuan: ''
});

// Watch for show changes to reset form or update status
watch(() => props.show, (newVal) => {
    if (newVal) {
        form.status = props.currentStatus;
        form.reset('alasan', 'keterangan', 'sekolah_tujuan');
        form.tanggal = new Date().toISOString().split('T')[0];
    }
});

// Status sama dengan current - untuk validasi
const isSameStatus = computed(() => form.status === props.currentStatus);

// Validation message untuk same status
const statusValidationMessage = computed(() => {
    if (isSameStatus.value && form.status) {
        return 'Pilih status yang berbeda dari status saat ini';
    }
    return '';
});

const submit = () => {
    if (isSameStatus.value) {
        haptics.error();
        return;
    }

    haptics.medium();
    form.post(props.updateUrl, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            emit('success');
            emit('close');
        },
        onError: () => {
            haptics.error();
        }
    });
};

const handleClose = () => {
    haptics.light();
    emit('close');
};

const statuses = [
    { value: 'aktif', label: 'Aktif' },
    { value: 'mutasi', label: 'Mutasi Keluar' },
    { value: 'do', label: 'Drop Out' },
    { value: 'lulus', label: 'Lulus' }
];
</script>

<template>
    <BaseModal
        :show="show"
        size="lg"
        @close="handleClose"
    >
        <!-- Custom Header -->
        <template #header>
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                    <UserCheck class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        Update Status Siswa
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Perubahan status akan tercatat dalam riwayat siswa
                    </p>
                </div>
            </div>
        </template>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Warning jika status sama -->
            <Motion
                v-if="isSameStatus && form.status"
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.2 }"
            >
                <div class="flex items-start gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50">
                    <AlertTriangle class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                            Status tidak berubah
                        </p>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
                            {{ statusValidationMessage }}
                        </p>
                    </div>
                </div>
            </Motion>

            <!-- Status Select -->
            <FormSelect
                v-model="form.status"
                label="Status Baru"
                :options="statuses"
                :icon="UserCheck"
                :error="form.errors.status"
                required
                placeholder="Pilih status baru"
            />

            <!-- Tanggal Perubahan -->
            <FormInput
                v-model="form.tanggal"
                type="date"
                label="Tanggal Perubahan"
                :icon="Calendar"
                :error="form.errors.tanggal"
                required
            />

            <!-- Alasan -->
            <FormTextarea
                v-model="form.alasan"
                label="Alasan Perubahan"
                :error="form.errors.alasan"
                placeholder="Contoh: Pindah tugas orang tua ke kota lain"
                :rows="2"
                required
                hint="Jelaskan alasan perubahan status secara singkat"
            />

            <!-- Sekolah Tujuan (hanya untuk mutasi) -->
            <Motion
                v-if="form.status === 'mutasi'"
                :initial="{ opacity: 0, height: 0 }"
                :animate="{ opacity: 1, height: 'auto' }"
                :exit="{ opacity: 0, height: 0 }"
                :transition="{ duration: 0.2 }"
            >
                <FormInput
                    v-model="form.sekolah_tujuan"
                    label="Sekolah Tujuan"
                    :icon="School"
                    :error="form.errors.sekolah_tujuan"
                    placeholder="Nama sekolah tujuan mutasi"
                    required
                />
            </Motion>

            <!-- Keterangan Tambahan -->
            <FormTextarea
                v-model="form.keterangan"
                label="Keterangan Tambahan"
                placeholder="Informasi tambahan yang perlu dicatat (opsional)"
                :rows="2"
                hint="Opsional - tambahkan catatan jika diperlukan"
            />
        </form>

        <!-- Footer Actions -->
        <template #footer>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <Motion :whileTap="{ scale: 0.97 }">
                    <button 
                        type="button" 
                        @click="handleClose"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-all duration-200 active:scale-95"
                    >
                        Batal
                    </button>
                </Motion>
                <Motion :whileTap="{ scale: 0.97 }">
                    <button 
                        type="submit" 
                        :disabled="form.processing || isSameStatus"
                        @click="submit"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-sm font-semibold text-white shadow-sm shadow-emerald-500/25 hover:shadow-md hover:shadow-emerald-500/30 transition-all duration-200 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <UserCheck v-else class="w-4 h-4" />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Status' }}
                    </button>
                </Motion>
            </div>
        </template>
    </BaseModal>
</template>

