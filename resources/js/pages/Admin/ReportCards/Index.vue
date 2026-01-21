<script setup lang="ts">
/**
 * Admin Report Cards Index Page - Halaman list semua rapor
 * dengan filter berdasarkan tahun ajaran, semester, kelas, dan status
 */
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import {
    FileText,
    Filter,
    Search,
    Download,
    Plus,
    Eye,
    Unlock,
    Users,
    CheckCircle,
    Clock,
    FileCheck,
    X
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import ReportCardStatusBadge from '@/components/features/grades/ReportCardStatusBadge.vue';
import { index, generate, show, downloadZip } from '@/routes/admin/report-cards';

interface ReportCardEntry {
    id: number;
    student_id: number;
    student_name: string;
    student_nis: string;
    class_id: number;
    class_name: string;
    tahun_ajaran: string;
    semester: string;
    semester_label: string;
    status: string;
    status_label: string;
    status_color: string;
    average_score: number | null;
    rank: number | null;
    generated_at: string | null;
    generated_by: string;
    has_pdf: boolean;
}

interface PaginatedReportCards {
    data: ReportCardEntry[];
    links: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface ClassOption {
    id: number;
    name: string;
    student_count: number;
}

interface Statistics {
    total: number;
    draft: number;
    pending_approval: number;
    approved: number;
    released: number;
}

interface Props {
    reportCards: PaginatedReportCards;
    filters: {
        tahun_ajaran: string;
        semester: string | null;
        class_id: number | null;
        status: string | null;
        search: string | null;
    };
    classes: ClassOption[];
    statusOptions: Record<string, string>;
    statistics: Statistics;
    availableTahunAjaran: string[];
}

const props = defineProps<Props>();

const haptics = useHaptics();

const showFilters = ref(false);
const localFilters = ref({
    tahun_ajaran: props.filters.tahun_ajaran,
    semester: props.filters.semester || '',
    class_id: props.filters.class_id?.toString() || '',
    status: props.filters.status || '',
    search: props.filters.search || ''
});

/**
 * Apply filters dengan navigasi ke URL baru
 */
const applyFilters = () => {
    haptics.light();

    const params: Record<string, any> = {
        tahun_ajaran: localFilters.value.tahun_ajaran
    };

    if (localFilters.value.semester) params.semester = localFilters.value.semester;
    if (localFilters.value.class_id) params.class_id = localFilters.value.class_id;
    if (localFilters.value.status) params.status = localFilters.value.status;
    if (localFilters.value.search) params.search = localFilters.value.search;

    router.visit(index.url(params), {
        preserveState: true,
        preserveScroll: true
    });
};

/**
 * Reset filters ke default
 */
const resetFilters = () => {
    haptics.light();
    localFilters.value = {
        tahun_ajaran: props.filters.tahun_ajaran,
        semester: '',
        class_id: '',
        status: '',
        search: ''
    };
    router.visit(index.url({ tahun_ajaran: localFilters.value.tahun_ajaran }), {
        preserveState: true,
        preserveScroll: true
    });
};

/**
 * Check apakah ada filter yang aktif
 */
const hasActiveFilters = computed(() => {
    return localFilters.value.semester ||
        localFilters.value.class_id ||
        localFilters.value.status ||
        localFilters.value.search;
});

/**
 * Handle pagination
 */
const goToPage = (url: string | null) => {
    if (!url) return;
    haptics.light();
    router.visit(url, { preserveState: true, preserveScroll: true });
};

/**
 * Download ZIP untuk kelas yang dipilih
 */
const handleDownloadZip = () => {
    if (!localFilters.value.class_id || !localFilters.value.semester) {
        alert('Pilih kelas dan semester terlebih dahulu untuk download ZIP');
        return;
    }

    haptics.medium();
    window.open(downloadZip.url({
        class_id: localFilters.value.class_id,
        tahun_ajaran: localFilters.value.tahun_ajaran,
        semester: localFilters.value.semester
    }), '_blank');
};
</script>

<template>
    <AppLayout>
        <Head title="Manajemen Rapor" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-indigo-400 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/25 shrink-0">
                                <FileText class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Manajemen Rapor
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Generate dan kelola rapor siswa
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Motion :whileTap="{ scale: 0.97 }">
                                <Link
                                    :href="generate()"
                                    @click="haptics.light()"
                                    class="flex items-center gap-2 px-4 py-2.5 min-h-[44px] bg-linear-to-r from-indigo-500 to-purple-500 text-white rounded-xl hover:from-indigo-600 hover:to-purple-600 transition-all duration-200 shadow-lg shadow-indigo-500/30"
                                >
                                    <Plus class="w-5 h-5" />
                                    <span class="font-semibold hidden sm:inline">Generate Rapor</span>
                                    <span class="font-semibold sm:hidden">Generate</span>
                                </Link>
                            </Motion>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Statistics Cards -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
            >
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                <FileText class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ statistics.total }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Total Rapor</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-900/30 flex items-center justify-center">
                                <FileText class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ statistics.draft }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Draft</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <Clock class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ statistics.pending_approval }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pending</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <FileCheck class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ statistics.approved }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Approved</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <CheckCircle class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ statistics.released }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Released</p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Filters -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <!-- Filter Toggle -->
                    <button
                        type="button"
                        @click="showFilters = !showFilters"
                        class="w-full px-4 sm:px-6 py-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <Filter class="w-5 h-5 text-slate-500" />
                            <span class="font-medium text-slate-700 dark:text-slate-300">Filter</span>
                            <span
                                v-if="hasActiveFilters"
                                class="px-2 py-0.5 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400"
                            >
                                Aktif
                            </span>
                        </div>
                        <svg
                            :class="['w-5 h-5 text-slate-400 transition-transform', showFilters && 'rotate-180']"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Filter Content -->
                    <div v-show="showFilters" class="px-4 sm:px-6 pb-4 border-t border-slate-100 dark:border-zinc-800 pt-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                    Tahun Ajaran
                                </label>
                                <select
                                    v-model="localFilters.tahun_ajaran"
                                    class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option
                                        v-for="ta in availableTahunAjaran"
                                        :key="ta"
                                        :value="ta"
                                    >
                                        {{ ta }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                    Semester
                                </label>
                                <select
                                    v-model="localFilters.semester"
                                    class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="">Semua Semester</option>
                                    <option value="1">Semester 1 (Ganjil)</option>
                                    <option value="2">Semester 2 (Genap)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                    Kelas
                                </label>
                                <select
                                    v-model="localFilters.class_id"
                                    class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="">Semua Kelas</option>
                                    <option
                                        v-for="cls in classes"
                                        :key="cls.id"
                                        :value="cls.id.toString()"
                                    >
                                        {{ cls.name }} ({{ cls.student_count }} siswa)
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                    Status
                                </label>
                                <select
                                    v-model="localFilters.status"
                                    class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="">Semua Status</option>
                                    <option
                                        v-for="(label, key) in statusOptions"
                                        :key="key"
                                        :value="key"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                    Cari Siswa
                                </label>
                                <input
                                    v-model="localFilters.search"
                                    type="text"
                                    placeholder="Nama atau NIS..."
                                    class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>

                            <div class="flex items-end gap-2">
                                <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                                    <button
                                        type="button"
                                        @click="applyFilters"
                                        class="w-full px-4 py-2.5 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition-colors font-medium text-sm"
                                    >
                                        <Search class="w-4 h-4 inline-block mr-1" />
                                        Cari
                                    </button>
                                </Motion>
                                <Motion v-if="hasActiveFilters" :whileTap="{ scale: 0.97 }">
                                    <button
                                        type="button"
                                        @click="resetFilters"
                                        class="p-2.5 border border-slate-200 dark:border-zinc-700 rounded-lg hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors"
                                        title="Reset Filter"
                                    >
                                        <X class="w-4 h-4 text-slate-500" />
                                    </button>
                                </Motion>
                            </div>
                        </div>

                        <!-- Download ZIP Button -->
                        <div
                            v-if="localFilters.class_id && localFilters.semester"
                            class="mt-4 pt-4 border-t border-slate-100 dark:border-zinc-800"
                        >
                            <Motion :whileTap="{ scale: 0.97 }">
                                <button
                                    type="button"
                                    @click="handleDownloadZip"
                                    class="flex items-center gap-2 px-4 py-2.5 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors font-medium text-sm"
                                >
                                    <Download class="w-4 h-4" />
                                    Download Semua Rapor (ZIP)
                                </button>
                            </Motion>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Table -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-zinc-800/50">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Siswa
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Kelas
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Semester
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Rata-rata
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Ranking
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Generate
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                <tr v-if="reportCards.data.length === 0">
                                    <td colspan="8" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <FileText class="w-12 h-12 text-slate-300 dark:text-zinc-600" />
                                            <p class="text-slate-500 dark:text-slate-400">
                                                Belum ada rapor yang di-generate
                                            </p>
                                            <Link
                                                :href="generate()"
                                                class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm font-medium"
                                            >
                                                Generate Rapor Sekarang
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr
                                    v-for="rc in reportCards.data"
                                    :key="rc.id"
                                    class="hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-colors"
                                >
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">
                                                {{ rc.student_name }}
                                            </p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ rc.student_nis }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                        {{ rc.class_name }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400 text-xs font-medium">
                                            {{ rc.semester_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <ReportCardStatusBadge :status="rc.status" />
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            v-if="rc.average_score !== null"
                                            class="font-semibold text-slate-900 dark:text-slate-100"
                                        >
                                            {{ rc.average_score.toFixed(2) }}
                                        </span>
                                        <span v-else class="text-slate-400">-</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            v-if="rc.rank !== null"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 font-semibold text-sm"
                                        >
                                            {{ rc.rank }}
                                        </span>
                                        <span v-else class="text-slate-400">-</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            <p>{{ rc.generated_at || '-' }}</p>
                                            <p v-if="rc.generated_by !== '-'">oleh {{ rc.generated_by }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1">
                                            <Motion :whileTap="{ scale: 0.95 }">
                                                <Link
                                                    :href="show(rc.id)"
                                                    class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                                                    title="Preview"
                                                >
                                                    <Eye class="w-4 h-4 text-slate-600 dark:text-slate-400" />
                                                </Link>
                                            </Motion>
                                            <Motion
                                                v-if="rc.has_pdf"
                                                :whileTap="{ scale: 0.95 }"
                                            >
                                                <a
                                                    :href="`/admin/report-cards/${rc.id}/download`"
                                                    target="_blank"
                                                    class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                                                    title="Download PDF"
                                                >
                                                    <Download class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                                </a>
                                            </Motion>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="reportCards.last_page > 1"
                        class="px-4 py-3 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between"
                    >
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Menampilkan {{ reportCards.data.length }} dari {{ reportCards.total }} data
                        </p>
                        <div class="flex items-center gap-1">
                            <button
                                v-for="link in reportCards.links"
                                :key="link.label"
                                @click="goToPage(link.url)"
                                :disabled="!link.url"
                                :class="[
                                    'px-3 py-1.5 text-sm rounded-lg transition-colors',
                                    link.active
                                        ? 'bg-indigo-500 text-white'
                                        : link.url
                                            ? 'hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-600 dark:text-slate-400'
                                            : 'text-slate-300 dark:text-zinc-600 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
