---
name: ios-design-standard
description: iOS-like design system for Vue/Tailwind components. Use when creating UI components, styling forms, modals, buttons, or when the user asks about design patterns, colors, typography, or mobile-first responsive design.
---

# iOS-like Design Standard

**Philosophy:** Clean, Solid, Performant. Function > Fashion.
**Target:** Low-end devices (UMKM market) with premium feel.

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

## Checklist

- [ ] Buttons: `active:scale-97` or Motion `whileTap`
- [ ] Forms: emerald focus
- [ ] Solid backgrounds
- [ ] Borders: `border-slate-200`
- [ ] Haptics on interactions
- [ ] Skeleton loading
- [ ] WCAG AA contrast
- [ ] Mobile touch targets
- [ ] Icons: lucide-vue-next with proper sizing
- [ ] Animations: motion-v with AnimatePresence for enter/exit
- [ ] Charts: vue-chartjs with emerald color palette
