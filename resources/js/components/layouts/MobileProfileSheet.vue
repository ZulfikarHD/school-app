<script setup lang="ts">
/**
 * MobileProfileSheet Component - Bottom sheet untuk profile menu di mobile
 *
 * Component ini menampilkan profile action sheet dengan:
 * - User profile header card
 * - Menu items (Profile, Settings, Logout)
 * - iOS-like drag handle indicator
 * Mengikuti iOS-like Design Standard
 */
import { Link, router } from '@inertiajs/vue3';
import BaseModal from '@/components/ui/BaseModal.vue';
import { useNavigation, getLogoutUrl, UserIcon, SettingsIcon } from '@/composables/useNavigation';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import { LogOut, ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const { user, profileUrl } = useNavigation();
const haptics = useHaptics();
const modal = useModal();

/**
 * Close the profile sheet
 */
const close = () => {
    emit('close');
};

/**
 * Placeholder handler untuk fitur yang belum tersedia
 */
const showComingSoon = (featureName: string) => {
    haptics.light();
    modal.info('Segera Hadir', `Fitur ${featureName} akan segera tersedia dalam pembaruan berikutnya.`);
};

/**
 * Handle logout dengan konfirmasi dialog
 */
const logout = async () => {
    close();
    haptics.medium();

    const confirmed = await modal.confirm(
        'Konfirmasi Keluar',
        'Apakah Anda yakin ingin keluar dari sesi ini?',
        'Ya, Keluar',
        'Batal'
    );

    if (confirmed) {
        haptics.heavy();
        router.post(getLogoutUrl());
    }
};
</script>

<template>
    <BaseModal
        :show="show"
        size="full"
        :show-close-button="false"
        @close="close"
        class="p-0! m-0!"
    >
        <div class="flex flex-col gap-3">
            <!-- Drag Handle Indicator -->
            <div class="w-10 h-1 bg-slate-200 dark:bg-zinc-700 rounded-full mx-auto -mt-2 mb-1" aria-hidden="true"></div>

            <!-- Profile Header Card -->
            <div class="flex items-center gap-4 p-4 bg-slate-50/80 rounded-2xl dark:bg-zinc-800/50 border border-slate-100 dark:border-zinc-700/50">
                <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-emerald-500/20">
                    {{ user?.name?.charAt(0) || 'U' }}
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-slate-900 dark:text-white text-lg truncate">{{ user?.name }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 truncate">{{ user?.email }}</p>
                    <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-2.5 py-1 rounded-lg mt-1.5 inline-block uppercase tracking-wide">
                        {{ user?.role }}
                    </span>
                </div>
            </div>

            <!-- Menu Items -->
            <Link
                :href="profileUrl"
                @click="close"
                class="w-full text-left px-4 py-3.5 rounded-xl active:bg-slate-50 dark:active:bg-zinc-800 flex items-center justify-between group transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
            >
                <span class="flex items-center gap-3.5 text-slate-700 dark:text-slate-300">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                        <UserIcon class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                    </div>
                    <span class="font-medium">Profil Saya</span>
                </span>
                <ChevronRight class="w-5 h-5 text-slate-400" />
            </Link>

            <button
                @click="showComingSoon('Pengaturan')"
                class="w-full text-left px-4 py-3.5 rounded-xl active:bg-slate-50 dark:active:bg-zinc-800 flex items-center justify-between group transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
            >
                <span class="flex items-center gap-3.5 text-slate-700 dark:text-slate-300">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                        <SettingsIcon class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                    </div>
                    <span class="font-medium">Pengaturan</span>
                </span>
                <span class="text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-0.5 rounded-full">Segera</span>
            </button>

            <div class="h-px bg-slate-100 dark:bg-zinc-800 my-1 mx-4"></div>

            <button
                @click="logout"
                class="w-full text-left px-4 py-3.5 rounded-xl active:bg-red-50 dark:active:bg-red-900/20 flex items-center gap-3.5 text-red-600 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"
            >
                <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                    <LogOut class="w-5 h-5" />
                </div>
                <span class="font-medium">Keluar Aplikasi</span>
            </button>

            <!-- Close Button -->
            <button
                @click="close"
                class="mt-2 w-full py-3.5 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400 font-semibold text-sm active:scale-[0.98] transition-transform focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
            >
                Tutup
            </button>
        </div>
    </BaseModal>
</template>
