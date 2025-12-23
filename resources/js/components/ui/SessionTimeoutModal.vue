<script setup lang="ts">
import { computed } from 'vue';
import BaseModal from './BaseModal.vue';
import { useHaptics } from '@/composables/useHaptics';
import { Clock, LogOut, RefreshCw } from 'lucide-vue-next';

interface Props {
    show: boolean;
    remainingSeconds: number;
}

const props = defineProps<Props>();
const emit = defineEmits(['extend', 'logout']);

const haptics = useHaptics();

const formatTime = computed(() => {
    const minutes = Math.floor(props.remainingSeconds / 60);
    const seconds = props.remainingSeconds % 60;
    return `${minutes} menit ${seconds} detik`;
});

const handleExtend = () => {
    haptics.medium();
    emit('extend');
};

const handleLogout = () => {
    haptics.heavy();
    emit('logout');
};
</script>

<template>
    <BaseModal
        :show="show"
        size="sm"
        :close-on-backdrop="false"
        :show-close-button="false"
    >
        <div class="text-center p-2">
            <!-- Icon dengan pulse animation -->
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/30 animate-pulse">
                <Clock class="h-8 w-8 text-yellow-600 dark:text-yellow-400" />
            </div>

            <!-- Title -->
            <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">
                Session Akan Berakhir
            </h3>

            <!-- Message dengan countdown -->
            <p class="mb-2 text-gray-600 dark:text-gray-400">
                Session Anda akan berakhir dalam:
            </p>
            <p class="mb-6 text-3xl font-bold text-yellow-600 dark:text-yellow-400 tabular-nums">
                {{ formatTime }}
            </p>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                Apakah Anda masih ingin melanjutkan bekerja?
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-3 sm:flex-row">
                <!-- Extend Button -->
                <button
                    type="button"
                    @click="handleExtend"
                    class="flex-1 flex items-center justify-center gap-2 rounded-xl px-6 py-3 font-semibold shadow-sm transition-all duration-200 hover:shadow-md focus:outline-none focus:ring-4 bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500/50 active:scale-95"
                >
                    <RefreshCw class="w-4 h-4" />
                    Perpanjang Session
                </button>

                <!-- Logout Button -->
                <button
                    type="button"
                    @click="handleLogout"
                    class="flex-1 flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-6 py-3 font-semibold text-gray-700 transition-all duration-200 hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-300 dark:hover:border-zinc-600 dark:hover:bg-zinc-700 dark:focus:ring-zinc-700/50 active:scale-95"
                >
                    <LogOut class="w-4 h-4" />
                    Keluar Sekarang
                </button>
            </div>
        </div>
    </BaseModal>
</template>
