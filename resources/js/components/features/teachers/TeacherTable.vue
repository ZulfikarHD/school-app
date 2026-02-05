<script setup lang="ts">
/**
 * TeacherTable Component - Komponen tabel untuk menampilkan daftar guru
 * dengan fitur search, filter, pagination, dan responsive design
 */
import { ref, computed, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import {
    Search,
    Edit,
    Power,
    ChevronLeft,
    ChevronRight,
    Filter,
    X,
    Users
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import Badge from '@/components/ui/Badge.vue';

interface Teacher {
    id: number;
    nip: string | null;
    nik: string;
    nama_lengkap: string;
    jenis_kelamin: 'L' | 'P';
    email: string;
    no_hp: string | null;
    foto: string | null;
    foto_url: string;
    status_kepegawaian: string;
    is_active: boolean;
    subjects: Array<{ id: number; kode_mapel: string; nama_mapel: string; is_primary: boolean }>;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Pagination {
    data: Teacher[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    per_page: number;
}

interface StatusOption {
    value: string;
    label: string;
}

interface Props {
    teachers: Pagination;
    filters?: {
        search?: string;
        status_kepegawaian?: string;
        is_active?: string;
    };
    loading?: boolean;
    statusOptions?: StatusOption[];
    filterUrl?: string;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    filters: () => ({}),
    statusOptions: () => [],
    filterUrl: ''
});

const emit = defineEmits(['edit', 'toggle-status']);
const haptics = useHaptics();

// Local state for filters
const search = ref(props.filters.search || '');
const statusKepegawaian = ref(props.filters.status_kepegawaian || '');
const isActive = ref(props.filters.is_active || '');
const showFilters = ref(false);

// Debounce search
let timeout: ReturnType<typeof setTimeout> | null = null;

const handleSearch = () => {
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

onUnmounted(() => {
    if (timeout) clearTimeout(timeout);
});

const applyFilters = () => {
    if (!props.filterUrl) {
        console.warn('TeacherTable: filterUrl prop is required for filtering');
        return;
    }

    haptics.selection();
    router.get(
        props.filterUrl,
        {
            search: search.value || undefined,
            status_kepegawaian: statusKepegawaian.value || undefined,
            is_active: isActive.value || undefined
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
};

const resetFilters = () => {
    haptics.light();
    search.value = '';
    statusKepegawaian.value = '';
    isActive.value = '';
    applyFilters();
};

const hasActiveFilters = computed(() => {
    return search.value || statusKepegawaian.value || isActive.value;
});

// Badge variant mapping untuk status kepegawaian
const getStatusBadgeVariant = (status: string): 'success' | 'warning' | 'info' => {
    switch (status) {
        case 'tetap': return 'success';
        case 'honorer': return 'warning';
        case 'kontrak': return 'info';
        default: return 'info';
    }
};

const getStatusLabel = (status: string): string => {
    switch (status) {
        case 'tetap': return 'Tetap';
        case 'honorer': return 'Honorer';
        case 'kontrak': return 'Kontrak';
        default: return status;
    }
};

// Get primary subjects as comma-separated string
const getPrimarySubjects = (teacher: Teacher): string => {
    const primarySubjects = teacher.subjects?.filter(s => s.is_primary) || [];
    if (primarySubjects.length === 0) return '-';
    return primarySubjects.map(s => s.nama_mapel).join(', ');
};
</script>

<template>
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
        <!-- Search and Filters Header -->
        <div class="p-4 border-b border-slate-200 dark:border-zinc-800">
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                    <input
                        v-model="search"
                        @input="handleSearch"
                        type="text"
                        placeholder="Cari nama, NIP, atau NIK..."
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    />
                </div>

                <!-- Filter Toggle Button (Mobile) -->
                <button
                    @click="showFilters = !showFilters; haptics.light()"
                    class="sm:hidden flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors"
                >
                    <Filter class="w-5 h-5" />
                    <span>Filter</span>
                    <span v-if="hasActiveFilters" class="w-2 h-2 bg-blue-500 rounded-full"></span>
                </button>

                <!-- Desktop Filters -->
                <div class="hidden sm:flex items-center gap-2">
                    <select
                        v-model="statusKepegawaian"
                        @change="applyFilters"
                        class="px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    >
                        <option value="">Semua Status</option>
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>

                    <select
                        v-model="isActive"
                        @change="applyFilters"
                        class="px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    >
                        <option value="">Semua</option>
                        <option value="true">Aktif</option>
                        <option value="false">Nonaktif</option>
                    </select>

                    <button
                        v-if="hasActiveFilters"
                        @click="resetFilters"
                        class="flex items-center gap-1 px-3 py-2.5 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors"
                    >
                        <X class="w-4 h-4" />
                        <span class="text-sm">Reset</span>
                    </button>
                </div>
            </div>

            <!-- Mobile Filters (Collapsible) -->
            <div v-if="showFilters" class="sm:hidden mt-3 pt-3 border-t border-slate-200 dark:border-zinc-700 space-y-3">
                <select
                    v-model="statusKepegawaian"
                    @change="applyFilters"
                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100"
                >
                    <option value="">Semua Status Kepegawaian</option>
                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>

                <select
                    v-model="isActive"
                    @change="applyFilters"
                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100"
                >
                    <option value="">Semua Status Aktif</option>
                    <option value="true">Aktif</option>
                    <option value="false">Nonaktif</option>
                </select>

                <button
                    v-if="hasActiveFilters"
                    @click="resetFilters"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors"
                >
                    <X class="w-4 h-4" />
                    <span>Reset Filter</span>
                </button>
            </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-zinc-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Guru
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            NIP
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Mata Pelajaran
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            No. HP
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    <tr
                        v-for="teacher in teachers.data"
                        :key="teacher.id"
                        class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                        :class="{ 'opacity-60': !teacher.is_active }"
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <img
                                    :src="teacher.foto_url"
                                    :alt="teacher.nama_lengkap"
                                    class="w-10 h-10 rounded-full object-cover border-2 border-slate-200 dark:border-zinc-700"
                                />
                                <div>
                                    <div class="font-medium text-slate-900 dark:text-slate-100">
                                        {{ teacher.nama_lengkap }}
                                    </div>
                                    <div class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ teacher.email }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300">
                            {{ teacher.nip || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col gap-1">
                                <Badge :variant="getStatusBadgeVariant(teacher.status_kepegawaian)">
                                    {{ getStatusLabel(teacher.status_kepegawaian) }}
                                </Badge>
                                <Badge v-if="!teacher.is_active" variant="error" size="sm">
                                    Nonaktif
                                </Badge>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-700 dark:text-slate-300 max-w-[200px] truncate">
                            {{ getPrimarySubjects(teacher) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300">
                            {{ teacher.no_hp || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Motion :whileTap="{ scale: 0.95 }">
                                    <button
                                        @click="emit('edit', teacher); haptics.light()"
                                        class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                        title="Edit"
                                    >
                                        <Edit class="w-4 h-4" />
                                    </button>
                                </Motion>
                                <Motion :whileTap="{ scale: 0.95 }">
                                    <button
                                        @click="emit('toggle-status', teacher); haptics.light()"
                                        class="p-2 rounded-lg transition-colors"
                                        :class="teacher.is_active
                                            ? 'text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20'
                                            : 'text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20'"
                                        :title="teacher.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                    >
                                        <Power class="w-4 h-4" />
                                    </button>
                                </Motion>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-slate-100 dark:divide-zinc-800">
            <div
                v-for="teacher in teachers.data"
                :key="teacher.id"
                class="p-4 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                :class="{ 'opacity-60': !teacher.is_active }"
            >
                <div class="flex items-start gap-3">
                    <img
                        :src="teacher.foto_url"
                        :alt="teacher.nama_lengkap"
                        class="w-12 h-12 rounded-full object-cover border-2 border-slate-200 dark:border-zinc-700 shrink-0"
                    />
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-medium text-slate-900 dark:text-slate-100 truncate">
                                    {{ teacher.nama_lengkap }}
                                </h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 truncate">
                                    {{ teacher.nip || 'Tanpa NIP' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-1">
                                <Badge :variant="getStatusBadgeVariant(teacher.status_kepegawaian)" size="sm">
                                    {{ getStatusLabel(teacher.status_kepegawaian) }}
                                </Badge>
                            </div>
                        </div>
                        <div class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                            <p class="truncate">{{ getPrimarySubjects(teacher) }}</p>
                            <p v-if="teacher.no_hp" class="mt-1">{{ teacher.no_hp }}</p>
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            <Motion :whileTap="{ scale: 0.95 }">
                                <button
                                    @click="emit('edit', teacher); haptics.light()"
                                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-blue-600 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors"
                                >
                                    <Edit class="w-4 h-4" />
                                    <span>Edit</span>
                                </button>
                            </Motion>
                            <Motion :whileTap="{ scale: 0.95 }">
                                <button
                                    @click="emit('toggle-status', teacher); haptics.light()"
                                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg transition-colors"
                                    :class="teacher.is_active
                                        ? 'text-amber-600 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30'
                                        : 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/30'"
                                >
                                    <Power class="w-4 h-4" />
                                    <span>{{ teacher.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                                </button>
                            </Motion>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-if="teachers.data.length === 0"
            class="p-12 text-center"
        >
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                <Users class="w-8 h-8 text-slate-400" />
            </div>
            <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100 mb-1">
                Tidak ada data guru
            </h3>
            <p class="text-slate-500 dark:text-slate-400 mb-4">
                {{ hasActiveFilters ? 'Tidak ada guru yang sesuai dengan filter' : 'Belum ada data guru yang terdaftar' }}
            </p>
            <button
                v-if="hasActiveFilters"
                @click="resetFilters"
                class="inline-flex items-center gap-2 px-4 py-2 text-blue-600 hover:text-blue-700 dark:hover:text-blue-400 transition-colors"
            >
                <X class="w-4 h-4" />
                <span>Reset Filter</span>
            </button>
        </div>

        <!-- Pagination -->
        <div
            v-if="teachers.last_page > 1"
            class="px-4 py-3 border-t border-slate-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-3"
        >
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Menampilkan {{ teachers.from }} - {{ teachers.to }} dari {{ teachers.total }} guru
            </p>
            <div class="flex items-center gap-1">
                <!-- Previous Button -->
                <Link
                    v-if="teachers.links[0].url"
                    :href="teachers.links[0].url"
                    @click="haptics.light()"
                    class="p-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                    preserve-scroll
                >
                    <ChevronLeft class="w-5 h-5" />
                </Link>
                <span v-else class="p-2 text-slate-300 dark:text-zinc-600">
                    <ChevronLeft class="w-5 h-5" />
                </span>

                <!-- Page Numbers (Desktop) -->
                <div class="hidden sm:flex items-center gap-1">
                    <template v-for="link in teachers.links.slice(1, -1)" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            @click="haptics.light()"
                            class="min-w-[36px] h-9 flex items-center justify-center rounded-lg text-sm font-medium transition-colors"
                            :class="link.active
                                ? 'bg-blue-500 text-white'
                                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800'"
                            preserve-scroll
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="min-w-[36px] h-9 flex items-center justify-center text-sm text-slate-400 dark:text-zinc-600"
                            v-html="link.label"
                        />
                    </template>
                </div>

                <!-- Mobile Page Indicator -->
                <div class="sm:hidden px-3 py-1.5 text-sm text-slate-600 dark:text-slate-400">
                    {{ teachers.current_page }} / {{ teachers.last_page }}
                </div>

                <!-- Next Button -->
                <Link
                    v-if="teachers.links[teachers.links.length - 1].url"
                    :href="teachers.links[teachers.links.length - 1].url"
                    @click="haptics.light()"
                    class="p-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                    preserve-scroll
                >
                    <ChevronRight class="w-5 h-5" />
                </Link>
                <span v-else class="p-2 text-slate-300 dark:text-zinc-600">
                    <ChevronRight class="w-5 h-5" />
                </span>
            </div>
        </div>
    </div>
</template>
