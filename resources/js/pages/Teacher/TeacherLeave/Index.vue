<template>
  <AppLayout :title="title">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
          <p class="mt-1 text-sm text-gray-600">
            Riwayat permohonan izin/cuti Anda
          </p>
        </div>
        <Link
          :href="createRoute().url"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
        >
          + Ajukan Izin Baru
        </Link>
      </div>

      <!-- Leave Requests List -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div v-if="leaves.data.length === 0" class="p-8 text-center">
          <p class="text-gray-500">Belum ada permohonan izin</p>
        </div>

        <div v-else class="divide-y divide-gray-200">
          <div
            v-for="leave in leaves.data"
            :key="leave.id"
            class="p-4 hover:bg-gray-50"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="{
                      'bg-blue-100 text-blue-800': leave.type === 'IZIN',
                      'bg-red-100 text-red-800': leave.type === 'SAKIT',
                      'bg-purple-100 text-purple-800': leave.type === 'CUTI',
                    }"
                  >
                    {{ leave.type }}
                  </span>
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="{
                      'bg-yellow-100 text-yellow-800': leave.status === 'PENDING',
                      'bg-green-100 text-green-800': leave.status === 'APPROVED',
                      'bg-red-100 text-red-800': leave.status === 'REJECTED',
                    }"
                  >
                    {{ leave.status }}
                  </span>
                </div>

                <p class="text-sm font-medium text-gray-900 mb-1">
                  {{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}
                  <span class="text-gray-500">({{ calculateDays(leave.start_date, leave.end_date) }} hari)</span>
                </p>

                <p class="text-sm text-gray-600 mb-2">
                  {{ leave.reason }}
                </p>

                <div v-if="leave.status === 'APPROVED' && leave.approved_by" class="text-xs text-gray-500">
                  Disetujui oleh {{ leave.approved_by.name }} pada {{ formatDateTime(leave.approved_at) }}
                </div>

                <div v-if="leave.status === 'REJECTED'" class="text-xs text-red-600 mt-1">
                  Ditolak: {{ leave.rejection_reason }}
                </div>
              </div>

              <div v-if="leave.attachment_path" class="ml-4">
                <a
                  :href="`/storage/${leave.attachment_path}`"
                  target="_blank"
                  class="text-xs text-blue-600 hover:text-blue-800"
                >
                  📎 Lihat Lampiran
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="leaves.links.length > 3" class="border-t border-gray-200 px-4 py-3">
          <div class="flex justify-center gap-2">
            <Link
              v-for="link in leaves.links"
              :key="link.label"
              :href="link.url || ''"
              :class="[
                'px-3 py-1 text-sm rounded-md',
                link.active
                  ? 'bg-blue-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300',
                !link.url && 'opacity-50 cursor-not-allowed',
              ]"
            ><span v-html="link.label" /></Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/components/layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { create as createRoute } from '@/routes/teacher/teacher-leaves'

interface TeacherLeave {
  id: number
  type: 'IZIN' | 'SAKIT' | 'CUTI'
  start_date: string | null
  end_date: string | null
  reason: string
  status: 'PENDING' | 'APPROVED' | 'REJECTED'
  attachment_path: string | null
  approved_by?: {
    name: string
  } | null
  approved_at?: string | null
  rejection_reason?: string | null
}

interface PaginatedLeaves {
  data: TeacherLeave[]
  links: Array<{
    url: string | null
    label: string
    active: boolean
  }>
}

defineProps<{
  title: string
  leaves: PaginatedLeaves
}>()

const formatDate = (date: string | null | undefined) => {
  if (!date) return 'N/A'

  try {
    const parsedDate = new Date(date)

    if (isNaN(parsedDate.getTime())) {
      return 'Invalid date'
    }

    return parsedDate.toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    })
  } catch (error) {
    console.error('Error formatting date:', error, date)
    return 'Invalid date'
  }
}

const formatDateTime = (datetime: string | null | undefined) => {
  if (!datetime) return 'N/A'

  try {
    const parsedDate = new Date(datetime)

    if (isNaN(parsedDate.getTime())) {
      return 'Invalid date'
    }

    return parsedDate.toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch (error) {
    console.error('Error formatting datetime:', error, datetime)
    return 'Invalid date'
  }
}

const calculateDays = (startDate: string, endDate: string) => {
  try {
    const start = new Date(startDate)
    const end = new Date(endDate)

    if (isNaN(start.getTime()) || isNaN(end.getTime())) {
      return 0
    }

    const diffTime = Math.abs(end.getTime() - start.getTime())
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return diffDays + 1
  } catch (error) {
    console.error('Error calculating days:', error, startDate, endDate)
    return 0
  }
}
</script>
