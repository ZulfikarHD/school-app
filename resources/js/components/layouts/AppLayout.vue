<script setup lang="ts">
import { computed, ref } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import { useSessionTimeout } from '@/composables/useSessionTimeout';
import { dashboard, logout as logoutRoute } from '@/routes';
import { index as adminUsersIndex } from '@/routes/admin/users';
import { index as adminStudentsIndex } from '@/routes/admin/students';
import { index as adminAuditLogsIndex } from '@/routes/admin/audit-logs';
import { index as auditLogsIndex } from '@/routes/audit-logs';
import { show as profileShow } from '@/routes/profile';
import { index as parentChildrenIndex } from '@/routes/parent/children';
import { index as parentLeaveRequestsIndex } from '@/routes/parent/leave-requests';
import { index as adminAttendanceStudentsIndex } from '@/routes/admin/attendance/students';
import { index as adminAttendanceTeachersIndex } from '@/routes/admin/attendance/teachers';
import { index as principalTeacherLeavesIndex } from '@/routes/principal/teacher-leaves';
import { index as teacherAttendanceIndex } from '@/routes/teacher/attendance';
import { create as teacherAttendanceSubjectCreate, index as teacherAttendanceSubjectIndex } from '@/routes/teacher/attendance/subject';
import { myAttendance as teacherMyAttendance } from '@/routes/teacher';
import { index as teacherLeaveRequestsIndex } from '@/routes/teacher/leave-requests';
import { index as teacherTeacherLeavesIndex } from '@/routes/teacher/teacher-leaves';
import DialogModal from '@/components/ui/DialogModal.vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import Alert from '@/components/ui/Alert.vue';
import SessionTimeoutModal from '@/components/ui/SessionTimeoutModal.vue';
import {
    Home,
    Users,
    Activity,
    FileText,
    ClipboardList,
    LogOut,
    User,
    Settings,
    Bell,
    Search,
    ChevronRight,
    GraduationCap,
    CalendarCheck,
    Clock,
    BookOpen,
    History
} from 'lucide-vue-next';

/**
 * Main application layout dengan Dual-Navigation Strategy
 * - Desktop: Persistent Sidebar + Top Glass Header
 * - Mobile: Bottom Tab Bar + Top Glass Header
 * Mengikuti iOS-like Design Standard (Performance First)
 */

const page = usePage();
const user = computed(() => page.props.auth?.user);
const haptics = useHaptics();
const modal = useModal();
const showProfileMenu = ref(false);

// Session Timeout Logic
// Admin: 15 mins, Others: 30 mins
const timeoutDuration = computed(() =>
    (user.value?.role === 'SUPERADMIN' || user.value?.role === 'ADMIN') ? 15 : 30
);
const { showWarning, remainingSeconds, extendSession, logout: sessionLogout } = useSessionTimeout(timeoutDuration.value);

// Destructure modal states for template usage
const { dialogState, alertState } = modal;

/**
 * Helper function untuk generate route URL menggunakan Wayfinder
 */
const getRouteUrl = (routeName: string): string => {
    const routeMap: Record<string, string> = {
        'dashboard': dashboard().url,
        'admin.users.index': adminUsersIndex().url,
        'admin.students.index': adminStudentsIndex().url,
        'admin.attendance.students.index': adminAttendanceStudentsIndex().url,
        'admin.attendance.teachers.index': adminAttendanceTeachersIndex().url,
        'admin.audit-logs.index': adminAuditLogsIndex().url,
        'audit-logs.index': auditLogsIndex().url,
        'profile.show': profileShow().url,
        'principal.teacher-leaves.index': principalTeacherLeavesIndex().url,
        'teacher.attendance.index': teacherAttendanceIndex().url,
        'teacher.attendance.subject.create': teacherAttendanceSubjectCreate().url,
        'teacher.attendance.subject.index': teacherAttendanceSubjectIndex().url,
        'teacher.my-attendance': teacherMyAttendance().url,
        'teacher.leave-requests': teacherLeaveRequestsIndex().url,
        'teacher.teacher-leaves.index': teacherTeacherLeavesIndex().url,
        'parent.children': parentChildrenIndex().url,
        'parent.leave-requests': parentLeaveRequestsIndex().url,
    };

    return routeMap[routeName] || '#';
};

/**
 * Handle logout using Reusable Dialog Component
 */
const logout = async () => {
    showProfileMenu.value = false; // Close menu first
    haptics.medium();

    const confirmed = await modal.confirm(
        'Konfirmasi Keluar',
        'Apakah Anda yakin ingin keluar dari sesi ini?',
        'Ya, Keluar',
        'Batal'
    );

    if (confirmed) {
        haptics.heavy();
        router.post(logoutRoute().url);
    }
};

/**
 * Toggle Profile Menu (Mobile Sheet)
 */
const toggleProfileMenu = () => {
    haptics.light();
    showProfileMenu.value = !showProfileMenu.value;
};

/**
 * Navigation Click Feedback
 */
const handleNavClick = () => {
    haptics.light();
};

/**
 * Check active route
 */
const isActive = (route: string) => {
    const routeUrl = getRouteUrl(route);
    const currentUrl = page.url.split('?')[0]; // Remove query params

    // For exact matches or specific sub-routes
    if (route === 'teacher.attendance.index') {
        // Match /teacher/attendance exactly or /teacher/attendance/daily
        return currentUrl === '/teacher/attendance' || currentUrl.startsWith('/teacher/attendance/daily');
    }

    if (route === 'teacher.attendance.subject.create') {
        // Match /teacher/attendance/subject exactly (not history)
        return currentUrl === '/teacher/attendance/subject';
    }

    if (route === 'teacher.attendance.subject.index') {
        // Match /teacher/attendance/subject/history
        return currentUrl.startsWith('/teacher/attendance/subject/history');
    }

    // Default: starts with
    return currentUrl.startsWith(routeUrl);
};

/**
 * Menu Items dengan Icon Components
 */
const menuItems = computed(() => {
    const role = user.value?.role;

    const commonItems = [
        { name: 'Dashboard', route: 'dashboard', icon: Home },
    ];

    if (role === 'SUPERADMIN' || role === 'ADMIN') {
        return [
            ...commonItems,
            { name: 'Manajemen User', route: 'admin.users.index', icon: Users },
            { name: 'Data Siswa', route: 'admin.students.index', icon: GraduationCap },
            { name: 'Absensi Siswa', route: 'admin.attendance.students.index', icon: CalendarCheck },
            { name: 'Presensi Guru', route: 'admin.attendance.teachers.index', icon: Clock },
            { name: 'Audit Log', route: 'admin.audit-logs.index', icon: Activity },
        ];
    }

    if (role === 'PRINCIPAL') {
        return [
            ...commonItems,
            { name: 'Izin Guru', route: 'principal.teacher-leaves.index', icon: ClipboardList },
            { name: 'Audit Log', route: 'audit-logs.index', icon: Activity },
        ];
    }

    if (role === 'TEACHER') {
        return [
            ...commonItems,
            { name: 'Presensi Siswa', route: 'teacher.attendance.index', icon: CalendarCheck },
            { name: 'Presensi Mapel', route: 'teacher.attendance.subject.create', icon: BookOpen },
            { name: 'Riwayat Presensi Mapel', route: 'teacher.attendance.subject.index', icon: History },
            { name: 'Riwayat Presensi Saya', route: 'teacher.my-attendance', icon: History },
            { name: 'Verifikasi Izin', route: 'teacher.leave-requests', icon: FileText },
            { name: 'Izin Saya', route: 'teacher.teacher-leaves.index', icon: ClipboardList },
        ];
    }

    if (role === 'PARENT') {
        return [
            ...commonItems,
            { name: 'Data Anak', route: 'parent.children', icon: Users },
            { name: 'Pengajuan Izin', route: 'parent.leave-requests', icon: FileText },
        ];
    }

    return commonItems;
});

// Format Date for Header
const currentDate = new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
}).format(new Date());

</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950 flex">
        <!-- GLOBAL MODALS & ALERTS -->
        <DialogModal
            :show="dialogState.show"
            v-bind="dialogState.options"
            @confirm="dialogState.onConfirm"
            @cancel="dialogState.onCancel"
            @close="modal.closeDialog"
        />
        <Alert
            :show="alertState.show"
            v-bind="alertState.options"
            @close="modal.closeAlert"
        />

        <!-- SESSION TIMEOUT WARNING -->
        <SessionTimeoutModal
            :show="showWarning"
            :remaining-seconds="remainingSeconds"
            @extend="extendSession"
            @logout="sessionLogout"
        />

        <!-- MOBILE PROFILE ACTION SHEET (Bottom Sheet) -->
        <BaseModal
            :show="showProfileMenu"
            size="full"
            :show-close-button="false"
            @close="showProfileMenu = false"
            class="p-0! m-0!"
        >
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-4 p-4 mb-2 bg-gray-50 rounded-2xl dark:bg-zinc-800/50">
                    <div class="w-12 h-12 rounded-full bg-linear-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                        {{ user?.name?.charAt(0) || 'U' }}
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">{{ user?.name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ user?.email }}</p>
                        <span class="text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-0.5 rounded mt-1 inline-block">
                            {{ user?.role }}
                        </span>
                    </div>
                </div>

                <Link :href="getRouteUrl('profile.show')" @click="showProfileMenu = false" class="w-full text-left px-4 py-3 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 flex items-center justify-between group">
                    <span class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                        <User class="w-5 h-5 text-gray-400" />
                        Profil Saya
                    </span>
                    <ChevronRight class="w-4 h-4 text-gray-400" />
                </Link>

                <button @click="showProfileMenu = false" class="w-full text-left px-4 py-3 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 flex items-center justify-between group">
                    <span class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                        <Settings class="w-5 h-5 text-gray-400" />
                        Pengaturan
                    </span>
                    <ChevronRight class="w-4 h-4 text-gray-400" />
                </button>

                 <div class="h-px bg-gray-100 dark:bg-zinc-800 my-1"></div>

                <button @click="logout" class="w-full text-left px-4 py-3 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-3 text-red-600">
                    <LogOut class="w-5 h-5" />
                    Keluar Aplikasi
                </button>

                <button @click="showProfileMenu = false" class="mt-2 w-full py-3 rounded-xl bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-gray-400 font-semibold text-sm">
                    Tutup
                </button>
            </div>
        </BaseModal>

        <!-- DESKTOP SIDEBAR -->
        <aside class="hidden lg:flex w-72 flex-col bg-white dark:bg-zinc-900 border-r border-gray-200 dark:border-zinc-800 h-screen sticky top-0 shadow-[4px_0_24px_-12px_rgba(0,0,0,0.05)] z-30">
            <!-- Brand Logo -->
            <div class="p-6 pb-2">
                <div class="flex items-center gap-3 px-2 py-2">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/20 text-white">
                        <span class="text-2xl">🏫</span>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">SchoolApp</h1>
                        <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider">Management</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto py-6 px-4 scrollbar-hide">
                <div class="space-y-1">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Menu Utama</p>
                    <Motion
                        v-for="item in menuItems"
                        :key="item.route"
                        :whileHover="{ scale: 1.01, x: 2 }"
                        :whileTap="{ scale: 0.98 }"
                    >
                        <Link
                            :href="getRouteUrl(item.route)"
                            @click="handleNavClick"
                            class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 border border-transparent"
                            :class="isActive(item.route)
                                ? 'bg-blue-50/80 border-blue-100 text-blue-600 dark:bg-blue-900/20 dark:border-blue-900/50 dark:text-blue-400 font-semibold shadow-sm'
                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-zinc-800 dark:hover:text-white'"
                        >
                            <component
                                :is="item.icon"
                                class="w-5 h-5 transition-colors duration-200"
                                :class="isActive(item.route) ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300'"
                            />
                            <span>{{ item.name }}</span>

                            <!-- Active Indicator Dot -->
                            <div
                                v-if="isActive(item.route)"
                                class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-600 dark:bg-blue-400 shadow-[0_0_8px_rgba(37,99,235,0.5)]"
                            ></div>
                        </Link>
                    </Motion>
                </div>
            </div>

            <!-- User Profile (Desktop) -->
            <div class="p-4 border-t border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-900/50">
                 <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700/50 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-linear-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                            {{ user?.name?.charAt(0) || 'U' }}
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white truncate max-w-[100px]">{{ user?.name }}</span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ user?.role }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        <Motion :whileTap="{ scale: 0.9 }" :whileHover="{ scale: 1.1 }">
                            <button
                                @click="logout"
                                class="p-2 text-gray-400 hover:text-red-500 transition-colors rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
                                title="Keluar"
                            >
                                <LogOut class="w-4 h-4" />
                            </button>
                        </Motion>
                    </div>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT WRAPPER -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
            <!-- UNIFIED TOP HEADER (Desktop & Mobile) -->
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-gray-100 bg-white/80 dark:bg-zinc-900/80 px-4 lg:px-8 backdrop-blur-md dark:border-zinc-800 transition-all">
                <!-- Left Section: Mobile Logo OR Desktop Greeting/Title -->
                <div class="flex items-center gap-4">
                    <!-- Mobile Logo -->
                    <div class="lg:hidden flex items-center gap-2">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center shadow-md text-white">
                             <span class="text-lg">🏫</span>
                        </div>
                        <span class="font-bold text-lg text-gray-900 dark:text-white">SchoolApp</span>
                    </div>

                    <!-- Desktop Context Title -->
                    <div class="hidden lg:block">
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                             <slot name="header">
                                <!-- Default Greeting if no header slot provided -->
                                <span>Selamat Datang, {{ user?.name?.split(' ')[0] }}</span>
                                <span class="text-xl">👋</span>
                             </slot>
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 hidden lg:block" v-if="!$slots.header">
                            {{ currentDate }}
                        </p>
                    </div>
                </div>

                <!-- Right Section: Utilities -->
                <div class="flex items-center gap-2 lg:gap-4">
                     <!-- Search Button (Optional) -->
                    <Motion :whileTap="{ scale: 0.9 }">
                        <button class="p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-zinc-800 rounded-full transition-colors">
                            <Search class="w-5 h-5" />
                        </button>
                    </Motion>

                    <!-- Notification Bell -->
                    <Motion :whileTap="{ scale: 0.9 }">
                        <button class="relative p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-zinc-800 rounded-full transition-colors">
                            <Bell class="w-5 h-5" />
                            <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white dark:ring-zinc-900"></span>
                        </button>
                    </Motion>

                    <!-- Mobile Profile Trigger -->
                    <div class="lg:hidden pl-2 border-l border-gray-100 dark:border-zinc-800">
                         <Motion :whileTap="{ scale: 0.9 }">
                            <button
                                @click="toggleProfileMenu"
                                class="w-8 h-8 rounded-full bg-linear-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs shadow-sm ring-2 ring-transparent focus:ring-blue-100 dark:focus:ring-blue-900"
                            >
                                {{ user?.name?.charAt(0) || 'U' }}
                            </button>
                        </Motion>
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <main class="flex-1 w-full pb-24 lg:pb-8 relative">
                <div class="p-4 lg:p-8 max-w-7xl mx-auto space-y-6">
                    <slot />
                </div>
            </main>
        </div>

        <!-- MOBILE BOTTOM TAB BAR -->
        <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white/98 dark:bg-zinc-900/98 border-t border-gray-200 dark:border-zinc-800 flex justify-around items-center py-2 pb-[env(safe-area-inset-bottom,16px)] z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)]">
            <Link
                v-for="item in menuItems"
                :key="item.route"
                :href="getRouteUrl(item.route)"
                @click="handleNavClick"
                class="flex flex-col items-center gap-1 p-2 min-w-[64px] transition-transform active:scale-95 group"
            >
                <div class="relative">
                    <component
                        :is="item.icon"
                        class="w-6 h-6 transition-all duration-300"
                        :class="isActive(item.route)
                            ? 'text-blue-600 stroke-[2.5px] -translate-y-1'
                            : 'text-gray-400 stroke-2 group-hover:text-gray-600'"
                    />
                     <span
                        v-if="isActive(item.route)"
                        class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-1 h-1 bg-blue-600 rounded-full"
                    ></span>
                </div>
                <span
                    class="text-[10px] font-medium transition-colors duration-200"
                    :class="isActive(item.route) ? 'text-blue-600' : 'text-gray-500'"
                >
                    {{ item.name }}
                </span>
            </Link>
        </nav>
    </div>
</template>

