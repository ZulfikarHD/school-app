<script setup lang="ts">
import { computed } from 'vue';
import { InertiaForm } from '@inertiajs/vue3';
import { Eye, EyeOff, User, Mail, Phone, Shield, Lock, AlertCircle } from 'lucide-vue-next';

interface Props {
    form: any; // Using any for InertiaForm generic type flexibility
    isEdit?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isEdit: false,
});

const roles = [
    { value: 'SUPERADMIN', label: 'Super Admin' },
    { value: 'ADMIN', label: 'Admin TU' },
    { value: 'PRINCIPAL', label: 'Kepala Sekolah' },
    { value: 'TEACHER', label: 'Guru' },
    { value: 'PARENT', label: 'Orang Tua' },
    { value: 'STUDENT', label: 'Siswa' },
];

const generatedPassword = computed(() => {
    if (props.isEdit || !props.form.name) return '';
    const firstName = props.form.name.split(' ')[0];
    return `${firstName}xxxx`;
});
</script>

<template>
    <div class="space-y-6">
        <!-- Nama Lengkap -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <User class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input 
                    v-model="form.name"
                    type="text" 
                    class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                    :class="{ 'border-red-500 focus:border-red-500': form.errors.name }"
                    placeholder="Masukkan nama lengkap"
                >
            </div>
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500 flex items-center gap-1">
                <AlertCircle class="w-3 h-3" /> {{ form.errors.name }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Email <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input 
                        v-model="form.email"
                        type="email" 
                        class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                        :class="{ 'border-red-500 focus:border-red-500': form.errors.email }"
                        placeholder="contoh@sekolah.id"
                    >
                </div>
                <p v-if="form.errors.email" class="mt-1 text-xs text-red-500 flex items-center gap-1">
                    <AlertCircle class="w-3 h-3" /> {{ form.errors.email }}
                </p>
            </div>

            <!-- Username -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Username <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <Shield class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input 
                        v-model="form.username"
                        type="text" 
                        class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                        :class="{ 'border-red-500 focus:border-red-500': form.errors.username }"
                        placeholder="username.unik"
                    >
                </div>
                <p v-if="form.errors.username" class="mt-1 text-xs text-red-500 flex items-center gap-1">
                    <AlertCircle class="w-3 h-3" /> {{ form.errors.username }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- No HP -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    No. Handphone
                </label>
                <div class="relative">
                    <Phone class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input 
                        v-model="form.phone_number"
                        type="text" 
                        class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                        :class="{ 'border-red-500 focus:border-red-500': form.errors.phone_number }"
                        placeholder="08123456789"
                    >
                </div>
                <p v-if="form.errors.phone_number" class="mt-1 text-xs text-red-500 flex items-center gap-1">
                    <AlertCircle class="w-3 h-3" /> {{ form.errors.phone_number }}
                </p>
            </div>

            <!-- Role -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Role User <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select 
                        v-model="form.role"
                        class="w-full pl-4 pr-10 py-2 rounded-xl border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none"
                        :class="{ 'border-red-500 focus:border-red-500': form.errors.role }"
                    >
                        <option value="" disabled>Pilih Role</option>
                        <option v-for="role in roles" :key="role.value" :value="role.value">
                            {{ role.label }}
                        </option>
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                <p v-if="form.errors.role" class="mt-1 text-xs text-red-500 flex items-center gap-1">
                    <AlertCircle class="w-3 h-3" /> {{ form.errors.role }}
                </p>
                <p v-if="isEdit" class="mt-1 text-xs text-yellow-600 dark:text-yellow-500 flex items-center gap-1">
                    <AlertCircle class="w-3 h-3" /> Mengubah role akan mengakhiri sesi user tersebut.
                </p>
            </div>
        </div>

        <!-- Status & Password Info -->
        <div class="p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-xl border border-gray-100 dark:border-zinc-800 space-y-4">
            <!-- Status Toggle -->
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                        Status Akun
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        User dengan status nonaktif tidak dapat login.
                    </p>
                </div>
                <button 
                    type="button"
                    @click="form.status = form.status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    :class="form.status === 'ACTIVE' ? 'bg-blue-600' : 'bg-gray-200 dark:bg-zinc-700'"
                >
                    <span 
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        :class="form.status === 'ACTIVE' ? 'translate-x-5' : 'translate-x-0'"
                    ></span>
                </button>
            </div>

            <!-- Password Info (Create Only) -->
            <div v-if="!isEdit" class="pt-4 border-t border-gray-200 dark:border-zinc-700">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 rounded-lg shrink-0">
                        <Lock class="w-5 h-5" />
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">
                            Password Default Otomatis
                        </h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-2">
                            Password akan digenerate otomatis dengan format: 
                            <code class="px-1 py-0.5 bg-gray-100 dark:bg-zinc-700 rounded text-blue-600 dark:text-blue-400 font-mono">NamaDepan + 4 digit angka</code>.
                            Contoh: <span class="font-medium text-gray-700 dark:text-gray-300">{{ generatedPassword || 'Siti1234' }}</span>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            User akan menerima email berisi kredensial login mereka.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

