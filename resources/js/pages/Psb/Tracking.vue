<script setup lang="ts">
/**
 * PSB Tracking Page - Halaman tracking status pendaftaran
 *
 * Page ini bertujuan untuk memungkinkan calon siswa mengecek status
 * pendaftaran mereka menggunakan nomor pendaftaran
 */
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Motion } from 'motion-v';
import {
    Search,
    ChevronLeft,
    AlertCircle,
    User,
    FileText,
    CheckCircle,
    XCircle,
    Clock,
    RefreshCw,
} from 'lucide-vue-next';
import { FormInput } from '@/components/ui/Form';
import PsbTimeline from '@/components/features/psb/PsbTimeline.vue';
import { landing as psbLanding, checkStatus } from '@/routes/psb';
import { useHaptics } from '@/composables/useHaptics';

interface TimelineItem {
    step: string;
    label: string;
    completed: boolean;
    current: boolean;
    date: string | null;
}

interface DocumentItem {
    type: string;
    type_label: string;
    status: string;
    revision_note: string | null;
}

interface Props {
    registration?: {
        registration_number: string;
        student_name: string;
        status: string;
        status_label: string;
        rejection_reason: string | null;
        created_at: string;
        verified_at: string | null;
        announced_at: string | null;
    } | null;
    timeline?: TimelineItem[];
    documents?: DocumentItem[];
    error?: string | null;
}

const props = defineProps<Props>();
const haptics = useHaptics();

/**
 * Form untuk input nomor pendaftaran
 */
const form = useForm({
    registration_number: props.registration?.registration_number || '',
});

/**
 * State loading
 */
const isSearching = ref(false);

/**
 * Submit pencarian status
 */
const searchStatus = () => {
    if (!form.registration_number.trim()) {
        haptics.heavy();
        return;
    }

    haptics.medium();
    isSearching.value = true;

    form.post(checkStatus().url, {
        preserveScroll: true,
        onFinish: () => {
            isSearching.value = false;
        },
    });
};

/**
 * Get status badge color
 */
const getStatusColor = (status: string): string => {
    switch (status) {
        case 'approved':
        case 'completed':
            return 'emerald';
        case 'rejected':
            return 'red';
        case 'waiting_list':
            return 'purple';
        case 'pending':
        case 'document_review':
            return 'amber';
        case 're_registration':
            return 'sky';
        default:
            return 'slate';
    }
};

/**
 * Get document status icon
 */
const getDocumentStatusIcon = (status: string) => {
    switch (status) {
        case 'approved':
            return CheckCircle;
        case 'rejected':
            return XCircle;
        default:
            return Clock;
    }
};

/**
 * Get document status color
 */
const getDocumentStatusColor = (status: string): string => {
    switch (status) {
        case 'approved':
            return 'text-emerald-500';
        case 'rejected':
            return 'text-red-500';
        default:
            return 'text-amber-500';
    }
};
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <Head title="Cek Status Pendaftaran - PSB" />

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-slate-200 bg-white shadow-sm">
            <div class="mx-auto flex max-w-2xl items-center justify-between px-4 py-4">
                <Link
                    :href="psbLanding()"
                    class="flex items-center gap-2 text-slate-600 transition-colors hover:text-slate-900"
                >
                    <ChevronLeft class="h-5 w-5" />
                    <span class="hidden sm:inline">Kembali</span>
                </Link>
                <h1 class="text-lg font-semibold text-slate-900">Cek Status</h1>
                <div class="w-20"></div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-2xl px-4 py-6">
            <!-- Search Form -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3 }"
            >
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50">
                            <Search class="h-5 w-5 text-emerald-600" />
                        </div>
                        <div>
                            <h2 class="font-semibold text-slate-900">Cek Status Pendaftaran</h2>
                            <p class="text-sm text-slate-500">
                                Masukkan nomor pendaftaran Anda
                            </p>
                        </div>
                    </div>

                    <form @submit.prevent="searchStatus" class="flex gap-3">
                        <div class="flex-1">
                            <FormInput
                                v-model="form.registration_number"
                                placeholder="Contoh: PSB/2025/0001"
                                :error="form.errors.registration_number"
                            />
                        </div>
                        <button
                            type="submit"
                            :disabled="isSearching || !form.registration_number.trim()"
                            class="flex shrink-0 items-center gap-2 rounded-xl bg-emerald-500 px-6 py-3 font-medium text-white transition-all hover:bg-emerald-600 active:scale-97 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <RefreshCw v-if="isSearching" class="h-5 w-5 animate-spin" />
                            <Search v-else class="h-5 w-5" />
                            <span class="hidden sm:inline">Cari</span>
                        </button>
                    </form>
                </div>
            </Motion>

            <!-- Error Message -->
            <Motion
                v-if="error"
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, delay: 0.1 }"
                class="mt-6"
            >
                <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                    <div class="flex items-center gap-3">
                        <AlertCircle class="h-5 w-5 text-red-500" />
                        <p class="text-sm font-medium text-red-800">{{ error }}</p>
                    </div>
                </div>
            </Motion>

            <!-- Results -->
            <Motion
                v-if="registration"
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, delay: 0.1 }"
                class="mt-6 space-y-6"
            >
                <!-- Registration Info Card -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                                <User class="h-6 w-6 text-slate-600" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900">
                                    {{ registration.student_name }}
                                </h3>
                                <p class="text-sm text-slate-500">
                                    {{ registration.registration_number }}
                                </p>
                            </div>
                        </div>
                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="{
                                'bg-emerald-100 text-emerald-700': ['approved', 'completed'].includes(registration.status),
                                'bg-red-100 text-red-700': registration.status === 'rejected',
                                'bg-purple-100 text-purple-700': registration.status === 'waiting_list',
                                'bg-amber-100 text-amber-700': ['pending', 'document_review'].includes(registration.status),
                                'bg-sky-100 text-sky-700': registration.status === 're_registration',
                            }"
                        >
                            {{ registration.status_label }}
                        </span>
                    </div>

                    <div class="grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium uppercase text-slate-500">Tanggal Daftar</p>
                            <p class="mt-1 text-sm font-medium text-slate-900">
                                {{ registration.created_at }}
                            </p>
                        </div>
                        <div v-if="registration.verified_at">
                            <p class="text-xs font-medium uppercase text-slate-500">Diverifikasi</p>
                            <p class="mt-1 text-sm font-medium text-slate-900">
                                {{ registration.verified_at }}
                            </p>
                        </div>
                        <div v-if="registration.announced_at">
                            <p class="text-xs font-medium uppercase text-slate-500">Diumumkan</p>
                            <p class="mt-1 text-sm font-medium text-slate-900">
                                {{ registration.announced_at }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="mb-6 font-semibold text-slate-900">Progress Pendaftaran</h3>
                    <PsbTimeline
                        v-if="timeline"
                        :timeline="timeline"
                        :status="registration.status"
                        :rejection-reason="registration.rejection_reason"
                    />
                </div>

                <!-- Documents Status -->
                <div
                    v-if="documents && documents.length > 0"
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-50">
                            <FileText class="h-5 w-5 text-amber-600" />
                        </div>
                        <h3 class="font-semibold text-slate-900">Status Dokumen</h3>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="doc in documents"
                            :key="doc.type"
                            class="flex items-start justify-between rounded-lg bg-slate-50 p-3"
                        >
                            <div class="flex items-center gap-3">
                                <component
                                    :is="getDocumentStatusIcon(doc.status)"
                                    class="h-5 w-5"
                                    :class="getDocumentStatusColor(doc.status)"
                                />
                                <div>
                                    <p class="font-medium text-slate-900">{{ doc.type_label }}</p>
                                    <p
                                        v-if="doc.revision_note"
                                        class="text-xs text-red-600"
                                    >
                                        {{ doc.revision_note }}
                                    </p>
                                </div>
                            </div>
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                :class="{
                                    'bg-emerald-100 text-emerald-700': doc.status === 'approved',
                                    'bg-red-100 text-red-700': doc.status === 'rejected',
                                    'bg-amber-100 text-amber-700': doc.status === 'pending',
                                }"
                            >
                                {{ doc.status === 'approved' ? 'Valid' : doc.status === 'rejected' ? 'Revisi' : 'Pending' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="rounded-xl border border-sky-200 bg-sky-50 p-4">
                    <div class="flex gap-3">
                        <AlertCircle class="h-5 w-5 shrink-0 text-sky-600" />
                        <div class="text-sm text-sky-800">
                            <p class="font-medium">Informasi</p>
                            <p class="mt-1">
                                Jika ada pertanyaan terkait status pendaftaran, silakan hubungi panitia PSB
                                melalui nomor yang tertera di halaman utama.
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Empty State -->
            <Motion
                v-if="!registration && !error"
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, delay: 0.2 }"
                class="mt-12 text-center"
            >
                <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-slate-100">
                    <Search class="h-10 w-10 text-slate-400" />
                </div>
                <h3 class="mt-4 font-semibold text-slate-900">Cek Status Pendaftaran</h3>
                <p class="mt-2 text-sm text-slate-500">
                    Masukkan nomor pendaftaran yang Anda terima saat mendaftar untuk melihat
                    status dan progress pendaftaran Anda.
                </p>
            </Motion>
        </main>
    </div>
</template>
