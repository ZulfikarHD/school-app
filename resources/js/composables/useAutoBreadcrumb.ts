/**
 * useAutoBreadcrumb Composable - Auto-generate breadcrumb items
 * berdasarkan current URL tanpa perlu konfigurasi manual di setiap page
 *
 * Composable ini reactive terhadap perubahan route dan akan
 * otomatis update breadcrumb items saat navigasi
 */
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/components/ui/Breadcrumb.vue';

// Import route functions untuk generate URLs
import { index as adminUsersIndex } from '@/routes/admin/users';
import { index as adminStudentsIndex } from '@/routes/admin/students';
import { index as adminAuditLogsIndex } from '@/routes/admin/audit-logs';
import { index as adminAttendanceStudentsIndex } from '@/routes/admin/attendance/students';
import { index as adminAttendanceTeachersIndex } from '@/routes/admin/attendance/teachers';
import { index as adminLeaveRequestsIndex } from '@/routes/admin/leave-requests';
import { index as adminPaymentCategoriesIndex } from '@/routes/admin/payment-categories';
import { index as adminBillsIndex } from '@/routes/admin/payments/bills';
import { index as adminPaymentRecordsIndex } from '@/routes/admin/payments/records';
import { index as adminPaymentReportsIndex } from '@/routes/admin/payments/reports';
import { index as adminPaymentReconciliationIndex } from '@/routes/admin/payments/reconciliation';
import { index as teacherStudentsIndex } from '@/routes/teacher/students';
import { index as teacherAttendanceIndex } from '@/routes/teacher/attendance';
import { index as teacherLeaveRequestsIndex } from '@/routes/teacher/leave-requests';
import { index as teacherTeacherLeavesIndex } from '@/routes/teacher/teacher-leaves';
import { index as parentChildrenIndex } from '@/routes/parent/children';
import { index as parentPaymentsIndex } from '@/routes/parent/payments';
import { index as parentLeaveRequestsIndex } from '@/routes/parent/leave-requests';
import { index as principalStudentsIndex } from '@/routes/principal/students';
import { reports as principalFinancialReports } from '@/routes/principal/financial';

/**
 * Breadcrumb configuration untuk setiap route pattern
 * - label: Text yang ditampilkan
 * - parent: Key parent breadcrumb untuk build hierarchy
 * - getUrl: Function untuk generate URL (optional, jika tidak ada = current page)
 */
interface BreadcrumbConfig {
    label: string | ((props: any) => string);
    parent?: string;
    getUrl?: () => string;
}

/**
 * Registry semua breadcrumb configurations
 * Key adalah URL pattern, value adalah config
 */
const breadcrumbRegistry: Record<string, BreadcrumbConfig> = {
    // ============ ADMIN ROUTES ============

    // Users
    '/admin/users': {
        label: 'Manajemen User',
    },
    '/admin/users/create': {
        label: 'Tambah User',
        parent: '/admin/users',
        getUrl: () => adminUsersIndex().url,
    },
    '/admin/users/*/edit': {
        label: (props) => props?.user?.name || 'Edit User',
        parent: '/admin/users',
        getUrl: () => adminUsersIndex().url,
    },

    // Students
    '/admin/students': {
        label: 'Data Siswa',
    },
    '/admin/students/create': {
        label: 'Tambah Siswa',
        parent: '/admin/students',
        getUrl: () => adminStudentsIndex().url,
    },
    '/admin/students/promote': {
        label: 'Kenaikan Kelas',
        parent: '/admin/students',
        getUrl: () => adminStudentsIndex().url,
    },
    '/admin/students/*/edit': {
        label: (props) => props?.student?.nama_lengkap || 'Edit Siswa',
        parent: '/admin/students',
        getUrl: () => adminStudentsIndex().url,
    },
    '/admin/students/*': {
        label: (props) => props?.student?.nama_lengkap || 'Detail Siswa',
        parent: '/admin/students',
        getUrl: () => adminStudentsIndex().url,
    },

    // Attendance - Students
    '/admin/attendance/students': {
        label: 'Absensi Siswa',
    },
    '/admin/attendance/students/*/report': {
        label: 'Laporan',
        parent: '/admin/attendance/students',
        getUrl: () => adminAttendanceStudentsIndex().url,
    },
    '/admin/attendance/students/*/correction': {
        label: 'Koreksi',
        parent: '/admin/attendance/students',
        getUrl: () => adminAttendanceStudentsIndex().url,
    },

    // Attendance - Teachers
    '/admin/attendance/teachers': {
        label: 'Presensi Guru',
    },
    '/admin/attendance/teachers/*/report': {
        label: 'Laporan',
        parent: '/admin/attendance/teachers',
        getUrl: () => adminAttendanceTeachersIndex().url,
    },

    // Leave Requests
    '/admin/leave-requests': {
        label: 'Verifikasi Izin',
    },

    // Payment Categories
    '/admin/payment-categories': {
        label: 'Kategori Pembayaran',
    },
    '/admin/payment-categories/create': {
        label: 'Tambah Kategori',
        parent: '/admin/payment-categories',
        getUrl: () => adminPaymentCategoriesIndex().url,
    },
    '/admin/payment-categories/*/edit': {
        label: (props) => props?.category?.nama || 'Edit Kategori',
        parent: '/admin/payment-categories',
        getUrl: () => adminPaymentCategoriesIndex().url,
    },

    // Bills
    '/admin/payments/bills': {
        label: 'Daftar Tagihan',
    },
    '/admin/payments/bills/generate': {
        label: 'Generate Tagihan',
        parent: '/admin/payments/bills',
        getUrl: () => adminBillsIndex().url,
    },

    // Payment Records
    '/admin/payments/records': {
        label: 'Riwayat Pembayaran',
    },
    '/admin/payments/records/create': {
        label: 'Catat Pembayaran',
        parent: '/admin/payments/records',
        getUrl: () => adminPaymentRecordsIndex().url,
    },
    '/admin/payments/records/*': {
        label: (props) => props?.payment?.nomor_transaksi ? `#${props.payment.nomor_transaksi}` : 'Detail Pembayaran',
        parent: '/admin/payments/records',
        getUrl: () => adminPaymentRecordsIndex().url,
    },

    // Payment Reports
    '/admin/payments/reports': {
        label: 'Laporan Keuangan',
    },
    '/admin/payments/reports/delinquents': {
        label: 'Siswa Menunggak',
        parent: '/admin/payments/reports',
        getUrl: () => adminPaymentReportsIndex().url,
    },

    // Reconciliation
    '/admin/payments/reconciliation': {
        label: 'Rekonsiliasi Bank',
    },
    '/admin/payments/reconciliation/match/*': {
        label: 'Cocokkan Transaksi',
        parent: '/admin/payments/reconciliation',
        getUrl: () => adminPaymentReconciliationIndex().url,
    },

    // Audit Logs
    '/admin/audit-logs': {
        label: 'Audit Log',
    },

    // ============ TEACHER ROUTES ============

    '/teacher/students': {
        label: 'Data Siswa',
    },
    '/teacher/students/*': {
        label: (props) => props?.student?.nama_lengkap || 'Detail Siswa',
        parent: '/teacher/students',
        getUrl: () => teacherStudentsIndex().url,
    },

    '/teacher/attendance': {
        label: 'Presensi Harian',
    },
    '/teacher/attendance/daily/*': {
        label: 'Input Presensi',
        parent: '/teacher/attendance',
        getUrl: () => teacherAttendanceIndex().url,
    },
    '/teacher/attendance/create': {
        label: 'Input Presensi',
        parent: '/teacher/attendance',
        getUrl: () => teacherAttendanceIndex().url,
    },

    '/teacher/attendance/subject': {
        label: 'Presensi Mapel',
    },
    '/teacher/attendance/subject/history': {
        label: 'Riwayat Presensi Mapel',
    },

    '/teacher/my-attendance': {
        label: 'Riwayat Saya',
    },

    '/teacher/leave-requests': {
        label: 'Verifikasi Izin',
    },

    '/teacher/teacher-leaves': {
        label: 'Izin Saya',
    },
    '/teacher/teacher-leaves/create': {
        label: 'Ajukan Izin',
        parent: '/teacher/teacher-leaves',
        getUrl: () => teacherTeacherLeavesIndex().url,
    },

    // ============ PRINCIPAL ROUTES ============

    '/principal/students': {
        label: 'Data Siswa',
    },
    '/principal/students/*': {
        label: (props) => props?.student?.nama_lengkap || 'Detail Siswa',
        parent: '/principal/students',
        getUrl: () => principalStudentsIndex().url,
    },

    '/principal/attendance/dashboard': {
        label: 'Dashboard Kehadiran',
    },
    '/principal/attendance/students': {
        label: 'Absensi Siswa',
    },
    '/principal/attendance/students/*/report': {
        label: 'Laporan',
        parent: '/principal/attendance/students',
    },
    '/principal/attendance/teachers': {
        label: 'Presensi Guru',
    },
    '/principal/attendance/teachers/*/report': {
        label: 'Laporan',
        parent: '/principal/attendance/teachers',
    },

    '/principal/teacher-leaves': {
        label: 'Izin Guru',
    },

    '/principal/financial/reports': {
        label: 'Laporan Keuangan',
    },
    '/principal/financial/delinquents': {
        label: 'Siswa Menunggak',
        parent: '/principal/financial/reports',
        getUrl: () => principalFinancialReports().url,
    },

    // ============ PARENT ROUTES ============

    '/parent/children': {
        label: 'Data Anak',
    },
    '/parent/children/*/attendance': {
        label: 'Kehadiran',
        parent: '/parent/children',
        getUrl: () => parentChildrenIndex().url,
    },
    '/parent/children/*': {
        label: (props) => props?.child?.nama_lengkap || 'Detail Anak',
        parent: '/parent/children',
        getUrl: () => parentChildrenIndex().url,
    },

    '/parent/payments': {
        label: 'Pembayaran',
    },
    '/parent/payments/history': {
        label: 'Riwayat Pembayaran',
        parent: '/parent/payments',
        getUrl: () => parentPaymentsIndex().url,
    },

    '/parent/leave-requests': {
        label: 'Pengajuan Izin',
    },
    '/parent/leave-requests/create': {
        label: 'Ajukan Izin Baru',
        parent: '/parent/leave-requests',
        getUrl: () => parentLeaveRequestsIndex().url,
    },
    '/parent/leave-requests/*/edit': {
        label: 'Edit Izin',
        parent: '/parent/leave-requests',
        getUrl: () => parentLeaveRequestsIndex().url,
    },

    // ============ COMMON ROUTES ============

    '/profile': {
        label: 'Profil Saya',
    },

    '/audit-logs': {
        label: 'Audit Log',
    },
};

/**
 * Match URL path dengan pattern yang support wildcard (*)
 * Wildcard * matches satu path segment (tidak termasuk /)
 */
function matchPath(url: string, pattern: string): boolean {
    // Convert pattern to regex
    const regexPattern = pattern
        .replace(/\*/g, '[^/]+')
        .replace(/\//g, '\\/');

    const regex = new RegExp(`^${regexPattern}$`);
    return regex.test(url);
}

/**
 * Find best matching breadcrumb config untuk URL
 * Prioritaskan exact match, lalu pattern match
 */
function findBreadcrumbConfig(url: string): { pattern: string; config: BreadcrumbConfig } | null {
    const cleanUrl = url.split('?')[0];

    // Try exact match first
    if (breadcrumbRegistry[cleanUrl]) {
        return { pattern: cleanUrl, config: breadcrumbRegistry[cleanUrl] };
    }

    // Try pattern matching - sort by specificity (longer patterns first)
    const patterns = Object.keys(breadcrumbRegistry)
        .filter(p => p.includes('*'))
        .sort((a, b) => b.length - a.length);

    for (const pattern of patterns) {
        if (matchPath(cleanUrl, pattern)) {
            return { pattern, config: breadcrumbRegistry[pattern] };
        }
    }

    return null;
}

/**
 * Build URL untuk parent breadcrumb
 */
function getParentUrl(parentPattern: string): string {
    const config = breadcrumbRegistry[parentPattern];
    if (config?.getUrl) {
        return config.getUrl();
    }
    return parentPattern;
}

/**
 * Main composable function
 */
export function useAutoBreadcrumb() {
    const page = usePage();

    /**
     * Computed breadcrumb items yang reactive terhadap route changes
     */
    const breadcrumbItems = computed((): BreadcrumbItem[] => {
        const currentUrl = page.url.split('?')[0];
        const props = page.props as any;

        // Skip breadcrumb untuk dashboard (home page)
        if (currentUrl === '/dashboard' || currentUrl === '/') {
            return [];
        }

        const items: BreadcrumbItem[] = [];
        const visited = new Set<string>();

        /**
         * Recursive function untuk build breadcrumb chain
         */
        const buildChain = (url: string, isCurrentPage: boolean = false) => {
            if (visited.has(url)) return;
            visited.add(url);

            const match = findBreadcrumbConfig(url);
            if (!match) return;

            const { pattern, config } = match;

            // Build parent chain first
            if (config.parent) {
                buildChain(config.parent, false);
            }

            // Resolve label (bisa string atau function)
            const label = typeof config.label === 'function'
                ? config.label(props)
                : config.label;

            // Add current item
            items.push({
                label,
                href: isCurrentPage ? undefined : (config.getUrl?.() || pattern),
            });
        };

        buildChain(currentUrl, true);

        return items;
    });

    return {
        breadcrumbItems,
    };
}
