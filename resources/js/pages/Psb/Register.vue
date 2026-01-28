<script setup lang="ts">
/**
 * PSB Register Page - Form pendaftaran multi-step untuk calon siswa baru
 *
 * Page ini bertujuan untuk mengumpulkan data lengkap pendaftaran melalui
 * 4 langkah: Data Siswa, Data Orang Tua, Upload Dokumen, dan Review
 */
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Motion } from 'motion-v';
import {
    ChevronLeft,
    ChevronRight,
    Save,
    User,
    Users,
    FileText,
    CheckCircle,
    Upload,
    AlertCircle,
    X,
    Eye,
} from 'lucide-vue-next';
import { FormInput, FormSelect, FormTextarea } from '@/components/ui/Form';
import PsbStepIndicator from '@/components/features/psb/PsbStepIndicator.vue';
import { landing as psbLanding, store as psbStore } from '@/routes/psb';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';

interface Props {
    settings: {
        registration_fee: number;
        formatted_fee: string;
        academic_year: string | null;
    } | null;
}

const props = defineProps<Props>();
const haptics = useHaptics();
const modal = useModal();

/**
 * Current step dalam form multi-step (1-4)
 */
const currentStep = ref(1);

/**
 * Definisi langkah-langkah pendaftaran
 */
const steps = [
    { id: 1, title: 'Data Siswa', description: 'Biodata calon siswa' },
    { id: 2, title: 'Data Orang Tua', description: 'Informasi ayah & ibu' },
    { id: 3, title: 'Upload Dokumen', description: 'Dokumen persyaratan' },
    { id: 4, title: 'Review', description: 'Periksa & kirim' },
];

/**
 * Form data dengan useForm untuk integrasi Inertia
 */
const form = useForm({
    // Data Siswa
    student_name: '',
    student_nik: '',
    birth_place: '',
    birth_date: '',
    gender: '',
    religion: '',
    address: '',
    child_order: 1,
    origin_school: '',
    // Data Ayah
    father_name: '',
    father_nik: '',
    father_occupation: '',
    father_phone: '',
    father_email: '',
    // Data Ibu
    mother_name: '',
    mother_nik: '',
    mother_occupation: '',
    mother_phone: '',
    mother_email: '',
    // Notes
    notes: '',
    // Documents (files)
    birth_certificate: null as File | null,
    family_card: null as File | null,
    parent_id: null as File | null,
    photo: null as File | null,
});

/**
 * Preview URL untuk dokumen yang diupload
 */
const documentPreviews = ref<Record<string, string>>({});

/**
 * Options untuk select inputs
 */
const genderOptions = [
    { value: 'L', label: 'Laki-laki' },
    { value: 'P', label: 'Perempuan' },
];

const religionOptions = [
    { value: 'Islam', label: 'Islam' },
    { value: 'Kristen', label: 'Kristen' },
    { value: 'Katolik', label: 'Katolik' },
    { value: 'Hindu', label: 'Hindu' },
    { value: 'Buddha', label: 'Buddha' },
    { value: 'Konghucu', label: 'Konghucu' },
    { value: 'Lainnya', label: 'Lainnya' },
];

/**
 * Daftar dokumen yang harus diupload
 */
const requiredDocuments = [
    { key: 'birth_certificate', label: 'Akte Kelahiran', accept: '.pdf,.jpg,.jpeg,.png', maxSize: '5MB' },
    { key: 'family_card', label: 'Kartu Keluarga', accept: '.pdf,.jpg,.jpeg,.png', maxSize: '5MB' },
    { key: 'parent_id', label: 'KTP Orang Tua', accept: '.pdf,.jpg,.jpeg,.png', maxSize: '5MB' },
    { key: 'photo', label: 'Pas Foto 3x4', accept: '.jpg,.jpeg,.png', maxSize: '2MB' },
];

/**
 * Validasi per step sebelum next
 */
const validateStep = (step: number): boolean => {
    const errors: string[] = [];

    if (step === 1) {
        if (!form.student_name) errors.push('Nama lengkap wajib diisi');
        if (!form.student_nik || form.student_nik.length !== 16) errors.push('NIK harus 16 digit');
        if (!form.birth_place) errors.push('Tempat lahir wajib diisi');
        if (!form.birth_date) errors.push('Tanggal lahir wajib diisi');
        if (!form.gender) errors.push('Jenis kelamin wajib dipilih');
        if (!form.religion) errors.push('Agama wajib dipilih');
        if (!form.address) errors.push('Alamat wajib diisi');
    } else if (step === 2) {
        if (!form.father_name) errors.push('Nama ayah wajib diisi');
        if (!form.father_nik || form.father_nik.length !== 16) errors.push('NIK ayah harus 16 digit');
        if (!form.father_occupation) errors.push('Pekerjaan ayah wajib diisi');
        if (!form.father_phone) errors.push('No HP ayah wajib diisi');
        if (!form.mother_name) errors.push('Nama ibu wajib diisi');
        if (!form.mother_nik || form.mother_nik.length !== 16) errors.push('NIK ibu harus 16 digit');
        if (!form.mother_occupation) errors.push('Pekerjaan ibu wajib diisi');
    } else if (step === 3) {
        if (!form.birth_certificate) errors.push('Akte kelahiran wajib diupload');
        if (!form.family_card) errors.push('Kartu keluarga wajib diupload');
        if (!form.parent_id) errors.push('KTP orang tua wajib diupload');
        if (!form.photo) errors.push('Pas foto wajib diupload');
    }

    if (errors.length > 0) {
        modal.alert({
            title: 'Data Belum Lengkap',
            message: errors.join('\n'),
            type: 'error',
        });
        return false;
    }

    return true;
};

/**
 * Handle next step dengan validasi
 */
const nextStep = () => {
    if (validateStep(currentStep.value)) {
        haptics.light();
        currentStep.value++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        haptics.heavy();
    }
};

/**
 * Handle previous step
 */
const prevStep = () => {
    haptics.light();
    currentStep.value--;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

/**
 * Handle file upload dengan preview
 */
const handleFileUpload = (event: Event, key: string) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        (form as any)[key] = file;

        // Generate preview URL untuk gambar
        if (file.type.startsWith('image/')) {
            documentPreviews.value[key] = URL.createObjectURL(file);
        } else {
            documentPreviews.value[key] = '';
        }

        haptics.light();
    }
};

/**
 * Remove uploaded file
 */
const removeFile = (key: string) => {
    (form as any)[key] = null;
    if (documentPreviews.value[key]) {
        URL.revokeObjectURL(documentPreviews.value[key]);
        delete documentPreviews.value[key];
    }
    haptics.light();
};

/**
 * Submit form pendaftaran
 */
const submit = async () => {
    const confirmed = await modal.confirm({
        title: 'Konfirmasi Pendaftaran',
        message: 'Pastikan semua data sudah benar. Data yang sudah dikirim tidak dapat diubah.',
        confirmText: 'Ya, Kirim',
        cancelText: 'Periksa Lagi',
    });

    if (!confirmed) return;

    haptics.medium();

    form.post(psbStore().url, {
        forceFormData: true,
        onSuccess: () => {
            haptics.success();
        },
        onError: () => {
            haptics.heavy();
            modal.alert({
                title: 'Gagal Mengirim',
                message: 'Terjadi kesalahan saat mengirim pendaftaran. Silakan coba lagi.',
                type: 'error',
            });
        },
    });
};

/**
 * Computed untuk format tanggal lahir display
 */
const formattedBirthDate = computed(() => {
    if (!form.birth_date) return '-';
    return new Date(form.birth_date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
});

/**
 * Get gender label
 */
const genderLabel = computed(() => {
    return form.gender === 'L' ? 'Laki-laki' : form.gender === 'P' ? 'Perempuan' : '-';
});
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <Head title="Form Pendaftaran PSB" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-slate-200 bg-white shadow-sm">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-4">
                <Link
                    :href="psbLanding()"
                    class="flex items-center gap-2 text-slate-600 transition-colors hover:text-slate-900"
                >
                    <ChevronLeft class="h-5 w-5" />
                    <span class="hidden sm:inline">Kembali</span>
                </Link>
                <h1 class="text-lg font-semibold text-slate-900">Form Pendaftaran</h1>
                <div class="w-20"></div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-4xl px-4 py-6">
            <!-- Step Indicator -->
            <div class="mb-8 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <PsbStepIndicator :steps="steps" :current-step="currentStep" />
            </div>

            <!-- Form Steps -->
            <Motion
                :key="currentStep"
                :initial="{ opacity: 0, x: 20 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ duration: 0.3 }"
            >
                <!-- Step 1: Data Siswa -->
                <div v-if="currentStep === 1" class="space-y-6">
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50">
                                <User class="h-5 w-5 text-emerald-600" />
                            </div>
                            <div>
                                <h2 class="font-semibold text-slate-900">Data Calon Siswa</h2>
                                <p class="text-sm text-slate-500">Isi biodata lengkap calon siswa</p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <FormInput
                                    v-model="form.student_name"
                                    label="Nama Lengkap"
                                    placeholder="Masukkan nama lengkap"
                                    :error="form.errors.student_name"
                                    required
                                />
                            </div>

                            <FormInput
                                v-model="form.student_nik"
                                label="NIK"
                                placeholder="16 digit NIK"
                                maxlength="16"
                                :error="form.errors.student_nik"
                                required
                            />

                            <FormInput
                                v-model="form.birth_place"
                                label="Tempat Lahir"
                                placeholder="Kota/Kabupaten"
                                :error="form.errors.birth_place"
                                required
                            />

                            <FormInput
                                v-model="form.birth_date"
                                type="date"
                                label="Tanggal Lahir"
                                :error="form.errors.birth_date"
                                required
                            />

                            <FormSelect
                                v-model="form.gender"
                                label="Jenis Kelamin"
                                :options="genderOptions"
                                :error="form.errors.gender"
                                required
                            />

                            <FormSelect
                                v-model="form.religion"
                                label="Agama"
                                :options="religionOptions"
                                :error="form.errors.religion"
                                required
                            />

                            <FormInput
                                v-model.number="form.child_order"
                                type="number"
                                label="Anak ke-"
                                min="1"
                                max="20"
                                :error="form.errors.child_order"
                                required
                            />

                            <div class="sm:col-span-2">
                                <FormTextarea
                                    v-model="form.address"
                                    label="Alamat Lengkap"
                                    placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota"
                                    rows="3"
                                    :error="form.errors.address"
                                    required
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <FormInput
                                    v-model="form.origin_school"
                                    label="Asal Sekolah (TK/PAUD)"
                                    placeholder="Nama TK/PAUD sebelumnya (opsional)"
                                    :error="form.errors.origin_school"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Data Orang Tua -->
                <div v-if="currentStep === 2" class="space-y-6">
                    <!-- Data Ayah -->
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-50">
                                <Users class="h-5 w-5 text-sky-600" />
                            </div>
                            <div>
                                <h2 class="font-semibold text-slate-900">Data Ayah</h2>
                                <p class="text-sm text-slate-500">Informasi lengkap ayah kandung</p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <FormInput
                                v-model="form.father_name"
                                label="Nama Lengkap Ayah"
                                placeholder="Masukkan nama lengkap"
                                :error="form.errors.father_name"
                                required
                            />

                            <FormInput
                                v-model="form.father_nik"
                                label="NIK Ayah"
                                placeholder="16 digit NIK"
                                maxlength="16"
                                :error="form.errors.father_nik"
                                required
                            />

                            <FormInput
                                v-model="form.father_occupation"
                                label="Pekerjaan"
                                placeholder="Contoh: Wiraswasta"
                                :error="form.errors.father_occupation"
                                required
                            />

                            <FormInput
                                v-model="form.father_phone"
                                label="No. HP"
                                placeholder="08xxxxxxxxxx"
                                :error="form.errors.father_phone"
                                required
                            />

                            <div class="sm:col-span-2">
                                <FormInput
                                    v-model="form.father_email"
                                    type="email"
                                    label="Email (Opsional)"
                                    placeholder="email@example.com"
                                    :error="form.errors.father_email"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Data Ibu -->
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-pink-50">
                                <Users class="h-5 w-5 text-pink-600" />
                            </div>
                            <div>
                                <h2 class="font-semibold text-slate-900">Data Ibu</h2>
                                <p class="text-sm text-slate-500">Informasi lengkap ibu kandung</p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <FormInput
                                v-model="form.mother_name"
                                label="Nama Lengkap Ibu"
                                placeholder="Masukkan nama lengkap"
                                :error="form.errors.mother_name"
                                required
                            />

                            <FormInput
                                v-model="form.mother_nik"
                                label="NIK Ibu"
                                placeholder="16 digit NIK"
                                maxlength="16"
                                :error="form.errors.mother_nik"
                                required
                            />

                            <FormInput
                                v-model="form.mother_occupation"
                                label="Pekerjaan"
                                placeholder="Contoh: Ibu Rumah Tangga"
                                :error="form.errors.mother_occupation"
                                required
                            />

                            <FormInput
                                v-model="form.mother_phone"
                                label="No. HP (Opsional)"
                                placeholder="08xxxxxxxxxx"
                                :error="form.errors.mother_phone"
                            />

                            <div class="sm:col-span-2">
                                <FormInput
                                    v-model="form.mother_email"
                                    type="email"
                                    label="Email (Opsional)"
                                    placeholder="email@example.com"
                                    :error="form.errors.mother_email"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Upload Dokumen -->
                <div v-if="currentStep === 3" class="space-y-6">
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-50">
                                <FileText class="h-5 w-5 text-amber-600" />
                            </div>
                            <div>
                                <h2 class="font-semibold text-slate-900">Upload Dokumen</h2>
                                <p class="text-sm text-slate-500">
                                    Upload dokumen persyaratan (Format: PDF, JPG, PNG)
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="doc in requiredDocuments"
                                :key="doc.key"
                                class="rounded-xl border border-slate-200 p-4"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-slate-900">
                                            {{ doc.label }}
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <p class="text-xs text-slate-500">
                                            Maks {{ doc.maxSize }} • {{ doc.accept }}
                                        </p>

                                        <!-- File preview -->
                                        <div
                                            v-if="(form as any)[doc.key]"
                                            class="mt-3 flex items-center gap-3"
                                        >
                                            <div
                                                v-if="documentPreviews[doc.key]"
                                                class="h-16 w-16 overflow-hidden rounded-lg border border-slate-200"
                                            >
                                                <img
                                                    :src="documentPreviews[doc.key]"
                                                    :alt="doc.label"
                                                    class="h-full w-full object-cover"
                                                />
                                            </div>
                                            <div
                                                v-else
                                                class="flex h-16 w-16 items-center justify-center rounded-lg bg-slate-100"
                                            >
                                                <FileText class="h-8 w-8 text-slate-400" />
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-slate-900">
                                                    {{ ((form as any)[doc.key] as File)?.name }}
                                                </p>
                                                <p class="text-xs text-slate-500">
                                                    {{ (((form as any)[doc.key] as File)?.size / 1024).toFixed(1) }} KB
                                                </p>
                                            </div>
                                            <button
                                                type="button"
                                                class="rounded-full p-2 text-red-500 transition-colors hover:bg-red-50"
                                                @click="removeFile(doc.key)"
                                            >
                                                <X class="h-5 w-5" />
                                            </button>
                                        </div>

                                        <!-- Error message -->
                                        <p
                                            v-if="form.errors[doc.key as keyof typeof form.errors]"
                                            class="mt-2 text-sm text-red-500"
                                        >
                                            {{ form.errors[doc.key as keyof typeof form.errors] }}
                                        </p>
                                    </div>

                                    <div v-if="!(form as any)[doc.key]">
                                        <label
                                            :for="doc.key"
                                            class="flex cursor-pointer items-center gap-2 rounded-xl bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-600 transition-all hover:bg-emerald-100 active:scale-97"
                                        >
                                            <Upload class="h-4 w-4" />
                                            Upload
                                        </label>
                                        <input
                                            :id="doc.key"
                                            type="file"
                                            :accept="doc.accept"
                                            class="hidden"
                                            @change="handleFileUpload($event, doc.key)"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <FormTextarea
                            v-model="form.notes"
                            label="Catatan Tambahan (Opsional)"
                            placeholder="Informasi tambahan yang perlu disampaikan"
                            rows="3"
                            :error="form.errors.notes"
                        />
                    </div>
                </div>

                <!-- Step 4: Review -->
                <div v-if="currentStep === 4" class="space-y-6">
                    <!-- Data Siswa Review -->
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50">
                                <User class="h-5 w-5 text-emerald-600" />
                            </div>
                            <h2 class="font-semibold text-slate-900">Data Calon Siswa</h2>
                        </div>

                        <dl class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-medium uppercase text-slate-500">Nama Lengkap</dt>
                                <dd class="mt-1 font-medium text-slate-900">{{ form.student_name || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase text-slate-500">NIK</dt>
                                <dd class="mt-1 font-medium text-slate-900">{{ form.student_nik || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase text-slate-500">Tempat, Tanggal Lahir</dt>
                                <dd class="mt-1 font-medium text-slate-900">
                                    {{ form.birth_place }}, {{ formattedBirthDate }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase text-slate-500">Jenis Kelamin</dt>
                                <dd class="mt-1 font-medium text-slate-900">{{ genderLabel }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase text-slate-500">Agama</dt>
                                <dd class="mt-1 font-medium text-slate-900">{{ form.religion || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase text-slate-500">Anak ke-</dt>
                                <dd class="mt-1 font-medium text-slate-900">{{ form.child_order }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium uppercase text-slate-500">Alamat</dt>
                                <dd class="mt-1 font-medium text-slate-900">{{ form.address || '-' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium uppercase text-slate-500">Asal Sekolah</dt>
                                <dd class="mt-1 font-medium text-slate-900">{{ form.origin_school || '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Data Orang Tua Review -->
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-50">
                                <Users class="h-5 w-5 text-sky-600" />
                            </div>
                            <h2 class="font-semibold text-slate-900">Data Orang Tua</h2>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <!-- Ayah -->
                            <div>
                                <h3 class="mb-3 text-sm font-semibold text-slate-700">Data Ayah</h3>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-xs text-slate-500">Nama</dt>
                                        <dd class="font-medium text-slate-900">{{ form.father_name || '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-500">NIK</dt>
                                        <dd class="font-medium text-slate-900">{{ form.father_nik || '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-500">Pekerjaan</dt>
                                        <dd class="font-medium text-slate-900">{{ form.father_occupation || '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-500">No. HP</dt>
                                        <dd class="font-medium text-slate-900">{{ form.father_phone || '-' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Ibu -->
                            <div>
                                <h3 class="mb-3 text-sm font-semibold text-slate-700">Data Ibu</h3>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-xs text-slate-500">Nama</dt>
                                        <dd class="font-medium text-slate-900">{{ form.mother_name || '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-500">NIK</dt>
                                        <dd class="font-medium text-slate-900">{{ form.mother_nik || '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-500">Pekerjaan</dt>
                                        <dd class="font-medium text-slate-900">{{ form.mother_occupation || '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-500">No. HP</dt>
                                        <dd class="font-medium text-slate-900">{{ form.mother_phone || '-' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Dokumen Review -->
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-50">
                                <FileText class="h-5 w-5 text-amber-600" />
                            </div>
                            <h2 class="font-semibold text-slate-900">Dokumen</h2>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="doc in requiredDocuments"
                                :key="doc.key"
                                class="flex items-center gap-3 rounded-lg bg-slate-50 p-3"
                            >
                                <CheckCircle
                                    v-if="(form as any)[doc.key]"
                                    class="h-5 w-5 shrink-0 text-emerald-500"
                                />
                                <AlertCircle
                                    v-else
                                    class="h-5 w-5 shrink-0 text-red-500"
                                />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-900">{{ doc.label }}</p>
                                    <p class="truncate text-xs text-slate-500">
                                        {{ ((form as any)[doc.key] as File)?.name || 'Belum diupload' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Agreement -->
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <div class="flex gap-3">
                            <AlertCircle class="h-5 w-5 shrink-0 text-amber-600" />
                            <div class="text-sm text-amber-800">
                                <p class="font-medium">Perhatian</p>
                                <p class="mt-1">
                                    Dengan mengirimkan formulir ini, Anda menyatakan bahwa semua data yang
                                    diisi adalah benar dan dapat dipertanggungjawabkan. Data yang sudah
                                    dikirim tidak dapat diubah.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Navigation Buttons -->
            <div class="mt-8 flex items-center justify-between gap-4">
                <button
                    v-if="currentStep > 1"
                    type="button"
                    class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3 font-medium text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-97"
                    @click="prevStep"
                >
                    <ChevronLeft class="h-5 w-5" />
                    Sebelumnya
                </button>
                <div v-else></div>

                <button
                    v-if="currentStep < 4"
                    type="button"
                    class="flex items-center gap-2 rounded-xl bg-emerald-500 px-6 py-3 font-medium text-white shadow-sm transition-all hover:bg-emerald-600 active:scale-97"
                    @click="nextStep"
                >
                    Selanjutnya
                    <ChevronRight class="h-5 w-5" />
                </button>

                <button
                    v-else
                    type="button"
                    :disabled="form.processing"
                    class="flex items-center gap-2 rounded-xl bg-emerald-500 px-8 py-3 font-medium text-white shadow-sm transition-all hover:bg-emerald-600 active:scale-97 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="submit"
                >
                    <Save v-if="!form.processing" class="h-5 w-5" />
                    <span
                        v-else
                        class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"
                    ></span>
                    {{ form.processing ? 'Mengirim...' : 'Kirim Pendaftaran' }}
                </button>
            </div>
        </main>
    </div>
</template>
