<script setup lang="ts">
import { ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/components/layouts/AppLayout.vue';
import ChangePasswordModal from '@/components/ui/ChangePasswordModal.vue';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import { User, Mail, Shield, Lock, Phone } from 'lucide-vue-next';
import { Motion } from 'motion-v';

const page = usePage();
const user = page.props.auth.user;
const haptics = useHaptics();
const modal = useModal();

const showChangePasswordModal = ref(false);

const openChangePasswordModal = () => {
    haptics.medium();
    showChangePasswordModal.value = true;
};

const handlePasswordChanged = () => {
    modal.success('Password berhasil diubah.');
};
</script>

<template>
    <Head title="Profil Saya" />

    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <Motion 
                :initial="{ opacity: 0, y: -20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
            >
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <User class="w-8 h-8 text-blue-600 dark:text-blue-500" />
                    Profil Saya
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola informasi akun dan keamanan Anda.
                </p>
            </Motion>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Profile Card -->
                <Motion 
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.1 }"
                    class="md:col-span-2 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6 md:p-8"
                >
                    <div class="flex items-center gap-4 mb-8">
                        <div class="h-20 w-20 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 text-3xl font-bold">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ user.name }}</h2>
                            <p class="text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 mt-2">
                                {{ user.role }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Nama Lengkap
                            </label>
                            <div class="flex items-center gap-3 text-gray-900 dark:text-white font-medium p-3 bg-gray-50 dark:bg-zinc-800/50 rounded-xl border border-gray-100 dark:border-zinc-800">
                                <User class="w-5 h-5 text-gray-400" />
                                {{ user.name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Email Address
                            </label>
                            <div class="flex items-center gap-3 text-gray-900 dark:text-white font-medium p-3 bg-gray-50 dark:bg-zinc-800/50 rounded-xl border border-gray-100 dark:border-zinc-800">
                                <Mail class="w-5 h-5 text-gray-400" />
                                {{ user.email }}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    Username
                                </label>
                                <div class="flex items-center gap-3 text-gray-900 dark:text-white font-medium p-3 bg-gray-50 dark:bg-zinc-800/50 rounded-xl border border-gray-100 dark:border-zinc-800">
                                    <Shield class="w-5 h-5 text-gray-400" />
                                    {{ user.username }}
                                </div>
                            </div>
                            
                            <div v-if="user.phone_number">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    No. Handphone
                                </label>
                                <div class="flex items-center gap-3 text-gray-900 dark:text-white font-medium p-3 bg-gray-50 dark:bg-zinc-800/50 rounded-xl border border-gray-100 dark:border-zinc-800">
                                    <Phone class="w-5 h-5 text-gray-400" />
                                    {{ user.phone_number }}
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <!-- Security Card -->
                <Motion 
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ type: 'spring', stiffness: 300, damping: 30, delay: 0.2 }"
                    class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6 md:p-8 h-fit"
                >
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <Lock class="w-5 h-5 text-blue-600 dark:text-blue-500" />
                        Keamanan
                    </h3>
                    
                    <div class="space-y-6">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Amankan akun Anda dengan mengganti password secara berkala.
                        </p>

                        <button 
                            @click="openChangePasswordModal"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-200 font-medium rounded-xl transition-all shadow-sm hover:shadow-md active:scale-95"
                        >
                            <Lock class="w-4 h-4" />
                            Ganti Password
                        </button>

                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800/50">
                            <h4 class="text-xs font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider mb-2">
                                Info Login Terakhir
                            </h4>
                             <!-- This would ideally come from backend logs -->
                            <p class="text-sm text-blue-600 dark:text-blue-400">
                                Saat ini Anda sedang login.
                            </p>
                        </div>
                    </div>
                </Motion>
            </div>
        </div>

        <ChangePasswordModal 
            :show="showChangePasswordModal" 
            @close="showChangePasswordModal = false" 
            @success="handlePasswordChanged" 
        />
    </AppLayout>
</template>

