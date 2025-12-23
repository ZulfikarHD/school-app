<script setup lang="ts">
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import {
    Search,
    Filter,
    Edit,
    Trash2,
    RefreshCw,
    Power,
    MoreVertical,
    ChevronLeft,
    ChevronRight,
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

// Role badges
const getRoleBadgeClass = (role: string) => {
    const classes: Record<string, string> = {
        SUPERADMIN: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        ADMIN: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        PRINCIPAL: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
        TEACHER: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        PARENT: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
        STUDENT: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
    };
    return classes[role] || 'bg-gray-100 text-gray-600';
};

// Status badges
const getStatusBadgeClass = (status: string) => {
    return status === 'ACTIVE'
        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
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
        <!-- Filters & Search -->
        <div class="flex flex-col sm:flex-row gap-4 justify-between bg-white dark:bg-zinc-900 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800">
            <div class="relative w-full sm:w-64">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                    v-model="search"
                    @input="handleSearch"
                    type="text"
                    placeholder="Cari user..."
                    class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                >
            </div>

            <div class="flex gap-2">
                <select
                    v-model="role"
                    @change="applyFilters"
                    class="text-sm rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2 px-3 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                >
                    <option value="">Semua Role</option>
                    <option value="SUPERADMIN">Super Admin</option>
                    <option value="ADMIN">Admin TU</option>
                    <option value="PRINCIPAL">Kepala Sekolah</option>
                    <option value="TEACHER">Guru</option>
                    <option value="PARENT">Orang Tua</option>
                </select>

                <select
                    v-model="status"
                    @change="applyFilters"
                    class="text-sm rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2 px-3 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                >
                    <option value="">Semua Status</option>
                    <option value="ACTIVE">Aktif</option>
                    <option value="INACTIVE">Nonaktif</option>
                </select>
            </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-zinc-800/50 border-b border-gray-100 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-3 font-medium">User</th>
                            <th class="px-6 py-3 font-medium">Username</th>
                            <th class="px-6 py-3 font-medium">Role</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                        <tr v-if="loading" class="animate-pulse">
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Memuat data...</td>
                        </tr>
                        <tr v-else-if="users.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <Shield class="w-8 h-8 text-gray-300" />
                                    <p>Belum ada user ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-else v-for="user in users.data" :key="user.id" class="group hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</span>
                                    <span class="text-xs text-gray-500">{{ user.email }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ user.username }}</td>
                            <td class="px-6 py-4">
                                <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium border border-transparent', getRoleBadgeClass(user.role)]">
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium border border-transparent', getStatusBadgeClass(user.status)]">
                                    {{ getStatusLabel(user.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="onEdit(user)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                        <Edit class="w-4 h-4" />
                                    </button>
                                    <button @click="onResetPassword(user)" class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Reset Password">
                                        <RefreshCw class="w-4 h-4" />
                                    </button>
                                    <button @click="onToggleStatus(user)" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Toggle Status">
                                        <Power class="w-4 h-4" />
                                    </button>
                                    <button @click="onDelete(user)" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-3">
            <div v-if="loading" class="p-4 text-center text-gray-500 animate-pulse">Memuat data...</div>
            <div v-else-if="users.data.length === 0" class="p-8 text-center text-gray-500 bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800">
                <Shield class="w-8 h-8 mx-auto text-gray-300 mb-2" />
                <p>Belum ada user ditemukan</p>
            </div>
            <div v-else v-for="user in users.data" :key="user.id" class="bg-white dark:bg-zinc-900 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 active:scale-[0.99] transition-transform">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</h3>
                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                    </div>
                    <span :class="['px-2 py-0.5 rounded-full text-[10px] font-medium border border-transparent', getStatusBadgeClass(user.status)]">
                        {{ getStatusLabel(user.status) }}
                    </span>
                </div>

                <div class="flex items-center gap-2 mb-4 text-sm text-gray-600 dark:text-gray-400">
                    <span :class="['px-2 py-0.5 rounded-md text-[10px] font-medium border border-transparent', getRoleBadgeClass(user.role)]">
                        {{ user.role }}
                    </span>
                    <span class="text-xs text-gray-400">• {{ user.username }}</span>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-50 dark:border-zinc-800">
                     <button @click="onResetPassword(user)" class="p-2 text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg" title="Reset Password">
                        <RefreshCw class="w-4 h-4" />
                    </button>
                    <button @click="onToggleStatus(user)" class="p-2 text-gray-600 bg-gray-100 dark:bg-gray-800 rounded-lg" title="Toggle Status">
                        <Power class="w-4 h-4" />
                    </button>
                    <button @click="onEdit(user)" class="p-2 text-blue-600 bg-blue-50 dark:bg-blue-900/20 rounded-lg" title="Edit">
                        <Edit class="w-4 h-4" />
                    </button>
                     <button @click="onDelete(user)" class="p-2 text-red-600 bg-red-50 dark:bg-red-900/20 rounded-lg" title="Hapus">
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="users.total > users.per_page" class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-zinc-800">
            <div class="text-sm text-gray-500">
                Menampilkan {{ users.from }} - {{ users.to }} dari {{ users.total }} data
            </div>
            <div class="flex gap-1">
                <Link
                    v-for="(link, i) in users.links"
                    :key="i"
                    :href="link.url || '#'"
                    :class="[
                        'px-3 py-1 text-sm rounded-md transition-colors',
                        link.active
                            ? 'bg-blue-600 text-white font-medium'
                            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-zinc-800',
                        !link.url && 'opacity-50 cursor-not-allowed hover:bg-transparent'
                    ]"
                    v-html="link.label"
                    preserve-scroll
                    preserve-state
                />
            </div>
        </div>
    </div>
</template>

