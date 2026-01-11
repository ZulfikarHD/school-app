<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Save, Users, BookOpen, Clock } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import AttendanceStatusBadge from '@/components/features/attendance/AttendanceStatusBadge.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';

interface Schedule {
    subject_id: number;
    nama_mapel: string;
    class_id: number;
    nama_kelas: string;
}

interface Student {
    id: number;
    nama_lengkap: string;
    nis: string;
}

interface Props {
    title: string;
    schedule: Schedule[];
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

// State
const selectedSubject = ref<number | null>(null);
const selectedClass = ref<number | null>(null);
const selectedJamKe = ref<number>(1);
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const students = ref<Student[]>([]);
const isLoadingStudents = ref(false);

const form = useForm({
    subject_id: null as number | null,
    class_id: null as number | null,
    tanggal: new Date().toISOString().split('T')[0],
    jam_ke: 1,
    attendances: [] as Array<{ student_id: number; status: string; keterangan: string | null }>,
});

// Computed
const availableClasses = computed(() => {
    if (!selectedSubject.value) return [];
    return props.schedule.filter(s => s.subject_id === selectedSubject.value);
});

const uniqueSubjects = computed(() => {
    const subjects = new Map();
    props.schedule.forEach(s => {
        if (!subjects.has(s.subject_id)) {
            subjects.set(s.subject_id, { id: s.subject_id, nama: s.nama_mapel });
        }
    });
    return Array.from(subjects.values());
});

const canLoadStudents = computed(() => {
    return selectedSubject.value && selectedClass.value && selectedDate.value && selectedJamKe.value;
});

const allPresent = computed(() => {
    return form.attendances.every(a => a.status === 'H');
});

// Methods
const loadStudents = async () => {
    if (!canLoadStudents.value) return;

    isLoadingStudents.value = true;
    haptics.light();

    try {
        const response = await fetch(`/api/classes/${selectedClass.value}/students`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await response.json();
        students.value = data.data || [];

        // Initialize form attendances
        form.attendances = students.value.map(student => ({
            student_id: student.id,
            status: 'H',
            keterangan: null,
        }));
    } catch {
        modal.error('Gagal memuat data siswa');
    } finally {
        isLoadingStudents.value = false;
    }
};

const markAllPresent = () => {
    haptics.light();
    form.attendances.forEach(attendance => {
        attendance.status = 'H';
        attendance.keterangan = null;
    });
};

const updateStatus = (index: number, status: string) => {
    haptics.light();
    form.attendances[index].status = status;
    if (status === 'H') {
        form.attendances[index].keterangan = null;
    }
};

const submit = () => {
    if (!selectedSubject.value || !selectedClass.value) {
        modal.warning('Pilih mata pelajaran dan kelas terlebih dahulu');
        return;
    }

    if (form.attendances.length === 0) {
        modal.warning('Tidak ada siswa untuk diabsen');
        return;
    }

    form.subject_id = selectedSubject.value;
    form.class_id = selectedClass.value;
    form.tanggal = selectedDate.value;
    form.jam_ke = selectedJamKe.value;

    haptics.medium();
    form.post('/teacher/attendance/subject', {
        preserveScroll: true,
        onSuccess: () => {
            modal.success('Presensi mata pelajaran berhasil disimpan');
            resetForm();
        },
        onError: () => {
            modal.error('Gagal menyimpan presensi');
        }
    });
};

const resetForm = () => {
    selectedSubject.value = null;
    selectedClass.value = null;
    selectedJamKe.value = 1;
    students.value = [];
    form.reset();
};

// Watch for changes
watch([selectedSubject, selectedClass, selectedDate, selectedJamKe], () => {
    if (canLoadStudents.value) {
        loadStudents();
    }
});
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ title }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Catat kehadiran siswa per mata pelajaran
                </p>
            </div>

            <!-- Selection Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Pilih Mata Pelajaran & Kelas
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <BookOpen :size="16" class="inline mr-1" />
                            Mata Pelajaran
                        </label>
                        <select
                            v-model="selectedSubject"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option :value="null">Pilih Mata Pelajaran</option>
                            <option v-for="subject in uniqueSubjects" :key="subject.id" :value="subject.id">
                                {{ subject.nama }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <Users :size="16" class="inline mr-1" />
                            Kelas
                        </label>
                        <select
                            v-model="selectedClass"
                            :disabled="!selectedSubject"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <option :value="null">Pilih Kelas</option>
                            <option v-for="cls in availableClasses" :key="cls.class_id" :value="cls.class_id">
                                {{ cls.nama_kelas }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <Clock :size="16" class="inline mr-1" />
                            Jam Ke
                        </label>
                        <select
                            v-model="selectedJamKe"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option v-for="i in 10" :key="i" :value="i">Jam ke-{{ i }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal
                        </label>
                        <input
                            v-model="selectedDate"
                            type="date"
                            :max="new Date().toISOString().split('T')[0]"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                </div>
            </div>

            <!-- Students List -->
            <div v-if="students.length > 0" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Daftar Siswa ({{ students.length }})
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Tandai kehadiran siswa
                        </p>
                    </div>
                    <button
                        v-if="!allPresent"
                        @click="markAllPresent"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-medium transition-colors"
                    >
                        Tandai Semua Hadir
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    No
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Nama Siswa
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    NIS
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Keterangan
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="(student, index) in students"
                                :key="student.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ student.nama_lengkap }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ student.nis }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <button
                                            v-for="status in ['H', 'I', 'S', 'A']"
                                            :key="status"
                                            @click="updateStatus(index, status)"
                                            :class="[
                                                'px-3 py-1 rounded-lg text-sm font-medium transition-all',
                                                form.attendances[index]?.status === status
                                                    ? 'ring-2 ring-blue-500 scale-105'
                                                    : 'opacity-50 hover:opacity-100'
                                            ]"
                                        >
                                            <AttendanceStatusBadge :status="status" />
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <input
                                        v-model="form.attendances[index].keterangan"
                                        type="text"
                                        placeholder="Keterangan (opsional)"
                                        :disabled="form.attendances[index]?.status === 'H'"
                                        class="w-full px-3 py-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Hadir: {{ form.attendances.filter(a => a.status === 'H').length }} •
                        Izin: {{ form.attendances.filter(a => a.status === 'I').length }} •
                        Sakit: {{ form.attendances.filter(a => a.status === 'S').length }} •
                        Alpha: {{ form.attendances.filter(a => a.status === 'A').length }}
                    </div>
                    <Motion
                        tag="button"
                        :whileTap="{ scale: 0.95 }"
                        @click="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white rounded-xl font-medium transition-colors"
                    >
                        <Save :size="20" />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Presensi' }}
                    </Motion>
                </div>
            </div>

            <!-- Loading State -->
            <div v-else-if="isLoadingStudents" class="bg-white dark:bg-gray-800 rounded-xl p-12 border border-gray-200 dark:border-gray-700 text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <p class="text-gray-500 dark:text-gray-400">Memuat data siswa...</p>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white dark:bg-gray-800 rounded-xl p-12 border border-gray-200 dark:border-gray-700 text-center">
                <Users :size="48" class="mx-auto mb-3 text-gray-400 opacity-50" />
                <p class="text-lg font-medium text-gray-500 dark:text-gray-400">Pilih mata pelajaran dan kelas</p>
                <p class="text-sm text-gray-400 mt-1">Data siswa akan muncul setelah Anda memilih</p>
            </div>
        </div>
    </AppLayout>
</template>
