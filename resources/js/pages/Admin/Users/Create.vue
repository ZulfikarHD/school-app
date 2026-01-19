<script setup lang="ts">
/**
 * Users Create Page - Halaman pembuatan user baru
 * dengan form validation dan sticky action bar untuk UX yang optimal
 */
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import UserForm from '@/components/features/users/UserForm.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import { ChevronLeft, UserPlus, Save } from 'lucide-vue-next';
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
    <AppLayout title="Tambah User">
        <Head title="Tambah User Baru" />

        <div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <Motion :whileTap="{ scale: 0.95 }">
                            <Link
                                :href="index()"
                                @click="haptics.light()"
                                class="w-11 h-11 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-zinc-800 active:bg-slate-200 rounded-xl transition-colors shrink-0
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                            >
                                <ChevronLeft class="w-5 h-5" />
                            </Link>
                        </Motion>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                    <UserPlus class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100 truncate">
                                    Tambah User Baru
                                </h1>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                Buat akun baru untuk Guru, Orang Tua, atau Staff
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Form Card -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 p-4 sm:p-6 md:p-8">
                        <UserForm :form="form" />
                    </div>
                </Motion>

                <!-- Sticky Action Bar - Consistent dengan Students pages -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
                >
                    <div class="sticky bottom-0 z-10 -mx-4 sm:mx-0 px-4 sm:px-0 pb-6 pt-4">
                        <div class="bg-white/98 dark:bg-zinc-900/98 border border-slate-200 dark:border-zinc-800 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl backdrop-blur-sm">
                            <p class="text-sm text-slate-600 dark:text-slate-400 hidden sm:block">
                                Password akan digenerate otomatis dan dikirim via email
                            </p>
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <Link
                                        :href="index()"
                                        @click="haptics.light()"
                                        class="flex-1 sm:flex-none px-5 py-3 min-h-[48px] text-slate-700 bg-slate-50 border border-slate-300 rounded-xl hover:bg-slate-100 dark:bg-zinc-800 dark:border-zinc-700 dark:text-slate-300 dark:hover:bg-zinc-700 transition-colors font-medium text-center flex items-center justify-center
                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500"
                                    >
                                        Batal
                                    </Link>
                                </Motion>
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-3 min-h-[48px] bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-lg shadow-emerald-500/25 font-semibold
                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                    >
                                        <Save class="w-5 h-5" />
                                        <span v-if="form.processing">Menyimpan...</span>
                                        <span v-else>Simpan User</span>
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </div>
                </Motion>
            </form>
        </div>
    </AppLayout>
</template>

