<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Filter, Calendar, Users, FileText, ArrowLeft } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import AttendanceStatusBadge from '@/components/features/attendance/AttendanceStatusBadge.vue';
import Badge from '@/components/ui/Badge.vue';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Halaman rekap kehadiran siswa untuk Principal (read-only)
 * dengan filter kelas, tanggal, dan status
 */

interface Attendance {
    id: number;
    tanggal: string;
    status: 'H' | 'I' | 'S' | 'A';
    keterangan: string | null;
    student: {
        id: number;
        nama_lengkap: string;
        nis: string;
    };
    class: {
        id: number;
        tingkat: string;
        nama: string;
    };
    recorded_by: {
        name: string;
    } | null;
    recorded_at: string;
}

interface ClassItem {
    id: number;
    nama_lengkap: string;
}

interface Props {
    title: string;
    attendances: {
        data: Attendance[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    classes: ClassItem[];
    summary: {
        total: number;
        hadir: number;
        izin: number;
        sakit: number;
        alpha: number;
    };
    filters: {
        class_id?: number;
        date?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const haptics = useHaptics();

// State
const filterForm = ref({
    class_id: props.filters.class_id || '',
    date: props.filters.date || new Date().toISOString().split('T')[0],
    status: props.filters.status || '',
});

const showFilters = ref(false);

// Computed
const hasFilters = computed(() => {
    return filterForm.value.class_id || filterForm.value.status;
});

const attendancePercentage = computed(() => {
    if (props.summary.total === 0) return 0;
    return Math.round((props.summary.hadir / props.summary.total) * 100);
});

// Methods
const applyFilters = () => {
    haptics.light();
    router.get('/principal/attendance/students', filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    haptics.light();
    filterForm.value = {
        class_id: '',
        date: new Date().toISOString().split('T')[0],
        status: '',
    };
    router.get(
        '/principal/attendance/students',
        { date: filterForm.value.date },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const goToPage = (page: number) => {
    haptics.light();
    router.get(`/principal/attendance/students?page=${page}`, filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const navigateTo = (path: string) => {
    haptics.light();
    router.visit(path);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatTime = (datetime: string) => {
    if (!datetime) return '-';
    return new Date(datetime).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getClassName = (attendance: Attendance) => {
    if (!attendance.class) return '-';
    return `Kelas ${attendance.class.tingkat}${attendance.class.nama}`;
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="space-y-6">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <button
                            @click="navigateTo('/principal/attendance/dashboard')"
                            class="p-2 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                        >
                            <ArrowLeft :size="20" class="text-slate-600 dark:text-slate-400" />
                        </button>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                                {{ title }}
                            </h1>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ formatDate(filters.date || new Date().toISOString().split('T')[0]) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <Motion
                            tag="button"
                            :animate="{ scale: showFilters ? 0.95 : 1 }"
                            @click="showFilters = !showFilters"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors"
                        >
                            <Filter :size="18" />
                            Filter
                            <Badge
                                v-if="hasFilters"
                                variant="success"
                                size="xs"
                                class="ml-1"
                            >
                                Aktif
                            </Badge>
                        </Motion>

                        <button
                            @click="navigateTo('/principal/attendance/students/report')"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition-colors"
                        >
                            <FileText :size="18" />
                            Lihat Laporan
                        </button>
                    </div>
                </div>
            </Motion>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total</p>
                            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                                {{ attendances.total }}
                            </p>
                        </div>
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                            <Users :size="20" class="text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800">
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Hadir</p>
                    <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ summary.hadir }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ attendancePercentage }}%</p>
                </div>

                <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800">
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Izin</p>
                    <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ summary.izin }}
                    </p>
                </div>

                <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800">
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Sakit</p>
                    <p class="mt-1 text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                        {{ summary.sakit }}
                    </p>
                </div>

                <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800">
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Alpha</p>
                    <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">
                        {{ summary.alpha }}
                    </p>
                </div>
            </div>

            <!-- Filters Panel -->
            <Motion
                v-if="showFilters"
                :initial="{ opacity: 0, height: 0 }"
                :animate="{ opacity: 1, height: 'auto' }"
                :exit="{ opacity: 0, height: 0 }"
                class="bg-white dark:bg-zinc-900 rounded-xl p-6 border border-slate-200 dark:border-zinc-800"
            >
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">
                            Tanggal
                        </label>
                        <input
                            v-model="filterForm.date"
                            type="date"
                            class="w-full px-4 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 transition-all"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">
                            Kelas
                        </label>
                        <select
                            v-model="filterForm.class_id"
                            class="w-full px-4 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 transition-all"
                        >
                            <option value="">Semua Kelas</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                {{ cls.nama_lengkap }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">
                            Status
                        </label>
                        <select
                            v-model="filterForm.status"
                            class="w-full px-4 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 transition-all"
                        >
                            <option value="">Semua Status</option>
                            <option value="H">Hadir</option>
                            <option value="I">Izin</option>
                            <option value="S">Sakit</option>
                            <option value="A">Alpha</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition-colors"
                    >
                        Terapkan Filter
                    </button>
                    <button
                        v-if="hasFilters"
                        @click="clearFilters"
                        class="px-4 py-2 bg-slate-200 dark:bg-zinc-700 hover:bg-slate-300 dark:hover:bg-zinc-600 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-medium transition-colors"
                    >
                        Reset Filter
                    </button>
                </div>
            </Motion>

            <!-- Attendance Table -->
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-zinc-800 border-b border-slate-200 dark:border-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    Tanggal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    Siswa
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    Kelas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    Keterangan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    Dicatat Oleh
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <tr
                                v-for="attendance in attendances.data"
                                :key="attendance.id"
                                class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                                    {{ formatDate(attendance.tanggal) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                            {{ attendance.student?.nama_lengkap || '-' }}
                                        </div>
                                        <div class="text-sm text-slate-500 dark:text-slate-400">
                                            NIS: {{ attendance.student?.nis || '-' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                                    {{ getClassName(attendance) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <AttendanceStatusBadge :status="attendance.status" />
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 max-w-xs truncate">
                                    {{ attendance.keterangan || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm text-slate-900 dark:text-white">
                                            {{ attendance.recorded_by?.name || '-' }}
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ formatTime(attendance.recorded_at) }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="attendances.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <Calendar :size="48" class="mx-auto mb-3 opacity-50" />
                                    <p class="text-lg font-medium">Tidak ada data kehadiran</p>
                                    <p class="text-sm mt-1">Coba ubah filter atau pilih tanggal lain</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="attendances.last_page > 1" class="px-6 py-4 border-t border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-slate-500 dark:text-slate-400">
                            Menampilkan {{ (attendances.current_page - 1) * attendances.per_page + 1 }} -
                            {{ Math.min(attendances.current_page * attendances.per_page, attendances.total) }}
                            dari {{ attendances.total }} data
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                v-for="page in attendances.last_page"
                                :key="page"
                                @click="goToPage(page)"
                                :class="[
                                    'px-3 py-1 rounded-lg text-sm font-medium transition-colors',
                                    page === attendances.current_page
                                        ? 'bg-emerald-600 text-white'
                                        : 'bg-slate-200 dark:bg-zinc-700 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-zinc-600',
                                ]"
                            >
                                {{ page }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
