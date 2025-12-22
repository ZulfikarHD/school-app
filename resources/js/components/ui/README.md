# 🎨 Modal, Dialog & Alert Components

Reusable UI components dengan iOS-like design, spring animations, haptic feedback, dan glass effects.

## 📦 Components

### 1. **BaseModal** - Modal Dasar
Modal component yang fully customizable dengan berbagai ukuran dan slot support.

#### Props
```typescript
{
    show?: boolean;              // Control visibility
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'full';  // Modal size
    closeOnBackdrop?: boolean;   // Close saat click backdrop (default: true)
    closeOnEscape?: boolean;     // Close saat tekan ESC (default: true)
    showCloseButton?: boolean;   // Tampilkan tombol close (default: true)
    title?: string;              // Modal title
    preventScroll?: boolean;     // Prevent body scroll (default: true)
}
```

#### Events
- `@close` - Triggered saat modal ditutup
- `@open` - Triggered saat modal dibuka

#### Slots
- `header` - Custom header content
- `default` - Modal body content
- `footer` - Custom footer dengan action buttons

#### Example
```vue
<script setup>
import BaseModal from '@/components/ui/BaseModal.vue';
import { ref } from 'vue';

const showModal = ref(false);
</script>

<template>
    <BaseModal
        :show="showModal"
        title="Judul Modal"
        size="md"
        @close="showModal = false"
    >
        <p>Konten modal di sini...</p>

        <template #footer>
            <button @click="showModal = false">Tutup</button>
        </template>
    </BaseModal>
</template>
```

---

### 2. **DialogModal** - Dialog Konfirmasi
Dialog component untuk confirmations, warnings, dan alerts dengan icon dan type support.

#### Props
```typescript
{
    show?: boolean;
    type?: 'success' | 'warning' | 'danger' | 'info';
    title: string;               // Dialog title (required)
    message: string;             // Dialog message (required)
    confirmText?: string;        // Text tombol confirm (default: 'Konfirmasi')
    cancelText?: string;         // Text tombol cancel (default: 'Batal')
    showCancel?: boolean;        // Tampilkan tombol cancel (default: true)
    loading?: boolean;           // Loading state untuk async operations
    icon?: 'check' | 'warning' | 'error' | 'info' | 'question';
}
```

#### Events
- `@confirm` - User click confirm button
- `@cancel` - User click cancel button
- `@close` - Dialog ditutup

#### Example
```vue
<script setup>
import DialogModal from '@/components/ui/DialogModal.vue';
import { ref } from 'vue';

const showDialog = ref(false);
const loading = ref(false);

const handleConfirm = async () => {
    loading.value = true;
    // API call...
    await deleteItem();
    loading.value = false;
    showDialog.value = false;
};
</script>

<template>
    <DialogModal
        :show="showDialog"
        type="danger"
        title="Konfirmasi Hapus"
        message="Data yang dihapus tidak dapat dikembalikan."
        confirm-text="Hapus"
        cancel-text="Batal"
        :loading="loading"
        @confirm="handleConfirm"
        @cancel="showDialog = false"
        @close="showDialog = false"
    />
</template>
```

---

### 3. **Alert** - Toast Notification
Toast notification component untuk feedback messages dengan auto-dismiss dan positioning.

#### Props
```typescript
{
    show?: boolean;
    type?: 'success' | 'error' | 'warning' | 'info';
    title?: string;
    message: string;             // Alert message (required)
    duration?: number;           // Auto-dismiss duration (ms), 0 = no auto-dismiss (default: 3000)
    position?: 'top' | 'top-right' | 'top-left' | 'bottom' | 'bottom-right' | 'bottom-left';
    dismissible?: boolean;       // User dapat close manual (default: true)
}
```

#### Events
- `@close` - Alert ditutup

#### Example
```vue
<script setup>
import Alert from '@/components/ui/Alert.vue';
import { ref } from 'vue';

const showAlert = ref(false);
</script>

<template>
    <Alert
        :show="showAlert"
        type="success"
        title="Berhasil"
        message="Data berhasil disimpan!"
        :duration="3000"
        position="top-right"
        @close="showAlert = false"
    />
</template>
```

---

## 🎯 useModal Composable

Composable untuk centralized modal state management dengan promise-based API.

### Methods

#### Modal Management
```typescript
const modal = useModal();

modal.open('modalName');        // Open modal by name
modal.close('modalName');       // Close modal by name
modal.toggle('modalName');      // Toggle modal
modal.isOpen('modalName');      // Check if modal is open
```

#### Dialog (Promise-based)
```typescript
// Custom dialog
const result = await modal.dialog({
    type: 'warning',
    title: 'Konfirmasi',
    message: 'Apakah Anda yakin?',
    confirmText: 'Ya',
    cancelText: 'Tidak',
});

// Quick confirm
const confirmed = await modal.confirm(
    'Konfirmasi Aksi',
    'Lanjutkan?'
);

// Delete confirmation
const deleted = await modal.confirmDelete(
    'Data akan dihapus permanen'
);
```

#### Alert / Toast
```typescript
// Type-specific alerts
modal.success('Operasi berhasil!');
modal.error('Terjadi kesalahan');
modal.warning('Peringatan penting');
modal.info('Informasi untuk Anda');

// Custom alert
modal.alert({
    type: 'success',
    title: 'Berhasil',
    message: 'Data tersimpan',
    duration: 3000,
    position: 'top-right',
});
```

### Complete Example
```vue
<script setup lang="ts">
import { useModal } from '@/composables/useModal';
import DialogModal from '@/components/ui/DialogModal.vue';
import Alert from '@/components/ui/Alert.vue';

const modal = useModal();

const handleDelete = async () => {
    const confirmed = await modal.confirmDelete();
    
    if (confirmed) {
        try {
            await deleteData();
            modal.success('Data berhasil dihapus');
        } catch (error) {
            modal.error('Gagal menghapus data');
        }
    }
};

const handleSave = async () => {
    try {
        await saveData();
        modal.success('Data berhasil disimpan!');
    } catch (error) {
        modal.error('Terjadi kesalahan saat menyimpan');
    }
};
</script>

<template>
    <div>
        <button @click="handleDelete">Hapus</button>
        <button @click="handleSave">Simpan</button>

        <!-- Required: Add modal components -->
        <DialogModal
            :show="modal.dialogState.value.show"
            :type="modal.dialogState.value.options.type"
            :title="modal.dialogState.value.options.title"
            :message="modal.dialogState.value.options.message"
            :confirm-text="modal.dialogState.value.options.confirmText"
            :cancel-text="modal.dialogState.value.options.cancelText"
            :show-cancel="modal.dialogState.value.options.showCancel"
            :icon="modal.dialogState.value.options.icon"
            @confirm="modal.dialogState.value.onConfirm?.()"
            @cancel="modal.dialogState.value.onCancel?.()"
            @close="modal.closeDialog()"
        />

        <Alert
            :show="modal.alertState.value.show"
            :type="modal.alertState.value.options.type"
            :title="modal.alertState.value.options.title"
            :message="modal.alertState.value.options.message"
            :duration="modal.alertState.value.options.duration"
            :position="modal.alertState.value.options.position"
            @close="modal.closeAlert()"
        />
    </div>
</template>
```

---

## ✨ Features

### iOS-like Design
- ✅ Spring physics animations (natural bounce)
- ✅ Glass effect dengan backdrop blur
- ✅ Press feedback (scale 0.97)
- ✅ Haptic feedback untuk tactile response
- ✅ Smooth transitions dengan motion-v
- ✅ Gradient backgrounds
- ✅ Dark mode support

### Mobile-First UX
- ✅ Responsive sizing
- ✅ Touch-friendly buttons
- ✅ Prevent body scroll saat modal open
- ✅ Swipe gestures (ready for implementation)
- ✅ Bottom sheet style (mobile)

### Accessibility
- ✅ ESC key untuk close
- ✅ Backdrop click untuk close
- ✅ Focus management
- ✅ ARIA labels
- ✅ Keyboard navigation

---

## 🎨 Design Tokens

### Modal Sizes
- `sm`: max-w-sm (24rem / 384px)
- `md`: max-w-md (28rem / 448px)
- `lg`: max-w-lg (32rem / 512px)
- `xl`: max-w-xl (36rem / 576px)
- `full`: max-w-full dengan margin

### Alert Positions
- `top`: Top center
- `top-right`: Top right corner (default)
- `top-left`: Top left corner
- `bottom`: Bottom center
- `bottom-right`: Bottom right corner
- `bottom-left`: Bottom left corner

### Animation Timings
- Modal entrance: spring (stiffness: 400, damping: 30)
- Button tap: spring (stiffness: 500, damping: 30)
- Alert slide: spring (stiffness: 400, damping: 30)
- Icon bounce: spring (stiffness: 500, damping: 25)

---

## 📝 Notes

1. **Teleport**: Semua components menggunakan `Teleport to="body"` untuk proper z-index stacking
2. **Haptic Feedback**: Automatic haptic feedback based on action type
3. **Loading States**: DialogModal support loading state untuk async operations
4. **Auto-dismiss**: Alert component auto-dismiss dengan progress bar
5. **Promise-based**: useModal dialog methods return Promise untuk easy async handling

---

## 🚀 Usage Tips

### Best Practices
1. Gunakan `useModal` composable untuk centralized management
2. Tambahkan DialogModal dan Alert components di root/layout component
3. Prefer promise-based dialog methods untuk async operations
4. Set appropriate duration untuk alerts berdasarkan message importance
5. Use type-specific methods (success, error, warning) untuk consistent UX

### Performance
- Components menggunakan lazy rendering (v-if)
- Auto cleanup saat unmount
- Optimized animations dengan motion-v
- Minimal re-renders dengan reactive state

---

Dibuat dengan ❤️ menggunakan Vue 3, TypeScript, Tailwind CSS 4, dan Motion-v

