<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import AuditLogTable from '@/components/features/audit/AuditLogTable.vue';
import { Activity, Shield, Clock } from 'lucide-vue-next';
import { Motion } from 'motion-v';

/**
 * Halaman Audit Log untuk monitoring aktivitas dan keamanan sistem
 * dengan filtering, search, dan expandable detail rows
 */

interface Props {
    logs: any;
    filters: any;
    users: any[];
}

defineProps<Props>();
</script>

<template>
    <Head title="Audit Log" />

    <AppLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
            <!-- Header Section dengan gradient subtle -->
            <Motion
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
            >
                <div class="bg-white px-6 py-8 border-b border-gray-100 dark:bg-zinc-900 dark:border-zinc-800">
                    <div class="mx-auto max-w-7xl">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <!-- Icon Container dengan gradient -->
                                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/25">
                                    <Activity class="w-7 h-7 text-white" />
                                </div>
                                <div>
                                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                                        Audit Log
                                    </h1>
                                    <p class="mt-1 text-gray-600 dark:text-gray-400">
                                        Monitor aktivitas dan keamanan sistem secara real-time
                                    </p>
                                </div>
                            </div>

                            <!-- Quick Stats badges -->
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2 px-4 py-2 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 rounded-xl border border-emerald-200 dark:border-emerald-800">
                                    <Shield :size="16" />
                                    <span class="text-sm font-medium">Sistem Aman</span>
                                </div>
                                <div class="hidden sm:flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-400 rounded-xl">
                                    <Clock :size="16" />
                                    <span class="text-sm">Live Update</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Content Area -->
            <div class="mx-auto max-w-7xl px-6 py-8">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.1 }"
                >
                    <AuditLogTable
                        :logs="logs"
                        :filters="filters"
                        :users="users"
                    />
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>

