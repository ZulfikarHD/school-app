<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { User, Lock, Eye, EyeOff, Loader2, LogIn, ShieldCheck, Zap, LayoutDashboard, AlertOctagon } from 'lucide-vue-next';
import login from '@/routes/login';
import { request as passwordRequest } from '@/routes/password';

/**
 * Login page dengan full-page immersive design
 * Menggunakan split-screen layout untuk desktop dan stacked untuk mobile
 * Dengan iOS-like animations dan haptic feedback untuk UX yang optimal
 */

const page = usePage();
const form = useForm({
    identifier: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const isSubmitting = computed(() => form.processing);
const haptics = useHaptics();

// Lockout Logic
const lockoutUntil = computed(() => {
    // Check if backend explicitly sends lockout timestamp
    // Backend returns locked_until timestamp dalam error response
    // @ts-expect-error - locked_until may not be in page props type definition
    return page.props.locked_until || null;
});

const lockoutRemaining = ref(0);
let lockoutInterval: ReturnType<typeof setInterval>;

const startLockoutTimer = () => {
    if (!lockoutUntil.value) return;

    const targetTime = lockoutUntil.value * 1000; // PHP timestamp is seconds, JS is ms

    const updateTimer = () => {
        const now = Date.now();
        const diff = Math.ceil((targetTime - now) / 1000);

        if (diff <= 0) {
            lockoutRemaining.value = 0;
            clearInterval(lockoutInterval);
            // Optionally reload to clear error state if needed
        } else {
            lockoutRemaining.value = diff;
        }
    };

    updateTimer(); // Initial call
    lockoutInterval = setInterval(updateTimer, 1000);
};

// Watch for errors to detect lockout
watch(() => page.props.errors, () => {
    // Also check if locked_until prop is present
    if (page.props.locked_until) {
        startLockoutTimer();
    }
}, { deep: true, immediate: true });

// Format remaining time
const formattedLockoutTime = computed(() => {
    const minutes = Math.floor(lockoutRemaining.value / 60);
    const seconds = lockoutRemaining.value % 60;

    if (minutes > 0) {
        return `${minutes} menit ${seconds} detik`;
    }
    return `${seconds} detik`;
});

/**
 * Submit login form dengan validation, error handling,
 * dan haptic feedback untuk user experience yang lebih baik
 */
const submit = () => {
    if (lockoutRemaining.value > 0) {
        haptics.error();
        return;
    }

    haptics.medium();
    form.post(login.post().url, {
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

onMounted(() => {
    if (page.props.locked_until) {
        startLockoutTimer();
    }
});

onUnmounted(() => {
    clearInterval(lockoutInterval);
});
</script>

<template>
    <Head title="Masuk ke Sistem" />

    <div class="min-h-screen w-full bg-gray-50 dark:bg-zinc-950 overflow-hidden relative">
        <!-- iOS-like Background Mesh/Blur -->
        <div class="absolute top-[-20%] left-[-10%] w-[70%] h-[70%] rounded-full bg-blue-400/10 blur-[100px] dark:bg-blue-600/10 pointer-events-none animate-pulse-slow"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[70%] h-[70%] rounded-full bg-indigo-400/10 blur-[100px] dark:bg-indigo-600/10 pointer-events-none animate-pulse-slow animation-delay-2000"></div>

        <!-- Main Container -->
        <div class="relative flex min-h-screen flex-col lg:flex-row">
            <!-- Left Side - Branding & Visual (Desktop Only) -->
            <Motion
                :initial="{ opacity: 0, x: -50 }"
                :animate="{ opacity: 1, x: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
                class="hidden lg:flex flex-1 flex-col justify-center px-16 py-20 relative z-10"
            >
                <div class="mx-auto w-full max-w-xl">
                    <!-- Logo/Brand Section -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.1 }"
                        class="mb-10"
                    >
                        <div class="mb-8 inline-flex items-center justify-center rounded-2xl bg-white p-4 shadow-sm border border-gray-100 dark:bg-zinc-900 dark:border-zinc-800">
                            <LayoutDashboard class="h-12 w-12 text-blue-600 dark:text-blue-500" />
                        </div>
                        <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl dark:text-white">
                            Sistem Informasi
                            <span class="block text-blue-600 dark:text-blue-500">Sekolah</span>
                        </h1>
                        <p class="mt-6 text-lg text-gray-600 sm:text-xl leading-relaxed max-w-md dark:text-gray-400">
                            Platform terpadu untuk manajemen sekolah yang lebih efisien, aman, dan modern.
                        </p>
                    </Motion>

                    <!-- Features List -->
                    <div class="mt-10 space-y-6 lg:mt-16">
                        <Motion
                            :initial="{ opacity: 0, x: -20 }"
                            :animate="{ opacity: 1, x: 0 }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.2 }"
                            class="flex items-start gap-4 group"
                        >
                            <div class="mt-1 shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm border border-gray-100 transition-transform group-hover:scale-110 dark:bg-zinc-900 dark:border-zinc-800">
                                    <LayoutDashboard class="h-5 w-5 text-blue-600 dark:text-blue-500" />
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 text-lg dark:text-white">Manajemen Terpadu</h3>
                                <p class="mt-1 text-sm text-gray-600 leading-relaxed dark:text-gray-400">Kelola data siswa, guru, dan akademik dalam satu dashboard yang intuitif.</p>
                            </div>
                        </Motion>

                        <Motion
                            :initial="{ opacity: 0, x: -20 }"
                            :animate="{ opacity: 1, x: 0 }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.25 }"
                            class="flex items-start gap-4 group"
                        >
                            <div class="mt-1 shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm border border-gray-100 transition-transform group-hover:scale-110 dark:bg-zinc-900 dark:border-zinc-800">
                                    <ShieldCheck class="h-5 w-5 text-blue-600 dark:text-blue-500" />
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 text-lg dark:text-white">Keamanan Terjamin</h3>
                                <p class="mt-1 text-sm text-gray-600 leading-relaxed dark:text-gray-400">Proteksi data tingkat tinggi dengan enkripsi modern dan backup berkala.</p>
                            </div>
                        </Motion>

                        <Motion
                            :initial="{ opacity: 0, x: -20 }"
                            :animate="{ opacity: 1, x: 0 }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.3 }"
                            class="flex items-start gap-4 group"
                        >
                            <div class="mt-1 shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm border border-gray-100 transition-transform group-hover:scale-110 dark:bg-zinc-900 dark:border-zinc-800">
                                    <Zap class="h-5 w-5 text-blue-600 dark:text-blue-500" />
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 text-lg dark:text-white">Performa Cepat</h3>
                                <p class="mt-1 text-sm text-gray-600 leading-relaxed dark:text-gray-400">Akses data real-time dengan antarmuka yang responsif di semua perangkat.</p>
                            </div>
                        </Motion>
                    </div>
                </div>
            </Motion>

            <!-- Right Side - Login Form (Desktop) / Full Screen (Mobile) -->
            <Motion
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.1 }"
                class="flex flex-1 items-center justify-center px-6 py-8 lg:px-16 lg:py-20 relative z-10 w-full"
            >
                <div class="w-full max-w-[420px]">
                    <!-- Mobile Logo (Visible only on mobile) -->
                    <div class="lg:hidden mb-12 text-center">
                        <div class="inline-flex items-center justify-center rounded-2xl bg-white p-3 shadow-sm border border-gray-100 mb-5 dark:bg-zinc-900 dark:border-zinc-800">
                            <LayoutDashboard class="h-10 w-10 text-blue-600 dark:text-blue-500" />
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">SIS Sekolah</h1>
                        <p class="mt-2 text-base text-gray-500 dark:text-gray-400">Selamat datang kembali</p>
                    </div>

                    <!-- Login Card (Transparent on Mobile, Card on Desktop) -->
                    <div
                        class="w-full lg:overflow-hidden lg:rounded-3xl lg:bg-white/95 lg:shadow-xl lg:border lg:border-gray-100 dark:lg:bg-zinc-900/95 dark:lg:border-zinc-800 dark:lg:shadow-none"
                    >
                        <!-- Card Header (Desktop Only) -->
                        <div class="relative px-8 pt-10 pb-6 text-center lg:block hidden">
                            <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Selamat Datang</h2>
                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Silakan masuk untuk mengakses akun Anda</p>
                        </div>

                        <!-- LOCKOUT ALERT -->
                        <div v-if="lockoutRemaining > 0" class="mx-6 lg:mx-8 mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 flex items-start gap-3">
                            <AlertOctagon class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                            <div>
                                <h3 class="text-sm font-bold text-red-800 dark:text-red-300">Akun Terkunci Sementara</h3>
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">
                                    Terlalu banyak percobaan login gagal. Silakan coba lagi dalam:
                                </p>
                                <p class="text-sm font-mono font-bold text-red-700 dark:text-red-300 mt-1.5">
                                    {{ formattedLockoutTime }}
                                </p>
                            </div>
                        </div>

                        <!-- Form Container -->
                        <form @submit.prevent="submit" class="lg:px-8 lg:pb-10 space-y-6">
                            <!-- Identifier Input (Username/Email) -->
                            <Motion
                                :initial="{ opacity: 0, y: 10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.3 }"
                            >
                                <div class="group">
                                    <label for="identifier" class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1 mb-1.5">
                                        Username atau Email
                                    </label>
                                    <div class="relative transition-all duration-200 focus-within:scale-[1.01]">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-blue-600 dark:group-focus-within:text-blue-500 transition-colors">
                                            <User class="h-5 w-5" />
                                        </div>
                                        <input
                                            id="identifier"
                                            v-model="form.identifier"
                                            type="text"
                                            required
                                            :disabled="lockoutRemaining > 0"
                                            autocomplete="username"
                                            class="block w-full rounded-xl border border-gray-200 bg-white lg:bg-gray-50/50 py-3.5 pl-11 pr-4 text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-zinc-700 dark:bg-zinc-900/50 lg:dark:bg-zinc-800/50 dark:text-white dark:placeholder:text-gray-500 dark:focus:bg-zinc-900 transition-all duration-200 shadow-sm lg:shadow-none disabled:opacity-60 disabled:cursor-not-allowed"
                                            placeholder="Masukkan username atau email"
                                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/10': form.errors.identifier }"
                                        />
                                    </div>
                                    <p v-if="form.errors.identifier" class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1.5 ml-1">
                                        <span class="inline-block h-1 w-1 rounded-full bg-red-600 dark:bg-red-400"></span>
                                        {{ form.errors.identifier }}
                                    </p>
                                </div>
                            </Motion>

                            <!-- Password Input -->
                            <Motion
                                :initial="{ opacity: 0, y: 10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.35 }"
                            >
                                <div class="group">
                                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1 mb-1.5">
                                        Password
                                    </label>
                                    <div class="relative transition-all duration-200 focus-within:scale-[1.01]">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-blue-600 dark:group-focus-within:text-blue-500 transition-colors">
                                            <Lock class="h-5 w-5" />
                                        </div>
                                        <input
                                            id="password"
                                            v-model="form.password"
                                            :type="showPassword ? 'text' : 'password'"
                                            required
                                            :disabled="lockoutRemaining > 0"
                                            autocomplete="current-password"
                                            class="block w-full rounded-xl border border-gray-200 bg-white lg:bg-gray-50/50 py-3.5 pl-11 pr-12 text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-zinc-700 dark:bg-zinc-900/50 lg:dark:bg-zinc-800/50 dark:text-white dark:placeholder:text-gray-500 dark:focus:bg-zinc-900 transition-all duration-200 shadow-sm lg:shadow-none disabled:opacity-60 disabled:cursor-not-allowed"
                                            placeholder="Masukkan password"
                                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/10': form.errors.password }"
                                        />
                                        <!-- Toggle Show/Hide Password -->
                                        <button
                                            type="button"
                                            @click="togglePasswordVisibility"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:hover:bg-zinc-800 dark:hover:text-gray-200"
                                            :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                        >
                                            <EyeOff v-if="!showPassword" class="h-5 w-5" />
                                            <Eye v-else class="h-5 w-5" />
                                        </button>
                                    </div>
                                    <p v-if="form.errors.password" class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1.5 ml-1">
                                        <span class="inline-block h-1 w-1 rounded-full bg-red-600 dark:bg-red-400"></span>
                                        {{ form.errors.password }}
                                    </p>
                                </div>
                            </Motion>

                            <!-- Remember Me & Forgot Password -->
                            <Motion
                                :initial="{ opacity: 0, y: 10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.4 }"
                                class="flex items-center justify-between"
                            >
                                <label class="flex items-center cursor-pointer group">
                                    <div class="relative flex items-center">
                                        <input
                                            v-model="form.remember"
                                            type="checkbox"
                                            @change="handleRememberChange"
                                            class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border-2 border-gray-300 bg-white transition-all checked:border-blue-600 checked:bg-blue-600 hover:border-blue-400 focus:ring-4 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-800 lg:dark:bg-zinc-700 dark:checked:border-blue-500 dark:checked:bg-blue-500"
                                        />
                                        <svg
                                            class="pointer-events-none absolute left-1/2 top-1/2 h-3.5 w-3.5 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 transition-opacity peer-checked:opacity-100"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="3"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="ml-2.5 text-sm font-medium text-gray-600 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-gray-200 transition-colors">Ingat saya</span>
                                </label>
                                <Link
                                    :href="passwordRequest()"
                                    class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                                >
                                    Lupa password?
                                </Link>
                            </Motion>

                            <!-- Submit Button -->
                            <Motion
                                :initial="{ opacity: 0, y: 10 }"
                                :animate="{ opacity: 1, y: 0 }"
                                :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.45 }"
                                :whileTap="{ scale: 0.97 }"
                                :transition-tap="{ type: 'spring', stiffness: 300, damping: 30 }"
                            >
                                <button
                                    type="submit"
                                    :disabled="isSubmitting || lockoutRemaining > 0"
                                    class="relative w-full overflow-hidden rounded-xl bg-blue-600 px-6 py-4 font-bold text-white shadow-lg shadow-blue-500/25 transition-all duration-300 hover:bg-blue-700 hover:shadow-blue-500/40 focus:outline-none focus:ring-4 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:shadow-none"
                                >
                                    <div class="relative flex items-center justify-center gap-2">
                                        <Loader2 v-if="isSubmitting" class="h-5 w-5 animate-spin" />
                                        <LogIn v-else class="h-5 w-5" />
                                        <span>
                                            {{ isSubmitting ? 'Memproses...' : (lockoutRemaining > 0 ? `Tunggu ${lockoutRemaining}s` : 'Masuk Sekarang') }}
                                        </span>
                                    </div>
                                </button>
                            </Motion>
                        </form>

                        <!-- Footer -->
                        <div class="lg:bg-gray-50/50 lg:px-8 lg:py-5 mt-8 lg:mt-0 text-center dark:lg:bg-zinc-800/30 lg:border-t lg:border-gray-100 dark:lg:border-zinc-800/50">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                &copy; {{ new Date().getFullYear() }} Sistem Informasi Sekolah. <br class="sm:hidden" />Hak Cipta Dilindungi.
                            </p>
                        </div>
                    </div>
                </div>
            </Motion>
        </div>
    </div>
</template>
