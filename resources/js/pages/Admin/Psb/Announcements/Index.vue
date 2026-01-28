<script setup lang="ts">
/**
 * Admin PSB Announcements Page
 *
 * Halaman untuk mengelola pengumuman PSB,
 * yaitu: bulk selection dan announce registrations yang sudah approved
 */
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    Megaphone,
    Search,
    Users,
    CheckCircle,
    Phone,
    Calendar,
    ChevronLeft,
    ChevronRight,
    Send,
    ArrowLeft
} from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import Badge from '@/components/ui/Badge.vue';
import DialogModal from '@/components/ui/DialogModal.vue';
import { useHaptics } from '@/composables/useHaptics';
// TODO: Uncomment setelah backend routes dibuat dan Wayfinder generate routes
// import { index as announcementsIndex, bulkAnnounce } from '@/routes/admin/psb/announcements';
import { index as psbIndex } from '@/routes/admin/psb';

// Temporary route functions sampai Wayfinder generate
const announcementsIndex = (options?: { query?: Record<string, any> }) => ({
    url: '/admin/psb/announcements' + (options?.query ? '?' + new URLSearchParams(options.query as any).toString() : ''),
});
const bulkAnnounce = () => ({ url: '/admin/psb/announcements/bulk-announce' });

/**
 * Interface untuk data registration
 */
interface Registration {
    id: number;
    registration_number: string;
    student_name: string;
    parent_name: string;
    parent_phone: string;
    parent_email: string;
    status: string;
    approved_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    registrations: {
        data: Registration[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        per_page: number;
    };
    filters: {
        academic_year_id?: number;
        search?: string;
    };
    academicYears: { id: number; name: string }[];
}

const props = defineProps<Props>();
const haptics = useHaptics();

// Local state untuk filters
const search = ref(props.filters.search || '');
const academicYearId = ref(props.filters.academic_year_id || '');

// Selection state
const selectedIds = ref<number[]>([]);

// Modal state
const showConfirmModal = ref(false);
const processing = ref(false);

// Debounce search
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

/**
 * Computed: apakah semua data terpilih
 */
const allSelected = computed(() => {
    return props.registrations.data.length > 0 &&
           selectedIds.value.length === props.registrations.data.length;
});

/**
 * Computed: apakah ada yang terpilih
 */
const hasSelected = computed(() => selectedIds.value.length > 0);

/**
 * Handle search dengan debounce
 */
const handleSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

/**
 * Apply filters ke server
 */
const applyFilters = () => {
    haptics.selection();
    selectedIds.value = [];

    router.get(
        announcementsIndex().url,
        {
            search: search.value || undefined,
            academic_year_id: academicYearId.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

/**
 * Toggle select all
 */
const toggleSelectAll = () => {
    haptics.light();
    if (allSelected.value) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.registrations.data.map(r => r.id);
    }
};

/**
 * Toggle single selection
 */
const toggleSelection = (id: number) => {
    haptics.light();
    if (selectedIds.value.includes(id)) {
        selectedIds.value = selectedIds.value.filter(itemId => itemId !== id);
    } else {
        selectedIds.value.push(id);
    }
};

/**
 * Open confirmation modal
 */
const openConfirmModal = () => {
    haptics.medium();
    showConfirmModal.value = true;
};

/**
 * Close confirmation modal
 */
const closeConfirmModal = () => {
    showConfirmModal.value = false;
};

/**
 * Submit bulk announce
 */
const submitBulkAnnounce = () => {
    processing.value = true;

    router.post(
        bulkAnnounce().url,
        {
            registration_ids: selectedIds.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                haptics.success();
                selectedIds.value = [];
                showConfirmModal.value = false;
                processing.value = false;
            },
            onError: () => {
                haptics.error();
                processing.value = false;
            },
        }
    );
};

/**
 * Format date ke format Indonesia
 */
const formatDate = (dateString: string): string => {
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
        <Head title="Pengumuman PSB" />

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
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/25 shrink-0">
                                <Megaphone :size="24" class="text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Pengumuman PSB
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Umumkan pendaftar yang diterima
                                </p>
                            </div>
                        </div>

                        <!-- Bulk Action Button -->
                        <div class="flex items-center gap-3">
                            <div v-if="hasSelected" class="text-sm text-slate-600 dark:text-slate-400">
                                <span class="font-semibold text-violet-600 dark:text-violet-400">{{ selectedIds.length }}</span> dipilih
                            </div>
                            <Motion :whileTap="{ scale: 0.97 }">
                                <button
                                    @click="openConfirmModal"
                                    :disabled="!hasSelected"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-violet-500 hover:bg-violet-600 disabled:bg-slate-300 disabled:dark:bg-zinc-700 text-white disabled:text-slate-500 disabled:dark:text-zinc-500 rounded-xl font-medium transition-colors shadow-sm disabled:shadow-none disabled:cursor-not-allowed"
                                >
                                    <Send :size="18" />
                                    <span>Umumkan Terpilih</span>
                                </button>
                            </Motion>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Filters -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <!-- Search -->
                        <div class="flex-1">
                            <div class="relative">
                                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                <input
                                    v-model="search"
                                    @input="handleSearch"
                                    type="text"
                                    placeholder="Cari nama siswa atau no. pendaftaran..."
                                    class="w-full pl-10 pr-4 py-3 min-h-[48px] text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50/80 dark:bg-zinc-800 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 focus:bg-white dark:focus:bg-zinc-900 transition-all"
                                />
                            </div>
                        </div>

                        <!-- Academic Year Filter -->
                        <div class="sm:w-48">
                            <select
                                v-model="academicYearId"
                                @change="applyFilters"
                                class="w-full text-sm rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 py-3 px-3 min-h-[48px] focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all"
                            >
                                <option value="">Semua Tahun Ajaran</option>
                                <option v-for="year in academicYears" :key="year.id" :value="year.id">
                                    {{ year.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Desktop Table -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="hidden md:block bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-100 dark:border-zinc-800">
                                <tr>
                                    <th class="px-6 py-3.5 w-10">
                                        <div class="flex items-center">
                                            <input
                                                type="checkbox"
                                                :checked="allSelected"
                                                @change="toggleSelectAll"
                                                class="w-4 h-4 text-violet-600 border-slate-300 rounded focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-700 dark:ring-offset-zinc-800"
                                            />
                                        </div>
                                    </th>
                                    <th class="px-6 py-3.5 font-semibold tracking-wide">No. Pendaftaran</th>
                                    <th class="px-6 py-3.5 font-semibold tracking-wide">Nama Siswa</th>
                                    <th class="px-6 py-3.5 font-semibold tracking-wide">Orang Tua</th>
                                    <th class="px-6 py-3.5 font-semibold tracking-wide">Kontak</th>
                                    <th class="px-6 py-3.5 font-semibold tracking-wide">Tgl Diterima</th>
                                    <th class="px-6 py-3.5 font-semibold tracking-wide">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                <!-- Empty State -->
                                <tr v-if="registrations.data.length === 0">
                                    <td colspan="7" class="px-6 py-16 text-center text-slate-500">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                                                <Users class="w-8 h-8 text-slate-300 dark:text-zinc-600" />
                                            </div>
                                            <div>
                                                <p class="font-medium text-slate-700 dark:text-slate-300">Tidak ada pendaftar diterima</p>
                                                <p class="text-sm text-slate-400 mt-1">
                                                    Belum ada pendaftar dengan status approved
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Data Rows -->
                                <tr
                                    v-for="registration in registrations.data"
                                    :key="registration.id"
                                    class="group hover:bg-slate-50/80 dark:hover:bg-zinc-800/50 transition-colors"
                                    :class="{ 'bg-violet-50/50 dark:bg-violet-900/10': selectedIds.includes(registration.id) }"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <input
                                                type="checkbox"
                                                :checked="selectedIds.includes(registration.id)"
                                                @change="toggleSelection(registration.id)"
                                                class="w-4 h-4 text-violet-600 border-slate-300 rounded focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-700 dark:ring-offset-zinc-800"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-xs font-medium text-slate-700 dark:text-slate-300">
                                            {{ registration.registration_number }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-medium text-slate-900 dark:text-slate-100">
                                            {{ registration.student_name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                        {{ registration.parent_name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                                                <Phone :size="12" />
                                                <span class="text-xs">{{ registration.parent_phone }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                                            <Calendar :size="12" />
                                            <span class="text-xs">{{ formatDate(registration.approved_at) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <Badge variant="success" size="sm" dot>
                                            <CheckCircle :size="12" class="mr-1" />
                                            Diterima
                                        </Badge>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </Motion>

            <!-- Mobile Cards -->
            <div class="md:hidden space-y-3">
                <!-- Empty State -->
                <div v-if="registrations.data.length === 0" class="py-12 text-center bg-white dark:bg-zinc-900 rounded-2xl border border-slate-100 dark:border-zinc-800">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-linear-to-br from-slate-100 to-slate-200 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center">
                        <Users class="w-8 h-8 text-slate-400 dark:text-zinc-500" />
                    </div>
                    <p class="font-medium text-slate-700 dark:text-slate-300">Tidak ada pendaftar diterima</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Belum ada pendaftar dengan status approved</p>
                </div>

                <!-- Data Cards -->
                <Motion
                    v-for="registration in registrations.data"
                    :key="registration.id"
                    :whileTap="{ scale: 0.98 }"
                >
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border transition-all duration-200 p-4"
                        :class="selectedIds.includes(registration.id)
                            ? 'border-violet-400 ring-2 ring-violet-500/20 bg-violet-50/30 dark:bg-violet-950/10'
                            : 'border-slate-100 dark:border-zinc-800'"
                    >
                        <div class="flex items-start gap-3">
                            <!-- Checkbox -->
                            <div class="pt-1 shrink-0">
                                <input
                                    type="checkbox"
                                    :checked="selectedIds.includes(registration.id)"
                                    @change="toggleSelection(registration.id)"
                                    class="w-5 h-5 text-violet-600 border-slate-300 rounded-md focus:ring-violet-500 focus:ring-offset-0 dark:border-zinc-600 dark:bg-zinc-700 transition-colors"
                                />
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="font-semibold text-slate-900 dark:text-slate-100 truncate">
                                            {{ registration.student_name }}
                                        </h3>
                                        <p class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">
                                            {{ registration.registration_number }}
                                        </p>
                                    </div>
                                    <Badge variant="success" size="xs" dot>
                                        Diterima
                                    </Badge>
                                </div>

                                <div class="mt-3 space-y-1.5">
                                    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                        <Users :size="14" class="shrink-0" />
                                        <span class="truncate">{{ registration.parent_name }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                        <Phone :size="14" class="shrink-0" />
                                        <span>{{ registration.parent_phone }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                        <Calendar :size="14" class="shrink-0" />
                                        <span>{{ formatDate(registration.approved_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>

            <!-- Pagination -->
            <Motion
                v-if="registrations.total > registrations.per_page"
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ duration: 0.3, delay: 0.2 }"
                class="pt-4 border-t border-slate-200 dark:border-zinc-800"
            >
                <!-- Mobile Pagination -->
                <div class="flex items-center justify-between md:hidden">
                    <div class="text-sm text-slate-500">
                        {{ registrations.from }}-{{ registrations.to }} dari {{ registrations.total }}
                    </div>
                    <div class="flex gap-2">
                        <Link
                            :href="registrations.links[0]?.url || '#'"
                            :class="[
                                'flex items-center justify-center w-11 h-11 rounded-xl border transition-colors',
                                registrations.links[0]?.url
                                    ? 'border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800'
                                    : 'border-slate-100 dark:border-zinc-800 text-slate-300 dark:text-zinc-600 cursor-not-allowed'
                            ]"
                            preserve-scroll
                            preserve-state
                        >
                            <ChevronLeft class="w-5 h-5" />
                        </Link>
                        <div class="flex items-center justify-center px-4 h-11 bg-violet-500 text-white rounded-xl font-semibold text-sm min-w-[44px]">
                            {{ registrations.current_page }}
                        </div>
                        <Link
                            :href="registrations.links[registrations.links.length - 1]?.url || '#'"
                            :class="[
                                'flex items-center justify-center w-11 h-11 rounded-xl border transition-colors',
                                registrations.links[registrations.links.length - 1]?.url
                                    ? 'border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800'
                                    : 'border-slate-100 dark:border-zinc-800 text-slate-300 dark:text-zinc-600 cursor-not-allowed'
                            ]"
                            preserve-scroll
                            preserve-state
                        >
                            <ChevronRight class="w-5 h-5" />
                        </Link>
                    </div>
                </div>

                <!-- Desktop Pagination -->
                <div class="hidden md:flex items-center justify-between">
                    <div class="text-sm text-slate-500">
                        Menampilkan {{ registrations.from }} - {{ registrations.to }} dari {{ registrations.total }} data
                    </div>
                    <div class="flex gap-1">
                        <Link
                            v-for="(link, i) in registrations.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'min-w-[36px] h-9 px-3 flex items-center justify-center text-sm rounded-lg transition-colors',
                                link.active
                                    ? 'bg-violet-500 text-white font-semibold shadow-sm'
                                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-zinc-800',
                                !link.url && 'opacity-50 cursor-not-allowed hover:bg-transparent'
                            ]"
                            preserve-scroll
                            preserve-state
                        >
                            <span v-html="link.label" />
                        </Link>
                    </div>
                </div>
            </Motion>
        </div>

        <!-- Confirmation Modal -->
        <DialogModal
            :show="showConfirmModal"
            type="info"
            title="Konfirmasi Pengumuman"
            :message="`Anda akan mengumumkan ${selectedIds.length} pendaftar. Notifikasi akan dikirim ke orang tua melalui email dan WhatsApp.`"
            confirm-text="Ya, Umumkan"
            cancel-text="Batal"
            :loading="processing"
            @confirm="submitBulkAnnounce"
            @cancel="closeConfirmModal"
            @close="closeConfirmModal"
        />
    </AppLayout>
</template>
