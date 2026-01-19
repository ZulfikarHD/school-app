# 🎨 UI Components Guide

> **Last Updated:** 2026-01-19
> **Status:** ✅ Implemented
> **Version:** 1.0

---

## Overview

UI Components Guide merupakan panduan komprehensif untuk menggunakan komponen-komponen UI yang telah dibangun dengan iOS-like Design System, yaitu: memberikan konsistensi visual, performa tinggi, dan pengalaman pengguna yang optimal untuk berbagai use case dalam aplikasi sekolah.

---

## 🎯 Badge Component

### Overview

Badge Component merupakan komponen reusable yang bertujuan untuk menampilkan status, label, dan tags dengan modern clean design yang mengikuti iOS-like design system, yaitu: menggunakan soft colors untuk look yang elegan, multiple variants untuk berbagai konteks, dan support untuk dot indicator serta removable functionality.

### Basic Usage

```vue
<script setup>
import Badge from '@/components/ui/Badge.vue';
</script>

<template>
    <!-- Basic badges -->
    <Badge>Default</Badge>
    <Badge variant="success">Aktif</Badge>
    <Badge variant="error">Gagal</Badge>
</template>
```

### Variants

| Variant | Use Case | Color |
|---------|----------|-------|
| `default` | General labels | Slate/Gray |
| `primary` | Primary actions/status | Blue |
| `secondary` | Secondary information | Violet |
| `success` | Positive states (active, completed) | Emerald |
| `error` | Error states, failed operations | Red |
| `warning` | Warning states, pending items | Amber |
| `info` | Information, neutral states | Sky |

```vue
<template>
    <!-- Status badges -->
    <Badge variant="success">Aktif</Badge>
    <Badge variant="error">Tidak Aktif</Badge>
    <Badge variant="warning">Pending</Badge>
    <Badge variant="info">Dalam Proses</Badge>
</template>
```

### Sizes

```vue
<template>
    <Badge size="xs">XS</Badge>
    <Badge size="sm">Small</Badge>
    <Badge size="md">Medium</Badge>
    <Badge size="lg">Large</Badge>
</template>
```

### Style Variants

#### Soft (Default)
Background dengan opacity rendah untuk look yang subtle dan modern.

```vue
<template>
    <Badge variant="success">Soft Badge</Badge>
    <Badge variant="error" soft>Soft Badge</Badge>
</template>
```

#### Outline
Hanya border tanpa background fill untuk style yang minimal.

```vue
<template>
    <Badge variant="primary" outline>Outline Badge</Badge>
    <Badge variant="success" outline>Outline Badge</Badge>
</template>
```

#### Solid
Full colored background dengan white text untuk emphasis yang tinggi.

```vue
<template>
    <Badge variant="success" :soft="false">Solid Badge</Badge>
    <Badge variant="error" :soft="false">Solid Badge</Badge>
</template>
```

### Advanced Features

#### Dot Indicator
Menampilkan dot indicator di sebelah kiri text untuk visual hierarchy yang lebih baik.

```vue
<template>
    <Badge variant="success" dot>Online</Badge>
    <Badge variant="error" dot>Offline</Badge>
    <Badge variant="warning" dot>Maintenance</Badge>
</template>
```

#### Removable Badge
Badge dengan close button yang dapat dihapus dengan `@remove` event.

```vue
<script setup>
import Badge from '@/components/ui/Badge.vue';

const tags = ref(['React', 'Vue', 'Angular']);

const removeTag = (index) => {
    tags.value.splice(index, 1);
};
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <Badge
            v-for="(tag, index) in tags"
            :key="tag"
            variant="primary"
            removable
            @remove="removeTag(index)"
        >
            {{ tag }}
        </Badge>
    </div>
</template>
```

#### With Icons
Support untuk icon di kiri dan kanan badge melalui slots.

```vue
<script setup>
import Badge from '@/components/ui/Badge.vue';
import { CheckIcon, XIcon, ClockIcon } from 'lucide-vue-next';
</script>

<template>
    <!-- Left icon -->
    <Badge variant="success">
        <template #icon-left>
            <CheckIcon :size="12" />
        </template>
        Verified
    </Badge>

    <!-- Right icon -->
    <Badge variant="warning">
        Pending
        <template #icon-right>
            <ClockIcon :size="12" />
        </template>
    </Badge>
</template>
```

### Rounded Styles

#### Pill (Default)
Fully rounded untuk look yang modern dan friendly.

```vue
<template>
    <Badge rounded="pill">Pill Badge</Badge>
</template>
```

#### Square
Slight rounded untuk style yang lebih formal.

```vue
<template>
    <Badge rounded="square">Square Badge</Badge>
</template>
```

### Practical Examples

#### User Status Badges

```vue
<template>
    <div class="flex items-center gap-2">
        <Badge
            :variant="user.isActive ? 'success' : 'error'"
            dot
        >
            {{ user.isActive ? 'Aktif' : 'Tidak Aktif' }}
        </Badge>
        <Badge variant="info" size="sm">
            {{ user.role }}
        </Badge>
    </div>
</template>
```

#### Attendance Status

```vue
<template>
    <div class="grid grid-cols-3 gap-2">
        <Badge variant="success" dot>Hadir</Badge>
        <Badge variant="error" dot>Tidak Hadir</Badge>
        <Badge variant="warning" dot>Terlambat</Badge>
    </div>
</template>
```

#### Tag System

```vue
<script setup>
const selectedTags = ref(['matematika', 'fisika']);

const toggleTag = (tag) => {
    const index = selectedTags.value.indexOf(tag);
    if (index > -1) {
        selectedTags.value.splice(index, 1);
    } else {
        selectedTags.value.push(tag);
    }
};
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <Badge
            v-for="tag in ['matematika', 'fisika', 'kimia', 'biologi']"
            :key="tag"
            :variant="selectedTags.includes(tag) ? 'primary' : 'default'"
            :soft="!selectedTags.includes(tag)"
            :outline="!selectedTags.includes(tag)"
            class="cursor-pointer"
            @click="toggleTag(tag)"
        >
            {{ tag }}
        </Badge>
    </div>
</template>
```

### API Reference

#### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `variant` | `'default' \| 'success' \| 'error' \| 'warning' \| 'info' \| 'primary' \| 'secondary'` | `'default'` | Badge variant styling |
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg'` | `'md'` | Badge size |
| `rounded` | `'pill' \| 'square'` | `'pill'` | Rounded corner style |
| `dot` | `boolean` | `false` | Show dot indicator |
| `outline` | `boolean` | `false` | Outline style (border only) |
| `soft` | `boolean` | `true` | Soft background style |
| `removable` | `boolean` | `false` | Show close button |
| `disabled` | `boolean` | `false` | Disabled state |

#### Slots

| Slot | Description |
|------|-------------|
| `default` | Badge content |
| `icon-left` | Icon displayed before content |
| `icon-right` | Icon displayed after content |

#### Events

| Event | Description |
|-------|-------------|
| `remove` | Emitted when close button is clicked |

### Accessibility

- Badge component menggunakan semantic markup dengan proper ARIA labels
- Close button memiliki `aria-label="Hapus"` untuk screen readers
- Dot indicator menggunakan `aria-hidden="true"` karena hanya decorative
- Focus management yang proper untuk keyboard navigation

### Performance Notes

- Menggunakan CSS classes yang dioptimalkan untuk minimal repaints
- Transition duration yang singkat (200ms) untuk responsive feel
- Efficient class computation dengan computed properties

### Design Guidelines

#### When to Use Badges
- ✅ Status indicators (active/inactive, online/offline)
- ✅ Tag systems (categories, filters)
- ✅ Count indicators (notification counts)
- ✅ Priority levels (high, medium, low)
- ✅ Small metadata (file types, user roles)

#### When NOT to Use Badges
- ❌ Long text content (use regular text or cards)
- ❌ Primary actions (use buttons instead)
- ❌ Large content blocks (use cards or modals)
- ❌ Navigation items (use tabs or menus)

---

## 🎨 Color Usage Guidelines

### Status Mapping

| Status | Badge Variant | Use Case |
|--------|---------------|----------|
| Active/Success | `success` | User aktif, task completed |
| Inactive/Error | `error` | User non-aktif, failed operations |
| Pending/Warning | `warning` | Pending approval, maintenance |
| Info/Neutral | `info` | General information |
| Primary Action | `primary` | Selected items, current status |
| Secondary | `secondary` | Alternative options |

### Contrast Requirements

- **Soft variant**: Minimum contrast ratio 4.5:1 untuk readability
- **Outline variant**: Border color harus memiliki contrast yang cukup
- **Solid variant**: White text on colored background (all variants pass WCAG AA)

---

## 📱 Responsive Behavior

Badge component secara otomatis responsive dengan:
- Font sizes yang scalable untuk mobile
- Padding yang disesuaikan untuk touch targets
- Flexible layout untuk berbagai container widths

---

## 🔧 Implementation Notes

- Badge menggunakan Tailwind CSS utilities untuk consistent styling
- Dark mode support penuh dengan automatic color switching
- TypeScript support untuk better DX dan error prevention
- Vue 3 Composition API dengan proper reactivity