<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import {
    Search,
    Edit,
    Trash2,
    Eye,
    RefreshCw,
    Shield,
    ChevronLeft,
    ChevronRight
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { index as studentsIndex } from '@/routes/admin/students';
import type { Student } from '@/types/student';

// Define pagination interfaces locally since they are generic Inertia types
interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Pagination {
    data: Student[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    per_page: number;
}

interface Props {
    students: Pagination;
    filters?: {
        search?: string;
        kelas_id?: number | string;
        status?: string;
        tahun_ajaran?: string;
        jenis_kelamin?: string;
    };
    loading?: boolean;
    // Options for filters
    classes?: Array<{ id: number; nama: string }>;
    academicYears?: string[];
    // Read-only mode (hide edit/delete actions)
    readOnly?: boolean;
    // Hide selection checkboxes
    hideSelection?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    filters: () => ({}),
    classes: () => [],
    academicYears: () => [],
    readOnly: false,
    hideSelection: false
});

const emit = defineEmits(['edit', 'delete', 'view', 'update-status', 'selection-change']);
const haptics = useHaptics();

// Local state for filters
const search = ref(props.filters.search || '');
const kelasId = ref(props.filters.kelas_id || '');
const status = ref(props.filters.status || '');
const tahunAjaran = ref(props.filters.tahun_ajaran || '');
const jenisKelamin = ref(props.filters.jenis_kelamin || '');

// Selection state
const selectedIds = ref<number[]>([]);

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
    // Reset selection when filtering
    selectedIds.value = [];
    emit('selection-change', []);

    router.get(
        studentsIndex().url,
        {
            search: search.value,
            kelas_id: kelasId.value,
            status: status.value,
            tahun_ajaran: tahunAjaran.value,
            jenis_kelamin: jenisKelamin.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
};

// Selection Logic
const allSelected = computed(() => {
    return props.students.data.length > 0 && selectedIds.value.length === props.students.data.length;
});

const toggleSelectAll = () => {
    haptics.light();
    if (allSelected.value) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.students.data.map(s => s.id);
    }
    emit('selection-change', props.students.data.filter(s => selectedIds.value.includes(s.id)));
};

const toggleSelection = (id: number) => {
    haptics.light();
    if (selectedIds.value.includes(id)) {
        selectedIds.value = selectedIds.value.filter(itemId => itemId !== id);
    } else {
        selectedIds.value.push(id);
    }
    emit('selection-change', props.students.data.filter(s => selectedIds.value.includes(s.id)));
};

// Helpers - Status badge dengan emerald sebagai primary color untuk "aktif"
const getStatusBadgeClass = (status: string) => {
    const classes: Record<string, string> = {
        aktif: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
        mutasi: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200 dark:border-amber-800',
        do: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border-red-200 dark:border-red-800',
        lulus: 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400 border-sky-200 dark:border-sky-800',
    };
    return classes[status] || 'bg-slate-100 text-slate-600 border-slate-200';
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        aktif: 'Aktif',
        mutasi: 'Mutasi',
        do: 'Drop Out',
        lulus: 'Lulus',
    };
    return labels[status] || status;
};

// Actions
const onView = (student: Student) => {
    haptics.light();
    emit('view', student);
};

const onEdit = (student: Student) => {
    haptics.light();
    emit('edit', student);
};

const onDelete = (student: Student) => {
    haptics.medium();
    emit('delete', student);
};

const onUpdateStatus = (student: Student) => {
    haptics.light();
    emit('update-status', student);
};

// Expose resetSelection method
defineExpose({
    resetSelection: () => {
        selectedIds.value = [];
        emit('selection-change', []);
    }
});
</script>

<template>
    <div class="space-y-4">
        <!-- Filters & Search -->
        <Motion
            :initial="{ opacity: 0, y: -10 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
        >
            <div class="flex flex-col gap-4 bg-white dark:bg-zinc-900 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-zinc-800">
                <!-- Search Row -->
                <div class="w-full">
                    <div class="relative">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input
                            v-model="search"
                            @input="handleSearch"
                            type="text"
                            placeholder="Cari nama, NIS, atau NISN..."
                            class="w-full pl-10 pr-4 py-3 min-h-[48px] text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50/80 dark:bg-zinc-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900 transition-all"
                        >
                    </div>
                </div>

                <!-- Filters Row - Horizontal scroll on mobile -->
                <div class="relative -mx-4 px-4 md:mx-0 md:px-0">
                    <!-- Scroll shadow indicators -->
                    <div class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-white dark:from-zinc-900 to-transparent pointer-events-none z-10 md:hidden"></div>
                    <div class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-white dark:from-zinc-900 to-transparent pointer-events-none z-10 md:hidden"></div>

                    <div class="flex gap-2 overflow-x-auto pb-2 md:pb-0 md:flex-wrap scrollbar-hide snap-x snap-mandatory">
                        <!-- Status Filter -->
                        <select
                            v-model="status"
                            @change="applyFilters"
                            class="shrink-0 snap-start text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2.5 px-3 min-h-[44px] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                        >
                            <option value="">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="mutasi">Mutasi</option>
                            <option value="do">Drop Out</option>
                            <option value="lulus">Lulus</option>
                        </select>

                        <!-- Gender Filter -->
                        <select
                            v-model="jenisKelamin"
                            @change="applyFilters"
                            class="shrink-0 snap-start text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2.5 px-3 min-h-[44px] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                        >
                            <option value="">Semua Gender</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>

                        <!-- Class Filter (Dynamic) -->
                        <select
                            v-if="classes.length > 0"
                            v-model="kelasId"
                            @change="applyFilters"
                            class="shrink-0 snap-start text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2.5 px-3 min-h-[44px] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                        >
                            <option value="">Semua Kelas</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                {{ cls.nama }}
                            </option>
                        </select>

                        <!-- Academic Year Filter (Dynamic) -->
                        <select
                            v-if="academicYears.length > 0"
                            v-model="tahunAjaran"
                            @change="applyFilters"
                            class="shrink-0 snap-start text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-2.5 px-3 min-h-[44px] focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                        >
                            <option value="">Semua Tahun</option>
                            <option v-for="year in academicYears" :key="year" :value="year">
                                {{ year }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </Motion>

        <!-- Desktop Table -->
        <Motion
            :initial="{ opacity: 0, y: 20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.1 }"
        >
            <div class="hidden md:block bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-100 dark:border-zinc-800">
                            <tr>
                                <th v-if="!hideSelection" class="px-6 py-3.5 w-10">
                                    <div class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :checked="allSelected"
                                            @change="toggleSelectAll"
                                            class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500 dark:border-zinc-600 dark:bg-zinc-700 dark:ring-offset-zinc-800"
                                        >
                                    </div>
                                </th>
                                <th class="px-6 py-3.5 font-semibold tracking-wide">Siswa</th>
                                <th class="px-6 py-3.5 font-semibold tracking-wide">NIS / NISN</th>
                                <th class="px-6 py-3.5 font-semibold tracking-wide">L/P</th>
                                <th class="px-6 py-3.5 font-semibold tracking-wide">Kelas</th>
                                <th class="px-6 py-3.5 font-semibold tracking-wide">Status</th>
                                <th class="px-6 py-3.5 font-semibold tracking-wide text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <!-- Skeleton Loading State -->
                            <template v-if="loading">
                                <tr v-for="i in 5" :key="i" class="animate-pulse">
                                    <td class="px-6 py-4"><div class="w-4 h-4 bg-slate-200 dark:bg-zinc-700 rounded"></div></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-zinc-700"></div>
                                            <div class="space-y-2">
                                                <div class="h-4 w-32 bg-slate-200 dark:bg-zinc-700 rounded"></div>
                                                <div class="h-3 w-20 bg-slate-100 dark:bg-zinc-800 rounded"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1.5">
                                            <div class="h-3.5 w-24 bg-slate-200 dark:bg-zinc-700 rounded"></div>
                                            <div class="h-3 w-20 bg-slate-100 dark:bg-zinc-800 rounded"></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="h-4 w-6 bg-slate-200 dark:bg-zinc-700 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="h-4 w-16 bg-slate-200 dark:bg-zinc-700 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="h-6 w-16 bg-slate-200 dark:bg-zinc-700 rounded-full"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <div class="w-8 h-8 bg-slate-100 dark:bg-zinc-800 rounded-lg"></div>
                                            <div class="w-8 h-8 bg-slate-100 dark:bg-zinc-800 rounded-lg"></div>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <!-- Empty State -->
                            <tr v-else-if="students.data.length === 0">
                                <td :colspan="hideSelection ? 6 : 7" class="px-6 py-16 text-center text-slate-500">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                                            <Shield class="w-8 h-8 text-slate-300 dark:text-zinc-600" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-700 dark:text-slate-300">Belum ada siswa ditemukan</p>
                                            <p class="text-sm text-slate-400 mt-1">Coba ubah filter atau tambah siswa baru</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Data Rows -->
                            <tr
                                v-else
                                v-for="student in students.data"
                                :key="student.id"
                                class="group hover:bg-slate-50/80 dark:hover:bg-zinc-800/50 transition-colors"
                                :class="{ 'bg-emerald-50/50 dark:bg-emerald-900/10': !hideSelection && selectedIds.includes(student.id) }"
                            >
                                <td v-if="!hideSelection" class="px-6 py-4">
                                    <div class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :checked="selectedIds.includes(student.id)"
                                            @change="toggleSelection(student.id)"
                                            class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500 dark:border-zinc-600 dark:bg-zinc-700 dark:ring-offset-zinc-800"
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img
                                            :src="student.foto ? `/storage/${student.foto}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(student.nama_lengkap)}&background=random`"
                                            :alt="student.nama_lengkap"
                                            class="w-10 h-10 rounded-full object-cover bg-slate-100 ring-2 ring-white dark:ring-zinc-800"
                                            loading="lazy"
                                        />
                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-900 dark:text-slate-100">{{ student.nama_lengkap }}</span>
                                            <span class="text-xs text-slate-500" v-if="student.nama_panggilan">({{ student.nama_panggilan }})</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-mono font-medium">{{ student.nis }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ student.nisn }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ student.jenis_kelamin }}</td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                    {{ student.kelas?.nama_lengkap || '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['px-2.5 py-1 rounded-lg text-xs font-semibold border', getStatusBadgeClass(student.status)]">
                                        {{ getStatusLabel(student.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Motion :whileTap="{ scale: 0.95 }">
                                            <button @click="onView(student)" class="w-9 h-9 flex items-center justify-center text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-xl transition-colors border border-transparent hover:border-emerald-200 dark:hover:border-emerald-800" title="Lihat Detail">
                                                <Eye class="w-4 h-4" />
                                            </button>
                                        </Motion>
                                        <template v-if="!readOnly">
                                            <Motion :whileTap="{ scale: 0.95 }">
                                                <button @click="onEdit(student)" class="w-9 h-9 flex items-center justify-center text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-xl transition-colors border border-transparent hover:border-amber-200 dark:hover:border-amber-800" title="Edit">
                                                    <Edit class="w-4 h-4" />
                                                </button>
                                            </Motion>
                                            <Motion :whileTap="{ scale: 0.95 }">
                                                <button @click="onUpdateStatus(student)" class="w-9 h-9 flex items-center justify-center text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-900/20 rounded-xl transition-colors border border-transparent hover:border-sky-200 dark:hover:border-sky-800" title="Update Status">
                                                    <RefreshCw class="w-4 h-4" />
                                                </button>
                                            </Motion>
                                            <Motion :whileTap="{ scale: 0.95 }">
                                                <button @click="onDelete(student)" class="w-9 h-9 flex items-center justify-center text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors border border-transparent hover:border-red-200 dark:hover:border-red-800" title="Hapus">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </Motion>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </Motion>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-3">
            <!-- Loading State -->
            <div v-if="loading" class="p-4 text-center text-gray-500 animate-pulse">Memuat data...</div>

            <!-- Empty State -->
            <div v-else-if="students.data.length === 0" class="p-8 text-center text-gray-500 bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800">
                <Shield class="w-8 h-8 mx-auto text-gray-300 mb-2" />
                <p>Belum ada siswa ditemukan</p>
            </div>

            <!-- Data Cards -->
            <div
                v-else
                v-for="student in students.data"
                :key="student.id"
                class="bg-white dark:bg-zinc-900 p-4 rounded-xl shadow-sm border transition-all active:scale-[0.99]"
                :class="!hideSelection && selectedIds.includes(student.id) ? 'border-emerald-500 ring-1 ring-emerald-500 bg-emerald-50/10' : 'border-gray-100 dark:border-zinc-800'"
            >
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <div v-if="!hideSelection" class="pt-1 shrink-0">
                            <input
                                type="checkbox"
                                :checked="selectedIds.includes(student.id)"
                                @change="toggleSelection(student.id)"
                                class="w-5 h-5 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500 dark:border-zinc-600 dark:bg-zinc-700 dark:ring-offset-zinc-800"
                            >
                        </div>
                        <img
                            :src="student.foto ? `/storage/${student.foto}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(student.nama_lengkap)}&background=random`"
                            :alt="student.nama_lengkap"
                            class="w-12 h-12 rounded-full object-cover bg-slate-100 ring-2 ring-white dark:ring-zinc-800 shrink-0"
                            loading="lazy"
                        />
                        <div class="min-w-0 flex-1">
                            <h3 class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ student.nama_lengkap }}</h3>
                            <p class="text-sm text-gray-500">{{ student.nis }}</p>
                            <p class="text-xs text-gray-400" v-if="student.nama_panggilan">({{ student.nama_panggilan }})</p>
                        </div>
                    </div>
                    <span :class="['px-2 py-0.5 rounded-full text-[10px] font-medium border border-transparent shrink-0', getStatusBadgeClass(student.status)]">
                        {{ getStatusLabel(student.status) }}
                    </span>
                </div>

                <div class="flex items-center gap-2 mb-4 text-sm text-gray-600 dark:text-gray-400" :class="hideSelection ? '' : 'pl-20'">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300">
                        {{ student.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </span>
                    <span class="text-xs text-gray-400">• Kelas {{ student.kelas?.nama_lengkap || '-' }}</span>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-50 dark:border-zinc-800" :class="hideSelection ? '' : 'pl-20'">
                    <button
                        @click="onView(student)"
                        class="p-2 text-blue-600 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
                        title="Lihat"
                    >
                        <Eye class="w-4 h-4" />
                    </button>
                    <template v-if="!readOnly">
                        <button
                            @click="onEdit(student)"
                            class="p-2 text-amber-600 bg-amber-50 dark:bg-amber-900/20 rounded-lg"
                            title="Edit"
                        >
                            <Edit class="w-4 h-4" />
                        </button>
                        <button
                            @click="onUpdateStatus(student)"
                            class="p-2 text-gray-600 bg-gray-100 dark:bg-gray-800 rounded-lg"
                            title="Update Status"
                        >
                            <RefreshCw class="w-4 h-4" />
                        </button>
                        <button
                            @click="onDelete(student)"
                            class="p-2 text-red-600 bg-red-50 dark:bg-red-900/20 rounded-lg"
                            title="Hapus"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <Motion
            v-if="students.total > students.per_page"
            :initial="{ opacity: 0 }"
            :animate="{ opacity: 1 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.2 }"
            class="pt-4 border-t border-slate-200 dark:border-zinc-800"
        >
            <!-- Mobile Pagination: Simple prev/next -->
            <div class="flex items-center justify-between md:hidden">
                <div class="text-sm text-slate-500">
                    {{ students.from }}-{{ students.to }} dari {{ students.total }}
                </div>
                <div class="flex gap-2">
                    <Motion :whileTap="{ scale: 0.95 }">
                        <Link
                            :href="students.links[0]?.url || '#'"
                            :class="[
                                'flex items-center justify-center w-11 h-11 rounded-xl border transition-colors',
                                students.links[0]?.url
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
                        {{ students.current_page }}
                    </div>

                    <Motion :whileTap="{ scale: 0.95 }">
                        <Link
                            :href="students.links[students.links.length - 1]?.url || '#'"
                            :class="[
                                'flex items-center justify-center w-11 h-11 rounded-xl border transition-colors',
                                students.links[students.links.length - 1]?.url
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

            <!-- Desktop Pagination: Full pagination -->
            <div class="hidden md:flex items-center justify-between">
                <div class="text-sm text-slate-500">
                    Menampilkan {{ students.from }} - {{ students.to }} dari {{ students.total }} data
                </div>
                <div class="flex gap-1">
                    <Link
                        v-for="(link, i) in students.links"
                        :key="i"
                        :href="link.url || '#'"
                        :class="[
                            'min-w-[36px] h-9 px-3 flex items-center justify-center text-sm rounded-lg transition-colors',
                            link.active
                                ? 'bg-emerald-500 text-white font-semibold shadow-sm'
                                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-zinc-800',
                            !link.url && 'opacity-50 cursor-not-allowed hover:bg-transparent'
                        ]"
                        v-html="link.label"
                        preserve-scroll
                        preserve-state
                    />
                </div>
            </div>
        </Motion>
    </div>
</template>
