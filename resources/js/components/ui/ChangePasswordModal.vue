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
        title="Ganti Password"
        size="md"
        @close="close"
    >
        <div class="px-6 pb-6 pt-2">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                Ganti Password
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                Pastikan akun Anda aman dengan menggunakan password yang kuat.
            </p>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Current Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Password Saat Ini
                    </label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Lock class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            v-model="form.current_password"
                            :type="showCurrentPassword ? 'text' : 'password'"
                            ref="currentPasswordInput"
                            class="block w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all sm:text-sm"
                            :class="{ 'border-red-500': form.errors.current_password }"
                            placeholder="Masukkan password lama"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" @click="showCurrentPassword = !showCurrentPassword">
                            <component :is="showCurrentPassword ? EyeOff : Eye" class="h-5 w-5 text-gray-400" />
                        </div>
                    </div>
                    <p v-if="form.errors.current_password" class="mt-1 text-xs text-red-500">
                        {{ form.errors.current_password }}
                    </p>
                </div>

                <!-- New Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Password Baru
                    </label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Lock class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            v-model="form.password"
                            :type="showNewPassword ? 'text' : 'password'"
                            class="block w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all sm:text-sm"
                            :class="{ 'border-red-500': form.errors.password }"
                            placeholder="Minimal 8 karakter"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" @click="showNewPassword = !showNewPassword">
                            <component :is="showNewPassword ? EyeOff : Eye" class="h-5 w-5 text-gray-400" />
                        </div>
                    </div>
                    <PasswordStrengthMeter :password="form.password" class="mt-2" />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Konfirmasi Password Baru
                    </label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Lock class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            v-model="form.password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            class="block w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all sm:text-sm"
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

                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-zinc-800">
                    <button
                        type="button"
                        @click="close"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-xl transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </BaseModal>
</template>

