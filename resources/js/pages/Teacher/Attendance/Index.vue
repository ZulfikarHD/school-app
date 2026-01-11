<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { Plus, Calendar, Edit2, Filter } from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';
import { ref, watch } from 'vue';

/**
 * Halaman riwayat presensi siswa untuk guru
 * dengan filter berdasarkan kelas dan tanggal
 * serta kemampuan edit presensi yang sudah terinput
 */

interface AttendanceRecord {
    id: number;
    tanggal: string;
    class_id: number;
    kelas_nama: string;
    total_siswa: number;
    hadir: number;
    izin: number;
    sakit: number;
    alpha: number;
    recorded_at: string;
    can_edit: boolean;
}

interface SchoolClass {
    id: number;
    tingkat: number;
    nama: string;
    nama_lengkap: string;
    tahun_ajaran: string;
    jumlah_siswa: number;
}

interface PaginatedAttendances {
    data: AttendanceRecord[];
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    title: string;
    classes: SchoolClass[];
    attendances: PaginatedAttendances;
    filters: {
        kelas_id: number | null;
        start_date: string | null;
        end_date: string | null;
    };
    summary: {
        total_records: number;
        total_hadir: number;
        total_izin: number;
        total_sakit: number;
        total_alpha: number;
        attendance_rate: string;
    };
}

const props = defineProps<Props>();

const haptics = useHaptics();

const filters = ref({
    kelas_id: props.filters.kelas_id,
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
});

const showFilters = ref(false);

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const formatDateTime = (datetime: string) => {
    return new Date(datetime).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const applyFilters = () => {
    haptics.light();
    router.get(
        '/teacher/attendance',
        {
            kelas_id: filters.value.kelas_id,
            start_date: filters.value.start_date,
            end_date: filters.value.end_date,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const resetFilters = () => {
    haptics.light();
    filters.value = {
        kelas_id: null,
        start_date: null,
        end_date: null,
    };
    applyFilters();
};

// Watch filters and apply automatically (debounced)
let filterTimeout: ReturnType<typeof setTimeout> | null = null;
watch(filters, () => {
    if (filterTimeout) {
        clearTimeout(filterTimeout);
    }
    filterTimeout = setTimeout(() => {
        applyFilters();
    }, 500); // 500ms debounce
}, { deep: true });

const editAttendance = (record: AttendanceRecord) => {
    haptics.light();
    router.get('/teacher/attendance/daily', {
        kelas_id: record.class_id,
        tanggal: record.tanggal,
    });
};

const getStatusColor = (status: 'hadir' | 'izin' | 'sakit' | 'alpha') => {
    const colors = {
        hadir: 'text-emerald-600 dark:text-emerald-400',
        izin: 'text-blue-600 dark:text-blue-400',
        sakit: 'text-yellow-600 dark:text-yellow-400',
        alpha: 'text-red-600 dark:text-red-400',
    };
    return colors[status];
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="bg-white px-6 py-8 border-b border-gray-100 dark:bg-zinc-900 dark:border-zinc-800">
                    <div class="mx-auto max-w-7xl flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">Lihat dan kelola riwayat presensi siswa</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <Motion :whileTap="{ scale: 0.97 }">
                                <button
                                    @click="showFilters = !showFilters"
                                    class="px-4 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-300 rounded-xl font-semibold
                                           flex items-center gap-2 transition-colors duration-150"
                                >
                                    <Filter :size="20" />
                                    <span class="hidden sm:inline">Filter</span>
                                </button>
                            </Motion>

                            <Motion :whileTap="{ scale: 0.97 }">
                                <Link
                                    href="/teacher/attendance/daily"
                                    @click="haptics.light()"
                                    class="px-5 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold
                                           flex items-center gap-2 shadow-sm shadow-emerald-500/25
                                           transition-colors duration-150"
                                >
                                    <Plus :size="20" />
                                    <span class="hidden sm:inline">Input Presensi</span>
                                </Link>
                            </Motion>
                        </div>
                    </div>
                </div>
            </Motion>

            <div class="mx-auto max-w-7xl px-6 py-8">
                <!-- Filters Panel -->
                <Motion
                    v-if="showFilters"
                    :initial="{ opacity: 0, height: 0 }"
                    :animate="{ opacity: 1, height: 'auto' }"
                    :exit="{ opacity: 0, height: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filter Riwayat Presensi</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Kelas
                                </label>
                                <select
                                    v-model="filters.kelas_id"
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl
                                           text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                >
                                    <option :value="null">Semua Kelas</option>
                                    <option v-for="kelas in classes" :key="kelas.id" :value="kelas.id">
                                        {{ kelas.nama_lengkap }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Tanggal Mulai
                                </label>
                                <input
                                    v-model="filters.start_date"
                                    type="date"
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl
                                           text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Tanggal Akhir
                                </label>
                                <input
                                    v-model="filters.end_date"
                                    type="date"
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl
                                           text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Filter otomatis diterapkan saat Anda memilih
                            </p>
                            <button
                                @click="resetFilters"
                                class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700
                                       text-gray-700 dark:text-gray-300 rounded-xl font-semibold transition-colors duration-150"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </Motion>

                <!-- Summary Stats -->
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-5 mb-6">
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-slate-600 dark:text-zinc-400">Total Record</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ summary.total_records }}</p>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                    >
                        <div class="bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Hadir</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-900 dark:text-emerald-100">{{ summary.total_hadir }}</p>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">{{ summary.attendance_rate }}%</p>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                    >
                        <div class="bg-blue-50 dark:bg-blue-950/30 rounded-2xl border border-blue-200 dark:border-blue-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Izin</p>
                            <p class="mt-2 text-3xl font-bold text-blue-900 dark:text-blue-100">{{ summary.total_izin }}</p>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                    >
                        <div class="bg-yellow-50 dark:bg-yellow-950/30 rounded-2xl border border-yellow-200 dark:border-yellow-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Sakit</p>
                            <p class="mt-2 text-3xl font-bold text-yellow-900 dark:text-yellow-100">{{ summary.total_sakit }}</p>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                    >
                        <div class="bg-red-50 dark:bg-red-950/30 rounded-2xl border border-red-200 dark:border-red-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">Alpha</p>
                            <p class="mt-2 text-3xl font-bold text-red-900 dark:text-red-100">{{ summary.total_alpha }}</p>
                        </div>
                    </Motion>
                </div>

                <!-- Attendance History Table -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.3 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-200 dark:border-zinc-800">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Riwayat Presensi</h2>
                            <p class="text-sm text-slate-600 dark:text-zinc-400 mt-1">
                                Klik "Edit" untuk mengubah data presensi
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                                <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Kelas
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Total Siswa
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                                            Hadir
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                                            Izin
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-yellow-600 dark:text-yellow-400 uppercase tracking-wider">
                                            Sakit
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-red-600 dark:text-red-400 uppercase tracking-wider">
                                            Alpha
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Diinput
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-800">
                                    <tr v-if="attendances.data.length === 0">
                                        <td colspan="9" class="px-6 py-12 text-center">
                                            <Calendar :size="48" class="mx-auto text-gray-300 dark:text-zinc-700 mb-3" />
                                            <p class="text-gray-500 dark:text-gray-400">Belum ada data presensi</p>
                                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                                                Mulai input presensi harian untuk melihat riwayat
                                            </p>
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="record in attendances.data"
                                        :key="record.id"
                                        class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                                            {{ formatDate(record.tanggal) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                            {{ record.kelas_nama }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white font-semibold">
                                            {{ record.total_siswa }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold" :class="getStatusColor('hadir')">
                                            {{ record.hadir }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold" :class="getStatusColor('izin')">
                                            {{ record.izin }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold" :class="getStatusColor('sakit')">
                                            {{ record.sakit }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold" :class="getStatusColor('alpha')">
                                            {{ record.alpha }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            {{ formatDateTime(record.recorded_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <button
                                                v-if="record.can_edit"
                                                @click="editAttendance(record)"
                                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/30 dark:hover:bg-blue-950/50
                                                       text-blue-600 dark:text-blue-400 rounded-lg font-medium transition-colors"
                                            >
                                                <Edit2 :size="14" />
                                                <span>Edit</span>
                                            </button>
                                            <span
                                                v-else
                                                class="text-xs text-gray-400 dark:text-gray-500"
                                            >
                                                Tidak dapat diedit
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="attendances.links.length > 3" class="border-t border-gray-200 dark:border-zinc-800 px-6 py-4 bg-gray-50 dark:bg-zinc-800/50">
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Halaman {{ attendances.current_page }} dari {{ attendances.last_page }}
                                    <span class="text-gray-400 dark:text-gray-500 ml-2">({{ attendances.total }} total)</span>
                                </p>
                                <div class="flex gap-2">
                                    <Link
                                        v-for="link in attendances.links"
                                        :key="link.label"
                                        :href="link.url || ''"
                                        preserve-scroll
                                        preserve-state
                                        :class="[
                                            'px-3 py-1.5 text-sm rounded-lg transition-colors',
                                            link.active
                                                ? 'bg-emerald-500 text-white font-semibold'
                                                : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 border border-gray-300 dark:border-zinc-700',
                                            !link.url && 'opacity-50 cursor-not-allowed',
                                        ]"
                                        v-html="link.label"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>
