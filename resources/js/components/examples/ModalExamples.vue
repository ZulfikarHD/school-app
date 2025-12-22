<script setup lang="ts">
import { ref } from 'vue';
import { Motion } from 'motion-v';
import BaseModal from '@/components/ui/BaseModal.vue';
import DialogModal from '@/components/ui/DialogModal.vue';
import Alert from '@/components/ui/Alert.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Modal Examples Component untuk showcase dan testing
 * Demonstrasi penggunaan BaseModal, DialogModal, Alert, dan useModal composable
 */

const modal = useModal();
const haptics = useHaptics();

// State untuk manual modals
const showBaseModal = ref(false);
const showSmallModal = ref(false);
const showLargeModal = ref(false);
const showSuccessDialog = ref(false);
const showWarningDialog = ref(false);
const showDangerDialog = ref(false);
const showAlert = ref(false);
const alertType = ref<'success' | 'error' | 'warning' | 'info'>('success');
const dialogLoading = ref(false);

/**
 * Demo functions untuk showcase
 */
const openBaseModalDemo = () => {
    haptics.light();
    showBaseModal.value = true;
};

const openSmallModalDemo = () => {
    haptics.light();
    showSmallModal.value = true;
};

const openLargeModalDemo = () => {
    haptics.light();
    showLargeModal.value = true;
};

const showSuccessDialogDemo = () => {
    haptics.light();
    showSuccessDialog.value = true;
};

const showWarningDialogDemo = () => {
    haptics.light();
    showWarningDialog.value = true;
};

const showDangerDialogDemo = () => {
    haptics.light();
    showDangerDialog.value = true;
};

const showAlertDemo = (type: 'success' | 'error' | 'warning' | 'info') => {
    haptics.light();
    alertType.value = type;
    showAlert.value = true;
};

/**
 * Demo dengan useModal composable
 */
const testConfirmDialog = async () => {
    const result = await modal.confirm(
        'Konfirmasi Aksi',
        'Apakah Anda yakin ingin melanjutkan aksi ini?',
    );
    if (result) {
        modal.success('Aksi berhasil dikonfirmasi!');
    } else {
        modal.info('Aksi dibatalkan');
    }
};

const testDeleteDialog = async () => {
    const result = await modal.confirmDelete('Data yang dihapus tidak dapat dikembalikan.');
    if (result) {
        modal.success('Data berhasil dihapus');
    }
};

const testAlerts = () => {
    setTimeout(() => modal.success('Operasi berhasil dilakukan!'), 0);
    setTimeout(() => modal.warning('Perhatian: Kuota hampir habis'), 1000);
    setTimeout(() => modal.error('Gagal menyimpan data'), 2000);
    setTimeout(() => modal.info('Ada update terbaru tersedia', 'Update'), 3000);
};

const handleDialogConfirm = () => {
    dialogLoading.value = true;
    // Simulate API call
    setTimeout(() => {
        dialogLoading.value = false;
        showSuccessDialog.value = false;
        showWarningDialog.value = false;
        showDangerDialog.value = false;
        modal.success('Aksi berhasil diproses!');
    }, 1500);
};
</script>

<template>
    <div class="space-y-8">
        <!-- Base Modal Examples -->
        <div>
            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Base Modal</h3>
            <div class="flex flex-wrap gap-3">
                <Motion
                    :whileTap="{ scale: 0.97 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                >
                    <button
                        @click="openBaseModalDemo"
                        class="rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-2 font-semibold text-white shadow-lg transition-all hover:from-blue-700 hover:to-indigo-700 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-500/50"
                    >
                        Open Modal (Medium)
                    </button>
                </Motion>
                <Motion
                    :whileTap="{ scale: 0.97 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                >
                    <button
                        @click="openSmallModalDemo"
                        class="rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 px-4 py-2 font-semibold text-white shadow-lg transition-all hover:from-purple-700 hover:to-pink-700 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-purple-500/50"
                    >
                        Small Modal
                    </button>
                </Motion>
                <Motion
                    :whileTap="{ scale: 0.97 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                >
                    <button
                        @click="openLargeModalDemo"
                        class="rounded-lg bg-gradient-to-r from-green-600 to-teal-600 px-4 py-2 font-semibold text-white shadow-lg transition-all hover:from-green-700 hover:to-teal-700 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-green-500/50"
                    >
                        Large Modal
                    </button>
                </Motion>
            </div>
        </div>

        <!-- Dialog Modal Examples -->
        <div>
            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Dialog Modal</h3>
            <div class="flex flex-wrap gap-3">
                <Motion
                    :whileTap="{ scale: 0.97 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                >
                    <button
                        @click="showSuccessDialogDemo"
                        class="rounded-lg bg-gradient-to-r from-green-600 to-green-700 px-4 py-2 font-semibold text-white shadow-lg transition-all hover:from-green-700 hover:to-green-800 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-green-500/50"
                    >
                        Success Dialog
                    </button>
                </Motion>
                <Motion
                    :whileTap="{ scale: 0.97 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                >
                    <button
                        @click="showWarningDialogDemo"
                        class="rounded-lg bg-gradient-to-r from-yellow-600 to-yellow-700 px-4 py-2 font-semibold text-white shadow-lg transition-all hover:from-yellow-700 hover:to-yellow-800 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-yellow-500/50"
                    >
                        Warning Dialog
                    </button>
                </Motion>
                <Motion
                    :whileTap="{ scale: 0.97 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                >
                    <button
                        @click="showDangerDialogDemo"
                        class="rounded-lg bg-gradient-to-r from-red-600 to-red-700 px-4 py-2 font-semibold text-white shadow-lg transition-all hover:from-red-700 hover:to-red-800 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-red-500/50"
                    >
                        Danger Dialog
                    </button>
                </Motion>
            </div>
        </div>

        <!-- Alert Examples -->
        <div>
            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Alert / Toast</h3>
            <div class="flex flex-wrap gap-3">
                <Motion
                    :whileTap="{ scale: 0.97 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                >
                    <button
                        @click="showAlertDemo('success')"
                        class="rounded-lg bg-gradient-to-r from-green-600 to-green-700 px-4 py-2 font-semibold text-white shadow-lg transition-all hover:from-green-700 hover:to-green-800 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-green-500/50"
                    >
                        Success Alert
                    </button>
                </Motion>
                <Motion
                    :whileTap="{ scale: 0.97 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                >
                    <button
                        @click="showAlertDemo('error')"
                        class="rounded-lg bg-gradient-to-r from-red-600 to-red-700 px-4 py-2 font-semibold text-white shadow-lg transition-all hover:from-red-700 hover:to-red-800 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-red-500/50"
                    >
                        Error Alert
                    </button>
                </Motion>
                <Motion
                    :whileTap="{ scale: 0.97 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                >
                    <button
                        @click="showAlertDemo('warning')"
                        class="rounded-lg bg-gradient-to-r from-yellow-600 to-yellow-700 px-4 py-2 font-semibold text-white shadow-lg transition-all hover:from-yellow-700 hover:to-yellow-800 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-yellow-500/50"
                    >
                        Warning Alert
                    </button>
                </Motion>
                <Motion
                    :whileTap="{ scale: 0.97 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                >
                    <button
                        @click="showAlertDemo('info')"
                        class="rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2 font-semibold text-white shadow-lg transition-all hover:from-blue-700 hover:to-blue-800 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-500/50"
                    >
                        Info Alert
                    </button>
                </Motion>
            </div>
        </div>

        <!-- useModal Composable Examples -->
        <div>
            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">useModal Composable</h3>
            <div class="flex flex-wrap gap-3">
                <Motion
                    :whileTap="{ scale: 0.97 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                >
                    <button
                        @click="testConfirmDialog"
                        class="rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-2 font-semibold text-white shadow-lg transition-all hover:from-blue-700 hover:to-indigo-700 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-500/50"
                    >
                        Test Confirm
                    </button>
                </Motion>
                <Motion
                    :whileTap="{ scale: 0.97 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                >
                    <button
                        @click="testDeleteDialog"
                        class="rounded-lg bg-gradient-to-r from-red-600 to-red-700 px-4 py-2 font-semibold text-white shadow-lg transition-all hover:from-red-700 hover:to-red-800 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-red-500/50"
                    >
                        Test Delete Confirm
                    </button>
                </Motion>
                <Motion
                    :whileTap="{ scale: 0.97 }"
                    :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                >
                    <button
                        @click="testAlerts"
                        class="rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 px-4 py-2 font-semibold text-white shadow-lg transition-all hover:from-purple-700 hover:to-pink-700 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-purple-500/50"
                    >
                        Test Multiple Alerts
                    </button>
                </Motion>
            </div>
        </div>

        <!-- Base Modal Components -->
        <BaseModal
            :show="showBaseModal"
            title="Modal Example"
            size="md"
            @close="showBaseModal = false"
        >
            <p class="text-gray-600 dark:text-gray-400">
                Ini adalah contoh Base Modal dengan iOS-like design, spring animations, dan glass effect.
                Modal ini support berbagai ukuran, custom header, footer, dan backdrop blur.
            </p>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <Motion
                        :whileTap="{ scale: 0.97 }"
                        :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                    >
                        <button
                            @click="showBaseModal = false"
                            class="rounded-lg border-2 border-gray-300 px-4 py-2 font-semibold text-gray-700 transition-all hover:border-gray-400 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-300/50 dark:border-gray-600 dark:text-gray-300 dark:hover:border-gray-500 dark:hover:bg-gray-700"
                        >
                            Tutup
                        </button>
                    </Motion>
                </div>
            </template>
        </BaseModal>

        <BaseModal
            :show="showSmallModal"
            title="Small Modal"
            size="sm"
            @close="showSmallModal = false"
        >
            <p class="text-gray-600 dark:text-gray-400">
                Modal ukuran kecil untuk konten singkat.
            </p>
        </BaseModal>

        <BaseModal
            :show="showLargeModal"
            title="Large Modal"
            size="lg"
            @close="showLargeModal = false"
        >
            <div class="space-y-4 text-gray-600 dark:text-gray-400">
                <p>Modal ukuran besar untuk konten yang lebih kompleks.</p>
                <p>Support scroll jika konten terlalu panjang.</p>
                <div class="h-96 rounded-lg bg-gray-100 dark:bg-gray-700" />
            </div>
        </BaseModal>

        <!-- Dialog Modals -->
        <DialogModal
            :show="showSuccessDialog"
            type="success"
            title="Berhasil!"
            message="Operasi telah berhasil dilakukan. Data Anda sudah tersimpan dengan aman."
            confirm-text="OK"
            :show-cancel="false"
            :loading="dialogLoading"
            @confirm="handleDialogConfirm"
            @close="showSuccessDialog = false"
        />

        <DialogModal
            :show="showWarningDialog"
            type="warning"
            title="Peringatan"
            message="Tindakan ini akan mengubah data. Apakah Anda yakin ingin melanjutkan?"
            confirm-text="Lanjutkan"
            cancel-text="Batal"
            :loading="dialogLoading"
            @confirm="handleDialogConfirm"
            @cancel="showWarningDialog = false"
            @close="showWarningDialog = false"
        />

        <DialogModal
            :show="showDangerDialog"
            type="danger"
            title="Konfirmasi Hapus"
            message="Data yang dihapus tidak dapat dikembalikan. Apakah Anda yakin ingin menghapus?"
            confirm-text="Hapus"
            cancel-text="Batal"
            :loading="dialogLoading"
            @confirm="handleDialogConfirm"
            @cancel="showDangerDialog = false"
            @close="showDangerDialog = false"
        />

        <!-- Alerts -->
        <Alert
            :show="showAlert"
            :type="alertType"
            :title="alertType === 'success' ? 'Berhasil' : alertType === 'error' ? 'Kesalahan' : alertType === 'warning' ? 'Peringatan' : 'Informasi'"
            :message="
                alertType === 'success'
                    ? 'Operasi berhasil dilakukan!'
                    : alertType === 'error'
                      ? 'Terjadi kesalahan saat memproses data'
                      : alertType === 'warning'
                        ? 'Perhatian: Data akan diubah'
                        : 'Ada informasi penting untuk Anda'
            "
            :duration="3000"
            position="top-right"
            @close="showAlert = false"
        />

        <!-- useModal Composable Modals -->
        <DialogModal
            :show="modal.dialogState.value.show"
            :type="modal.dialogState.value.options.type"
            :title="modal.dialogState.value.options.title"
            :message="modal.dialogState.value.options.message"
            :confirm-text="modal.dialogState.value.options.confirmText"
            :cancel-text="modal.dialogState.value.options.cancelText"
            :show-cancel="modal.dialogState.value.options.showCancel"
            :icon="modal.dialogState.value.options.icon"
            @confirm="modal.dialogState.value.onConfirm?.()"
            @cancel="modal.dialogState.value.onCancel?.()"
            @close="modal.closeDialog()"
        />

        <Alert
            :show="modal.alertState.value.show"
            :type="modal.alertState.value.options.type"
            :title="modal.alertState.value.options.title"
            :message="modal.alertState.value.options.message"
            :duration="modal.alertState.value.options.duration"
            :position="modal.alertState.value.options.position"
            :dismissible="modal.alertState.value.options.dismissible"
            @close="modal.closeAlert()"
        />
    </div>
</template>

