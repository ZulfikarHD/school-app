---
name: PSB Design Consistency Fix
overview: Comprehensive design consistency fix across all pages - fixing navigation active state bug, standardizing gradient usage (pragmatic approach), and updating design skill with component references and patterns.
todos:
  - id: fix-nav-active-state
    content: Add exact matching for PSB dashboard routes in useNavigation.ts isActive function
    status: completed
  - id: fix-principal-psb-dashboard
    content: Standardize Principal/Psb/Dashboard.vue stat cards to use solid colors for regular cards
    status: completed
  - id: fix-principal-academic
    content: Fix Principal/Academic/Dashboard.vue gradient violations
    status: completed
  - id: fix-principal-financial
    content: Fix Principal/Financial/Reports.vue gradient violations
    status: completed
  - id: fix-parent-children
    content: Fix Parent/Children/Index.vue gradient violations
    status: completed
  - id: fix-parent-payments
    content: Fix Parent/Payments/Index.vue gradient violations
    status: completed
  - id: fix-parent-psb-reregister
    content: Standardize Parent/Psb/ReRegister.vue (keep hero gradient, fix regular cards)
    status: completed
  - id: fix-parent-psb-welcome
    content: Standardize Parent/Psb/Welcome.vue (keep hero gradient, fix regular elements)
    status: completed
  - id: fix-psb-landing
    content: Review Psb/Landing.vue (hero gradient acceptable, check other elements)
    status: completed
  - id: update-design-skill
    content: Update ios-design-standard/SKILL.md with pragmatic patterns and component references
    status: completed
isProject: false
---

# Comprehensive Design Consistency Fix

## Problem Summary

1. **Navigation Active State Bug**: PSB Dashboard menu item stays active when on other PSB pages
2. **Gradient Inconsistencies**: ~10+ pages use gradients where solid colors should be used
3. **Design Skill Incomplete**: Missing component references and pragmatic usage guidelines

---

## Design Standard (Pragmatic Approach)

### When Gradients ARE Allowed

- Hero sections / landing page headers
- Featured/highlight cards (max 1 per page)
- Primary CTA buttons on landing pages

### When Solid Colors MUST Be Used

- Regular stat cards
- Icon containers inside cards
- List item indicators
- Secondary buttons
- Navigation elements

---

## Fix 1: Navigation Active State Bug

**File**: `[resources/js/composables/useNavigation.ts](resources/js/composables/useNavigation.ts)`

Add exact matching for dashboard routes in `isActive` function (around line 206):

```typescript
// PSB Dashboard routes - exact matching to prevent child routes from activating parent
if (route === 'admin.psb.index') {
    return currentUrl === '/admin/psb';
}

if (route === 'principal.psb.dashboard') {
    return currentUrl === '/principal/psb';
}
```

---

## Fix 2: Principal/Psb/Dashboard.vue

**File**: `[resources/js/pages/Principal/Psb/Dashboard.vue](resources/js/pages/Principal/Psb/Dashboard.vue)`

**Current** (lines 78-127): Uses `gradient` property for stat cards
**Fix**: Replace with `color` property and use solid color pattern

Changes:

1. Remove `gradient` property from `statCards`
2. Add `color` property (emerald, amber, blue, red, purple, teal)
3. Update template to use solid backgrounds for icon containers
4. Keep header icon gradient (acceptable for featured element)

---

## Fix 3: Principal/Academic/Dashboard.vue

**File**: `[resources/js/pages/Principal/Academic/Dashboard.vue](resources/js/pages/Principal/Academic/Dashboard.vue)`

**Issue** (line 165): `bg-linear-to-br from-indigo-400 to-purple-600`
**Fix**: Replace with `bg-indigo-500` (solid)

---

## Fix 4: Principal/Financial/Reports.vue

**File**: `[resources/js/pages/Principal/Financial/Reports.vue](resources/js/pages/Principal/Financial/Reports.vue)`

**Issues**:

- Line 320: Icon container gradient
- Line 417: Summary card gradient

**Fix**: Replace icon containers with solid `bg-emerald-500`, keep hero card gradient if needed

---

## Fix 5: Parent/Children/Index.vue

**File**: `[resources/js/pages/Parent/Children/Index.vue](resources/js/pages/Parent/Children/Index.vue)`

**Issue** (line 37): `bg-gradient-to-br from-blue-500 to-blue-600`
**Fix**: Replace with `bg-blue-500` (solid)

---

## Fix 6: Parent/Payments/Index.vue

**File**: `[resources/js/pages/Parent/Payments/Index.vue](resources/js/pages/Parent/Payments/Index.vue)`

**Issues**:

- Line 440: Icon container gradient
- Line 517: Summary card gradient
- Line 1088: Amount card gradient

**Fix**: Replace icon containers with solid colors, evaluate summary cards

---

## Fix 7: Parent/Psb/ReRegister.vue

**File**: `[resources/js/pages/Parent/Psb/ReRegister.vue](resources/js/pages/Parent/Psb/ReRegister.vue)`

**Issue** (line 282): Congratulations banner gradient
**Decision**: Keep as hero/featured element (acceptable)
**Check**: Ensure other elements use solid colors

---

## Fix 8: Parent/Psb/Welcome.vue

**File**: `[resources/js/pages/Parent/Psb/Welcome.vue](resources/js/pages/Parent/Psb/Welcome.vue)`

**Issues**:

- Line 105: Trophy icon gradient
- Line 155: Student info card gradient
- Line 272: CTA button gradient

**Decision**: Keep trophy icon gradient (hero), fix others

---

## Fix 9: Psb/Landing.vue

**File**: `[resources/js/pages/Psb/Landing.vue](resources/js/pages/Psb/Landing.vue)`

**Issue** (line 103): Hero section gradient
**Decision**: Keep as landing page hero (acceptable)

---

## Fix 10: Update Design Skill

**File**: `[.cursor/skills/ios-design-standard/SKILL.md](.cursor/skills/ios-design-standard/SKILL.md)`

Add new sections:

### A. Reference Documentation Links

```markdown
## Reference Documentation
- Full Design Guide: `docs/guides/ios-design-system.md`
- Navigation Guide: `docs/guides/navigation-design-system.md`
- UI Components Guide: `docs/guides/ui-components.md`
```

### B. Existing UI Components Table

```markdown
## Reusable UI Components
| Component | Path | Usage |
|-----------|------|-------|
| Badge | `@/components/ui/Badge.vue` | Status badges, counts |
| DialogModal | `@/components/ui/DialogModal.vue` | Confirmations |
| BaseModal | `@/components/ui/BaseModal.vue` | Generic modals |
| Alert | `@/components/ui/Alert.vue` | Toast notifications |
| Breadcrumb | `@/components/ui/Breadcrumb.vue` | Navigation breadcrumbs |
| Form/* | `@/components/ui/Form/*.vue` | Form inputs |
```

### C. Pragmatic Gradient Guidelines

```markdown
## Gradient vs Solid Colors (Pragmatic Approach)

### Gradients Allowed
- Hero sections on landing pages
- Featured/highlight card (max 1 per page)
- Primary CTA on landing/marketing pages

### Solid Colors Required
- Regular stat cards: `bg-{color}-50` background, `bg-{color}-500` icon
- Icon containers in cards: `bg-{color}-100` or `bg-{color}-500`
- Secondary elements
- Navigation items
```

### D. Dashboard Card Pattern

```markdown
## Dashboard Card Patterns

### Stat Card (Solid - Default)
```vue
<div class="rounded-2xl border shadow-sm p-4 bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800">
    <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center">
        <Icon class="w-5 h-5 text-white" />
    </div>
</div>
```

### Featured Card (Gradient - Sparingly)

```vue
<div class="rounded-2xl shadow-lg p-6 bg-linear-to-br from-emerald-500 to-teal-600 text-white">
    <!-- Only for hero/featured elements -->
</div>
```

### Color Helper Pattern

```typescript
const getColorClasses = (color: string) => ({
    bg: `bg-${color}-50 dark:bg-${color}-950/30`,
    border: `border-${color}-200 dark:border-${color}-800`,
    icon: `bg-${color}-500`,
    text: `text-${color}-600 dark:text-${color}-400`,
});
```

```

---

## Implementation Order

1. **Fix navigation bug** (critical UX)
2. **Fix Principal pages** (3 files)
3. **Fix Parent pages** (4 files)
4. **Review PSB Landing** (likely no changes)
5. **Update design skill** (document patterns)

---

## Files to Modify

| Priority | File | Changes |
|----------|------|---------|
| P0 | `composables/useNavigation.ts` | Add exact matching for PSB routes |
| P1 | `Principal/Psb/Dashboard.vue` | Convert stat cards to solid colors |
| P1 | `Principal/Academic/Dashboard.vue` | Fix gradient icons |
| P1 | `Principal/Financial/Reports.vue` | Fix gradient icons/cards |
| P2 | `Parent/Children/Index.vue` | Fix gradient icon |
| P2 | `Parent/Payments/Index.vue` | Fix gradient icons/cards |
| P2 | `Parent/Psb/ReRegister.vue` | Review (hero OK) |
| P2 | `Parent/Psb/Welcome.vue` | Fix non-hero gradients |
| P3 | `.cursor/skills/ios-design-standard/SKILL.md` | Add patterns & refs |
```

