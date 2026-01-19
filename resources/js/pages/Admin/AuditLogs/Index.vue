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
        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <!-- Header Section -->
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <!-- Icon Container -->
                            <div class="w-12 h-12 rounded-xl bg-linear-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/25 shrink-0">
                                <Activity class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Audit Log
                                </h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Monitor aktivitas dan keamanan sistem secara real-time
                                </p>
                            </div>
                        </div>

                        <!-- Quick Stats badges -->
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 rounded-xl border border-emerald-200 dark:border-emerald-800">
                                <Shield :size="16" />
                                <span class="text-sm font-medium">Sistem Aman</span>
                            </div>
                            <div class="hidden sm:flex items-center gap-2 px-3 py-2 bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400 rounded-xl">
                                <Clock :size="16" />
                                <span class="text-sm">Live Update</span>
                            </div>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Content Area -->
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <AuditLogTable
                    :logs="logs"
                    :filters="filters"
                    :users="users"
                />
            </Motion>
        </div>
    </AppLayout>
</template>

