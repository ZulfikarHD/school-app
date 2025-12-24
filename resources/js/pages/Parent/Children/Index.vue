<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { GraduationCap, Users } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import AppLayout from '@/components/layouts/AppLayout.vue';
import ChildCard from '@/components/ui/ChildCard.vue';
import { useHaptics } from '@/composables/useHaptics';
import { show as showChild } from '@/routes/parent/children';
import type { Student } from '@/types/student';

interface Props {
    children: Student[];
    message?: string;
}

defineProps<Props>();
const haptics = useHaptics();

const handleViewChild = (student: Student) => {
    haptics.light();
    router.visit(showChild(student.id).url);
};
</script>

<template>
    <AppLayout title="Data Anak">
        <Head title="Data Anak" />

        <div class="max-w-6xl mx-auto space-y-6">
            <Motion
                :initial="{ opacity: 0, y: -10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut' }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-zinc-800">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl text-white shadow-lg">
                            <Users class="w-6 h-6" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                Data Anak
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                Informasi data anak yang terdaftar
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Children Grid -->
            <Motion
                v-if="children.length > 0"
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <ChildCard
                        v-for="child in children"
                        :key="child.id"
                        :student="child"
                        @view="handleViewChild"
                    />
                </div>
            </Motion>

            <!-- Empty State -->
            <Motion
                v-else
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3, ease: 'easeOut', delay: 0.1 }"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-12 text-center shadow-sm border border-gray-200 dark:border-zinc-800">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-gray-100 dark:bg-zinc-800 flex items-center justify-center">
                        <GraduationCap class="w-10 h-10 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        {{ message || 'Tidak ada data anak' }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Hubungi admin sekolah untuk informasi lebih lanjut
                    </p>
                </div>
            </Motion>
        </div>
    </AppLayout>
</template>
