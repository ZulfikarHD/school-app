<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import UserTable from '@/components/features/users/UserTable.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import { UserPlus, Users } from 'lucide-vue-next';
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
    <Head title="Manajemen User" />

    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <Users class="w-8 h-8 text-blue-600 dark:text-blue-500" />
                        Manajemen User
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Kelola data user, role, dan hak akses sistem.
                    </p>
                </div>

                <Link
                    :href="create()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md active:scale-95"
                >
                    <UserPlus class="w-4 h-4" />
                    Tambah User Baru
                </Link>
            </Motion>

            <!-- Content -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.1 }"
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

