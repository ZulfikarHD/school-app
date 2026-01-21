<script setup lang="ts">
/**
 * Teacher Report Cards Index Page - List rapor untuk wali kelas
 * dengan card view per siswa dan status tracking
 */
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import {
    FileText,
    Users,
    CheckCircle,
    Clock,
    AlertCircle,
    Send,
    Eye,
    MessageSquare,
    User
} from 'lucide-vue-next';
import { Motion } from 'motion-v';
import ReportCardStatusBadge from '@/components/features/grades/ReportCardStatusBadge.vue';
import { index, show, submitAll } from '@/routes/teacher/report-cards';

interface ReportCardEntry {
    id: number;
    student_id: number;
    student_name: string;
    student_nis: string;
    student_foto: string | null;
    status: string;
    status_label: string;
    status_color: string;
    average_score: number | null;
    rank: number | null;
    has_notes: boolean;
    generated_at: string | null;
}

interface Statistics {
    total: number;
    draft: number;
    pending_approval: number;
    approved: number;
    released: number;
    with_notes: number;
}

interface Props {
    title: string;
    isWaliKelas: boolean;
    classData: {
        id: number;
        nama_lengkap: string;
        tingkat: number;
        tahun_ajaran: string;
    } | null;
    reportCards: ReportCardEntry[];
    filters: {
        tahun_ajaran: string;
        semester: string;
    };
    statistics: Statistics | null;
    semesters: { value: string; label: string }[];
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();

const localFilters = ref({
    tahun_ajaran: props.filters.tahun_ajaran,
    semester: props.filters.semester
});

/**
 * Apply filters
 */
const applyFilters = () => {
    haptics.light();
    router.visit(index.url(localFilters.value), {
        preserveState: true,
        preserveScroll: true
    });
};

/**
 * Check jika semua rapor sudah ada catatan
 */
const allHaveNotes = computed(() => {
    return props.reportCards.length > 0 &&
           props.reportCards.every(rc => rc.has_notes);
});

/**
 * Get draft rapor count untuk submit all
 */
const draftCount = computed(() => {
    return props.reportCards.filter(rc => rc.status === 'DRAFT').length;
});

/**
 * Handle submit all untuk approval
 */
const handleSubmitAll = async () => {
    if (draftCount.value === 0) {
        modal.error('Tidak ada rapor Draft yang dapat disubmit');
        return;
    }

    const confirmed = await modal.dialog({
        type: 'warning',
        icon: 'question',
        title: 'Submit Semua Rapor',
        message: `Yakin ingin mensubmit <b>${draftCount.value} rapor</b> untuk persetujuan kepala sekolah?`,
        confirmText: 'Ya, Submit',
        cancelText: 'Batal',
        showCancel: true
    });

    if (!confirmed) return;

    haptics.medium();
    router.post(submitAll.url(), {
        tahun_ajaran: localFilters.value.tahun_ajaran,
        semester: localFilters.value.semester
    }, {
        preserveScroll: true
    });
};

/**
 * Get avatar initials
 */
const getInitials = (name: string) => {
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Not Wali Kelas Warning -->
            <template v-if="!isWaliKelas">
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut' }"
                >
                    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl p-6 border border-amber-200 dark:border-amber-800">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center shrink-0">
                                <AlertCircle class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-amber-800 dark:text-amber-200">
                                    Akses Terbatas
                                </h2>
                                <p class="text-amber-700 dark:text-amber-300 text-sm mt-1">
                                    Halaman ini hanya dapat diakses oleh wali kelas. Anda bukan wali kelas untuk tahun ajaran ini.
                                </p>
                            </div>
                        </div>
                    </div>
                </Motion>
            </template>

            <!-- Wali Kelas Content -->
            <template v-else>
                <!-- Header -->
                <Motion
                    :initial="{ opacity: 0, y: -10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut' }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="w-12 h-12 rounded-xl bg-linear-to-br from-violet-400 to-violet-600 flex items-center justify-center shadow-lg shadow-violet-500/25 shrink-0">
                                    <FileText class="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                        Rapor Kelas {{ classData?.nama_lengkap }}
                                    </h1>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                        Kelola rapor siswa dan input catatan wali kelas
                                    </p>
                                </div>
                            </div>

                            <Motion
                                v-if="draftCount > 0"
                                :whileTap="{ scale: 0.97 }"
                            >
                                <button
                                    type="button"
                                    @click="handleSubmitAll"
                                    class="flex items-center gap-2 px-4 py-2.5 min-h-[44px] bg-linear-to-r from-violet-500 to-purple-500 text-white rounded-xl hover:from-violet-600 hover:to-purple-600 transition-all duration-200 shadow-lg shadow-violet-500/30"
                                >
                                    <Send class="w-5 h-5" />
                                    <span class="font-semibold">Submit Semua ({{ draftCount }})</span>
                                </button>
                            </Motion>
                        </div>
                    </div>
                </Motion>

                <!-- Statistics Cards -->
                <Motion
                    v-if="statistics"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
                >
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                                    <Users class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                        {{ statistics.total }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Total Siswa</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-900/30 flex items-center justify-center">
                                    <FileText class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                        {{ statistics.draft }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Draft</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                    <Clock class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                        {{ statistics.pending_approval }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Pending</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                    <MessageSquare class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                        {{ statistics.with_notes }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Dengan Catatan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Filters -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <div class="flex-1 grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                        Semester
                                    </label>
                                    <select
                                        v-model="localFilters.semester"
                                        @change="applyFilters"
                                        class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"
                                    >
                                        <option
                                            v-for="sem in semesters"
                                            :key="sem.value"
                                            :value="sem.value"
                                        >
                                            {{ sem.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Student Cards -->
                <Motion
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
                >
                    <div v-if="reportCards.length === 0" class="bg-white dark:bg-zinc-900 rounded-2xl p-12 shadow-sm border border-slate-200 dark:border-zinc-800 text-center">
                        <FileText class="w-12 h-12 text-slate-300 dark:text-zinc-600 mx-auto mb-4" />
                        <p class="text-slate-500 dark:text-slate-400">
                            Belum ada rapor yang di-generate untuk semester ini
                        </p>
                        <p class="text-sm text-slate-400 dark:text-slate-500 mt-2">
                            Hubungi admin untuk generate rapor siswa
                        </p>
                    </div>

                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <Motion
                            v-for="(rc, idx) in reportCards"
                            :key="rc.id"
                            :initial="{ opacity: 0, y: 20 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 * idx }"
                        >
                            <Link
                                :href="show(rc.id)"
                                class="block bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800 hover:shadow-md hover:border-violet-300 dark:hover:border-violet-700 transition-all"
                            >
                                <div class="flex items-start gap-4">
                                    <!-- Avatar -->
                                    <div class="w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center shrink-0 overflow-hidden">
                                        <img
                                            v-if="rc.student_foto"
                                            :src="rc.student_foto"
                                            :alt="rc.student_name"
                                            class="w-full h-full object-cover"
                                        />
                                        <span
                                            v-else
                                            class="text-violet-600 dark:text-violet-400 font-semibold text-sm"
                                        >
                                            {{ getInitials(rc.student_name) }}
                                        </span>
                                    </div>

                                    <!-- Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <h3 class="font-semibold text-slate-900 dark:text-slate-100 truncate">
                                                    {{ rc.student_name }}
                                                </h3>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ rc.student_nis }}
                                                </p>
                                            </div>
                                            <ReportCardStatusBadge :status="rc.status" size="sm" />
                                        </div>

                                        <!-- Stats -->
                                        <div class="flex items-center gap-4 mt-3">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs text-slate-500 dark:text-slate-400">Rata-rata:</span>
                                                <span
                                                    v-if="rc.average_score !== null"
                                                    class="font-semibold text-slate-900 dark:text-slate-100 text-sm"
                                                >
                                                    {{ rc.average_score.toFixed(1) }}
                                                </span>
                                                <span v-else class="text-slate-400 text-sm">-</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs text-slate-500 dark:text-slate-400">Rank:</span>
                                                <span
                                                    v-if="rc.rank !== null"
                                                    class="font-semibold text-slate-900 dark:text-slate-100 text-sm"
                                                >
                                                    #{{ rc.rank }}
                                                </span>
                                                <span v-else class="text-slate-400 text-sm">-</span>
                                            </div>
                                        </div>

                                        <!-- Notes indicator -->
                                        <div class="flex items-center gap-2 mt-2">
                                            <div
                                                :class="[
                                                    'flex items-center gap-1 px-2 py-0.5 rounded-full text-xs',
                                                    rc.has_notes
                                                        ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                                                        : 'bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-slate-400'
                                                ]"
                                            >
                                                <MessageSquare class="w-3 h-3" />
                                                {{ rc.has_notes ? 'Ada Catatan' : 'Belum Ada Catatan' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </Motion>
                    </div>
                </Motion>
            </template>
        </div>
    </AppLayout>
</template>
