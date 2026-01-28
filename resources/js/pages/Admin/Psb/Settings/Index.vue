<script setup lang="ts">
/**
 * Admin PSB Settings Page
 *
 * Halaman untuk mengelola pengaturan PSB per tahun ajaran,
 * yaitu: periode pendaftaran, pengumuman, biaya, dan kuota
 */
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    Settings,
    Calendar,
    DollarSign,
    Users,
    ArrowLeft,
    Save,
    Clock,
    CheckCircle,
    XCircle,
    ListChecks,
    Edit3,
    Plus,
    History
} from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import Badge from '@/components/ui/Badge.vue';
import DialogModal from '@/components/ui/DialogModal.vue';
import { useHaptics } from '@/composables/useHaptics';
import { index as psbIndex } from '@/routes/admin/psb';
import { index as settingsIndex, store, update } from '@/routes/admin/psb/settings';

/**
 * Interface untuk Academic Year
 */
interface AcademicYear {
    id: number;
    name: string;
    is_active: boolean;
}

/**
 * Interface untuk PSB Setting
 */
interface PsbSetting {
    id: number;
    academic_year_id: number;
    academic_year: string;
    is_active: boolean;
    registration_open_date: string;
    registration_close_date: string;
    announcement_date: string;
    re_registration_deadline_days: number;
    registration_fee: number;
    formatted_fee: string;
    quota_per_class: number;
    waiting_list_enabled: boolean;
    is_registration_open: boolean;
    registration_count: number;
    re_registration_deadline: string;
    created_at: string;
}

interface Props {
    title: string;
    academicYears: AcademicYear[];
    activeSettings: PsbSetting | null;
    settings: PsbSetting[];
}

const props = defineProps<Props>();
const haptics = useHaptics();

// Form state
const isEditing = ref(false);
const editingSettingId = ref<number | null>(null);
const showForm = ref(false);

// Form menggunakan useForm
const form = useForm({
    academic_year_id: '',
    registration_open_date: '',
    registration_close_date: '',
    announcement_date: '',
    re_registration_deadline_days: 14,
    registration_fee: 0,
    quota_per_class: 30,
    waiting_list_enabled: false,
});

/**
 * Computed: Filter academic years yang belum punya settings
 */
const availableAcademicYears = computed(() => {
    const usedYearIds = props.settings.map(s => s.academic_year_id);

    if (isEditing.value && editingSettingId.value) {
        const editingSetting = props.settings.find(s => s.id === editingSettingId.value);
        if (editingSetting) {
            return props.academicYears.filter(
                y => !usedYearIds.includes(y.id) || y.id === editingSetting.academic_year_id
            );
        }
    }

    return props.academicYears.filter(y => !usedYearIds.includes(y.id));
});

/**
 * Computed: Status periode pendaftaran aktif
 */
const registrationStatus = computed(() => {
    if (!props.activeSettings) {
        return { open: false, label: 'Tidak Ada Setting', color: 'gray' };
    }

    if (props.activeSettings.is_registration_open) {
        return { open: true, label: 'Pendaftaran Dibuka', color: 'emerald' };
    }

    const now = new Date();
    const openDate = new Date(props.activeSettings.registration_open_date);
    const closeDate = new Date(props.activeSettings.registration_close_date);

    if (now < openDate) {
        return { open: false, label: 'Belum Dibuka', color: 'amber' };
    }

    if (now > closeDate) {
        return { open: false, label: 'Sudah Ditutup', color: 'red' };
    }

    return { open: false, label: 'Tidak Aktif', color: 'gray' };
});

/**
 * Open form for creating new setting
 */
const openCreateForm = () => {
    haptics.light();
    isEditing.value = false;
    editingSettingId.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
};

/**
 * Open form for editing existing setting
 */
const openEditForm = (setting: PsbSetting) => {
    haptics.light();
    isEditing.value = true;
    editingSettingId.value = setting.id;
    form.academic_year_id = String(setting.academic_year_id);
    form.registration_open_date = setting.registration_open_date;
    form.registration_close_date = setting.registration_close_date;
    form.announcement_date = setting.announcement_date;
    form.re_registration_deadline_days = setting.re_registration_deadline_days;
    form.registration_fee = setting.registration_fee;
    form.quota_per_class = setting.quota_per_class;
    form.waiting_list_enabled = setting.waiting_list_enabled;
    form.clearErrors();
    showForm.value = true;
};

/**
 * Close form
 */
const closeForm = () => {
    showForm.value = false;
    isEditing.value = false;
    editingSettingId.value = null;
    form.reset();
};

/**
 * Submit form
 */
const submitForm = () => {
    haptics.medium();

    if (isEditing.value && editingSettingId.value) {
        form.put(update(editingSettingId.value).url, {
            onSuccess: () => {
                haptics.success();
                closeForm();
            },
            onError: () => {
                haptics.error();
            },
        });
    } else {
        form.post(store().url, {
            onSuccess: () => {
                haptics.success();
                closeForm();
            },
            onError: () => {
                haptics.error();
            },
        });
    }
};

/**
 * Format currency ke Rupiah
 */
const formatCurrency = (amount: number): string => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
};

/**
 * Format date untuk display
 */
const formatDate = (dateString: string): string => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

/**
 * Format short date untuk display
 */
const formatShortDate = (dateString: string): string => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <Link
                                :href="psbIndex().url"
                                class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                            >
                                <ArrowLeft :size="20" class="text-slate-600 dark:text-slate-400" />
                            </Link>
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-slate-500 to-slate-600 flex items-center justify-center shadow-lg shadow-slate-500/25 shrink-0">
                                <Settings :size="24" class="text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ title }}
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Kelola periode pendaftaran PSB
                                </p>
                            </div>
                        </div>

                        <Motion :whileTap="{ scale: 0.97 }">
                            <button
                                v-if="availableAcademicYears.length > 0"
                                @click="openCreateForm"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-medium transition-colors shadow-sm"
                            >
                                <Plus :size="18" />
                                <span>Tambah Periode</span>
                            </button>
                        </Motion>
                    </div>
                </div>
            </Motion>

            <!-- Active Period Card -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <Calendar :size="20" class="text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <h2 class="font-semibold text-slate-900 dark:text-slate-100">Periode Aktif</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Tahun ajaran yang sedang berjalan</p>
                        </div>
                    </div>

                    <div v-if="activeSettings" class="space-y-4">
                        <!-- Status Badge -->
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-slate-900 dark:text-slate-100">
                                {{ activeSettings.academic_year }}
                            </span>
                            <Badge
                                :variant="registrationStatus.color as any"
                                size="sm"
                                dot
                            >
                                <component :is="registrationStatus.open ? CheckCircle : XCircle" :size="12" class="mr-1" />
                                {{ registrationStatus.label }}
                            </Badge>
                        </div>

                        <!-- Period Info Grid -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Buka Pendaftaran</p>
                                <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm">
                                    {{ formatShortDate(activeSettings.registration_open_date) }}
                                </p>
                            </div>
                            <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Tutup Pendaftaran</p>
                                <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm">
                                    {{ formatShortDate(activeSettings.registration_close_date) }}
                                </p>
                            </div>
                            <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Pengumuman</p>
                                <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm">
                                    {{ formatShortDate(activeSettings.announcement_date) }}
                                </p>
                            </div>
                            <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Biaya Pendaftaran</p>
                                <p class="font-semibold text-emerald-600 dark:text-emerald-400 text-sm">
                                    {{ activeSettings.formatted_fee }}
                                </p>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="flex flex-wrap gap-3 pt-2">
                            <div class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-400">
                                <Users :size="14" />
                                <span>Kuota: {{ activeSettings.quota_per_class }} per kelas</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-400">
                                <Clock :size="14" />
                                <span>Daftar Ulang: {{ activeSettings.re_registration_deadline_days }} hari</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-400">
                                <ListChecks :size="14" />
                                <span>Waiting List: {{ activeSettings.waiting_list_enabled ? 'Aktif' : 'Tidak Aktif' }}</span>
                            </div>
                        </div>

                        <!-- Edit Button -->
                        <div class="pt-2">
                            <button
                                @click="openEditForm(activeSettings)"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 rounded-xl transition-colors"
                            >
                                <Edit3 :size="16" />
                                <span>Edit Pengaturan</span>
                            </button>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                            <Calendar class="w-8 h-8 text-slate-300 dark:text-zinc-600" />
                        </div>
                        <p class="font-medium text-slate-700 dark:text-slate-300">Belum ada periode aktif</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Tambahkan pengaturan PSB untuk tahun ajaran aktif
                        </p>
                    </div>
                </div>
            </Motion>

            <!-- Settings History -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <History :size="20" class="text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h2 class="font-semibold text-slate-900 dark:text-slate-100">Riwayat Periode PSB</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Semua pengaturan PSB per tahun ajaran</p>
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <!-- Empty State -->
                        <div v-if="settings.length === 0" class="py-12 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                                <Settings class="w-8 h-8 text-slate-300 dark:text-zinc-600" />
                            </div>
                            <p class="font-medium text-slate-700 dark:text-slate-300">Belum ada pengaturan</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                Klik tombol "Tambah Periode" untuk membuat pengaturan baru
                            </p>
                        </div>

                        <!-- Settings List -->
                        <div
                            v-for="setting in settings"
                            :key="setting.id"
                            class="p-4 sm:p-6 hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors"
                        >
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h3 class="font-semibold text-slate-900 dark:text-slate-100">
                                            {{ setting.academic_year }}
                                        </h3>
                                        <Badge
                                            v-if="setting.is_active"
                                            variant="emerald"
                                            size="xs"
                                        >
                                            Aktif
                                        </Badge>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600 dark:text-slate-400">
                                        <span>{{ formatShortDate(setting.registration_open_date) }} - {{ formatShortDate(setting.registration_close_date) }}</span>
                                        <span>{{ setting.formatted_fee }}</span>
                                        <span>{{ setting.registration_count }} pendaftar</span>
                                    </div>
                                </div>
                                <button
                                    @click="openEditForm(setting)"
                                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                >
                                    <Edit3 :size="16" />
                                    <span>Edit</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>
        </div>

        <!-- Form Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showForm"
                    class="fixed inset-0 z-50 overflow-y-auto"
                >
                    <div class="flex min-h-full items-end sm:items-center justify-center p-4">
                        <!-- Backdrop -->
                        <div
                            class="fixed inset-0 bg-black/50 backdrop-blur-sm"
                            @click="closeForm"
                        />

                        <!-- Modal -->
                        <Transition
                            enter-active-class="duration-200 ease-out"
                            enter-from-class="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                            enter-to-class="opacity-100 scale-100 translate-y-0"
                            leave-active-class="duration-150 ease-in"
                            leave-from-class="opacity-100 scale-100 translate-y-0"
                            leave-to-class="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                        >
                            <div
                                v-if="showForm"
                                class="relative w-full max-w-lg bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-slate-200 dark:border-zinc-700"
                            >
                                <!-- Modal Header -->
                                <div class="px-6 py-4 border-b border-slate-200 dark:border-zinc-800">
                                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                        {{ isEditing ? 'Edit Pengaturan PSB' : 'Tambah Periode PSB' }}
                                    </h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                        {{ isEditing ? 'Perbarui pengaturan periode PSB' : 'Buat pengaturan PSB untuk tahun ajaran baru' }}
                                    </p>
                                </div>

                                <!-- Modal Body -->
                                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                                    <!-- Academic Year -->
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                            Tahun Ajaran <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            v-model="form.academic_year_id"
                                            class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                                            :class="{ 'border-red-500': form.errors.academic_year_id }"
                                        >
                                            <option value="">Pilih Tahun Ajaran</option>
                                            <option v-for="year in availableAcademicYears" :key="year.id" :value="year.id">
                                                {{ year.name }} {{ year.is_active ? '(Aktif)' : '' }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors.academic_year_id" class="mt-1 text-xs text-red-500">
                                            {{ form.errors.academic_year_id }}
                                        </p>
                                    </div>

                                    <!-- Date Row -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                                Tanggal Buka <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model="form.registration_open_date"
                                                type="date"
                                                class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                                                :class="{ 'border-red-500': form.errors.registration_open_date }"
                                            />
                                            <p v-if="form.errors.registration_open_date" class="mt-1 text-xs text-red-500">
                                                {{ form.errors.registration_open_date }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                                Tanggal Tutup <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model="form.registration_close_date"
                                                type="date"
                                                class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                                                :class="{ 'border-red-500': form.errors.registration_close_date }"
                                            />
                                            <p v-if="form.errors.registration_close_date" class="mt-1 text-xs text-red-500">
                                                {{ form.errors.registration_close_date }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Announcement Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                            Tanggal Pengumuman <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.announcement_date"
                                            type="date"
                                            class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                                            :class="{ 'border-red-500': form.errors.announcement_date }"
                                        />
                                        <p v-if="form.errors.announcement_date" class="mt-1 text-xs text-red-500">
                                            {{ form.errors.announcement_date }}
                                        </p>
                                    </div>

                                    <!-- Re-registration Deadline -->
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                            Batas Daftar Ulang (hari) <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model.number="form.re_registration_deadline_days"
                                            type="number"
                                            min="7"
                                            max="30"
                                            class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                                            :class="{ 'border-red-500': form.errors.re_registration_deadline_days }"
                                        />
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            Batas waktu daftar ulang setelah pengumuman (7-30 hari)
                                        </p>
                                        <p v-if="form.errors.re_registration_deadline_days" class="mt-1 text-xs text-red-500">
                                            {{ form.errors.re_registration_deadline_days }}
                                        </p>
                                    </div>

                                    <!-- Fee and Quota Row -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                                Biaya Pendaftaran (Rp) <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model.number="form.registration_fee"
                                                type="number"
                                                min="0"
                                                class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                                                :class="{ 'border-red-500': form.errors.registration_fee }"
                                            />
                                            <p v-if="form.errors.registration_fee" class="mt-1 text-xs text-red-500">
                                                {{ form.errors.registration_fee }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                                Kuota per Kelas <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model.number="form.quota_per_class"
                                                type="number"
                                                min="1"
                                                class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                                                :class="{ 'border-red-500': form.errors.quota_per_class }"
                                            />
                                            <p v-if="form.errors.quota_per_class" class="mt-1 text-xs text-red-500">
                                                {{ form.errors.quota_per_class }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Waiting List Toggle -->
                                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-xl">
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">Aktifkan Waiting List</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                Pendaftar yang melebihi kuota masuk waiting list
                                            </p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input
                                                v-model="form.waiting_list_enabled"
                                                type="checkbox"
                                                class="sr-only peer"
                                            />
                                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-500/20 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-emerald-500" />
                                        </label>
                                    </div>
                                </form>

                                <!-- Modal Footer -->
                                <div class="px-6 py-4 border-t border-slate-200 dark:border-zinc-800 flex justify-end gap-3">
                                    <button
                                        type="button"
                                        @click="closeForm"
                                        class="px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-colors"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        @click="submitForm"
                                        :disabled="form.processing"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-emerald-500 hover:bg-emerald-600 disabled:bg-emerald-400 disabled:cursor-not-allowed rounded-xl transition-colors shadow-sm"
                                    >
                                        <Save :size="16" />
                                        <span>{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</span>
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
