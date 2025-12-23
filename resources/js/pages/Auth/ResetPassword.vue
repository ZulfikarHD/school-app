<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { Lock, RefreshCw, LayoutDashboard, Eye, EyeOff } from 'lucide-vue-next';
import PasswordStrengthMeter from '@/components/ui/PasswordStrengthMeter.vue';
import { update as passwordUpdate } from '@/routes/password';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const haptics = useHaptics();
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const isSuccess = ref(false);

const isSubmitting = computed(() => form.processing);

const submit = () => {
    haptics.medium();
    form.post(passwordUpdate().url, {
        onSuccess: () => {
            haptics.success();
            isSuccess.value = true;
            setTimeout(() => {
                // Backend usually redirects, but if we stay on page, we can show success
                // and then redirect manually if needed, but backend redirect is preferred.
            }, 2000);
        },
        onError: () => {
            haptics.error();
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head title="Reset Password" />

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
                            Reset Password
                        </h1>
                        <p class="mt-6 text-lg text-gray-600 leading-relaxed max-w-md dark:text-gray-400">
                            Buat password baru yang aman untuk akun Anda. Gunakan kombinasi huruf, angka, dan simbol.
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
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Reset Password</h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Masukkan password baru Anda di bawah ini.
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Hidden Token -->
                        <input type="hidden" name="token" :value="form.token">

                        <!-- Email (Read Only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Email Address
                            </label>
                            <input
                                type="email"
                                disabled
                                :value="form.email"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-xl bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-gray-400 sm:text-sm cursor-not-allowed"
                            />
                        </div>

                        <!-- Password Baru -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Password Baru
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Lock class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 dark:border-zinc-700 rounded-xl leading-5 bg-white dark:bg-zinc-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition duration-150 ease-in-out sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.password }"
                                    placeholder="Password baru"
                                />
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" @click="showPassword = !showPassword">
                                    <component :is="showPassword ? EyeOff : Eye" class="h-5 w-5 text-gray-400" />
                                </div>
                            </div>
                            <PasswordStrengthMeter :password="form.password" class="mt-2" />
                            <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Konfirmasi Password
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Lock class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    required
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 dark:border-zinc-700 rounded-xl leading-5 bg-white dark:bg-zinc-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition duration-150 ease-in-out sm:text-sm"
                                    placeholder="Ulangi password baru"
                                />
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" @click="showConfirmPassword = !showConfirmPassword">
                                    <component :is="showConfirmPassword ? EyeOff : Eye" class="h-5 w-5 text-gray-400" />
                                </div>
                            </div>
                             <p v-if="form.errors.password_confirmation" class="mt-1 text-xs text-red-500">
                                {{ form.errors.password_confirmation }}
                            </p>
                        </div>

                        <div>
                            <Motion
                                :whileTap="{ scale: 0.97 }"
                                class="w-full"
                            >
                                <button
                                    type="submit"
                                    :disabled="isSubmitting"
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                                >
                                    <span v-if="isSubmitting" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Memproses...
                                    </span>
                                    <span v-else class="flex items-center gap-2">
                                        Reset Password <RefreshCw class="w-4 h-4" />
                                    </span>
                                </button>
                            </Motion>
                        </div>
                    </form>
                </Motion>
            </div>
        </div>
    </div>
</template>

