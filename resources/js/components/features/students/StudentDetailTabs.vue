<script setup lang="ts">
import { ref, computed } from 'vue';
import { User, Users, History, MapPin, GraduationCap, Phone, Mail } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import type { Student, Guardian, ClassHistory, StatusHistory } from '@/types/student';

interface Props {
    student: Student & {
        guardians?: Guardian[];
        primaryGuardian?: Guardian;
        classHistory?: ClassHistory[];
        statusHistory?: StatusHistory[];
    };
    // Read-only mode (hides certain admin-only features)
    readOnly?: boolean;
    // Show payment tab (Principal can see, Teacher cannot)
    showPayment?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    readOnly: false,
    showPayment: false
});

const haptics = useHaptics();

const activeTab = ref<'biodata' | 'guardians' | 'class-history' | 'status-history'>('biodata');

// Tabs dengan short labels untuk mobile
// Filter tabs based on role permissions
const tabs = computed(() => {
    const allTabs = [
        { id: 'biodata', label: 'Biodata', shortLabel: 'Bio', icon: User },
        { id: 'guardians', label: 'Orang Tua/Wali', shortLabel: 'Ortu', icon: Users },
        { id: 'class-history', label: 'Riwayat Kelas', shortLabel: 'Kelas', icon: GraduationCap },
    ];
    
    // Status history only for admin/principal (not for teachers)
    if (props.student.statusHistory && props.student.statusHistory.length > 0) {
        allTabs.push({ id: 'status-history', label: 'Riwayat Status', shortLabel: 'Status', icon: History });
    }
    
    // TODO: Add payment tab when payment module is implemented
    // if (props.showPayment) {
    //     allTabs.push({ id: 'payment', label: 'Pembayaran', shortLabel: 'Bayar', icon: DollarSign });
    // }
    
    return allTabs;
});

const changeTab = (tabId: typeof activeTab.value) => {
    haptics.selection();
    activeTab.value = tabId;
};

/**
 * Status badge classes menggunakan design system colors
 * untuk visual consistency di seluruh aplikasi
 */
const getStatusBadgeClass = (status: string) => {
    const classes: Record<string, string> = {
        aktif: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
        mutasi: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200 dark:border-amber-800',
        do: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border-red-200 dark:border-red-800',
        lulus: 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400 border-sky-200 dark:border-sky-800',
    };
    return classes[status] || 'bg-slate-100 text-slate-600 border-slate-200';
};

/**
 * Get status label yang readable
 */
const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        aktif: 'Aktif',
        mutasi: 'Mutasi',
        do: 'Drop Out',
        lulus: 'Lulus',
    };
    return labels[status] || status;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <div class="space-y-6">
        <!-- Tabs Navigation dengan proper accessibility -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-2 shadow-sm border border-slate-200 dark:border-zinc-800">
            <div class="grid grid-cols-4 gap-1.5 sm:gap-2" role="tablist" aria-label="Tab detail siswa">
                <Motion
                    v-for="tab in tabs"
                    :key="tab.id"
                    :whileTap="{ scale: 0.97 }"
                >
                    <button
                        @click="changeTab(tab.id as typeof activeTab)"
                        role="tab"
                        :id="`tab-${tab.id}`"
                        :aria-selected="activeTab === tab.id"
                        :aria-controls="`panel-${tab.id}`"
                        :tabindex="activeTab === tab.id ? 0 : -1"
                        class="w-full flex flex-col items-center justify-center gap-1 px-1.5 sm:px-4 py-2.5 sm:py-3 rounded-xl font-medium transition-all min-h-[56px] sm:min-h-[52px] focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                        :class="activeTab === tab.id
                            ? 'bg-emerald-500 text-white shadow-sm shadow-emerald-500/25'
                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-zinc-800 active:bg-slate-100 dark:active:bg-zinc-700'"
                    >
                        <component :is="tab.icon" class="w-5 h-5 shrink-0" aria-hidden="true" />
                        <!-- Short label on mobile, full label on desktop -->
                        <span class="text-[11px] sm:text-xs text-center leading-tight font-semibold sm:hidden">{{ tab.shortLabel }}</span>
                        <span class="text-xs text-center leading-tight font-semibold hidden sm:block">{{ tab.label }}</span>
                    </button>
                </Motion>
            </div>
        </div>

        <!-- Tab Content dengan proper accessibility -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
            <!-- Biodata Tab -->
            <Motion
                v-if="activeTab === 'biodata'"
                :initial="{ opacity: 0, x: -10 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ duration: 0.2 }"
                class="p-4 sm:p-8"
                role="tabpanel"
                id="panel-biodata"
                aria-labelledby="tab-biodata"
            >
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
                    <!-- Photo -->
                    <div class="lg:col-span-1">
                        <div class="aspect-3/4 max-w-[200px] mx-auto lg:mx-0 rounded-2xl overflow-hidden bg-slate-100 dark:bg-zinc-800 shadow-sm">
                            <img
                                v-if="student.foto"
                                :src="student.foto"
                                :alt="student.nama_lengkap"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <User class="w-16 h-16 sm:w-20 sm:h-20 text-gray-300 dark:text-gray-600" />
                            </div>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                        <div class="text-center lg:text-left">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                {{ student.nama_lengkap }}
                            </h2>
                            <p v-if="student.nama_panggilan" class="text-sm sm:text-base text-gray-500 dark:text-gray-400">
                                Panggilan: {{ student.nama_panggilan }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">NIS</p>
                                <p class="font-mono font-semibold text-gray-900 dark:text-gray-100">{{ student.nis }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">NISN</p>
                                <p class="font-mono font-semibold text-gray-900 dark:text-gray-100">{{ student.nisn }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">NIK</p>
                                <p class="font-mono text-sm text-gray-700 dark:text-gray-300">{{ student.nik }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Jenis Kelamin</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ student.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Tempat, Tanggal Lahir</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ student.tempat_lahir }}, {{ formatDate(student.tanggal_lahir) }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Agama</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ student.agama }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Anak Ke / Jumlah Saudara</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ student.anak_ke }} dari {{ student.jumlah_saudara }} bersaudara
                                </p>
                            </div>
                                <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Status</p>
                                <span 
                                    :class="getStatusBadgeClass(student.status)" 
                                    class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold border"
                                    role="status"
                                >
                                    {{ getStatusLabel(student.status) }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-zinc-800 space-y-3">
                            <div class="flex items-start gap-3">
                                <MapPin class="w-4 h-4 text-gray-400 mt-1 shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Alamat</p>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                        {{ student.alamat }}
                                        <span v-if="student.rt_rw">, RT/RW {{ student.rt_rw }}</span>
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1.5 leading-relaxed">
                                        {{ student.kelurahan }}, {{ student.kecamatan }}, {{ student.kota }}, {{ student.provinsi }}
                                        <span v-if="student.kode_pos"> - {{ student.kode_pos }}</span>
                                    </p>
                                </div>
                            </div>
                            <div v-if="student.no_hp" class="flex items-center gap-3">
                                <Phone class="w-4 h-4 text-gray-400 shrink-0" />
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ student.no_hp }}</p>
                            </div>
                            <div v-if="student.email" class="flex items-center gap-3">
                                <Mail class="w-4 h-4 text-gray-400 shrink-0" />
                                <p class="text-sm text-gray-700 dark:text-gray-300 break-all">{{ student.email }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Guardians Tab -->
            <Motion
                v-if="activeTab === 'guardians'"
                :initial="{ opacity: 0, x: -10 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ duration: 0.2 }"
                class="p-4 sm:p-8"
                role="tabpanel"
                id="panel-guardians"
                aria-labelledby="tab-guardians"
            >
                <div v-if="student.guardians && student.guardians.length > 0" class="space-y-4 sm:space-y-6">
                    <article v-for="guardian in student.guardians" :key="guardian.id" class="bg-slate-50 dark:bg-zinc-800/50 rounded-2xl p-4 sm:p-6 border border-slate-200 dark:border-zinc-700">
                        <div class="flex items-start justify-between mb-4">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100 capitalize">
                                {{ guardian.hubungan }}
                            </h3>
                            <span v-if="guardian.pivot?.is_primary_contact" class="px-2.5 py-1 bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400 text-xs font-semibold rounded-lg border border-sky-200 dark:border-sky-800 shrink-0">
                                Kontak Utama
                            </span>
                        </div>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Nama Lengkap</dt>
                                <dd class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ guardian.nama_lengkap }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">NIK</dt>
                                <dd class="text-sm font-mono text-slate-700 dark:text-slate-300">{{ guardian.nik }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Pekerjaan</dt>
                                <dd class="text-sm text-slate-700 dark:text-slate-300">{{ guardian.pekerjaan }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Pendidikan</dt>
                                <dd class="text-sm text-slate-700 dark:text-slate-300">{{ guardian.pendidikan }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Penghasilan</dt>
                                <dd class="text-sm text-slate-700 dark:text-slate-300">{{ guardian.penghasilan }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">No. HP</dt>
                                <dd class="text-sm text-slate-700 dark:text-slate-300">{{ guardian.no_hp || '-' }}</dd>
                            </div>
                        </dl>
                    </article>
                </div>
                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <Users class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" aria-hidden="true" />
                    <p class="text-slate-500 dark:text-slate-400 font-medium">Belum ada data orang tua/wali</p>
                    <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Data orang tua/wali akan ditampilkan di sini</p>
                </div>
            </Motion>

            <!-- Class History Tab -->
            <Motion
                v-if="activeTab === 'class-history'"
                :initial="{ opacity: 0, x: -10 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ duration: 0.2 }"
                class="p-4 sm:p-8"
                role="tabpanel"
                id="panel-class-history"
                aria-labelledby="tab-class-history"
            >
                <div v-if="student.classHistory && student.classHistory.length > 0" class="space-y-4">
                    <article v-for="history in student.classHistory" :key="history.id" class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-xl border border-slate-200 dark:border-zinc-700">
                        <div class="w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center">
                            <GraduationCap class="w-6 h-6 text-sky-600 dark:text-sky-400" aria-hidden="true" />
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-slate-900 dark:text-slate-100">Kelas {{ history.kelas_id }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ history.tahun_ajaran }} • Wali Kelas: {{ history.wali_kelas }}</p>
                        </div>
                        <time class="text-xs text-slate-500 dark:text-slate-400">{{ formatDate(history.created_at) }}</time>
                    </article>
                </div>
                <div v-else class="text-center py-12">
                    <GraduationCap class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" aria-hidden="true" />
                    <p class="text-slate-500 dark:text-slate-400">Belum ada riwayat kelas</p>
                </div>
            </Motion>

            <!-- Status History Tab -->
            <Motion
                v-if="activeTab === 'status-history'"
                :initial="{ opacity: 0, x: -10 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ duration: 0.2 }"
                class="p-4 sm:p-8"
                role="tabpanel"
                id="panel-status-history"
                aria-labelledby="tab-status-history"
            >
                <div v-if="student.statusHistory && student.statusHistory.length > 0" class="space-y-4">
                    <article v-for="history in student.statusHistory" :key="history.id" class="p-5 bg-slate-50 dark:bg-zinc-800/50 rounded-xl border border-slate-200 dark:border-zinc-700">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <span :class="getStatusBadgeClass(history.status_lama)" class="px-2.5 py-1 rounded-lg text-xs font-semibold border">
                                    {{ getStatusLabel(history.status_lama) }}
                                </span>
                                <span class="text-slate-400" aria-hidden="true">→</span>
                                <span :class="getStatusBadgeClass(history.status_baru)" class="px-2.5 py-1 rounded-lg text-xs font-semibold border">
                                    {{ getStatusLabel(history.status_baru) }}
                                </span>
                            </div>
                            <time class="text-xs text-slate-500 dark:text-slate-400">{{ formatDate(history.tanggal) }}</time>
                        </div>
                        <p class="text-sm text-slate-700 dark:text-slate-300 mb-2">{{ history.alasan }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Diubah oleh: {{ history.changed_by.name }}</p>
                    </article>
                </div>
                <div v-else class="text-center py-12">
                    <History class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" aria-hidden="true" />
                    <p class="text-slate-500 dark:text-slate-400">Belum ada riwayat perubahan status</p>
                </div>
            </Motion>
        </div>
    </div>
</template>
