/**
 * useNavigation Composable - Centralized Navigation State Management
 *
 * Composable ini mengelola seluruh state dan logic navigasi aplikasi,
 * yaitu: route mapping, menu items berdasarkan role, dan active state tracking.
 * Mendukung dropdown menu dengan auto-expand saat child route aktif.
 */
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    Home,
    Users,
    Activity,
    FileText,
    ClipboardList,
    User,
    Settings,
    GraduationCap,
    CalendarCheck,
    Clock,
    BookOpen,
    History,
    Wallet,
    Tags,
    Receipt,
    CreditCard,
    FileSpreadsheet,
    Award,
    Heart,
    UserPlus,
} from 'lucide-vue-next';

// Import route functions dari Wayfinder
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
import { index as adminPaymentReconciliationIndex } from '@/routes/admin/payments/reconciliation';
import { index as adminGradesIndex, summary as adminGradesSummary } from '@/routes/admin/grades';
import { index as adminGradeWeightsIndex } from '@/routes/admin/settings/grade-weights';
import { index as teacherStudentsIndex } from '@/routes/teacher/students';
import { index as teacherAttendanceIndex } from '@/routes/teacher/attendance';
import { create as teacherAttendanceSubjectCreate, index as teacherAttendanceSubjectIndex } from '@/routes/teacher/attendance/subject';
import { myAttendance as teacherMyAttendance } from '@/routes/teacher';
import { index as teacherLeaveRequestsIndex } from '@/routes/teacher/leave-requests';
import { index as teacherTeacherLeavesIndex } from '@/routes/teacher/teacher-leaves';
import { index as teacherGradesIndex } from '@/routes/teacher/grades';
import { index as teacherAttitudeGradesIndex } from '@/routes/teacher/attitude-grades';
import { index as teacherReportCardsIndex } from '@/routes/teacher/report-cards';
import { index as adminReportCardsIndex } from '@/routes/admin/report-cards';
import { index as adminPsbIndex } from '@/routes/admin/psb';
import { index as adminPsbRegistrationsIndex } from '@/routes/admin/psb/registrations';
import { index as principalReportCardsIndex } from '@/routes/principal/report-cards';
import { dashboard as principalAcademicDashboard, grades as principalAcademicGrades } from '@/routes/principal/academic';

import type { Component } from 'vue';

/**
 * Interface untuk menu item yang mendukung dropdown
 */
export interface MenuItem {
    name: string;
    route?: string;
    icon: Component;
    badge?: number;
    children?: MenuItem[];
}

/**
 * Type untuk pending counts yang dikirim dari backend
 */
export interface PendingCounts {
    leaveRequests: number;
    pendingPayments: number;
    pendingPsb?: number;
}

/**
 * Route map untuk mapping route name ke URL
 * Menggunakan Wayfinder functions untuk generate URLs
 */
const routeMap: Record<string, () => string> = {
    'dashboard': () => dashboard().url,
    'admin.users.index': () => adminUsersIndex().url,
    'admin.students.index': () => adminStudentsIndex().url,
    'admin.attendance.students.index': () => adminAttendanceStudentsIndex().url,
    'admin.attendance.teachers.index': () => adminAttendanceTeachersIndex().url,
    'admin.leave-requests.index': () => adminLeaveRequestsIndex().url,
    'admin.payment-categories.index': () => adminPaymentCategoriesIndex().url,
    'admin.payments.bills.index': () => adminBillsIndex().url,
    'admin.payments.bills.generate': () => adminBillsGenerate().url,
    'admin.payments.records.index': () => adminPaymentRecordsIndex().url,
    'admin.payments.records.create': () => adminPaymentRecordsCreate().url,
    'admin.audit-logs.index': () => adminAuditLogsIndex().url,
    'audit-logs.index': () => auditLogsIndex().url,
    'profile.show': () => profileShow().url,
    'principal.students.index': () => principalStudentsIndex().url,
    'principal.teacher-leaves.index': () => principalTeacherLeavesIndex().url,
    'principal.attendance.dashboard': () => '/principal/attendance/dashboard',
    'principal.attendance.students.index': () => '/principal/attendance/students',
    'principal.attendance.teachers.index': () => '/principal/attendance/teachers',
    'principal.financial.reports': () => principalFinancialReports().url,
    'principal.financial.delinquents': () => principalFinancialDelinquents().url,
    'admin.payments.reports.index': () => adminPaymentReportsIndex().url,
    'admin.payments.reports.delinquents': () => adminPaymentDelinquents().url,
    'admin.payments.reconciliation.index': () => adminPaymentReconciliationIndex().url,
    'admin.grades.index': () => adminGradesIndex().url,
    'admin.grades.summary': () => adminGradesSummary().url,
    'admin.settings.grade-weights.index': () => adminGradeWeightsIndex().url,
    'teacher.students.index': () => teacherStudentsIndex().url,
    'teacher.attendance.index': () => teacherAttendanceIndex().url,
    'teacher.attendance.subject.create': () => teacherAttendanceSubjectCreate().url,
    'teacher.attendance.subject.index': () => teacherAttendanceSubjectIndex().url,
    'teacher.my-attendance': () => teacherMyAttendance().url,
    'teacher.leave-requests': () => teacherLeaveRequestsIndex().url,
    'teacher.teacher-leaves.index': () => teacherTeacherLeavesIndex().url,
    'teacher.grades.index': () => teacherGradesIndex().url,
    'teacher.attitude-grades.index': () => teacherAttitudeGradesIndex().url,
    'teacher.report-cards.index': () => teacherReportCardsIndex().url,
    'admin.report-cards.index': () => adminReportCardsIndex().url,
    'admin.psb.index': () => adminPsbIndex().url,
    'admin.psb.registrations.index': () => adminPsbRegistrationsIndex().url,
    'principal.report-cards.index': () => principalReportCardsIndex().url,
    'principal.academic.dashboard': () => principalAcademicDashboard().url,
    'principal.academic.grades': () => principalAcademicGrades().url,
    'parent.children': () => parentChildrenIndex().url,
    'parent.leave-requests': () => parentLeaveRequestsIndex().url,
    'parent.payments.index': () => parentPaymentsIndex().url,
};

/**
 * Get logout route URL
 */
export function getLogoutUrl(): string {
    return logoutRoute().url;
}

/**
 * Main composable function untuk navigation
 */
export function useNavigation() {
    const page = usePage();

    /**
     * Current authenticated user
     */
    const user = computed(() => page.props.auth?.user);

    /**
     * Pending counts dari backend untuk badge display
     */
    const pendingCounts = computed((): PendingCounts =>
        (page.props as any).pendingCounts ?? { leaveRequests: 0, pendingPayments: 0 }
    );

    /**
     * State untuk tracking dropdown menu yang sedang terbuka
     */
    const openDropdowns = ref<Record<string, boolean>>({});

    /**
     * Helper function untuk generate route URL
     */
    const getRouteUrl = (routeName: string): string => {
        const getter = routeMap[routeName];
        return getter ? getter() : '#';
    };

    /**
     * Check active route dengan logic yang presisi
     * untuk menghindari konflik antara route dengan prefix serupa
     */
    const isActive = (route: string): boolean => {
        const routeUrl = getRouteUrl(route);
        const currentUrl = page.url.split('?')[0];

        // Special cases untuk teacher attendance routes
        if (route === 'teacher.attendance.index') {
            return currentUrl === '/teacher/attendance' || currentUrl.startsWith('/teacher/attendance/daily');
        }

        if (route === 'teacher.attendance.subject.create') {
            return currentUrl === '/teacher/attendance/subject';
        }

        if (route === 'teacher.attendance.subject.index') {
            return currentUrl.startsWith('/teacher/attendance/subject/history');
        }

        // Payment routes - exact matching untuk menghindari konflik
        if (route === 'admin.payments.bills.generate') {
            return currentUrl === '/admin/payments/bills/generate';
        }

        if (route === 'admin.payments.bills.index') {
            return currentUrl === '/admin/payments/bills' ||
                   (currentUrl.startsWith('/admin/payments/bills') && !currentUrl.includes('/generate'));
        }

        if (route === 'admin.payments.records.create') {
            return currentUrl === '/admin/payments/records/create';
        }

        if (route === 'admin.payments.records.index') {
            return currentUrl === '/admin/payments/records' ||
                   (currentUrl.startsWith('/admin/payments/records') &&
                    !currentUrl.includes('/create') &&
                    !currentUrl.includes('/verification'));
        }

        // Default: starts with
        return currentUrl.startsWith(routeUrl);
    };

    /**
     * Check if any child route in a dropdown group is active
     */
    const isDropdownActive = (children: MenuItem[]): boolean => {
        return children.some(child => child.route && isActive(child.route));
    };

    /**
     * Toggle dropdown menu state
     */
    const toggleDropdown = (groupName: string): void => {
        openDropdowns.value[groupName] = !openDropdowns.value[groupName];
    };

    /**
     * Menu items berdasarkan user role dengan dropdown support
     */
    const menuItems = computed((): MenuItem[] => {
        const role = user.value?.role;
        const leaveRequestsBadge = pendingCounts.value.leaveRequests;

        const commonItems: MenuItem[] = [
            { name: 'Dashboard', route: 'dashboard', icon: Home, badge: 0 },
        ];

        if (role === 'SUPERADMIN' || role === 'ADMIN') {
            const pendingPaymentsBadge = pendingCounts.value.pendingPayments;

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
                    name: 'PSB',
                    icon: UserPlus,
                    children: [
                        { name: 'Dashboard', route: 'admin.psb.index', icon: Home, badge: 0 },
                        { name: 'Pendaftaran', route: 'admin.psb.registrations.index', icon: Users, badge: pendingCounts.value.pendingPsb || 0 },
                    ]
                },
                {
                    name: 'Pembayaran',
                    icon: Wallet,
                    badge: pendingPaymentsBadge,
                    children: [
                        { name: 'Kategori Pembayaran', route: 'admin.payment-categories.index', icon: Tags, badge: 0 },
                        { name: 'Generate Tagihan', route: 'admin.payments.bills.generate', icon: Receipt, badge: 0 },
                        { name: 'Daftar Tagihan', route: 'admin.payments.bills.index', icon: FileText, badge: 0 },
                        { name: 'Catat Pembayaran', route: 'admin.payments.records.create', icon: CreditCard, badge: 0 },
                        { name: 'Riwayat Pembayaran', route: 'admin.payments.records.index', icon: History, badge: pendingPaymentsBadge },
                        { name: 'Rekonsiliasi Bank', route: 'admin.payments.reconciliation.index', icon: FileSpreadsheet, badge: 0 },
                        { name: 'Laporan Keuangan', route: 'admin.payments.reports.index', icon: Activity, badge: 0 },
                    ]
                },
                {
                    name: 'Penilaian',
                    icon: Award,
                    children: [
                        { name: 'Rekap Nilai', route: 'admin.grades.index', icon: GraduationCap, badge: 0 },
                        { name: 'Summary Nilai', route: 'admin.grades.summary', icon: FileSpreadsheet, badge: 0 },
                        { name: 'Rapor', route: 'admin.report-cards.index', icon: FileText, badge: 0 },
                        { name: 'Bobot Nilai', route: 'admin.settings.grade-weights.index', icon: Settings, badge: 0 },
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
                    name: 'Akademik',
                    icon: Award,
                    children: [
                        { name: 'Dashboard', route: 'principal.academic.dashboard', icon: Activity, badge: 0 },
                        { name: 'Rekap Nilai', route: 'principal.academic.grades', icon: BookOpen, badge: 0 },
                        { name: 'Approval Rapor', route: 'principal.report-cards.index', icon: FileText, badge: 0 },
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
                    name: 'Penilaian',
                    icon: Award,
                    children: [
                        { name: 'Input Nilai', route: 'teacher.grades.index', icon: Award, badge: 0 },
                        { name: 'Nilai Sikap', route: 'teacher.attitude-grades.index', icon: Heart, badge: 0 },
                        { name: 'Rapor Kelas', route: 'teacher.report-cards.index', icon: FileText, badge: 0 },
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
     * Mobile bottom nav items - max 4 items + More button
     */
    const mobileNavItems = computed((): MenuItem[] => {
        const items: MenuItem[] = [];
        const maxItems = 4;

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
     */
    const hasMoreMenuItems = computed((): boolean => {
        let totalItems = 0;
        menuItems.value.forEach(item => {
            totalItems += 1;
        });
        return totalItems > 4;
    });

    /**
     * Get profile route URL
     */
    const profileUrl = computed(() => getRouteUrl('profile.show'));

    return {
        // State
        user,
        pendingCounts,
        openDropdowns,

        // Menu data
        menuItems,
        mobileNavItems,
        hasMoreMenuItems,

        // URL helpers
        getRouteUrl,
        profileUrl,

        // Active state
        isActive,
        isDropdownActive,

        // Actions
        toggleDropdown,
    };
}

// Re-export icons untuk digunakan di components yang membutuhkan
export {
    User as UserIcon,
    Settings as SettingsIcon,
};
