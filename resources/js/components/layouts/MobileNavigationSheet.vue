<script setup lang="ts">
/**
 * MobileNavigationSheet Component - Full navigation sheet untuk mobile
 *
 * Component ini menampilkan seluruh menu navigasi dalam bottom sheet dengan:
 * - Support dropdown menu groups
 * - Badge notification support
 * - Active state indicator
 * - iOS-like drag handle indicator
 * Mengikuti iOS-like Design Standard
 */
import { Link } from '@inertiajs/vue3';
import BaseModal from '@/components/ui/BaseModal.vue';
import { useNavigation } from '@/composables/useNavigation';
import { useHaptics } from '@/composables/useHaptics';
import { ChevronDown, X } from 'lucide-vue-next';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const {
    menuItems,
    openDropdowns,
    getRouteUrl,
    isActive,
    isDropdownActive,
    toggleDropdown,
} = useNavigation();

const haptics = useHaptics();

/**
 * Close the navigation sheet
 */
const close = () => {
    emit('close');
};

/**
 * Navigation click feedback dan close sheet
 */
const handleNavClick = () => {
    haptics.light();
    close();
};

/**
 * Handle dropdown toggle dengan haptic feedback
 */
const handleDropdownToggle = (groupName: string) => {
    haptics.light();
    toggleDropdown(groupName);
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
        <div class="flex flex-col gap-2">
            <!-- Drag Handle -->
            <div class="w-10 h-1 bg-slate-200 dark:bg-zinc-700 rounded-full mx-auto -mt-2 mb-1" aria-hidden="true"></div>

            <!-- Header -->
            <div class="flex items-center justify-between px-1 mb-2">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Menu Navigasi</h3>
                <button
                    @click="close"
                    class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                    aria-label="Tutup menu"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Full Navigation Menu -->
            <div class="space-y-1 max-h-[60vh] overflow-y-auto">
                <template v-for="item in menuItems" :key="item.name">
                    <!-- Regular Menu Item -->
                    <Link
                        v-if="!item.children"
                        :href="getRouteUrl(item.route!)"
                        @click="handleNavClick"
                        class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                        :class="isActive(item.route!)
                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400'
                            : 'text-slate-700 dark:text-slate-300 active:bg-slate-50 dark:active:bg-zinc-800'"
                    >
                        <div
                            class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                            :class="isActive(item.route!)
                                ? 'bg-emerald-500 text-white'
                                : 'bg-slate-100 dark:bg-zinc-800'"
                        >
                            <component :is="item.icon" class="w-5 h-5" />
                        </div>
                        <span class="font-medium flex-1">{{ item.name }}</span>
                        <span
                            v-if="item.badge && item.badge > 0"
                            class="min-w-[22px] h-[22px] flex items-center justify-center px-1.5 text-[11px] font-bold text-white bg-red-500 rounded-full"
                        >
                            {{ item.badge > 99 ? '99+' : item.badge }}
                        </span>
                    </Link>

                    <!-- Dropdown Menu Group -->
                    <div v-else class="space-y-1">
                        <button
                            @click="handleDropdownToggle(item.name)"
                            class="w-full flex items-center gap-3 px-3 py-3 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                            :class="isDropdownActive(item.children!) || openDropdowns[item.name]
                                ? 'bg-emerald-50/50 text-emerald-700 dark:bg-emerald-900/10 dark:text-emerald-400'
                                : 'text-slate-700 dark:text-slate-300 active:bg-slate-50 dark:active:bg-zinc-800'"
                        >
                            <div
                                class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                :class="isDropdownActive(item.children!) || openDropdowns[item.name]
                                    ? 'bg-emerald-500/90 text-white'
                                    : 'bg-slate-100 dark:bg-zinc-800'"
                            >
                                <component :is="item.icon" class="w-5 h-5" />
                            </div>
                            <span class="font-medium flex-1 text-left">{{ item.name }}</span>
                            <span
                                v-if="item.badge && item.badge > 0"
                                class="min-w-[22px] h-[22px] flex items-center justify-center px-1.5 text-[11px] font-bold text-white bg-red-500 rounded-full mr-1"
                            >
                                {{ item.badge > 99 ? '99+' : item.badge }}
                            </span>
                            <ChevronDown
                                class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                :class="openDropdowns[item.name] || isDropdownActive(item.children!) ? 'rotate-180' : ''"
                            />
                        </button>

                        <!-- Children Items -->
                        <div
                            v-if="openDropdowns[item.name] || isDropdownActive(item.children!)"
                            class="ml-6 pl-3 border-l-2 border-slate-100 dark:border-zinc-800 space-y-1"
                        >
                            <Link
                                v-for="child in item.children"
                                :key="child.route"
                                :href="getRouteUrl(child.route!)"
                                @click="handleNavClick"
                                class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                                :class="isActive(child.route!)
                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 font-medium'
                                    : 'text-slate-600 dark:text-slate-400 active:bg-slate-50 dark:active:bg-zinc-800'"
                            >
                                <component :is="child.icon" class="w-4 h-4 shrink-0" />
                                <span class="text-sm flex-1">{{ child.name }}</span>
                                <span
                                    v-if="child.badge && child.badge > 0"
                                    class="min-w-[20px] h-5 flex items-center justify-center px-1 text-[10px] font-bold text-white bg-red-500 rounded-full"
                                >
                                    {{ child.badge > 99 ? '99+' : child.badge }}
                                </span>
                            </Link>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Close Button -->
            <button
                @click="close"
                class="mt-3 w-full py-3.5 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400 font-semibold text-sm active:scale-[0.98] transition-transform focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
            >
                Tutup
            </button>
        </div>
    </BaseModal>
</template>
