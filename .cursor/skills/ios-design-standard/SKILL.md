---
name: ios-design-standard
description: iOS-like design system for Vue/Tailwind components. Use when creating UI components, styling forms, modals, buttons, or when the user asks about design patterns, colors, typography, or mobile-first responsive design.
---

# iOS-like Design Standard

**Philosophy:** Clean, Solid, Performant. Function > Fashion.
**Target:** Low-end devices (UMKM market) with premium feel.

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

### Motion (Strategic)
```vue
<!-- ✅ Use motion-v for: -->
Page transitions: motion-v preset-fade
Modals:           motion-v preset-slide-up
Button press:     :whileTap="{ scale: 0.97 }"

<!-- ❌ Long lists: use CSS transition-all duration-200 -->
```
Spring: `{ stiffness: 300, damping: 28 }`

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
<BaseModal :show="show" title="Judul" @close="show = false">
  <div class="p-6">Content</div>
</BaseModal>

<Alert :show="show" type="success" message="Tersimpan" />
```

## Layout Patterns
```
Spacing:    space-y-4 (content), space-y-6 (sections)
Cards:      rounded-xl p-6 bg-white border border-slate-200
Containers: max-w-7xl mx-auto px-4 sm:px-6 lg:px-8
```

## Checklist

- [ ] Buttons: `active:scale-97`
- [ ] Forms: emerald focus
- [ ] Solid backgrounds
- [ ] Borders: `border-slate-200`
- [ ] Haptics on interactions
- [ ] Skeleton loading
- [ ] WCAG AA contrast
- [ ] Mobile touch targets
