<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Search, Edit, Trash2, Save, X, AlertCircle } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import AttendanceStatusBadge from '@/components/features/attendance/AttendanceStatusBadge.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';

interface Props {
    title: string;
}

defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

// State
const searchQuery = ref('');
const searchResults = ref<any[]>([]);
const isSearching = ref(false);
const selectedStudent = ref<any>(null);
const attendanceHistory = ref<any[]>([]);
const editingId = ref<number | null>(null);

const editForm = useForm({
    status: '',
    keterangan: '',
});

// Methods
const searchStudent = async () => {
    if (!searchQuery.value.trim()) {
        modal.warning('Masukkan NIS atau nama siswa');
        return;
    }

    isSearching.value = true;
    haptics.light();

    try {
        const response = await fetch(`/api/students/search?q=${encodeURIComponent(searchQuery.value)}`);
        const data = await response.json();
        searchResults.value = data.data || [];
        
        if (searchResults.value.length === 0) {
            modal.info('Siswa tidak ditemukan');
        }
    } catch {
        modal.error('Gagal mencari siswa');
    } finally {
        isSearching.value = false;
    }
};

const selectStudent = async (student: any) => {
    selectedStudent.value = student;
    searchResults.value = [];
    searchQuery.value = '';
    haptics.light();

    // Load attendance history
    try {
        const response = await fetch(`/api/students/${student.id}/attendance`);
        const data = await response.json();
        attendanceHistory.value = data.data || [];
    } catch {
        modal.error('Gagal memuat riwayat presensi');
    }
};

const startEdit = (attendance: any) => {
    editingId.value = attendance.id;
    editForm.status = attendance.status;
    editForm.keterangan = attendance.keterangan || '';
    haptics.light();
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
    haptics.light();
};

const saveEdit = (attendanceId: number) => {
    haptics.medium();
    
    editForm.put(`/admin/attendance/${attendanceId}`, {
        preserveScroll: true,
        onSuccess: () => {
            modal.success('Data presensi berhasil diperbarui');
            editingId.value = null;
            // Reload attendance history
            if (selectedStudent.value) {
                selectStudent(selectedStudent.value);
            }
        },
        onError: () => {
            modal.error('Gagal memperbarui data presensi');
        }
    });
};

const deleteAttendance = async (attendance: any) => {
    const confirmed = await modal.dialog({
        type: 'danger',
        icon: 'warning',
        title: 'Hapus Data Presensi',
        message: `Apakah Anda yakin ingin menghapus data presensi tanggal <b>${formatDate(attendance.tanggal)}</b>?`,
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal',
        showCancel: true,
        allowHtml: true
    });

    if (confirmed) {
        haptics.heavy();
        router.delete(`/admin/attendance/${attendance.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                modal.success('Data presensi berhasil dihapus');
                // Reload attendance history
                if (selectedStudent.value) {
                    selectStudent(selectedStudent.value);
                }
            }
        });
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatTime = (datetime: string) => {
    return new Date(datetime).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
};
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
                    Edit atau hapus data presensi siswa
                </p>
            </div>

            <!-- Warning Notice -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <AlertCircle :size="20" class="text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
                    <div>
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
                            Perhatian
                        </h3>
                        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-400">
                            Perubahan data presensi akan tercatat dalam audit log. Pastikan Anda memiliki alasan yang valid untuk melakukan koreksi.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Cari Siswa
                </h2>
                
                <div class="flex gap-3">
                    <div class="flex-1 relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Masukkan NIS atau nama siswa..."
                            @keyup.enter="searchStudent"
                            class="w-full px-4 py-3 pl-10 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                        <Search :size="20" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                    </div>
                    <Motion
                        tag="button"
                        :whileTap="{ scale: 0.95 }"
                        @click="searchStudent"
                        :disabled="isSearching"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white rounded-xl font-medium transition-colors"
                    >
                        {{ isSearching ? 'Mencari...' : 'Cari' }}
                    </Motion>
                </div>

                <!-- Search Results -->
                <div v-if="searchResults.length > 0" class="mt-4 space-y-2">
                    <Motion
                        v-for="student in searchResults"
                        :key="student.id"
                        :initial="{ opacity: 0, y: -10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        tag="button"
                        @click="selectStudent(student)"
                        class="w-full p-4 bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl text-left transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ student.nama_lengkap }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    NIS: {{ student.nis }} • {{ student.kelas?.nama_lengkap }}
                                </div>
                            </div>
                            <div class="text-blue-600 dark:text-blue-400">
                                Pilih →
                            </div>
                        </div>
                    </Motion>
                </div>
            </div>

            <!-- Selected Student & Attendance History -->
            <div v-if="selectedStudent" class="space-y-6">
                <!-- Student Info -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ selectedStudent.nama_lengkap }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                NIS: {{ selectedStudent.nis }} • {{ selectedStudent.kelas?.nama_lengkap }}
                            </p>
                        </div>
                        <button
                            @click="selectedStudent = null; attendanceHistory = []"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <X :size="20" />
                        </button>
                    </div>
                </div>

                <!-- Attendance History -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Riwayat Presensi
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Tanggal
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Keterangan
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Dicatat Oleh
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="attendance in attendanceHistory"
                                    :key="attendance.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ formatDate(attendance.tanggal) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select
                                            v-if="editingId === attendance.id"
                                            v-model="editForm.status"
                                            class="px-3 py-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
                                        >
                                            <option value="H">Hadir</option>
                                            <option value="I">Izin</option>
                                            <option value="S">Sakit</option>
                                            <option value="A">Alpha</option>
                                        </select>
                                        <AttendanceStatusBadge v-else :status="attendance.status" />
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <input
                                            v-if="editingId === attendance.id"
                                            v-model="editForm.keterangan"
                                            type="text"
                                            placeholder="Keterangan..."
                                            class="w-full px-3 py-1 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white"
                                        />
                                        <span v-else class="text-gray-500 dark:text-gray-400">
                                            {{ attendance.keterangan || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                {{ attendance.recorded_by?.name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ formatTime(attendance.recorded_at) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div v-if="editingId === attendance.id" class="flex items-center justify-end gap-2">
                                            <button
                                                @click="saveEdit(attendance.id)"
                                                class="p-2 text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300"
                                            >
                                                <Save :size="18" />
                                            </button>
                                            <button
                                                @click="cancelEdit"
                                                class="p-2 text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                            >
                                                <X :size="18" />
                                            </button>
                                        </div>
                                        <div v-else class="flex items-center justify-end gap-2">
                                            <button
                                                @click="startEdit(attendance)"
                                                class="p-2 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                            >
                                                <Edit :size="18" />
                                            </button>
                                            <button
                                                @click="deleteAttendance(attendance)"
                                                class="p-2 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                            >
                                                <Trash2 :size="18" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="attendanceHistory.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <p class="text-lg font-medium">Belum ada riwayat presensi</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
