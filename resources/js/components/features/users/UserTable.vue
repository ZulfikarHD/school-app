<script setup lang="ts">
/**
 * UserTable Component - Tabel data user dengan filter, pagination,
 * dan aksi CRUD yang mencakup skeleton loading dan mobile-optimized view
 */
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import {
    Search,
    Edit,
    Trash2,
    RefreshCw,
    Power,
    Shield,
    Filter,
    X,
    ChevronLeft,
    ChevronRight
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { index as usersIndex } from '@/routes/admin/users';
import Badge from '@/components/ui/Badge.vue';

interface User {
    id: number;
    name: string;
    email: string;
    username: string;
    role: string;
    status: 'ACTIVE' | 'INACTIVE';
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Pagination {
    data: User[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    per_page: number;
}

interface Props {
    users: Pagination;
    filters?: {
        search?: string;
        role?: string;
        status?: string;
    };
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    filters: () => ({}),
});

const emit = defineEmits(['edit', 'delete', 'reset-password', 'toggle-status']);
const haptics = useHaptics();

// Local state for filters
const search = ref(props.filters.search || '');
const role = ref(props.filters.role || '');
const status = ref(props.filters.status || '');

// Debounce search
let timeout: ReturnType<typeof setTimeout>;

const handleSearch = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const applyFilters = () => {
    haptics.selection();
    router.get(
        usersIndex().url,
        {
            search: search.value,
            role: role.value,
            status: status.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
};

/**
 * Role badge variant mapping menggunakan Badge component
 * untuk visual consistency di seluruh aplikasi
 */
const getRoleBadgeVariant = (role: string): 'secondary' | 'info' | 'primary' | 'success' | 'warning' | 'default' => {
    const variants: Record<string, 'secondary' | 'info' | 'primary' | 'success' | 'warning' | 'default'> = {
        SUPERADMIN: 'secondary',
        ADMIN: 'info',
        PRINCIPAL: 'primary',
        TEACHER: 'success',
        PARENT: 'warning',
        STUDENT: 'default',
    };
    return variants[role] || 'default';
};

/**
 * Status badge variant untuk indicator aktif/nonaktif user
 */
const getStatusBadgeVariant = (status: string): 'success' | 'error' => {
    return status === 'ACTIVE' ? 'success' : 'error';
};

const getStatusLabel = (status: string) => {
    return status === 'ACTIVE' ? 'Aktif' : 'Nonaktif';
};

// Action handlers
const onEdit = (user: User) => {
    haptics.light();
    emit('edit', user);
};

const onDelete = (user: User) => {
    haptics.medium();
    emit('delete', user);
};

const onResetPassword = (user: User) => {
    haptics.light();
    emit('reset-password', user);
};

// State untuk collapsible filter (mobile)
const showFilters = ref(false);

// Check if any filter is active
const hasActiveFilters = () => {
    return search.value || role.value || status.value;
};

// Reset all filters
const resetFilters = () => {
    haptics.light();
    search.value = '';
    role.value = '';
    status.value = '';
    applyFilters();
};

const onToggleStatus = (user: User) => {
    haptics.light();
    emit('toggle-status', user);
};

</script>

<template>
    <div class="space-y-4">
        <!-- Filters & Search - Enhanced dengan collapsible mobile filters -->
        <Motion
            :initial="{ opacity: 0, y: -10 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
        >
            <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-zinc-800">
                <!-- Search Row -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                    <!-- Search Input -->
                    <div class="flex-1 relative">
                        <label for="user-search" class="sr-only">Cari user</label>
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" aria-hidden="true" />
                        <input
                            id="user-search"
                            v-model="search"
                            @input="handleSearch"
                            type="text"
                            placeholder="Cari nama, email, atau username..."
                            class="w-full pl-10 pr-4 py-3 min-h-[48px] text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50/80 dark:bg-zinc-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900 transition-all"
                        >
                    </div>

                    <!-- Filter & Reset Buttons (Mobile) -->
                    <div class="flex gap-2 sm:hidden">
                        <button
                            @click="showFilters = !showFilters"
                            :class="[
                                'flex-1 flex items-center justify-center gap-2 px-4 py-3 min-h-[48px] rounded-xl border transition-colors',
                                showFilters
                                    ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400'
                                    : 'bg-slate-50/80 dark:bg-zinc-800 border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300'
                            ]"
                        >
                            <Filter :size="18" />
                            <span class="font-medium">Filter</span>
                            <span v-if="hasActiveFilters()" class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                        </button>

                        <button
                            v-if="hasActiveFilters()"
                            @click="resetFilters"
                            class="flex items-center justify-center gap-2 px-4 py-3 min-h-[48px] bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-xl text-red-600 dark:text-red-400 transition-colors"
                        >
                            <X :size="18" />
                        </button>
                    </div>

                    <!-- Desktop Filters -->
                    <div class="hidden sm:flex gap-2">
                        <div>
                            <label for="user-role-filter" class="sr-only">Filter Role</label>
                            <select
                                id="user-role-filter"
                                v-model="role"
                                @change="applyFilters"
                                class="text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2.5 px-3 min-h-[44px] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                            >
                                <option value="">Semua Role</option>
                                <option value="SUPERADMIN">Super Admin</option>
                                <option value="ADMIN">Admin TU</option>
                                <option value="PRINCIPAL">Kepala Sekolah</option>
                                <option value="TEACHER">Guru</option>
                                <option value="PARENT">Orang Tua</option>
                            </select>
                        </div>

                        <div>
                            <label for="user-status-filter" class="sr-only">Filter Status</label>
                            <select
                                id="user-status-filter"
                                v-model="status"
                                @change="applyFilters"
                                class="text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2.5 px-3 min-h-[44px] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                            >
                                <option value="">Semua Status</option>
                                <option value="ACTIVE">Aktif</option>
                                <option value="INACTIVE">Nonaktif</option>
                            </select>
                        </div>

                        <!-- Reset Button (Desktop) -->
                        <button
                            v-if="hasActiveFilters()"
                            @click="resetFilters"
                            class="flex items-center gap-2 px-3 py-2.5 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-xl text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors text-sm font-medium"
                        >
                            <X :size="16" />
                            <span>Reset</span>
                        </button>
                    </div>
                </div>

                <!-- Collapsible Mobile Filters -->
                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 max-h-0"
                    enter-to-class="opacity-100 max-h-40"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 max-h-40"
                    leave-to-class="opacity-0 max-h-0"
                >
                    <div v-if="showFilters" class="sm:hidden mt-3 pt-3 border-t border-slate-200 dark:border-zinc-700 overflow-hidden">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-semibold tracking-wide uppercase text-slate-500 dark:text-zinc-400 mb-1.5">
                                    Role
                                </label>
                                <select
                                    v-model="role"
                                    @change="applyFilters"
                                    class="w-full text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2.5 px-3 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                                >
                                    <option value="">Semua</option>
                                    <option value="SUPERADMIN">Super Admin</option>
                                    <option value="ADMIN">Admin TU</option>
                                    <option value="PRINCIPAL">Kepala Sekolah</option>
                                    <option value="TEACHER">Guru</option>
                                    <option value="PARENT">Orang Tua</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold tracking-wide uppercase text-slate-500 dark:text-zinc-400 mb-1.5">
                                    Status
                                </label>
                                <select
                                    v-model="status"
                                    @change="applyFilters"
                                    class="w-full text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2.5 px-3 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                                >
                                    <option value="">Semua</option>
                                    <option value="ACTIVE">Aktif</option>
                                    <option value="INACTIVE">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Motion>

        <!-- Desktop Table dengan proper accessibility dan skeleton loading -->
        <Motion
            :initial="{ opacity: 0, y: 20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.1 }"
        >
            <div class="hidden md:block bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left" role="table" aria-label="Tabel daftar user">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-100 dark:border-zinc-800">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 font-semibold tracking-wide">User</th>
                                <th scope="col" class="px-6 py-3.5 font-semibold tracking-wide">Username</th>
                                <th scope="col" class="px-6 py-3.5 font-semibold tracking-wide">Role</th>
                                <th scope="col" class="px-6 py-3.5 font-semibold tracking-wide">Status</th>
                                <th scope="col" class="px-6 py-3.5 font-semibold tracking-wide text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <!-- Skeleton Loading State -->
                            <template v-if="loading">
                                <tr v-for="i in 5" :key="i" class="animate-pulse">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-2">
                                            <div class="h-4 w-32 bg-slate-200 dark:bg-zinc-700 rounded"></div>
                                            <div class="h-3 w-40 bg-slate-100 dark:bg-zinc-800 rounded"></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="h-4 w-24 bg-slate-200 dark:bg-zinc-700 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="h-6 w-20 bg-slate-200 dark:bg-zinc-700 rounded-full"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="h-6 w-16 bg-slate-200 dark:bg-zinc-700 rounded-full"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-1.5">
                                            <div class="w-9 h-9 bg-slate-100 dark:bg-zinc-800 rounded-xl"></div>
                                            <div class="w-9 h-9 bg-slate-100 dark:bg-zinc-800 rounded-xl"></div>
                                            <div class="w-9 h-9 bg-slate-100 dark:bg-zinc-800 rounded-xl"></div>
                                            <div class="w-9 h-9 bg-slate-100 dark:bg-zinc-800 rounded-xl"></div>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <!-- Empty State -->
                            <tr v-else-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                                            <Shield class="w-8 h-8 text-slate-300 dark:text-zinc-600" aria-hidden="true" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-700 dark:text-slate-300">Belum ada user ditemukan</p>
                                            <p class="text-sm text-slate-400 mt-1">
                                                {{ hasActiveFilters() ? 'Coba ubah filter pencarian' : 'Tambah user baru untuk memulai' }}
                                            </p>
                                        </div>
                                        <button
                                            v-if="hasActiveFilters()"
                                            @click="resetFilters"
                                            class="mt-2 inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-medium transition-colors text-sm"
                                        >
                                            <X :size="16" />
                                            <span>Reset Filter</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Data Rows -->
                            <tr v-else v-for="user in users.data" :key="user.id" class="group hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <!-- Avatar - Modern gradient border style -->
                                        <div class="relative shrink-0">
                                            <div class="w-10 h-10 rounded-xl overflow-hidden bg-linear-to-br from-emerald-400 to-teal-500 p-0.5">
                                                <div class="w-full h-full rounded-[8px] bg-linear-to-br from-slate-50 to-slate-100 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center">
                                                    <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                                        {{ user.name.charAt(0).toUpperCase() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-medium text-slate-900 dark:text-slate-100 truncate">{{ user.name }}</span>
                                            <span class="text-xs text-slate-500 truncate">{{ user.email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400 font-mono text-sm">{{ user.username }}</td>
                                <td class="px-6 py-4">
                                    <Badge :variant="getRoleBadgeVariant(user.role)" size="sm" rounded="square">
                                        {{ user.role }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge
                                        :variant="getStatusBadgeVariant(user.status)"
                                        size="sm"
                                        rounded="square"
                                        dot
                                    >
                                        {{ getStatusLabel(user.status) }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5" role="group" aria-label="Aksi user">
                                        <Motion :whileTap="{ scale: 0.95 }">
                                            <button
                                                @click="onEdit(user)"
                                                class="w-9 h-9 flex items-center justify-center text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 border border-transparent hover:border-emerald-200 dark:hover:border-emerald-800"
                                                :aria-label="`Edit ${user.name}`"
                                                title="Edit"
                                            >
                                                <Edit class="w-4 h-4" aria-hidden="true" />
                                            </button>
                                        </Motion>
                                        <Motion :whileTap="{ scale: 0.95 }">
                                            <button
                                                @click="onResetPassword(user)"
                                                class="w-9 h-9 flex items-center justify-center text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/50 border border-transparent hover:border-amber-200 dark:hover:border-amber-800"
                                                :aria-label="`Reset password ${user.name}`"
                                                title="Reset Password"
                                            >
                                                <RefreshCw class="w-4 h-4" aria-hidden="true" />
                                            </button>
                                        </Motion>
                                        <Motion :whileTap="{ scale: 0.95 }">
                                            <button
                                                @click="onToggleStatus(user)"
                                                class="w-9 h-9 flex items-center justify-center text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-900/20 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500/50 border border-transparent hover:border-sky-200 dark:hover:border-sky-800"
                                                :aria-label="`${user.status === 'ACTIVE' ? 'Nonaktifkan' : 'Aktifkan'} ${user.name}`"
                                                :title="user.status === 'ACTIVE' ? 'Nonaktifkan' : 'Aktifkan'"
                                            >
                                                <Power class="w-4 h-4" aria-hidden="true" />
                                            </button>
                                        </Motion>
                                        <Motion :whileTap="{ scale: 0.95 }">
                                            <button
                                                @click="onDelete(user)"
                                                class="w-9 h-9 flex items-center justify-center text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50 border border-transparent hover:border-red-200 dark:hover:border-red-800"
                                                :aria-label="`Hapus ${user.name}`"
                                                title="Hapus"
                                            >
                                                <Trash2 class="w-4 h-4" aria-hidden="true" />
                                            </button>
                                        </Motion>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </Motion>

        <!-- Mobile Cards dengan proper accessibility dan skeleton -->
        <div class="md:hidden space-y-3">
            <!-- Mobile Skeleton Loading -->
            <template v-if="loading">
                <div v-for="i in 4" :key="i" class="bg-white dark:bg-zinc-900 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-zinc-800 animate-pulse">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-zinc-700"></div>
                            <div>
                                <div class="h-4 w-28 bg-slate-200 dark:bg-zinc-700 rounded mb-2"></div>
                                <div class="h-3 w-36 bg-slate-100 dark:bg-zinc-800 rounded"></div>
                            </div>
                        </div>
                        <div class="h-6 w-14 bg-slate-200 dark:bg-zinc-700 rounded-full"></div>
                    </div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="h-5 w-16 bg-slate-200 dark:bg-zinc-700 rounded"></div>
                        <div class="h-3 w-20 bg-slate-100 dark:bg-zinc-800 rounded"></div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-zinc-800">
                        <div class="w-10 h-10 bg-slate-100 dark:bg-zinc-800 rounded-xl"></div>
                        <div class="w-10 h-10 bg-slate-100 dark:bg-zinc-800 rounded-xl"></div>
                        <div class="w-10 h-10 bg-slate-100 dark:bg-zinc-800 rounded-xl"></div>
                        <div class="w-10 h-10 bg-slate-100 dark:bg-zinc-800 rounded-xl"></div>
                    </div>
                </div>
            </template>

            <!-- Mobile Empty State -->
            <div v-else-if="users.data.length === 0" class="p-8 text-center bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                    <Shield class="w-8 h-8 text-slate-300 dark:text-zinc-600" aria-hidden="true" />
                </div>
                <p class="font-medium text-slate-700 dark:text-slate-300 mb-1">Belum ada user</p>
                <p class="text-sm text-slate-400 mb-4">
                    {{ hasActiveFilters() ? 'Coba ubah filter pencarian' : 'Tambah user baru untuk memulai' }}
                </p>
                <button
                    v-if="hasActiveFilters()"
                    @click="resetFilters"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-medium transition-colors text-sm"
                >
                    <X :size="16" />
                    <span>Reset Filter</span>
                </button>
            </div>

            <!-- Mobile Data Cards -->
            <article
                v-else
                v-for="(user, index) in users.data"
                :key="user.id"
                class="bg-white dark:bg-zinc-900 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-zinc-800 active:scale-[0.99] transition-transform"
            >
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: index * 0.03 }"
                >
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <!-- Avatar - Modern gradient border style -->
                            <div class="relative shrink-0">
                                <div class="w-12 h-12 rounded-xl overflow-hidden bg-linear-to-br from-emerald-400 to-teal-500 p-0.5 shadow-sm">
                                    <div class="w-full h-full rounded-[10px] bg-linear-to-br from-slate-50 to-slate-100 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center">
                                        <span class="text-base font-bold text-emerald-600 dark:text-emerald-400">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-slate-900 dark:text-slate-100 truncate">{{ user.name }}</h3>
                                <p class="text-sm text-slate-500 truncate">{{ user.email }}</p>
                            </div>
                        </div>
                        <Badge
                            :variant="getStatusBadgeVariant(user.status)"
                            size="xs"
                            dot
                            class="shrink-0"
                        >
                            {{ getStatusLabel(user.status) }}
                        </Badge>
                    </div>

                    <div class="flex items-center gap-2 mb-4 text-sm text-slate-600 dark:text-slate-400">
                        <Badge :variant="getRoleBadgeVariant(user.role)" size="xs" rounded="square">
                            {{ user.role }}
                        </Badge>
                        <span class="text-xs text-slate-400 font-mono">@{{ user.username }}</span>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-zinc-800" role="group" :aria-label="`Aksi untuk ${user.name}`">
                        <button
                            @click="onResetPassword(user)"
                            class="w-11 h-11 flex items-center justify-center text-amber-600 bg-amber-50 dark:bg-amber-900/20 rounded-xl active:bg-amber-100 dark:active:bg-amber-900/30 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/50"
                            :aria-label="`Reset password ${user.name}`"
                        >
                            <RefreshCw class="w-4 h-4" aria-hidden="true" />
                        </button>
                        <button
                            @click="onToggleStatus(user)"
                            class="w-11 h-11 flex items-center justify-center text-sky-600 bg-sky-50 dark:bg-sky-900/20 rounded-xl active:bg-sky-100 dark:active:bg-sky-900/30 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500/50"
                            :aria-label="`${user.status === 'ACTIVE' ? 'Nonaktifkan' : 'Aktifkan'} ${user.name}`"
                        >
                            <Power class="w-4 h-4" aria-hidden="true" />
                        </button>
                        <button
                            @click="onEdit(user)"
                            class="w-11 h-11 flex items-center justify-center text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl active:bg-emerald-100 dark:active:bg-emerald-900/30 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50"
                            :aria-label="`Edit ${user.name}`"
                        >
                            <Edit class="w-4 h-4" aria-hidden="true" />
                        </button>
                        <button
                            @click="onDelete(user)"
                            class="w-11 h-11 flex items-center justify-center text-red-600 bg-red-50 dark:bg-red-900/20 rounded-xl active:bg-red-100 dark:active:bg-red-900/30 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50"
                            :aria-label="`Hapus ${user.name}`"
                        >
                            <Trash2 class="w-4 h-4" aria-hidden="true" />
                        </button>
                    </div>
                </Motion>
            </article>
        </div>

        <!-- Pagination - Enhanced dengan mobile dan desktop view -->
        <Motion
            v-if="users.total > users.per_page"
            :initial="{ opacity: 0 }"
            :animate="{ opacity: 1 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.2 }"
            class="pt-4 border-t border-slate-200 dark:border-zinc-800"
        >
            <!-- Mobile Pagination -->
            <div class="flex items-center justify-between md:hidden">
                <div class="text-sm text-slate-500">
                    {{ users.from }}-{{ users.to }} dari {{ users.total }}
                </div>
                <div class="flex gap-2">
                    <Motion :whileTap="{ scale: 0.95 }">
                        <Link
                            :href="users.links[0]?.url || '#'"
                            :class="[
                                'flex items-center justify-center w-11 h-11 rounded-xl border transition-colors',
                                users.links[0]?.url
                                    ? 'border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800 active:bg-slate-100'
                                    : 'border-slate-100 dark:border-zinc-800 text-slate-300 dark:text-zinc-600 cursor-not-allowed'
                            ]"
                            preserve-scroll
                            preserve-state
                        >
                            <ChevronLeft class="w-5 h-5" />
                        </Link>
                    </Motion>

                    <!-- Current Page Indicator -->
                    <div class="flex items-center justify-center px-4 h-11 bg-emerald-500 text-white rounded-xl font-semibold text-sm min-w-[44px]">
                        {{ users.current_page }}
                    </div>

                    <Motion :whileTap="{ scale: 0.95 }">
                        <Link
                            :href="users.links[users.links.length - 1]?.url || '#'"
                            :class="[
                                'flex items-center justify-center w-11 h-11 rounded-xl border transition-colors',
                                users.links[users.links.length - 1]?.url
                                    ? 'border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800 active:bg-slate-100'
                                    : 'border-slate-100 dark:border-zinc-800 text-slate-300 dark:text-zinc-600 cursor-not-allowed'
                            ]"
                            preserve-scroll
                            preserve-state
                        >
                            <ChevronRight class="w-5 h-5" />
                        </Link>
                    </Motion>
                </div>
            </div>

            <!-- Desktop Pagination -->
            <nav class="hidden md:flex items-center justify-between" aria-label="Navigasi halaman user">
                <div class="text-sm text-slate-500">
                    Menampilkan {{ users.from }} - {{ users.to }} dari {{ users.total }} data
                </div>
                <div class="flex gap-1">
                    <Link
                        v-for="(link, i) in users.links"
                        :key="i"
                        :href="link.url || '#'"
                        :class="[
                            'min-w-[36px] h-9 px-3 flex items-center justify-center text-sm rounded-lg transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50',
                            link.active
                                ? 'bg-emerald-500 text-white font-semibold shadow-sm'
                                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-zinc-800',
                            !link.url && 'opacity-50 cursor-not-allowed hover:bg-transparent'
                        ]"
                        :aria-current="link.active ? 'page' : undefined"
                        :aria-disabled="!link.url"
                        preserve-scroll
                        preserve-state
                    ><span v-html="link.label" /></Link>
                </div>
            </nav>
        </Motion>
    </div>
</template>

