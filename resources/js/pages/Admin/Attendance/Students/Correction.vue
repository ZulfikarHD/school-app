<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Search, Edit, Trash2, Save, X, AlertCircle, ClipboardEdit, Calendar } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import AttendanceStatusBadge from '@/components/features/attendance/AttendanceStatusBadge.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Halaman koreksi presensi siswa untuk admin
 * dengan search student, edit, dan delete functionality
 */

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

        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
            <!-- Header Section -->
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="bg-white px-6 py-8 border-b border-gray-100 dark:bg-zinc-900 dark:border-zinc-800">
                    <div class="mx-auto max-w-7xl">
                        <div class="flex items-start gap-4">
                            <!-- Icon Container -->
                            <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center shadow-lg shadow-orange-500/25">
                                <ClipboardEdit class="w-7 h-7 text-white" />
                            </div>
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ title }}
                                </h1>
                                <p class="mt-1 text-gray-600 dark:text-gray-400">
                                    Edit atau hapus data presensi siswa
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <div class="mx-auto max-w-7xl px-6 py-8 space-y-6">
                <!-- Warning Notice -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                >
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 p-2 bg-amber-100 dark:bg-amber-800/30 rounded-xl">
                                <AlertCircle :size="18" class="text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                                    Perhatian
                                </h3>
                                <p class="mt-1 text-sm text-amber-700 dark:text-amber-400">
                                    Perubahan data presensi akan tercatat dalam audit log. Pastikan Anda memiliki alasan yang valid untuk melakukan koreksi.
                                </p>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Search Section -->
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.15 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-slate-200 dark:border-zinc-800 shadow-sm">
                        <h2 class="text-sm font-semibold text-slate-700 dark:text-zinc-300 uppercase tracking-wide mb-4">
                            Cari Siswa
                        </h2>
                        
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1 relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Masukkan NIS atau nama siswa..."
                                    @keyup.enter="searchStudent"
                                    class="w-full px-4 py-3 pl-11 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                           rounded-xl text-slate-900 dark:text-white placeholder-slate-400
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                           transition-all duration-150"
                                />
                                <Search :size="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                            </div>
                            <Motion
                                tag="button"
                                :whileTap="{ scale: 0.97 }"
                                @click="searchStudent"
                                :disabled="isSearching"
                                class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 disabled:bg-slate-400 text-white rounded-xl font-semibold transition-colors shadow-sm shadow-emerald-500/25"
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
                                class="w-full p-4 bg-slate-50 dark:bg-zinc-800 hover:bg-slate-100 dark:hover:bg-zinc-700 rounded-xl text-left transition-colors border border-transparent hover:border-emerald-200 dark:hover:border-emerald-800"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-slate-900 dark:text-white">
                                            {{ student.nama_lengkap }}
                                        </div>
                                        <div class="text-sm text-slate-500 dark:text-zinc-400">
                                            NIS: {{ student.nis }} • {{ student.kelas?.nama_lengkap }}
                                        </div>
                                    </div>
                                    <div class="text-emerald-600 dark:text-emerald-400 font-medium text-sm">
                                        Pilih →
                                    </div>
                                </div>
                            </Motion>
                        </div>
                    </div>
                </Motion>

                <!-- Selected Student & Attendance History -->
                <div v-if="selectedStudent" class="space-y-6">
                    <!-- Student Info -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                    >
                        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 shadow-lg shadow-emerald-500/20">
                            <div class="flex items-center justify-between">
                                <div class="text-white">
                                    <h2 class="text-xl font-bold">
                                        {{ selectedStudent.nama_lengkap }}
                                    </h2>
                                    <p class="text-emerald-100 mt-1">
                                        NIS: {{ selectedStudent.nis }} • {{ selectedStudent.kelas?.nama_lengkap }}
                                    </p>
                                </div>
                                <button
                                    @click="selectedStudent = null; attendanceHistory = []"
                                    class="p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-xl transition-colors"
                                >
                                    <X :size="20" />
                                </button>
                            </div>
                        </div>
                    </Motion>

                    <!-- Attendance History -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-200 dark:border-zinc-800">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                                    Riwayat Presensi
                                </h3>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-100 dark:border-zinc-800">
                                        <tr>
                                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                                Tanggal
                                            </th>
                                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                                Status
                                            </th>
                                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                                Keterangan
                                            </th>
                                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                                Dicatat Oleh
                                            </th>
                                            <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wide">
                                                Aksi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                        <tr
                                            v-for="attendance in attendanceHistory"
                                            :key="attendance.id"
                                            class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                                        >
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                                                {{ formatDate(attendance.tanggal) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <select
                                                    v-if="editingId === attendance.id"
                                                    v-model="editForm.status"
                                                    class="px-3 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50"
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
                                                    class="w-full px-3 py-1.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50"
                                                />
                                                <span v-else class="text-slate-500 dark:text-zinc-400">
                                                    {{ attendance.keterangan || '-' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <div class="text-sm text-slate-900 dark:text-white">
                                                        {{ attendance.recorded_by?.name }}
                                                    </div>
                                                    <div class="text-xs text-slate-500 dark:text-zinc-400">
                                                        {{ formatTime(attendance.recorded_at) }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <div v-if="editingId === attendance.id" class="flex items-center justify-end gap-1">
                                                    <Motion
                                                        tag="button"
                                                        :whileTap="{ scale: 0.9 }"
                                                        @click="saveEdit(attendance.id)"
                                                        class="p-2 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors"
                                                    >
                                                        <Save :size="18" />
                                                    </Motion>
                                                    <Motion
                                                        tag="button"
                                                        :whileTap="{ scale: 0.9 }"
                                                        @click="cancelEdit"
                                                        class="p-2 text-slate-600 hover:text-slate-700 dark:text-zinc-400 dark:hover:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                                    >
                                                        <X :size="18" />
                                                    </Motion>
                                                </div>
                                                <div v-else class="flex items-center justify-end gap-1">
                                                    <Motion
                                                        tag="button"
                                                        :whileTap="{ scale: 0.9 }"
                                                        @click="startEdit(attendance)"
                                                        class="p-2 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                                    >
                                                        <Edit :size="18" />
                                                    </Motion>
                                                    <Motion
                                                        tag="button"
                                                        :whileTap="{ scale: 0.9 }"
                                                        @click="deleteAttendance(attendance)"
                                                        class="p-2 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                    >
                                                        <Trash2 :size="18" />
                                                    </Motion>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="attendanceHistory.length === 0">
                                            <td colspan="5" class="px-6 py-16 text-center">
                                                <Calendar :size="48" class="mx-auto mb-4 text-slate-300 dark:text-zinc-600" />
                                                <p class="text-lg font-semibold text-slate-700 dark:text-zinc-300">Belum ada riwayat presensi</p>
                                                <p class="text-sm mt-1 text-slate-500 dark:text-zinc-500">Data presensi siswa akan muncul di sini</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </Motion>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
