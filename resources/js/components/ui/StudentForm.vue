<script setup lang="ts">
/**
 * StudentForm - Comprehensive multi-section form untuk data siswa
 * dengan refined aesthetic, collapsible sections, dan cohesive design language
 * untuk memastikan UX optimal dan visual yang premium
 */
import { ref, computed, watch } from 'vue';
import { ChevronDown, User, MapPin, GraduationCap, Users, IdCard, Calendar, Heart, Info, Check } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import PhotoUpload from '@/components/ui/PhotoUpload.vue';
import { FormInput, FormSelect, FormTextarea, FormNumberInput, FormCheckbox } from '@/components/ui/Form';
import { useHaptics } from '@/composables/useHaptics';

interface Props {
    form: any; // Inertia form object
    mode?: 'create' | 'edit';
    classes?: Array<{ id: number; nama: string }>;
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    classes: () => []
});

const haptics = useHaptics();

// Sections state - biodata, address, academic, dan parent sections open by default
// karena parent sections memiliki required fields
const sections = ref({
    biodata: true,
    address: true,
    academic: true,
    father: true,  // Open - has required fields
    mother: true,  // Open - has required fields
    guardian: false // Truly optional
});

// Progress tracking untuk form completion
const sectionFields = {
    biodata: ['nama_lengkap', 'nik', 'nisn', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama'],
    address: ['alamat', 'kelurahan', 'kecamatan', 'kota', 'provinsi'],
    academic: ['tahun_ajaran_masuk', 'tanggal_masuk'],
    father: ['ayah.nama_lengkap', 'ayah.nik', 'ayah.no_hp'],
    mother: ['ibu.nama_lengkap', 'ibu.nik', 'ibu.no_hp'],
    guardian: [] // Optional, no required fields
};

const getNestedValue = (obj: any, path: string) => {
    if (!obj) return null;
    return path.split('.').reduce((acc, part) => acc && acc[part], obj);
};

const sectionProgress = computed(() => {
    const progress: Record<string, { filled: number; total: number; complete: boolean }> = {};

    // Guard: jika form belum initialized, return empty progress
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
            try {
                const value = getNestedValue(props.form, field);
                return value !== null && value !== undefined && value !== '';
            } catch (e) {
                return false;
            }
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

const completedSections = computed(() => {
    if (!sectionProgress.value) return 0;
    return Object.values(sectionProgress.value).filter(s => s.complete && s.total > 0).length;
});

const totalSections = computed(() => {
    if (!sectionProgress.value) return 0;
    return Object.values(sectionProgress.value).filter(s => s.total > 0).length;
});

const toggleSection = (section: keyof typeof sections.value) => {
    haptics.selection();
    sections.value[section] = !sections.value[section];
};

// Form Options
const genders = [
    { value: 'L', label: 'Laki-laki' },
    { value: 'P', label: 'Perempuan' }
];

const religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'];

const familyStatuses = ['Anak Kandung', 'Anak Angkat', 'Anak Tiri', 'Lainnya'];

const educationLevels = ['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3', 'Tidak Sekolah', 'Lainnya'];

const incomeRanges = [
    { value: '<1jt', label: '< Rp 1 Juta' },
    { value: '1-3jt', label: 'Rp 1 - 3 Juta' },
    { value: '3-5jt', label: 'Rp 3 - 5 Juta' },
    { value: '5-10jt', label: 'Rp 5 - 10 Juta' },
    { value: '>10jt', label: '> Rp 10 Juta' }
];

// Section config untuk cleaner template
const sectionConfig = [
    { key: 'biodata', icon: User, label: 'Biodata Siswa', desc: 'Identitas dan data pribadi siswa', accent: 'emerald' },
    { key: 'address', icon: MapPin, label: 'Alamat & Kontak', desc: 'Domisili dan informasi kontak', accent: 'emerald' },
    { key: 'academic', icon: GraduationCap, label: 'Data Akademik', desc: 'Kelas dan tahun ajaran', accent: 'emerald' },
    { key: 'father', icon: Users, label: 'Data Ayah', desc: 'Informasi ayah kandung', accent: 'sky' },
    { key: 'mother', icon: Heart, label: 'Data Ibu', desc: 'Informasi ibu kandung', accent: 'rose' },
    { key: 'guardian', icon: Users, label: 'Data Wali', desc: 'Wali atau pengasuh (opsional)', accent: 'slate', optional: true },
] as const;
</script>

<template>
    <div class="space-y-4">
        <!-- Progress Indicator - Sticky at top -->
        <div class="sticky top-0 z-20 -mx-4 px-4 sm:mx-0 sm:px-0">
            <div class="bg-white/95 dark:bg-zinc-900/95 backdrop-blur-sm border border-slate-200 dark:border-zinc-800 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <Check class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                Progress Pengisian
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ completedSections }} dari {{ totalSections }} bagian selesai
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ totalProgress }}%</span>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="h-2 bg-slate-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                    <Motion
                        :animate="{ width: totalProgress + '%' }"
                        :transition="{ duration: 0.5, ease: 'easeOut' }"
                        class="h-full bg-linear-to-r from-emerald-500 to-teal-500 rounded-full"
                    />
                </div>

                <!-- Section Pills -->
                <div class="flex gap-1.5 mt-3 overflow-x-auto pb-1 -mx-1 px-1 scrollbar-hide">
                    <button
                        v-for="(config, index) in [
                            { key: 'biodata', label: 'Biodata' },
                            { key: 'address', label: 'Alamat' },
                            { key: 'academic', label: 'Akademik' },
                            { key: 'father', label: 'Ayah' },
                            { key: 'mother', label: 'Ibu' },
                        ]"
                        :key="config.key"
                        type="button"
                        @click="() => { sections[config.key as keyof typeof sections] = true; haptics.selection(); }"
                        class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
                        :class="sectionProgress[config.key]?.complete
                            ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800'
                            : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-zinc-700'"
                    >
                        <span v-if="sectionProgress[config.key]?.complete" class="w-3.5 h-3.5 rounded-full bg-emerald-500 flex items-center justify-center">
                            <Check class="w-2.5 h-2.5 text-white" />
                        </span>
                        <span v-else class="w-3.5 h-3.5 rounded-full bg-slate-300 dark:bg-zinc-600 flex items-center justify-center text-[10px] text-white font-bold">
                            {{ index + 1 }}
                        </span>
                        {{ config.label }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Section 1: Biodata Siswa -->
        <Motion
            :initial="{ opacity: 0, y: 8 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.35, ease: 'easeOut' }"
        >
            <div class="bg-white dark:bg-zinc-950 rounded-2xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden shadow-sm">
                <!-- Section Header -->
                <Motion :whileTap="{ scale: 0.995 }">
                    <button
                        type="button"
                        @click="toggleSection('biodata')"
                        class="w-full px-5 py-4 flex items-center justify-between hover:bg-slate-50/50 dark:hover:bg-zinc-900/50 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-linear-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                <User class="w-5 h-5 text-white" />
                            </div>
                            <div class="text-left">
                                <h3 class="font-semibold text-slate-800 dark:text-slate-100 text-[15px]">Biodata Siswa</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Identitas dan data pribadi siswa</p>
                            </div>
                        </div>
                        <Motion
                            :animate="{ rotate: sections.biodata ? 180 : 0 }"
                            :transition="{ duration: 0.2 }"
                        >
                            <ChevronDown class="w-5 h-5 text-slate-400" />
                        </Motion>
                    </button>
                </Motion>

                <!-- Section Content -->
                <Motion
                    :initial="false"
                    :animate="{ height: sections.biodata ? 'auto' : 0, opacity: sections.biodata ? 1 : 0 }"
                    :transition="{ duration: 0.3, ease: 'circOut' }"
                    class="overflow-hidden"
                >
                    <div class="px-5 pb-6 pt-2 space-y-6 border-t border-slate-100 dark:border-zinc-800/50">
                        <div class="grid grid-cols-1 lg:grid-cols-[180px_1fr] gap-6">
                            <!-- Photo Upload -->
                            <div>
                                <PhotoUpload
                                    v-model="form.foto"
                                    :error="form.errors.foto"
                                    label="Foto Siswa"
                                    hint="Foto 3x4, maks 2MB"
                                />
                            </div>

                            <!-- Main Identity Fields -->
                            <div class="space-y-5">
                                <!-- Nama Lengkap -->
                                <FormInput
                                    v-model="form.nama_lengkap"
                                    label="Nama Lengkap"
                                    placeholder="Sesuai Akta Kelahiran"
                                    :error="form.errors.nama_lengkap"
                                    :icon="User"
                                    required
                                />

                                <!-- Row: Nama Panggilan & NIK -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <FormInput
                                        v-model="form.nama_panggilan"
                                        label="Nama Panggilan"
                                        placeholder="Nama sehari-hari"
                                        :error="form.errors.nama_panggilan"
                                    />

                                    <FormInput
                                        v-model="form.nik"
                                        label="NIK"
                                        type="text"
                                        placeholder="16 digit"
                                        :error="form.errors.nik"
                                        :icon="IdCard"
                                        :maxlength="16"
                                        input-class="font-mono tracking-wider"
                                        required
                                    />
                                </div>

                                <!-- Row: NISN & Jenis Kelamin -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <FormInput
                                        v-model="form.nisn"
                                        label="NISN"
                                        type="text"
                                        placeholder="10 digit"
                                        :error="form.errors.nisn"
                                        :maxlength="10"
                                        input-class="font-mono tracking-wider"
                                        required
                                    />

                                    <FormSelect
                                        v-model="form.jenis_kelamin"
                                        label="Jenis Kelamin"
                                        :options="genders"
                                        placeholder="Pilih jenis kelamin"
                                        :error="form.errors.jenis_kelamin"
                                        required
                                    />
                                </div>

                                <!-- Row: Tempat & Tanggal Lahir -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <FormInput
                                        v-model="form.tempat_lahir"
                                        label="Tempat Lahir"
                                        placeholder="Kota kelahiran"
                                        :error="form.errors.tempat_lahir"
                                        required
                                    />

                                    <FormInput
                                        v-model="form.tanggal_lahir"
                                        label="Tanggal Lahir"
                                        type="date"
                                        :error="form.errors.tanggal_lahir"
                                        :icon="Calendar"
                                        required
                                    />
                                </div>

                                <!-- Row: Agama & Status Keluarga -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <FormSelect
                                        v-model="form.agama"
                                        label="Agama"
                                        :options="religions"
                                        placeholder="Pilih agama"
                                        :error="form.errors.agama"
                                        required
                                    />

                                    <FormSelect
                                        v-model="form.status_keluarga"
                                        label="Status Keluarga"
                                        :options="familyStatuses"
                                        placeholder="Pilih status"
                                        :error="form.errors.status_keluarga"
                                    />
                                </div>

                                <!-- Row: Anak Ke & Jumlah Saudara -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <FormNumberInput
                                        v-model="form.anak_ke"
                                        label="Anak Ke"
                                        :min="1"
                                        :max="20"
                                        :error="form.errors.anak_ke"
                                    />

                                    <FormNumberInput
                                        v-model="form.jumlah_saudara"
                                        label="Jumlah Saudara"
                                        :min="0"
                                        :max="20"
                                        :error="form.errors.jumlah_saudara"
                                        hint="Termasuk diri sendiri"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>
            </div>
        </Motion>

        <!-- Section 2: Alamat & Kontak -->
        <Motion
            :initial="{ opacity: 0, y: 8 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.35, ease: 'easeOut', delay: 0.05 }"
        >
            <div class="bg-white dark:bg-zinc-950 rounded-2xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden shadow-sm">
                <Motion :whileTap="{ scale: 0.995 }">
                    <button
                        type="button"
                        @click="toggleSection('address')"
                        class="w-full px-5 py-4 flex items-center justify-between hover:bg-slate-50/50 dark:hover:bg-zinc-900/50 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-linear-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                <MapPin class="w-5 h-5 text-white" />
                            </div>
                            <div class="text-left">
                                <h3 class="font-semibold text-slate-800 dark:text-slate-100 text-[15px]">Alamat & Kontak</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Domisili dan informasi kontak</p>
                            </div>
                        </div>
                        <Motion :animate="{ rotate: sections.address ? 180 : 0 }" :transition="{ duration: 0.2 }">
                            <ChevronDown class="w-5 h-5 text-slate-400" />
                        </Motion>
                    </button>
                </Motion>

                <Motion
                    :initial="false"
                    :animate="{ height: sections.address ? 'auto' : 0, opacity: sections.address ? 1 : 0 }"
                    :transition="{ duration: 0.3, ease: 'circOut' }"
                    class="overflow-hidden"
                >
                    <div class="px-5 pb-6 pt-2 space-y-5 border-t border-slate-100 dark:border-zinc-800/50">
                        <!-- Alamat Lengkap -->
                        <FormTextarea
                            v-model="form.alamat"
                            label="Alamat Lengkap"
                            placeholder="Jalan, nomor rumah, RT/RW"
                            :rows="3"
                            :error="form.errors.alamat"
                            required
                        />

                        <!-- Row: RT/RW & Kelurahan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormInput
                                v-model="form.rt_rw"
                                label="RT / RW"
                                placeholder="001/002"
                                :error="form.errors.rt_rw"
                            />

                            <FormInput
                                v-model="form.kelurahan"
                                label="Kelurahan / Desa"
                                placeholder="Nama kelurahan"
                                :error="form.errors.kelurahan"
                                required
                            />
                        </div>

                        <!-- Row: Kecamatan & Kota -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormInput
                                v-model="form.kecamatan"
                                label="Kecamatan"
                                placeholder="Nama kecamatan"
                                :error="form.errors.kecamatan"
                                required
                            />

                            <FormInput
                                v-model="form.kota"
                                label="Kota / Kabupaten"
                                placeholder="Nama kota/kabupaten"
                                :error="form.errors.kota"
                                required
                            />
                        </div>

                        <!-- Row: Provinsi & Kode Pos -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormInput
                                v-model="form.provinsi"
                                label="Provinsi"
                                placeholder="Nama provinsi"
                                :error="form.errors.provinsi"
                                required
                            />

                            <FormInput
                                v-model="form.kode_pos"
                                label="Kode Pos"
                                type="text"
                                placeholder="12345"
                                :error="form.errors.kode_pos"
                                :maxlength="5"
                            />
                        </div>

                        <!-- Row: No HP & Email -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormInput
                                v-model="form.no_hp"
                                label="No. HP (Siswa)"
                                type="tel"
                                placeholder="08123456789"
                                :error="form.errors.no_hp"
                                hint="Nomor HP siswa (opsional)"
                            />

                            <FormInput
                                v-model="form.email"
                                label="Email (Siswa)"
                                type="email"
                                placeholder="siswa@email.com"
                                :error="form.errors.email"
                                hint="Email siswa (opsional)"
                            />
                        </div>
                    </div>
                </Motion>
            </div>
        </Motion>

        <!-- Section 3: Data Akademik -->
        <Motion
            :initial="{ opacity: 0, y: 8 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.35, ease: 'easeOut', delay: 0.1 }"
        >
            <div class="bg-white dark:bg-zinc-950 rounded-2xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden shadow-sm">
                <Motion :whileTap="{ scale: 0.995 }">
                    <button
                        type="button"
                        @click="toggleSection('academic')"
                        class="w-full px-5 py-4 flex items-center justify-between hover:bg-slate-50/50 dark:hover:bg-zinc-900/50 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-linear-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                <GraduationCap class="w-5 h-5 text-white" />
                            </div>
                            <div class="text-left">
                                <h3 class="font-semibold text-slate-800 dark:text-slate-100 text-[15px]">Data Akademik</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelas dan tahun ajaran</p>
                            </div>
                        </div>
                        <Motion :animate="{ rotate: sections.academic ? 180 : 0 }" :transition="{ duration: 0.2 }">
                            <ChevronDown class="w-5 h-5 text-slate-400" />
                        </Motion>
                    </button>
                </Motion>

                <Motion
                    :initial="false"
                    :animate="{ height: sections.academic ? 'auto' : 0, opacity: sections.academic ? 1 : 0 }"
                    :transition="{ duration: 0.3, ease: 'circOut' }"
                    class="overflow-hidden"
                >
                    <div class="px-5 pb-6 pt-2 border-t border-slate-100 dark:border-zinc-800/50">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <FormSelect
                                v-model="form.kelas_id"
                                label="Kelas Masuk"
                                :options="classes.map(c => ({ value: c.id, label: c.nama }))"
                                placeholder="Pilih kelas"
                                :error="form.errors.kelas_id"
                                hint="Kelas saat pertama masuk"
                            />

                            <FormInput
                                v-model="form.tahun_ajaran_masuk"
                                label="Tahun Ajaran Masuk"
                                type="text"
                                placeholder="2024/2025"
                                :error="form.errors.tahun_ajaran_masuk"
                                required
                            />

                            <FormInput
                                v-model="form.tanggal_masuk"
                                label="Tanggal Masuk"
                                type="date"
                                :error="form.errors.tanggal_masuk"
                                :icon="Calendar"
                                required
                            />
                        </div>
                    </div>
                </Motion>
            </div>
        </Motion>

        <!-- Divider untuk Guardian Section -->
        <div class="relative py-3">
            <div class="absolute inset-0 flex items-center px-2">
                <div class="w-full border-t border-dashed border-slate-200 dark:border-zinc-800"></div>
            </div>
            <div class="relative flex justify-center">
                <span class="bg-slate-50 dark:bg-zinc-900 px-4 py-1.5 rounded-full text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <Users class="w-3.5 h-3.5" />
                    Data Orang Tua / Wali
                </span>
            </div>
        </div>

        <!-- Section 4: Data Ayah -->
        <Motion
            :initial="{ opacity: 0, y: 8 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.35, ease: 'easeOut', delay: 0.15 }"
        >
            <div class="bg-white dark:bg-zinc-950 rounded-2xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden shadow-sm">
                <Motion :whileTap="{ scale: 0.995 }">
                    <button
                        type="button"
                        @click="toggleSection('father')"
                        class="w-full px-5 py-4 flex items-center justify-between hover:bg-slate-50/50 dark:hover:bg-zinc-900/50 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-linear-to-br from-sky-500 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-500/20">
                                <Users class="w-5 h-5 text-white" />
                            </div>
                            <div class="text-left">
                                <h3 class="font-semibold text-slate-800 dark:text-slate-100 text-[15px]">Data Ayah</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Informasi ayah kandung</p>
                            </div>
                        </div>
                        <Motion :animate="{ rotate: sections.father ? 180 : 0 }" :transition="{ duration: 0.2 }">
                            <ChevronDown class="w-5 h-5 text-slate-400" />
                        </Motion>
                    </button>
                </Motion>

                <Motion
                    :initial="false"
                    :animate="{ height: sections.father ? 'auto' : 0, opacity: sections.father ? 1 : 0 }"
                    :transition="{ duration: 0.3, ease: 'circOut' }"
                    class="overflow-hidden"
                >
                    <div class="px-5 pb-6 pt-2 space-y-5 border-t border-slate-100 dark:border-zinc-800/50">
                        <!-- Primary Contact Checkbox -->
                        <FormCheckbox
                            v-model="form.ayah.is_primary_contact"
                            label="Jadikan Kontak Utama"
                            description="Kontak utama akan menerima notifikasi dan informasi penting"
                        />

                        <!-- Row: Nama & NIK -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormInput
                                v-model="form.ayah.nama_lengkap"
                                label="Nama Lengkap Ayah"
                                placeholder="Nama sesuai KTP"
                                :error="form.errors['ayah.nama_lengkap']"
                                :icon="User"
                                required
                            />

                            <FormInput
                                v-model="form.ayah.nik"
                                label="NIK Ayah"
                                type="text"
                                placeholder="16 digit"
                                :error="form.errors['ayah.nik']"
                                :icon="IdCard"
                                :maxlength="16"
                                input-class="font-mono tracking-wider"
                                required
                            />
                        </div>

                        <!-- Row: Pekerjaan & Pendidikan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormInput
                                v-model="form.ayah.pekerjaan"
                                label="Pekerjaan"
                                placeholder="Contoh: Wiraswasta"
                                :error="form.errors['ayah.pekerjaan']"
                            />

                            <FormSelect
                                v-model="form.ayah.pendidikan"
                                label="Pendidikan Terakhir"
                                :options="educationLevels"
                                placeholder="Pilih pendidikan"
                                :error="form.errors['ayah.pendidikan']"
                            />
                        </div>

                        <!-- Row: Penghasilan & No HP -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormSelect
                                v-model="form.ayah.penghasilan"
                                label="Penghasilan Per Bulan"
                                :options="incomeRanges"
                                placeholder="Pilih range penghasilan"
                                :error="form.errors['ayah.penghasilan']"
                            />

                            <FormInput
                                v-model="form.ayah.no_hp"
                                label="No. HP Ayah"
                                type="tel"
                                placeholder="08123456789"
                                :error="form.errors['ayah.no_hp']"
                                required
                            />
                        </div>
                    </div>
                </Motion>
            </div>
        </Motion>

        <!-- Section 5: Data Ibu -->
        <Motion
            :initial="{ opacity: 0, y: 8 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.35, ease: 'easeOut', delay: 0.2 }"
        >
            <div class="bg-white dark:bg-zinc-950 rounded-2xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden shadow-sm">
                <Motion :whileTap="{ scale: 0.995 }">
                    <button
                        type="button"
                        @click="toggleSection('mother')"
                        class="w-full px-5 py-4 flex items-center justify-between hover:bg-slate-50/50 dark:hover:bg-zinc-900/50 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-linear-to-br from-rose-500 to-pink-600 flex items-center justify-center shadow-lg shadow-rose-500/20">
                                <Heart class="w-5 h-5 text-white" />
                            </div>
                            <div class="text-left">
                                <h3 class="font-semibold text-slate-800 dark:text-slate-100 text-[15px]">Data Ibu</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Informasi ibu kandung</p>
                            </div>
                        </div>
                        <Motion :animate="{ rotate: sections.mother ? 180 : 0 }" :transition="{ duration: 0.2 }">
                            <ChevronDown class="w-5 h-5 text-slate-400" />
                        </Motion>
                    </button>
                </Motion>

                <Motion
                    :initial="false"
                    :animate="{ height: sections.mother ? 'auto' : 0, opacity: sections.mother ? 1 : 0 }"
                    :transition="{ duration: 0.3, ease: 'circOut' }"
                    class="overflow-hidden"
                >
                    <div class="px-5 pb-6 pt-2 space-y-5 border-t border-slate-100 dark:border-zinc-800/50">
                        <!-- Primary Contact Checkbox -->
                        <FormCheckbox
                            v-model="form.ibu.is_primary_contact"
                            label="Jadikan Kontak Utama"
                            description="Kontak utama akan menerima notifikasi dan informasi penting"
                        />

                        <!-- Row: Nama & NIK -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormInput
                                v-model="form.ibu.nama_lengkap"
                                label="Nama Lengkap Ibu"
                                placeholder="Nama sesuai KTP"
                                :error="form.errors['ibu.nama_lengkap']"
                                :icon="User"
                                required
                            />

                            <FormInput
                                v-model="form.ibu.nik"
                                label="NIK Ibu"
                                type="text"
                                placeholder="16 digit"
                                :error="form.errors['ibu.nik']"
                                :icon="IdCard"
                                :maxlength="16"
                                input-class="font-mono tracking-wider"
                                required
                            />
                        </div>

                        <!-- Row: Pekerjaan & Pendidikan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormInput
                                v-model="form.ibu.pekerjaan"
                                label="Pekerjaan"
                                placeholder="Contoh: Ibu Rumah Tangga"
                                :error="form.errors['ibu.pekerjaan']"
                            />

                            <FormSelect
                                v-model="form.ibu.pendidikan"
                                label="Pendidikan Terakhir"
                                :options="educationLevels"
                                placeholder="Pilih pendidikan"
                                :error="form.errors['ibu.pendidikan']"
                            />
                        </div>

                        <!-- Row: Penghasilan & No HP -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormSelect
                                v-model="form.ibu.penghasilan"
                                label="Penghasilan Per Bulan"
                                :options="incomeRanges"
                                placeholder="Pilih range penghasilan"
                                :error="form.errors['ibu.penghasilan']"
                            />

                            <FormInput
                                v-model="form.ibu.no_hp"
                                label="No. HP Ibu"
                                type="tel"
                                placeholder="08123456789"
                                :error="form.errors['ibu.no_hp']"
                                required
                            />
                        </div>
                    </div>
                </Motion>
            </div>
        </Motion>

        <!-- Section 6: Data Wali (Optional) -->
        <Motion
            :initial="{ opacity: 0, y: 8 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.35, ease: 'easeOut', delay: 0.25 }"
        >
            <div class="bg-white dark:bg-zinc-950 rounded-2xl border border-dashed border-slate-300 dark:border-zinc-700 overflow-hidden">
                <Motion :whileTap="{ scale: 0.995 }">
                    <button
                        type="button"
                        @click="toggleSection('guardian')"
                        class="w-full px-5 py-4 flex items-center justify-between hover:bg-slate-50/50 dark:hover:bg-zinc-900/50 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 flex items-center justify-center">
                                <Users class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                            </div>
                            <div class="text-left">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-slate-800 dark:text-slate-100 text-[15px]">Data Wali</h3>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-slate-400 uppercase">Opsional</span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Wali atau pengasuh lainnya</p>
                            </div>
                        </div>
                        <Motion :animate="{ rotate: sections.guardian ? 180 : 0 }" :transition="{ duration: 0.2 }">
                            <ChevronDown class="w-5 h-5 text-slate-400" />
                        </Motion>
                    </button>
                </Motion>

                <Motion
                    :initial="false"
                    :animate="{ height: sections.guardian ? 'auto' : 0, opacity: sections.guardian ? 1 : 0 }"
                    :transition="{ duration: 0.3, ease: 'circOut' }"
                    class="overflow-hidden"
                >
                    <div class="px-5 pb-6 pt-2 space-y-5 border-t border-slate-100 dark:border-zinc-800/50">
                        <!-- Info Banner -->
                        <div class="flex items-start gap-3 p-3 bg-slate-50 dark:bg-zinc-900/50 rounded-xl">
                            <Info class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" />
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                Isi data wali jika siswa tinggal dengan wali atau pengasuh lain selain orang tua kandung.
                            </p>
                        </div>

                        <!-- Primary Contact Checkbox -->
                        <FormCheckbox
                            v-model="form.wali.is_primary_contact"
                            label="Jadikan Kontak Utama"
                            description="Kontak utama akan menerima notifikasi dan informasi penting"
                        />

                        <!-- Row: Nama & NIK -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormInput
                                v-model="form.wali.nama_lengkap"
                                label="Nama Lengkap Wali"
                                placeholder="Nama sesuai KTP"
                                :error="form.errors['wali.nama_lengkap']"
                                :icon="User"
                            />

                            <FormInput
                                v-model="form.wali.nik"
                                label="NIK Wali"
                                type="text"
                                placeholder="16 digit"
                                :error="form.errors['wali.nik']"
                                :icon="IdCard"
                                :maxlength="16"
                                input-class="font-mono tracking-wider"
                            />
                        </div>

                        <!-- Row: Pekerjaan & Pendidikan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormInput
                                v-model="form.wali.pekerjaan"
                                label="Pekerjaan"
                                placeholder="Contoh: Wiraswasta"
                                :error="form.errors['wali.pekerjaan']"
                            />

                            <FormSelect
                                v-model="form.wali.pendidikan"
                                label="Pendidikan Terakhir"
                                :options="educationLevels"
                                placeholder="Pilih pendidikan"
                                :error="form.errors['wali.pendidikan']"
                            />
                        </div>

                        <!-- Row: Penghasilan & No HP -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormSelect
                                v-model="form.wali.penghasilan"
                                label="Penghasilan Per Bulan"
                                :options="incomeRanges"
                                placeholder="Pilih range penghasilan"
                                :error="form.errors['wali.penghasilan']"
                            />

                            <FormInput
                                v-model="form.wali.no_hp"
                                label="No. HP Wali"
                                type="tel"
                                placeholder="08123456789"
                                :error="form.errors['wali.no_hp']"
                            />
                        </div>
                    </div>
                </Motion>
            </div>
        </Motion>
    </div>
</template>
