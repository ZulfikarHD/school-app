<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import {
    Search,
    Edit,
    Trash2,
    RefreshCw,
    Power,
    Shield
} from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';
import { index as usersIndex } from '@/routes/admin/users';

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
 * Role badge classes menggunakan design system colors
 * untuk visual consistency di seluruh aplikasi
 */
const getRoleBadgeClass = (role: string) => {
    const classes: Record<string, string> = {
        SUPERADMIN: 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
        ADMIN: 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400',
        PRINCIPAL: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
        TEACHER: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        PARENT: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        STUDENT: 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-slate-400',
    };
    return classes[role] || 'bg-slate-100 text-slate-600';
};

/**
 * Status badge classes untuk indicator aktif/nonaktif user
 */
const getStatusBadgeClass = (status: string) => {
    return status === 'ACTIVE'
        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
        : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
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

const onToggleStatus = (user: User) => {
    haptics.light();
    emit('toggle-status', user);
};

</script>

<template>
    <div class="space-y-4">
        <!-- Filters & Search dengan accessible labels -->
        <div class="flex flex-col sm:flex-row gap-4 justify-between bg-white dark:bg-zinc-900 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-zinc-800">
            <div class="relative w-full sm:w-64">
                <label for="user-search" class="sr-only">Cari user</label>
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" aria-hidden="true" />
                <input
                    id="user-search"
                    v-model="search"
                    @input="handleSearch"
                    type="text"
                    placeholder="Cari user..."
                    class="w-full pl-9 pr-4 py-2.5 min-h-[44px] text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900 transition-all"
                >
            </div>

            <div class="flex gap-2">
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
            </div>
        </div>

        <!-- Desktop Table dengan proper accessibility -->
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
                        <tr v-if="loading" class="animate-pulse">
                            <td colspan="5" class="px-6 py-4 text-center text-slate-500">Memuat data...</td>
                        </tr>
                        <tr v-else-if="users.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center gap-2">
                                    <Shield class="w-8 h-8 text-slate-300 dark:text-slate-600" aria-hidden="true" />
                                    <p>Belum ada user ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-else v-for="user in users.data" :key="user.id" class="group hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-medium text-slate-900 dark:text-slate-100">{{ user.name }}</span>
                                    <span class="text-xs text-slate-500">{{ user.email }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ user.username }}</td>
                            <td class="px-6 py-4">
                                <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium border border-transparent', getRoleBadgeClass(user.role)]">
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="['px-2.5 py-0.5 rounded-full text-xs font-medium border border-transparent', getStatusBadgeClass(user.status)]"
                                    role="status"
                                >
                                    {{ getStatusLabel(user.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity" role="group" aria-label="Aksi user">
                                    <button
                                        @click="onEdit(user)"
                                        class="w-9 h-9 flex items-center justify-center text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-900/20 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500/50"
                                        :aria-label="`Edit ${user.name}`"
                                    >
                                        <Edit class="w-4 h-4" aria-hidden="true" />
                                    </button>
                                    <button
                                        @click="onResetPassword(user)"
                                        class="w-9 h-9 flex items-center justify-center text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/50"
                                        :aria-label="`Reset password ${user.name}`"
                                    >
                                        <RefreshCw class="w-4 h-4" aria-hidden="true" />
                                    </button>
                                    <button
                                        @click="onToggleStatus(user)"
                                        class="w-9 h-9 flex items-center justify-center text-slate-600 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500/50"
                                        :aria-label="`${user.status === 'ACTIVE' ? 'Nonaktifkan' : 'Aktifkan'} ${user.name}`"
                                    >
                                        <Power class="w-4 h-4" aria-hidden="true" />
                                    </button>
                                    <button
                                        @click="onDelete(user)"
                                        class="w-9 h-9 flex items-center justify-center text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50"
                                        :aria-label="`Hapus ${user.name}`"
                                    >
                                        <Trash2 class="w-4 h-4" aria-hidden="true" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards dengan proper accessibility -->
        <div class="md:hidden space-y-3">
            <div v-if="loading" class="p-4 text-center text-slate-500 animate-pulse">Memuat data...</div>
            <div v-else-if="users.data.length === 0" class="p-8 text-center text-slate-500 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800">
                <Shield class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2" aria-hidden="true" />
                <p>Belum ada user ditemukan</p>
            </div>
            <article v-else v-for="user in users.data" :key="user.id" class="bg-white dark:bg-zinc-900 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-zinc-800 active:scale-[0.99] transition-transform">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-medium text-slate-900 dark:text-slate-100">{{ user.name }}</h3>
                        <p class="text-sm text-slate-500">{{ user.email }}</p>
                    </div>
                    <span
                        :class="['px-2 py-0.5 rounded-full text-[11px] font-medium border border-transparent', getStatusBadgeClass(user.status)]"
                        role="status"
                    >
                        {{ getStatusLabel(user.status) }}
                    </span>
                </div>

                <div class="flex items-center gap-2 mb-4 text-sm text-slate-600 dark:text-slate-400">
                    <span :class="['px-2 py-0.5 rounded-md text-[11px] font-medium border border-transparent', getRoleBadgeClass(user.role)]">
                        {{ user.role }}
                    </span>
                    <span class="text-xs text-slate-400">• {{ user.username }}</span>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-zinc-800" role="group" :aria-label="`Aksi untuk ${user.name}`">
                    <button
                        @click="onResetPassword(user)"
                        class="w-10 h-10 flex items-center justify-center text-amber-600 bg-amber-50 dark:bg-amber-900/20 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/50"
                        :aria-label="`Reset password ${user.name}`"
                    >
                        <RefreshCw class="w-4 h-4" aria-hidden="true" />
                    </button>
                    <button
                        @click="onToggleStatus(user)"
                        class="w-10 h-10 flex items-center justify-center text-slate-600 bg-slate-100 dark:bg-zinc-800 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500/50"
                        :aria-label="`${user.status === 'ACTIVE' ? 'Nonaktifkan' : 'Aktifkan'} ${user.name}`"
                    >
                        <Power class="w-4 h-4" aria-hidden="true" />
                    </button>
                    <button
                        @click="onEdit(user)"
                        class="w-10 h-10 flex items-center justify-center text-sky-600 bg-sky-50 dark:bg-sky-900/20 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500/50"
                        :aria-label="`Edit ${user.name}`"
                    >
                        <Edit class="w-4 h-4" aria-hidden="true" />
                    </button>
                    <button
                        @click="onDelete(user)"
                        class="w-10 h-10 flex items-center justify-center text-red-600 bg-red-50 dark:bg-red-900/20 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50"
                        :aria-label="`Hapus ${user.name}`"
                    >
                        <Trash2 class="w-4 h-4" aria-hidden="true" />
                    </button>
                </div>
            </article>
        </div>

        <!-- Pagination dengan accessible navigation -->
        <nav v-if="users.total > users.per_page" class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-zinc-800" aria-label="Navigasi halaman user">
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
                    v-html="link.label"
                    preserve-scroll
                    preserve-state
                />
            </div>
        </nav>
    </div>
</template>

