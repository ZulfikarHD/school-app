<script setup lang="ts">
/**
 * StudentSearchInput - Autocomplete input untuk mencari siswa
 * dengan debounced search dan keyboard navigation
 */
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useHaptics } from '@/composables/useHaptics';
import { Search, User, X, Loader2 } from 'lucide-vue-next';
import { search as searchStudents } from '@/routes/admin/api/students';

interface Student {
    id: number;
    nis: string;
    nisn?: string;
    nama_lengkap: string;
    kelas: string;
    kelas_id: number;
    display_label: string;
    total_tunggakan: number;
    formatted_tunggakan: string;
}

interface Props {
    modelValue: Student | null;
    disabled?: boolean;
    placeholder?: string;
    autofocus?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    placeholder: 'Cari siswa berdasarkan nama atau NIS...',
    autofocus: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: Student | null];
    'select': [student: Student];
    'clear': [];
}>();

const haptics = useHaptics();

// State
const searchQuery = ref('');
const isOpen = ref(false);
const isLoading = ref(false);
const results = ref<Student[]>([]);
const highlightedIndex = ref(-1);
const inputRef = ref<HTMLInputElement | null>(null);
const dropdownRef = ref<HTMLDivElement | null>(null);

// Debounce timer
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

// Computed
const hasSelection = computed(() => props.modelValue !== null);

const showDropdown = computed(() => {
    return isOpen.value && (results.value.length > 0 || isLoading.value || (searchQuery.value.length >= 2 && !isLoading.value));
});

// Methods
const search = async (query: string) => {
    if (query.length < 2) {
        results.value = [];
        return;
    }

    isLoading.value = true;

    try {
        const response = await fetch(searchStudents.url({ query: { q: query } }), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.ok) {
            results.value = await response.json();
        }
    } catch (error) {
        console.error('Failed to search students:', error);
        results.value = [];
    } finally {
        isLoading.value = false;
    }
};

const debouncedSearch = (query: string) => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    debounceTimer = setTimeout(() => {
        search(query);
    }, 300);
};

const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    searchQuery.value = target.value;
    highlightedIndex.value = -1;

    if (!isOpen.value) {
        isOpen.value = true;
    }

    debouncedSearch(target.value);
};

const selectStudent = (student: Student) => {
    haptics.medium();
    emit('update:modelValue', student);
    emit('select', student);
    searchQuery.value = '';
    results.value = [];
    isOpen.value = false;
    highlightedIndex.value = -1;
};

const clearSelection = () => {
    haptics.light();
    emit('update:modelValue', null);
    emit('clear');
    searchQuery.value = '';
    results.value = [];

    // Focus input after clearing
    setTimeout(() => {
        inputRef.value?.focus();
    }, 10);
};

const handleKeydown = (event: KeyboardEvent) => {
    if (!showDropdown.value) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            if (highlightedIndex.value < results.value.length - 1) {
                highlightedIndex.value++;
            }
            break;
        case 'ArrowUp':
            event.preventDefault();
            if (highlightedIndex.value > 0) {
                highlightedIndex.value--;
            }
            break;
        case 'Enter':
            event.preventDefault();
            if (highlightedIndex.value >= 0 && results.value[highlightedIndex.value]) {
                selectStudent(results.value[highlightedIndex.value]);
            }
            break;
        case 'Escape':
            event.preventDefault();
            isOpen.value = false;
            highlightedIndex.value = -1;
            break;
    }
};

const handleFocus = () => {
    if (searchQuery.value.length >= 2) {
        isOpen.value = true;
    }
};

const handleBlur = (event: FocusEvent) => {
    // Delay closing to allow click on dropdown
    setTimeout(() => {
        const relatedTarget = event.relatedTarget as HTMLElement;
        if (!dropdownRef.value?.contains(relatedTarget)) {
            isOpen.value = false;
            highlightedIndex.value = -1;
        }
    }, 150);
};

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!inputRef.value?.contains(target) && !dropdownRef.value?.contains(target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    if (props.autofocus) {
        inputRef.value?.focus();
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
});
</script>

<template>
    <div class="relative">
        <!-- Selected Student Display -->
        <div
            v-if="hasSelection"
            class="flex items-center justify-between gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl"
        >
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                    <User class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-slate-900 dark:text-slate-100 truncate">
                        {{ modelValue?.nama_lengkap }}
                    </p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        NIS: {{ modelValue?.nis }} • {{ modelValue?.kelas }}
                    </p>
                </div>
            </div>
            <button
                type="button"
                @click="clearSelection"
                :disabled="disabled"
                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 dark:hover:bg-zinc-700/50 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                aria-label="Hapus pilihan siswa"
            >
                <X class="w-5 h-5" />
            </button>
        </div>

        <!-- Search Input -->
        <div v-else class="relative">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                <Loader2 v-if="isLoading" class="w-5 h-5 text-slate-400 animate-spin" />
                <Search v-else class="w-5 h-5 text-slate-400" />
            </div>
            <input
                ref="inputRef"
                type="text"
                :value="searchQuery"
                @input="handleInput"
                @keydown="handleKeydown"
                @focus="handleFocus"
                @blur="handleBlur"
                :disabled="disabled"
                :placeholder="placeholder"
                class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:bg-white dark:focus:bg-zinc-900 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                autocomplete="off"
            />
        </div>

        <!-- Dropdown Results -->
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
        >
            <div
                v-if="showDropdown && !hasSelection"
                ref="dropdownRef"
                class="absolute z-50 top-full left-0 right-0 mt-2 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl shadow-xl overflow-hidden"
            >
                <!-- Loading State -->
                <div v-if="isLoading" class="p-4 text-center text-slate-500 dark:text-slate-400">
                    <Loader2 class="w-5 h-5 mx-auto mb-2 animate-spin" />
                    <span class="text-sm">Mencari siswa...</span>
                </div>

                <!-- Results -->
                <div v-else-if="results.length > 0" class="max-h-64 overflow-y-auto">
                    <button
                        v-for="(student, idx) in results"
                        :key="student.id"
                        type="button"
                        @click="selectStudent(student)"
                        @mouseenter="highlightedIndex = idx"
                        :class="[
                            'w-full px-4 py-3 flex items-center gap-3 text-left transition-colors',
                            highlightedIndex === idx
                                ? 'bg-emerald-50 dark:bg-emerald-900/20'
                                : 'hover:bg-slate-50 dark:hover:bg-zinc-800'
                        ]"
                    >
                        <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                            <User class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-900 dark:text-slate-100 truncate">
                                {{ student.nama_lengkap }}
                            </p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                NIS: {{ student.nis }} • {{ student.kelas }}
                            </p>
                        </div>
                        <div v-if="student.total_tunggakan > 0" class="text-right shrink-0">
                            <span class="text-xs text-amber-600 dark:text-amber-400">
                                Tunggakan: {{ student.formatted_tunggakan }}
                            </span>
                        </div>
                    </button>
                </div>

                <!-- No Results -->
                <div v-else-if="searchQuery.length >= 2" class="p-6 text-center">
                    <User class="w-10 h-10 mx-auto mb-2 text-slate-300 dark:text-zinc-600" />
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Siswa tidak ditemukan
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                        Coba kata kunci lain
                    </p>
                </div>

                <!-- Hint -->
                <div v-else class="p-4 text-center text-sm text-slate-400 dark:text-slate-500">
                    Ketik minimal 2 karakter untuk mencari
                </div>
            </div>
        </Transition>
    </div>
</template>
