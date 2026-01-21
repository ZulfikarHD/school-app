<script setup lang="ts">
/**
 * Breadcrumb Component - Navigasi hierarki untuk membantu user
 * memahami posisi mereka dalam aplikasi serta navigasi kembali
 * ke halaman parent dengan mudah
 *
 * Features:
 * - Responsive: Collapsed on mobile, full on desktop
 * - Support icons per item
 * - Clickable links dengan Inertia navigation
 * - Current page non-clickable dengan styling distinct
 */
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Home, ChevronRight } from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';

export interface BreadcrumbItem {
    /** Label yang ditampilkan */
    label: string;
    /** URL tujuan - jika undefined, item tidak clickable (current page) */
    href?: string;
    /** Icon component dari lucide-vue-next (optional) */
    icon?: any;
}

interface Props {
    /** Array breadcrumb items dari parent ke current */
    items: BreadcrumbItem[];
    /** Tampilkan home icon di awal breadcrumb */
    showHome?: boolean;
    /** URL untuk home link */
    homeHref?: string;
    /** Separator style */
    separator?: 'chevron' | 'slash';
    /** Max items to show on mobile (sisanya di-collapse) */
    mobileMaxItems?: number;
}

const props = withDefaults(defineProps<Props>(), {
    showHome: true,
    homeHref: '/dashboard',
    separator: 'chevron',
    mobileMaxItems: 2,
});

const haptics = useHaptics();

/**
 * Items untuk desktop view - tampilkan semua
 */
const desktopItems = computed(() => props.items);

/**
 * Items untuk mobile view - collapse jika terlalu banyak
 * Tampilkan: [first item] ... [last N items]
 */
const mobileItems = computed(() => {
    if (props.items.length <= props.mobileMaxItems) {
        return { collapsed: false, items: props.items };
    }

    // Ambil item pertama dan N item terakhir
    const firstItem = props.items[0];
    const lastItems = props.items.slice(-(props.mobileMaxItems - 1));

    return {
        collapsed: true,
        firstItem,
        lastItems,
        collapsedCount: props.items.length - props.mobileMaxItems,
    };
});

/**
 * Handle navigation click dengan haptic feedback
 */
const handleClick = () => {
    haptics.light();
};
</script>

<template>
    <nav aria-label="Breadcrumb" class="flex items-center text-sm">
        <!-- Desktop Breadcrumb -->
        <ol class="hidden sm:flex items-center gap-1.5 flex-wrap">
            <!-- Home Icon -->
            <li v-if="showHome" class="flex items-center">
                <Link
                    :href="homeHref"
                    @click="handleClick"
                    class="flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:text-slate-300 dark:hover:bg-zinc-800 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                    title="Dashboard"
                >
                    <Home class="w-4 h-4" />
                </Link>
                <ChevronRight v-if="separator === 'chevron'" class="w-4 h-4 text-slate-300 dark:text-zinc-600 mx-1" />
                <span v-else class="text-slate-300 dark:text-zinc-600 mx-2">/</span>
            </li>

            <!-- Breadcrumb Items -->
            <li
                v-for="(item, index) in desktopItems"
                :key="index"
                class="flex items-center"
            >
                <!-- Clickable Link (not current page) -->
                <Link
                    v-if="item.href"
                    :href="item.href"
                    @click="handleClick"
                    class="flex items-center gap-1.5 px-2 py-1 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-zinc-800 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                >
                    <component v-if="item.icon" :is="item.icon" class="w-3.5 h-3.5" />
                    <span>{{ item.label }}</span>
                </Link>

                <!-- Current Page (non-clickable) -->
                <span
                    v-else
                    class="flex items-center gap-1.5 px-2 py-1 text-slate-900 dark:text-slate-100 font-medium"
                >
                    <component v-if="item.icon" :is="item.icon" class="w-3.5 h-3.5" />
                    <span class="truncate max-w-[200px]">{{ item.label }}</span>
                </span>

                <!-- Separator (not on last item) -->
                <template v-if="index < desktopItems.length - 1">
                    <ChevronRight v-if="separator === 'chevron'" class="w-4 h-4 text-slate-300 dark:text-zinc-600 mx-1" />
                    <span v-else class="text-slate-300 dark:text-zinc-600 mx-2">/</span>
                </template>
            </li>
        </ol>

        <!-- Mobile Breadcrumb (Collapsed) -->
        <ol class="sm:hidden flex items-center gap-1">
            <!-- Home Icon -->
            <li v-if="showHome" class="flex items-center">
                <Link
                    :href="homeHref"
                    @click="handleClick"
                    class="flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 active:bg-slate-100 dark:active:bg-zinc-800 transition-colors"
                >
                    <Home class="w-4 h-4" />
                </Link>
                <ChevronRight class="w-4 h-4 text-slate-300 dark:text-zinc-600" />
            </li>

            <!-- Collapsed View -->
            <template v-if="mobileItems.collapsed">
                <!-- First Item -->
                <li class="flex items-center">
                    <Link
                        v-if="mobileItems.firstItem?.href"
                        :href="mobileItems.firstItem.href"
                        @click="handleClick"
                        class="px-2 py-1 rounded-lg text-slate-500 dark:text-slate-400 active:bg-slate-100 dark:active:bg-zinc-800 transition-colors text-sm"
                    >
                        {{ mobileItems.firstItem?.label }}
                    </Link>
                    <span v-else class="px-2 py-1 text-slate-900 dark:text-slate-100 font-medium text-sm">
                        {{ mobileItems.firstItem?.label }}
                    </span>
                    <ChevronRight class="w-4 h-4 text-slate-300 dark:text-zinc-600" />
                </li>

                <!-- Ellipsis -->
                <li class="flex items-center">
                    <span class="px-2 py-1 text-slate-400 dark:text-slate-500">...</span>
                    <ChevronRight class="w-4 h-4 text-slate-300 dark:text-zinc-600" />
                </li>

                <!-- Last Items -->
                <li
                    v-for="(item, index) in mobileItems.lastItems"
                    :key="index"
                    class="flex items-center"
                >
                    <Link
                        v-if="item.href"
                        :href="item.href"
                        @click="handleClick"
                        class="px-2 py-1 rounded-lg text-slate-500 dark:text-slate-400 active:bg-slate-100 dark:active:bg-zinc-800 transition-colors text-sm truncate max-w-[100px]"
                    >
                        {{ item.label }}
                    </Link>
                    <span v-else class="px-2 py-1 text-slate-900 dark:text-slate-100 font-medium text-sm truncate max-w-[120px]">
                        {{ item.label }}
                    </span>
                    <ChevronRight
                        v-if="index < (mobileItems.lastItems?.length || 0) - 1"
                        class="w-4 h-4 text-slate-300 dark:text-zinc-600"
                    />
                </li>
            </template>

            <!-- Non-Collapsed View -->
            <template v-else>
                <li
                    v-for="(item, index) in mobileItems.items"
                    :key="index"
                    class="flex items-center"
                >
                    <Link
                        v-if="item.href"
                        :href="item.href"
                        @click="handleClick"
                        class="px-2 py-1 rounded-lg text-slate-500 dark:text-slate-400 active:bg-slate-100 dark:active:bg-zinc-800 transition-colors text-sm truncate max-w-[100px]"
                    >
                        {{ item.label }}
                    </Link>
                    <span v-else class="px-2 py-1 text-slate-900 dark:text-slate-100 font-medium text-sm truncate max-w-[120px]">
                        {{ item.label }}
                    </span>
                    <ChevronRight
                        v-if="index < mobileItems.items.length - 1"
                        class="w-4 h-4 text-slate-300 dark:text-zinc-600"
                    />
                </li>
            </template>
        </ol>
    </nav>
</template>
