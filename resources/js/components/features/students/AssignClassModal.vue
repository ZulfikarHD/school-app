<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import { assignClass } from '@/routes/admin/students';
import FormSelect from '@/components/ui/Form/FormSelect.vue';
import FormTextarea from '@/components/ui/Form/FormTextarea.vue';
import BaseModal from '@/components/ui/BaseModal.vue';

interface Props {
    show: boolean;
    students: Array<{ id: number; nama_lengkap: string }>;
    classes: Array<{ id: number; nama_lengkap: string; tahun_ajaran: string }>;
    currentClassId?: number | null;
}

const props = defineProps<Props>();
const emit = defineEmits(['close', 'success']);

const modal = useModal();
const haptics = useHaptics();

const form = useForm({
    student_ids: [] as number[],
    kelas_id: props.currentClassId || null,
    tahun_ajaran: '2024/2025',
    notes: '',
});

// Reset form ketika modal dibuka
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        form.kelas_id = props.currentClassId || null;
        form.notes = '';
        form.clearErrors();
    }
});

// Computed
const title = computed(() => {
    return props.students.length === 1
        ? 'Pindah Kelas Siswa'
        : `Pindah Kelas (${props.students.length} Siswa)`;
});

const classOptions = computed(() => {
    if (!props.classes || !Array.isArray(props.classes) || props.classes.length === 0) {
        return [];
    }
    
    return props.classes.map(c => {
        if (!c || typeof c.id === 'undefined') {
            return null;
        }
        return {
            value: c.id,
            label: `${c.nama_lengkap || c.nama || 'Kelas'} (${c.tahun_ajaran || 'N/A'})`
        };
    }).filter(Boolean);
});

// Methods
const handleSubmit = () => {
    haptics.medium();

    if (!form.kelas_id) {
        modal.error('Pilih kelas tujuan terlebih dahulu.');
        return;
    }

    form.student_ids = props.students.map(s => s.id);

    form.post(assignClass().url, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            modal.success(`${props.students.length} siswa berhasil dipindahkan.`);
            emit('success');
            emit('close');
            form.reset();
        },
        onError: () => {
            haptics.heavy();
        }
    });
};

const handleClose = () => {
    emit('close');
    form.reset();
};
</script>

<template>
    <BaseModal
        :show="show"
        :title="title"
        @close="handleClose"
        max-width="md"
    >
        <form @submit.prevent="handleSubmit" class="p-4 space-y-4" aria-label="Form pindah kelas siswa">
            <!-- Summary Info -->
            <div class="bg-slate-50 dark:bg-zinc-800/50 p-3 rounded-xl border border-slate-200 dark:border-zinc-800">
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    <span v-if="students.length === 1">
                        Siswa: <span class="font-semibold text-slate-900 dark:text-slate-100">{{ students[0].nama_lengkap }}</span>
                    </span>
                    <span v-else>
                        Memindahkan <span class="font-semibold text-slate-900 dark:text-slate-100">{{ students.length }} siswa</span> terpilih.
                    </span>
                </p>
            </div>

            <!-- Class Selection -->
            <FormSelect
                v-model="form.kelas_id"
                label="Pilih Kelas Tujuan"
                :options="classOptions"
                :error="form.errors.kelas_id"
                required
                placeholder="Pilih kelas..."
            />

            <!-- Notes (Optional) -->
            <FormTextarea
                v-model="form.notes"
                label="Catatan (Opsional)"
                :rows="2"
                :error="form.errors.notes"
                placeholder="Alasan perpindahan..."
            />

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <button
                    type="button"
                    @click="handleClose"
                    class="px-4 py-2.5 min-h-[44px] text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-zinc-800 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500/50 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    :disabled="form.processing"
                    :aria-busy="form.processing"
                    class="px-4 py-2.5 min-h-[44px] text-sm font-medium text-white bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 rounded-xl shadow-lg shadow-emerald-500/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                >
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </BaseModal>
</template>
