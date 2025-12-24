<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import StudentDetailTabs from '@/components/ui/StudentDetailTabs.vue';
import { useHaptics } from '@/composables/useHaptics';
import { index as childrenIndex } from '@/routes/parent/children';
import type { Student, Guardian, ClassHistory } from '@/types/student';

interface Props {
    student: Student & {
        guardians: Guardian[];
        primaryGuardian?: Guardian;
        classHistory?: ClassHistory[];
    };
}

defineProps<Props>();
const haptics = useHaptics();
</script>

<template>
    <AppLayout title="Detail Anak">
        <Head :title="`Detail - ${student.nama_lengkap}`" />

        <div class="max-w-7xl mx-auto space-y-6">
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-zinc-800">
                    <div class="flex items-center gap-4">
                        <Motion :whileTap="{ scale: 0.97 }">
                            <Link
                                :href="childrenIndex().url"
                                @click="haptics.light()"
                                class="p-2.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-xl transition-colors"
                            >
                                <ChevronLeft class="w-5 h-5" />
                            </Link>
                        </Motion>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                Detail Anak
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                Informasi lengkap data anak
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <StudentDetailTabs :student="student" />
            </Motion>
        </div>
    </AppLayout>
</template>
