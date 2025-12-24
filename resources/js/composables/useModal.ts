import { ref } from 'vue';

/**
 * Modal state management composable untuk centralized modal control
 * Support multiple modals, dialog confirmations, dan alert notifications
 */

interface DialogOptions {
    type?: 'success' | 'warning' | 'danger' | 'info';
    title: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    showCancel?: boolean;
    icon?: 'check' | 'warning' | 'error' | 'info' | 'question';
    allowHtml?: boolean; // Enable sanitized HTML rendering
}

interface AlertOptions {
    type?: 'success' | 'error' | 'warning' | 'info';
    title?: string;
    message: string;
    duration?: number;
    position?: 'top' | 'top-right' | 'top-left' | 'bottom' | 'bottom-right' | 'bottom-left';
    dismissible?: boolean;
}

// Global state (Singleton)
const modals = ref<Record<string, boolean>>({});

const dialogState = ref<{
    show: boolean;
    options: DialogOptions;
    onConfirm?: () => void;
    onCancel?: () => void;
}>({
    show: false,
    options: {
        title: '',
        message: '',
    },
});

const alertState = ref<{
    show: boolean;
    options: AlertOptions;
}>({
    show: false,
    options: {
        message: '',
    },
});

export const useModal = () => {
    /**
     * Open modal by name
     */
    const open = (name: string) => {
        modals.value[name] = true;
    };

    /**
     * Close modal by name
     */
    const close = (name: string) => {
        modals.value[name] = false;
    };

    /**
     * Toggle modal by name
     */
    const toggle = (name: string) => {
        modals.value[name] = !modals.value[name];
    };

    /**
     * Check if modal is open
     */
    const isOpen = (name: string): boolean => {
        return modals.value[name] || false;
    };

    /**
     * Show dialog confirmation dengan promise-based API
     */
    const dialog = (options: DialogOptions): Promise<boolean> => {
        return new Promise((resolve) => {
            dialogState.value = {
                show: true,
                options,
                onConfirm: () => {
                    dialogState.value.show = false;
                    resolve(true);
                },
                onCancel: () => {
                    dialogState.value.show = false;
                    resolve(false);
                },
            };
        });
    };

    /**
     * Shorthand methods untuk dialog types
     */
    const confirm = (title: string, message: string, confirmText = 'Ya', cancelText = 'Tidak'): Promise<boolean> => {
        return dialog({
            type: 'info',
            icon: 'question',
            title,
            message,
            confirmText,
            cancelText,
            showCancel: true,
        });
    };

    const confirmDelete = (message = 'Apakah Anda yakin ingin menghapus data ini?'): Promise<boolean> => {
        return dialog({
            type: 'danger',
            icon: 'warning',
            title: 'Konfirmasi Hapus',
            message,
            confirmText: 'Hapus',
            cancelText: 'Batal',
            showCancel: true,
        });
    };

    /**
     * Show alert notification
     */
    const alert = (options: AlertOptions) => {
        alertState.value = {
            show: true,
            options,
        };
    };

    /**
     * Shorthand methods untuk alert types
     */
    const success = (message: string, title = 'Berhasil', duration = 3000) => {
        alert({
            type: 'success',
            title,
            message,
            duration,
        });
    };

    const error = (message: string, title = 'Kesalahan', duration = 5000) => {
        alert({
            type: 'error',
            title,
            message,
            duration,
        });
    };

    const warning = (message: string, title = 'Peringatan', duration = 4000) => {
        alert({
            type: 'warning',
            title,
            message,
            duration,
        });
    };

    const info = (message: string, title?: string, duration = 3000) => {
        alert({
            type: 'info',
            title,
            message,
            duration,
        });
    };

    /**
     * Close dialog
     */
    const closeDialog = () => {
        dialogState.value.show = false;
    };

    /**
     * Close alert
     */
    const closeAlert = () => {
        alertState.value.show = false;
    };

    return {
        // Modal management
        modals,
        open,
        close,
        toggle,
        isOpen,

        // Dialog
        dialogState,
        dialog,
        confirm,
        confirmDelete,
        closeDialog,

        // Alert
        alertState,
        alert,
        success,
        error,
        warning,
        info,
        closeAlert,
    };
};
