<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import AttendanceStatusBadge from '@/components/features/attendance/AttendanceStatusBadge.vue';
import { Save, Calendar, Users, AlertCircle, Search, X } from 'lucide-vue-next';
import type { Student } from '@/types/student';

/**
 * Form input presensi harian untuk guru
 * dengan radio selection H/I/S/A per siswa dan summary counter
 */

interface SchoolClass {
    id: number;
    tingkat: number;
    nama: string;
    nama_lengkap: string;
    tahun_ajaran: string;
    jumlah_siswa: number;
}

interface ExistingAttendance {
    id: number;
    student_id: number;
    student_nama: string;
    student_nis: string;
    status: 'H' | 'I' | 'S' | 'A';
    keterangan?: string;
}

interface Props {
    title: string;
    classes: SchoolClass[];
    existingAttendance?: ExistingAttendance[] | null;
    editMode?: {
        kelas_id: number | null;
        tanggal: string | null;
    };
}

const props = defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

const selectedClassId = ref<number | null>(props.editMode?.kelas_id || null);
const students = ref<Student[]>([]);
const loadingStudents = ref(false);
const isEditMode = ref(!!props.existingAttendance);
const searchQuery = ref<string>('');

/**
 * Form data untuk submission
 */
const form = useForm({
    class_id: props.editMode?.kelas_id || null,
    tanggal: props.editMode?.tanggal || new Date().toISOString().split('T')[0],
    attendances: [] as Array<{
        student_id: number;
        status: 'H' | 'I' | 'S' | 'A';
        keterangan?: string;
    }>
});

/**
 * Filtered students berdasarkan search query
 * dengan filtering by NIS atau nama siswa (case-insensitive)
 */
const filteredStudents = computed(() => {
    if (!searchQuery.value.trim()) {
        return students.value;
    }

    const query = searchQuery.value.toLowerCase().trim();
    return students.value.filter(student => {
        const nama = student.nama_lengkap?.toLowerCase() || '';
        const nis = student.nis?.toLowerCase() || '';
        return nama.includes(query) || nis.includes(query);
    });
});

/**
 * Summary counter untuk display
 */
const summary = computed(() => {
    const counts = {
        total: form.attendances.length,
        hadir: 0,
        izin: 0,
        sakit: 0,
        alpha: 0
    };

    form.attendances.forEach(att => {
        if (att.status === 'H') counts.hadir++;
        else if (att.status === 'I') counts.izin++;
        else if (att.status === 'S') counts.sakit++;
        else if (att.status === 'A') counts.alpha++;
    });

    return counts;
});

/**
 * Fetch students ketika class dipilih
 */
const fetchStudents = async (classId: number) => {
    loadingStudents.value = true;

    try {
        const response = await fetch(`/api/classes/${classId}/students`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) throw new Error('Failed to fetch students');

        const data = await response.json();
        students.value = data.data || data;

        // Check for existing attendance if date is selected
        let existingData: ExistingAttendance[] = [];
        if (form.tanggal && classId) {
            try {
                console.log('Checking existing attendance for:', { classId, tanggal: form.tanggal });
                const existingResponse = await fetch(`/teacher/attendance/check-existing?class_id=${classId}&tanggal=${form.tanggal}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (existingResponse.ok) {
                    const result = await existingResponse.json();
                    existingData = result.data || [];
                    console.log('Existing attendance data:', existingData);
                } else {
                    console.error('Failed to fetch existing attendance:', existingResponse.status);
                }
            } catch (err) {
                console.warn('Could not fetch existing attendance:', err);
            }
        } else {
            console.log('Skipping existing check - missing data:', { hasDate: !!form.tanggal, classId });
        }

        // Initialize form attendances
        if (existingData.length > 0) {
            // Has existing attendance: populate with existing data
            console.log('Using existing attendance data from API');
            form.attendances = students.value.map(student => {
                const existing = existingData.find(a => a.student_id === student.id);
                const status = existing?.status || 'H';
                console.log(`Student ${student.nama_lengkap}: ${status}`, existing);
                return {
                    student_id: student.id,
                    status,
                    keterangan: existing?.keterangan
                };
            });
        } else if (isEditMode.value && props.existingAttendance) {
            // Edit mode from props: populate with props data
            form.attendances = students.value.map(student => {
                const existing = props.existingAttendance!.find(a => a.student_id === student.id);
                return {
                    student_id: student.id,
                    status: existing?.status || 'H',
                    keterangan: existing?.keterangan
                };
            });
        } else {
            // New mode: default all to "Hadir"
            form.attendances = students.value.map(student => ({
                student_id: student.id,
                status: 'H',
                keterangan: undefined
            }));
        }
    } catch (error) {
        console.error('Error fetching students:', error);
        modal.error('Gagal memuat daftar siswa');
        students.value = [];
        form.attendances = [];
    } finally {
        loadingStudents.value = false;
    }
};

/**
 * Handle class selection change
 */
watch(selectedClassId, (newClassId) => {
    if (newClassId) {
        form.class_id = newClassId;
        fetchStudents(newClassId);
    } else {
        students.value = [];
        form.attendances = [];
    }
});

/**
 * Handle date change - refetch students to load existing attendance
 */
watch(() => form.tanggal, () => {
    if (selectedClassId.value) {
        fetchStudents(selectedClassId.value);
    }
});

/**
 * Load students on mount if in edit mode
 */
onMounted(() => {
    if (isEditMode.value && selectedClassId.value) {
        fetchStudents(selectedClassId.value);
    }
});

/**
 * Update attendance status untuk siswa
 */
const updateStatus = (studentId: number, status: 'H' | 'I' | 'S' | 'A') => {
    haptics.selection();
    const attendance = form.attendances.find(a => a.student_id === studentId);
    if (attendance) {
        attendance.status = status;

        // Clear keterangan jika status = Hadir
        if (status === 'H') {
            attendance.keterangan = undefined;
        }
    }
};

/**
 * Update keterangan untuk siswa
 */
const updateKeterangan = (studentId: number, keterangan: string) => {
    const attendance = form.attendances.find(a => a.student_id === studentId);
    if (attendance) {
        attendance.keterangan = keterangan || undefined;
    }
};

/**
 * Clear search query untuk reset filter
 */
const clearSearch = () => {
    haptics.light();
    searchQuery.value = '';
};


/**
 * Submit attendance
 */
const submitAttendance = () => {
    if (!form.class_id) {
        modal.error('Mohon pilih kelas terlebih dahulu');
        return;
    }

    if (form.attendances.length === 0) {
        modal.error('Tidak ada siswa untuk diabsen');
        return;
    }

    haptics.medium();

    form.post('/teacher/attendance/daily', {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            modal.success(`Berhasil menyimpan presensi untuk ${summary.value.total} siswa`);
        },
        onError: (errors) => {
            haptics.error();
            const message = errors.message || 'Gagal menyimpan presensi';
            modal.error(message);
        }
    });
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
                    <div class="mx-auto max-w-7xl">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Input presensi harian untuk siswa di kelas Anda</p>
                    </div>
                </div>
            </Motion>

            <div class="mx-auto max-w-7xl px-6 py-8 space-y-6">
                <!-- Class & Date Selection -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                        <div class="grid gap-6 md:grid-cols-2">
                            <!-- Class Selection -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">
                                    Pilih Kelas
                                </label>
                                <select
                                    v-model="selectedClassId"
                                    class="w-full h-[52px] px-4 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-900 dark:text-white
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 focus:bg-white
                                           transition-all duration-150"
                                >
                                    <option :value="null">-- Pilih Kelas --</option>
                                    <option v-for="kelas in classes" :key="kelas.id" :value="kelas.id">
                                        {{ kelas.nama_lengkap }} ({{ kelas.jumlah_siswa }} siswa)
                                    </option>
                                </select>
                            </div>

                            <!-- Date Selection -->
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide mb-2">
                                    Tanggal
                                </label>
                                <div class="relative">
                                    <Calendar class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="20" />
                                    <input
                                        v-model="form.tanggal"
                                        type="date"
                                        :max="new Date().toISOString().split('T')[0]"
                                        class="w-full h-[52px] pl-12 pr-4 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                               rounded-xl text-slate-900 dark:text-white
                                               focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50 focus:bg-white
                                               transition-all duration-150"
                                    />
                                </div>
                            </div>
                        </div>

                        <div v-if="form.errors.class_id || form.errors.tanggal" class="mt-4 p-3 bg-red-50/80 border border-red-200/50 rounded-lg flex items-start gap-2">
                            <AlertCircle :size="16" class="text-red-500 flex-shrink-0 mt-0.5" />
                            <div class="text-sm text-red-600">
                                <p v-if="form.errors.class_id">{{ form.errors.class_id }}</p>
                                <p v-if="form.errors.tanggal">{{ form.errors.tanggal }}</p>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Summary Cards -->
                <div v-if="students.length > 0" class="grid gap-4 grid-cols-2 md:grid-cols-5">
                    <Motion
                        v-for="(item, index) in [
                            { label: 'Total', value: summary.total, color: 'slate' },
                            { label: 'Hadir', value: summary.hadir, color: 'emerald' },
                            { label: 'Izin', value: summary.izin, color: 'amber' },
                            { label: 'Sakit', value: summary.sakit, color: 'sky' },
                            { label: 'Alpha', value: summary.alpha, color: 'red' }
                        ]"
                        :key="item.label"
                        :initial="{ opacity: 0, scale: 0.9 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 + index * 0.02 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 shadow-sm p-4">
                            <p class="text-xs font-medium text-slate-600 dark:text-zinc-400">{{ item.label }}</p>
                            <p class="mt-1 text-2xl font-bold" :class="`text-${item.color}-600`">{{ item.value }}</p>
                        </div>
                    </Motion>
                </div>

                <!-- Loading Students -->
                <div v-if="loadingStudents" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
                    <div class="space-y-3">
                        <div v-for="i in 5" :key="i" class="h-16 bg-slate-100 dark:bg-zinc-800 rounded-xl animate-pulse"></div>
                    </div>
                </div>

                <!-- Student Attendance Table -->
                <Motion
                    v-else-if="students.length > 0"
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-200 dark:border-zinc-800">
                            <div class="flex items-center justify-between gap-4 mb-4">
                                <div class="flex items-center gap-2">
                                    <Users :size="20" class="text-slate-600 dark:text-zinc-400" />
                                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Daftar Siswa</h2>
                                </div>
                                <div class="text-sm text-slate-600 dark:text-zinc-400">
                                    {{ filteredStudents.length }} dari {{ students.length }} siswa
                                </div>
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
                                <button
                                    v-if="searchQuery"
                                    @click="clearSearch"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300
                                           transition-colors duration-150"
                                >
                                    <X :size="20" />
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50/80 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide">No</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide">Nama Siswa</th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-zinc-400 uppercase tracking-wide">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-zinc-800">
                                    <!-- Empty State - No Results -->
                                    <tr v-if="filteredStudents.length === 0" class="hover:bg-transparent">
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center gap-3">
                                                <Search :size="48" class="text-slate-300 dark:text-zinc-700" />
                                                <div>
                                                    <p class="text-lg font-semibold text-slate-900 dark:text-white mb-1">
                                                        Tidak ada hasil
                                                    </p>
                                                    <p class="text-sm text-slate-600 dark:text-zinc-400">
                                                        Tidak ditemukan siswa dengan pencarian "{{ searchQuery }}"
                                                    </p>
                                                    <Motion :whileTap="{ scale: 0.97 }">
                                                        <button
                                                            @click="clearSearch"
                                                            class="mt-4 px-4 py-2 text-sm font-medium text-emerald-600 hover:text-emerald-700
                                                                   bg-emerald-50/50 hover:bg-emerald-50 rounded-lg
                                                                   transition-colors duration-150"
                                                        >
                                                            Hapus pencarian
                                                        </button>
                                                    </Motion>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Student List -->
                                    <tr v-for="(student, index) in filteredStudents" :key="student.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-zinc-400">{{ index + 1 }}</td>
                                        <td class="px-6 py-4">
                                            <p class="font-medium text-slate-900 dark:text-white">{{ student.nama_lengkap }}</p>
                                            <p class="text-sm text-slate-500 dark:text-zinc-400">NIS: {{ student.nis }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                <label
                                                    v-for="status in ['H', 'I', 'S', 'A']"
                                                    :key="status"
                                                    class="cursor-pointer"
                                                >
                                                    <input
                                                        type="radio"
                                                        :name="`status-${student.id}`"
                                                        :value="status"
                                                        :checked="form.attendances.find((a: any) => a.student_id === student.id)?.status === status"
                                                        @change="updateStatus(student.id, status as 'H' | 'I' | 'S' | 'A')"
                                                        class="sr-only"
                                                    />
                                                    <AttendanceStatusBadge
                                                        :status="(status as 'H' | 'I' | 'S' | 'A')"
                                                        size="sm"
                                                        :class="[
                                                            'transition-all duration-150',
                                                            form.attendances.find((a: any) => a.student_id === student.id)?.status === status
                                                                ? 'ring-2 ring-emerald-500/50 scale-105'
                                                                : 'opacity-50 hover:opacity-75'
                                                        ]"
                                                    />
                                                </label>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input
                                                v-if="form.attendances.find((a: any) => a.student_id === student.id)?.status !== 'H'"
                                                type="text"
                                                placeholder="Masukkan keterangan..."
                                                :value="form.attendances.find((a: any) => a.student_id === student.id)?.keterangan || ''"
                                                @input="updateKeterangan(student.id, ($event.target as HTMLInputElement).value)"
                                                class="w-full px-3 py-2 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                       rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-400
                                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                                       transition-all duration-150"
                                            />
                                            <span v-else class="text-sm text-slate-400 dark:text-zinc-500">-</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Submit Button -->
                        <div class="p-6 border-t border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-800/30">
                            <Motion :whileTap="{ scale: 0.97 }">
                                <button
                                    @click="submitAttendance"
                                    :disabled="form.processing || students.length === 0"
                                    class="w-full md:w-auto px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold
                                           flex items-center justify-center gap-2
                                           disabled:opacity-50 disabled:cursor-not-allowed
                                           transition-colors duration-150"
                                >
                                    <Save :size="20" />
                                    <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Presensi' }}</span>
                                </button>
                            </Motion>
                        </div>
                    </div>
                </Motion>

                <!-- Empty State -->
                <div v-else-if="!loadingStudents && !selectedClassId" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-12 text-center">
                    <Users :size="48" class="mx-auto text-slate-300 dark:text-zinc-700 mb-4" />
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Pilih Kelas</h3>
                    <p class="text-slate-600 dark:text-zinc-400">Silakan pilih kelas untuk memulai input presensi</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
