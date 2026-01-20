<script setup lang="ts">
/**
 * Payment Categories Index Page - Halaman daftar kategori pembayaran
 * dengan fitur search, filter, dan aksi CRUD untuk mengelola
 * jenis pembayaran seperti SPP, Uang Gedung, Seragam, dll.
 */
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useModal } from '@/composables/useModal';
import { useHaptics } from '@/composables/useHaptics';
import { Plus, Wallet, Search, Filter, MoreVertical, Pencil, Trash2, ToggleLeft, ToggleRight, Eye, X } from 'lucide-vue-next';
import { create, edit, destroy, toggleStatus } from '@/routes/admin/payment-categories';
import { index } from '@/routes/admin/payment-categories';
import { Motion } from 'motion-v';

interface PaymentCategory {
    id: number;
    nama: string;
    kode: string;
    deskripsi: string | null;
    tipe: 'bulanan' | 'tahunan' | 'insidental';
    nominal_default: number;
    is_active: boolean;
    is_mandatory: boolean;
    due_day: number | null;
    tahun_ajaran: string | null;
    formatted_nominal?: string;
    tipe_label?: string;
    created_at: string;
}

interface Props {
    categories: {
        data: PaymentCategory[];
        links: any;
        meta?: any;
    };
    filters: {
        search?: string;
        tipe?: string;
        is_active?: string;
        tahun_ajaran?: string;
    };
}

const props = defineProps<Props>();

const modal = useModal();
const haptics = useHaptics();
const loading = ref(false);
const showFilters = ref(false);
const activeDropdown = ref<number | null>(null);

// Local filter state
const searchQuery = ref(props.filters.search || '');
const filterTipe = ref(props.filters.tipe || '');
const filterStatus = ref(props.filters.is_active || '');

// Computed
const hasActiveFilters = computed(() => {
    return filterTipe.value || filterStatus.value;
});

// Methods
const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const getTipeLabel = (tipe: string): string => {
    const labels: Record<string, string> = {
        bulanan: 'Bulanan',
        tahunan: 'Tahunan',
        insidental: 'Insidental',
    };
    return labels[tipe] || tipe;
};

const getTipeBadgeClass = (tipe: string): string => {
    const classes: Record<string, string> = {
        bulanan: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        tahunan: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        insidental: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    };
    return classes[tipe] || 'bg-slate-100 text-slate-700';
};

const applyFilters = () => {
    haptics.light();
    router.get(index().url, {
        search: searchQuery.value || undefined,
        tipe: filterTipe.value || undefined,
        is_active: filterStatus.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    haptics.light();
    searchQuery.value = '';
    filterTipe.value = '';
    filterStatus.value = '';
    router.get(index().url, {}, { preserveState: true, replace: true });
};

const toggleDropdown = (id: number) => {
    activeDropdown.value = activeDropdown.value === id ? null : id;
};

const closeDropdown = () => {
    activeDropdown.value = null;
};

const handleEdit = (category: PaymentCategory) => {
    closeDropdown();
    router.visit(edit(category.id).url);
};

const handleDelete = async (category: PaymentCategory) => {
    closeDropdown();
    const confirmed = await modal.confirmDelete(
        `Apakah Anda yakin ingin menghapus kategori "${category.nama}"?`
    );

    if (confirmed) {
        haptics.heavy();
        router.delete(destroy(category.id).url, {
            onStart: () => loading.value = true,
            onFinish: () => loading.value = false,
            onSuccess: () => {
                modal.success('Kategori pembayaran berhasil dihapus');
                haptics.success();
            },
            onError: (errors) => {
                haptics.error();
                modal.error(errors.error || 'Gagal menghapus kategori pembayaran');
            },
        });
    }
};

const handleToggleStatus = async (category: PaymentCategory) => {
    closeDropdown();
    const newStatus = category.is_active ? 'Nonaktif' : 'Aktif';
    const action = category.is_active ? 'menonaktifkan' : 'mengaktifkan';

    const confirmed = await modal.confirm(
        `Konfirmasi ${newStatus}`,
        `Apakah Anda yakin ingin ${action} kategori "${category.nama}"?`,
        `Ya, ${newStatus}kan`,
        'Batal'
    );

    if (confirmed) {
        haptics.medium();
        router.patch(toggleStatus(category.id).url, {}, {
            onStart: () => loading.value = true,
            onFinish: () => loading.value = false,
            onSuccess: () => {
                modal.success(`Kategori berhasil di${newStatus.toLowerCase()}kan`);
                haptics.success();
            },
            onError: () => {
                haptics.error();
                modal.error('Gagal mengubah status kategori');
            },
        });
    }
};

// Close dropdown when clicking outside
const handleClickOutside = () => {
    closeDropdown();
};
</script>

<template>
    <AppLayout>
        <Head title="Kategori Pembayaran" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8" @click="handleClickOutside">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <!-- Left Side: Title -->
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-violet-400 to-violet-600 flex items-center justify-center shadow-lg shadow-violet-500/25 shrink-0">
                                <Wallet class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Kategori Pembayaran
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Kelola jenis pembayaran sekolah
                                </p>
                            </div>
                        </div>

                        <!-- Right Side: Actions -->
                        <div class="flex items-center gap-2.5">
                            <Motion :whileTap="{ scale: 0.97 }">
                                <Link
                                    :href="create()"
                                    @click="haptics.light()"
                                    class="group flex items-center gap-2.5 px-5 py-2.5 min-h-[44px] bg-linear-to-r from-violet-500 to-purple-500 text-white rounded-xl hover:from-violet-600 hover:to-purple-600 transition-all duration-200 shadow-lg shadow-violet-500/30
                                           focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                                >
                                    <Plus class="w-5 h-5 transition-transform group-hover:rotate-90 duration-200" />
                                    <span class="font-semibold hidden sm:inline">Tambah Kategori</span>
                                    <span class="font-semibold sm:hidden">Tambah</span>
                                </Link>
                            </Motion>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Filters & Search -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.05 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Search -->
                        <div class="flex-1 relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari nama atau kode kategori..."
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <!-- Filter Toggle Button (Mobile) -->
                        <button
                            @click="showFilters = !showFilters; haptics.light()"
                            class="sm:hidden flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300"
                        >
                            <Filter class="w-5 h-5" />
                            <span>Filter</span>
                            <span v-if="hasActiveFilters" class="w-2 h-2 rounded-full bg-violet-500"></span>
                        </button>

                        <!-- Desktop Filters -->
                        <div class="hidden sm:flex items-center gap-3">
                            <select
                                v-model="filterTipe"
                                class="px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500"
                                @change="applyFilters"
                            >
                                <option value="">Semua Tipe</option>
                                <option value="bulanan">Bulanan</option>
                                <option value="tahunan">Tahunan</option>
                                <option value="insidental">Insidental</option>
                            </select>

                            <select
                                v-model="filterStatus"
                                class="px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500"
                                @change="applyFilters"
                            >
                                <option value="">Semua Status</option>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>

                            <button
                                @click="applyFilters"
                                class="px-4 py-2.5 bg-violet-500 text-white rounded-xl hover:bg-violet-600 transition-colors font-medium"
                            >
                                Cari
                            </button>

                            <button
                                v-if="hasActiveFilters || searchQuery"
                                @click="clearFilters"
                                class="p-2.5 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-colors"
                                title="Reset Filter"
                            >
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Filters (Collapsible) -->
                    <div v-if="showFilters" class="sm:hidden mt-3 pt-3 border-t border-slate-200 dark:border-zinc-700 space-y-3">
                        <select
                            v-model="filterTipe"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500"
                        >
                            <option value="">Semua Tipe</option>
                            <option value="bulanan">Bulanan</option>
                            <option value="tahunan">Tahunan</option>
                            <option value="insidental">Insidental</option>
                        </select>

                        <select
                            v-model="filterStatus"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500"
                        >
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>

                        <div class="flex gap-2">
                            <button
                                @click="applyFilters"
                                class="flex-1 px-4 py-2.5 bg-violet-500 text-white rounded-xl hover:bg-violet-600 transition-colors font-medium"
                            >
                                Terapkan
                            </button>
                            <button
                                v-if="hasActiveFilters"
                                @click="clearFilters"
                                class="px-4 py-2.5 text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-zinc-800 rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-700 transition-colors"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Categories List -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50">
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kode</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipe</th>
                                    <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nominal</th>
                                    <th class="text-center px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                    <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-zinc-800">
                                <tr
                                    v-for="category in categories.data"
                                    :key="category.id"
                                    class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ category.nama }}</p>
                                            <p v-if="category.deskripsi" class="text-sm text-slate-500 dark:text-slate-400 line-clamp-1">{{ category.deskripsi }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <code class="px-2 py-1 bg-slate-100 dark:bg-zinc-800 rounded text-sm font-mono text-slate-700 dark:text-slate-300">{{ category.kode }}</code>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', getTipeBadgeClass(category.tipe)]">
                                            {{ getTipeLabel(category.tipe) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold text-slate-900 dark:text-slate-100">
                                        {{ formatCurrency(category.nominal_default) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            :class="[
                                                'px-2.5 py-1 rounded-full text-xs font-medium',
                                                category.is_active
                                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                    : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400'
                                            ]"
                                        >
                                            {{ category.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="relative inline-block" @click.stop>
                                            <button
                                                @click="toggleDropdown(category.id)"
                                                class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                            >
                                                <MoreVertical class="w-5 h-5" />
                                            </button>
                                            <div
                                                v-if="activeDropdown === category.id"
                                                class="absolute right-0 mt-1 w-48 bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-slate-200 dark:border-zinc-700 py-1 z-50"
                                            >
                                                <button
                                                    @click="handleEdit(category)"
                                                    class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors"
                                                >
                                                    <Pencil class="w-4 h-4" />
                                                    Edit
                                                </button>
                                                <button
                                                    @click="handleToggleStatus(category)"
                                                    class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors"
                                                >
                                                    <component :is="category.is_active ? ToggleLeft : ToggleRight" class="w-4 h-4" />
                                                    {{ category.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                                <hr class="my-1 border-slate-200 dark:border-zinc-700" />
                                                <button
                                                    @click="handleDelete(category)"
                                                    class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                >
                                                    <Trash2 class="w-4 h-4" />
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden divide-y divide-slate-200 dark:divide-zinc-800">
                        <div
                            v-for="category in categories.data"
                            :key="category.id"
                            class="p-4 space-y-3"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-slate-900 dark:text-slate-100 truncate">{{ category.nama }}</p>
                                        <span
                                            :class="[
                                                'px-2 py-0.5 rounded-full text-xs font-medium shrink-0',
                                                category.is_active
                                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                    : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-400'
                                            ]"
                                        >
                                            {{ category.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-zinc-800 rounded text-xs font-mono text-slate-600 dark:text-slate-400">{{ category.kode }}</code>
                                        <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', getTipeBadgeClass(category.tipe)]">
                                            {{ getTipeLabel(category.tipe) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="relative" @click.stop>
                                    <button
                                        @click="toggleDropdown(category.id)"
                                        class="p-2 -m-2 text-slate-500"
                                    >
                                        <MoreVertical class="w-5 h-5" />
                                    </button>
                                    <div
                                        v-if="activeDropdown === category.id"
                                        class="absolute right-0 mt-1 w-44 bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-slate-200 dark:border-zinc-700 py-1 z-50"
                                    >
                                        <button
                                            @click="handleEdit(category)"
                                            class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-700"
                                        >
                                            <Pencil class="w-4 h-4" />
                                            Edit
                                        </button>
                                        <button
                                            @click="handleToggleStatus(category)"
                                            class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-700"
                                        >
                                            <component :is="category.is_active ? ToggleLeft : ToggleRight" class="w-4 h-4" />
                                            {{ category.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                        <hr class="my-1 border-slate-200 dark:border-zinc-700" />
                                        <button
                                            @click="handleDelete(category)"
                                            class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-zinc-800">
                                <span class="text-sm text-slate-500 dark:text-slate-400">Nominal Default</span>
                                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency(category.nominal_default) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="categories.data.length === 0" class="p-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                            <Wallet class="w-8 h-8 text-slate-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">Belum Ada Kategori</h3>
                        <p class="text-slate-500 dark:text-slate-400 mb-4">Mulai dengan menambahkan kategori pembayaran pertama.</p>
                        <Link
                            :href="create()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-violet-500 text-white rounded-xl hover:bg-violet-600 transition-colors font-medium"
                        >
                            <Plus class="w-4 h-4" />
                            Tambah Kategori
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="categories.data.length > 0 && categories.links" class="px-6 py-4 border-t border-slate-200 dark:border-zinc-800 flex items-center justify-between">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Menampilkan {{ categories.data.length }} kategori
                        </p>
                        <div class="flex gap-2">
                            <template v-for="link in categories.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-1.5 text-sm rounded-lg transition-colors',
                                        link.active
                                            ? 'bg-violet-500 text-white'
                                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800'
                                    ]"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="px-3 py-1.5 text-sm text-slate-400 dark:text-slate-600"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
