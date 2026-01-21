<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { Plus, Heart, Filter, Search, X, AlertCircle, UserX } from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';
import Badge from '@/components/ui/Badge.vue';
import { ref, watch } from 'vue';
import { index, create } from '@/routes/teacher/attitude-grades';

/**
 * Halaman daftar nilai sikap yang sudah diinput oleh wali kelas
 * dengan filter berdasarkan semester
 * Hanya bisa diakses oleh guru yang menjadi wali kelas
 */

interface AttitudeGradeRecord {
    id: number;
    student_id: number;
    class_id: number;
    tahun_ajaran: string;
    semester: string;
    spiritual_grade: string;
    spiritual_description: string | null;
    social_grade: string;
    social_description: string | null;
    homeroom_notes: string | null;
    created_at: string;
    student: {
        id: number;
        nis: string;
        nama_lengkap: string;
    };
}

interface SchoolClass {
    id: number;
    nama_lengkap: string;
    tahun_ajaran: string;
}

interface PaginatedAttitudeGrades {
    data: AttitudeGradeRecord[];
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
    isWaliKelas: boolean;
    class: SchoolClass | null;
    attitudeGrades: PaginatedAttitudeGrades | AttitudeGradeRecord[];
    filters: {
        semester: string | null;
        tahun_ajaran: string | null;
        search: string | null;
    };
    summary?: {
        total_students: number;
        spiritual_distribution: Record<string, number>;
        social_distribution: Record<string, number>;
    };
    gradeOptions: Record<string, string>;
    semesters?: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const haptics = useHaptics();

const filters = ref({
    semester: props.filters.semester,
    tahun_ajaran: props.filters.tahun_ajaran,
    search: props.filters.search,
});

const showFilters = ref(false);
const searchQuery = ref(props.filters.search || '');

/**
 * Get badge variant berdasarkan grade
 */
const getGradeBadge = (grade: string) => {
    const badges: Record<string, { variant: string; color: string }> = {
        A: { variant: 'success', color: 'text-emerald-600 dark:text-emerald-400' },
        B: { variant: 'info', color: 'text-blue-600 dark:text-blue-400' },
        C: { variant: 'warning', color: 'text-yellow-600 dark:text-yellow-400' },
        D: { variant: 'danger', color: 'text-red-600 dark:text-red-400' },
    };
    return badges[grade] || { variant: 'secondary', color: 'text-gray-600' };
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
 * Check if attitudeGrades is paginated
 */
const isPaginated = (data: any): data is PaginatedAttitudeGrades => {
    return data && 'data' in data && 'links' in data;
};

const gradesData = isPaginated(props.attitudeGrades) ? props.attitudeGrades.data : props.attitudeGrades;
const gradesLinks = isPaginated(props.attitudeGrades) ? props.attitudeGrades.links : [];
const gradesPagination = isPaginated(props.attitudeGrades) ? props.attitudeGrades : null;
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
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                <template v-if="isWaliKelas && props.class">
                                    Nilai sikap spiritual dan sosial siswa kelas {{ props.class.nama_lengkap }}
                                </template>
                                <template v-else>
                                    Input nilai sikap spiritual dan sosial siswa
                                </template>
                            </p>
                        </div>

                        <div v-if="isWaliKelas" class="flex items-center gap-3">
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
                                    <span class="hidden sm:inline">Input Nilai Sikap</span>
                                </Link>
                            </Motion>
                        </div>
                    </div>
                </div>
            </Motion>

            <div class="mx-auto max-w-7xl px-6 py-8">
                <!-- Not Wali Kelas Warning -->
                <div v-if="!isWaliKelas" class="bg-yellow-50 dark:bg-yellow-950/30 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-8 text-center">
                    <UserX :size="48" class="mx-auto text-yellow-500 mb-4" />
                    <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-2">
                        Akses Terbatas
                    </h3>
                    <p class="text-yellow-700 dark:text-yellow-300 max-w-md mx-auto">
                        Fitur input nilai sikap hanya tersedia untuk guru yang menjadi wali kelas.
                        Hubungi admin jika Anda seharusnya menjadi wali kelas.
                    </p>
                </div>

                <template v-else>
                    <!-- Filters Panel -->
                    <Motion
                        v-if="showFilters"
                        :initial="{ opacity: 0, height: 0 }"
                        :animate="{ opacity: 1, height: 'auto' }"
                        :exit="{ opacity: 0, height: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filter Nilai Sikap</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                                        <option v-for="sem in semesters" :key="sem.value" :value="sem.value">
                                            {{ sem.label }}
                                        </option>
                                    </select>
                                </div>

                                <div class="md:col-span-2 flex items-end">
                                    <button
                                        @click="resetFilters"
                                        class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700
                                               text-gray-700 dark:text-gray-300 rounded-xl font-semibold transition-colors duration-150"
                                    >
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <!-- Summary Stats -->
                    <div v-if="summary" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-6">
                        <Motion
                            :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                            :animate="{ opacity: 1, y: 0, scale: 1 }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                        >
                            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                                <p class="text-sm font-medium text-slate-600 dark:text-zinc-400">Total Siswa Dinilai</p>
                                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ summary.total_students }}</p>
                            </div>
                        </Motion>

                        <Motion
                            :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                            :animate="{ opacity: 1, y: 0, scale: 1 }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                        >
                            <div class="bg-pink-50 dark:bg-pink-950/30 rounded-2xl border border-pink-200 dark:border-pink-800 shadow-sm p-6">
                                <p class="text-sm font-medium text-pink-600 dark:text-pink-400">Spiritual (A/B/C/D)</p>
                                <div class="mt-2 flex items-center gap-2 text-lg font-bold">
                                    <span class="text-emerald-600">{{ summary.spiritual_distribution.A }}</span>/
                                    <span class="text-blue-600">{{ summary.spiritual_distribution.B }}</span>/
                                    <span class="text-yellow-600">{{ summary.spiritual_distribution.C }}</span>/
                                    <span class="text-red-600">{{ summary.spiritual_distribution.D }}</span>
                                </div>
                            </div>
                        </Motion>

                        <Motion
                            :initial="{ opacity: 0, y: 20, scale: 0.95 }"
                            :animate="{ opacity: 1, y: 0, scale: 1 }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                        >
                            <div class="bg-violet-50 dark:bg-violet-950/30 rounded-2xl border border-violet-200 dark:border-violet-800 shadow-sm p-6">
                                <p class="text-sm font-medium text-violet-600 dark:text-violet-400">Sosial (A/B/C/D)</p>
                                <div class="mt-2 flex items-center gap-2 text-lg font-bold">
                                    <span class="text-emerald-600">{{ summary.social_distribution.A }}</span>/
                                    <span class="text-blue-600">{{ summary.social_distribution.B }}</span>/
                                    <span class="text-yellow-600">{{ summary.social_distribution.C }}</span>/
                                    <span class="text-red-600">{{ summary.social_distribution.D }}</span>
                                </div>
                            </div>
                        </Motion>
                    </div>

                    <!-- Attitude Grades Table -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.2 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-slate-200 dark:border-zinc-800 space-y-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Daftar Nilai Sikap</h2>
                                    <p class="text-sm text-slate-600 dark:text-zinc-400 mt-1">
                                        Kelas {{ props.class?.nama_lengkap }} | {{ props.class?.tahun_ajaran }}
                                    </p>
                                </div>

                                <!-- Search Input -->
                                <div class="relative">
                                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="20" />
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Cari siswa berdasarkan NIS atau nama..."
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
                                                Siswa
                                            </th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Semester
                                            </th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-pink-600 dark:text-pink-400 uppercase tracking-wider">
                                                Spiritual
                                            </th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-violet-600 dark:text-violet-400 uppercase tracking-wider">
                                                Sosial
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Catatan Wali Kelas
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-800">
                                        <tr v-if="gradesData.length === 0">
                                            <td colspan="5" class="px-6 py-12 text-center">
                                                <Heart :size="48" class="mx-auto text-gray-300 dark:text-zinc-700 mb-3" />
                                                <p class="text-gray-500 dark:text-gray-400">Belum ada data nilai sikap</p>
                                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                                                    Mulai input nilai sikap untuk melihat daftar
                                                </p>
                                                <Link
                                                    :href="create()"
                                                    class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-medium transition-colors"
                                                >
                                                    <Plus :size="16" />
                                                    Input Nilai Sikap
                                                </Link>
                                            </td>
                                        </tr>
                                        <tr
                                            v-for="record in gradesData"
                                            :key="record.id"
                                            class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                                        >
                                            <td class="px-6 py-4">
                                                <p class="font-medium text-gray-900 dark:text-white">{{ record.student.nama_lengkap }}</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">NIS: {{ record.student.nis }}</p>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <Badge variant="secondary">
                                                    Semester {{ record.semester }}
                                                </Badge>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex flex-col items-center">
                                                    <span class="text-xl font-bold" :class="getGradeBadge(record.spiritual_grade).color">
                                                        {{ record.spiritual_grade }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ gradeOptions[record.spiritual_grade] }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex flex-col items-center">
                                                    <span class="text-xl font-bold" :class="getGradeBadge(record.social_grade).color">
                                                        {{ record.social_grade }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ gradeOptions[record.social_grade] }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate">
                                                {{ record.homeroom_notes || '-' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div v-if="gradesPagination && gradesLinks.length > 3" class="border-t border-gray-200 dark:border-zinc-800 px-6 py-4 bg-gray-50 dark:bg-zinc-800/50">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Halaman {{ gradesPagination.current_page }} dari {{ gradesPagination.last_page }}
                                        <span class="text-gray-400 dark:text-gray-500 ml-2">({{ gradesPagination.total }} total)</span>
                                    </p>
                                    <div class="flex gap-2">
                                        <Link
                                            v-for="link in gradesLinks"
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
                </template>
            </div>
        </div>
    </AppLayout>
</template>
