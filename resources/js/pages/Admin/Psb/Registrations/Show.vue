<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { Motion, AnimatePresence } from 'motion-v';
import {
    ArrowLeft,
    User,
    Users,
    FileText,
    Clock,
    CheckCircle,
    XCircle,
    AlertTriangle,
    FileCheck,
    RefreshCw,
    Trophy,
    Calendar,
    MapPin,
    Phone,
    Mail,
    Briefcase,
    X,
    Image,
    FileWarning,
    Eye,
    ZoomIn
} from 'lucide-vue-next';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import Badge from '@/components/ui/Badge.vue';
import PsbTimeline from '@/components/features/psb/PsbTimeline.vue';
import { index as registrationsIndex, approve, reject, revision } from '@/routes/admin/psb/registrations';

/**
 * Interface definitions
 */
interface Document {
    id: number;
    document_type: string;
    type_label: string;
    file_path: string;
    file_url: string;
    original_name: string;
    status: string;
    status_label: string;
    revision_note: string | null;
}

interface TimelineItem {
    step: string;
    label: string;
    completed: boolean;
    current: boolean;
    date: string | null;
}

interface Registration {
    id: number;
    registration_number: string;
    status: string;
    status_label: string;
    rejection_reason: string | null;
    notes: string | null;
    // Data Siswa
    student_name: string;
    student_nik: string;
    birth_place: string;
    birth_date: string;
    birth_date_formatted: string;
    gender: string;
    gender_label: string;
    religion: string;
    address: string;
    child_order: number;
    origin_school: string | null;
    // Data Ayah
    father_name: string;
    father_nik: string;
    father_occupation: string;
    father_phone: string;
    father_email: string | null;
    // Data Ibu
    mother_name: string;
    mother_nik: string;
    mother_occupation: string;
    mother_phone: string | null;
    mother_email: string | null;
    // Metadata
    academic_year: string | null;
    verifier_name: string | null;
    created_at: string;
    verified_at: string | null;
    announced_at: string | null;
}

interface Props {
    title: string;
    registration: Registration;
    documents: Document[];
    timeline: TimelineItem[];
}

const props = defineProps<Props>();
const haptics = useHaptics();
const modal = useModal();

// Modal states
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const showRevisionModal = ref(false);
const showLightbox = ref(false);
const lightboxUrl = ref('');
const approvalNotes = ref('');
const rejectionReason = ref('');
const selectedDocuments = ref<{ id: number; revision_note: string }[]>([]);
const isSubmitting = ref(false);

/**
 * Status config untuk badge dan styling
 */
const statusConfig: Record<string, { label: string; variant: string; icon: any }> = {
    pending: { label: 'Menunggu', variant: 'warning', icon: Clock },
    document_review: { label: 'Review Dokumen', variant: 'info', icon: FileCheck },
    approved: { label: 'Diterima', variant: 'success', icon: CheckCircle },
    rejected: { label: 'Ditolak', variant: 'error', icon: XCircle },
    waiting_list: { label: 'Waiting List', variant: 'warning', icon: AlertTriangle },
    re_registration: { label: 'Daftar Ulang', variant: 'info', icon: RefreshCw },
    completed: { label: 'Selesai', variant: 'success', icon: Trophy },
};

/**
 * Get status config
 */
const getStatusConfig = (status: string) => {
    return statusConfig[status] || { label: status, variant: 'default', icon: Clock };
};

/**
 * Check if actions are allowed
 */
const canTakeAction = computed(() => {
    return ['pending', 'document_review'].includes(props.registration.status);
});

/**
 * Check if document is an image
 */
const isImage = (path: string) => {
    const ext = path.split('.').pop()?.toLowerCase();
    return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext || '');
};

/**
 * Open lightbox untuk preview dokumen
 */
const openLightbox = (url: string) => {
    lightboxUrl.value = url;
    showLightbox.value = true;
};

/**
 * Toggle document selection untuk revision
 */
const toggleDocumentSelection = (doc: Document) => {
    const index = selectedDocuments.value.findIndex(d => d.id === doc.id);
    if (index > -1) {
        selectedDocuments.value.splice(index, 1);
    } else {
        selectedDocuments.value.push({ id: doc.id, revision_note: '' });
    }
};

/**
 * Check if document is selected
 */
const isDocumentSelected = (docId: number) => {
    return selectedDocuments.value.some(d => d.id === docId);
};

/**
 * Get revision note for document
 */
const getRevisionNote = (docId: number) => {
    const doc = selectedDocuments.value.find(d => d.id === docId);
    return doc?.revision_note || '';
};

/**
 * Update revision note for document
 */
const updateRevisionNote = (docId: number, note: string) => {
    const doc = selectedDocuments.value.find(d => d.id === docId);
    if (doc) {
        doc.revision_note = note;
    }
};

/**
 * Approve registration
 */
const submitApprove = async () => {
    const confirmed = await modal.confirm(
        'Setujui Pendaftaran',
        `Apakah Anda yakin ingin menyetujui pendaftaran ${props.registration.student_name}?`,
        'Ya, Setujui',
        'Batal'
    );

    if (!confirmed) return;

    haptics.medium();
    isSubmitting.value = true;

    router.post(approve(props.registration.id).url, {
        notes: approvalNotes.value || null,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            showApproveModal.value = false;
        },
        onError: () => {
            haptics.error();
            modal.error('Gagal menyetujui pendaftaran');
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

/**
 * Reject registration
 */
const submitReject = () => {
    if (!rejectionReason.value.trim()) {
        modal.error('Alasan penolakan wajib diisi');
        return;
    }

    if (rejectionReason.value.length < 10) {
        modal.error('Alasan penolakan minimal 10 karakter');
        return;
    }

    haptics.medium();
    isSubmitting.value = true;

    router.post(reject(props.registration.id).url, {
        rejection_reason: rejectionReason.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            showRejectModal.value = false;
        },
        onError: () => {
            haptics.error();
            modal.error('Gagal menolak pendaftaran');
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

/**
 * Request document revision
 */
const submitRevision = () => {
    if (selectedDocuments.value.length === 0) {
        modal.error('Pilih minimal satu dokumen untuk revisi');
        return;
    }

    const hasEmptyNotes = selectedDocuments.value.some(d => !d.revision_note.trim());
    if (hasEmptyNotes) {
        modal.error('Catatan revisi wajib diisi untuk setiap dokumen');
        return;
    }

    haptics.medium();
    isSubmitting.value = true;

    router.post(revision(props.registration.id).url, {
        documents: selectedDocuments.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            showRevisionModal.value = false;
            selectedDocuments.value = [];
        },
        onError: () => {
            haptics.error();
            modal.error('Gagal mengirim permintaan revisi');
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

/**
 * Open revision modal
 */
const openRevisionModal = () => {
    selectedDocuments.value = [];
    showRevisionModal.value = true;
};
</script>

<template>
    <AppLayout>
        <Head :title="`${registration.registration_number} - ${title}`" />

        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <Link
                            :href="registrationsIndex().url"
                            class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors"
                        >
                            <ArrowLeft :size="20" />
                            <span class="text-sm font-medium">Kembali</span>
                        </Link>
                        <div class="flex-1">
                            <div class="flex items-center gap-3 flex-wrap">
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ registration.registration_number }}
                                </h1>
                                <Badge
                                    :variant="getStatusConfig(registration.status).variant as any"
                                    size="md"
                                    rounded="square"
                                >
                                    <component :is="getStatusConfig(registration.status).icon" :size="14" class="mr-1" />
                                    {{ getStatusConfig(registration.status).label }}
                                </Badge>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                {{ registration.student_name }} • Terdaftar {{ registration.created_at }}
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Data Siswa -->
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm">
                            <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-zinc-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                        <User :size="20" class="text-emerald-600 dark:text-emerald-400" />
                                    </div>
                                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Data Calon Siswa</h2>
                                </div>
                            </div>
                            <div class="p-4 sm:p-6">
                                <dl class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Nama Lengkap</dt>
                                        <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-white">{{ registration.student_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">NIK</dt>
                                        <dd class="mt-1 text-sm font-mono text-slate-900 dark:text-white">{{ registration.student_nik }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tempat, Tanggal Lahir</dt>
                                        <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ registration.birth_place }}, {{ registration.birth_date_formatted }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Jenis Kelamin</dt>
                                        <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ registration.gender_label }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Agama</dt>
                                        <dd class="mt-1 text-sm text-slate-900 dark:text-white capitalize">{{ registration.religion }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Anak Ke</dt>
                                        <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ registration.child_order }}</dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Alamat</dt>
                                        <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ registration.address }}</dd>
                                    </div>
                                    <div v-if="registration.origin_school" class="sm:col-span-2">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Asal Sekolah</dt>
                                        <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ registration.origin_school }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </Motion>

                    <!-- Data Orang Tua -->
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.15 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm">
                            <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-zinc-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                        <Users :size="20" class="text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Data Orang Tua</h2>
                                </div>
                            </div>
                            <div class="p-4 sm:p-6">
                                <div class="grid gap-6 lg:grid-cols-2">
                                    <!-- Ayah -->
                                    <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-4">
                                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Data Ayah</h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-xs text-slate-500 dark:text-slate-400">Nama</dt>
                                                <dd class="text-sm font-medium text-slate-900 dark:text-white">{{ registration.father_name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs text-slate-500 dark:text-slate-400">NIK</dt>
                                                <dd class="text-sm font-mono text-slate-900 dark:text-white">{{ registration.father_nik }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs text-slate-500 dark:text-slate-400">Pekerjaan</dt>
                                                <dd class="text-sm text-slate-900 dark:text-white">{{ registration.father_occupation }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs text-slate-500 dark:text-slate-400">No. HP</dt>
                                                <dd class="text-sm text-slate-900 dark:text-white">{{ registration.father_phone }}</dd>
                                            </div>
                                            <div v-if="registration.father_email">
                                                <dt class="text-xs text-slate-500 dark:text-slate-400">Email</dt>
                                                <dd class="text-sm text-slate-900 dark:text-white">{{ registration.father_email }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Ibu -->
                                    <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-4">
                                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Data Ibu</h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-xs text-slate-500 dark:text-slate-400">Nama</dt>
                                                <dd class="text-sm font-medium text-slate-900 dark:text-white">{{ registration.mother_name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs text-slate-500 dark:text-slate-400">NIK</dt>
                                                <dd class="text-sm font-mono text-slate-900 dark:text-white">{{ registration.mother_nik }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs text-slate-500 dark:text-slate-400">Pekerjaan</dt>
                                                <dd class="text-sm text-slate-900 dark:text-white">{{ registration.mother_occupation }}</dd>
                                            </div>
                                            <div v-if="registration.mother_phone">
                                                <dt class="text-xs text-slate-500 dark:text-slate-400">No. HP</dt>
                                                <dd class="text-sm text-slate-900 dark:text-white">{{ registration.mother_phone }}</dd>
                                            </div>
                                            <div v-if="registration.mother_email">
                                                <dt class="text-xs text-slate-500 dark:text-slate-400">Email</dt>
                                                <dd class="text-sm text-slate-900 dark:text-white">{{ registration.mother_email }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Motion>

                    <!-- Dokumen -->
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.2 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm">
                            <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-zinc-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                        <FileText :size="20" class="text-purple-600 dark:text-purple-400" />
                                    </div>
                                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Dokumen Persyaratan</h2>
                                </div>
                            </div>
                            <div class="p-4 sm:p-6">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div
                                        v-for="doc in documents"
                                        :key="doc.id"
                                        class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-4"
                                    >
                                        <div class="flex items-start justify-between gap-3 mb-3">
                                            <div>
                                                <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ doc.type_label }}</h4>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ doc.original_name }}</p>
                                            </div>
                                            <Badge
                                                :variant="doc.status === 'approved' ? 'success' : doc.status === 'rejected' ? 'error' : 'warning'"
                                                size="sm"
                                            >
                                                {{ doc.status_label }}
                                            </Badge>
                                        </div>

                                        <!-- Revision Note -->
                                        <div
                                            v-if="doc.revision_note"
                                            class="mb-3 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"
                                        >
                                            <div class="flex items-start gap-2">
                                                <FileWarning :size="14" class="text-red-500 shrink-0 mt-0.5" />
                                                <p class="text-xs text-red-700 dark:text-red-400">{{ doc.revision_note }}</p>
                                            </div>
                                        </div>

                                        <!-- Preview -->
                                        <div class="relative aspect-video rounded-lg overflow-hidden bg-slate-200 dark:bg-zinc-700">
                                            <img
                                                v-if="isImage(doc.file_path)"
                                                :src="doc.file_url"
                                                :alt="doc.type_label"
                                                class="w-full h-full object-cover"
                                                loading="lazy"
                                            />
                                            <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-400">
                                                <FileText :size="32" />
                                                <span class="text-xs mt-1">PDF</span>
                                            </div>

                                            <!-- Preview Button -->
                                            <button
                                                @click="openLightbox(doc.file_url)"
                                                class="absolute inset-0 bg-black/0 hover:bg-black/30 flex items-center justify-center opacity-0 hover:opacity-100 transition-all"
                                            >
                                                <div class="w-10 h-10 bg-white/90 rounded-full flex items-center justify-center">
                                                    <ZoomIn :size="20" class="text-slate-700" />
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Motion>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Actions -->
                    <Motion
                        v-if="canTakeAction"
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.25 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-4 sm:p-6">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Aksi Verifikasi</h2>
                            <div class="space-y-3">
                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        @click="showApproveModal = true"
                                        class="w-full px-4 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold
                                               flex items-center justify-center gap-2 transition-colors shadow-sm shadow-emerald-500/25"
                                    >
                                        <CheckCircle :size="20" />
                                        <span>Setujui Pendaftaran</span>
                                    </button>
                                </Motion>

                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        @click="showRejectModal = true"
                                        class="w-full px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold
                                               flex items-center justify-center gap-2 transition-colors shadow-sm shadow-red-500/25"
                                    >
                                        <XCircle :size="20" />
                                        <span>Tolak Pendaftaran</span>
                                    </button>
                                </Motion>

                                <Motion :whileTap="{ scale: 0.97 }">
                                    <button
                                        @click="openRevisionModal"
                                        class="w-full px-4 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-semibold
                                               flex items-center justify-center gap-2 transition-colors shadow-sm shadow-amber-500/25"
                                    >
                                        <FileWarning :size="20" />
                                        <span>Minta Revisi Dokumen</span>
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </Motion>

                    <!-- Timeline -->
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.3 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-4 sm:p-6">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Timeline Status</h2>
                            <PsbTimeline
                                :timeline="timeline"
                                :status="registration.status"
                                :rejection-reason="registration.rejection_reason"
                            />
                        </div>
                    </Motion>

                    <!-- Metadata -->
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.35 }"
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-4 sm:p-6">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Informasi</h2>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-xs text-slate-500 dark:text-slate-400">Tahun Ajaran</dt>
                                    <dd class="text-sm font-medium text-slate-900 dark:text-white">{{ registration.academic_year || '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-500 dark:text-slate-400">Tanggal Daftar</dt>
                                    <dd class="text-sm font-medium text-slate-900 dark:text-white">{{ registration.created_at }}</dd>
                                </div>
                                <div v-if="registration.verified_at">
                                    <dt class="text-xs text-slate-500 dark:text-slate-400">Diverifikasi</dt>
                                    <dd class="text-sm font-medium text-slate-900 dark:text-white">{{ registration.verified_at }}</dd>
                                </div>
                                <div v-if="registration.verifier_name">
                                    <dt class="text-xs text-slate-500 dark:text-slate-400">Verifikator</dt>
                                    <dd class="text-sm font-medium text-slate-900 dark:text-white">{{ registration.verifier_name }}</dd>
                                </div>
                                <div v-if="registration.notes">
                                    <dt class="text-xs text-slate-500 dark:text-slate-400">Catatan</dt>
                                    <dd class="text-sm text-slate-900 dark:text-white">{{ registration.notes }}</dd>
                                </div>
                            </dl>
                        </div>
                    </Motion>
                </div>
            </div>
        </div>

        <!-- Lightbox Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showLightbox"
                    class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4"
                    @click="showLightbox = false"
                >
                    <button
                        @click="showLightbox = false"
                        class="absolute top-4 right-4 p-2 text-white/70 hover:text-white transition-colors"
                    >
                        <X :size="24" />
                    </button>
                    <img
                        v-if="isImage(lightboxUrl)"
                        :src="lightboxUrl"
                        class="max-w-full max-h-full object-contain"
                        @click.stop
                    />
                    <iframe
                        v-else
                        :src="lightboxUrl"
                        class="w-full h-full max-w-4xl"
                        @click.stop
                    />
                </div>
            </Transition>
        </Teleport>

        <!-- Approve Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showApproveModal"
                    class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
                    @click="showApproveModal = false"
                >
                    <Motion
                        :initial="{ opacity: 0, scale: 0.95, y: 20 }"
                        :animate="{ opacity: 1, scale: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                        @click.stop
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Setujui Pendaftaran</h3>
                                    <p class="text-sm text-slate-600 dark:text-zinc-400 mt-1">{{ registration.student_name }}</p>
                                </div>
                                <button
                                    @click="showApproveModal = false"
                                    class="p-1.5 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                >
                                    <X :size="20" class="text-slate-500" />
                                </button>
                            </div>

                            <p class="text-sm text-slate-600 dark:text-zinc-400">
                                Catatan opsional (akan dikirim ke orang tua):
                            </p>

                            <textarea
                                v-model="approvalNotes"
                                rows="3"
                                placeholder="Contoh: Selamat! Silakan melakukan daftar ulang pada tanggal..."
                                class="w-full px-4 py-3 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                       rounded-xl text-slate-900 dark:text-white placeholder-slate-400
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                       transition-all duration-150 resize-none"
                            ></textarea>

                            <div class="flex gap-3 pt-2">
                                <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                                    <button
                                        @click="showApproveModal = false"
                                        :disabled="isSubmitting"
                                        class="w-full px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700
                                               text-slate-700 dark:text-zinc-300 rounded-xl font-semibold transition-colors
                                               disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Batal
                                    </button>
                                </Motion>

                                <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                                    <button
                                        @click="submitApprove"
                                        :disabled="isSubmitting"
                                        class="w-full px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold
                                               flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    >
                                        <CheckCircle :size="18" />
                                        <span>Setujui</span>
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </Motion>
                </div>
            </Transition>
        </Teleport>

        <!-- Reject Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showRejectModal"
                    class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
                    @click="showRejectModal = false"
                >
                    <Motion
                        :initial="{ opacity: 0, scale: 0.95, y: 20 }"
                        :animate="{ opacity: 1, scale: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                        @click.stop
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tolak Pendaftaran</h3>
                                    <p class="text-sm text-slate-600 dark:text-zinc-400 mt-1">{{ registration.student_name }}</p>
                                </div>
                                <button
                                    @click="showRejectModal = false"
                                    class="p-1.5 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                >
                                    <X :size="20" class="text-slate-500" />
                                </button>
                            </div>

                            <p class="text-sm text-slate-600 dark:text-zinc-400">
                                Alasan penolakan (wajib, minimal 10 karakter):
                            </p>

                            <textarea
                                v-model="rejectionReason"
                                rows="4"
                                placeholder="Contoh: Dokumen tidak lengkap, kuota sudah terpenuhi..."
                                class="w-full px-4 py-3 bg-slate-50/80 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                       rounded-xl text-slate-900 dark:text-white placeholder-slate-400
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500/50
                                       transition-all duration-150 resize-none"
                            ></textarea>

                            <div class="text-xs text-slate-500 text-right">{{ rejectionReason.length }} / 1000 karakter</div>

                            <div class="flex gap-3 pt-2">
                                <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                                    <button
                                        @click="showRejectModal = false"
                                        :disabled="isSubmitting"
                                        class="w-full px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700
                                               text-slate-700 dark:text-zinc-300 rounded-xl font-semibold transition-colors
                                               disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Batal
                                    </button>
                                </Motion>

                                <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                                    <button
                                        @click="submitReject"
                                        :disabled="isSubmitting || !rejectionReason.trim() || rejectionReason.length < 10"
                                        class="w-full px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold
                                               flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    >
                                        <XCircle :size="18" />
                                        <span>Tolak</span>
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </Motion>
                </div>
            </Transition>
        </Teleport>

        <!-- Revision Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showRevisionModal"
                    class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
                    @click="showRevisionModal = false"
                >
                    <Motion
                        :initial="{ opacity: 0, scale: 0.95, y: 20 }"
                        :animate="{ opacity: 1, scale: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                        @click.stop
                    >
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Minta Revisi Dokumen</h3>
                                    <p class="text-sm text-slate-600 dark:text-zinc-400 mt-1">Pilih dokumen yang perlu direvisi</p>
                                </div>
                                <button
                                    @click="showRevisionModal = false"
                                    class="p-1.5 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                >
                                    <X :size="20" class="text-slate-500" />
                                </button>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="doc in documents"
                                    :key="doc.id"
                                    class="border border-slate-200 dark:border-zinc-700 rounded-xl overflow-hidden"
                                >
                                    <button
                                        @click="toggleDocumentSelection(doc)"
                                        class="w-full p-4 text-left flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                                        :class="{ 'bg-amber-50 dark:bg-amber-900/20': isDocumentSelected(doc.id) }"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors"
                                                :class="isDocumentSelected(doc.id)
                                                    ? 'bg-amber-500 border-amber-500 text-white'
                                                    : 'border-slate-300 dark:border-zinc-600'"
                                            >
                                                <CheckCircle v-if="isDocumentSelected(doc.id)" :size="14" />
                                            </div>
                                            <div>
                                                <p class="font-medium text-slate-900 dark:text-white">{{ doc.type_label }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ doc.original_name }}</p>
                                            </div>
                                        </div>
                                        <Badge :variant="doc.status === 'approved' ? 'success' : doc.status === 'rejected' ? 'error' : 'warning'" size="sm">
                                            {{ doc.status_label }}
                                        </Badge>
                                    </button>

                                    <!-- Revision Note Input -->
                                    <Transition
                                        enter-active-class="transition-all duration-200 ease-out"
                                        enter-from-class="opacity-0 max-h-0"
                                        enter-to-class="opacity-100 max-h-40"
                                        leave-active-class="transition-all duration-200 ease-in"
                                        leave-from-class="opacity-100 max-h-40"
                                        leave-to-class="opacity-0 max-h-0"
                                    >
                                        <div v-if="isDocumentSelected(doc.id)" class="px-4 pb-4 overflow-hidden">
                                            <textarea
                                                :value="getRevisionNote(doc.id)"
                                                @input="(e: Event) => updateRevisionNote(doc.id, (e.target as HTMLTextAreaElement).value)"
                                                rows="2"
                                                placeholder="Catatan revisi untuk dokumen ini..."
                                                class="w-full px-3 py-2 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700
                                                       rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-400
                                                       focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50
                                                       transition-all resize-none"
                                            ></textarea>
                                        </div>
                                    </Transition>
                                </div>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                                    <button
                                        @click="showRevisionModal = false"
                                        :disabled="isSubmitting"
                                        class="w-full px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700
                                               text-slate-700 dark:text-zinc-300 rounded-xl font-semibold transition-colors
                                               disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Batal
                                    </button>
                                </Motion>

                                <Motion :whileTap="{ scale: 0.97 }" class="flex-1">
                                    <button
                                        @click="submitRevision"
                                        :disabled="isSubmitting || selectedDocuments.length === 0"
                                        class="w-full px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-semibold
                                               flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    >
                                        <FileWarning :size="18" />
                                        <span>Kirim Permintaan</span>
                                    </button>
                                </Motion>
                            </div>
                        </div>
                    </Motion>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
