<template>
  <AppLayout :title="title">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
        <p class="mt-1 text-sm text-gray-600">
          Kelola permohonan izin/cuti dari guru
        </p>
      </div>

      <!-- Filter Tabs -->
      <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <Link
            :href="indexRoute().url"
            :class="[
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              !filters.status || filters.status === 'PENDING'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            ]"
          >
            Pending
            <span
              v-if="!filters.status || filters.status === 'PENDING'"
              class="ml-2 py-0.5 px-2 rounded-full text-xs font-medium bg-blue-100 text-blue-600"
            >
              {{ leaves.data.length }}
            </span>
          </Link>
          <Link
            :href="indexRoute({ query: { status: 'APPROVED' } }).url"
            :class="[
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              filters.status === 'APPROVED'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            ]"
          >
            Disetujui
          </Link>
          <Link
            :href="indexRoute({ query: { status: 'REJECTED' } }).url"
            :class="[
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              filters.status === 'REJECTED'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            ]"
          >
            Ditolak
          </Link>
        </nav>
      </div>

      <!-- Leave Requests List -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div v-if="leaves.data.length === 0" class="p-8 text-center">
          <p class="text-gray-500">Tidak ada permohonan izin</p>
        </div>

        <div v-else class="divide-y divide-gray-200">
          <div
            v-for="leave in leaves.data"
            :key="leave.id"
            class="p-6 hover:bg-gray-50"
          >
            <div class="flex items-start gap-4">
              <!-- Teacher Info -->
              <div class="flex-shrink-0">
                <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
                  <span class="text-lg font-medium text-gray-600">
                    {{ leave.teacher.name.charAt(0) }}
                  </span>
                </div>
              </div>

              <!-- Leave Details -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                  <p class="text-sm font-medium text-gray-900">
                    {{ leave.teacher.name }}
                  </p>
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

                <p class="text-sm text-gray-600 mb-1">
                  📅 {{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}
                  <span class="text-gray-500">({{ calculateDays(leave.start_date, leave.end_date) }} hari)</span>
                </p>

                <p class="text-sm text-gray-700 mb-2">
                  {{ leave.reason }}
                </p>

                <div v-if="leave.attachment_path" class="mb-2">
                  <a
                    :href="`/storage/${leave.attachment_path}`"
                    target="_blank"
                    class="text-xs text-blue-600 hover:text-blue-800"
                  >
                    📎 Lihat Lampiran
                  </a>
                </div>

                <p class="text-xs text-gray-500">
                  Diajukan pada {{ formatDateTime(leave.created_at) }}
                </p>

                <!-- Approval Info -->
                <div v-if="leave.status === 'APPROVED' && leave.approved_by" class="mt-2 text-xs text-green-600">
                  ✓ Disetujui oleh {{ leave.approved_by.name }} pada {{ formatDateTime(leave.approved_at) }}
                </div>

                <div v-if="leave.status === 'REJECTED'" class="mt-2 text-xs text-red-600">
                  ✗ Ditolak: {{ leave.rejection_reason }}
                </div>
              </div>

              <!-- Actions -->
              <div v-if="leave.status === 'PENDING'" class="flex-shrink-0 flex gap-2">
                <button
                  @click="approveLeave(leave.id)"
                  class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700"
                >
                  ✓ Setujui
                </button>
                <button
                  @click="openRejectModal(leave)"
                  class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
                >
                  ✗ Tolak
                </button>
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
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div
      v-if="showRejectModal"
      class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
      @click.self="closeRejectModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Tolak Permohonan Izin
          </h3>
          <p class="text-sm text-gray-600 mb-4">
            Berikan alasan penolakan untuk {{ selectedLeave?.teacher.name }}
          </p>
          <textarea
            v-model="rejectForm.rejection_reason"
            rows="4"
            placeholder="Alasan penolakan..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
          ></textarea>
          <p v-if="rejectForm.errors.rejection_reason" class="mt-1 text-sm text-red-600">
            {{ rejectForm.errors.rejection_reason }}
          </p>
        </div>
        <div class="bg-gray-50 px-6 py-3 flex justify-end gap-3 rounded-b-lg">
          <button
            @click="closeRejectModal"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Batal
          </button>
          <button
            @click="submitReject"
            :disabled="rejectForm.processing"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50"
          >
            {{ rejectForm.processing ? 'Memproses...' : 'Tolak' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/components/layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { index as indexRoute, approve as approveRoute, reject as rejectRoute } from '@/routes/principal/teacher-leaves'

interface TeacherLeave {
  id: number
  teacher: {
    name: string
    email: string
  }
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
  created_at: string
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
  filters: {
    status?: string
  }
}>()

const showRejectModal = ref(false)
const selectedLeave = ref<TeacherLeave | null>(null)

const rejectForm = useForm({
  rejection_reason: '',
})

const formatDate = (date: string | null | undefined) => {
  if (!date) return 'N/A'

  try {
    // Handle Laravel date format (YYYY-MM-DD)
    const parsedDate = new Date(date)

    // Check if date is valid
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

    // Check if date is valid
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

    // Check if dates are valid
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

const approveLeave = (leaveId: number) => {
  if (confirm('Apakah Anda yakin ingin menyetujui permohonan izin ini?')) {
    router.post(
      approveRoute(leaveId).url,
      {},
      {
        preserveScroll: true,
      }
    )
  }
}

const openRejectModal = (leave: TeacherLeave) => {
  selectedLeave.value = leave
  showRejectModal.value = true
  rejectForm.reset()
}

const closeRejectModal = () => {
  showRejectModal.value = false
  selectedLeave.value = null
  rejectForm.reset()
}

const submitReject = () => {
  if (!selectedLeave.value) return

  rejectForm.post(rejectRoute(selectedLeave.value.id).url, {
    preserveScroll: true,
    onSuccess: () => {
      closeRejectModal()
    },
  })
}
</script>
