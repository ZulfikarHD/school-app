<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { Mail, ArrowLeft, Send, CheckCircle, LayoutDashboard, AlertCircle } from 'lucide-vue-next';
import { email as passwordEmail } from '@/routes/password';
import { login } from '@/routes';

interface Props {
    status?: string;
}

defineProps<Props>();

const form = useForm({
    email: '',
});

const haptics = useHaptics();
const cooldown = ref(0);
const isSuccess = ref(false);

const isSubmitting = computed(() => form.processing);

const startCooldown = () => {
    cooldown.value = 60;
    const interval = setInterval(() => {
        cooldown.value--;
        if (cooldown.value <= 0) clearInterval(interval);
    }, 1000);
};

const submit = () => {
    haptics.medium();
    form.post(passwordEmail().url, {
        onSuccess: () => {
            haptics.success();
            isSuccess.value = true;
            startCooldown();
            form.reset();
        },
        onError: () => {
            haptics.error();
        },
    });
};
</script>

<template>
    <Head title="Lupa Password" />

    <div class="min-h-screen w-full bg-gray-50 dark:bg-zinc-950 overflow-hidden relative">
        <!-- iOS-like Background Mesh/Blur -->
        <div class="absolute top-[-20%] left-[-10%] w-[70%] h-[70%] rounded-full bg-blue-400/10 blur-[100px] dark:bg-blue-600/10 pointer-events-none animate-pulse-slow"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[70%] h-[70%] rounded-full bg-indigo-400/10 blur-[100px] dark:bg-indigo-600/10 pointer-events-none animate-pulse-slow animation-delay-2000"></div>

        <div class="relative flex min-h-screen flex-col lg:flex-row">
            <!-- Left Side - Branding (Desktop Only) -->
            <Motion
                :initial="{ opacity: 0, x: -50 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
                class="hidden lg:flex flex-1 flex-col justify-center px-16 py-20 relative z-10"
            >
                <div class="mx-auto w-full max-w-xl">
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.1 }"
                        class="mb-10"
                    >
                        <div class="mb-8 inline-flex items-center justify-center rounded-2xl bg-white p-4 shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800">
                            <LayoutDashboard class="h-12 w-12 text-blue-600 dark:text-blue-500" />
                        </div>
                        <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl dark:text-white">
                            Lupa Password?
                        </h1>
                        <p class="mt-6 text-lg text-gray-600 leading-relaxed max-w-md dark:text-gray-400">
                            Jangan khawatir. Masukkan email Anda dan kami akan mengirimkan link untuk mereset password Anda.
                        </p>
                    </Motion>
                </div>
            </Motion>

            <!-- Right Side - Form -->
            <div class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24 relative z-20">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.2 }"
                    class="mx-auto w-full max-w-sm lg:w-96 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl p-8 rounded-3xl shadow-xl border border-white/20 dark:border-zinc-800"
                >
                    <!-- Mobile Logo -->
                    <div class="lg:hidden text-center mb-8">
                        <div class="inline-flex items-center justify-center rounded-xl bg-white p-3 shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800 mb-4">
                            <LayoutDashboard class="h-8 w-8 text-blue-600 dark:text-blue-500" />
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Lupa Password?</h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Kami akan kirim link reset ke email Anda.
                        </p>
                    </div>

                    <div v-if="isSuccess" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl p-6 text-center animate-in fade-in zoom-in duration-300">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-800 mb-4">
                            <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-300" />
                        </div>
                        <h3 class="text-lg font-medium text-green-800 dark:text-green-300 mb-2">Email Terkirim!</h3>
                        <p class="text-sm text-green-600 dark:text-green-400 mb-6">
                            Silakan periksa inbox email Anda untuk instruksi selanjutnya. Link valid selama 1 jam.
                        </p>
                        <button
                            @click="isSuccess = false"
                            class="text-sm font-medium text-green-700 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 underline"
                        >
                            Kirim ulang email
                        </button>
                    </div>

                    <form v-else @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Email Address
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Mail class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-zinc-700 rounded-xl leading-5 bg-white dark:bg-zinc-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition duration-150 ease-in-out sm:text-sm"
                                    placeholder="nama@sekolah.sch.id"
                                    :class="{ 'border-red-500 focus:ring-red-500/20 focus:border-red-500': form.errors.email }"
                                />
                            </div>
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-500 flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.email }}
                            </p>
                        </div>

                        <div v-if="status" class="text-sm font-medium text-green-600 dark:text-green-400">
                            {{ status }}
                        </div>

                        <div>
                            <Motion
                                :whileTap="{ scale: 0.97 }"
                                class="w-full"
                            >
                                <button
                                    type="submit"
                                    :disabled="isSubmitting || cooldown > 0"
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                                >
                                    <span v-if="isSubmitting" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Mengirim...
                                    </span>
                                    <span v-else-if="cooldown > 0">
                                        Kirim Ulang ({{ cooldown }}s)
                                    </span>
                                    <span v-else class="flex items-center gap-2">
                                        Kirim Link Reset <Send class="w-4 h-4" />
                                    </span>
                                </button>
                            </Motion>
                        </div>
                    </form>

                    <div class="mt-8 text-center">
                        <Link
                            :href="login()"
                            class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors"
                        >
                            <ArrowLeft class="w-4 h-4" />
                            Kembali ke Halaman Login
                        </Link>
                    </div>
                </Motion>
            </div>
        </div>
    </div>
</template>

