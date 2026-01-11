<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Plus, Calendar, Filter, BookOpen } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import AttendanceStatusBadge from '@/components/features/attendance/AttendanceStatusBadge.vue';
import { useHaptics } from '@/composables/useHaptics';

interface SubjectAttendance {
    id: number;
    tanggal: string;
    jam_ke: number;
    status: 'H' | 'I' | 'S' | 'A';
    student: {
        nama_lengkap: string;
        nis: string;
    };
    subject: {
        nama_mapel: string;
    };
    class: {
        nama_lengkap: string;
    };
}

interface Props {
    title: string;
    attendances: {
        data: SubjectAttendance[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        subject_id?: number;
        class_id?: number;
        date?: string;
    };
    subjects: Array<{ id: number; nama_mapel: string }>;
    classes: Array<{ id: number; nama_lengkap: string }>;
}

const props = defineProps<Props>();

const haptics = useHaptics();

// State
const filterForm = ref({
    subject_id: props.filters.subject_id || '',
    class_id: props.filters.class_id || '',
    date: props.filters.date || '',
});

const showFilters = ref(false);

// Computed
const hasFilters = computed(() => {
    return filterForm.value.subject_id || filterForm.value.class_id || filterForm.value.date;
});

const groupedAttendances = computed(() => {
    const groups = new Map();
    
    props.attendances.data.forEach(attendance => {
        const key = `${attendance.tanggal}-${attendance.jam_ke}-${attendance.subject.nama_mapel}-${attendance.class.nama_lengkap}`;
        if (!groups.has(key)) {
            groups.set(key, {
                tanggal: attendance.tanggal,
                jam_ke: attendance.jam_ke,
                subject: attendance.subject.nama_mapel,
                class: attendance.class.nama_lengkap,
                attendances: [],
            });
        }
        groups.get(key).attendances.push(attendance);
    });
    
    return Array.from(groups.values());
});

// Methods
const applyFilters = () => {
    haptics.light();
    router.get('/teacher/attendance/subject/history', filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    haptics.light();
    filterForm.value = {
        subject_id: '',
        class_id: '',
        date: '',
    };
    router.get('/teacher/attendance/subject/history', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const createNew = () => {
    haptics.light();
    router.visit('/teacher/attendance/subject');
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const getSummary = (attendances: SubjectAttendance[]) => {
    return {
        total: attendances.length,
        hadir: attendances.filter(a => a.status === 'H').length,
        izin: attendances.filter(a => a.status === 'I').length,
        sakit: attendances.filter(a => a.status === 'S').length,
        alpha: attendances.filter(a => a.status === 'A').length,
    };
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ title }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Riwayat presensi per mata pelajaran
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Motion
                        tag="button"
                        :animate="{ scale: showFilters ? 0.95 : 1 }"
                        @click="showFilters = !showFilters"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <Filter :size="18" />
                        Filter
                        <span v-if="hasFilters" class="ml-1 px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full text-xs">
                            Aktif
                        </span>
                    </Motion>

                    <Motion
                        tag="button"
                        :whileTap="{ scale: 0.95 }"
                        @click="createNew"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors"
                    >
                        <Plus :size="18" />
                        Input Baru
                    </Motion>
                </div>
            </div>

            <!-- Filters Panel -->
            <Motion
                v-if="showFilters"
                :initial="{ opacity: 0, height: 0 }"
                :animate="{ opacity: 1, height: 'auto' }"
                :exit="{ opacity: 0, height: 0 }"
                class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700"
            >
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mata Pelajaran
                        </label>
                        <select
                            v-model="filterForm.subject_id"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Semua Mata Pelajaran</option>
                            <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                {{ subject.nama_mapel }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Kelas
                        </label>
                        <select
                            v-model="filterForm.class_id"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Semua Kelas</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                {{ cls.nama_lengkap }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal
                        </label>
                        <input
                            v-model="filterForm.date"
                            type="date"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors"
                    >
                        Terapkan Filter
                    </button>
                    <button
                        v-if="hasFilters"
                        @click="clearFilters"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium transition-colors"
                    >
                        Reset Filter
                    </button>
                </div>
            </Motion>

            <!-- Grouped Attendance Records -->
            <div class="space-y-4">
                <Motion
                    v-for="(group, index) in groupedAttendances"
                    :key="index"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ delay: index * 0.05 }"
                    class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                >
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <Calendar :size="18" class="text-gray-400" />
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ formatDate(group.tanggal) }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <BookOpen :size="18" class="text-gray-400" />
                                    <span class="text-gray-600 dark:text-gray-400">
                                        {{ group.subject }} • {{ group.class }} • Jam ke-{{ group.jam_ke }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ getSummary(group.attendances).hadir }}/{{ getSummary(group.attendances).total }} hadir
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div
                                v-for="attendance in group.attendances"
                                :key="attendance.id"
                                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg"
                            >
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ attendance.student.nama_lengkap }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ attendance.student.nis }}
                                    </p>
                                </div>
                                <AttendanceStatusBadge :status="attendance.status" />
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-6 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 dark:text-gray-400">Hadir:</span>
                                    <span class="font-medium text-green-600 dark:text-green-400">
                                        {{ getSummary(group.attendances).hadir }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 dark:text-gray-400">Izin:</span>
                                    <span class="font-medium text-blue-600 dark:text-blue-400">
                                        {{ getSummary(group.attendances).izin }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 dark:text-gray-400">Sakit:</span>
                                    <span class="font-medium text-yellow-600 dark:text-yellow-400">
                                        {{ getSummary(group.attendances).sakit }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 dark:text-gray-400">Alpha:</span>
                                    <span class="font-medium text-red-600 dark:text-red-400">
                                        {{ getSummary(group.attendances).alpha }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <div v-if="groupedAttendances.length === 0" class="bg-white dark:bg-gray-800 rounded-xl p-12 border border-gray-200 dark:border-gray-700 text-center">
                    <Calendar :size="48" class="mx-auto mb-3 text-gray-400 opacity-50" />
                    <p class="text-lg font-medium text-gray-500 dark:text-gray-400">Belum ada riwayat presensi</p>
                    <p class="text-sm text-gray-400 mt-1">Mulai input presensi per mata pelajaran</p>
                    <button
                        @click="createNew"
                        class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors"
                    >
                        <Plus :size="18" />
                        Input Presensi
                    </button>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="attendances.last_page > 1" class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Menampilkan {{ (attendances.current_page - 1) * attendances.per_page + 1 }} - 
                        {{ Math.min(attendances.current_page * attendances.per_page, attendances.total) }} 
                        dari {{ attendances.total }} data
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            v-for="page in attendances.last_page"
                            :key="page"
                            @click="router.get(`/teacher/attendance/subject/history?page=${page}`, filterForm)"
                            :class="[
                                'px-3 py-1 rounded-lg text-sm font-medium transition-colors',
                                page === attendances.current_page
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                            ]"
                        >
                            {{ page }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
