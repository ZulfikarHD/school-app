<script setup lang="ts">
/**
 * Parent PSB Welcome Page
 *
 * Halaman selamat datang setelah pendaftaran selesai,
 * yaitu: celebration UI, info siswa, dan langkah selanjutnya
 */
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import {
    Trophy,
    GraduationCap,
    Calendar,
    Clock,
    CheckCircle,
    ArrowRight,
    Sparkles,
    Home,
    BookOpen,
    Users
} from 'lucide-vue-next';
import AppLayout from '@/components/layouts/AppLayout.vue';
import { useHaptics } from '@/composables/useHaptics';
import { dashboard } from '@/routes/parent';
// TODO: Gunakan route dari Wayfinder setelah backend dibuat

interface Student {
    name: string;
    nis: string;
    class_name: string;
}

interface NextSteps {
    orientation_date?: string;
    first_day?: string;
    required_items?: string[];
}

interface Props {
    student: Student;
    nextSteps: NextSteps;
    schoolName: string;
}

const props = defineProps<Props>();
const haptics = useHaptics();

// Confetti animation state
const showConfetti = ref(false);

onMounted(() => {
    // Trigger confetti animation
    setTimeout(() => {
        showConfetti.value = true;
        haptics.success();
    }, 500);
});

/**
 * Format date
 */
const formatDate = (dateString: string | undefined): string => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Selamat Bergabung!" />

        <!-- Confetti Animation -->
        <div v-if="showConfetti" class="fixed inset-0 pointer-events-none z-50 overflow-hidden">
            <div class="confetti-container">
                <div v-for="i in 50" :key="i" class="confetti" :style="{
                    left: `${Math.random() * 100}%`,
                    animationDelay: `${Math.random() * 3}s`,
                    backgroundColor: ['#10B981', '#8B5CF6', '#F59E0B', '#EF4444', '#3B82F6', '#EC4899'][Math.floor(Math.random() * 6)]
                }"></div>
            </div>
        </div>

        <div class="min-h-[80vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-2xl w-full space-y-8">
                <!-- Hero Section -->
                <Motion
                    :initial="{ opacity: 0, y: -30, scale: 0.9 }"
                    :animate="{ opacity: 1, y: 0, scale: 1 }"
                    :transition="{ type: 'spring', stiffness: 200, damping: 20 }"
                    class="text-center"
                >
                    <!-- Trophy Icon -->
                    <Motion
                        :initial="{ scale: 0, rotate: -180 }"
                        :animate="{ scale: 1, rotate: 0 }"
                        :transition="{ type: 'spring', stiffness: 200, damping: 15, delay: 0.3 }"
                    >
                        <div class="mx-auto w-24 h-24 rounded-3xl bg-linear-to-br from-amber-400 via-amber-500 to-orange-500 flex items-center justify-center shadow-2xl shadow-amber-500/30 mb-6">
                            <Trophy :size="48" class="text-white" />
                        </div>
                    </Motion>

                    <!-- Welcome Text -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.5 }"
                    >
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <Sparkles :size="20" class="text-amber-500" />
                            <span class="text-sm font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wide">
                                Pendaftaran Selesai
                            </span>
                            <Sparkles :size="20" class="text-amber-500" />
                        </div>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.6 }"
                    >
                        <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-slate-100">
                            Selamat Bergabung! 🎉
                        </h1>
                    </Motion>

                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ delay: 0.7 }"
                    >
                        <p class="text-lg text-slate-600 dark:text-slate-400 mt-3">
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ student.name }}</span>
                            resmi menjadi siswa {{ schoolName }}
                        </p>
                    </Motion>
                </Motion>

                <!-- Student Info Card -->
                <Motion
                    :initial="{ opacity: 0, y: 30 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.8 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200 dark:border-zinc-800">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-16 h-16 rounded-2xl bg-linear-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                                <GraduationCap :size="32" class="text-white" />
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ student.name }}
                                </h2>
                                <p class="text-slate-500 dark:text-slate-400">Siswa Baru</p>
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <!-- NIS -->
                            <div class="p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-xl">
                                <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 mb-1">
                                    <BookOpen :size="16" />
                                    <span class="text-xs font-medium uppercase tracking-wide">NIS</span>
                                </div>
                                <p class="text-xl font-bold font-mono text-slate-900 dark:text-slate-100">
                                    {{ student.nis }}
                                </p>
                            </div>

                            <!-- Kelas -->
                            <div class="p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-xl">
                                <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 mb-1">
                                    <Users :size="16" />
                                    <span class="text-xs font-medium uppercase tracking-wide">Kelas</span>
                                </div>
                                <p class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ student.class_name }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Next Steps -->
                <Motion
                    :initial="{ opacity: 0, y: 30 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.9 }"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200 dark:border-zinc-800">
                        <h3 class="font-bold text-lg text-slate-900 dark:text-slate-100 mb-6">
                            Langkah Selanjutnya
                        </h3>

                        <div class="space-y-4">
                            <!-- Orientation Date -->
                            <div v-if="nextSteps.orientation_date" class="flex items-start gap-4 p-4 bg-violet-50 dark:bg-violet-900/20 rounded-xl border border-violet-100 dark:border-violet-800">
                                <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center shrink-0">
                                    <Calendar :size="20" class="text-violet-600 dark:text-violet-400" />
                                </div>
                                <div>
                                    <p class="font-semibold text-violet-800 dark:text-violet-300">
                                        Orientasi Siswa Baru
                                    </p>
                                    <p class="text-sm text-violet-600 dark:text-violet-400 mt-0.5">
                                        {{ formatDate(nextSteps.orientation_date) }}
                                    </p>
                                </div>
                            </div>

                            <!-- First Day -->
                            <div v-if="nextSteps.first_day" class="flex items-start gap-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                    <Clock :size="20" class="text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <p class="font-semibold text-emerald-800 dark:text-emerald-300">
                                        Hari Pertama Sekolah
                                    </p>
                                    <p class="text-sm text-emerald-600 dark:text-emerald-400 mt-0.5">
                                        {{ formatDate(nextSteps.first_day) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Required Items -->
                            <div v-if="nextSteps.required_items && nextSteps.required_items.length > 0" class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-100 dark:border-amber-800">
                                <p class="font-semibold text-amber-800 dark:text-amber-300 mb-3">
                                    Perlengkapan yang Diperlukan
                                </p>
                                <ul class="space-y-2">
                                    <li
                                        v-for="(item, index) in nextSteps.required_items"
                                        :key="index"
                                        class="flex items-center gap-2 text-sm text-amber-700 dark:text-amber-400"
                                    >
                                        <CheckCircle :size="16" class="text-amber-500 shrink-0" />
                                        {{ item }}
                                    </li>
                                </ul>
                            </div>

                            <!-- Empty State jika tidak ada info -->
                            <div
                                v-if="!nextSteps.orientation_date && !nextSteps.first_day && (!nextSteps.required_items || nextSteps.required_items.length === 0)"
                                class="p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-xl text-center"
                            >
                                <p class="text-slate-500 dark:text-slate-400">
                                    Informasi selanjutnya akan disampaikan melalui portal orang tua.
                                </p>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- CTA Button -->
                <Motion
                    :initial="{ opacity: 0, y: 30 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 1 }"
                >
                    <Link
                        :href="dashboard().url"
                        class="flex items-center justify-center gap-3 w-full px-8 py-4 bg-linear-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-2xl font-bold text-lg shadow-xl shadow-emerald-500/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <Home :size="22" />
                        Ke Dashboard Orang Tua
                        <ArrowRight :size="22" />
                    </Link>
                </Motion>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Confetti Animation */
.confetti-container {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.confetti {
    position: absolute;
    width: 10px;
    height: 10px;
    top: -10px;
    opacity: 0;
    animation: confetti-fall 4s ease-out forwards;
}

.confetti:nth-child(odd) {
    border-radius: 50%;
}

.confetti:nth-child(even) {
    border-radius: 2px;
    transform: rotate(45deg);
}

@keyframes confetti-fall {
    0% {
        opacity: 1;
        top: -10px;
        transform: translateX(0) rotate(0deg);
    }
    100% {
        opacity: 0;
        top: 100vh;
        transform: translateX(calc(var(--random, 1) * 100px - 50px)) rotate(720deg);
    }
}

/* Random horizontal movement for each confetti */
.confetti:nth-child(1) { --random: 0.2; }
.confetti:nth-child(2) { --random: 0.4; }
.confetti:nth-child(3) { --random: 0.6; }
.confetti:nth-child(4) { --random: 0.8; }
.confetti:nth-child(5) { --random: 1.0; }
.confetti:nth-child(6) { --random: 0.3; }
.confetti:nth-child(7) { --random: 0.5; }
.confetti:nth-child(8) { --random: 0.7; }
.confetti:nth-child(9) { --random: 0.9; }
.confetti:nth-child(10) { --random: 0.1; }
/* Repeat pattern for more confetti */
.confetti:nth-child(n+11) { --random: calc((var(--n, 1) % 10) / 10); }
</style>
