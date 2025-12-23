# 🎨 iOS-like Design System (Performance Optimized)

> **Last Updated:** 2024-12-23
> **Status:** ✅ Implemented
> **Version:** 1.0

---

## Overview

iOS-like Design System merupakan panduan desain frontend yang bertujuan untuk memberikan pengalaman visual modern seperti Apple iOS namun dioptimalkan untuk perangkat low-end (target market UMKM), yaitu: mengurangi beban GPU dengan menghindari heavy blur effects, menggunakan spring animations yang lebih snappy (stiffness: 300), dan menerapkan "Fake Glass" technique dengan high opacity backgrounds.

---

## 🎯 Design Philosophy

### Core Principles

| Principle | Implementation | Rationale |
|-----------|---------------|-----------|
| **Function > Fashion** | Simple transitions over complex animations | Low-end device performance |
| **"Calm Tech"** | Subtle, purposeful animations | Reduce cognitive load |
| **High Performance** | No heavy blur, crisp borders | 60fps on low-end Android |
| **Haptic Feedback** | Vibration API for tactile response | Premium feel without cost |

---

## 🎨 Visual Design Standards

### Color Palette

#### Light Mode
- **Primary**: `blue-600` (#2563eb)
- **Background**: `gray-50` (#f9fafb)
- **Surface**: `white` (#ffffff)
- **Text Primary**: `gray-900` (#111827)
- **Text Secondary**: `gray-600` (#4b5563)
- **Border**: `gray-100` / `gray-200`

#### Dark Mode
- **Primary**: `blue-500` (#3b82f6)
- **Background**: `zinc-950` (#09090b)
- **Surface**: `zinc-900` (#18181b)
- **Text Primary**: `white` (#ffffff)
- **Text Secondary**: `gray-400` (#9ca3af)
- **Border**: `zinc-800` / `zinc-700`

### Typography

| Element | Class | Font Size | Weight |
|---------|-------|-----------|--------|
| Heading 1 | `text-3xl` | 30px | `font-bold` (700) |
| Heading 2 | `text-xl` | 20px | `font-bold` (700) |
| Body Text | `text-base` | 16px | `font-normal` (400) |
| Caption | `text-sm` | 14px | `font-medium` (500) |
| Small Text | `text-xs` | 12px | `font-medium` (500) |

**Font Family:** System Sans (San Francisco/Roboto) - 0ms load time

### Spacing

**Generous Padding:** Use `p-4` / `p-6` untuk memberikan whitespace yang cukup.

| Size | Class | Pixels | Use Case |
|------|-------|--------|----------|
| XS | `p-2` | 8px | Compact elements |
| SM | `p-4` | 16px | Cards, buttons |
| MD | `p-6` | 24px | Sections, containers |
| LG | `p-8` | 32px | Page padding |

---

## 🚀 Performance Optimization

### 1. "Fake Glass" Technique

❌ **AVOID:**
```css
/* Heavy blur - kills FPS on low-end devices */
backdrop-blur-xl
bg-white/50
```

✅ **USE INSTEAD:**
```css
/* High opacity + crisp border */
bg-white/95
border border-gray-100
shadow-sm

/* Dark mode */
dark:bg-zinc-900/95
dark:border-zinc-800
```

**Rationale:** High opacity (95%+) backgrounds with clean borders memberikan visual hierarchy yang sama tanpa expensive blur computation.

### 2. Crisp Borders > Heavy Shadows

❌ **AVOID:**
```css
shadow-2xl        /* Large spread, causes layout thrashing */
shadow-xl
```

✅ **USE INSTEAD:**
```css
shadow-sm         /* Subtle shadow */
border border-gray-200
hover:shadow-md   /* Only on hover */
```

### 3. Spring Animation Tuning

**Old (Slower):**
```typescript
{
  type: 'spring',
  stiffness: 400-500,  // Too bouncy
  damping: 30
}
```

**New (Snappy):**
```typescript
{
  type: 'spring',
  stiffness: 300,      // Faster, tighter
  damping: 25-30       // More controlled
}
```

**Impact:** Reduces animation time by ~30%, feels more responsive on low-end devices.

---

## 🎭 Component Patterns

### Buttons

```vue
<Motion :whileTap="{ scale: 0.97 }" :transition="{ type: 'spring', stiffness: 300, damping: 25 }">
  <button
    @click="handleClick"
    class="rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white 
           shadow-sm transition-all duration-200 hover:bg-blue-700 
           focus:outline-none focus:ring-4 focus:ring-blue-500/50"
  >
    Button Text
  </button>
</Motion>
```

**Key Points:**
- `scale: 0.97` pada tap (press feedback)
- `rounded-xl` untuk modern look
- `shadow-sm` bukan `shadow-lg`
- `ring-4` untuk focus state dengan opacity 50%

### Cards

```vue
<div class="rounded-2xl bg-white shadow-sm border border-gray-100 
            transition-all hover:shadow-md 
            dark:bg-zinc-900 dark:border-zinc-800">
  <div class="p-6">
    <!-- Card content -->
  </div>
</div>
```

**Key Points:**
- `rounded-2xl` untuk consistency
- `shadow-sm` + `border` approach
- Hover: hanya subtle shadow increase
- Dark mode: `zinc` color palette

### Modals

```vue
<div class="rounded-3xl bg-white/95 shadow-xl border border-gray-100 
            dark:bg-zinc-900/95 dark:border-zinc-800">
  <!-- No backdrop-blur! -->
</div>
```

**Key Points:**
- 95% opacity (bukan 80% dengan blur)
- Solid shadows
- Crisp borders

---

## 🎬 Animation Standards

### Press Feedback (Crucial)

**ALL clickable elements MUST have press feedback:**

```vue
:whileTap="{ scale: 0.97 }"
:transition="{ type: 'spring', stiffness: 300, damping: 25 }"
```

**Mobile active state:**
```css
active:bg-gray-100
```

### Hover Effects (Desktop Only)

```vue
:whileHover="{ scale: 1.01, y: -2 }"  /* Subtle lift */
```

**CSS fallback:**
```css
hover:shadow-md transition-all duration-200
```

### Page Transitions

**Fade + Slide:**
```vue
:initial="{ opacity: 0, y: 20 }"
:animate="{ opacity: 1, y: 0 }"
:transition="{ type: 'spring', stiffness: 300, damping: 30 }"
```

❌ **DO NOT** animate long lists (>10 items) dengan complex springs.

---

## 📱 Mobile-First Guidelines

### Touch Targets

**Minimum size:** 44x44px untuk SEMUA clickable elements.

```css
min-h-[44px] min-w-[44px]
```

### Responsive Spacing

```css
px-4 py-3        /* Mobile */
sm:px-6 sm:py-4  /* Tablet+ */
```

### Bento Grid Layout

Break complex data into Cards/Widgets untuk mobile readability.

```vue
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
  <!-- Card items -->
</div>
```

---

## ✨ Haptic Feedback

### Implementation

```typescript
import { useHaptics } from '@/composables/useHaptics';

const haptics = useHaptics();

haptics.light();    // 10ms - Tap, Tab switch
haptics.medium();   // 20ms - Success, Submit  
haptics.heavy();    // 40ms - Delete, Error
```

### Usage Guidelines

| Action | Haptic | Example |
|--------|--------|---------|
| Button tap | `light()` | Navigation, selections |
| Form submit | `medium()` | Save, update actions |
| Destructive action | `heavy()` | Delete confirmation |
| Success feedback | `success()` | Data saved (double pulse) |
| Error | `error()` | Validation failed (triple pulse) |

---

## 🎯 Reusable Components

### Location
`resources/js/components/ui/`

### Standard Components

| Component | Use Case | Import |
|-----------|----------|--------|
| `Alert.vue` | Toast notifications | `@/components/ui/Alert.vue` |
| `BaseModal.vue` | Generic modals | `@/components/ui/BaseModal.vue` |
| `DialogModal.vue` | Confirmations | `@/components/ui/DialogModal.vue` |
| `AppLayout.vue` | Page layout | `@/components/layouts/AppLayout.vue` |

### Composables

| Composable | Purpose | Location |
|------------|---------|----------|
| `useHaptics` | Vibration feedback | `@/composables/useHaptics.ts` |
| `useModal` | Modal state management | `@/composables/useModal.ts` |
| `useSpringAnimations` | Animation presets | `@/composables/useSpringAnimations.ts` |
| `useTransition` | Transition helpers | `@/composables/useTransition.ts` |

---

## 🎨 Design Tokens

### Border Radius

| Size | Class | Value | Use Case |
|------|-------|-------|----------|
| Medium | `rounded-xl` | 12px | Buttons, inputs |
| Large | `rounded-2xl` | 16px | Cards, sections |
| Extra Large | `rounded-3xl` | 24px | Modals, overlays |

### Shadows

| Level | Class | Use Case |
|-------|-------|----------|
| Subtle | `shadow-sm` | Cards, buttons |
| Medium | `shadow-md` | Hover states |
| Large | `shadow-lg` | Alerts (non-moving) |
| Extra Large | `shadow-xl` | Modals |

**Rule:** Never use `shadow-2xl` pada moving elements.

---

## ✅ Component Checklist

Before committing any new component:

- [ ] "Fake Glass" applied (95%+ opacity, no heavy blur)
- [ ] Borders used instead of heavy shadows
- [ ] Press feedback (0.97 scale) on interactive elements
- [ ] Haptic feedback integrated
- [ ] Skeleton loading for data fetching (if applicable)
- [ ] Text contrast checked (WCAG AA minimum)
- [ ] Mobile touch targets ≥ 44px
- [ ] Spring animations use `stiffness: 300, damping: 25-30`
- [ ] Dark mode support with `zinc` palette
- [ ] Linting passed (`yarn run lint`)

---

## 🚫 Anti-Patterns to Avoid

| ❌ DON'T | ✅ DO |
|----------|-------|
| `backdrop-blur-xl` on modals | `bg-white/95` + border |
| `shadow-2xl` on cards | `shadow-sm` + `border` |
| `from-blue-600 to-indigo-600` gradients | Solid `bg-blue-600` |
| `stiffness: 500` animations | `stiffness: 300` |
| Complex animations on lists | Simple fade/slide |
| `hover:scale-1.05` | `hover:scale-1.01` (subtle) |

---

## 📊 Performance Metrics

### Target Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| Frame rate | ≥ 60 FPS | Chrome DevTools Performance |
| Animation smoothness | No jank | motion-v built-in |
| Paint time | < 16ms | Chrome DevTools |
| Time to Interactive | < 3s | Lighthouse |

### Testing on Low-End Devices

Simulate low-end Android:
1. Chrome DevTools → Performance
2. CPU: 4x slowdown
3. Network: Fast 3G
4. Test all animations and transitions

---

## 🔄 Migration from Old Design

### Key Changes

| Old | New | Reason |
|-----|-----|--------|
| `bg-white/80 backdrop-blur-xl` | `bg-white/95 border-gray-100` | Performance |
| `shadow-lg` | `shadow-sm` | Less GPU usage |
| `dark:bg-gray-900` | `dark:bg-zinc-950` | Better dark contrast |
| `stiffness: 400-500` | `stiffness: 300` | Snappier feel |
| `bg-gradient-to-r` headers | Solid `bg-white` + border | Cleaner, faster |

---

## 📚 Related Documentation

- **Component README:** [UI Components](../../resources/js/components/ui/README.md)
- **Design Standard:** [.cursor/rules/design-standard.mdc](../../.cursor/rules/design-standard.mdc)
- **Wayfinder Migration:** [Wayfinder Guide](./wayfinder-migration.md)

---

## 📝 Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2024-12-23 | Initial iOS-like Design System implementation |

---

**Formula:** Snappy Interactions + Haptic + High Contrast = Premium User Trust

*Dokumentasi ini memastikan konsistensi visual dan performa optimal di seluruh aplikasi.*

