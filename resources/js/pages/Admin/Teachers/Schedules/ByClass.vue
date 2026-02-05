<script setup lang="ts">
/**
 * ByClass Page - Matrix view jadwal per kelas
 * Menampilkan jadwal pelajaran satu kelas dalam format matrix (hari x waktu)
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, GraduationCap, FileDown, ChevronDown } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import ScheduleMatrix from '@/components/features/schedules/ScheduleMatrix.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import { index as schedulesIndex, edit as editSchedule } from '@/routes/admin/teachers/schedules';

interface Schedule {
    id: number;
    hari: { value: string; label?: string } | string;
    jam_mulai: string;
    jam_selesai: string;
    ruangan: string | null;
    time_range: string;
    teacher: {
        id: number;
        nama_lengkap: string;
    };
    subject: {
        id: number;
        kode_mapel: string;
        nama_mapel: string;
    };
}

interface SchoolClass {
    id: number;
    tingkat: number;
    nama: string;
    wali_kelas_id: number | null;
    kapasitas: number;
    tahun_ajaran: string;
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

interface Props {
    schoolClass: SchoolClass;
    schedules: Schedule[];
    academicYear: AcademicYear | null;
    academicYears: AcademicYear[];
    hariOptions: HariOption[];
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

const selectedAcademicYearId = ref(props.academicYear?.id || '');
const showYearDropdown = ref(false);

// Total jam pelajaran per minggu
const totalHours = computed(() => {
    let totalMinutes = 0;
    props.schedules.forEach(schedule => {
        const start = schedule.jam_mulai.split(':').map(Number);
        const end = schedule.jam_selesai.split(':').map(Number);
        const startMinutes = start[0] * 60 + start[1];
        const endMinutes = end[0] * 60 + end[1];
        totalMinutes += endMinutes - startMinutes;
    });
    return Math.round(totalMinutes / 60 * 10) / 10;
});

// Jumlah mata pelajaran unik
const uniqueSubjects = computed(() => {
    const subjectIds = new Set(props.schedules.map(s => s.subject.id));
    return subjectIds.size;
});

// Jumlah guru unik yang mengajar
const uniqueTeachers = computed(() => {
    const teacherIds = new Set(props.schedules.map(s => s.teacher.id));
    return teacherIds.size;
});

/**
 * Handle perubahan tahun ajaran
 */
const changeAcademicYear = (yearId: string) => {
    haptics.selection();
    showYearDropdown.value = false;
    router.get(`/admin/teachers/schedules/by-class/${props.schoolClass.id}`, {
        academic_year_id: yearId || undefined
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

/**
 * Handle klik pada jadwal untuk navigasi ke edit
 */
const handleScheduleClick = (schedule: Schedule) => {
    haptics.light();
    router.visit(editSchedule(schedule.id).url);
};

/**
 * Handle export PDF
 */
const handleExportPdf = () => {
    haptics.medium();
    const params = new URLSearchParams({
        type: 'class',
        id: props.schoolClass.id.toString()
    });
    if (props.academicYear) {
        params.append('academic_year_id', props.academicYear.id.toString());
    }
    window.location.href = `/admin/teachers/schedules/export-pdf?${params.toString()}`;
};
</script>

<template>
    <AppLayout :title="`Jadwal - Kelas ${schoolClass.tingkat}${schoolClass.nama}`">
        <Head :title="`Jadwal - Kelas ${schoolClass.tingkat}${schoolClass.nama}`" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col gap-4">
                        <!-- Back Button & Title -->
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <Link
                                    :href="schedulesIndex().url"
                                    @click="haptics.light()"
                                    class="p-2 -ml-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                >
                                    <ArrowLeft class="w-5 h-5" />
                                </Link>
                                <div class="w-12 h-12 rounded-xl bg-linear-to-br from-emerald-400 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/25 shrink-0">
                                    <GraduationCap class="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                        Kelas {{ schoolClass.tingkat }}{{ schoolClass.nama }}
                                    </h1>
                                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
                                        Jadwal Pelajaran
                                        <span class="mx-1">•</span>
                                        Kapasitas {{ schoolClass.kapasitas }} siswa
                                    </p>
                                </div>
                            </div>

                            <!-- Export Button -->
                            <Motion :whileTap="{ scale: 0.97 }">
                                <button
                                    @click="handleExportPdf"
                                    class="flex items-center gap-2 px-4 py-2.5 text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded-xl transition-colors"
                                >
                                    <FileDown class="w-4 h-4" />
                                    <span class="hidden sm:inline">Export PDF</span>
                                </button>
                            </Motion>
                        </div>

                        <!-- Stats & Year Selector -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-slate-200 dark:border-zinc-700">
                            <!-- Stats -->
                            <div class="flex items-center gap-6">
                                <div>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ schedules.length }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Total Jadwal</p>
                                </div>
                                <div class="h-10 w-px bg-slate-200 dark:bg-zinc-700"></div>
                                <div>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ uniqueSubjects }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Mata Pelajaran</p>
                                </div>
                                <div class="h-10 w-px bg-slate-200 dark:bg-zinc-700"></div>
                                <div>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ uniqueTeachers }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Guru</p>
                                </div>
                                <div class="h-10 w-px bg-slate-200 dark:bg-zinc-700"></div>
                                <div>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ totalHours }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Jam/Minggu</p>
                                </div>
                            </div>

                            <!-- Year Selector -->
                            <div class="relative">
                                <button
                                    @click="showYearDropdown = !showYearDropdown"
                                    class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded-xl transition-colors"
                                >
                                    <Calendar class="w-4 h-4 text-slate-500" />
                                    <span class="text-slate-700 dark:text-slate-300">
                                        {{ academicYear?.name || 'Pilih Tahun Ajaran' }}
                                    </span>
                                    <ChevronDown class="w-4 h-4 text-slate-400" />
                                </button>

                                <!-- Dropdown -->
                                <div
                                    v-if="showYearDropdown"
                                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-slate-200 dark:border-zinc-700 z-10 overflow-hidden"
                                >
                                    <button
                                        v-for="year in academicYears"
                                        :key="year.id"
                                        @click="changeAcademicYear(year.id.toString())"
                                        class="w-full px-4 py-2.5 text-left text-sm hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors"
                                        :class="academicYear?.id === year.id ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300'"
                                    >
                                        {{ year.name }}
                                        <span v-if="year.is_active" class="text-xs text-emerald-600 dark:text-emerald-400 ml-1">(Aktif)</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Matrix -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <ScheduleMatrix
                    :schedules="schedules"
                    :hari-options="hariOptions"
                    :show-teacher="true"
                    :show-class="false"
                    @click-schedule="handleScheduleClick"
                />
            </Motion>
        </div>
    </AppLayout>
</template>
