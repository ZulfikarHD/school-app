<script setup lang="ts">
/**
 * ScheduleTable Component - Komponen tabel untuk menampilkan daftar jadwal mengajar
 * dengan fitur search, filter, pagination, dan responsive design
 */
import { ref, computed, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import {
    Search,
    Edit,
    Trash2,
    ChevronLeft,
    ChevronRight,
    Filter,
    X,
    Calendar,
    Clock,
    User,
    BookOpen
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import Badge from '@/components/ui/Badge.vue';

interface Schedule {
    id: number;
    teacher_id: number;
    subject_id: number;
    class_id: number;
    academic_year_id: number;
    hari: { value: string; label?: string };
    jam_mulai: string;
    jam_selesai: string;
    ruangan: string | null;
    is_active: boolean;
    time_range: string;
    full_class_name: string;
    teacher: {
        id: number;
        nama_lengkap: string;
        foto_url?: string;
    };
    subject: {
        id: number;
        kode_mapel: string;
        nama_mapel: string;
    };
    school_class: {
        id: number;
        tingkat: number;
        nama: string;
    };
    academic_year: {
        id: number;
        name: string;
    };
}

interface Teacher {
    id: number;
    nama_lengkap: string;
}

interface SchoolClass {
    id: number;
    tingkat: number;
    nama: string;
}

interface AcademicYear {
    id: number;
    name: string;
    is_active: boolean;
}

interface HariOption {
    value: string;
    label: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Pagination {
    data: Schedule[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    per_page: number;
}

interface Props {
    schedules: Pagination;
    filters?: {
        search?: string;
        teacher_id?: string;
        class_id?: string;
        hari?: string;
        academic_year_id?: string;
        is_active?: string;
    };
    teachers: Teacher[];
    classes: SchoolClass[];
    academicYears: AcademicYear[];
    hariOptions: HariOption[];
    filterUrl: string;
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    filters: () => ({})
});

const emit = defineEmits(['edit', 'delete']);
const haptics = useHaptics();

// Local state for filters
const search = ref(props.filters.search || '');
const teacherId = ref(props.filters.teacher_id || '');
const classId = ref(props.filters.class_id || '');
const hari = ref(props.filters.hari || '');
const academicYearId = ref(props.filters.academic_year_id || '');
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
        console.warn('ScheduleTable: filterUrl prop is required for filtering');
        return;
    }

    haptics.selection();
    router.get(
        props.filterUrl,
        {
            search: search.value || undefined,
            teacher_id: teacherId.value || undefined,
            class_id: classId.value || undefined,
            hari: hari.value || undefined,
            academic_year_id: academicYearId.value || undefined
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
    teacherId.value = '';
    classId.value = '';
    hari.value = '';
    academicYearId.value = '';
    applyFilters();
};

const hasActiveFilters = computed(() => {
    return search.value || teacherId.value || classId.value || hari.value;
});

// Get hari label from value
const getHariLabel = (hariValue: string | { value: string; label?: string }): string => {
    if (typeof hariValue === 'object' && hariValue.value) {
        const option = props.hariOptions.find(h => h.value === hariValue.value);
        return option?.label || hariValue.value;
    }
    const option = props.hariOptions.find(h => h.value === hariValue);
    return option?.label || String(hariValue);
};

// Get hari badge variant
const getHariBadgeVariant = (hariValue: string | { value: string }): 'info' | 'success' | 'warning' | 'error' | 'default' => {
    const value = typeof hariValue === 'object' ? hariValue.value : hariValue;
    switch (value) {
        case 'senin': return 'info';
        case 'selasa': return 'success';
        case 'rabu': return 'warning';
        case 'kamis': return 'default';
        case 'jumat': return 'error';
        case 'sabtu': return 'info';
        default: return 'default';
    }
};

// Format class name
const formatClassName = (schedule: Schedule): string => {
    if (schedule.school_class) {
        return `Kelas ${schedule.school_class.tingkat}${schedule.school_class.nama}`;
    }
    return schedule.full_class_name || '-';
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
                        placeholder="Cari guru, mapel, atau kelas..."
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
                <div class="hidden sm:flex items-center gap-2 flex-wrap">
                    <select
                        v-model="academicYearId"
                        @change="applyFilters"
                        class="px-3 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    >
                        <option value="">Semua Tahun Ajaran</option>
                        <option v-for="year in academicYears" :key="year.id" :value="year.id">
                            {{ year.name }}{{ year.is_active ? ' (Aktif)' : '' }}
                        </option>
                    </select>

                    <select
                        v-model="teacherId"
                        @change="applyFilters"
                        class="px-3 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all max-w-[180px]"
                    >
                        <option value="">Semua Guru</option>
                        <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                            {{ teacher.nama_lengkap }}
                        </option>
                    </select>

                    <select
                        v-model="classId"
                        @change="applyFilters"
                        class="px-3 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    >
                        <option value="">Semua Kelas</option>
                        <option v-for="kelas in classes" :key="kelas.id" :value="kelas.id">
                            Kelas {{ kelas.tingkat }}{{ kelas.nama }}
                        </option>
                    </select>

                    <select
                        v-model="hari"
                        @change="applyFilters"
                        class="px-3 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    >
                        <option value="">Semua Hari</option>
                        <option v-for="option in hariOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
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
                    v-model="academicYearId"
                    @change="applyFilters"
                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100"
                >
                    <option value="">Semua Tahun Ajaran</option>
                    <option v-for="year in academicYears" :key="year.id" :value="year.id">
                        {{ year.name }}{{ year.is_active ? ' (Aktif)' : '' }}
                    </option>
                </select>

                <select
                    v-model="teacherId"
                    @change="applyFilters"
                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100"
                >
                    <option value="">Semua Guru</option>
                    <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                        {{ teacher.nama_lengkap }}
                    </option>
                </select>

                <select
                    v-model="classId"
                    @change="applyFilters"
                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100"
                >
                    <option value="">Semua Kelas</option>
                    <option v-for="kelas in classes" :key="kelas.id" :value="kelas.id">
                        Kelas {{ kelas.tingkat }}{{ kelas.nama }}
                    </option>
                </select>

                <select
                    v-model="hari"
                    @change="applyFilters"
                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100"
                >
                    <option value="">Semua Hari</option>
                    <option v-for="option in hariOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
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
                            Mata Pelajaran
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Kelas
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Hari
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Waktu
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Ruangan
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    <tr
                        v-for="schedule in schedules.data"
                        :key="schedule.id"
                        class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                        :class="{ 'opacity-60': !schedule.is_active }"
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <User class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                <span class="font-medium text-slate-900 dark:text-slate-100">
                                    {{ schedule.teacher?.nama_lengkap || '-' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <BookOpen class="w-4 h-4 text-slate-400" />
                                <span class="text-slate-700 dark:text-slate-300">
                                    {{ schedule.subject?.nama_mapel || '-' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300">
                            {{ formatClassName(schedule) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <Badge :variant="getHariBadgeVariant(schedule.hari)">
                                {{ getHariLabel(schedule.hari) }}
                            </Badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <Clock class="w-4 h-4 text-slate-400" />
                                <span class="text-slate-700 dark:text-slate-300">
                                    {{ schedule.time_range }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300">
                            {{ schedule.ruangan || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Motion :whileTap="{ scale: 0.95 }">
                                    <button
                                        @click="emit('edit', schedule); haptics.light()"
                                        class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                        title="Edit"
                                    >
                                        <Edit class="w-4 h-4" />
                                    </button>
                                </Motion>
                                <Motion :whileTap="{ scale: 0.95 }">
                                    <button
                                        @click="emit('delete', schedule); haptics.light()"
                                        class="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                        title="Hapus"
                                    >
                                        <Trash2 class="w-4 h-4" />
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
                v-for="schedule in schedules.data"
                :key="schedule.id"
                class="p-4 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                :class="{ 'opacity-60': !schedule.is_active }"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <Badge :variant="getHariBadgeVariant(schedule.hari)" size="sm">
                                {{ getHariLabel(schedule.hari) }}
                            </Badge>
                            <span class="text-sm text-slate-500 dark:text-slate-400">
                                {{ schedule.time_range }}
                            </span>
                        </div>
                        <h3 class="font-medium text-slate-900 dark:text-slate-100 truncate">
                            {{ schedule.subject?.nama_mapel || '-' }}
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                            {{ schedule.teacher?.nama_lengkap || '-' }}
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ formatClassName(schedule) }}
                            <span v-if="schedule.ruangan"> • {{ schedule.ruangan }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-1">
                        <Motion :whileTap="{ scale: 0.95 }">
                            <button
                                @click="emit('edit', schedule); haptics.light()"
                                class="p-2 text-blue-600 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
                            >
                                <Edit class="w-4 h-4" />
                            </button>
                        </Motion>
                        <Motion :whileTap="{ scale: 0.95 }">
                            <button
                                @click="emit('delete', schedule); haptics.light()"
                                class="p-2 text-red-600 bg-red-50 dark:bg-red-900/20 rounded-lg"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </Motion>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-if="schedules.data.length === 0"
            class="p-12 text-center"
        >
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                <Calendar class="w-8 h-8 text-slate-400" />
            </div>
            <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100 mb-1">
                Tidak ada jadwal
            </h3>
            <p class="text-slate-500 dark:text-slate-400 mb-4">
                {{ hasActiveFilters ? 'Tidak ada jadwal yang sesuai dengan filter' : 'Belum ada jadwal mengajar yang terdaftar' }}
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
            v-if="schedules.last_page > 1"
            class="px-4 py-3 border-t border-slate-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-3"
        >
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Menampilkan {{ schedules.from }} - {{ schedules.to }} dari {{ schedules.total }} jadwal
            </p>
            <div class="flex items-center gap-1">
                <!-- Previous Button -->
                <Link
                    v-if="schedules.links[0]?.url"
                    :href="schedules.links[0].url"
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
                    <template v-for="link in schedules.links.slice(1, -1)" :key="link.label">
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
                    {{ schedules.current_page }} / {{ schedules.last_page }}
                </div>

                <!-- Next Button -->
                <Link
                    v-if="schedules.links[schedules.links.length - 1]?.url"
                    :href="schedules.links[schedules.links.length - 1].url"
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
