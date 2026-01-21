<script setup lang="ts">
/**
 * ReportCardPreview - HTML preview rapor yang responsive
 * menampilkan data nilai, sikap, kehadiran, dan catatan
 */
import GradePredikatBadge from './GradePredikatBadge.vue';

interface SubjectGrade {
    subject_id: number;
    subject_name: string;
    subject_code: string;
    final_grade: number;
    predikat: string;
    predikat_label: string;
    breakdown: {
        uh: { average: number; weight: number; count: number };
        uts: { score: number; weight: number };
        uas: { score: number; weight: number };
        praktik: { average: number; weight: number; count: number };
    };
}

interface Props {
    data: {
        student: {
            nama_lengkap: string;
            nis: string;
            nisn: string;
            jenis_kelamin: string;
            tempat_lahir: string;
            tanggal_lahir: string;
        };
        class: {
            nama_lengkap: string;
            tingkat: number;
            wali_kelas: string;
        };
        academic: {
            tahun_ajaran: string;
            semester: string;
            semester_label: string;
        };
        grades: SubjectGrade[];
        overall: {
            average: number;
            rank: number;
            total_students: number;
        };
        attitude: {
            spiritual_grade: string;
            spiritual_label: string;
            spiritual_description: string;
            social_grade: string;
            social_label: string;
            social_description: string;
            homeroom_notes: string;
        } | null;
        attendance: {
            hadir: number;
            sakit: number;
            izin: number;
            alpha: number;
            total_days: number;
        };
        generated_at: string;
    };
}

const props = defineProps<Props>();

/**
 * Get predikat color class
 */
const getPredikatColor = (predikat: string): string => {
    const colors: Record<string, string> = {
        'A': 'text-emerald-600 dark:text-emerald-400',
        'B': 'text-blue-600 dark:text-blue-400',
        'C': 'text-amber-600 dark:text-amber-400',
        'D': 'text-red-600 dark:text-red-400'
    };
    return colors[predikat] || colors['D'];
};
</script>

<template>
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
        <!-- Header -->
        <div class="bg-slate-50 dark:bg-zinc-800/50 px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-zinc-700">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 text-center">
                LAPORAN HASIL BELAJAR PESERTA DIDIK
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 text-center mt-1">
                Semester {{ data.academic.semester_label }} Tahun Pelajaran {{ data.academic.tahun_ajaran }}
            </p>
        </div>

        <!-- Student Info -->
        <div class="px-4 sm:px-6 py-4 bg-slate-50/50 dark:bg-zinc-800/30 border-b border-slate-200 dark:border-zinc-700">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <div class="flex gap-2">
                    <span class="text-slate-500 dark:text-slate-400 w-32 shrink-0">Nama Siswa</span>
                    <span class="text-slate-700 dark:text-slate-300 font-medium">: {{ data.student.nama_lengkap }}</span>
                </div>
                <div class="flex gap-2">
                    <span class="text-slate-500 dark:text-slate-400 w-32 shrink-0">Kelas</span>
                    <span class="text-slate-700 dark:text-slate-300 font-medium">: {{ data.class.nama_lengkap }}</span>
                </div>
                <div class="flex gap-2">
                    <span class="text-slate-500 dark:text-slate-400 w-32 shrink-0">NIS / NISN</span>
                    <span class="text-slate-700 dark:text-slate-300 font-medium">: {{ data.student.nis }} / {{ data.student.nisn }}</span>
                </div>
                <div class="flex gap-2">
                    <span class="text-slate-500 dark:text-slate-400 w-32 shrink-0">Wali Kelas</span>
                    <span class="text-slate-700 dark:text-slate-300 font-medium">: {{ data.class.wali_kelas }}</span>
                </div>
            </div>
        </div>

        <!-- Section A: Sikap -->
        <div v-if="data.attitude" class="px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-zinc-700">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4">
                A. Sikap
            </h3>

            <div class="space-y-4">
                <!-- Sikap Spiritual -->
                <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-4">
                    <div class="flex items-start justify-between gap-4 mb-2">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">1. Sikap Spiritual</span>
                        <GradePredikatBadge :predikat="data.attitude.spiritual_grade" size="sm" />
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        {{ data.attitude.spiritual_description }}
                    </p>
                </div>

                <!-- Sikap Sosial -->
                <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-4">
                    <div class="flex items-start justify-between gap-4 mb-2">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">2. Sikap Sosial</span>
                        <GradePredikatBadge :predikat="data.attitude.social_grade" size="sm" />
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        {{ data.attitude.social_description }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Section B: Pengetahuan dan Keterampilan -->
        <div class="px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-zinc-700">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4">
                B. Pengetahuan dan Keterampilan
            </h3>

            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="w-full text-sm min-w-[500px]">
                    <thead>
                        <tr class="bg-slate-100 dark:bg-zinc-800">
                            <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 w-8">No</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600 dark:text-slate-400">Mata Pelajaran</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 w-20">Nilai</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 w-20">Predikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr
                            v-for="(grade, idx) in data.grades"
                            :key="grade.subject_id"
                            class="hover:bg-slate-50 dark:hover:bg-zinc-800/30"
                        >
                            <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400">
                                {{ idx + 1 }}
                            </td>
                            <td class="px-3 py-2.5 text-slate-900 dark:text-slate-100 font-medium">
                                {{ grade.subject_name }}
                            </td>
                            <td class="px-3 py-2.5 text-center font-semibold text-slate-900 dark:text-slate-100">
                                {{ Math.round(grade.final_grade) }}
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                <span :class="['font-bold', getPredikatColor(grade.predikat)]">
                                    {{ grade.predikat }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="data.grades.length === 0">
                            <td colspan="4" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">
                                Belum ada data nilai
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Keterangan Predikat -->
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-3">
                Keterangan: A = Sangat Baik (90-100), B = Baik (80-89), C = Cukup (70-79), D = Kurang (&lt;70)
            </p>
        </div>

        <!-- Section C: Kehadiran -->
        <div class="px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-zinc-700">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4">
                C. Kehadiran
            </h3>

            <div class="grid grid-cols-3 gap-4">
                <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-3 text-center">
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ data.attendance.sakit }}</p>
                    <p class="text-xs text-red-500 dark:text-red-400 mt-1">Sakit (hari)</p>
                </div>
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-3 text-center">
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ data.attendance.izin }}</p>
                    <p class="text-xs text-amber-500 dark:text-amber-400 mt-1">Izin (hari)</p>
                </div>
                <div class="bg-slate-100 dark:bg-zinc-800 rounded-xl p-3 text-center">
                    <p class="text-2xl font-bold text-slate-600 dark:text-slate-400">{{ data.attendance.alpha }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tanpa Ket. (hari)</p>
                </div>
            </div>
        </div>

        <!-- Section D: Ringkasan -->
        <div class="px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-zinc-700">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4">
                D. Ringkasan
            </h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-4">
                    <p class="text-xs text-indigo-500 dark:text-indigo-400 mb-1">Rata-rata Nilai</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                        {{ data.overall.average.toFixed(2) }}
                    </p>
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4">
                    <p class="text-xs text-emerald-500 dark:text-emerald-400 mb-1">Peringkat</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ data.overall.rank }} <span class="text-sm font-normal">/ {{ data.overall.total_students }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Section E: Catatan Wali Kelas -->
        <div class="px-4 sm:px-6 py-4">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4">
                E. Catatan Wali Kelas
            </h3>

            <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4">
                <p
                    v-if="data.attitude?.homeroom_notes"
                    class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap"
                >
                    {{ data.attitude.homeroom_notes }}
                </p>
                <p
                    v-else
                    class="text-sm text-slate-500 dark:text-slate-400 italic"
                >
                    Tidak ada catatan
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-4 sm:px-6 py-3 bg-slate-50 dark:bg-zinc-800/50 text-center">
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Dicetak pada {{ data.generated_at }}
            </p>
        </div>
    </div>
</template>
