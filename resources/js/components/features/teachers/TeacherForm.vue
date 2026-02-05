<script setup lang="ts">
/**
 * TeacherForm - Form komprehensif untuk data guru
 * dengan sections untuk biodata, kepegawaian, dan akademik
 */
import { ref, computed, watch } from 'vue';
import { ChevronDown, User, Briefcase, GraduationCap, Check, Camera, X } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { FormInput, FormSelect, FormTextarea } from '@/components/ui/Form';
import { useHaptics } from '@/composables/useHaptics';

interface StatusOption {
    value: string;
    label: string;
}

interface Props {
    form: any; // Inertia form object
    mode?: 'create' | 'edit';
    subjects?: Array<{ id: number; kode_mapel: string; nama_mapel: string }>;
    statusOptions?: StatusOption[];
    currentFoto?: string | null;
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    subjects: () => [],
    statusOptions: () => [],
    currentFoto: null
});

const haptics = useHaptics();

// Sections state
const sections = ref({
    biodata: true,
    kepegawaian: true,
    akademik: true
});

// Photo preview state
const photoPreview = ref<string | null>(null);
const photoInput = ref<HTMLInputElement | null>(null);

// Initialize photo preview for edit mode
if (props.currentFoto) {
    photoPreview.value = `/storage/${props.currentFoto}`;
}

// Watch for status_kepegawaian changes to handle tanggal_berakhir_kontrak visibility
const showKontrakEndDate = computed(() => props.form.status_kepegawaian === 'kontrak');

// Clear kontrak end date if status changes
watch(() => props.form.status_kepegawaian, (newVal) => {
    if (newVal !== 'kontrak') {
        props.form.tanggal_berakhir_kontrak = '';
    }
});

// Photo handling
const handlePhotoSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        props.form.foto = file;
        photoPreview.value = URL.createObjectURL(file);
        haptics.light();
    }
};

const removePhoto = () => {
    props.form.foto = null;
    photoPreview.value = null;
    if (photoInput.value) {
        photoInput.value.value = '';
    }
    haptics.light();
};

const triggerPhotoUpload = () => {
    photoInput.value?.click();
};

// Section toggle
const toggleSection = (section: keyof typeof sections.value) => {
    haptics.selection();
    sections.value[section] = !sections.value[section];
};

// Form options
const genders = [
    { value: 'L', label: 'Laki-laki' },
    { value: 'P', label: 'Perempuan' }
];

const educationLevels = [
    { value: 'D3', label: 'D3' },
    { value: 'S1', label: 'S1' },
    { value: 'S2', label: 'S2' },
    { value: 'S3', label: 'S3' }
];

// Subject selection handling
const toggleSubject = (subjectId: number) => {
    haptics.light();
    const index = props.form.subjects.indexOf(subjectId);
    if (index > -1) {
        props.form.subjects.splice(index, 1);
    } else {
        props.form.subjects.push(subjectId);
    }
};

const isSubjectSelected = (subjectId: number) => {
    return props.form.subjects.includes(subjectId);
};

// Progress tracking
const sectionFields = {
    biodata: ['nik', 'nama_lengkap', 'jenis_kelamin', 'email'],
    kepegawaian: ['status_kepegawaian'],
    akademik: []
};

const sectionProgress = computed(() => {
    const progress: Record<string, { filled: number; total: number; complete: boolean }> = {};

    if (!props.form) {
        for (const section of Object.keys(sectionFields)) {
            progress[section] = { filled: 0, total: 0, complete: false };
        }
        return progress;
    }

    for (const [section, fields] of Object.entries(sectionFields)) {
        if (fields.length === 0) {
            progress[section] = { filled: 0, total: 0, complete: true };
            continue;
        }

        const filled = fields.filter(field => {
            const value = props.form[field];
            return value !== null && value !== undefined && value !== '';
        }).length;

        progress[section] = {
            filled,
            total: fields.length,
            complete: filled === fields.length
        };
    }

    return progress;
});

const totalProgress = computed(() => {
    const sections = Object.values(sectionProgress.value).filter(s => s.total > 0);
    if (sections.length === 0) return 0;

    const totalFilled = sections.reduce((sum, s) => sum + s.filled, 0);
    const totalFields = sections.reduce((sum, s) => sum + s.total, 0);

    if (totalFields === 0) return 0;
    return Math.round((totalFilled / totalFields) * 100);
});
</script>

<template>
    <div class="space-y-4">
        <!-- Progress Indicator -->
        <div class="sticky top-0 z-20 -mx-4 px-4 sm:mx-0 sm:px-0">
            <div class="bg-white/95 dark:bg-zinc-900/95 backdrop-blur-sm border border-slate-200 dark:border-zinc-800 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <Check class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">Progress Pengisian</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ totalProgress }}% selesai</p>
                        </div>
                    </div>
                </div>
                <div class="h-2 bg-slate-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                    <Motion
                        :animate="{ width: `${totalProgress}%` }"
                        :transition="{ duration: 0.3, ease: 'easeOut' }"
                        class="h-full bg-linear-to-r from-blue-500 to-indigo-500 rounded-full"
                    />
                </div>
            </div>
        </div>

        <!-- Section 1: Biodata Pribadi -->
        <Motion
            :initial="{ opacity: 0, y: 10 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.3, ease: 'easeOut' }"
        >
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                <!-- Section Header -->
                <button
                    type="button"
                    @click="toggleSection('biodata')"
                    class="w-full px-5 py-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                             :class="sectionProgress.biodata?.complete
                                ? 'bg-blue-100 dark:bg-blue-900/30'
                                : 'bg-slate-100 dark:bg-zinc-800'">
                            <User class="w-5 h-5" :class="sectionProgress.biodata?.complete
                                ? 'text-blue-600 dark:text-blue-400'
                                : 'text-slate-500 dark:text-slate-400'" />
                        </div>
                        <div class="text-left">
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100">Data Pribadi</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">NIK, nama, dan informasi kontak</p>
                        </div>
                    </div>
                    <ChevronDown
                        class="w-5 h-5 text-slate-400 transition-transform duration-200"
                        :class="{ 'rotate-180': sections.biodata }"
                    />
                </button>

                <!-- Section Content -->
                <div v-show="sections.biodata" class="px-5 pb-5 space-y-4 border-t border-slate-100 dark:border-zinc-800">
                    <!-- Photo Upload -->
                    <div class="pt-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Foto Profil</label>
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div
                                    v-if="photoPreview"
                                    class="w-24 h-24 rounded-xl overflow-hidden border-2 border-slate-200 dark:border-zinc-700"
                                >
                                    <img :src="photoPreview" alt="Preview" class="w-full h-full object-cover" />
                                    <button
                                        type="button"
                                        @click="removePhoto"
                                        class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-red-600 transition-colors"
                                    >
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                                <button
                                    v-else
                                    type="button"
                                    @click="triggerPhotoUpload"
                                    class="w-24 h-24 rounded-xl border-2 border-dashed border-slate-300 dark:border-zinc-700 flex flex-col items-center justify-center gap-1 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/10 transition-colors"
                                >
                                    <Camera class="w-6 h-6 text-slate-400" />
                                    <span class="text-xs text-slate-500">Upload</span>
                                </button>
                                <input
                                    ref="photoInput"
                                    type="file"
                                    accept="image/jpeg,image/png,image/jpg"
                                    class="hidden"
                                    @change="handlePhotoSelect"
                                />
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                <p>Format: JPG, JPEG, PNG</p>
                                <p>Maksimal: 2MB</p>
                            </div>
                        </div>
                        <p v-if="form.errors.foto" class="mt-1 text-sm text-red-500">{{ form.errors.foto }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <FormInput
                            v-model="form.nik"
                            label="NIK"
                            placeholder="Masukkan 16 digit NIK"
                            :error="form.errors.nik"
                            required
                            maxlength="16"
                        />
                        <FormInput
                            v-model="form.nama_lengkap"
                            label="Nama Lengkap"
                            placeholder="Nama lengkap sesuai KTP"
                            :error="form.errors.nama_lengkap"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <FormInput
                            v-model="form.tempat_lahir"
                            label="Tempat Lahir"
                            placeholder="Kota kelahiran"
                            :error="form.errors.tempat_lahir"
                        />
                        <FormInput
                            v-model="form.tanggal_lahir"
                            type="date"
                            label="Tanggal Lahir"
                            :error="form.errors.tanggal_lahir"
                        />
                    </div>

                    <FormSelect
                        v-model="form.jenis_kelamin"
                        label="Jenis Kelamin"
                        :options="genders"
                        placeholder="Pilih jenis kelamin"
                        :error="form.errors.jenis_kelamin"
                        required
                    />

                    <FormTextarea
                        v-model="form.alamat"
                        label="Alamat"
                        placeholder="Alamat lengkap"
                        :error="form.errors.alamat"
                        rows="2"
                    />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <FormInput
                            v-model="form.no_hp"
                            label="No. HP"
                            placeholder="08xxxxxxxxxx"
                            :error="form.errors.no_hp"
                        />
                        <FormInput
                            v-model="form.email"
                            type="email"
                            label="Email"
                            placeholder="email@domain.com"
                            :error="form.errors.email"
                            required
                        />
                    </div>
                </div>
            </div>
        </Motion>

        <!-- Section 2: Data Kepegawaian -->
        <Motion
            :initial="{ opacity: 0, y: 10 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
        >
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                <button
                    type="button"
                    @click="toggleSection('kepegawaian')"
                    class="w-full px-5 py-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                             :class="sectionProgress.kepegawaian?.complete
                                ? 'bg-blue-100 dark:bg-blue-900/30'
                                : 'bg-slate-100 dark:bg-zinc-800'">
                            <Briefcase class="w-5 h-5" :class="sectionProgress.kepegawaian?.complete
                                ? 'text-blue-600 dark:text-blue-400'
                                : 'text-slate-500 dark:text-slate-400'" />
                        </div>
                        <div class="text-left">
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100">Data Kepegawaian</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">NIP, status, dan masa kerja</p>
                        </div>
                    </div>
                    <ChevronDown
                        class="w-5 h-5 text-slate-400 transition-transform duration-200"
                        :class="{ 'rotate-180': sections.kepegawaian }"
                    />
                </button>

                <div v-show="sections.kepegawaian" class="px-5 pb-5 space-y-4 border-t border-slate-100 dark:border-zinc-800">
                    <div class="pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <FormInput
                            v-model="form.nip"
                            label="NIP"
                            placeholder="Nomor Induk Pegawai (kosongkan jika honorer)"
                            :error="form.errors.nip"
                            :hint="form.status_kepegawaian === 'honorer' ? 'Tidak wajib untuk guru honorer' : ''"
                        />
                        <FormSelect
                            v-model="form.status_kepegawaian"
                            label="Status Kepegawaian"
                            :options="statusOptions"
                            placeholder="Pilih status"
                            :error="form.errors.status_kepegawaian"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <FormInput
                            v-model="form.tanggal_mulai_kerja"
                            type="date"
                            label="Tanggal Mulai Kerja"
                            :error="form.errors.tanggal_mulai_kerja"
                        />
                        <FormInput
                            v-if="showKontrakEndDate"
                            v-model="form.tanggal_berakhir_kontrak"
                            type="date"
                            label="Tanggal Berakhir Kontrak"
                            :error="form.errors.tanggal_berakhir_kontrak"
                            required
                        />
                    </div>
                </div>
            </div>
        </Motion>

        <!-- Section 3: Data Akademik -->
        <Motion
            :initial="{ opacity: 0, y: 10 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
        >
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                <button
                    type="button"
                    @click="toggleSection('akademik')"
                    class="w-full px-5 py-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                            <GraduationCap class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                        </div>
                        <div class="text-left">
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100">Data Akademik</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Kualifikasi dan mata pelajaran</p>
                        </div>
                    </div>
                    <ChevronDown
                        class="w-5 h-5 text-slate-400 transition-transform duration-200"
                        :class="{ 'rotate-180': sections.akademik }"
                    />
                </button>

                <div v-show="sections.akademik" class="px-5 pb-5 space-y-4 border-t border-slate-100 dark:border-zinc-800">
                    <div class="pt-4">
                        <FormSelect
                            v-model="form.kualifikasi_pendidikan"
                            label="Kualifikasi Pendidikan"
                            :options="educationLevels"
                            placeholder="Pilih jenjang pendidikan"
                            :error="form.errors.kualifikasi_pendidikan"
                        />
                    </div>

                    <!-- Subjects Selection -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Mata Pelajaran yang Diajar
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="subject in subjects"
                                :key="subject.id"
                                type="button"
                                @click="toggleSubject(subject.id)"
                                class="px-3 py-1.5 rounded-lg text-sm font-medium border transition-all duration-150"
                                :class="isSubjectSelected(subject.id)
                                    ? 'bg-blue-500 text-white border-blue-500 shadow-md shadow-blue-500/25'
                                    : 'bg-slate-50 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-zinc-700 hover:border-blue-300 dark:hover:border-blue-600'"
                            >
                                {{ subject.nama_mapel }}
                            </button>
                        </div>
                        <p v-if="form.errors.subjects" class="mt-1 text-sm text-red-500">{{ form.errors.subjects }}</p>
                        <p v-if="form.subjects.length === 0" class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            Klik untuk memilih mata pelajaran
                        </p>
                        <p v-else class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            {{ form.subjects.length }} mata pelajaran dipilih
                        </p>
                    </div>
                </div>
            </div>
        </Motion>
    </div>
</template>
