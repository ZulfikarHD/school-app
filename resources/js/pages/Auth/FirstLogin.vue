<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import firstLogin from '@/routes/auth/first-login';

/**
 * First Login page untuk user yang baru pertama kali login dan harus
 * mengubah password default mereka untuk security compliance
 * dengan iOS-like animations dan haptic feedback
 */

interface Props {
    user: {
        name: string;
        username: string;
        email: string;
    };
}

const props = defineProps<Props>();

const form = useForm({
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const isSubmitting = computed(() => form.processing);

const haptics = useHaptics();

/**
 * Submit form untuk update password dengan validation, error handling,
 * dan haptic feedback untuk user experience yang lebih baik
 */
const submit = () => {
    haptics.medium();
    form.post(firstLogin.update().url, {
        onFinish: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
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
 * Toggle visibility password confirmation dengan haptic feedback
 */
const togglePasswordConfirmationVisibility = () => {
    haptics.light();
    showPasswordConfirmation.value = !showPasswordConfirmation.value;
};
</script>

<template>
    <Head title="Ubah Password - Login Pertama" />

    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 dark:bg-zinc-950">
        <Motion
            :initial="{ opacity: 0, scale: 0.95, y: 20 }"
            :animate="{ opacity: 1, scale: 1, y: 0 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 25, mass: 0.8 }"
            class="w-full max-w-md"
        >
            <!-- Card Container dengan glass effect untuk iOS-like design -->
            <div class="overflow-hidden rounded-2xl bg-white/95 shadow-xl border border-gray-100 dark:bg-zinc-900/95 dark:border-zinc-800">
                <!-- Header -->
                <div class="bg-blue-600 px-8 py-10 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white">Login Pertama</h1>
                    <p class="mt-2 text-sm text-blue-100">Silakan ubah password Anda</p>
                </div>

                <!-- Welcome Message -->
                <div class="border-b border-gray-100 bg-blue-50 px-8 py-4 dark:border-zinc-800 dark:bg-zinc-900/50">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Selamat datang, <strong>{{ props.user.name }}</strong>!
                    </p>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                        Untuk keamanan akun, harap ubah password default Anda sebelum melanjutkan.
                    </p>
                </div>

                <!-- Form Container -->
                <form @submit.prevent="submit" class="space-y-6 px-8 py-8">
                    <!-- Password Requirements Info -->
                    <div class="rounded-lg bg-amber-50 p-4 dark:bg-amber-900/20">
                        <h3 class="mb-2 text-sm font-semibold text-amber-800 dark:text-amber-200">
                            Persyaratan Password:
                        </h3>
                        <ul class="space-y-1 text-xs text-amber-700 dark:text-amber-300">
                            <li class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Minimal 8 karakter</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Mengandung huruf besar dan kecil</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Mengandung angka dan simbol</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password Baru
                        </label>
                        <div class="relative mt-2">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                class="block w-full rounded-lg border border-gray-200 bg-white px-4 py-3 pr-12 text-gray-900 transition-all duration-200 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-gray-500"
                                placeholder="Masukkan password baru"
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

                    <!-- Password Confirmation Input -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Konfirmasi Password
                        </label>
                        <div class="relative mt-2">
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                class="block w-full rounded-lg border border-gray-200 bg-white px-4 py-3 pr-12 text-gray-900 transition-all duration-200 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-gray-500"
                                placeholder="Masukkan ulang password baru"
                            />
                            <!-- Toggle Show/Hide Password Confirmation -->
                            <button
                                type="button"
                                @click="togglePasswordConfirmationVisibility"
                                class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md p-1 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:text-gray-400 dark:hover:text-gray-200"
                                :aria-label="showPasswordConfirmation ? 'Sembunyikan password' : 'Tampilkan password'"
                            >
                                <svg v-if="!showPasswordConfirmation" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button dengan spring animation -->
                    <Motion
                        :whileTap="{ scale: 0.97 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                    >
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:bg-blue-700 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-500/50 disabled:cursor-not-allowed disabled:opacity-60"
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
                            <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan & Lanjutkan' }}</span>
                        </button>
                    </Motion>
                </form>

                <!-- Footer -->
                <div class="border-t border-gray-100 bg-gray-50 px-8 py-4 text-center dark:border-zinc-800 dark:bg-zinc-900/50">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Password Anda akan di-enkripsi dan disimpan dengan aman
                    </p>
                </div>
            </div>
        </Motion>
    </div>
</template>

