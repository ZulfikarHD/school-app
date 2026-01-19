# [EPIC_ID]: [Epic Name] - Implementation Check Report

**Epic ID:** [EPIC_ID]  
**Epic Name:** [Epic Name]  
**Priority:** [Critical/High/Medium]  
**Story Points:** [X] ([Y] Sprints)  
**Date:** [DD MMMM YYYY]  
**Reviewer:** Mainow

---

## Executive Summary

| Status | Count |
|--------|-------|
| ✅ Implemented | X |
| ⚠️ Partial | X |
| ❌ Missing | X |
| **Total User Stories** | **X** |

**Overall Status:** [✅ **COMPLETE** / ⚠️ **PARTIAL** / ❌ **INCOMPLETE**] - [Brief summary]

---

## 📋 User Stories Coverage

### [EPIC_ID].1 - [Sub-Epic Name]

| ID | User Story | Status | Notes |
|----|------------|--------|-------|
| OS-X.X.X | [User story description] | ✅/⚠️/❌ | [Implementation notes] |
| OS-X.X.X | [User story description] | ✅/⚠️/❌ | [Implementation notes] |

### [EPIC_ID].2 - [Sub-Epic Name]

| ID | User Story | Status | Notes |
|----|------------|--------|-------|
| OS-X.X.X | [User story description] | ✅/⚠️/❌ | [Implementation notes] |

---

## 🔧 Backend Implementation Check

### 1. Model Verification

```
✅ Found: app/Models/[ModelName].php
   - Primary Key: [type]
   - Fillable fields:
     • field1
     • field2
     • field3
   
   - Relationships:
     • belongsTo [Model]
     • hasMany [Model]
   
   - Casts:
     • field → type
   
   - Helper Methods:
     • methodName()

❌ MISSING: [Expected model] - not found in app/Models
```

### 2. Migration Verification

```
✅ Found: database/migrations/[timestamp]_create_[table]_table.php
   - Columns:
     • id (type, primary)
     • column_name (type, constraints)
   
   - Indexes:
     • column_name
   
   - Foreign Keys:
     • foreign_key → table.id (cascade on delete)

❌ MISSING: Migration for [table] not found
```

### 3. Controller Verification

```
✅ Controller: app/Http/Controllers/[Path]/[Name]Controller.php
   Methods:
   - methodName() → Inertia::render('[Page]')
   - methodName(Request) → redirect route.name

   Custom Imports Check:
   ✅ App\Services\[Name]Service → EXISTS
   ✅ App\Http\Requests\[Name]Request → EXISTS
   ❌ App\Http\Requests\[Name]Request → NOT FOUND

   Security Features:
   - [Security feature implemented]
```

### 4. Service Verification

```
✅ Service: app/Services/[Path]/[Name]Service.php
   Methods:
   - methodName(params) → ReturnType
   - methodName(params) → ReturnType

   Constants:
   - CONSTANT_NAME = [value]

❌ MISSING: Expected service [Name]Service not found
```

### 5. Form Request Verification

```
✅ Form Request: app/Http/Requests/[Path]/[Name]Request.php
   Rules:
   - field_name: required, string, max:255
   - field_name: nullable, file types:[pdf, jpg], max:10MB

   Messages: Indonesian custom messages ✅

❌ MISSING: Form Request for [action] not found
```

### 6. Route Registration Verification

```
Route Analysis ([route_file].php):

✅ GET    /path/to/route            → Controller@method         (route: route.name)
✅ POST   /path/to/route            → Controller@method         (route: route.name)
✅ PUT    /path/to/route/{id}       → Controller@method         (route: route.name)
✅ DELETE /path/to/route/{id}       → Controller@method         (route: route.name)

❌ MISSING: Route for [action] not registered

Middleware Check:
✅ 'auth' middleware applied
✅ 'verified' middleware applied
✅ '[custom]' middleware applied

Wayfinder Compatibility:
✅ All routes have names defined
❌ Route [path] missing name - will break Wayfinder
```

---

## 🎨 Frontend Implementation Check

### 7. Vue Page Verification

```
Expected Vue Pages from Backend Controllers:

✅ resources/js/pages/[path]/[Page].vue - EXISTS
   Props: prop1, prop2, prop3
   Features:
   - [Feature description]
   - [Feature description]

❌ resources/js/pages/[path]/[Page].vue - NOT FOUND
```

### 8. Wayfinder Route Usage Verification

```
Wayfinder Usage Check:
✅ CORRECT: import { method } from '@/actions/App/Http/Controllers/[Path]/[Name]Controller'
✅ Using Inertia Form component with action props
✅ Route names properly imported from @/routes

Route Name Verification:
✅ 'route.name' → exists in routes/[file].php
❌ 'route.name' → NOT FOUND in routes

❌ INCORRECT: import { route } from 'ziggy-js' - MUST FIX!
```

### 9. Data Reference Verification

```
✅ CORRECT REFERENCE - [Entity Name]:
   Backend: $fillable = ['field1', 'field2', 'field3']
   Frontend: useForm({ field1, field2, field3 })

❌ INCORRECT REFERENCE:
   Frontend uses 'fieldName' but backend expects 'field_name'
   Fix: Change to snake_case
```

### 10. Tailwind v4 & Motion-V Verification

```
Tailwind v4 Check:
✅ Using Tailwind v4 classes (rounded-xl, shadow-lg, etc.)
✅ Dark mode support with dark: prefix
✅ Mobile-first responsive classes

Motion-V Check:
✅ import { Motion } from 'motion-v'
✅ Spring animations implemented
✅ Staggered entrance animations
✅ WhileTap scale effects (0.97)

Mobile-First Check:
✅ Touch-friendly button sizes (h-12, min 44px tap target)
✅ Responsive layouts
✅ Haptic feedback via useHaptic() composable
```

### 11. Sidebar/Navigation Registration

```
Navigation Check:
✅ Route registered in sidebar: [Menu Name] → [href]
✅ Active state properly detected
✅ Icon: [IconName] from lucide-vue-next

❌ MISSING: Route [name] not in sidebar - users can't access!
```

---

## 🧪 Test Coverage

### Feature Tests

```
✅ tests/Feature/[Path]/[TestName]Test.php
   Tests (X test methods):
   - test_description_one
   - test_description_two
   - test_description_three

❌ MISSING: No tests found for [feature]
```

---

## 🎯 User Access Path (Non-Technical)

### [Feature Name] User Journey

```
🎯 User Journey: [Feature Name]

Starting Point: [Where user begins]

Steps:
1. [Action description]
   Expected: [What happens]
2. [Action description]
   Expected: [What happens]
3. [Continue...]

Required Permissions:
- [Role/permission needed]
- [Authentication requirement]

Alternative Paths:
- [Alternative way to access]
- [Edge case handling]
```

---

## 📝 Manual Test Document (QA)

### Test Environment

```
- URL: http://localhost:8000 (development) atau staging URL
- Test Data: 
  - Email: test@mainow.test
  - Password: TestUser123!
- Browser: Chrome, Firefox, Safari
- Device: Desktop & Mobile (iPhone 12 viewport: 390x844)
```

### Test Case 1: Happy Path - [Scenario Name]

```
Pre-conditions:
- [ ] [Condition 1]
- [ ] [Condition 2]

Test Steps:
1. Action: [What to do]
   Expected: [What should happen]

2. Action: [What to do]
   Expected: [What should happen]

Post-conditions:
- [ ] [Expected state after test]
```

### Test Case 2: Validation Error - [Scenario Name]

```
Test Steps:
1. Action: [Trigger validation error]
   Expected: Error "[Error message in Indonesian]"

2. Action: [Trigger another error]
   Expected: Error "[Error message]"
```

### Test Case 3: Mobile Responsive

```
Device: iPhone 12 (390x844)

Test Steps:
1. Action: [Mobile-specific test]
   Expected: [Mobile-specific expectation]
```

---

## Defect Reporting Template

```
Title: [Brief description]
Severity: Critical / High / Medium / Low
Component: [EPIC_ID] - [Sub-component]
Steps to Reproduce:
1. [Step 1]
2. [Step 2]
Expected Result: [What should happen]
Actual Result: [What actually happened]
Screenshot: [Attach if applicable]
Environment:
- Browser: [e.g., Chrome 120]
- OS: [e.g., macOS 14]
- Screen: [e.g., 1920x1080 or iPhone 12]
```

---

## ✅ Verification Checklist Summary

### Backend
- [ ] Models created with proper fields and relationships
- [ ] Migrations with correct schema and indexes
- [ ] Controllers with all required methods
- [ ] Services following Service Pattern
- [ ] Form Requests with Indonesian validation messages
- [ ] Routes registered with proper names
- [ ] Middleware applied correctly
- [ ] Security features (rate limiting, validation)

### Frontend
- [ ] Vue pages exist for all Inertia::render calls
- [ ] Wayfinder used for routing (NOT Ziggy)
- [ ] Props match controller data structure
- [ ] Form fields match Form Request validation
- [ ] Field names match (snake_case)
- [ ] Tailwind v4 syntax used correctly
- [ ] Motion-V animations implemented
- [ ] Mobile-first responsive design
- [ ] Haptic feedback implemented
- [ ] Routes registered in sidebar/navigation

### Documentation
- [ ] User journey documented (Indonesian)
- [ ] Manual test cases created
- [ ] All scenarios covered (happy path, errors, edge cases)
- [ ] Mobile testing included

---

## 📌 Notes & Recommendations

### Implemented Features Summary

1. **[Feature Category 1]**
   - [Feature detail]
   - [Feature detail]

2. **[Feature Category 2]**
   - [Feature detail]
   - [Feature detail]

### Known Issues / Partial Implementations

⚠️ **[Story ID] Note**: [Description of partial implementation and recommendation]

### Security Features Implemented

- [Security feature 1]
- [Security feature 2]

---

**Document Version:** 1.0  
**Author:** Mainow  
**Last Updated:** [DD MMMM YYYY]
