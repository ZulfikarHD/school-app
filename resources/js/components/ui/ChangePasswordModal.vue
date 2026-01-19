<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from './BaseModal.vue';
import PasswordStrengthMeter from './PasswordStrengthMeter.vue';
import { useHaptics } from '@/composables/useHaptics';
import { Lock, Eye, EyeOff } from 'lucide-vue-next';
import { update as profilePasswordUpdate } from '@/routes/profile/password';

interface Props {
    show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['close', 'success']);

const haptics = useHaptics();
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    haptics.medium();
    form.post(profilePasswordUpdate().url, {
        preserveScroll: true,
        onSuccess: () => {
            haptics.success();
            form.reset();
            emit('success');
            emit('close');
        },
        onError: () => {
            haptics.error();
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
            }
            if (form.errors.current_password) {
                form.reset('current_password');
            }
        },
    });
};

const close = () => {
    haptics.light();
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <BaseModal
        :show="show"
        size="md"
        :show-close-button="true"
        @close="close"
    >
        <!-- Custom Header dengan subtitle -->
        <template #header>
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                    Ganti Password
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Pastikan akun Anda aman dengan password yang kuat
                </p>
            </div>
        </template>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Current Password -->
            <div>
                <label for="current-password" class="block text-[11px] font-semibold tracking-wide uppercase text-slate-600 dark:text-slate-400 mb-2">
                    Password Saat Ini <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <Lock class="h-[18px] w-[18px] text-slate-400" />
                    </div>
                    <input
                        id="current-password"
                        v-model="form.current_password"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        ref="currentPasswordInput"
                        autocomplete="current-password"
                        class="block w-full h-[52px] pl-11 pr-12 rounded-xl border bg-slate-50/80 dark:bg-zinc-900/80 text-slate-900 dark:text-slate-100 text-[15px] transition-all duration-200 focus:outline-none focus:bg-white dark:focus:bg-zinc-900 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20"
                        :class="form.errors.current_password 
                            ? 'border-red-400 ring-2 ring-red-400/20 bg-red-50/30 dark:bg-red-950/10' 
                            : 'border-slate-200 dark:border-zinc-800 hover:border-slate-300 dark:hover:border-zinc-700'"
                        placeholder="Masukkan password lama"
                    />
                    <button 
                        type="button" 
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors" 
                        @click="showCurrentPassword = !showCurrentPassword"
                        :aria-label="showCurrentPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                    >
                        <component :is="showCurrentPassword ? EyeOff : Eye" class="h-5 w-5" />
                    </button>
                </div>
                <p v-if="form.errors.current_password" class="mt-1.5 text-xs text-red-500 dark:text-red-400 flex items-center gap-1.5 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
                    </svg>
                    {{ form.errors.current_password }}
                </p>
            </div>

            <!-- New Password -->
            <div>
                <label for="new-password" class="block text-[11px] font-semibold tracking-wide uppercase text-slate-600 dark:text-slate-400 mb-2">
                    Password Baru <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <Lock class="h-[18px] w-[18px] text-slate-400" />
                    </div>
                    <input
                        id="new-password"
                        v-model="form.password"
                        :type="showNewPassword ? 'text' : 'password'"
                        autocomplete="new-password"
                        class="block w-full h-[52px] pl-11 pr-12 rounded-xl border bg-slate-50/80 dark:bg-zinc-900/80 text-slate-900 dark:text-slate-100 text-[15px] transition-all duration-200 focus:outline-none focus:bg-white dark:focus:bg-zinc-900 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20"
                        :class="form.errors.password 
                            ? 'border-red-400 ring-2 ring-red-400/20 bg-red-50/30 dark:bg-red-950/10' 
                            : 'border-slate-200 dark:border-zinc-800 hover:border-slate-300 dark:hover:border-zinc-700'"
                        placeholder="Minimal 8 karakter"
                    />
                    <button 
                        type="button" 
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors" 
                        @click="showNewPassword = !showNewPassword"
                        :aria-label="showNewPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                    >
                        <component :is="showNewPassword ? EyeOff : Eye" class="h-5 w-5" />
                    </button>
                </div>
                <PasswordStrengthMeter :password="form.password" class="mt-3" />
                <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500 dark:text-red-400 flex items-center gap-1.5 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
                    </svg>
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="confirm-password" class="block text-[11px] font-semibold tracking-wide uppercase text-slate-600 dark:text-slate-400 mb-2">
                    Konfirmasi Password Baru <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <Lock class="h-[18px] w-[18px] text-slate-400" />
                    </div>
                    <input
                        id="confirm-password"
                        v-model="form.password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        autocomplete="new-password"
                        class="block w-full h-[52px] pl-11 pr-12 rounded-xl border bg-slate-50/80 dark:bg-zinc-900/80 text-slate-900 dark:text-slate-100 text-[15px] transition-all duration-200 focus:outline-none focus:bg-white dark:focus:bg-zinc-900 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20"
                        :class="form.errors.password_confirmation 
                            ? 'border-red-400 ring-2 ring-red-400/20 bg-red-50/30 dark:bg-red-950/10' 
                            : 'border-slate-200 dark:border-zinc-800 hover:border-slate-300 dark:hover:border-zinc-700'"
                        placeholder="Ulangi password baru"
                    />
                    <button 
                        type="button" 
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors" 
                        @click="showConfirmPassword = !showConfirmPassword"
                        :aria-label="showConfirmPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                    >
                        <component :is="showConfirmPassword ? EyeOff : Eye" class="h-5 w-5" />
                    </button>
                </div>
                <!-- Password Match Indicator -->
                <p 
                    v-if="form.password_confirmation && form.password" 
                    class="mt-1.5 text-xs flex items-center gap-1.5 font-medium"
                    :class="form.password === form.password_confirmation 
                        ? 'text-emerald-600 dark:text-emerald-400' 
                        : 'text-amber-600 dark:text-amber-400'"
                >
                    <svg v-if="form.password === form.password_confirmation" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/>
                    </svg>
                    {{ form.password === form.password_confirmation ? 'Password cocok' : 'Password belum cocok' }}
                </p>
                <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-red-500 dark:text-red-400 flex items-center gap-1.5 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
                    </svg>
                    {{ form.errors.password_confirmation }}
                </p>
            </div>
        </form>

        <!-- Footer Actions -->
        <template #footer>
            <div class="flex items-center justify-end gap-3">
                <button
                    type="button"
                    @click="close"
                    class="px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-all duration-200 active:scale-95"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    :disabled="form.processing"
                    @click="submit"
                    class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl shadow-sm shadow-emerald-500/25 hover:shadow-md hover:shadow-emerald-500/30 transition-all duration-200 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <Lock v-else class="w-4 h-4" />
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Password' }}
                </button>
            </div>
        </template>
    </BaseModal>
</template>

