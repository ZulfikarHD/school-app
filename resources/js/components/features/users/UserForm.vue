<script setup lang="ts">
import { computed } from 'vue';
import { User, Mail, Phone, Shield, Lock, AlertCircle } from 'lucide-vue-next';

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
        <!-- Nama Lengkap dengan proper accessibility -->
        <div>
            <label for="user-name" class="block text-[11px] font-semibold tracking-wide uppercase text-slate-600 dark:text-slate-400 mb-1.5 pl-1">
                Nama Lengkap <span class="text-red-400">*</span>
            </label>
            <div class="relative">
                <User class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" aria-hidden="true" />
                <input 
                    id="user-name"
                    v-model="form.name"
                    type="text" 
                    class="w-full h-[52px] pl-10 pr-4 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900 transition-all text-slate-900 dark:text-slate-100 placeholder-slate-400"
                    :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-500/20': form.errors.name }"
                    :aria-invalid="!!form.errors.name"
                    :aria-describedby="form.errors.name ? 'name-error' : undefined"
                    placeholder="Masukkan nama lengkap"
                    required
                >
            </div>
            <p v-if="form.errors.name" id="name-error" class="mt-1.5 text-xs text-red-500 flex items-center gap-1 pl-1" role="alert">
                <AlertCircle class="w-3 h-3" aria-hidden="true" /> {{ form.errors.name }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Email dengan proper accessibility -->
            <div>
                <label for="user-email" class="block text-[11px] font-semibold tracking-wide uppercase text-slate-600 dark:text-slate-400 mb-1.5 pl-1">
                    Email <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" aria-hidden="true" />
                    <input 
                        id="user-email"
                        v-model="form.email"
                        type="email" 
                        class="w-full h-[52px] pl-10 pr-4 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900 transition-all text-slate-900 dark:text-slate-100 placeholder-slate-400"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-500/20': form.errors.email }"
                        :aria-invalid="!!form.errors.email"
                        :aria-describedby="form.errors.email ? 'email-error' : undefined"
                        placeholder="contoh@sekolah.id"
                        required
                    >
                </div>
                <p v-if="form.errors.email" id="email-error" class="mt-1.5 text-xs text-red-500 flex items-center gap-1 pl-1" role="alert">
                    <AlertCircle class="w-3 h-3" aria-hidden="true" /> {{ form.errors.email }}
                </p>
            </div>

            <!-- Username dengan proper accessibility -->
            <div>
                <label for="user-username" class="block text-[11px] font-semibold tracking-wide uppercase text-slate-600 dark:text-slate-400 mb-1.5 pl-1">
                    Username <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <Shield class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" aria-hidden="true" />
                    <input 
                        id="user-username"
                        v-model="form.username"
                        type="text" 
                        class="w-full h-[52px] pl-10 pr-4 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900 transition-all text-slate-900 dark:text-slate-100 placeholder-slate-400"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-500/20': form.errors.username }"
                        :aria-invalid="!!form.errors.username"
                        :aria-describedby="form.errors.username ? 'username-error' : undefined"
                        placeholder="username.unik"
                        required
                    >
                </div>
                <p v-if="form.errors.username" id="username-error" class="mt-1.5 text-xs text-red-500 flex items-center gap-1 pl-1" role="alert">
                    <AlertCircle class="w-3 h-3" aria-hidden="true" /> {{ form.errors.username }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- No HP dengan proper accessibility -->
            <div>
                <label for="user-phone" class="block text-[11px] font-semibold tracking-wide uppercase text-slate-600 dark:text-slate-400 mb-1.5 pl-1">
                    No. Handphone
                </label>
                <div class="relative">
                    <Phone class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" aria-hidden="true" />
                    <input 
                        id="user-phone"
                        v-model="form.phone_number"
                        type="tel" 
                        class="w-full h-[52px] pl-10 pr-4 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900 transition-all text-slate-900 dark:text-slate-100 placeholder-slate-400"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-500/20': form.errors.phone_number }"
                        :aria-invalid="!!form.errors.phone_number"
                        :aria-describedby="form.errors.phone_number ? 'phone-error' : undefined"
                        placeholder="08123456789"
                    >
                </div>
                <p v-if="form.errors.phone_number" id="phone-error" class="mt-1.5 text-xs text-red-500 flex items-center gap-1 pl-1" role="alert">
                    <AlertCircle class="w-3 h-3" aria-hidden="true" /> {{ form.errors.phone_number }}
                </p>
            </div>

            <!-- Role dengan proper accessibility -->
            <div>
                <label for="user-role" class="block text-[11px] font-semibold tracking-wide uppercase text-slate-600 dark:text-slate-400 mb-1.5 pl-1">
                    Role User <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <select 
                        id="user-role"
                        v-model="form.role"
                        class="w-full h-[52px] pl-4 pr-10 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white dark:focus:bg-zinc-900 transition-all appearance-none text-slate-900 dark:text-slate-100"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-500/20': form.errors.role }"
                        :aria-invalid="!!form.errors.role"
                        :aria-describedby="form.errors.role ? 'role-error' : isEdit ? 'role-warning' : undefined"
                        required
                    >
                        <option value="" disabled>Pilih Role</option>
                        <option v-for="role in roles" :key="role.value" :value="role.value">
                            {{ role.label }}
                        </option>
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                <p v-if="form.errors.role" id="role-error" class="mt-1.5 text-xs text-red-500 flex items-center gap-1 pl-1" role="alert">
                    <AlertCircle class="w-3 h-3" aria-hidden="true" /> {{ form.errors.role }}
                </p>
                <p v-if="isEdit" id="role-warning" class="mt-1.5 text-xs text-amber-600 dark:text-amber-500 flex items-center gap-1 pl-1">
                    <AlertCircle class="w-3 h-3" aria-hidden="true" /> Mengubah role akan mengakhiri sesi user tersebut.
                </p>
            </div>
        </div>

        <!-- Status & Password Info -->
        <div class="p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-xl border border-slate-200 dark:border-zinc-800 space-y-4">
            <!-- Status Toggle dengan proper accessibility -->
            <div class="flex items-center justify-between">
                <div>
                    <label id="status-label" class="block text-sm font-medium text-slate-900 dark:text-slate-100">
                        Status Akun
                    </label>
                    <p id="status-description" class="text-xs text-slate-500 dark:text-slate-400">
                        User dengan status nonaktif tidak dapat login.
                    </p>
                </div>
                <button 
                    type="button"
                    role="switch"
                    :aria-checked="form.status === 'ACTIVE'"
                    aria-labelledby="status-label"
                    aria-describedby="status-description"
                    @click="form.status = form.status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE'"
                    class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                    :class="form.status === 'ACTIVE' ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-zinc-700'"
                >
                    <span 
                        class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                        :class="form.status === 'ACTIVE' ? 'translate-x-5' : 'translate-x-0'"
                        aria-hidden="true"
                    ></span>
                </button>
            </div>

            <!-- Password Info (Create Only) -->
            <div v-if="!isEdit" class="pt-4 border-t border-slate-200 dark:border-zinc-700">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-sky-100 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 rounded-xl shrink-0">
                        <Lock class="w-5 h-5" aria-hidden="true" />
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-slate-900 dark:text-slate-100 mb-1">
                            Password Default Otomatis
                        </h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-2">
                            Password akan digenerate otomatis dengan format: 
                            <code class="px-1.5 py-0.5 bg-slate-100 dark:bg-zinc-700 rounded text-sky-600 dark:text-sky-400 font-mono text-[11px]">NamaDepan + 4 digit angka</code>.
                            Contoh: <span class="font-medium text-slate-700 dark:text-slate-300">{{ generatedPassword || 'Siti1234' }}</span>
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            User akan menerima email berisi kredensial login mereka.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

