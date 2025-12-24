# Feature: AUTH-P2 - User Management (CRUD)

> **Code:** AUTH-P2 | **Priority:** High | **Status:** ✅ Complete
> **Sprint:** 1-2 | **Menu:** Admin > Manajemen User

---

## Pre-Documentation Verification

- [x] Routes verified: `php artisan route:list --path=admin/users` (8 routes found)
- [x] Vue pages exist: Index, Create, Edit
- [x] Components exist: UserTable, UserForm
- [x] Wayfinder routes generated: `@/routes/admin/users`
- [x] Following DOCUMENTATION_GUIDE.md template

---

## Overview

User Management merupakan fitur administrative yang bertujuan untuk mengelola akses pengguna ke sistem, yaitu: create user dengan auto-generated password, read dan search user dengan pagination, update informasi user (nama, email, role, status), delete user dengan confirmation, serta reset password user untuk recovery dengan iOS-like design yang dioptimasi untuk low-end devices.

## Business Requirements

### User Stories

| ID | As a | I want to | So that | Status |
|----|------|-----------|---------|--------|
| US-01 | Super Admin | Mengelola user (CRUD) untuk memberikan dan mencabut akses sistem | • List users dengan pagination<br>• Create user dengan auto-generated password<br>• Edit user (nama, email, role, status)<br>• Delete user dengan confirmation<br>• Reset password user | ✅ Complete |
| US-02 | Admin TU | Me-reset password user untuk membantu user yang lupa kredensial | • Reset password button di user table<br>• Confirmation modal<br>• Password baru dikirim ke email | ✅ Complete |

### Business Rules

| Rule ID | Rule Description | Implementation |
|---------|------------------|----------------|
| BR-01 | User management hanya accessible oleh Super Admin dan Admin TU | Middleware: `role:SUPERADMIN,ADMIN` |
| BR-02 | User tidak bisa delete diri sendiri | Frontend check `user.id === authUser.id` + backend validation |
| BR-03 | Toggle status user langsung update status ACTIVE/INACTIVE | Optimistic UI update dengan rollback on error |
| BR-04 | Reset password user generate password baru random 12 karakter | Backend logic di UserController@resetPassword |
| BR-05 | Password baru dikirim ke email user setelah reset | Email notification dengan password plain text (secure channel) |

## Technical Implementation

### Components Involved

| Layer | File | Responsibility |
|-------|------|----------------|
| Controller | `app/Http/Controllers/Admin/UserController.php` | Handle CRUD operations dengan validation |
| Request | `app/Http/Requests/Admin/StoreUserRequest.php` | Validation untuk create user |
| Request | `app/Http/Requests/Admin/UpdateUserRequest.php` | Validation untuk update user |
| Model | `app/Models/User.php` | User entity dengan relationships |
| Page | `resources/js/pages/Admin/Users/Index.vue` | List users dengan search & pagination |
| Page | `resources/js/pages/Admin/Users/Create.vue` | Form tambah user baru |
| Page | `resources/js/pages/Admin/Users/Edit.vue` | Form edit user existing |
| Component | `resources/js/components/ui/UserTable.vue` | Reusable table component |
| Component | `resources/js/components/ui/UserForm.vue` | Reusable form component |
| Routes | `resources/js/routes/admin/users/index.ts` | Wayfinder type-safe routes |

### Routes Summary

| Method | URI | Name | Controller Method | Frontend |
|--------|-----|------|------------------|----------|
| GET | `/admin/users` | `admin.users.index` | `UserController@index` | Index.vue |
| GET | `/admin/users/create` | `admin.users.create` | `UserController@create` | Create.vue |
| POST | `/admin/users` | `admin.users.store` | `UserController@store` | Form submit |
| GET | `/admin/users/{user}/edit` | `admin.users.edit` | `UserController@edit` | Edit.vue |
| PUT/PATCH | `/admin/users/{user}` | `admin.users.update` | `UserController@update` | Form submit |
| DELETE | `/admin/users/{user}` | `admin.users.destroy` | `UserController@destroy` | Action button |
| POST | `/admin/users/{user}/reset-password` | `admin.users.reset-password` | `UserController@resetPassword` | Action button |
| PATCH | `/admin/users/{user}/toggle-status` | `admin.users.toggle-status` | `UserController@toggleStatus` | Action button |

**Middleware:** `auth`, `role:SUPERADMIN,ADMIN`

> 📡 Full API documentation: [Users API](../../api/users.md)

### Database

> 📌 Menggunakan tabel `users` existing dengan fields:
> - `id`, `name`, `email`, `username`, `role`, `status`, `password`
> - `last_login_at`, `last_login_ip`, `is_first_login`
> - `created_at`, `updated_at`

## Data Structures

```typescript
// User List Request
interface UserIndexRequest {
    search?: string;          // Search by name, email, username
    role?: string;            // Filter by role
    status?: 'ACTIVE' | 'INACTIVE';  // Filter by status
    page?: number;            // Current page (default: 1)
    per_page?: number;        // Items per page (default: 50)
}

// User Create/Update Request
interface UserFormData {
    name: string;             // Full name (required)
    email: string;            // Email address (required, unique)
    username: string;         // Username (required, unique)
    role: 'SUPERADMIN' | 'PRINCIPAL' | 'ADMIN' | 'TEACHER' | 'PARENT';
    status: 'ACTIVE' | 'INACTIVE';
    password?: string;        // Auto-generated on create, optional on update
}

// User Response
interface User {
    id: number;
    name: string;
    email: string;
    username: string;
    role: string;
    status: string;
    last_login_at: string | null;
    last_login_ip: string | null;
    created_at: string;
    updated_at: string;
}
```

## UI/UX Specifications

### Index Page (List Users)

**Desktop Layout (≥768px):**
- Table view dengan columns: Name, Email, Username, Role, Status, Last Login, Actions
- Search bar di top (debounced 300ms)
- Filter dropdowns: Role, Status
- Pagination di bottom

**Mobile Layout (<768px):**
- Card-based layout (table → cards)
- Each card menampilkan: Avatar initial, Name, Email, Role badge, Status badge
- Actions di card footer
- Search bar sticky di top

**Interactive Elements:**
- Quick action buttons: Edit (pencil), Delete (trash), Reset Password (key), Toggle Status (power)
- Hover states dengan scale transform
- Loading skeleton saat fetch data
- Empty state dengan icon dan message

### Create/Edit Page (Form)

**Form Fields:**
1. Name (text input, required)
2. Email (email input, required, unique validation)
3. Username (text input, required, unique validation)
4. Role (dropdown select, 5 options)
5. Status (toggle switch, Active/Inactive)
6. Password (auto-generated display pada create, tidak editable)

**Validation:**
- Real-time validation dengan inline error messages
- Form submit disabled jika ada error
- isDirty check untuk enable save button

**Design:**
- iOS-style form dengan rounded inputs
- Floating labels
- Success/error toast notifications
- Haptic feedback pada button press

## Edge Cases & Handling

| Scenario | Detection | Handling | User Feedback |
|----------|-----------|----------|---------------|
| **Delete self** | Frontend check `user.id === authUser.id` | Disable button | Tooltip: "Anda tidak dapat menghapus akun sendiri" |
| **Empty search result** | `users.data.length === 0` | Show empty state | Icon + "Belum ada user ditemukan" |
| **Duplicate email** | Backend validation error 422 | Show inline error | "Email sudah terdaftar dalam sistem" |
| **Duplicate username** | Backend validation error 422 | Show inline error | "Username sudah digunakan" |
| **Network error** | Inertia error handler | Show error toast | "Koneksi bermasalah. Periksa internet Anda." |
| **Unauthorized access** | 403 response | Redirect to 403 page | "Anda tidak memiliki akses ke halaman ini" |
| **User already deleted** | 404 response | Show error toast | "User tidak ditemukan atau sudah dihapus" |

## Wayfinder Integration

```typescript
// Import Wayfinder routes
import { index, create, store, edit, update, destroy, resetPassword, toggleStatus } 
    from '@/routes/admin/users';

// Usage in components
<Link :href="index()">User List</Link>
<Link :href="create()">Tambah User</Link>
<Link :href="edit(user.id)">Edit</Link>

// Router methods (need .url)
router.delete(destroy(user.id).url);
router.post(resetPassword(user.id).url);
router.patch(toggleStatus(user.id).url);

// Form submission (need .url)
form.post(store().url);
form.put(update(user.id).url);
```

## iOS-like Design Implementation

### Design Standards Applied

| Standard | Implementation | Example |
|----------|----------------|---------|
| **Spring Physics** | `stiffness: 300, damping: 30` | Page entrance animations |
| **Press Feedback** | `:whileTap="{ scale: 0.97 }"` | All buttons |
| **Fake Glass** | `bg-white/95` (no blur) | Modal backgrounds |
| **Crisp Borders** | `border border-gray-200 shadow-sm` | Cards, tables, inputs |
| **Haptic Feedback** | `haptics.light()` on tap | All interactive elements |
| **Touch Targets** | Min 44x44px | Mobile buttons |

### Performance Optimizations

| Technique | Implementation | Benefit |
|-----------|----------------|---------|
| **Lazy Loading** | Pagination (50/page) | Reduced initial load |
| **Debouncing** | Search input (300ms delay) | Reduced API calls |
| **Optimistic UI** | Instant toggle status feedback | Snappy UX |
| **Skeleton Loading** | Animated placeholders | Perceived performance |

## Security Considerations

| Area | Implementation | Protection Against |
|------|----------------|-------------------|
| **Role-Based Access** | Middleware `role:SUPERADMIN,ADMIN` | Unauthorized access |
| **Self-Delete Prevention** | Frontend + backend check | Accidental lockout |
| **CSRF Protection** | Laravel Sanctum (automatic) | Cross-site request forgery |
| **XSS Prevention** | Vue automatic escaping | Script injection |
| **SQL Injection** | Eloquent ORM (parameterized) | Database attacks |
| **Audit Logging** | Activity log untuk semua actions | Non-repudiation |

## Testing

### Quick Verification

- [ ] Create user dengan auto-generated password
- [ ] Search user by name/email/username
- [ ] Filter by role dan status
- [ ] Edit user (name, email, role, status)
- [ ] Delete user dengan confirmation
- [ ] Reset password user (email sent)
- [ ] Toggle status (optimistic update)
- [ ] Cannot delete self (button disabled)
- [ ] Mobile responsive (cards layout)
- [ ] Empty state displayed ketika no results

> 📋 Full test plan: [AUTH-P2 Test Plan](../../testing/AUTH-P2-user-management-test-plan.md)

## Related Documentation

- **API Documentation:** [Users API](../../api/users.md)
- **Test Plan:** [AUTH-P2 Test Plan](../../testing/AUTH-P2-user-management-test-plan.md)
- **User Journeys:** [Authentication User Journeys - User Management Section](../../guides/auth-user-journeys.md#user-management)

## Update Triggers

Update dokumentasi ini ketika:
- [ ] Business rules berubah (access control, validation)
- [ ] Routes berubah (new endpoints, renamed)
- [ ] UI/UX flow berubah (new features, layout changes)
- [ ] Edge cases baru ditemukan

---

*Last Updated: 2025-12-23*
*Documentation Status: ✅ Complete*
*Implementation Status: ✅ Tested dan Production-Ready*




