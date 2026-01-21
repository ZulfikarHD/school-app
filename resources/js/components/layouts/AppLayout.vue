<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import { Motion, AnimatePresence } from 'motion-v';
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
import { index as adminLeaveRequestsIndex } from '@/routes/admin/leave-requests';
import { index as adminPaymentCategoriesIndex } from '@/routes/admin/payment-categories';
import { index as adminBillsIndex, generate as adminBillsGenerate } from '@/routes/admin/payments/bills';
import { index as adminPaymentRecordsIndex, create as adminPaymentRecordsCreate } from '@/routes/admin/payments/records';
import { index as parentPaymentsIndex } from '@/routes/parent/payments';
import { index as principalStudentsIndex } from '@/routes/principal/students';
import { index as principalTeacherLeavesIndex } from '@/routes/principal/teacher-leaves';
import { reports as principalFinancialReports, delinquents as principalFinancialDelinquents } from '@/routes/principal/financial';
import { index as adminPaymentReportsIndex, delinquents as adminPaymentDelinquents } from '@/routes/admin/payments/reports';
import { index as teacherStudentsIndex } from '@/routes/teacher/students';
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
    ChevronDown,
    GraduationCap,
    CalendarCheck,
    Clock,
    BookOpen,
    History,
    MoreHorizontal,
    X,
    Wallet,
    Tags,
    Receipt,
    CreditCard
} from 'lucide-vue-next';

/**
 * Main application layout dengan Dual-Navigation Strategy
 * - Desktop: Persistent Sidebar + Top Glass Header
 * - Mobile: Bottom Tab Bar + Top Glass Header
 * Mengikuti iOS-like Design Standard (Performance First)
 */

const page = usePage();
const user = computed(() => page.props.auth?.user);
const pendingCounts = computed(() => (page.props as any).pendingCounts ?? { leaveRequests: 0 });
const haptics = useHaptics();
const modal = useModal();
const showProfileMenu = ref(false);
const showMobileNav = ref(false);
const showDesktopProfileMenu = ref(false);

/**
 * Check apakah user prefer reduced motion untuk accessibility
 * Digunakan untuk menonaktifkan animasi bagi pengguna yang sensitif
 */
const prefersReducedMotion = ref(false);
if (typeof window !== 'undefined') {
    prefersReducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * State untuk tracking dropdown menu yang sedang terbuka
 * Key: nama grup dropdown, Value: boolean status open/close
 */
const openDropdowns = ref<Record<string, boolean>>({});

// Session Timeout Logic
// Admin: 15 mins, Others: 30 mins
const timeoutDuration = computed(() =>
    (user.value?.role === 'SUPERADMIN' || user.value?.role === 'ADMIN') ? 15 : 30
);
const { showWarning, remainingSeconds, extendSession, logout: sessionLogout } = useSessionTimeout({
    timeoutMinutes: timeoutDuration.value
});

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
        'admin.leave-requests.index': adminLeaveRequestsIndex().url,
        'admin.payment-categories.index': adminPaymentCategoriesIndex().url,
        'admin.payments.bills.index': adminBillsIndex().url,
        'admin.payments.bills.generate': adminBillsGenerate().url,
        'admin.payments.records.index': adminPaymentRecordsIndex().url,
        'admin.payments.records.create': adminPaymentRecordsCreate().url,
        'admin.audit-logs.index': adminAuditLogsIndex().url,
        'audit-logs.index': auditLogsIndex().url,
        'profile.show': profileShow().url,
        'principal.students.index': principalStudentsIndex().url,
        'principal.teacher-leaves.index': principalTeacherLeavesIndex().url,
        'principal.attendance.dashboard': '/principal/attendance/dashboard',
        'principal.attendance.students.index': '/principal/attendance/students',
        'principal.attendance.teachers.index': '/principal/attendance/teachers',
        'principal.financial.reports': principalFinancialReports().url,
        'principal.financial.delinquents': principalFinancialDelinquents().url,
        'admin.payments.reports.index': adminPaymentReportsIndex().url,
        'admin.payments.reports.delinquents': adminPaymentDelinquents().url,
        'teacher.students.index': teacherStudentsIndex().url,
        'teacher.attendance.index': teacherAttendanceIndex().url,
        'teacher.attendance.subject.create': teacherAttendanceSubjectCreate().url,
        'teacher.attendance.subject.index': teacherAttendanceSubjectIndex().url,
        'teacher.my-attendance': teacherMyAttendance().url,
        'teacher.leave-requests': teacherLeaveRequestsIndex().url,
        'teacher.teacher-leaves.index': teacherTeacherLeavesIndex().url,
        'parent.children': parentChildrenIndex().url,
        'parent.leave-requests': parentLeaveRequestsIndex().url,
        'parent.payments.index': parentPaymentsIndex().url,
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
 * Toggle Mobile Navigation Sheet untuk akses semua menu
 */
const toggleMobileNav = () => {
    haptics.light();
    showMobileNav.value = !showMobileNav.value;
};

/**
 * Toggle Desktop Profile Menu dropdown
 */
const toggleDesktopProfileMenu = () => {
    haptics.light();
    showDesktopProfileMenu.value = !showDesktopProfileMenu.value;
};

/**
 * Handle click outside untuk menutup desktop profile menu
 */
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    // Check if click is outside the profile section
    if (showDesktopProfileMenu.value && !target.closest('.desktop-profile-section')) {
        showDesktopProfileMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

/**
 * Placeholder handler untuk fitur yang belum tersedia
 * Menampilkan info modal dengan pesan "Segera Hadir"
 */
const showComingSoon = (featureName: string) => {
    haptics.light();
    modal.info('Segera Hadir', `Fitur ${featureName} akan segera tersedia dalam pembaruan berikutnya.`);
};

/**
 * Navigation Click Feedback
 */
const handleNavClick = () => {
    haptics.light();
};

/**
 * Toggle dropdown menu state dengan haptic feedback
 */
const toggleDropdown = (groupName: string) => {
    haptics.light();
    openDropdowns.value[groupName] = !openDropdowns.value[groupName];
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
 * Check if any child route in a dropdown group is active
 * untuk auto-expand dropdown ketika child route aktif
 */
const isDropdownActive = (children: MenuItem[]) => {
    return children.some(child => child.route && isActive(child.route));
};

/**
 * Interface untuk menu item yang mendukung dropdown
 */
interface MenuItem {
    name: string;
    route?: string;
    icon: any;
    badge?: number;
    children?: MenuItem[];
}

/**
 * Menu Items dengan Icon Components, Badge Count, dan Dropdown Support
 *
 * Sprint C Enhancement:
 * - Added badge property untuk menampilkan pending count pada menu item
 * - Badge ditampilkan di desktop sidebar dan mobile bottom nav
 * - Added children property untuk dropdown menu support
 */
const menuItems = computed((): MenuItem[] => {
    const role = user.value?.role;
    const leaveRequestsBadge = pendingCounts.value.leaveRequests;

    const commonItems: MenuItem[] = [
        { name: 'Dashboard', route: 'dashboard', icon: Home, badge: 0 },
    ];

    if (role === 'SUPERADMIN' || role === 'ADMIN') {
        return [
            ...commonItems,
            { name: 'Manajemen User', route: 'admin.users.index', icon: Users, badge: 0 },
            { name: 'Data Siswa', route: 'admin.students.index', icon: GraduationCap, badge: 0 },
            {
                name: 'Kehadiran',
                icon: CalendarCheck,
                children: [
                    { name: 'Absensi Siswa', route: 'admin.attendance.students.index', icon: Users, badge: 0 },
                    { name: 'Presensi Guru', route: 'admin.attendance.teachers.index', icon: Clock, badge: 0 },
                ]
            },
            { name: 'Verifikasi Izin', route: 'admin.leave-requests.index', icon: FileText, badge: leaveRequestsBadge },
            {
                name: 'Pembayaran',
                icon: Wallet,
                children: [
                    { name: 'Kategori Pembayaran', route: 'admin.payment-categories.index', icon: Tags, badge: 0 },
                    { name: 'Generate Tagihan', route: 'admin.payments.bills.generate', icon: Receipt, badge: 0 },
                    { name: 'Daftar Tagihan', route: 'admin.payments.bills.index', icon: FileText, badge: 0 },
                    { name: 'Catat Pembayaran', route: 'admin.payments.records.create', icon: CreditCard, badge: 0 },
                    { name: 'Riwayat Pembayaran', route: 'admin.payments.records.index', icon: History, badge: 0 },
                    { name: 'Laporan Keuangan', route: 'admin.payments.reports.index', icon: Activity, badge: 0 },
                ]
            },
            { name: 'Audit Log', route: 'admin.audit-logs.index', icon: Activity, badge: 0 },
        ];
    }

    if (role === 'PRINCIPAL') {
        return [
            ...commonItems,
            { name: 'Data Siswa', route: 'principal.students.index', icon: GraduationCap, badge: 0 },
            {
                name: 'Kehadiran',
                icon: CalendarCheck,
                children: [
                    { name: 'Dashboard', route: 'principal.attendance.dashboard', icon: Home, badge: 0 },
                    { name: 'Absensi Siswa', route: 'principal.attendance.students.index', icon: Users, badge: 0 },
                    { name: 'Presensi Guru', route: 'principal.attendance.teachers.index', icon: Clock, badge: 0 },
                ]
            },
            {
                name: 'Keuangan',
                icon: Wallet,
                children: [
                    { name: 'Laporan Keuangan', route: 'principal.financial.reports', icon: Receipt, badge: 0 },
                    { name: 'Siswa Menunggak', route: 'principal.financial.delinquents', icon: CreditCard, badge: 0 },
                ]
            },
            { name: 'Izin Guru', route: 'principal.teacher-leaves.index', icon: ClipboardList, badge: 0 },
            { name: 'Audit Log', route: 'audit-logs.index', icon: Activity, badge: 0 },
        ];
    }

    if (role === 'TEACHER') {
        return [
            ...commonItems,
            { name: 'Data Siswa', route: 'teacher.students.index', icon: GraduationCap, badge: 0 },
            {
                name: 'Presensi',
                icon: CalendarCheck,
                children: [
                    { name: 'Presensi Harian', route: 'teacher.attendance.index', icon: CalendarCheck, badge: 0 },
                    { name: 'Presensi Mapel', route: 'teacher.attendance.subject.create', icon: BookOpen, badge: 0 },
                    { name: 'Riwayat Mapel', route: 'teacher.attendance.subject.index', icon: History, badge: 0 },
                    { name: 'Riwayat Saya', route: 'teacher.my-attendance', icon: History, badge: 0 },
                ]
            },
            {
                name: 'Perizinan',
                icon: FileText,
                badge: leaveRequestsBadge,
                children: [
                    { name: 'Verifikasi Izin', route: 'teacher.leave-requests', icon: FileText, badge: leaveRequestsBadge },
                    { name: 'Izin Saya', route: 'teacher.teacher-leaves.index', icon: ClipboardList, badge: 0 },
                ]
            },
        ];
    }

    if (role === 'PARENT') {
        return [
            ...commonItems,
            { name: 'Data Anak', route: 'parent.children', icon: Users, badge: 0 },
            { name: 'Pembayaran', route: 'parent.payments.index', icon: CreditCard, badge: 0 },
            { name: 'Pengajuan Izin', route: 'parent.leave-requests', icon: FileText, badge: 0 },
        ];
    }

    return commonItems;
});

/**
 * Mobile bottom nav items dengan batasan 4 item + More button
 * Menampilkan item utama dan "Lainnya" untuk akses menu lengkap
 */
const mobileNavItems = computed((): MenuItem[] => {
    const items: MenuItem[] = [];
    const maxItems = 4; // Maksimal 4 item di bottom nav + More

    menuItems.value.forEach(item => {
        if (items.length >= maxItems) return;

        if (item.children) {
            // Untuk mobile, tampilkan child pertama sebagai representasi grup
            items.push({
                name: item.name,
                route: item.children[0].route,
                icon: item.icon,
                badge: item.badge
            });
        } else {
            items.push(item);
        }
    });

    return items;
});

/**
 * Check apakah ada menu yang tidak ditampilkan di bottom nav
 * untuk menentukan perlu tidaknya tombol "Lainnya"
 */
const hasMoreMenuItems = computed(() => {
    let totalItems = 0;
    menuItems.value.forEach(item => {
        if (item.children) {
            totalItems += 1; // Hitung grup sebagai 1 item
        } else {
            totalItems += 1;
        }
    });
    return totalItems > 4;
});

// Format Date for Header (Desktop - Full format)
const currentDate = new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
}).format(new Date());

// Format Date for Mobile Header (Compact format)
const currentDateShort = new Intl.DateTimeFormat('id-ID', {
    weekday: 'short',
    day: 'numeric',
    month: 'short'
}).format(new Date());

/**
 * Animation config yang mempertimbangkan prefers-reduced-motion
 * untuk accessibility compliance
 */
const getSpringConfig = (stiffness = 300, damping = 25) => {
    if (prefersReducedMotion.value) {
        return { type: 'tween', duration: 0 };
    }
    return { type: 'spring', stiffness, damping };
};

// Export untuk penggunaan di child components jika diperlukan
defineExpose({ getSpringConfig });

</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-zinc-950 flex">
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
            <div class="flex flex-col gap-3">
                <!-- Drag Handle Indicator -->
                <div class="w-10 h-1 bg-slate-200 dark:bg-zinc-700 rounded-full mx-auto -mt-2 mb-1" aria-hidden="true"></div>

                <!-- Profile Header Card -->
                <div class="flex items-center gap-4 p-4 bg-slate-50/80 rounded-2xl dark:bg-zinc-800/50 border border-slate-100 dark:border-zinc-700/50">
                    <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-emerald-500/20">
                        {{ user?.name?.charAt(0) || 'U' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-slate-900 dark:text-white text-lg truncate">{{ user?.name }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 truncate">{{ user?.email }}</p>
                        <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-2.5 py-1 rounded-lg mt-1.5 inline-block uppercase tracking-wide">
                            {{ user?.role }}
                        </span>
                    </div>
                </div>

                <!-- Menu Items -->
                <Link
                    :href="getRouteUrl('profile.show')"
                    @click="showProfileMenu = false"
                    class="w-full text-left px-4 py-3.5 rounded-xl active:bg-slate-50 dark:active:bg-zinc-800 flex items-center justify-between group transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                >
                    <span class="flex items-center gap-3.5 text-slate-700 dark:text-slate-300">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                            <User class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                        </div>
                        <span class="font-medium">Profil Saya</span>
                    </span>
                    <ChevronRight class="w-5 h-5 text-slate-400" />
                </Link>

                <button
                    @click="showComingSoon('Pengaturan')"
                    class="w-full text-left px-4 py-3.5 rounded-xl active:bg-slate-50 dark:active:bg-zinc-800 flex items-center justify-between group transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                >
                    <span class="flex items-center gap-3.5 text-slate-700 dark:text-slate-300">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                            <Settings class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                        </div>
                        <span class="font-medium">Pengaturan</span>
                    </span>
                    <span class="text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-0.5 rounded-full">Segera</span>
                </button>

                <div class="h-px bg-slate-100 dark:bg-zinc-800 my-1 mx-4"></div>

                <button
                    @click="logout"
                    class="w-full text-left px-4 py-3.5 rounded-xl active:bg-red-50 dark:active:bg-red-900/20 flex items-center gap-3.5 text-red-600 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"
                >
                    <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                        <LogOut class="w-5 h-5" />
                    </div>
                    <span class="font-medium">Keluar Aplikasi</span>
                </button>

                <!-- Close Button -->
                <button
                    @click="showProfileMenu = false"
                    class="mt-2 w-full py-3.5 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400 font-semibold text-sm active:scale-[0.98] transition-transform focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                >
                    Tutup
                </button>
            </div>
        </BaseModal>

        <!-- MOBILE FULL NAVIGATION SHEET -->
        <BaseModal
            :show="showMobileNav"
            size="full"
            :show-close-button="false"
            @close="showMobileNav = false"
            class="p-0! m-0!"
        >
            <div class="flex flex-col gap-2">
                <!-- Drag Handle -->
                <div class="w-10 h-1 bg-slate-200 dark:bg-zinc-700 rounded-full mx-auto -mt-2 mb-1" aria-hidden="true"></div>

                <!-- Header -->
                <div class="flex items-center justify-between px-1 mb-2">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Menu Navigasi</h3>
                    <button
                        @click="showMobileNav = false"
                        class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                        aria-label="Tutup menu"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Full Navigation Menu -->
                <div class="space-y-1 max-h-[60vh] overflow-y-auto">
                    <template v-for="item in menuItems" :key="item.name">
                        <!-- Regular Menu Item -->
                        <Link
                            v-if="!item.children"
                            :href="getRouteUrl(item.route!)"
                            @click="showMobileNav = false; handleNavClick()"
                            class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                            :class="isActive(item.route!)
                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400'
                                : 'text-slate-700 dark:text-slate-300 active:bg-slate-50 dark:active:bg-zinc-800'"
                        >
                            <div
                                class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                :class="isActive(item.route!)
                                    ? 'bg-emerald-500 text-white'
                                    : 'bg-slate-100 dark:bg-zinc-800'"
                            >
                                <component :is="item.icon" class="w-5 h-5" />
                            </div>
                            <span class="font-medium flex-1">{{ item.name }}</span>
                            <span
                                v-if="item.badge && item.badge > 0"
                                class="min-w-[22px] h-[22px] flex items-center justify-center px-1.5 text-[11px] font-bold text-white bg-red-500 rounded-full"
                            >
                                {{ item.badge > 99 ? '99+' : item.badge }}
                            </span>
                        </Link>

                        <!-- Dropdown Menu Group -->
                        <div v-else class="space-y-1">
                            <button
                                @click="toggleDropdown(item.name)"
                                class="w-full flex items-center gap-3 px-3 py-3 rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                                :class="isDropdownActive(item.children!) || openDropdowns[item.name]
                                    ? 'bg-emerald-50/50 text-emerald-700 dark:bg-emerald-900/10 dark:text-emerald-400'
                                    : 'text-slate-700 dark:text-slate-300 active:bg-slate-50 dark:active:bg-zinc-800'"
                            >
                                <div
                                    class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                    :class="isDropdownActive(item.children!) || openDropdowns[item.name]
                                        ? 'bg-emerald-500/90 text-white'
                                        : 'bg-slate-100 dark:bg-zinc-800'"
                                >
                                    <component :is="item.icon" class="w-5 h-5" />
                                </div>
                                <span class="font-medium flex-1 text-left">{{ item.name }}</span>
                                <span
                                    v-if="item.badge && item.badge > 0"
                                    class="min-w-[22px] h-[22px] flex items-center justify-center px-1.5 text-[11px] font-bold text-white bg-red-500 rounded-full mr-1"
                                >
                                    {{ item.badge > 99 ? '99+' : item.badge }}
                                </span>
                                <ChevronDown
                                    class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                    :class="openDropdowns[item.name] || isDropdownActive(item.children!) ? 'rotate-180' : ''"
                                />
                            </button>

                            <!-- Children Items -->
                            <div
                                v-if="openDropdowns[item.name] || isDropdownActive(item.children!)"
                                class="ml-6 pl-3 border-l-2 border-slate-100 dark:border-zinc-800 space-y-1"
                            >
                                <Link
                                    v-for="child in item.children"
                                    :key="child.route"
                                    :href="getRouteUrl(child.route!)"
                                    @click="showMobileNav = false; handleNavClick()"
                                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                                    :class="isActive(child.route!)
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 font-medium'
                                        : 'text-slate-600 dark:text-slate-400 active:bg-slate-50 dark:active:bg-zinc-800'"
                                >
                                    <component :is="child.icon" class="w-4 h-4 shrink-0" />
                                    <span class="text-sm flex-1">{{ child.name }}</span>
                                    <span
                                        v-if="child.badge && child.badge > 0"
                                        class="min-w-[20px] h-5 flex items-center justify-center px-1 text-[10px] font-bold text-white bg-red-500 rounded-full"
                                    >
                                        {{ child.badge > 99 ? '99+' : child.badge }}
                                    </span>
                                </Link>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Close Button -->
                <button
                    @click="showMobileNav = false"
                    class="mt-3 w-full py-3.5 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400 font-semibold text-sm active:scale-[0.98] transition-transform focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                >
                    Tutup
                </button>
            </div>
        </BaseModal>

        <!-- DESKTOP SIDEBAR - Compact w-60 -->
        <aside class="hidden lg:flex w-60 flex-col bg-white dark:bg-zinc-900 border-r border-slate-200 dark:border-zinc-800 h-screen sticky top-0 z-30">
            <!-- Brand Logo - Compact -->
            <div class="p-4 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-linear-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-md shadow-emerald-500/20">
                        <span class="text-lg">🏫</span>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 dark:text-white leading-tight">SchoolApp</h1>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Management</p>
                    </div>
                </div>
            </div>

            <!-- Navigation with Dropdown Support -->
            <div class="flex-1 overflow-y-auto py-3 px-3 sidebar-scroll">
                <div class="space-y-0.5">
                    <p class="px-2.5 text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Menu</p>

                    <template v-for="(item, index) in menuItems" :key="item.name">
                        <!-- Regular Menu Item (no children) with Spring Animation -->
                        <Motion
                            v-if="!item.children"
                            :initial="{ opacity: 0, x: -16 }"
                            :animate="{ opacity: 1, x: 0 }"
                            :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: index * 0.03 }"
                            :whileHover="{ scale: 1.02, x: 4 }"
                            :whileTap="{ scale: 0.97 }"
                        >
                            <Link
                                :href="getRouteUrl(item.route!)"
                                @click="handleNavClick"
                                class="group flex items-center gap-2.5 px-2.5 py-2.5 rounded-xl transition-all duration-200 border"
                                :class="isActive(item.route!)
                                    ? 'bg-emerald-50/80 border-emerald-200/60 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-800/50 dark:text-emerald-400 font-semibold'
                                    : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-zinc-800/70 dark:hover:text-white'"
                            >
                                <Motion
                                    :whileHover="{ rotate: [0, -10, 10, -5, 5, 0] }"
                                    :transition="{ duration: 0.5 }"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors duration-200 shrink-0"
                                    :class="isActive(item.route!)
                                        ? 'bg-emerald-500 shadow-sm shadow-emerald-500/25'
                                        : 'bg-slate-100 dark:bg-zinc-800 group-hover:bg-slate-200 dark:group-hover:bg-zinc-700'"
                                >
                                    <component
                                        :is="item.icon"
                                        class="w-4 h-4 transition-colors duration-200"
                                        :class="isActive(item.route!) ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300'"
                                    />
                                </Motion>
                                <span class="flex-1 text-[13px] truncate">{{ item.name }}</span>

                                <!-- Pending Badge with Pulse -->
                                <Motion
                                    v-if="item.badge && item.badge > 0"
                                    :initial="{ scale: 0 }"
                                    :animate="{ scale: 1 }"
                                    :transition="{ type: 'spring', stiffness: 400, damping: 15 }"
                                    class="flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm"
                                >
                                    {{ item.badge > 99 ? '99+' : item.badge }}
                                </Motion>

                                <!-- Active Indicator Dot with Animation -->
                                <Motion
                                    v-else-if="isActive(item.route!)"
                                    :initial="{ scale: 0 }"
                                    :animate="{ scale: 1 }"
                                    :transition="{ type: 'spring', stiffness: 500, damping: 20 }"
                                    class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400 shadow-[0_0_6px_rgba(16,185,129,0.5)]"
                                />
                            </Link>
                        </Motion>

                        <!-- Dropdown Menu Item (has children) -->
                        <div v-else class="space-y-0.5">
                            <!-- Dropdown Toggle Button with Spring Animation -->
                            <Motion
                                :whileHover="{ scale: 1.01, x: 2 }"
                                :whileTap="{ scale: 0.97 }"
                                :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                            >
                                <button
                                    @click="toggleDropdown(item.name)"
                                    class="w-full group flex items-center gap-2.5 px-2.5 py-2.5 rounded-xl transition-all duration-200 border"
                                    :class="isDropdownActive(item.children!) || openDropdowns[item.name]
                                        ? 'bg-emerald-50/50 border-emerald-100 text-emerald-700 dark:bg-emerald-900/10 dark:border-emerald-900/30 dark:text-emerald-400'
                                        : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-zinc-800/70 dark:hover:text-white'"
                                >
                                    <Motion
                                        :animate="{ rotate: openDropdowns[item.name] || isDropdownActive(item.children!) ? 360 : 0 }"
                                        :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors duration-200 shrink-0"
                                        :class="isDropdownActive(item.children!) || openDropdowns[item.name]
                                            ? 'bg-emerald-500/90 shadow-sm shadow-emerald-500/20'
                                            : 'bg-slate-100 dark:bg-zinc-800 group-hover:bg-slate-200 dark:group-hover:bg-zinc-700'"
                                    >
                                        <component
                                            :is="item.icon"
                                            class="w-4 h-4 transition-colors duration-200"
                                            :class="isDropdownActive(item.children!) || openDropdowns[item.name] ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300'"
                                        />
                                    </Motion>
                                    <span class="flex-1 text-[13px] text-left truncate">{{ item.name }}</span>

                                    <!-- Dropdown Badge (sum of children badges) -->
                                    <Motion
                                        v-if="item.badge && item.badge > 0"
                                        :initial="{ scale: 0 }"
                                        :animate="{ scale: 1 }"
                                        :transition="{ type: 'spring', stiffness: 400, damping: 20 }"
                                        class="flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm mr-1"
                                    >
                                        {{ item.badge > 99 ? '99+' : item.badge }}
                                    </Motion>

                                    <!-- Chevron Indicator with Rotation Animation -->
                                    <Motion
                                        :animate="{ rotate: openDropdowns[item.name] || isDropdownActive(item.children!) ? 180 : 0 }"
                                        :transition="{ type: 'spring', stiffness: 300, damping: 25 }"
                                    >
                                        <ChevronDown class="w-4 h-4 text-slate-400" />
                                    </Motion>
                                </button>
                            </Motion>

                            <!-- Dropdown Children with AnimatePresence -->
                            <AnimatePresence>
                                <Motion
                                    v-if="openDropdowns[item.name] || isDropdownActive(item.children!)"
                                    :initial="{ opacity: 0, height: 0, y: -8 }"
                                    :animate="{ opacity: 1, height: 'auto', y: 0 }"
                                    :exit="{ opacity: 0, height: 0, y: -8 }"
                                    :transition="{ type: 'spring', stiffness: 300, damping: 28, mass: 0.8 }"
                                    class="overflow-hidden"
                                >
                                    <div class="ml-4 pl-3 border-l-2 border-slate-100 dark:border-zinc-800 space-y-0.5 pt-1">
                                        <Motion
                                            v-for="(child, childIndex) in item.children"
                                            :key="child.route"
                                            :initial="{ opacity: 0, x: -12 }"
                                            :animate="{ opacity: 1, x: 0 }"
                                            :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: childIndex * 0.05 }"
                                            :whileHover="{ x: 4, scale: 1.01 }"
                                            :whileTap="{ scale: 0.97 }"
                                        >
                                            <Link
                                                :href="getRouteUrl(child.route!)"
                                                @click="handleNavClick"
                                                class="group flex items-center gap-2 px-2.5 py-2 rounded-lg transition-all duration-200"
                                                :class="isActive(child.route!)
                                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 font-medium'
                                                    : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-zinc-800/50 dark:hover:text-slate-300'"
                                            >
                                                <component
                                                    :is="child.icon"
                                                    class="w-3.5 h-3.5 shrink-0 transition-transform duration-200 group-hover:scale-110"
                                                    :class="isActive(child.route!) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-500'"
                                                />
                                                <span class="text-[12px] truncate">{{ child.name }}</span>

                                                <!-- Child Badge with Pulse Animation -->
                                                <Motion
                                                    v-if="child.badge && child.badge > 0"
                                                    :initial="{ scale: 0 }"
                                                    :animate="{ scale: 1 }"
                                                    :transition="{ type: 'spring', stiffness: 400, damping: 15 }"
                                                    class="ml-auto flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[9px] font-bold text-white bg-red-500 rounded-full"
                                                >
                                                    {{ child.badge > 99 ? '99+' : child.badge }}
                                                </Motion>

                                                <!-- Active Dot with Scale Animation -->
                                                <Motion
                                                    v-else-if="isActive(child.route!)"
                                                    :initial="{ scale: 0 }"
                                                    :animate="{ scale: 1 }"
                                                    :transition="{ type: 'spring', stiffness: 500, damping: 20 }"
                                                    class="ml-auto w-1.5 h-1.5 rounded-full bg-emerald-500"
                                                />
                                            </Link>
                                        </Motion>
                                    </div>
                                </Motion>
                            </AnimatePresence>
                        </div>
                    </template>
                </div>
            </div>

            <!-- User Profile (Desktop) - With Dropdown Menu -->
            <div class="desktop-profile-section p-3 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 relative">
                <!-- Profile Card - Clickable to toggle dropdown -->
                <button
                    @click="toggleDesktopProfileMenu"
                    class="w-full flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700/50 shadow-sm hover:border-emerald-200 dark:hover:border-emerald-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                >
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-linear-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-xs shadow-sm shadow-emerald-500/20 shrink-0">
                            {{ user?.name?.charAt(0) || 'U' }}
                        </div>
                        <div class="flex flex-col min-w-0 text-left">
                            <span class="text-[13px] font-semibold text-slate-900 dark:text-white truncate">{{ user?.name }}</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 truncate uppercase tracking-wide">{{ user?.role }}</span>
                        </div>
                    </div>
                    <ChevronDown
                        class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0"
                        :class="showDesktopProfileMenu ? 'rotate-180' : ''"
                    />
                </button>

                <!-- Desktop Profile Dropdown Menu -->
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-2"
                >
                    <div
                        v-if="showDesktopProfileMenu"
                        class="absolute bottom-full left-3 right-3 mb-2 bg-white dark:bg-zinc-800 rounded-xl border border-slate-200 dark:border-zinc-700 shadow-lg overflow-hidden"
                    >
                        <!-- Profile Link -->
                        <Link
                            :href="getRouteUrl('profile.show')"
                            @click="showDesktopProfileMenu = false"
                            class="flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-700/50 transition-colors focus:outline-none focus-visible:bg-slate-50 dark:focus-visible:bg-zinc-700/50"
                        >
                            <User class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                            <span class="text-sm font-medium">Profil Saya</span>
                        </Link>

                        <!-- Settings Link (Coming Soon) -->
                        <button
                            @click="showDesktopProfileMenu = false; showComingSoon('Pengaturan')"
                            class="w-full flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-700/50 transition-colors focus:outline-none focus-visible:bg-slate-50 dark:focus-visible:bg-zinc-700/50"
                        >
                            <Settings class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                            <span class="text-sm font-medium">Pengaturan</span>
                            <span class="ml-auto text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-0.5 rounded-full">
                                Segera
                            </span>
                        </button>

                        <!-- Divider -->
                        <div class="h-px bg-slate-100 dark:bg-zinc-700"></div>

                        <!-- Logout Button -->
                        <button
                            @click="logout"
                            class="w-full flex items-center gap-3 px-4 py-3 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors focus:outline-none focus-visible:bg-red-50 dark:focus-visible:bg-red-900/20"
                        >
                            <LogOut class="w-4 h-4" />
                            <span class="text-sm font-medium">Keluar</span>
                        </button>
                    </div>
                </Transition>
            </div>
        </aside>

        <!-- MAIN CONTENT WRAPPER -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
            <!-- UNIFIED TOP HEADER (Desktop & Mobile) - Fake Glass: No heavy blur on mobile -->
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-100 bg-white/95 dark:bg-zinc-900/95 px-4 lg:px-8 dark:border-zinc-800 transition-all">
                <!-- Left Section: Mobile Logo OR Desktop Greeting/Title -->
                <div class="flex items-center gap-4">
                    <!-- Mobile Logo -->
                    <div class="lg:hidden flex items-center gap-2.5">
                        <div class="w-9 h-9 bg-linear-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-md shadow-emerald-500/20">
                            <span class="text-lg">🏫</span>
                        </div>
                        <span class="font-bold text-lg text-slate-900 dark:text-white">SchoolApp</span>
                    </div>

                    <!-- Desktop Context Title -->
                    <div class="hidden lg:block">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <slot name="header">
                                <!-- Default Greeting if no header slot provided -->
                                <span>Selamat Datang, {{ user?.name?.split(' ')[0] }}</span>
                                <span class="text-xl">👋</span>
                            </slot>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 hidden lg:block" v-if="!$slots.header">
                            {{ currentDate }}
                        </p>
                    </div>
                </div>

                <!-- Right Section: Utilities -->
                <div class="flex items-center gap-1.5 lg:gap-3">
                    <!-- Mobile Date Display -->
                    <span class="lg:hidden text-[11px] font-medium text-slate-500 dark:text-slate-400 hidden xs:inline">
                        {{ currentDateShort }}
                    </span>

                    <!-- Search Button -->
                    <Motion :whileTap="{ scale: 0.9 }">
                        <button
                            @click="showComingSoon('Pencarian')"
                            class="p-2.5 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-zinc-800 rounded-xl transition-colors active:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                            aria-label="Cari"
                        >
                            <Search class="w-5 h-5" />
                        </button>
                    </Motion>

                    <!-- Notification Bell -->
                    <Motion :whileTap="{ scale: 0.9 }">
                        <button
                            @click="showComingSoon('Notifikasi')"
                            class="relative p-2.5 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-zinc-800 rounded-xl transition-colors active:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                            aria-label="Notifikasi"
                        >
                            <Bell class="w-5 h-5" />
                            <!-- Badge indicator hidden until notifications implemented -->
                            <!-- <span class="absolute top-2 right-2 w-2.5 h-2.5 bg-red-500 rounded-full ring-2 ring-white dark:ring-zinc-900"></span> -->
                        </button>
                    </Motion>

                    <!-- Mobile Profile Trigger -->
                    <div class="lg:hidden pl-2 border-l border-slate-100 dark:border-zinc-800">
                        <Motion :whileTap="{ scale: 0.9 }">
                            <button
                                @click="toggleProfileMenu"
                                class="w-9 h-9 rounded-xl bg-linear-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm shadow-sm shadow-emerald-500/20 ring-2 ring-transparent focus:ring-emerald-200 dark:focus:ring-emerald-900 active:scale-95 transition-transform focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                                aria-label="Menu profil"
                            >
                                {{ user?.name?.charAt(0) || 'U' }}
                            </button>
                        </Motion>
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <main class="flex-1 w-full pb-28 lg:pb-8 relative">
                <div class="p-4 lg:p-8 max-w-7xl mx-auto space-y-6">
                    <slot />
                </div>
            </main>
        </div>

        <!-- MOBILE BOTTOM TAB BAR - Max 4 items + More button -->
        <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white/95 dark:bg-zinc-900/95 border-t border-slate-200 dark:border-zinc-800 z-50" role="navigation" aria-label="Menu utama">
            <div class="flex justify-around items-stretch h-[72px] pb-[env(safe-area-inset-bottom,8px)]">
                <!-- Navigation Items -->
                <Link
                    v-for="item in mobileNavItems"
                    :key="item.route"
                    :href="getRouteUrl(item.route!)"
                    @click="handleNavClick"
                    class="flex flex-col items-center justify-center gap-1 flex-1 min-w-0 px-1 active:bg-slate-50 dark:active:bg-zinc-800/50 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-emerald-500"
                    :aria-current="isActive(item.route!) ? 'page' : undefined"
                >
                    <div class="relative">
                        <!-- Icon Container with Active Background -->
                        <div
                            class="w-11 h-7 rounded-full flex items-center justify-center transition-all duration-200"
                            :class="isActive(item.route!) ? 'bg-emerald-100 dark:bg-emerald-900/30' : ''"
                        >
                            <component
                                :is="item.icon"
                                class="w-[22px] h-[22px] transition-all duration-200"
                                :class="isActive(item.route!)
                                    ? 'text-emerald-600 dark:text-emerald-400'
                                    : 'text-slate-400 dark:text-slate-500'"
                            />
                        </div>
                        <!-- Mobile Badge (absolute positioned) -->
                        <span
                            v-if="item.badge && item.badge > 0"
                            class="absolute -top-1 right-0 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm"
                            aria-label="Ada notifikasi baru"
                        >
                            {{ item.badge > 99 ? '99+' : item.badge }}
                        </span>
                    </div>
                    <span
                        class="text-[11px] font-medium transition-colors duration-200 truncate max-w-full px-0.5"
                        :class="isActive(item.route!) ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-slate-500 dark:text-slate-400'"
                    >
                        {{ item.name }}
                    </span>
                </Link>

                <!-- More Button - Shows full navigation sheet -->
                <button
                    v-if="hasMoreMenuItems"
                    @click="toggleMobileNav"
                    class="flex flex-col items-center justify-center gap-1 flex-1 min-w-0 px-1 active:bg-slate-50 dark:active:bg-zinc-800/50 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-emerald-500"
                    :class="showMobileNav ? 'text-emerald-600 dark:text-emerald-400' : ''"
                    aria-label="Menu lainnya"
                    :aria-expanded="showMobileNav"
                >
                    <div class="relative">
                        <div
                            class="w-11 h-7 rounded-full flex items-center justify-center transition-all duration-200"
                            :class="showMobileNav ? 'bg-emerald-100 dark:bg-emerald-900/30' : ''"
                        >
                            <MoreHorizontal
                                class="w-[22px] h-[22px] transition-all duration-200"
                                :class="showMobileNav
                                    ? 'text-emerald-600 dark:text-emerald-400'
                                    : 'text-slate-400 dark:text-slate-500'"
                            />
                        </div>
                    </div>
                    <span
                        class="text-[11px] font-medium transition-colors duration-200"
                        :class="showMobileNav ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-slate-500 dark:text-slate-400'"
                    >
                        Lainnya
                    </span>
                </button>
            </div>
        </nav>
    </div>
</template>

<style scoped>
/**
 * Custom thin scrollbar untuk sidebar navigation
 * Visible tapi tidak mengganggu - sesuai accessibility guidelines
 * Menggunakan CSS variables dari Tailwind v4
 */
.sidebar-scroll {
    scrollbar-width: thin;
    scrollbar-color: var(--color-slate-200) transparent;
}

.sidebar-scroll::-webkit-scrollbar {
    width: 4px;
}

.sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
    background-color: var(--color-slate-200);
    border-radius: 9999px;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background-color: var(--color-slate-300);
}

/* Dark mode scrollbar */
:root.dark .sidebar-scroll {
    scrollbar-color: var(--color-zinc-700) transparent;
}

:root.dark .sidebar-scroll::-webkit-scrollbar-thumb {
    background-color: var(--color-zinc-700);
}

:root.dark .sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background-color: var(--color-zinc-600);
}
</style>

