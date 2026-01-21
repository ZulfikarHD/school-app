<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    Download,
    Filter,
    AlertTriangle,
    TrendingUp,
    ArrowLeft,
    Users,
} from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import Badge from '@/components/ui/Badge.vue';

/**
 * Halaman laporan kehadiran siswa untuk Principal
 * dengan statistik, chart, dan daftar siswa dengan kehadiran rendah
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
}

interface ClassItem {
    id: number;
    nama_lengkap: string;
}

interface LowAttendanceStudent {
    id: number;
    nis: string;
    nama: string;
    kelas: string;
    persentase: number;
    total_hadir: number;
    total_alpha: number;
    total_hari: number;
}

interface TrendDataItem {
    date: string;
    hadir: number;
    izin: number;
    sakit: number;
    alpha: number;
}

interface Props {
    title: string;
    attendances: Attendance[];
    statistics: {
        total_records: number;
        hadir: number;
        izin: number;
        sakit: number;
        alpha: number;
        persentase_hadir: number;
        persentase_kehadiran_valid: number;
    };
    trendData: Record<string, TrendDataItem>;
    classes: ClassItem[];
    lowAttendanceStudents: LowAttendanceStudent[];
    filters: {
        start_date: string;
        end_date: string;
        class_id?: number;
        status?: string;
    };
}

const props = defineProps<Props>();

const haptics = useHaptics();

// State
const filterForm = ref({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    class_id: props.filters.class_id || '',
    status: props.filters.status || '',
});

const showFilters = ref(false);
const activeTab = ref<'summary' | 'low-attendance'>('summary');

// Computed
const hasFilters = computed(() => {
    return filterForm.value.class_id || filterForm.value.status;
});

const pieChartData = computed(() => {
    const total = props.statistics.total_records || 1;
    return [
        { label: 'Hadir', value: props.statistics.hadir, color: '#10b981', percentage: Math.round((props.statistics.hadir / total) * 100) },
        { label: 'Izin', value: props.statistics.izin, color: '#3b82f6', percentage: Math.round((props.statistics.izin / total) * 100) },
        { label: 'Sakit', value: props.statistics.sakit, color: '#f59e0b', percentage: Math.round((props.statistics.sakit / total) * 100) },
        { label: 'Alpha', value: props.statistics.alpha, color: '#ef4444', percentage: Math.round((props.statistics.alpha / total) * 100) },
    ];
});

// Methods
const applyFilters = () => {
    haptics.light();
    router.get('/principal/attendance/students/report', filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    haptics.light();
    const today = new Date();
    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    filterForm.value = {
        start_date: startOfMonth.toISOString().split('T')[0],
        end_date: today.toISOString().split('T')[0],
        class_id: '',
        status: '',
    };
    router.get('/principal/attendance/students/report', {
        start_date: filterForm.value.start_date,
        end_date: filterForm.value.end_date,
    }, {
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
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const getPercentageColor = (percentage: number) => {
    if (percentage >= 90) return 'text-green-600 dark:text-green-400';
    if (percentage >= 80) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
};

const getPercentageBgColor = (percentage: number) => {
    if (percentage >= 90) return 'bg-green-100 dark:bg-green-900/50';
    if (percentage >= 80) return 'bg-yellow-100 dark:bg-yellow-900/50';
    return 'bg-red-100 dark:bg-red-900/50';
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
                                {{ formatDate(filters.start_date) }} - {{ formatDate(filters.end_date) }}
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
                    </div>
                </div>
            </Motion>

            <!-- Filters Panel -->
            <Motion
                v-if="showFilters"
                :initial="{ opacity: 0, height: 0 }"
                :animate="{ opacity: 1, height: 'auto' }"
                :exit="{ opacity: 0, height: 0 }"
                class="bg-white dark:bg-zinc-900 rounded-xl p-6 border border-slate-200 dark:border-zinc-800"
            >
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">
                            Tanggal Mulai
                        </label>
                        <input
                            v-model="filterForm.start_date"
                            type="date"
                            class="w-full px-4 py-2.5 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 transition-all"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">
                            Tanggal Selesai
                        </label>
                        <input
                            v-model="filterForm.end_date"
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

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-xl">
                                <Users :size="24" class="text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-slate-900 dark:text-white">
                            {{ statistics.total_records }}
                        </p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Total Records</p>
                    </div>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div class="p-3 bg-green-100 dark:bg-green-900/50 rounded-xl">
                                <TrendingUp :size="24" class="text-green-600 dark:text-green-400" />
                            </div>
                            <span :class="['text-sm font-semibold', getPercentageColor(statistics.persentase_hadir)]">
                                {{ statistics.persentase_hadir }}%
                            </span>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-green-600 dark:text-green-400">
                            {{ statistics.hadir }}
                        </p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Total Hadir</p>
                    </div>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 border border-slate-200 dark:border-zinc-800">
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Kehadiran Valid</p>
                        <p class="mt-2 text-3xl font-bold" :class="getPercentageColor(statistics.persentase_kehadiran_valid)">
                            {{ statistics.persentase_kehadiran_valid }}%
                        </p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Hadir + Izin + Sakit</p>
                    </div>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.25 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 border border-slate-200 dark:border-zinc-800">
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total Alpha</p>
                        <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">
                            {{ statistics.alpha }}
                        </p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tanpa keterangan</p>
                    </div>
                </Motion>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 border-b border-slate-200 dark:border-zinc-700">
                <button
                    @click="activeTab = 'summary'"
                    :class="[
                        'px-4 py-2 text-sm font-medium border-b-2 transition-colors -mb-px',
                        activeTab === 'summary'
                            ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
                    ]"
                >
                    Ringkasan
                </button>
                <button
                    @click="activeTab = 'low-attendance'"
                    :class="[
                        'px-4 py-2 text-sm font-medium border-b-2 transition-colors -mb-px',
                        activeTab === 'low-attendance'
                            ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
                    ]"
                >
                    Kehadiran Rendah
                    <span v-if="lowAttendanceStudents.length > 0" class="ml-1 px-2 py-0.5 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-full text-xs">
                        {{ lowAttendanceStudents.length }}
                    </span>
                </button>
            </div>

            <!-- Summary Tab -->
            <div v-if="activeTab === 'summary'" class="grid lg:grid-cols-2 gap-6">
                <!-- Pie Chart Visualization -->
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.3 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 border border-slate-200 dark:border-zinc-800">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                            Distribusi Status
                        </h3>
                        
                        <!-- Simple bar visualization -->
                        <div class="space-y-4">
                            <div v-for="item in pieChartData" :key="item.label" class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600 dark:text-slate-400">{{ item.label }}</span>
                                    <span class="font-medium text-slate-900 dark:text-white">
                                        {{ item.value }} ({{ item.percentage }}%)
                                    </span>
                                </div>
                                <div class="h-3 bg-slate-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :style="{ width: `${item.percentage}%`, backgroundColor: item.color }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Status Breakdown -->
                <Motion
                    :initial="{ opacity: 0, x: 20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.35 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 border border-slate-200 dark:border-zinc-800">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                            Detail Status
                        </h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800">
                                <p class="text-sm font-medium text-green-800 dark:text-green-300">Hadir</p>
                                <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ statistics.hadir }}
                                </p>
                            </div>
                            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                                <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Izin</p>
                                <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ statistics.izin }}
                                </p>
                            </div>
                            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl border border-yellow-200 dark:border-yellow-800">
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Sakit</p>
                                <p class="mt-1 text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                    {{ statistics.sakit }}
                                </p>
                            </div>
                            <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800">
                                <p class="text-sm font-medium text-red-800 dark:text-red-300">Alpha</p>
                                <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">
                                    {{ statistics.alpha }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>

            <!-- Low Attendance Tab -->
            <div v-if="activeTab === 'low-attendance'">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-red-100 dark:bg-red-900/50 rounded-lg">
                                    <AlertTriangle :size="20" class="text-red-600 dark:text-red-400" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900 dark:text-white">
                                        Siswa dengan Kehadiran &lt; 80%
                                    </h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        Risiko tidak naik kelas
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50 dark:bg-zinc-800 border-b border-slate-200 dark:border-zinc-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            Siswa
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            Kelas
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            Kehadiran
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            Hadir
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            Alpha
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            Total Hari
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                    <tr
                                        v-for="student in lowAttendanceStudents"
                                        :key="student.id"
                                        class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                    {{ student.nama }}
                                                </div>
                                                <div class="text-sm text-slate-500 dark:text-slate-400">
                                                    NIS: {{ student.nis }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                                            {{ student.kelas }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <Badge
                                                :variant="student.persentase >= 90 ? 'success' : student.persentase >= 80 ? 'warning' : 'error'"
                                                size="sm"
                                            >
                                                {{ student.persentase }}%
                                            </Badge>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-green-600 dark:text-green-400 font-medium">
                                            {{ student.total_hadir }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-red-600 dark:text-red-400 font-medium">
                                            {{ student.total_alpha }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-500 dark:text-slate-400">
                                            {{ student.total_hari }}
                                        </td>
                                    </tr>
                                    <tr v-if="lowAttendanceStudents.length === 0">
                                        <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                            <TrendingUp :size="48" class="mx-auto mb-3 text-green-500 opacity-50" />
                                            <p class="text-lg font-medium">Semua siswa memiliki kehadiran baik</p>
                                            <p class="text-sm mt-1">Tidak ada siswa dengan kehadiran di bawah 80%</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>
