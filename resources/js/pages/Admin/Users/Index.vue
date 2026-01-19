<script setup lang="ts">
/**
 * Users Index Page - Halaman manajemen user dengan fitur CRUD
 * yang mencakup filter, pagination, dan aksi bulk untuk mengelola
 * akun guru, orang tua, dan staff sekolah
 */
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import UserTable from '@/components/features/users/UserTable.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import { UserPlus, UserCog } from 'lucide-vue-next';
import { create, edit, destroy, resetPassword, toggleStatus } from '@/routes/admin/users';
import { Motion } from 'motion-v';

interface Props {
    users: any;
    filters: any;
}

defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();
const loading = ref(false);

const handleEdit = (user: any) => {
    router.visit(edit.url(user.id));
};

const handleDelete = async (user: any) => {
    const confirmed = await modal.confirmDelete(
        `Apakah Anda yakin ingin menghapus user "${user.name}"? Data yang dihapus tidak dapat dikembalikan.`
    );

    if (confirmed) {
        haptics.heavy();
        router.delete(destroy(user.id).url, {
            onStart: () => loading.value = true,
            onFinish: () => loading.value = false,
            onSuccess: () => {
                modal.success('User berhasil dihapus');
                haptics.success();
            },
            onError: () => {
                haptics.error();
                modal.error('Gagal menghapus user');
            }
        });
    }
};

const handleResetPassword = async (user: any) => {
    const confirmed = await modal.confirm(
        'Reset Password',
        `Apakah Anda yakin ingin mereset password user "${user.name}"? Password baru akan dikirim ke email user.`,
        'Ya, Reset Password',
        'Batal'
    );

    if (confirmed) {
        haptics.medium();
        router.post(resetPassword(user.id).url, {}, {
            onStart: () => loading.value = true,
            onFinish: () => loading.value = false,
            onSuccess: () => {
                modal.success('Password berhasil direset dan dikirim ke email user.');
                haptics.success();
            },
            onError: () => {
                haptics.error();
                modal.error('Gagal mereset password');
            }
        });
    }
};

const handleToggleStatus = async (user: any) => {
    const newStatus = user.status === 'ACTIVE' ? 'Nonaktif' : 'Aktif';
    const action = user.status === 'ACTIVE' ? 'menonaktifkan' : 'mengaktifkan';

    const confirmed = await modal.confirm(
        `Konfirmasi ${newStatus}`,
        `Apakah Anda yakin ingin ${action} user "${user.name}"?`,
        `Ya, ${newStatus}kan`,
        'Batal'
    );

    if (confirmed) {
        haptics.medium();
        router.patch(toggleStatus(user.id).url, {}, {
            onStart: () => loading.value = true,
            onFinish: () => loading.value = false,
            onSuccess: () => {
                modal.success(`Status user berhasil diubah menjadi ${newStatus}`);
                haptics.success();
            },
            onError: () => {
                haptics.error();
                modal.error('Gagal mengubah status user');
            }
        });
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Manajemen User" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header - Consistent dengan design system -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <!-- Left Side: Title -->
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-emerald-400 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/25 shrink-0">
                                <UserCog class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Manajemen User
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Kelola akun guru, orang tua, dan staff
                                </p>
                            </div>
                        </div>

                        <!-- Right Side: Actions -->
                        <div class="flex items-center gap-2.5">
                            <Motion :whileTap="{ scale: 0.97 }">
                                <Link
                                    :href="create()"
                                    @click="haptics.light()"
                                    class="group flex items-center gap-2.5 px-5 py-2.5 min-h-[44px] bg-linear-to-r from-emerald-500 to-teal-500 text-white rounded-xl hover:from-emerald-600 hover:to-teal-600 transition-all duration-200 shadow-lg shadow-emerald-500/30
                                           focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                >
                                    <UserPlus class="w-5 h-5 transition-transform group-hover:scale-110 duration-200" />
                                    <span class="font-semibold hidden sm:inline">Tambah User</span>
                                    <span class="font-semibold sm:hidden">Tambah</span>
                                </Link>
                            </Motion>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Content -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <UserTable
                    :users="users"
                    :filters="filters"
                    :loading="loading"
                    @edit="handleEdit"
                    @delete="handleDelete"
                    @reset-password="handleResetPassword"
                    @toggle-status="handleToggleStatus"
                />
            </Motion>
        </div>
    </AppLayout>
</template>

