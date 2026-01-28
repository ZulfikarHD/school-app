---
name: ios-design-standard
description: iOS-like design system for Vue/Tailwind components. Use when creating UI components, styling forms, modals, buttons, or when the user asks about design patterns, colors, typography, or mobile-first responsive design.
---

# iOS-like Design Standard

**Philosophy:** Clean, Solid, Performant. Function > Fashion.
**Target:** Low-end devices (UMKM market) with premium feel.

## Reference Documentation

- **Full Design Guide**: `docs/guides/ios-design-system.md`
- **Navigation Guide**: `docs/guides/navigation-design-system.md`
- **UI Components Guide**: `docs/guides/ui-components.md`

## Reusable UI Components

| Component | Path | Usage |
|-----------|------|-------|
| Badge | `@/components/ui/Badge.vue` | Status badges, counts |
| DialogModal | `@/components/ui/DialogModal.vue` | Confirmations |
| BaseModal | `@/components/ui/BaseModal.vue` | Generic modals |
| Alert | `@/components/ui/Alert.vue` | Toast notifications |
| Breadcrumb | `@/components/ui/Breadcrumb.vue` | Navigation breadcrumbs |
| FormInput | `@/components/ui/Form/FormInput.vue` | Text inputs |
| FormSelect | `@/components/ui/Form/FormSelect.vue` | Dropdowns |
| FormTextarea | `@/components/ui/Form/FormTextarea.vue` | Multiline text |
| FormNumberInput | `@/components/ui/Form/FormNumberInput.vue` | Number inputs |
| FormCheckbox | `@/components/ui/Form/FormCheckbox.vue` | Checkboxes |

## Package Dependencies

| Package | Purpose |
|---------|---------|
| `motion-v` | Animation library (AnimatePresence, Motion components) |
| `vue-chartjs` | Chart components (Bar, Line, Pie, Doughnut) |
| `lucide-vue-next` | Icon library |

## Mobile-First Essentials

- **Touch Targets:** Min 44x44px
- **Loading:** Skeleton or spinner
- **Empty States:** Illustration + message
- **Images:** `loading="lazy"`

## Design Patterns

### Gradient vs Solid Colors (Pragmatic Approach)

**Gradients Allowed** (max 1-2 per page):
- Hero sections on landing pages
- Featured/highlight card (primary metric)
- Primary CTA on landing/marketing pages
- Page header icons (1 per page)

**Solid Colors Required**:
- Regular stat cards: `bg-{color}-50` background, `bg-{color}-500` icon
- Icon containers in cards: `bg-{color}-100` or `bg-{color}-500`
- Secondary elements and buttons
- Navigation items
- Modal content cards

### Solid Surfaces (No Glass)
```
Navbar/Footer: bg-white border-b border-slate-200 shadow-sm
Cards:         bg-white border border-slate-200 rounded-xl shadow-sm
Modals:        bg-white rounded-2xl shadow-xl border border-slate-200
```

### Motion (motion-v)

```vue
<script setup>
import { Motion, AnimatePresence } from 'motion-v';
</script>

<!-- ✅ Page Transitions -->
<AnimatePresence>
  <Motion
    v-if="show"
    :initial="{ opacity: 0, y: 10 }"
    :animate="{ opacity: 1, y: 0 }"
    :exit="{ opacity: 0, y: -10 }"
    :transition="{ duration: 0.2 }"
  >
    <div>Content</div>
  </Motion>
</AnimatePresence>

<!-- ✅ Modal Animation -->
<AnimatePresence>
  <Motion
    v-if="isOpen"
    :initial="{ opacity: 0, scale: 0.95 }"
    :animate="{ opacity: 1, scale: 1 }"
    :exit="{ opacity: 0, scale: 0.95 }"
    :transition="{ type: 'spring', stiffness: 300, damping: 28 }"
  >
    <div class="modal">...</div>
  </Motion>
</AnimatePresence>

<!-- ✅ Button Press -->
<Motion
  as="button"
  :whileTap="{ scale: 0.97 }"
  :transition="{ duration: 0.1 }"
>
  Click me
</Motion>

<!-- ✅ List Items (staggered) -->
<Motion
  v-for="(item, i) in items"
  :key="item.id"
  :initial="{ opacity: 0, y: 20 }"
  :animate="{ opacity: 1, y: 0 }"
  :transition="{ delay: i * 0.05 }"
>
  {{ item.name }}
</Motion>
```

**Spring Config:** `{ type: 'spring', stiffness: 300, damping: 28 }`

> ❌ For long lists (50+ items), use CSS `transition-all duration-200` instead.

### Icons (Lucide)

```vue
<script setup>
import { User, ChevronRight, Plus, Trash2, Edit, Check, X } from 'lucide-vue-next';
</script>

<!-- Standard sizes -->
<User :size="16" class="text-slate-400" />      <!-- Small (inline text) -->
<User :size="20" class="text-slate-500" />      <!-- Default (buttons) -->
<User :size="24" class="text-slate-600" />      <!-- Large (headers) -->

<!-- With stroke width -->
<Check :size="20" :stroke-width="2.5" class="text-emerald-500" />

<!-- Button with icon -->
<button class="inline-flex items-center gap-2">
  <Plus :size="18" />
  Tambah
</button>
```

**Common Icons:**
| Action | Icon |
|--------|------|
| Add | `Plus` |
| Edit | `Edit`, `Pencil` |
| Delete | `Trash2` |
| Close | `X` |
| Confirm | `Check` |
| Back | `ChevronLeft`, `ArrowLeft` |
| Next | `ChevronRight`, `ArrowRight` |
| Menu | `Menu` |
| Search | `Search` |
| Settings | `Settings` |
| User | `User`, `Users` |
| Loading | `Loader2` (with `animate-spin`) |

### Charts (vue-chartjs)

```vue
<script setup>
import { Bar, Line, Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  LineElement,
  PointElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  LineElement,
  PointElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
);

const chartData = {
  labels: ['Jan', 'Feb', 'Mar'],
  datasets: [{
    label: 'Pendapatan',
    data: [1000000, 1500000, 1200000],
    backgroundColor: 'rgba(16, 185, 129, 0.8)', // emerald-500
    borderColor: 'rgb(16, 185, 129)',
    borderRadius: 8,
  }],
};

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: 'rgba(148, 163, 184, 0.1)' }, // slate-400/10
    },
    x: {
      grid: { display: false },
    },
  },
};
</script>

<template>
  <div class="h-64 w-full">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>
```

**Chart Color Palette:**
```ts
const colors = {
  primary: 'rgba(16, 185, 129, 0.8)',   // emerald-500
  secondary: 'rgba(59, 130, 246, 0.8)', // blue-500
  warning: 'rgba(245, 158, 11, 0.8)',   // amber-500
  danger: 'rgba(239, 68, 68, 0.8)',     // red-500
  info: 'rgba(14, 165, 233, 0.8)',      // sky-500
  muted: 'rgba(148, 163, 184, 0.5)',    // slate-400
};
```

### Press Feedback (Required)
```vue
<button class="active:scale-97 transition-transform">
<button class="bg-emerald-500 hover:bg-emerald-600 shadow-sm shadow-emerald-500/10">
<div class="hover:scale-101 transition-transform"> <!-- Cards desktop -->
```

### Haptics
```ts
haptics.light()   // Tap, switch
haptics.medium()  // Success, submit
haptics.heavy()   // Delete, error
```

## Color System

| Role | Class |
|------|-------|
| Accent | emerald-500 |
| Hover | emerald-600 |
| Highlight | emerald-50 |
| Background | white |
| Surface | slate-50 |
| Border | slate-200 |
| Text Primary | slate-900 |
| Text Secondary | slate-500 |
| Placeholder | slate-400 |

### Semantic
```
Success: emerald-500  Warning: amber-500  Error: red-500  Info: sky-500
```

## Typography
- Font: System Sans (0ms load)
- Primary: `text-slate-900`
- Secondary: `text-slate-500`
- Labels: `text-[11px] font-semibold tracking-wide uppercase text-slate-600`

## Form Components

Path: `resources/js/components/ui/Form/`

Available: `FormInput`, `FormSelect`, `FormTextarea`, `FormNumberInput`, `FormCheckbox`

```vue
<FormInput v-model="form.name" label="Nama" :error="form.errors.name" />
```

**Specs:**
- Unfocused: `bg-slate-50 border-slate-200`
- Focused: `bg-white border-emerald-500 ring-2 ring-emerald-500/20 shadow-sm`
- Height: `h-[52px]`, Radius: `rounded-xl`

## Modals & Alerts

```ts
import { useModal } from '@/composables/useModal';
const modal = useModal();

await modal.success('Berhasil!');
const confirmed = await modal.confirm('Simpan?');
```

```vue
<script setup>
import { Motion, AnimatePresence } from 'motion-v';
import { X } from 'lucide-vue-next';
</script>

<!-- Modal with motion-v -->
<AnimatePresence>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
    <!-- Backdrop -->
    <Motion
      :initial="{ opacity: 0 }"
      :animate="{ opacity: 1 }"
      :exit="{ opacity: 0 }"
      class="absolute inset-0 bg-black/50"
      @click="show = false"
    />
    <!-- Modal Content -->
    <Motion
      :initial="{ opacity: 0, scale: 0.95, y: 20 }"
      :animate="{ opacity: 1, scale: 1, y: 0 }"
      :exit="{ opacity: 0, scale: 0.95, y: 20 }"
      :transition="{ type: 'spring', stiffness: 300, damping: 28 }"
      class="relative bg-white rounded-2xl shadow-xl border border-slate-200 p-6 max-w-md w-full mx-4"
    >
      <button @click="show = false" class="absolute top-4 right-4">
        <X :size="20" class="text-slate-400 hover:text-slate-600" />
      </button>
      <slot />
    </Motion>
  </div>
</AnimatePresence>
```

## Layout Patterns
```
Spacing:    space-y-4 (content), space-y-6 (sections)
Cards:      rounded-xl p-6 bg-white border border-slate-200
Containers: max-w-7xl mx-auto px-4 sm:px-6 lg:px-8
```

### Symmetric Grid Layout for Stat Cards

When displaying stat cards that don't divide evenly into rows, split into multiple grids for visual symmetry:

```vue
<!-- Example: 7 cards = 4 + 3 (centered) -->

<!-- First Row: 4 cards, full width -->
<div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
    <div v-for="card in cards.slice(0, 4)" :key="card.key">
        <!-- Card content -->
    </div>
</div>

<!-- Second Row: 3 cards, centered -->
<div class="grid gap-4 grid-cols-2 lg:grid-cols-3 lg:max-w-3xl lg:mx-auto">
    <div 
        v-for="(card, index) in cards.slice(4)" 
        :key="card.key"
        :class="[
            // Last item spans full width on mobile if odd count
            cards.slice(4).length % 2 !== 0 && index === cards.slice(4).length - 1 
                ? 'col-span-2 lg:col-span-1' 
                : ''
        ]"
    >
        <!-- Card content -->
    </div>
</div>
```

**Grid Symmetry Rules:**
- 4 items: `grid-cols-2 lg:grid-cols-4` (2x2 mobile, 1x4 desktop)
- 6 items: `grid-cols-2 lg:grid-cols-3` (3x2 mobile, 2x3 desktop)
- 7 items: Split into 4+3, center the second row
- 8 items: `grid-cols-2 lg:grid-cols-4` (4x2 mobile, 2x4 desktop)
- Odd last row on mobile: Last item `col-span-2` for full width

## Dashboard Card Patterns

### Stat Card (Solid - Default)
```vue
<div class="rounded-2xl border shadow-sm p-4 bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800">
    <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center">
        <Icon class="w-5 h-5 text-white" />
    </div>
    <p class="text-amber-600 dark:text-amber-400">Label</p>
    <p class="text-slate-900 dark:text-slate-100 font-bold">Value</p>
</div>
```

### Featured Card (Gradient - Sparingly)
```vue
<!-- Only for hero/featured elements, max 1 per page -->
<div class="rounded-2xl shadow-lg p-6 bg-linear-to-br from-emerald-500 to-teal-600 text-white">
    <p class="text-emerald-100">Label</p>
    <p class="text-3xl font-bold">Value</p>
</div>
```

### Color Helper Pattern
```typescript
const getColorClasses = (color: string) => {
    const colors: Record<string, { bg: string; border: string; icon: string; text: string }> = {
        amber: {
            bg: 'bg-amber-50 dark:bg-amber-950/30',
            border: 'border-amber-200 dark:border-amber-800',
            icon: 'bg-amber-500',
            text: 'text-amber-600 dark:text-amber-400',
        },
        emerald: {
            bg: 'bg-emerald-50 dark:bg-emerald-950/30',
            border: 'border-emerald-200 dark:border-emerald-800',
            icon: 'bg-emerald-500',
            text: 'text-emerald-600 dark:text-emerald-400',
        },
        blue: {
            bg: 'bg-blue-50 dark:bg-blue-950/30',
            border: 'border-blue-200 dark:border-blue-800',
            icon: 'bg-blue-500',
            text: 'text-blue-600 dark:text-blue-400',
        },
        red: {
            bg: 'bg-red-50 dark:bg-red-950/30',
            border: 'border-red-200 dark:border-red-800',
            icon: 'bg-red-500',
            text: 'text-red-600 dark:text-red-400',
        },
        purple: {
            bg: 'bg-purple-50 dark:bg-purple-950/30',
            border: 'border-purple-200 dark:border-purple-800',
            icon: 'bg-purple-500',
            text: 'text-purple-600 dark:text-purple-400',
        },
        teal: {
            bg: 'bg-teal-50 dark:bg-teal-950/30',
            border: 'border-teal-200 dark:border-teal-800',
            icon: 'bg-teal-500',
            text: 'text-teal-600 dark:text-teal-400',
        },
    };
    return colors[color] || colors.emerald;
};
```

## Checklist

- [ ] Buttons: `active:scale-97` or Motion `whileTap`
- [ ] Forms: emerald focus
- [ ] Solid backgrounds (no gradients except hero/featured)
- [ ] Borders: `border-slate-200`
- [ ] Haptics on interactions
- [ ] Skeleton loading
- [ ] WCAG AA contrast
- [ ] Mobile touch targets
- [ ] Icons: lucide-vue-next with proper sizing
- [ ] Animations: motion-v with AnimatePresence for enter/exit
- [ ] Charts: vue-chartjs with emerald color palette
- [ ] Stat cards: Use `getColorClasses` helper for consistent styling
- [ ] Gradient usage: Max 1-2 per page (hero/featured only)
- [ ] Reuse existing UI components from `@/components/ui/`
- [ ] Grid symmetry: Split uneven card counts into centered rows
