<script setup lang="ts">
/**
 * MobileBottomNav Component - Bottom tab bar untuk tampilan mobile
 *
 * Component ini menampilkan navigasi bottom bar dengan:
 * - Maksimal 4 menu items + tombol "Lainnya"
 * - Badge notification support
 * - Active state indicator
 * Mengikuti iOS-like Design Standard
 */
import { Link } from '@inertiajs/vue3';
import { useNavigation } from '@/composables/useNavigation';
import { useHaptics } from '@/composables/useHaptics';
import { MoreHorizontal } from 'lucide-vue-next';

const props = defineProps<{
    showMobileNav: boolean;
}>();

const emit = defineEmits<{
    (e: 'toggle-mobile-nav'): void;
}>();

const {
    mobileNavItems,
    hasMoreMenuItems,
    getRouteUrl,
    isActive,
} = useNavigation();

const haptics = useHaptics();

/**
 * Navigation click feedback
 */
const handleNavClick = () => {
    haptics.light();
};

/**
 * Toggle mobile navigation sheet
 */
const toggleMobileNav = () => {
    haptics.light();
    emit('toggle-mobile-nav');
};
</script>

<template>
    <nav
        class="lg:hidden fixed bottom-0 inset-x-0 bg-white/95 dark:bg-zinc-900/95 border-t border-slate-200 dark:border-zinc-800 z-50"
        role="navigation"
        aria-label="Menu utama"
    >
        <div class="flex justify-around items-stretch h-[72px] pb-[env(safe-area-inset-bottom,8px)]">
            <!-- Navigation Items -->
            <Link
                v-for="item in mobileNavItems"
                :key="item.route"
                :href="getRouteUrl(item.route!)"
                @click="handleNavClick"
                class="flex flex-col items-center justify-center gap-1 flex-1 min-w-0 px-1 active:bg-slate-50 dark:active:bg-zinc-800/50 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-emerald-500"
                :aria-current="isActive(item.route!) ? 'page' : undefined"
            >
                <div class="relative">
                    <!-- Icon Container with Active Background -->
                    <div
                        class="w-11 h-7 rounded-full flex items-center justify-center transition-all duration-200"
                        :class="isActive(item.route!) ? 'bg-emerald-100 dark:bg-emerald-900/30' : ''"
                    >
                        <component
                            :is="item.icon"
                            class="w-[22px] h-[22px] transition-all duration-200"
                            :class="isActive(item.route!)
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : 'text-slate-400 dark:text-slate-500'"
                        />
                    </div>
                    <!-- Mobile Badge (absolute positioned) -->
                    <span
                        v-if="item.badge && item.badge > 0"
                        class="absolute -top-1 right-0 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm"
                        aria-label="Ada notifikasi baru"
                    >
                        {{ item.badge > 99 ? '99+' : item.badge }}
                    </span>
                </div>
                <span
                    class="text-[11px] font-medium transition-colors duration-200 truncate max-w-full px-0.5"
                    :class="isActive(item.route!) ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-slate-500 dark:text-slate-400'"
                >
                    {{ item.name }}
                </span>
            </Link>

            <!-- More Button - Shows full navigation sheet -->
            <button
                v-if="hasMoreMenuItems"
                @click="toggleMobileNav"
                class="flex flex-col items-center justify-center gap-1 flex-1 min-w-0 px-1 active:bg-slate-50 dark:active:bg-zinc-800/50 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-emerald-500"
                :class="showMobileNav ? 'text-emerald-600 dark:text-emerald-400' : ''"
                aria-label="Menu lainnya"
                :aria-expanded="showMobileNav"
            >
                <div class="relative">
                    <div
                        class="w-11 h-7 rounded-full flex items-center justify-center transition-all duration-200"
                        :class="showMobileNav ? 'bg-emerald-100 dark:bg-emerald-900/30' : ''"
                    >
                        <MoreHorizontal
                            class="w-[22px] h-[22px] transition-all duration-200"
                            :class="showMobileNav
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : 'text-slate-400 dark:text-slate-500'"
                        />
                    </div>
                </div>
                <span
                    class="text-[11px] font-medium transition-colors duration-200"
                    :class="showMobileNav ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-slate-500 dark:text-slate-400'"
                >
                    Lainnya
                </span>
            </button>
        </div>
    </nav>
</template>
