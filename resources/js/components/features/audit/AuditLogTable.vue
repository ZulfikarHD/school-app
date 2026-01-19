<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import {
    Search,
    ChevronDown,
    ChevronUp,
    Activity,
    CheckCircle,
    XCircle
} from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';
import { index as adminAuditLogsIndex } from '@/routes/admin/audit-logs';
import Badge from '@/components/ui/Badge.vue';

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
    } catch {
        return data;
    }
};

/**
 * Status badge variant untuk audit log status
 */
const getStatusBadgeVariant = (status: string): 'success' | 'error' => {
    return status === 'failed' ? 'error' : 'success';
};
</script>

<template>
    <div class="space-y-4">
        <!-- Filters dengan accessible labels -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-zinc-800 grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="relative md:col-span-1">
                <label for="audit-search" class="sr-only">Cari IP atau Deskripsi</label>
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" aria-hidden="true" />
                <input
                    id="audit-search"
                    v-model="search"
                    @input="handleSearch"
                    type="text"
                    placeholder="Cari IP atau Deskripsi..."
                    class="w-full pl-9 pr-4 py-2.5 min-h-[44px] text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900 transition-all"
                >
            </div>

            <!-- User Filter -->
            <div>
                <label for="audit-user-filter" class="sr-only">Filter User</label>
                <select
                    id="audit-user-filter"
                    v-model="userId"
                    @change="applyFilters"
                    class="w-full text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2.5 px-3 min-h-[44px] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                >
                    <option value="">Semua User</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">
                        {{ user.name }}
                    </option>
                </select>
            </div>

            <!-- Action Filter -->
            <div>
                <label for="audit-action-filter" class="sr-only">Filter Aksi</label>
                <select
                    id="audit-action-filter"
                    v-model="action"
                    @change="applyFilters"
                    class="w-full text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2.5 px-3 min-h-[44px] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
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
            </div>

            <!-- Status Filter -->
            <div>
                <label for="audit-status-filter" class="sr-only">Filter Status</label>
                <select
                    id="audit-status-filter"
                    v-model="status"
                    @change="applyFilters"
                    class="w-full text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2.5 px-3 min-h-[44px] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                >
                    <option value="">Semua Status</option>
                    <option value="success">Berhasil</option>
                    <option value="failed">Gagal</option>
                </select>
            </div>
        </div>

        <!-- Table dengan proper accessibility -->
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left" role="table" aria-label="Tabel log aktivitas">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-100 dark:border-zinc-800">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 font-semibold tracking-wide">Waktu</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold tracking-wide">User</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold tracking-wide">Aksi</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold tracking-wide">IP Address</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold tracking-wide">Status</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold tracking-wide w-10">
                                <span class="sr-only">Expand</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr v-if="loading" class="animate-pulse">
                            <td colspan="6" class="px-6 py-4 text-center text-slate-500">Memuat log...</td>
                        </tr>
                        <tr v-else-if="logs.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center gap-2">
                                    <Activity class="w-8 h-8 text-slate-300 dark:text-slate-600" aria-hidden="true" />
                                    <p>Belum ada aktivitas tercatat</p>
                                </div>
                            </td>
                        </tr>
                        <template v-else v-for="log in logs.data" :key="log.id">
                            <tr
                                class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors cursor-pointer focus:outline-none focus-visible:bg-slate-50 dark:focus-visible:bg-zinc-800/50"
                                tabindex="0"
                                role="button"
                                :aria-expanded="expandedRows.has(log.id)"
                                :aria-label="`Log aktivitas ${log.action || log.description} oleh ${log.causer?.name || 'System'}, klik untuk ${expandedRows.has(log.id) ? 'tutup' : 'lihat'} detail`"
                                @click="toggleExpand(log.id)"
                                @keydown.enter="toggleExpand(log.id)"
                                @keydown.space.prevent="toggleExpand(log.id)"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                    {{ log.created_at }}
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100">
                                    <div class="flex flex-col">
                                        <span>{{ log.causer?.name || 'System' }}</span>
                                        <span class="text-xs text-slate-500">{{ log.causer?.email || '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge variant="default" size="sm">
                                        {{ log.action || log.description }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400 font-mono text-xs">
                                    {{ log.ip_address }}
                                </td>
                                <td class="px-6 py-4">
                                    <Badge
                                        :variant="getStatusBadgeVariant(log.status || 'success')"
                                        size="sm"
                                        dot
                                    >
                                        {{ log.status === 'failed' ? 'Gagal' : 'Berhasil' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <component :is="expandedRows.has(log.id) ? ChevronUp : ChevronDown" class="w-4 h-4 text-slate-400" aria-hidden="true" />
                                </td>
                            </tr>

                            <!-- Expanded Detail Row -->
                            <tr v-if="expandedRows.has(log.id)" class="bg-slate-50 dark:bg-zinc-800/30">
                                <td colspan="6" class="px-6 py-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-mono">
                                        <div v-if="log.old_values || log.properties?.old">
                                            <h4 class="font-bold text-slate-500 mb-2">Old Values</h4>
                                            <pre class="bg-white dark:bg-zinc-900 p-3 rounded-xl border border-slate-200 dark:border-zinc-700 overflow-x-auto text-slate-700 dark:text-slate-300">{{ formatJson(log.old_values || log.properties?.old) }}</pre>
                                        </div>
                                        <div v-if="log.new_values || log.properties?.attributes || log.properties?.new">
                                            <h4 class="font-bold text-slate-500 mb-2">New Values / Details</h4>
                                            <pre class="bg-white dark:bg-zinc-900 p-3 rounded-xl border border-slate-200 dark:border-zinc-700 overflow-x-auto text-slate-700 dark:text-slate-300">{{ formatJson(log.new_values || log.properties?.attributes || log.properties?.new) }}</pre>
                                        </div>
                                        <div class="md:col-span-2">
                                            <h4 class="font-bold text-slate-500 mb-2">Metadata</h4>
                                            <div class="bg-white dark:bg-zinc-900 p-3 rounded-xl border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-slate-300">
                                                <p><span class="text-slate-500">User Agent:</span> {{ log.user_agent }}</p>
                                                <p><span class="text-slate-500">Event:</span> {{ log.event }}</p>
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

        <!-- Pagination dengan accessible navigation -->
        <nav v-if="logs.total > logs.per_page" class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-zinc-800" aria-label="Navigasi halaman log">
            <div class="text-sm text-slate-500">
                Menampilkan {{ logs.from }} - {{ logs.to }} dari {{ logs.total }} log
            </div>
            <div class="flex gap-1">
                <Link
                    v-for="(link, i) in logs.links"
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
    </div>
</template>

