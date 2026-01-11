<template>
  <AppLayout :title="title">
    <div class="max-w-3xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
        <p class="mt-1 text-sm text-gray-600">
          Ajukan permohonan izin/cuti Anda melalui form di bawah ini
        </p>
      </div>

      <!-- Form Card -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form @submit.prevent="submitForm">
          <!-- Jenis Izin -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Jenis <span class="text-red-500">*</span>
            </label>
            <div class="flex gap-4">
              <label class="flex items-center">
                <input
                  v-model="form.type"
                  type="radio"
                  value="IZIN"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                />
                <span class="ml-2 text-sm text-gray-700">📋 Izin</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="form.type"
                  type="radio"
                  value="SAKIT"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                />
                <span class="ml-2 text-sm text-gray-700">🏥 Sakit</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="form.type"
                  type="radio"
                  value="CUTI"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                />
                <span class="ml-2 text-sm text-gray-700">🏖️ Cuti</span>
              </label>
            </div>
            <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">
              {{ form.errors.type }}
            </p>
          </div>

          <!-- Tanggal -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tanggal Mulai <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.start_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              />
              <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.start_date }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tanggal Selesai <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.end_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              />
              <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.end_date }}
              </p>
            </div>
          </div>

          <!-- Alasan -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Alasan <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="form.reason"
              rows="4"
              placeholder="Jelaskan alasan permohonan izin Anda (minimal 10 karakter)"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
            <div class="mt-1 flex justify-between">
              <p v-if="form.errors.reason" class="text-sm text-red-600">
                {{ form.errors.reason }}
              </p>
              <p class="text-sm text-gray-500">
                {{ form.reason?.length || 0 }}/500 karakter
              </p>
            </div>
          </div>

          <!-- Upload Surat -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Upload Surat Pendukung (Opsional)
            </label>
            <input
              type="file"
              accept=".jpg,.jpeg,.png,.pdf"
              @change="handleFileUpload"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            />
            <p class="mt-1 text-sm text-gray-500">
              Format: JPG, PNG, PDF (Maksimal 5MB)
            </p>
            <p v-if="form.errors.attachment" class="mt-1 text-sm text-red-600">
              {{ form.errors.attachment }}
            </p>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <Link
              :href="indexRoute().url"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Batal
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
              {{ form.processing ? 'Mengirim...' : 'Kirim Pengajuan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/components/layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { index as indexRoute, store as storeRoute } from '@/routes/teacher/teacher-leaves'

defineProps<{
  title: string
}>()

const form = useForm({
  type: 'IZIN',
  start_date: '',
  end_date: '',
  reason: '',
  attachment: null as File | null,
})

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    form.attachment = target.files[0]
  }
}

const submitForm = () => {
  form.post(storeRoute().url, {
    preserveScroll: true,
  })
}
</script>
