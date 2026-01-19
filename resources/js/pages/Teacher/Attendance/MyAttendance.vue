<template>
  <AppLayout :title="title">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
        <p class="mt-1 text-sm text-gray-600">
          Riwayat presensi clock in/out Anda
        </p>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <p class="text-sm text-gray-600">Bulan Ini</p>
          <p class="text-2xl font-bold text-gray-900">{{ summary.total_days }}</p>
          <p class="text-xs text-gray-500">Hari kerja</p>
        </div>

        <div class="bg-green-50 rounded-lg shadow-sm border border-green-200 p-4">
          <p class="text-sm text-green-600">Hadir</p>
          <p class="text-2xl font-bold text-green-900">{{ summary.present_days }}</p>
          <p class="text-xs text-green-600">{{ summary.present_percentage }}%</p>
        </div>

        <div class="bg-yellow-50 rounded-lg shadow-sm border border-yellow-200 p-4">
          <p class="text-sm text-yellow-600">Terlambat</p>
          <p class="text-2xl font-bold text-yellow-900">{{ summary.late_days }}</p>
          <p class="text-xs text-yellow-600">kali</p>
        </div>

        <div class="bg-blue-50 rounded-lg shadow-sm border border-blue-200 p-4">
          <p class="text-sm text-blue-600">Total Jam Kerja</p>
          <p class="text-2xl font-bold text-blue-900">{{ summary.total_hours }}</p>
          <p class="text-xs text-blue-600">jam</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Bulan
            </label>
            <select
              v-model="filters.month"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
              <option v-for="month in months" :key="month.value" :value="month.value">
                {{ month.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tahun
            </label>
            <select
              v-model="filters.year"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
              <option v-for="year in years" :key="year" :value="year">
                {{ year }}
              </option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              @click="resetFilters"
              class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Reset Filter
            </button>
          </div>
        </div>
      </div>

      <!-- Attendance Table -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tanggal
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Clock In
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Clock Out
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Durasi
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Keterangan
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="attendances.data.length === 0">
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                  Tidak ada data presensi untuk periode ini
                </td>
              </tr>
              <tr v-for="attendance in attendances.data" :key="attendance.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(attendance.tanggal) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ attendance.clock_in ? formatTime(attendance.clock_in) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ attendance.clock_out ? formatTime(attendance.clock_out) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ attendance.duration || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="{
                      'bg-green-100 text-green-800': attendance.status === 'HADIR',
                      'bg-yellow-100 text-yellow-800': attendance.status === 'TERLAMBAT',
                      'bg-blue-100 text-blue-800': attendance.status === 'IZIN',
                      'bg-red-100 text-red-800': attendance.status === 'SAKIT',
                      'bg-gray-100 text-gray-800': attendance.status === 'ALPHA',
                    }"
                  >
                    {{ attendance.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  <span v-if="attendance.is_late" class="text-yellow-600">
                    Terlambat {{ attendance.late_minutes }} menit
                  </span>
                  <span v-else-if="attendance.notes">
                    {{ attendance.notes }}
                  </span>
                  <span v-else>-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="attendances.links.length > 3" class="border-t border-gray-200 px-4 py-3 bg-gray-50">
          <div class="flex justify-center gap-2">
            <Link
              v-for="link in attendances.links"
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
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/components/layouts/AppLayout.vue'
import { myAttendance } from '@/routes/teacher'

interface TeacherAttendance {
  id: number
  tanggal: string
  clock_in: string | null
  clock_out: string | null
  duration: string | null
  status: 'HADIR' | 'TERLAMBAT' | 'IZIN' | 'SAKIT' | 'ALPHA'
  is_late: boolean
  late_minutes: number | null
  notes: string | null
}

interface PaginatedAttendances {
  data: TeacherAttendance[]
  links: Array<{
    url: string | null
    label: string
    active: boolean
  }>
}

interface Summary {
  total_days: number
  present_days: number
  late_days: number
  total_hours: string
  present_percentage: string
}

interface Props {
  title: string
  attendances: PaginatedAttendances
  summary: Summary
  filters: {
    month: number
    year: number
  }
}

const props = defineProps<Props>()

const filters = ref({
  month: props.filters.month,
  year: props.filters.year,
})

const months = [
  { value: 1, label: 'Januari' },
  { value: 2, label: 'Februari' },
  { value: 3, label: 'Maret' },
  { value: 4, label: 'April' },
  { value: 5, label: 'Mei' },
  { value: 6, label: 'Juni' },
  { value: 7, label: 'Juli' },
  { value: 8, label: 'Agustus' },
  { value: 9, label: 'September' },
  { value: 10, label: 'Oktober' },
  { value: 11, label: 'November' },
  { value: 12, label: 'Desember' },
]

const currentYear = new Date().getFullYear()
const years = Array.from({ length: 3 }, (_, i) => currentYear - i)

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

const formatTime = (timeString: string) => {
  // timeString is in format HH:MM:SS
  // Extract HH:MM only
  const [hours, minutes] = timeString.split(':')
  return `${hours}:${minutes}`
}

const applyFilters = () => {
  router.get(
    myAttendance().url,
    {
      month: filters.value.month,
      year: filters.value.year,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  )
}

const resetFilters = () => {
  const now = new Date()
  filters.value = {
    month: now.getMonth() + 1,
    year: now.getFullYear(),
  }
  applyFilters()
}
</script>
