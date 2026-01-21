<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { Plus, Calendar, Edit2, Filter, Search, X, Trash2, BookOpen, Lock } from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import Badge from '@/components/ui/Badge.vue';
import { ref, watch, computed } from 'vue';
import { index, create, edit, destroy } from '@/routes/teacher/grades';

/**
 * Halaman daftar penilaian yang sudah diinput oleh guru
 * dengan filter berdasarkan semester, mata pelajaran, dan kelas
 * serta action untuk edit dan hapus penilaian
 */

interface GradeRecord {
    id: number;
    class_id: number;
    subject_id: number;
    assessment_type: string;
    assessment_number: number | null;
    title: string;
    assessment_date: string;
    tahun_ajaran: string;
    semester: string;
    student_count: number;
    average_score: number;
    min_score: number;
    max_score: number;
    is_locked: boolean;
    class: {
        id: number;
        nama_lengkap: string;
    };
    subject: {
        id: number;
        nama_mapel: string;
    };
}

interface SchoolClass {
    id: number;
    tingkat: number;
    nama: string;
    nama_lengkap: string;
    tahun_ajaran: string;
    jumlah_siswa: number;
    is_wali_kelas: boolean;
}

interface Subject {
    id: number;
    kode_mapel: string;
    nama_mapel: string;
}

interface PaginatedGrades {
    data: GradeRecord[];
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
    grades: PaginatedGrades;
    classes: SchoolClass[];
    subjects: Subject[];
    filters: {
        semester: string | null;
        subject_id: number | null;
        class_id: number | null;
        tahun_ajaran: string;
        search: string | null;
    };
    summary: {
        total_assessments: number;
        total_students: number;
        average_score: number | null;
    };
    assessmentTypes: Record<string, string>;
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

const filters = ref({
    semester: props.filters.semester,
    subject_id: props.filters.subject_id,
    class_id: props.filters.class_id,
    tahun_ajaran: props.filters.tahun_ajaran,
    search: props.filters.search,
});

const showFilters = ref(false);
const searchQuery = ref(props.filters.search || '');

/**
 * Format tanggal ke format Indonesia
 */
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

/**
 * Get badge variant berdasarkan assessment type
 */
const getAssessmentTypeBadge = (type: string) => {
    const badges: Record<string, { variant: string; label: string }> = {
        UH: { variant: 'info', label: 'UH' },
        UTS: { variant: 'warning', label: 'UTS' },
        UAS: { variant: 'danger', label: 'UAS' },
        PRAKTIK: { variant: 'success', label: 'Praktik' },
        PROYEK: { variant: 'secondary', label: 'Proyek' },
    };
    return badges[type] || { variant: 'secondary', label: type };
};

/**
 * Get predikat berdasarkan nilai rata-rata
 */
const getPredikat = (score: number) => {
    if (score >= 90) return { grade: 'A', color: 'text-emerald-600 dark:text-emerald-400' };
    if (score >= 80) return { grade: 'B', color: 'text-blue-600 dark:text-blue-400' };
    if (score >= 70) return { grade: 'C', color: 'text-yellow-600 dark:text-yellow-400' };
    return { grade: 'D', color: 'text-red-600 dark:text-red-400' };
};

/**
 * Apply filters ke server
 */
const applyFilters = () => {
    haptics.light();
    router.get(
        index().url,
        {
            semester: filters.value.semester,
            subject_id: filters.value.subject_id,
            class_id: filters.value.class_id,
            tahun_ajaran: filters.value.tahun_ajaran,
            search: filters.value.search,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

/**
 * Reset semua filters
 */
const resetFilters = () => {
    haptics.light();
    filters.value = {
        semester: null,
        subject_id: null,
        class_id: null,
        tahun_ajaran: props.filters.tahun_ajaran,
        search: null,
    };
    searchQuery.value = '';
    applyFilters();
};

/**
 * Clear search query
 */
const clearSearch = () => {
    haptics.light();
    searchQuery.value = '';
    filters.value.search = null;
};

// Watch search query dan sync dengan filters
watch(searchQuery, (newValue) => {
    filters.value.search = newValue || null;
});

// Watch filters and apply automatically (debounced)
let filterTimeout: ReturnType<typeof setTimeout> | null = null;
watch(filters, () => {
    if (filterTimeout) {
        clearTimeout(filterTimeout);
    }
    filterTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
}, { deep: true });

/**
 * Navigate ke halaman edit
 */
const editGrade = (record: GradeRecord) => {
    haptics.light();
    router.get(edit(record.id).url);
};

/**
 * Konfirmasi dan hapus penilaian
 */
const confirmDelete = async (record: GradeRecord) => {
    haptics.medium();
    
    const confirmed = await modal.confirm(
        'Hapus Penilaian',
        `Apakah Anda yakin ingin menghapus penilaian "${record.title}" untuk kelas ${record.class.nama_lengkap}? Semua nilai siswa dalam penilaian ini akan dihapus.`
    );

    if (confirmed) {
        router.delete(destroy(record.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                haptics.success();
                modal.success('Penilaian berhasil dihapus');
            },
            onError: () => {
                haptics.error();
                modal.error('Gagal menghapus penilaian');
            },
        });
    }
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
                            <p class="mt-2 text-gray-600 dark:text-gray-400">Kelola penilaian UH, UTS, UAS, Praktik, dan Proyek</p>
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
                                    :href="create()"
                                    @click="haptics.light()"
                                    class="px-5 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold
                                           flex items-center gap-2 shadow-sm shadow-emerald-500/25
                                           transition-colors duration-150"
                                >
                                    <Plus :size="20" />
                                    <span class="hidden sm:inline">Input Nilai</span>
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
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filter Penilaian</h3>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Semester
                                </label>
                                <select
                                    v-model="filters.semester"
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl
                                           text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                >
                                    <option :value="null">Semua Semester</option>
                                    <option value="1">Semester 1 (Ganjil)</option>
                                    <option value="2">Semester 2 (Genap)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Kelas
                                </label>
                                <select
                                    v-model="filters.class_id"
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
                                    Mata Pelajaran
                                </label>
                                <select
                                    v-model="filters.subject_id"
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl
                                           text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                >
                                    <option :value="null">Semua Mapel</option>
                                    <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                        {{ subject.nama_mapel }}
                                    </option>
                                </select>
                            </div>

                            <div class="flex items-end">
                                <button
                                    @click="resetFilters"
                                    class="w-full px-6 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700
                                           text-gray-700 dark:text-gray-300 rounded-xl font-semibold transition-colors duration-150"
                                >
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Summary Stats -->
                <div class="grid gap-6 sm:grid-cols-3 mb-6">
                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-slate-600 dark:text-zinc-400">Total Penilaian</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ summary.total_assessments }}</p>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                    >
                        <div class="bg-blue-50 dark:bg-blue-950/30 rounded-2xl border border-blue-200 dark:border-blue-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Siswa Dinilai</p>
                            <p class="mt-2 text-3xl font-bold text-blue-900 dark:text-blue-100">{{ summary.total_students }}</p>
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                        :animate="{ opacity: 1, y: 0, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                    >
                        <div class="bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-800 shadow-sm p-6">
                            <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Rata-rata Nilai</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-900 dark:text-emerald-100">
                                {{ summary.average_score ?? '-' }}
                            </p>
                        </div>
                    </Motion>
                </div>

                <!-- Grades Table -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-200 dark:border-zinc-800 space-y-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Daftar Penilaian</h2>
                                <p class="text-sm text-slate-600 dark:text-zinc-400 mt-1">
                                    Klik "Edit" untuk mengubah nilai atau "Hapus" untuk menghapus penilaian
                                </p>
                            </div>

                            <!-- Search Input -->
                            <div class="relative">
                                <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="20" />
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari berdasarkan judul penilaian..."
                                    class="w-full h-[52px] pl-12 pr-12 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-900 dark:text-white placeholder-slate-400
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 focus:bg-white
                                           transition-all duration-150"
                                />
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        v-if="searchQuery"
                                        @click="clearSearch"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300
                                               transition-colors duration-150"
                                    >
                                        <X :size="20" />
                                    </button>
                                </Motion>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                                <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Penilaian
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Kelas
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Mapel
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Jenis
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Siswa
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Rata-rata
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-800">
                                    <tr v-if="grades.data.length === 0">
                                        <td colspan="8" class="px-6 py-12 text-center">
                                            <BookOpen :size="48" class="mx-auto text-gray-300 dark:text-zinc-700 mb-3" />
                                            <p class="text-gray-500 dark:text-gray-400">Belum ada data penilaian</p>
                                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                                                Mulai input nilai untuk melihat daftar penilaian
                                            </p>
                                            <Link
                                                :href="create()"
                                                class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-medium transition-colors"
                                            >
                                                <Plus :size="16" />
                                                Input Nilai Baru
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="record in grades.data"
                                        :key="record.id"
                                        class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                                    >
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <Lock v-if="record.is_locked" :size="14" class="text-gray-400" />
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white">{{ record.title }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">Semester {{ record.semester }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                            {{ record.class?.nama_lengkap }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                            {{ record.subject?.nama_mapel }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <Badge :variant="(getAssessmentTypeBadge(record.assessment_type).variant as any)">
                                                {{ getAssessmentTypeBadge(record.assessment_type).label }}
                                                {{ record.assessment_number ? record.assessment_number : '' }}
                                            </Badge>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white font-semibold">
                                            {{ record.student_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex flex-col items-center">
                                                <span class="font-semibold text-gray-900 dark:text-white">
                                                    {{ Number(record.average_score).toFixed(1) }}
                                                </span>
                                                <span class="text-xs font-medium" :class="getPredikat(Number(record.average_score)).color">
                                                    {{ getPredikat(Number(record.average_score)).grade }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                            {{ formatDate(record.assessment_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <div class="flex items-center justify-end gap-2">
                                                <button
                                                    v-if="!record.is_locked"
                                                    @click="editGrade(record)"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/30 dark:hover:bg-blue-950/50
                                                           text-blue-600 dark:text-blue-400 rounded-lg font-medium transition-colors"
                                                >
                                                    <Edit2 :size="14" />
                                                    <span>Edit</span>
                                                </button>
                                                <button
                                                    v-if="!record.is_locked"
                                                    @click="confirmDelete(record)"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 hover:bg-red-100 dark:bg-red-950/30 dark:hover:bg-red-950/50
                                                           text-red-600 dark:text-red-400 rounded-lg font-medium transition-colors"
                                                >
                                                    <Trash2 :size="14" />
                                                    <span>Hapus</span>
                                                </button>
                                                <span
                                                    v-if="record.is_locked"
                                                    class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1"
                                                >
                                                    <Lock :size="12" />
                                                    Terkunci
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="grades.links.length > 3" class="border-t border-gray-200 dark:border-zinc-800 px-6 py-4 bg-gray-50 dark:bg-zinc-800/50">
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Halaman {{ grades.current_page }} dari {{ grades.last_page }}
                                    <span class="text-gray-400 dark:text-gray-500 ml-2">({{ grades.total }} total)</span>
                                </p>
                                <div class="flex gap-2">
                                    <Link
                                        v-for="link in grades.links"
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
                                    ><span v-html="link.label" /></Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>
