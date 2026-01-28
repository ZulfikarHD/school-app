<script setup lang="ts">
/**
 * AppLayout Component - Main application layout dengan Dual-Navigation Strategy
 *
 * Layout utama aplikasi yang menggunakan:
 * - Desktop: Persistent Sidebar (DesktopSidebar) + Top Glass Header
 * - Mobile: Bottom Tab Bar (MobileBottomNav) + Top Glass Header + Sheet Modals
 *
 * Component ini bertanggung jawab untuk:
 * - Koordinasi antar sub-components (sidebar, nav, sheets)
 * - Global modals dan alerts
 * - Session timeout management
 * - Header dengan breadcrumb
 *
 * Mengikuti iOS-like Design Standard (Performance First)
 */
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import { useSessionTimeout } from '@/composables/useSessionTimeout';
import { useNavigation } from '@/composables/useNavigation';
import { useAutoBreadcrumb } from '@/composables/useAutoBreadcrumb';

// Layout Sub-Components
import DesktopSidebar from '@/components/layouts/DesktopSidebar.vue';
import MobileBottomNav from '@/components/layouts/MobileBottomNav.vue';
import MobileProfileSheet from '@/components/layouts/MobileProfileSheet.vue';
import MobileNavigationSheet from '@/components/layouts/MobileNavigationSheet.vue';

// UI Components
import DialogModal from '@/components/ui/DialogModal.vue';
import Alert from '@/components/ui/Alert.vue';
import SessionTimeoutModal from '@/components/ui/SessionTimeoutModal.vue';
import Breadcrumb from '@/components/ui/Breadcrumb.vue';

// Icons
import { Search, Bell } from 'lucide-vue-next';

// Navigation composable
const { user } = useNavigation();

const page = usePage();
const haptics = useHaptics();
const modal = useModal();

// Mobile sheet states
const showProfileMenu = ref(false);
const showMobileNav = ref(false);

/**
 * Check apakah user prefer reduced motion untuk accessibility
 */
const prefersReducedMotion = ref(false);
if (typeof window !== 'undefined') {
    prefersReducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

// Session Timeout Logic - Admin: 15 mins, Others: 30 mins
const timeoutDuration = computed(() =>
    (user.value?.role === 'SUPERADMIN' || user.value?.role === 'ADMIN') ? 15 : 30
);
const { showWarning, remainingSeconds, extendSession, logout: sessionLogout } = useSessionTimeout({
    timeoutMinutes: timeoutDuration.value
});

// Destructure modal states for template usage
const { dialogState, alertState } = modal;

// Auto-generate breadcrumb items berdasarkan current URL
const { breadcrumbItems } = useAutoBreadcrumb();

/**
 * Toggle Profile Menu (Mobile Sheet)
 */
const toggleProfileMenu = () => {
    haptics.light();
    showProfileMenu.value = !showProfileMenu.value;
};

/**
 * Toggle Mobile Navigation Sheet
 */
const toggleMobileNav = () => {
    haptics.light();
    showMobileNav.value = !showMobileNav.value;
};

/**
 * Placeholder handler untuk fitur yang belum tersedia
 */
const showComingSoon = (featureName: string) => {
    haptics.light();
    modal.info('Segera Hadir', `Fitur ${featureName} akan segera tersedia dalam pembaruan berikutnya.`);
};

// Format Date for Header (Desktop - Full format)
const currentDate = new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
}).format(new Date());

// Format Date for Mobile Header (Compact format)
const currentDateShort = new Intl.DateTimeFormat('id-ID', {
    weekday: 'short',
    day: 'numeric',
    month: 'short'
}).format(new Date());

/**
 * Animation config yang mempertimbangkan prefers-reduced-motion
 */
const getSpringConfig = (stiffness = 300, damping = 25) => {
    if (prefersReducedMotion.value) {
        return { type: 'tween', duration: 0 };
    }
    return { type: 'spring', stiffness, damping };
};

// Export untuk penggunaan di child components jika diperlukan
defineExpose({ getSpringConfig });
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-zinc-950 flex">
        <!-- GLOBAL MODALS & ALERTS -->
        <DialogModal
            :show="dialogState.show"
            v-bind="dialogState.options"
            @confirm="dialogState.onConfirm"
            @cancel="dialogState.onCancel"
            @close="modal.closeDialog"
        />
        <Alert
            :show="alertState.show"
            v-bind="alertState.options"
            @close="modal.closeAlert"
        />

        <!-- SESSION TIMEOUT WARNING -->
        <SessionTimeoutModal
            :show="showWarning"
            :remaining-seconds="remainingSeconds"
            @extend="extendSession"
            @logout="sessionLogout"
        />

        <!-- MOBILE PROFILE ACTION SHEET -->
        <MobileProfileSheet
            :show="showProfileMenu"
            @close="showProfileMenu = false"
        />

        <!-- MOBILE FULL NAVIGATION SHEET -->
        <MobileNavigationSheet
            :show="showMobileNav"
            @close="showMobileNav = false"
        />

        <!-- DESKTOP SIDEBAR -->
        <DesktopSidebar />

        <!-- MAIN CONTENT WRAPPER -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
            <!-- UNIFIED TOP HEADER (Desktop & Mobile) -->
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-100 bg-white/95 dark:bg-zinc-900/95 px-4 lg:px-8 dark:border-zinc-800 transition-all">
                <!-- Left Section: Mobile Logo OR Desktop Greeting/Title -->
                <div class="flex items-center gap-4">
                    <!-- Mobile Logo -->
                    <div class="lg:hidden flex items-center gap-2.5">
                        <div class="w-9 h-9 bg-linear-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-md shadow-emerald-500/20">
                            <span class="text-lg">🏫</span>
                        </div>
                        <span class="font-bold text-lg text-slate-900 dark:text-white">SchoolApp</span>
                    </div>

                    <!-- Desktop Context Title -->
                    <div class="hidden lg:block">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <slot name="header">
                                <!-- Default Greeting if no header slot provided -->
                                <span>Selamat Datang, {{ user?.name?.split(' ')[0] }}</span>
                                <span class="text-xl">👋</span>
                            </slot>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 hidden lg:block" v-if="!$slots.header">
                            {{ currentDate }}
                        </p>
                    </div>
                </div>

                <!-- Right Section: Utilities -->
                <div class="flex items-center gap-1.5 lg:gap-3">
                    <!-- Mobile Date Display -->
                    <span class="lg:hidden text-[11px] font-medium text-slate-500 dark:text-slate-400 hidden xs:inline">
                        {{ currentDateShort }}
                    </span>

                    <!-- Search Button -->
                    <Motion :whileTap="{ scale: 0.9 }">
                        <button
                            @click="showComingSoon('Pencarian')"
                            class="p-2.5 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-zinc-800 rounded-xl transition-colors active:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                            aria-label="Cari"
                        >
                            <Search class="w-5 h-5" />
                        </button>
                    </Motion>

                    <!-- Notification Bell -->
                    <Motion :whileTap="{ scale: 0.9 }">
                        <button
                            @click="showComingSoon('Notifikasi')"
                            class="relative p-2.5 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-zinc-800 rounded-xl transition-colors active:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                            aria-label="Notifikasi"
                        >
                            <Bell class="w-5 h-5" />
                        </button>
                    </Motion>

                    <!-- Mobile Profile Trigger -->
                    <div class="lg:hidden pl-2 border-l border-slate-100 dark:border-zinc-800">
                        <Motion :whileTap="{ scale: 0.9 }">
                            <button
                                @click="toggleProfileMenu"
                                class="w-9 h-9 rounded-xl bg-linear-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm shadow-sm shadow-emerald-500/20 ring-2 ring-transparent focus:ring-emerald-200 dark:focus:ring-emerald-900 active:scale-95 transition-transform focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                                aria-label="Menu profil"
                            >
                                {{ user?.name?.charAt(0) || 'U' }}
                            </button>
                        </Motion>
                    </div>
                </div>
            </header>

            <!-- BREADCRUMB (Auto-generated from current route) -->
            <div v-if="breadcrumbItems.length > 0" class="border-b border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 px-4 lg:px-8 py-2">
                <div class="max-w-7xl mx-auto">
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>

            <!-- PAGE CONTENT -->
            <main class="flex-1 w-full pb-28 lg:pb-8 relative">
                <div class="p-4 lg:p-8 max-w-7xl mx-auto space-y-6">
                    <slot />
                </div>
            </main>
        </div>

        <!-- MOBILE BOTTOM TAB BAR -->
        <MobileBottomNav
            :show-mobile-nav="showMobileNav"
            @toggle-mobile-nav="toggleMobileNav"
        />
    </div>
</template>
