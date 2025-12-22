<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Main application layout dengan navigation dan logout functionality
 * Support responsive design untuk mobile dan desktop,
 * iOS-like animations, haptic feedback, dan swipe gestures
 */

const page = usePage();
const user = computed(() => page.props.auth?.user);
const showMobileMenu = ref(false);
const mobileMenuRef = ref<HTMLElement | null>(null);
const overlayRef = ref<HTMLElement | null>(null);

const haptics = useHaptics();

/**
 * Toggle mobile menu dengan haptic feedback
 */
const toggleMobileMenu = () => {
    haptics.light();
    showMobileMenu.value = !showMobileMenu.value;
};

/**
 * Close mobile menu dengan haptic feedback
 */
const closeMobileMenu = () => {
    haptics.light();
    showMobileMenu.value = false;
};

/**
 * Handle logout dengan confirmation, haptic feedback,
 * dan redirect ke login page
 */
const logout = () => {
    haptics.medium();
    if (confirm('Apakah Anda yakin ingin keluar?')) {
        haptics.heavy();
        router.post(route('logout'));
    }
};

/**
 * Handle navigation click dengan haptic feedback
 */
const handleNavClick = () => {
    haptics.light();
};

/**
 * Dynamic menu items berdasarkan user role untuk role-based access control
 */
const menuItems = computed(() => {
    const role = user.value?.role;

    const commonItems = [
        { name: 'Dashboard', route: 'dashboard', icon: 'home' },
    ];

    if (role === 'SUPERADMIN' || role === 'ADMIN') {
        return [
            ...commonItems,
            { name: 'Manajemen User', route: 'admin.users.index', icon: 'users' },
            { name: 'Log Aktivitas', route: 'admin.audit-logs.index', icon: 'activity' },
        ];
    }

    if (role === 'PRINCIPAL') {
        return [
            ...commonItems,
            { name: 'Laporan', route: 'principal.reports', icon: 'chart' },
            { name: 'Log Aktivitas', route: 'admin.audit-logs.index', icon: 'activity' },
        ];
    }

    if (role === 'TEACHER') {
        return [
            ...commonItems,
            { name: 'Kelas Saya', route: 'teacher.classes', icon: 'book' },
            { name: 'Nilai', route: 'teacher.grades', icon: 'clipboard' },
        ];
    }

    if (role === 'PARENT') {
        return [
            ...commonItems,
            { name: 'Anak Saya', route: 'parent.children', icon: 'users' },
            { name: 'Pembayaran', route: 'parent.payments', icon: 'dollar' },
        ];
    }

    return commonItems;
});

/**
 * Transition handlers untuk mobile menu animation
 */
const onEnter = (el: Element, done: () => void) => {
    setTimeout(done, 300);
};

const onLeave = (el: Element, done: () => void) => {
    setTimeout(done, 300);
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Top Navigation Bar dengan glass effect -->
        <nav class="sticky top-0 z-50 border-b border-gray-200 bg-white/80 backdrop-blur-xl dark:border-gray-700 dark:bg-gray-800/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo & Brand -->
                    <div class="flex items-center">
                        <button
                            @click="toggleMobileMenu"
                            class="mr-2 rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 lg:hidden dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Sistem Sekolah</h1>
                    </div>

                    <!-- Desktop Navigation dengan spring animation -->
                    <div class="hidden lg:flex lg:items-center lg:space-x-4">
                        <Motion
                            v-for="item in menuItems"
                            :key="item.route"
                            :whileTap="{ scale: 0.97 }"
                            :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                        >
                            <a
                                :href="route(item.route)"
                                @click="handleNavClick"
                                class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
                            >
                                {{ item.name }}
                            </a>
                        </Motion>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-4">
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user?.name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ user?.role }}</p>
                        </div>
                        <Motion
                            :whileTap="{ scale: 0.97 }"
                            :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                        >
                            <button
                                @click="logout"
                                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition-all duration-200 hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-500/50"
                            >
                                Keluar
                            </button>
                        </Motion>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Mobile Menu dengan slide animation dan swipe-to-close -->
        <Transition
            :css="false"
            @enter="onEnter"
            @leave="onLeave"
        >
            <div
                v-if="showMobileMenu"
                class="fixed inset-0 z-40 lg:hidden"
            >
                <!-- Overlay dengan fade animation -->
                <Motion
                    ref="overlayRef"
                    :initial="{ opacity: 0 }"
                    :animate="{ opacity: 1 }"
                    :exit="{ opacity: 0 }"
                    :transition="{ duration: 0.2 }"
                >
                    <div
                        @click="closeMobileMenu"
                        class="fixed inset-0 bg-black/50 backdrop-blur-sm"
                    />
                </Motion>

                <!-- Menu Panel dengan slide-in animation dan swipe gesture -->
                <Motion
                    ref="mobileMenuRef"
                    :initial="{ x: -264 }"
                    :animate="{ x: 0 }"
                    :exit="{ x: -264 }"
                    :transition="{ type: 'spring', stiffness: 400, damping: 35 }"
                    class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl dark:bg-gray-800"
                >
                <div class="flex h-16 items-center justify-between border-b border-gray-200 px-4 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Menu</h2>
                    <button
                        @click="toggleMobileMenu"
                        class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="space-y-1 px-3 py-4">
                    <Motion
                        v-for="item in menuItems"
                        :key="item.route"
                        :whileTap="{ scale: 0.97 }"
                        :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                    >
                        <a
                            :href="route(item.route)"
                            class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
                            @click="closeMobileMenu"
                        >
                            {{ item.name }}
                        </a>
                    </Motion>
                </nav>
                </Motion>
            </div>
        </Transition>

        <!-- Main Content -->
        <main>
            <slot />
        </main>
    </div>
</template>

