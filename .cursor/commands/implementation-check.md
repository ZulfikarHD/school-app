# Implementation Verification Rules

## Purpose
These rules guide thorough verification of sprint/epic implementations for **Laravel backend + Vue 3 (Inertia) frontend** projects. **CRITICAL: Never assume files exist or are implemented correctly just because they should be - always verify with proof.**

### Tech Stack Reference
- **Backend:** Laravel (latest)
- **Frontend:** Vue 3 + Inertia.js
- **Styling:** Tailwind CSS v4
- **Routing:** Wayfinder (NOT Ziggy)
- **Animation:** Motion-V (Motion Studio)
- **Pattern:** Service Pattern (NOT Repository Pattern)
- **Timezone:** Asia/Jakarta (WIB)
- **Currency:** Rupiah (Rp)

---

## Backend Implementation Check

### 1. Model Verification
**Task:** Verify if new Eloquent models were created for this feature.

**Process:**
- Search for new `*.php` files in `app/Models` directory
- Look for class extending `Model` with fillable/guarded properties
- Check for relationships, casts, and accessors/mutators
- **Provide proof:** Show the actual model definition with filename and line numbers
- If NOT found, explicitly state: "❌ No new model found - expected but missing"

**Proof Format:**
```
✅ Found: app/Models/[ModelName].php:[line-range]
   - Fillable fields: [list fields]
   - Relationships: [list relationships]
   - Casts: [list casts if any]
❌ MISSING: [Expected model] - not found in app/Models
```

---

### 2. Migration Verification
**Task:** Verify database migrations were created for new models/features.

**Process:**
- Search for new migration files in `database/migrations`
- Check migration naming convention follows Laravel standards
- Verify table structure matches model requirements
- Check for indexes, foreign keys, and constraints
- **Provide proof:** Show migration filename and key schema definitions

**Proof Format:**
```
✅ Found: database/migrations/[timestamp]_create_[table]_table.php
   - Columns: [list key columns]
   - Foreign keys: [list FK relationships]
   - Indexes: [list indexes]
❌ MISSING: Migration for [table] not found
❌ ISSUE: Missing index on [column] / Missing FK constraint
```

---

### 3. Controller Verification
**Task:** Verify controllers created or updated for this feature.

**Process:**
- Search for `*Controller.php` files in `app/Http/Controllers`
- Identify new/modified controller methods
- **Check all custom imports** (use statements from app namespace)
- Validate each import actually exists
- Check for proper Inertia responses (`Inertia::render()`)
- **List all frontend connections:** Note Vue pages/components that receive data
- Flag potential errors: missing imports, incorrect service calls, undefined methods

**Proof Format:**
```
✅ Controller Found: app/Http/Controllers/[Name]Controller.php
   Methods: index(), create(), store(), show(), edit(), update(), destroy()

Custom Imports Check:
  ✅ App\Services\[Name]Service → exists
  ❌ App\Http\Requests\[Name]Request → FILE NOT FOUND

Inertia Render Targets:
  - Inertia::render('[Page]/Index') → resources/js/Pages/[Page]/Index.vue
  - Inertia::render('[Page]/Create') → resources/js/Pages/[Page]/Create.vue
```

---

### 4. Service Verification
**Task:** Verify service layer created or updated for this feature (Service Pattern - NOT Repository).

**Process:**
- Search for `*Service.php` files in `app/Services`
- Identify new/modified service methods
- **Check all custom imports** (app-level imports only)
- Validate import paths and file existence
- Verify service is injected into controllers properly
- **List frontend connections:** Track which Vue pages consume this service via controller
- Flag potential errors: missing models, incorrect method signatures

**Proof Format:**
```
✅ Service Found: app/Services/[Name]Service.php
   Methods: [list public methods]

Custom Imports Check:
  ✅ App\Models\[Model] → exists
  ❌ App\Models\[Model] → NOT FOUND

Service Usage:
  - Injected in: app/Http/Controllers/[Name]Controller.php
  - Methods called: [list methods used by controller]
```

---

### 5. Form Request Verification
**Task:** Verify Form Request validation classes are created.

**Process:**
- Search for `*Request.php` files in `app/Http/Requests`
- Check validation rules match frontend form fields
- Verify authorization rules are correct
- Check custom error messages (Indonesian if applicable)

**Proof Format:**
```
✅ Found: app/Http/Requests/[Store/Update][Name]Request.php
   Authorization: auth()->check() / specific permission
   Rules: [list validation rules]
   Messages: [Indonesian messages if defined]

Field Mapping:
  ✅ 'field_name' → matches Vue form field
  ❌ 'missing_field' → Vue has field but no validation rule
```

---

### 6. Route Registration Verification
**Task:** Verify all new endpoints are registered in routing configuration.

**Process:**
- Open `routes/web.php` (for Inertia routes)
- Search for route registrations matching new controllers
- **Cross-reference** every controller method found in step 3
- Check HTTP methods match (GET, POST, PUT, PATCH, DELETE)
- Verify middleware and route groups are correct
- **Check Wayfinder compatibility** (route names must be defined)

**Proof Format:**
```
Route Analysis (routes/web.php):
✅ GET    /[path]           → [Controller]@index    (route: [name].index)
✅ POST   /[path]           → [Controller]@store    (route: [name].store)
❌ DELETE /[path]/{id}      → [Controller]@destroy  - NOT REGISTERED

Middleware Check:
✅ 'auth' middleware applied
❌ 'verified' middleware missing

Wayfinder Compatibility:
✅ All routes have names defined
❌ Route [path] missing name - will break Wayfinder
```

---

## Frontend Implementation Check

### 7. Vue Page Verification
**Task:** Based on backend findings, verify Vue pages are correctly implemented.

**Process:**
- Locate Vue files from Inertia::render() calls in controllers
- Verify files exist in `resources/js/Pages`
- Check defineProps matches controller data
- Verify Wayfinder route usage (NOT Ziggy)

**Proof Format:**
```
Expected Vue Pages from Backend Controllers:
✅ resources/js/Pages/[Feature]/Index.vue - EXISTS
✅ resources/js/Pages/[Feature]/Create.vue - EXISTS
❌ resources/js/Pages/[Feature]/Show.vue - MISSING

Props Verification ([Page].vue):
  Controller sends: { [prop]: [Type] }
  Vue receives: defineProps<{ [prop]: [Type] }>()
  ✅ Matches / ❌ Mismatch: [details]
```

---

### 8. Wayfinder Route Usage Verification
**Task:** Verify Vue components use Wayfinder correctly (NOT Ziggy).

**Process:**
- Search for route usage in Vue files
- Ensure Wayfinder syntax is used: `route('route.name')` from wayfinder
- Flag any Ziggy usage as error
- Verify route names match Laravel routes

**Proof Format:**
```
Wayfinder Usage Check:
✅ CORRECT: import { route } from '@wayfinder/client'
❌ INCORRECT: import { route } from 'ziggy-js' - MUST FIX!

Route Name Verification:
  ✅ '[name].index' → exists in routes/web.php
  ❌ '[name].delete' → NOT FOUND (typo? should be 'destroy')
```

---

### 9. Data Reference Verification
**Task:** Verify Vue components reference correct backend data structures.

**Process:**
- Open each Vue file
- Check form submissions match Form Request validation
- **Verify request/response data structure** matches backend models
- Check field names match exactly (snake_case from Laravel)
- **Provide proof:** Show matching between frontend and backend

**Proof Format:**
```
✅ CORRECT REFERENCE:
   Backend: $fillable = ['user_id', 'name', 'bio']
   Frontend: useForm({ user_id: '', name: '', bio: '' })
   Route: form.post(route('[name].store')) → POST /[path] ✅

❌ INCORRECT REFERENCE:
   Frontend uses 'userName' but backend expects 'user_name'
   Fix: Change to snake_case
```

---

### 10. Tailwind v4 & Motion-V Verification
**Task:** Verify proper usage of Tailwind v4 and Motion-V animations.

**Process:**
- Check Tailwind v4 CSS syntax (new @theme directive, etc.)
- Verify Motion-V imports and usage
- Check animation implementations follow Motion-V patterns
- Ensure mobile-first responsive design

**Proof Format:**
```
Tailwind v4 Check:
✅ resources/css/app.css uses @theme directive
❌ Using old @apply syntax without v4 migration

Motion-V Check:
✅ import { Motion, AnimatePresence } from 'motion-v'
❌ MISSING: [Page].vue has no page transition animation

Mobile-First Check:
✅ Uses responsive classes: 'grid-cols-1 md:grid-cols-2'
❌ Button too small on mobile: needs min 44px tap target
```

---

### 11. Sidebar/Navigation Registration
**Task:** Verify new routes are registered in sidebar/navigation menu.

**Process:**
- Check navigation component (usually in `resources/js/Layouts` or `resources/js/Components`)
- Search for route paths matching new features
- Verify Wayfinder route names are correct
- Check permissions/guards if applicable

**Proof Format:**
```
Navigation Check (AuthenticatedLayout.vue or Sidebar.vue):
✅ Found: NavLink with route('[name].index') at line [X]
✅ Active state: route().current('[name].*')
❌ MISSING: Route '[name].index' not in sidebar - users can't access!
```

---

## User Access Path (Non-Technical)

**Task:** Document how end-users access this feature through the UI.

**Format:**
```
🎯 User Journey: [Feature Name]

Starting Point: [Where user begins]
Steps:
1. [First action - e.g., "Klik menu 'Profile' di sidebar kiri"]
2. [Second action]
3. [Continue...]
4. [Final outcome]

Alternative Paths:
- [Other ways to access]

Required Permissions:
- [Roles/permissions needed]
```

---

## Manual Test Document (QA)

**Task:** Create step-by-step manual testing guide for QA team.

**Format:**
```
# Manual Test Plan: [Feature Name]

## Test Environment
- URL: [Testing URL]
- Test Data: [Required accounts/data]
- Browser: Chrome, Firefox, Safari
- Device: Desktop & Mobile

## Test Case 1: Happy Path - [Scenario]
### Pre-conditions:
- [ ] [Condition 1]
- [ ] [Condition 2]

### Test Steps:
1. Action: [What to do]
   Expected: [What should happen]
2. Action: [Next step]
   Expected: [Result]

### Post-conditions:
- [ ] [Expected state after test]

## Test Case 2: Validation Errors
[Similar format for negative scenarios]

## Test Case 3: Mobile Responsive
[Test on mobile viewport]

## Defect Reporting Template
- Title: [Brief description]
- Severity: Critical / High / Medium / Low
- Steps to Reproduce: [Steps]
- Expected vs Actual Result
- Screenshot & Environment details
```

---

## Verification Checklist

### Backend
- [ ] New Eloquent models created and verified (with proof)
- [ ] Migrations created with proper schema
- [ ] Controllers exist with correct Inertia::render() calls
- [ ] Services exist (Service Pattern - NOT Repository)
- [ ] Form Requests created with validation rules
- [ ] All routes registered in routes/web.php
- [ ] Route names defined (required for Wayfinder)
- [ ] No import errors or missing files
- [ ] Middleware applied correctly

### Frontend
- [ ] Vue pages exist in resources/js/Pages as expected
- [ ] Wayfinder used for routing (NOT Ziggy)
- [ ] Props match controller data structure
- [ ] Form fields match Form Request validation
- [ ] Field names match (snake_case)
- [ ] Tailwind v4 syntax used correctly
- [ ] Motion-V animations implemented
- [ ] Mobile-first responsive design
- [ ] Routes registered in sidebar/navigation

### Documentation
- [ ] User journey documented (non-technical, Indonesian)
- [ ] Manual test cases created
- [ ] All scenarios covered (happy path, errors, edge cases)
- [ ] Mobile testing included

---

## Common Pitfalls to Check

1. **Wayfinder vs Ziggy:** Using Ziggy imports instead of Wayfinder - MUST use `@wayfinder/client`
2. **Case Sensitivity:** `userName` vs `user_name` - Laravel uses snake_case, be consistent
3. **Missing Routes:** Controller method exists but not registered in routes/web.php
4. **Missing Route Names:** Routes without names break Wayfinder
5. **Service Pattern Violation:** Using Repository pattern instead of Service pattern
6. **Dead Code:** Vue pages exist but not rendered by any controller
7. **Orphaned Controllers:** Backend endpoints with no frontend consumers
8. **Missing Sidebar:** Feature works but users can't find it in UI
9. **Permission Gaps:** Backend requires auth but frontend allows access
10. **Tailwind v4 Syntax:** Using old Tailwind syntax instead of v4 @theme directive
11. **Motion-V Missing:** Pages without animations (should have at least page transitions)
12. **Mobile UX Issues:** Buttons too small, forms not responsive, horizontal scroll

---

## File Structure Reference

```
app/
├── Http/
│   ├── Controllers/[Name]Controller.php
│   └── Requests/[Store/Update][Name]Request.php
├── Models/[Name].php
└── Services/[Name]Service.php

database/migrations/[timestamp]_create_[table]_table.php

resources/js/
├── Pages/[Feature]/{Index,Create,Edit,Show}.vue
├── Components/[Feature]/[Component].vue
└── Layouts/AuthenticatedLayout.vue

routes/web.php
```

---

## Usage Instructions

When given a sprint/epic implementation:

1. **Start with Backend:** Verify models → migrations → controllers → services → form requests → routes
2. **Document Evidence:** Always provide file paths and line numbers
3. **Map Connections:** Track which Vue pages receive data from which controllers
4. **Verify Frontend:** Check Vue files against backend findings
5. **Check Wayfinder:** Ensure all route usage is via Wayfinder, not Ziggy
6. **Check Styling:** Verify Tailwind v4 and Motion-V usage
7. **Test User Flow:** Document how users actually access the feature
8. **Create Test Plan:** Write detailed QA manual test cases (including mobile)
9. **Report Issues:** Flag missing files, incorrect references, registration gaps

**Remember:** 
- NEVER assume - always verify with proof!
- Service Pattern ONLY - no Repository pattern!
- Wayfinder ONLY - no Ziggy!
- Mobile-first UX is critical!

---

## These are the Epic or Sprint Files needed to be verified:
