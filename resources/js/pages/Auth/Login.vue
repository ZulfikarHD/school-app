<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';

/**
 * Login page untuk authentication user dengan support
 * username/email login, remember me, dan rate limiting feedback
 * serta iOS-like animations dan haptic feedback
 */

const form = useForm({
    identifier: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const isSubmitting = computed(() => form.processing);

const haptics = useHaptics();

/**
 * Submit login form dengan validation, error handling,
 * dan haptic feedback untuk user experience yang lebih baik
 */
const submit = () => {
    haptics.medium();
    form.post(route('login'), {
        onFinish: () => {
            if (form.errors.password) {
                form.reset('password');
                haptics.error();
            } else if (!form.hasErrors) {
                haptics.success();
            }
        },
    });
};

/**
 * Toggle visibility password dengan haptic feedback
 */
const togglePasswordVisibility = () => {
    haptics.light();
    showPassword.value = !showPassword.value;
};

/**
 * Handle checkbox change dengan haptic feedback
 */
const handleRememberChange = () => {
    haptics.selection();
};
</script>

<template>
    <Head title="Masuk ke Sistem" />

    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 px-4 dark:from-gray-900 dark:to-gray-800">
        <Motion
            :initial="{ opacity: 0, scale: 0.95, y: 20 }"
            :animate="{ opacity: 1, scale: 1, y: 0 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 25, mass: 0.8 }"
            class="w-full max-w-md"
        >
            <!-- Card Container dengan glass effect untuk iOS-like design -->
            <div class="overflow-hidden rounded-2xl bg-white/80 shadow-2xl backdrop-blur-xl dark:bg-gray-800/80">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-10 text-center">
                    <h1 class="text-3xl font-bold text-white">Sistem Sekolah</h1>
                    <p class="mt-2 text-sm text-blue-100">Silakan masuk untuk melanjutkan</p>
                </div>

                <!-- Form Container -->
                <form @submit.prevent="submit" class="space-y-6 px-8 py-8">
                    <!-- Identifier Input (Username/Email) -->
                    <div>
                        <label for="identifier" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Username atau Email
                        </label>
                        <input
                            id="identifier"
                            v-model="form.identifier"
                            type="text"
                            required
                            autocomplete="username"
                            class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 transition-all duration-200 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-500"
                            placeholder="Masukkan username atau email"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.identifier }"
                        />
                        <p v-if="form.errors.identifier" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.identifier }}
                        </p>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password
                        </label>
                        <div class="relative mt-2">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 pr-12 text-gray-900 transition-all duration-200 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-500"
                                placeholder="Masukkan password"
                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.password }"
                            />
                            <!-- Toggle Show/Hide Password -->
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md p-1 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:text-gray-400 dark:hover:text-gray-200"
                                :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                            >
                                <svg v-if="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                @change="handleRememberChange"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 transition-colors focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700"
                            />
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Ingat saya</span>
                        </label>
                    </div>

                    <!-- Submit Button dengan spring animation -->
                    <Motion
                        :whileTap="{ scale: 0.97 }"
                        :transition="{ type: 'spring', stiffness: 500, damping: 30 }"
                    >
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="flex w-full items-center justify-center rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:from-blue-700 hover:to-indigo-700 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-500/50 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:from-blue-600 disabled:hover:to-indigo-600"
                        >
                        <svg
                            v-if="isSubmitting"
                            class="mr-2 h-5 w-5 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <span>{{ isSubmitting ? 'Memproses...' : 'Masuk' }}</span>
                        </button>
                    </Motion>
                </form>

                <!-- Footer -->
                <div class="border-t border-gray-200 bg-gray-50 px-8 py-4 text-center dark:border-gray-700 dark:bg-gray-800/50">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        &copy; 2024 Sistem Informasi Sekolah
                    </p>
                </div>
            </div>
        </Motion>
    </div>
</template>

