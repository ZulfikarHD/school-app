<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import AuditLogTable from '@/components/ui/AuditLogTable.vue';
import { Activity } from 'lucide-vue-next';
import { Motion } from 'motion-v';

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
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <Motion 
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
            >
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <Activity class="w-8 h-8 text-blue-600 dark:text-blue-500" />
                    Audit Log
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Monitor aktivitas dan keamanan sistem.
                </p>
            </Motion>

            <!-- Content -->
            <Motion 
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.1 }"
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

