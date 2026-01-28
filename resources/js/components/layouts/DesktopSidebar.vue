<script setup lang="ts">
/**
 * DesktopSidebar Component - Sidebar navigasi untuk tampilan desktop
 *
 * Component ini menampilkan sidebar dengan:
 * - Brand logo dan nama aplikasi
 * - Navigation menu dengan dropdown support
 * - User profile section dengan dropdown menu
 * Mengikuti iOS-like Design Standard
 */
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Motion, AnimatePresence } from 'motion-v';
import { useNavigation, getLogoutUrl, UserIcon, SettingsIcon } from '@/composables/useNavigation';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import { ChevronDown, LogOut } from 'lucide-vue-next';

const {
    user,
    menuItems,
    openDropdowns,
    getRouteUrl,
    profileUrl,
    isActive,
    isDropdownActive,
    toggleDropdown,
} = useNavigation();

const haptics = useHaptics();
const modal = useModal();

/**
 * State untuk desktop profile dropdown menu
 */
const showDesktopProfileMenu = ref(false);

/**
 * Toggle desktop profile menu dropdown
 */
const toggleDesktopProfileMenu = () => {
    haptics.light();
    showDesktopProfileMenu.value = !showDesktopProfileMenu.value;
};

/**
 * Handle click outside untuk menutup desktop profile menu
 */
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (showDesktopProfileMenu.value && !target.closest('.desktop-profile-section')) {
        showDesktopProfileMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

/**
 * Navigation click feedback
 */
const handleNavClick = () => {
    haptics.light();
};

/**
 * Handle dropdown toggle dengan haptic feedback
 */
const handleDropdownToggle = (groupName: string) => {
    haptics.light();
    toggleDropdown(groupName);
};

/**
 * Placeholder handler untuk fitur yang belum tersedia
 */
const showComingSoon = (featureName: string) => {
    haptics.light();
    showDesktopProfileMenu.value = false;
    modal.info('Segera Hadir', `Fitur ${featureName} akan segera tersedia dalam pembaruan berikutnya.`);
};

/**
 * Handle logout dengan konfirmasi dialog
 */
const logout = async () => {
    showDesktopProfileMenu.value = false;
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
    <aside class="hidden lg:flex w-60 flex-col bg-white dark:bg-zinc-900 border-r border-slate-200 dark:border-zinc-800 h-screen sticky top-0 z-30">
        <!-- Brand Logo - Compact -->
        <div class="p-4 pb-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-linear-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-md shadow-emerald-500/20">
                    <span class="text-lg">🏫</span>
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-900 dark:text-white leading-tight">SchoolApp</h1>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Management</p>
                </div>
            </div>
        </div>

        <!-- Navigation with Dropdown Support -->
        <div class="flex-1 overflow-y-auto py-3 px-3 sidebar-scroll">
            <div class="space-y-0.5">
                <p class="px-2.5 text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Menu</p>

                <template v-for="(item, index) in menuItems" :key="item.name">
                    <!-- Regular Menu Item (no children) with Spring Animation -->
                    <Motion
                        v-if="!item.children"
                        :initial="{ opacity: 0, x: -16 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: index * 0.03 }"
                        :whileHover="{ scale: 1.02, x: 4 }"
                        :whileTap="{ scale: 0.97 }"
                    >
                        <Link
                            :href="getRouteUrl(item.route!)"
                            @click="handleNavClick"
                            class="group flex items-center gap-2.5 px-2.5 py-2.5 rounded-xl transition-all duration-200 border"
                            :class="isActive(item.route!)
                                ? 'bg-emerald-50/80 border-emerald-200/60 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-800/50 dark:text-emerald-400 font-semibold'
                                : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-zinc-800/70 dark:hover:text-white'"
                        >
                            <Motion
                                :whileHover="{ rotate: [0, -10, 10, -5, 5, 0] }"
                                :transition="{ duration: 0.5 }"
                                class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors duration-200 shrink-0"
                                :class="isActive(item.route!)
                                    ? 'bg-emerald-500 shadow-sm shadow-emerald-500/25'
                                    : 'bg-slate-100 dark:bg-zinc-800 group-hover:bg-slate-200 dark:group-hover:bg-zinc-700'"
                            >
                                <component
                                    :is="item.icon"
                                    class="w-4 h-4 transition-colors duration-200"
                                    :class="isActive(item.route!) ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300'"
                                />
                            </Motion>
                            <span class="flex-1 text-[13px] truncate">{{ item.name }}</span>

                            <!-- Pending Badge with Pulse -->
                            <Motion
                                v-if="item.badge && item.badge > 0"
                                :initial="{ scale: 0 }"
                                :animate="{ scale: 1 }"
                                :transition="{ type: 'spring', stiffness: 400, damping: 15 }"
                                class="flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm"
                            >
                                {{ item.badge > 99 ? '99+' : item.badge }}
                            </Motion>

                            <!-- Active Indicator Dot with Animation -->
                            <Motion
                                v-else-if="isActive(item.route!)"
                                :initial="{ scale: 0 }"
                                :animate="{ scale: 1 }"
                                :transition="{ type: 'spring', stiffness: 500, damping: 20 }"
                                class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400 shadow-[0_0_6px_rgba(16,185,129,0.5)]"
                            />
                        </Link>
                    </Motion>

                    <!-- Dropdown Menu Item (has children) -->
                    <div v-else class="space-y-0.5">
                        <!-- Dropdown Toggle Button with Spring Animation -->
                        <Motion
                            :whileHover="{ scale: 1.01, x: 2 }"
                            :whileTap="{ scale: 0.97 }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                        >
                            <button
                                @click="handleDropdownToggle(item.name)"
                                class="w-full group flex items-center gap-2.5 px-2.5 py-2.5 rounded-xl transition-all duration-200 border"
                                :class="isDropdownActive(item.children!) || openDropdowns[item.name]
                                    ? 'bg-emerald-50/50 border-emerald-100 text-emerald-700 dark:bg-emerald-900/10 dark:border-emerald-900/30 dark:text-emerald-400'
                                    : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-zinc-800/70 dark:hover:text-white'"
                            >
                                <Motion
                                    :animate="{ rotate: openDropdowns[item.name] || isDropdownActive(item.children!) ? 360 : 0 }"
                                    :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors duration-200 shrink-0"
                                    :class="isDropdownActive(item.children!) || openDropdowns[item.name]
                                        ? 'bg-emerald-500/90 shadow-sm shadow-emerald-500/20'
                                        : 'bg-slate-100 dark:bg-zinc-800 group-hover:bg-slate-200 dark:group-hover:bg-zinc-700'"
                                >
                                    <component
                                        :is="item.icon"
                                        class="w-4 h-4 transition-colors duration-200"
                                        :class="isDropdownActive(item.children!) || openDropdowns[item.name] ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300'"
                                    />
                                </Motion>
                                <span class="flex-1 text-[13px] text-left truncate">{{ item.name }}</span>

                                <!-- Dropdown Badge (sum of children badges) -->
                                <Motion
                                    v-if="item.badge && item.badge > 0"
                                    :initial="{ scale: 0 }"
                                    :animate="{ scale: 1 }"
                                    :transition="{ type: 'spring', stiffness: 400, damping: 20 }"
                                    class="flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm mr-1"
                                >
                                    {{ item.badge > 99 ? '99+' : item.badge }}
                                </Motion>

                                <!-- Chevron Indicator with Rotation Animation -->
                                <Motion
                                    :animate="{ rotate: openDropdowns[item.name] || isDropdownActive(item.children!) ? 180 : 0 }"
                                    :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                                >
                                    <ChevronDown class="w-4 h-4 text-slate-400" />
                                </Motion>
                            </button>
                        </Motion>

                        <!-- Dropdown Children with AnimatePresence -->
                        <AnimatePresence>
                            <Motion
                                v-if="openDropdowns[item.name] || isDropdownActive(item.children!)"
                                :initial="{ opacity: 0, height: 0, y: -8 }"
                                :animate="{ opacity: 1, height: 'auto', y: 0 }"
                                :exit="{ opacity: 0, height: 0, y: -8 }"
                                :transition="{ type: 'spring', stiffness: 300, damping: 28, mass: 0.8 }"
                                class="overflow-hidden"
                            >
                                <div class="ml-4 pl-3 border-l-2 border-slate-100 dark:border-zinc-800 space-y-0.5 pt-1">
                                    <Motion
                                        v-for="(child, childIndex) in item.children"
                                        :key="child.route"
                                        :initial="{ opacity: 0, x: -12 }"
                                        :animate="{ opacity: 1, x: 0 }"
                                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: childIndex * 0.05 }"
                                        :whileHover="{ x: 4, scale: 1.01 }"
                                        :whileTap="{ scale: 0.97 }"
                                    >
                                        <Link
                                            :href="getRouteUrl(child.route!)"
                                            @click="handleNavClick"
                                            class="group flex items-center gap-2 px-2.5 py-2 rounded-lg transition-all duration-200"
                                            :class="isActive(child.route!)
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 font-medium'
                                                : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-zinc-800/50 dark:hover:text-slate-300'"
                                        >
                                            <component
                                                :is="child.icon"
                                                class="w-3.5 h-3.5 shrink-0 transition-transform duration-200 group-hover:scale-110"
                                                :class="isActive(child.route!) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-500'"
                                            />
                                            <span class="text-[12px] truncate">{{ child.name }}</span>

                                            <!-- Child Badge with Pulse Animation -->
                                            <Motion
                                                v-if="child.badge && child.badge > 0"
                                                :initial="{ scale: 0 }"
                                                :animate="{ scale: 1 }"
                                                :transition="{ type: 'spring', stiffness: 400, damping: 15 }"
                                                class="ml-auto flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[9px] font-bold text-white bg-red-500 rounded-full"
                                            >
                                                {{ child.badge > 99 ? '99+' : child.badge }}
                                            </Motion>

                                            <!-- Active Dot with Scale Animation -->
                                            <Motion
                                                v-else-if="isActive(child.route!)"
                                                :initial="{ scale: 0 }"
                                                :animate="{ scale: 1 }"
                                                :transition="{ type: 'spring', stiffness: 500, damping: 20 }"
                                                class="ml-auto w-1.5 h-1.5 rounded-full bg-emerald-500"
                                            />
                                        </Link>
                                    </Motion>
                                </div>
                            </Motion>
                        </AnimatePresence>
                    </div>
                </template>
            </div>
        </div>

        <!-- User Profile (Desktop) - With Dropdown Menu -->
        <div class="desktop-profile-section p-3 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 relative">
            <!-- Profile Card - Clickable to toggle dropdown -->
            <button
                @click="toggleDesktopProfileMenu"
                class="w-full flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700/50 shadow-sm hover:border-emerald-200 dark:hover:border-emerald-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
            >
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-linear-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-xs shadow-sm shadow-emerald-500/20 shrink-0">
                        {{ user?.name?.charAt(0) || 'U' }}
                    </div>
                    <div class="flex flex-col min-w-0 text-left">
                        <span class="text-[13px] font-semibold text-slate-900 dark:text-white truncate">{{ user?.name }}</span>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 truncate uppercase tracking-wide">{{ user?.role }}</span>
                    </div>
                </div>
                <ChevronDown
                    class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0"
                    :class="showDesktopProfileMenu ? 'rotate-180' : ''"
                />
            </button>

            <!-- Desktop Profile Dropdown Menu -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-2"
            >
                <div
                    v-if="showDesktopProfileMenu"
                    class="absolute bottom-full left-3 right-3 mb-2 bg-white dark:bg-zinc-800 rounded-xl border border-slate-200 dark:border-zinc-700 shadow-lg overflow-hidden"
                >
                    <!-- Profile Link -->
                    <Link
                        :href="profileUrl"
                        @click="showDesktopProfileMenu = false"
                        class="flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-700/50 transition-colors focus:outline-none focus-visible:bg-slate-50 dark:focus-visible:bg-zinc-700/50"
                    >
                        <UserIcon class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                        <span class="text-sm font-medium">Profil Saya</span>
                    </Link>

                    <!-- Settings Link (Coming Soon) -->
                    <button
                        @click="showComingSoon('Pengaturan')"
                        class="w-full flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-700/50 transition-colors focus:outline-none focus-visible:bg-slate-50 dark:focus-visible:bg-zinc-700/50"
                    >
                        <SettingsIcon class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                        <span class="text-sm font-medium">Pengaturan</span>
                        <span class="ml-auto text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-0.5 rounded-full">
                            Segera
                        </span>
                    </button>

                    <!-- Divider -->
                    <div class="h-px bg-slate-100 dark:bg-zinc-700"></div>

                    <!-- Logout Button -->
                    <button
                        @click="logout"
                        class="w-full flex items-center gap-3 px-4 py-3 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors focus:outline-none focus-visible:bg-red-50 dark:focus-visible:bg-red-900/20"
                    >
                        <LogOut class="w-4 h-4" />
                        <span class="text-sm font-medium">Keluar</span>
                    </button>
                </div>
            </Transition>
        </div>
    </aside>
</template>

<style scoped>
/**
 * Custom thin scrollbar untuk sidebar navigation
 */
.sidebar-scroll {
    scrollbar-width: thin;
    scrollbar-color: var(--color-slate-200) transparent;
}

.sidebar-scroll::-webkit-scrollbar {
    width: 4px;
}

.sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
    background-color: var(--color-slate-200);
    border-radius: 9999px;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background-color: var(--color-slate-300);
}

/* Dark mode scrollbar */
:root.dark .sidebar-scroll {
    scrollbar-color: var(--color-zinc-700) transparent;
}

:root.dark .sidebar-scroll::-webkit-scrollbar-thumb {
    background-color: var(--color-zinc-700);
}

:root.dark .sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background-color: var(--color-zinc-600);
}
</style>
