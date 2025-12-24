<script setup lang="ts">
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import {
    Search,
    Calendar,
    Filter,
    ChevronDown,
    ChevronUp,
    Activity,
    CheckCircle,
    XCircle,
    Info
} from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';
import { index as adminAuditLogsIndex } from '@/routes/admin/audit-logs';

interface ActivityLog {
    id: number;
    description: string;
    subject_type: string;
    event: string; // created, updated, deleted, login, etc.
    causer: {
        id: number;
        name: string;
        email: string;
    } | null;
    properties: {
        old?: any;
        attributes?: any;
        ip?: string;
        user_agent?: string;
        [key: string]: any;
    };
    created_at: string;
    status?: 'success' | 'failed'; // Assuming we added this column or infer it
    action?: string; // The specific action name if different from event
    ip_address?: string;
    user_agent?: string;
    // For our specific app pattern from summary:
    // 'action', 'ip_address', 'user_agent', 'old_values', 'new_values', 'status'
    old_values?: any;
    new_values?: any;
}

interface Pagination {
    data: ActivityLog[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    from: number;
    to: number;
    total: number;
    per_page: number;
}

interface Props {
    logs: Pagination;
    filters?: {
        search?: string;
        action?: string;
        user_id?: string;
        status?: string;
        date_range?: string;
    };
    loading?: boolean;
    users?: Array<{ id: number; name: string }>;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    filters: () => ({}),
    users: () => [],
});

const haptics = useHaptics();
const expandedRows = ref<Set<number>>(new Set());

// Filter state
const search = ref(props.filters.search || '');
const action = ref(props.filters.action || '');
const userId = ref(props.filters.user_id || '');
const status = ref(props.filters.status || '');
// Date range could be implemented with a library, simple version for now

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
        adminAuditLogsIndex().url,
        {
            search: search.value,
            action: action.value,
            user_id: userId.value,
            status: status.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
};

const toggleExpand = (id: number) => {
    haptics.light();
    if (expandedRows.value.has(id)) {
        expandedRows.value.delete(id);
    } else {
        expandedRows.value.add(id);
    }
};

const formatJson = (data: any) => {
    try {
        return JSON.stringify(data, null, 2);
    } catch (e) {
        return data;
    }
};

const getStatusColor = (status: string) => {
    return status === 'failed'
        ? 'text-red-600 bg-red-50 dark:text-red-400 dark:bg-red-900/20'
        : 'text-green-600 bg-green-50 dark:text-green-400 dark:bg-green-900/20';
};
</script>

<template>
    <div class="space-y-4">
        <!-- Filters -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="relative md:col-span-1">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                    v-model="search"
                    @input="handleSearch"
                    type="text"
                    placeholder="Cari IP atau Deskripsi..."
                    class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                >
            </div>

            <!-- User Filter -->
            <select
                v-model="userId"
                @change="applyFilters"
                class="text-sm rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2 px-3 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
            >
                <option value="">Semua User</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }}
                </option>
            </select>

            <!-- Action Filter -->
            <select
                v-model="action"
                @change="applyFilters"
                class="text-sm rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2 px-3 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
            >
                <option value="">Semua Aksi</option>
                <option value="login">Login</option>
                <option value="logout">Logout</option>
                <option value="create_user">Create User</option>
                <option value="update_user">Update User</option>
                <option value="delete_user">Delete User</option>
                <option value="failed_login">Failed Login</option>
                <option value="password_change">Change Password</option>
                <option value="password_reset">Reset Password</option>
            </select>

            <!-- Status Filter -->
            <select
                v-model="status"
                @change="applyFilters"
                class="text-sm rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2 px-3 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
            >
                <option value="">Semua Status</option>
                <option value="success">Berhasil</option>
                <option value="failed">Gagal</option>
            </select>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-zinc-800/50 border-b border-gray-100 dark:border-zinc-800">
                        <tr>
                            <th class="px-6 py-3 font-medium">Waktu</th>
                            <th class="px-6 py-3 font-medium">User</th>
                            <th class="px-6 py-3 font-medium">Aksi</th>
                            <th class="px-6 py-3 font-medium">IP Address</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                        <tr v-if="loading" class="animate-pulse">
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Memuat log...</td>
                        </tr>
                        <tr v-else-if="logs.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <Activity class="w-8 h-8 text-gray-300" />
                                    <p>Belum ada aktivitas tercatat</p>
                                </div>
                            </td>
                        </tr>
                        <template v-else v-for="log in logs.data" :key="log.id">
                            <tr
                                class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors cursor-pointer"
                                @click="toggleExpand(log.id)"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                    {{ log.created_at }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                    <div class="flex flex-col">
                                        <span>{{ log.causer?.name || 'System' }}</span>
                                        <span class="text-xs text-gray-500">{{ log.causer?.email || '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                        {{ log.action || log.description }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400 font-mono text-xs">
                                    {{ log.ip_address }}
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(log.status || 'success')]">
                                        <component :is="log.status === 'failed' ? XCircle : CheckCircle" class="w-3 h-3" />
                                        {{ log.status === 'failed' ? 'Gagal' : 'Berhasil' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <component :is="expandedRows.has(log.id) ? ChevronUp : ChevronDown" class="w-4 h-4 text-gray-400" />
                                </td>
                            </tr>

                            <!-- Expanded Detail Row -->
                            <tr v-if="expandedRows.has(log.id)" class="bg-gray-50 dark:bg-zinc-800/30">
                                <td colspan="6" class="px-6 py-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-mono">
                                        <div v-if="log.old_values || log.properties?.old">
                                            <h4 class="font-bold text-gray-500 mb-2">Old Values</h4>
                                            <pre class="bg-white dark:bg-zinc-900 p-3 rounded-lg border border-gray-200 dark:border-zinc-700 overflow-x-auto">{{ formatJson(log.old_values || log.properties?.old) }}</pre>
                                        </div>
                                        <div v-if="log.new_values || log.properties?.attributes || log.properties?.new">
                                            <h4 class="font-bold text-gray-500 mb-2">New Values / Details</h4>
                                            <pre class="bg-white dark:bg-zinc-900 p-3 rounded-lg border border-gray-200 dark:border-zinc-700 overflow-x-auto">{{ formatJson(log.new_values || log.properties?.attributes || log.properties?.new) }}</pre>
                                        </div>
                                        <div class="md:col-span-2">
                                            <h4 class="font-bold text-gray-500 mb-2">Metadata</h4>
                                            <div class="bg-white dark:bg-zinc-900 p-3 rounded-lg border border-gray-200 dark:border-zinc-700">
                                                <p><span class="text-gray-500">User Agent:</span> {{ log.user_agent }}</p>
                                                <p><span class="text-gray-500">Event:</span> {{ log.event }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="logs.total > logs.per_page" class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-zinc-800">
            <div class="text-sm text-gray-500">
                Menampilkan {{ logs.from }} - {{ logs.to }} dari {{ logs.total }} log
            </div>
            <div class="flex gap-1">
                <Link
                    v-for="(link, i) in logs.links"
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

