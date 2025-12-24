# Feature: AUTH-P5 - Audit Log Viewing

> **Code:** AUTH-P5 | **Priority:** Medium | **Status:** ✅ Complete
> **Sprint:** 1-2 | **Menu:** Admin > Audit Log / Reports > Activity Log

---

## Pre-Documentation Verification

- [x] Routes verified: `php artisan route:list --path=audit-log` (2 routes found)
- [x] Vue page exists: Admin/AuditLogs/Index
- [x] Component exists: AuditLogTable
- [x] Wayfinder routes generated: `@/routes/admin/audit-logs`
- [x] Following DOCUMENTATION_GUIDE.md template

---

## Overview

Audit Log Viewing merupakan fitur compliance dan monitoring yang bertujuan untuk memberikan transparansi aktivitas sistem kepada administrator dan principal, yaitu: view log aktivitas dengan pagination, filter by date range dengan presets, filter by user dan action type, filter by status (success/failed), expandable rows untuk melihat old/new values, dan color-coded status badges dengan iOS-like design yang dioptimasi untuk low-end devices.

## Business Requirements

### User Stories

| ID | As a | I want to | So that | Status |
|----|------|-----------|---------|--------|
| US-06 | Super Admin / Admin TU | Melihat audit log untuk monitoring aktivitas sistem | • Audit log table dengan pagination<br>• Filter by date range, user, action, status<br>• Expandable rows (old/new values)<br>• Color-coded status badges | ✅ Complete |
| US-07 | Principal | Melihat audit log (read-only) untuk transparansi aktivitas | • Same view as admin<br>• No edit/delete capabilities<br>• Filter by date range | ✅ Complete |

### Business Rules

| Rule ID | Rule Description | Implementation |
|---------|------------------|----------------|
| BR-01 | Audit log hanya boleh dilihat oleh Super Admin, Admin TU, dan Principal | Role-based access control: `role:SUPERADMIN,ADMIN,PRINCIPAL` |
| BR-02 | Principal hanya bisa view, tidak bisa edit/delete log | Read-only UI untuk Principal role |
| BR-03 | Log activities include: login, logout, create, update, delete user | Activity types tercatat di ActivityLog model |
| BR-04 | Log mencatat old values dan new values untuk update actions | JSON fields: `old_values`, `new_values` |
| BR-05 | Log mencatat IP address dan user agent | Fields: `ip_address`, `user_agent` |

## Technical Implementation

### Components Involved

| Layer | File | Responsibility |
|-------|------|----------------|
| Controller | `app/Http/Controllers/Admin/AuditLogController.php` | Handle index dengan filters & pagination |
| Model | `app/Models/ActivityLog.php` | Activity log entity dengan relationships |
| Page | `resources/js/pages/Admin/AuditLogs/Index.vue` | List audit logs dengan filters |
| Component | `resources/js/components/ui/AuditLogTable.vue` | Reusable table dengan expandable rows |
| Routes | `resources/js/routes/admin/audit-logs/index.ts` | Wayfinder type-safe routes |

### Routes Summary

| Method | URI | Name | Controller Method | Access |
|--------|-----|------|------------------|--------|
| GET | `/admin/audit-logs` | `admin.audit-logs.index` | `AuditLogController@index` | SUPERADMIN, ADMIN |
| GET | `/audit-logs` | `audit-logs.index` | `AuditLogController@index` | PRINCIPAL (read-only) |

**Middleware:** `auth`, role-specific (`role:SUPERADMIN,ADMIN` atau `role:PRINCIPAL`)

> 📡 Full API documentation: [Authentication API - Audit Log Section](../../api/authentication.md#audit-logs)

### Database

> 📌 Menggunakan tabel `activity_logs` dengan columns:
> - `id`, `user_id`, `action`, `description`, `model_type`, `model_id`
> - `old_values` (JSON), `new_values` (JSON)
> - `ip_address`, `user_agent`, `status`
> - `created_at`, `updated_at`

## Data Structures

```typescript
// Audit Log Index Request
interface AuditLogIndexRequest {
    date_from?: string;           // Filter start date (YYYY-MM-DD)
    date_to?: string;             // Filter end date (YYYY-MM-DD)
    user_id?: number;             // Filter by specific user
    action?: string[];            // Filter by action types (multi-select)
    status?: 'success' | 'failed';  // Filter by status
    page?: number;                // Current page (default: 1)
    per_page?: number;            // Items per page (default: 50)
}

// Audit Log Response
interface ActivityLog {
    id: number;
    user_id: number;
    user: {
        id: number;
        name: string;
        email: string;
        role: string;
    };
    action: string;               // 'login', 'logout', 'create', 'update', 'delete'
    description: string;          // Human-readable description
    model_type: string | null;    // e.g., 'App\Models\User'
    model_id: number | null;      // e.g., user ID
    old_values: Record<string, any> | null;  // Before update (JSON)
    new_values: Record<string, any> | null;  // After update (JSON)
    ip_address: string;
    user_agent: string;
    status: 'success' | 'failed';
    created_at: string;           // ISO 8601 timestamp
}
```

## UI/UX Specifications

### Audit Log Index Page

**Desktop Layout (≥768px):**
- Filter section di top dengan 4 filter fields:
  - Date range picker (with presets: Today, Last 7 days, Last 30 days, Custom)
  - User dropdown (searchable)
  - Action multi-select (login, logout, create, update, delete)
  - Status dropdown (All, Success, Failed)
- Table view dengan columns: Timestamp, User, Action, Description, Status, Details (expand icon)
- Expandable rows menampilkan old/new values (JSON diff viewer)
- Pagination di bottom

**Mobile Layout (<768px):**
- Filter toggleable (collapsed by default)
- Card-based layout (table → cards)
- Each card: User avatar, Action badge, Timestamp, Status badge, Expand button
- Expandable detail section di dalam card

**Interactive Elements:**
- Filter dropdowns dengan auto-apply
- Date presets untuk quick select
- Expandable rows dengan smooth slide animation
- Status badges dengan color coding:
  - Success: Green (`bg-green-100 text-green-800`)
  - Failed: Red (`bg-red-100 text-red-800`)
- Action badges dengan icon + text
- Loading skeleton saat fetch data
- Empty state dengan icon dan message

### Date Range Presets

| Preset | Date Range |
|--------|------------|
| Today | Start: today 00:00, End: now |
| Last 7 days | Start: 7 days ago, End: now |
| Last 30 days | Start: 30 days ago, End: now |
| This Month | Start: first day of month, End: now |
| Custom | User picks start & end date |

### Old/New Values Display

**Format:**
- Side-by-side comparison (desktop)
- Stacked comparison (mobile)
- Highlight changed fields dengan color coding
- JSON pretty-print dengan indentation
- Null values shown as "-" (dash)

## Edge Cases & Handling

| Scenario | Detection | Handling | User Feedback |
|----------|-----------|----------|---------------|
| **No data in audit log** | `logs.data.length === 0` | Show empty state | Icon + "Belum ada log aktivitas" |
| **No results from filter** | `logs.data.length === 0` after filter | Show empty state | "Tidak ada log yang sesuai dengan filter" |
| **Invalid date range** | Frontend validation | Disable apply | "Tanggal akhir tidak boleh sebelum tanggal awal" |
| **Unauthorized access** | 403 response | Redirect to 403 page | "Anda tidak memiliki akses ke halaman ini" |
| **Network error** | Inertia error handler | Show error toast | "Koneksi bermasalah. Periksa internet Anda." |
| **Large JSON values** | Frontend detection | Collapsible with "Show more" | Prevent layout break |

## Wayfinder Integration

```typescript
// Import Wayfinder routes
import { index } from '@/routes/admin/audit-logs';
// OR for Principal role
import { index as auditIndex } from '@/routes/audit-logs';

// Usage in components
<Link :href="index()">Audit Log</Link>

// With filters (query params)
router.get(index().url, {
    date_from: '2025-12-01',
    date_to: '2025-12-23',
    user_id: 5,
    action: ['create', 'update'],
    status: 'success'
});
```

## iOS-like Design Implementation

### Design Standards Applied

| Standard | Implementation | Example |
|----------|----------------|---------|
| **Spring Physics** | `stiffness: 300, damping: 30` | Page entrance, row expand |
| **Press Feedback** | `:whileTap="{ scale: 0.97 }"` | Filter buttons, expand buttons |
| **Fake Glass** | `bg-white/95` (no blur) | Filter section background |
| **Crisp Borders** | `border border-gray-200 shadow-sm` | Cards, table, inputs |
| **Haptic Feedback** | `haptics.light()` on tap | Row expand, filter apply |
| **Touch Targets** | Min 44x44px | Mobile buttons |

### Animation Patterns

```vue
<!-- Expandable Row Animation -->
<Motion 
    :initial="{ height: 0, opacity: 0 }"
    :animate="{ height: 'auto', opacity: 1 }"
    :exit="{ height: 0, opacity: 0 }"
    :transition="{ type: 'spring', stiffness: 300, damping: 30 }"
>
    <div class="expanded-content">
        <!-- Old/New values JSON -->
    </div>
</Motion>

<!-- Status Badge Pulse (for failed) -->
<Motion 
    :animate="{ scale: [1, 1.05, 1] }"
    :transition="{ duration: 2, repeat: Infinity }"
>
    <span class="badge-failed">Failed</span>
</Motion>
```

## Security Considerations

| Area | Implementation | Protection Against |
|------|----------------|-------------------|
| **Role-Based Access** | Middleware + policy checks | Unauthorized access |
| **Read-Only for Principal** | UI restriction (no edit/delete buttons) | Accidental data modification |
| **XSS Prevention** | Vue automatic escaping + JSON sanitization | Script injection |
| **SQL Injection** | Eloquent ORM (parameterized) | Database attacks |
| **Sensitive Data Masking** | Password fields masked in old/new values | Data leakage |
| **Activity Logging** | All access logged | Audit trail for compliance |

## Performance Optimizations

| Technique | Implementation | Benefit |
|-----------|----------------|---------|
| **Lazy Loading** | Pagination (50/page) | Reduced initial load |
| **Indexed Queries** | Database indexes on user_id, action, created_at | Fast filtering |
| **JSON Lazy Loading** | Old/new values only loaded when expanded | Reduced payload |
| **Debounced Search** | User dropdown search debounced 300ms | Reduced API calls |
| **Skeleton Loading** | Animated placeholders | Perceived performance |

## Testing

### Quick Verification

- [ ] List audit logs dengan pagination
- [ ] Filter by date range (presets work)
- [ ] Filter by user (searchable dropdown)
- [ ] Filter by action (multi-select)
- [ ] Filter by status (success/failed)
- [ ] Expand row shows old/new values
- [ ] Status badges show correct colors
- [ ] Empty state displayed ketika no data
- [ ] Mobile responsive (cards layout)
- [ ] Principal role: read-only access
- [ ] Admin role: full access

> 📋 Full test plan: [AUTH-P5 Test Plan](../../testing/AUTH-P5-audit-logs-test-plan.md)

## Related Documentation

- **API Documentation:** [Authentication API - Audit Logs](../../api/authentication.md#audit-logs)
- **Test Plan:** [AUTH-P5 Test Plan](../../testing/AUTH-P5-audit-logs-test-plan.md)
- **User Journeys:** [Authentication User Journeys - Audit Log Section](../../guides/auth-user-journeys.md#audit-logs)

## Future Enhancements

| Enhancement | Priority | Complexity | Estimated Effort |
|-------------|----------|------------|------------------|
| Export audit log to CSV | Nice to have | Low | 2 hours |
| Advanced search (JSON filter) | Nice to have | High | 8 hours |
| Real-time log updates (WebSocket) | Low | High | 16 hours |
| Log retention policy (auto-delete old logs) | Medium | Medium | 4 hours |

## Update Triggers

Update dokumentasi ini ketika:
- [ ] New action types ditambahkan
- [ ] Filter options berubah
- [ ] Access control rules berubah
- [ ] UI/UX flow berubah
- [ ] New edge cases ditemukan

---

*Last Updated: 2025-12-23*
*Documentation Status: ✅ Complete*
*Implementation Status: ✅ Tested dan Production-Ready*




