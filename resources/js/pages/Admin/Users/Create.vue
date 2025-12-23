<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import UserForm from '@/components/ui/UserForm.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import { ChevronLeft, UserPlus } from 'lucide-vue-next';
import { index, store } from '@/routes/admin/users';
import { Motion } from 'motion-v';

const form = useForm({
    name: '',
    email: '',
    username: '',
    phone_number: '',
    role: '',
    status: 'ACTIVE',
});

const haptics = useHaptics();
const modal = useModal();

const submit = () => {
    haptics.medium();
    form.post(store().url, {
        onSuccess: () => {
            haptics.success();
            modal.success('User berhasil ditambahkan. Password default telah dikirim ke email user.');
        },
        onError: () => {
            haptics.error();
            modal.error('Gagal menambahkan user. Periksa kembali input Anda.');
        },
    });
};
</script>

<template>
    <Head title="Tambah User Baru" />

    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <Motion 
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
                class="flex items-center gap-4"
            >
                <Link
                    :href="index()"
                    class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-xl transition-colors"
                >
                    <ChevronLeft class="w-6 h-6 text-gray-500" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <UserPlus class="w-7 h-7 text-blue-600 dark:text-blue-500" />
                        Tambah User Baru
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Buat akun baru untuk Guru, Siswa, Orang Tua, atau Staff.
                    </p>
                </div>
            </Motion>

            <!-- Form Card -->
            <Motion 
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.1 }"
                class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6 md:p-8"
            >
                <form @submit.prevent="submit">
                    <UserForm :form="form" />

                    <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100 dark:border-zinc-800">
                        <Link
                            :href="index()"
                            class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-xl transition-colors"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan User</span>
                        </button>
                    </div>
                </form>
            </Motion>
        </div>
    </AppLayout>
</template>

