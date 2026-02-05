<script setup lang="ts">
/**
 * ScheduleForm Component - Form untuk create/edit jadwal mengajar
 * dengan real-time conflict detection dan cascading dropdowns
 */
import { ref, computed, watch } from 'vue';
import { User, BookOpen, GraduationCap, Calendar, Clock, MapPin } from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';
import ConflictWarning from './ConflictWarning.vue';

interface Teacher {
    id: number;
    nama_lengkap: string;
    subjects?: Array<{
        id: number;
        kode_mapel: string;
        nama_mapel: string;
        pivot?: { is_primary: boolean };
    }>;
}

interface Subject {
    id: number;
    kode_mapel: string;
    nama_mapel: string;
}

interface SchoolClass {
    id: number;
    tingkat: number;
    nama: string;
}

interface AcademicYear {
    id: number;
    name: string;
    is_active: boolean;
}

interface HariOption {
    value: string;
    label: string;
}

interface ConflictDetail {
    message: string;
    conflicting_schedule?: {
        id: number;
        subject: string;
        class?: string;
        teacher?: string;
        time: string;
    };
}

interface FormData {
    teacher_id: number | string;
    subject_id: number | string;
    class_id: number | string;
    academic_year_id: number | string;
    hari: string;
    jam_mulai: string;
    jam_selesai: string;
    ruangan: string;
    is_active: boolean;
}

interface Props {
    form: FormData & {
        errors: Record<string, string>;
        processing: boolean;
    };
    teachers: Teacher[];
    subjects: Subject[];
    classes: SchoolClass[];
    academicYears: AcademicYear[];
    hariOptions: HariOption[];
    mode: 'create' | 'edit';
    activeAcademicYearId?: number | null;
}

const props = defineProps<Props>();
defineEmits(['check-conflict']);

const haptics = useHaptics();

// Conflict state
const isCheckingConflict = ref(false);
const conflicts = ref<Record<string, ConflictDetail>>({});
const timeErrors = ref<Record<string, string>>({});

// Get subjects for selected teacher (primary subjects)
const availableSubjects = computed(() => {
    if (!props.form.teacher_id) {
        return props.subjects;
    }

    const selectedTeacher = props.teachers.find(t => t.id === Number(props.form.teacher_id));
    if (selectedTeacher?.subjects && selectedTeacher.subjects.length > 0) {
        return selectedTeacher.subjects;
    }

    return props.subjects;
});

// Common time slots for quick selection
const timeSlots = [
    { start: '07:00', end: '07:45', label: 'Jam 1 (07:00-07:45)' },
    { start: '07:45', end: '08:30', label: 'Jam 2 (07:45-08:30)' },
    { start: '08:30', end: '09:15', label: 'Jam 3 (08:30-09:15)' },
    { start: '09:30', end: '10:15', label: 'Jam 4 (09:30-10:15)' },
    { start: '10:15', end: '11:00', label: 'Jam 5 (10:15-11:00)' },
    { start: '11:00', end: '11:45', label: 'Jam 6 (11:00-11:45)' },
    { start: '12:30', end: '13:15', label: 'Jam 7 (12:30-13:15)' },
    { start: '13:15', end: '14:00', label: 'Jam 8 (13:15-14:00)' },
    { start: '14:00', end: '14:45', label: 'Jam 9 (14:00-14:45)' },
    { start: '14:45', end: '15:30', label: 'Jam 10 (14:45-15:30)' },
];

// Watch for changes that require conflict check
let conflictCheckTimeout: ReturnType<typeof setTimeout> | null = null;

const scheduleConflictCheck = () => {
    if (conflictCheckTimeout) {
        clearTimeout(conflictCheckTimeout);
    }

    // Only check if all required fields are filled
    if (
        props.form.teacher_id &&
        props.form.class_id &&
        props.form.academic_year_id &&
        props.form.hari &&
        props.form.jam_mulai &&
        props.form.jam_selesai
    ) {
        conflictCheckTimeout = setTimeout(() => {
            checkConflict();
        }, 500);
    }
};

watch(
    () => [
        props.form.teacher_id,
        props.form.class_id,
        props.form.academic_year_id,
        props.form.hari,
        props.form.jam_mulai,
        props.form.jam_selesai,
        props.form.ruangan
    ],
    () => {
        scheduleConflictCheck();
    }
);

// Check conflict via API
const checkConflict = async () => {
    isCheckingConflict.value = true;
    conflicts.value = {};
    timeErrors.value = {};

    try {
        const response = await fetch('/admin/teachers/schedules/check-conflict', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                teacher_id: props.form.teacher_id,
                class_id: props.form.class_id,
                academic_year_id: props.form.academic_year_id,
                hari: props.form.hari,
                jam_mulai: props.form.jam_mulai,
                jam_selesai: props.form.jam_selesai,
                ruangan: props.form.ruangan || null,
                exclude_id: props.mode === 'edit' ? (props.form as any).id : null,
            }),
        });

        const data = await response.json();
        conflicts.value = data.conflicts || {};
        timeErrors.value = data.time_errors || {};

        if (data.has_conflicts) {
            haptics.warning();
        }
    } catch (error) {
        console.error('Failed to check conflict:', error);
    } finally {
        isCheckingConflict.value = false;
    }
};

// Has any conflict
const hasConflicts = computed(() => {
    return Object.keys(conflicts.value).length > 0 || Object.keys(timeErrors.value).length > 0;
});

// Apply time slot
const applyTimeSlot = (slot: { start: string; end: string }) => {
    haptics.selection();
    props.form.jam_mulai = slot.start;
    props.form.jam_selesai = slot.end;
};

// Handle teacher change - reset subject if not available
watch(() => props.form.teacher_id, (newValue) => {
    if (newValue && props.form.subject_id) {
        const subjectExists = availableSubjects.value.some(s => s.id === Number(props.form.subject_id));
        if (!subjectExists) {
            props.form.subject_id = '';
        }
    }
});

// Set default academic year
if (!props.form.academic_year_id && props.activeAcademicYearId) {
    props.form.academic_year_id = props.activeAcademicYearId;
}
</script>

<template>
    <div class="space-y-6">
        <!-- Conflict Warning -->
        <ConflictWarning
            v-if="hasConflicts"
            :conflicts="conflicts"
            :time-errors="timeErrors"
            :is-checking="isCheckingConflict"
        />

        <!-- Form Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Teacher -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    <div class="flex items-center gap-2">
                        <User class="w-4 h-4" />
                        <span>Guru <span class="text-red-500">*</span></span>
                    </div>
                </label>
                <select
                    v-model="form.teacher_id"
                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    :class="{ 'border-red-500 dark:border-red-500': form.errors.teacher_id }"
                >
                    <option value="">Pilih Guru</option>
                    <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                        {{ teacher.nama_lengkap }}
                    </option>
                </select>
                <p v-if="form.errors.teacher_id" class="mt-1 text-sm text-red-500">
                    {{ form.errors.teacher_id }}
                </p>
            </div>

            <!-- Subject -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    <div class="flex items-center gap-2">
                        <BookOpen class="w-4 h-4" />
                        <span>Mata Pelajaran <span class="text-red-500">*</span></span>
                    </div>
                </label>
                <select
                    v-model="form.subject_id"
                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    :class="{ 'border-red-500 dark:border-red-500': form.errors.subject_id }"
                >
                    <option value="">Pilih Mata Pelajaran</option>
                    <option v-for="subject in availableSubjects" :key="subject.id" :value="subject.id">
                        {{ subject.nama_mapel }} ({{ subject.kode_mapel }})
                    </option>
                </select>
                <p v-if="form.errors.subject_id" class="mt-1 text-sm text-red-500">
                    {{ form.errors.subject_id }}
                </p>
                <p v-if="form.teacher_id && availableSubjects.length < subjects.length" class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan mata pelajaran yang diajar guru terpilih
                </p>
            </div>

            <!-- Class -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    <div class="flex items-center gap-2">
                        <GraduationCap class="w-4 h-4" />
                        <span>Kelas <span class="text-red-500">*</span></span>
                    </div>
                </label>
                <select
                    v-model="form.class_id"
                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    :class="{ 'border-red-500 dark:border-red-500': form.errors.class_id }"
                >
                    <option value="">Pilih Kelas</option>
                    <option v-for="kelas in classes" :key="kelas.id" :value="kelas.id">
                        Kelas {{ kelas.tingkat }}{{ kelas.nama }}
                    </option>
                </select>
                <p v-if="form.errors.class_id" class="mt-1 text-sm text-red-500">
                    {{ form.errors.class_id }}
                </p>
            </div>

            <!-- Academic Year -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    <div class="flex items-center gap-2">
                        <Calendar class="w-4 h-4" />
                        <span>Tahun Ajaran <span class="text-red-500">*</span></span>
                    </div>
                </label>
                <select
                    v-model="form.academic_year_id"
                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    :class="{ 'border-red-500 dark:border-red-500': form.errors.academic_year_id }"
                >
                    <option value="">Pilih Tahun Ajaran</option>
                    <option v-for="year in academicYears" :key="year.id" :value="year.id">
                        {{ year.name }}{{ year.is_active ? ' (Aktif)' : '' }}
                    </option>
                </select>
                <p v-if="form.errors.academic_year_id" class="mt-1 text-sm text-red-500">
                    {{ form.errors.academic_year_id }}
                </p>
            </div>

            <!-- Day -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    <div class="flex items-center gap-2">
                        <Calendar class="w-4 h-4" />
                        <span>Hari <span class="text-red-500">*</span></span>
                    </div>
                </label>
                <select
                    v-model="form.hari"
                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    :class="{ 'border-red-500 dark:border-red-500': form.errors.hari }"
                >
                    <option value="">Pilih Hari</option>
                    <option v-for="option in hariOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
                <p v-if="form.errors.hari" class="mt-1 text-sm text-red-500">
                    {{ form.errors.hari }}
                </p>
            </div>

            <!-- Room -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    <div class="flex items-center gap-2">
                        <MapPin class="w-4 h-4" />
                        <span>Ruangan</span>
                    </div>
                </label>
                <input
                    v-model="form.ruangan"
                    type="text"
                    placeholder="Contoh: R. 101, Lab Komputer"
                    class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    :class="{ 'border-red-500 dark:border-red-500': form.errors.ruangan }"
                />
                <p v-if="form.errors.ruangan" class="mt-1 text-sm text-red-500">
                    {{ form.errors.ruangan }}
                </p>
            </div>
        </div>

        <!-- Time Section -->
        <div class="space-y-4">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                <div class="flex items-center gap-2">
                    <Clock class="w-4 h-4" />
                    <span>Waktu <span class="text-red-500">*</span></span>
                </div>
            </label>

            <!-- Quick Time Slots -->
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="slot in timeSlots"
                    :key="slot.start"
                    type="button"
                    @click="applyTimeSlot(slot)"
                    class="px-3 py-1.5 text-xs rounded-lg transition-colors"
                    :class="form.jam_mulai === slot.start && form.jam_selesai === slot.end
                        ? 'bg-blue-500 text-white'
                        : 'bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-700'"
                >
                    {{ slot.label }}
                </button>
            </div>

            <!-- Custom Time Input -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Jam Mulai</label>
                    <input
                        v-model="form.jam_mulai"
                        type="time"
                        min="07:00"
                        max="16:00"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        :class="{ 'border-red-500 dark:border-red-500': form.errors.jam_mulai || timeErrors.jam_mulai }"
                    />
                    <p v-if="form.errors.jam_mulai" class="mt-1 text-sm text-red-500">
                        {{ form.errors.jam_mulai }}
                    </p>
                    <p v-else-if="timeErrors.jam_mulai" class="mt-1 text-sm text-red-500">
                        {{ timeErrors.jam_mulai }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Jam Selesai</label>
                    <input
                        v-model="form.jam_selesai"
                        type="time"
                        min="07:00"
                        max="16:00"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        :class="{ 'border-red-500 dark:border-red-500': form.errors.jam_selesai || timeErrors.jam_selesai }"
                    />
                    <p v-if="form.errors.jam_selesai" class="mt-1 text-sm text-red-500">
                        {{ form.errors.jam_selesai }}
                    </p>
                    <p v-else-if="timeErrors.jam_selesai" class="mt-1 text-sm text-red-500">
                        {{ timeErrors.jam_selesai }}
                    </p>
                </div>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Jam sekolah: 07:00 - 16:00. Pilih slot waktu atau masukkan manual.
            </p>
        </div>

        <!-- Checking Indicator -->
        <div v-if="isCheckingConflict" class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Memeriksa konflik jadwal...</span>
        </div>
    </div>
</template>
