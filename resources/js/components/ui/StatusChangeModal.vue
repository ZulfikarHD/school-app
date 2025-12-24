<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { X, AlertTriangle, CheckCircle } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';

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

const submit = () => {
    if (form.status === props.currentStatus) {
        alert('Silakan pilih status baru yang berbeda.');
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
            haptics.heavy();
        }
    });
};

const statuses = [
    { value: 'aktif', label: 'Aktif' },
    { value: 'mutasi', label: 'Mutasi Keluar' },
    { value: 'do', label: 'Drop Out' },
    { value: 'lulus', label: 'Lulus' }
];
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <Motion 
            :initial="{ opacity: 0 }"
            :animate="{ opacity: 1 }"
            :transition="{ duration: 0.2 }"
            class="fixed inset-0 bg-gray-500/75 transition-opacity" 
            @click="$emit('close')"
        />

        <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
            <Motion
                :initial="{ opacity: 0, scale: 0.95, y: 20 }"
                :animate="{ opacity: 1, scale: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
                class="relative bg-white dark:bg-zinc-900 rounded-2xl text-left overflow-hidden shadow-sm transform sm:my-8 sm:max-w-lg sm:w-full border border-gray-200 dark:border-zinc-800"
            >
                
                <!-- Header -->
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200 dark:border-zinc-800 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                        Update Status Siswa
                    </h3>
                    <Motion :whileTap="{ scale: 0.9 }">
                        <button @click="$emit('close'); haptics.light()" class="text-gray-400 hover:text-gray-500 transition-colors rounded-xl p-1">
                            <X class="w-5 h-5" />
                        </button>
                    </Motion>
                </div>

                <!-- Body -->
                <form @submit.prevent="submit">
                    <div class="px-4 py-5 sm:p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status Baru <span class="text-red-500">*</span></label>
                            <select v-model="form.status" class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800">
                                <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Perubahan <span class="text-red-500">*</span></label>
                            <input v-model="form.tanggal" type="date" class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800" />
                            <p v-if="form.errors.tanggal" class="text-sm text-red-600 mt-1">{{ form.errors.tanggal }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alasan <span class="text-red-500">*</span></label>
                            <textarea v-model="form.alasan" rows="2" class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800" placeholder="Contoh: Pindah tugas orang tua"></textarea>
                            <p v-if="form.errors.alasan" class="text-sm text-red-600 mt-1">{{ form.errors.alasan }}</p>
                        </div>

                         <div v-if="form.status === 'mutasi'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sekolah Tujuan <span class="text-red-500">*</span></label>
                            <input v-model="form.sekolah_tujuan" type="text" class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800" />
                            <p v-if="form.errors.sekolah_tujuan" class="text-sm text-red-600 mt-1">{{ form.errors.sekolah_tujuan }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan Tambahan</label>
                            <textarea v-model="form.keterangan" rows="2" class="w-full rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800"></textarea>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse bg-gray-50 dark:bg-zinc-800/50 border-t border-gray-200 dark:border-zinc-800 gap-3">
                        <Motion :whileTap="{ scale: 0.97 }">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                @click="haptics.medium()"
                                class="w-full inline-flex justify-center rounded-xl border border-blue-700 shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>Simpan Status</span>
                            </button>
                        </Motion>
                        <Motion :whileTap="{ scale: 0.97 }">
                            <button 
                                type="button" 
                                @click="$emit('close'); haptics.light()"
                                class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-zinc-900 dark:border-zinc-700 dark:text-gray-300 dark:hover:bg-zinc-800 transition-colors"
                            >
                                Batal
                            </button>
                        </Motion>
                    </div>
                </form>
            </Motion>
        </div>
    </div>
</template>

