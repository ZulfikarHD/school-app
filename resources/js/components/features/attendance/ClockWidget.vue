<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Clock, MapPin, AlertCircle, CheckCircle2, LogIn, LogOut } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useHaptics } from '@/composables/useHaptics';
import { useModal } from '@/composables/useModal';
import type { ClockStatus } from '@/types/attendance';

/**
 * Clock In/Out Widget untuk teacher attendance dengan GPS tracking
 * Menampilkan status clock in/out, durasi, dan handling geolocation permission
 */

interface Props {
    teacherId: number;
}

defineProps<Props>();

const haptics = useHaptics();
const modal = useModal();

const clockStatus = ref<ClockStatus | null>(null);
const loading = ref(false);
const gpsLoading = ref(false);

// Check if running in browser (not SSR)
const isBrowser = typeof window !== 'undefined';
const hasGeolocation = isBrowser && 'geolocation' in navigator;

/**
 * Format waktu ke format HH:mm WIB
 */
const formatTime = (time?: string) => {
    if (!time) return '-';
    const date = new Date(`2000-01-01T${time}`);
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        timeZone: 'Asia/Jakarta'
    }) + ' WIB';
};

/**
 * Status message yang ditampilkan ke user
 */
const statusMessage = computed(() => {
    if (!clockStatus.value) return 'Loading...';

    if (clockStatus.value.is_clocked_out) {
        return `Sudah Clock Out pada ${formatTime(clockStatus.value.clock_out)}`;
    }

    if (clockStatus.value.is_clocked_in) {
        const time = formatTime(clockStatus.value.clock_in);
        const status = clockStatus.value.is_late ? '⚠️ Terlambat' : '✓ Tepat Waktu';
        return `Sudah Clock In pada ${time} ${status}`;
    }

    return 'Belum Clock In Hari Ini';
});

/**
 * Menentukan apakah button clock in tersedia
 */
const canClockIn = computed(() => {
    return clockStatus.value && !clockStatus.value.is_clocked_in;
});

/**
 * Menentukan apakah button clock out tersedia
 */
const canClockOut = computed(() => {
    return clockStatus.value && clockStatus.value.is_clocked_in && !clockStatus.value.is_clocked_out;
});

/**
 * Fetch clock status dari backend
 */
const fetchClockStatus = async () => {
    if (!isBrowser) return;

    loading.value = true;
    try {
        const response = await fetch('/teacher/clock/status', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) throw new Error('Failed to fetch status');

        const data = await response.json();
        clockStatus.value = data.data;
    } catch (error) {
        console.error('Error fetching clock status:', error);
        modal.error('Gagal memuat status clock in/out');
    } finally {
        loading.value = false;
    }
};

/**
 * Request GPS location dari browser
 * dengan error handling untuk permission denied
 */
const getLocation = (): Promise<{ latitude: number; longitude: number }> => {
    return new Promise((resolve, reject) => {
        if (!hasGeolocation) {
            reject(new Error('Browser tidak mendukung GPS'));
            return;
        }

        gpsLoading.value = true;

        // Try to get permission first
        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'geolocation' }).then((permissionStatus) => {
                console.log('Geolocation permission:', permissionStatus.state);
            }).catch(err => {
                console.log('Permission query not supported:', err);
            });
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                gpsLoading.value = false;
                resolve({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                });
            },
            (error) => {
                gpsLoading.value = false;
                let message = 'Gagal mendapatkan lokasi';

                if (error.code === 1) { // PERMISSION_DENIED
                    message = 'Izin GPS ditolak. Klik ikon kunci/info di address bar browser, lalu izinkan akses lokasi.';
                } else if (error.code === 2) { // POSITION_UNAVAILABLE
                    message = 'Lokasi tidak tersedia. Pastikan GPS aktif.';
                } else if (error.code === 3) { // TIMEOUT
                    message = 'Request GPS timeout. Coba lagi.';
                }

                console.error('Geolocation error:', error);
                reject(new Error(message));
            },
            {
                enableHighAccuracy: false, // Changed to false for faster response
                timeout: 15000, // Increased timeout
                maximumAge: 60000 // Allow cached position
            }
        );
    });
};

/**
 * Handle clock in action dengan GPS tracking
 */
const handleClockIn = async () => {
    try {
        haptics.light();

        console.log('Requesting GPS location...');
        const location = await getLocation();
        console.log('GPS location obtained:', location);

        loading.value = true;

        router.post('/teacher/clock/in', {
            latitude: location.latitude,
            longitude: location.longitude
        }, {
            preserveScroll: true,
            onSuccess: () => {
                haptics.success();
                modal.success('Berhasil Clock In');
                fetchClockStatus();
            },
            onError: (errors: any) => {
                haptics.error();
                const message = errors.message || 'Gagal melakukan clock in';
                modal.error(message);
            },
            onFinish: () => {
                loading.value = false;
            }
        });
    } catch (error: any) {
        console.error('Clock in error:', error);
        haptics.error();
        modal.error(error.message || 'Gagal melakukan clock in');
    }
};

/**
 * Handle clock out action dengan GPS tracking
 */
const handleClockOut = async () => {
    try {
        const confirmed = await modal.confirm(
            'Konfirmasi Clock Out',
            'Apakah Anda yakin ingin clock out sekarang?',
            'Ya, Clock Out',
            'Batal'
        );

        if (!confirmed) return;

        haptics.light();

        const location = await getLocation();

        loading.value = true;

        router.post('/teacher/clock/out', {
            latitude: location.latitude,
            longitude: location.longitude
        }, {
            preserveScroll: true,
            onSuccess: () => {
                haptics.success();
                modal.success('Berhasil Clock Out. Terima kasih atas kerja keras Anda hari ini!');
                fetchClockStatus();
            },
            onError: (errors: any) => {
                haptics.error();
                const message = errors.message || 'Gagal melakukan clock out';
                modal.error(message);
            },
            onFinish: () => {
                loading.value = false;
            }
        });
    } catch (error: any) {
        if (error.message) {
            haptics.error();
            modal.error(error.message);
        }
    }
};

onMounted(() => {
    fetchClockStatus();
});
</script>

<template>
    <Motion
        :initial="{ opacity: 0, y: 20, scale: 0.95 }"
        :animate="{ opacity: 1, y: 0, scale: 1 }"
        :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
    >
        <div class="overflow-hidden rounded-2xl bg-linear-to-br from-emerald-500 to-teal-600 shadow-lg border border-emerald-200/50">
            <div class="p-6 text-white">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <Clock :size="20" />
                        <h3 class="text-sm font-semibold uppercase tracking-wide">Presensi Guru</h3>
                    </div>

                    <!-- Status Indicator -->
                    <div v-if="clockStatus?.is_clocked_in && !clockStatus?.is_clocked_out" class="flex items-center gap-1">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                        </span>
                        <span class="text-[10px] font-medium">Aktif</span>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading && !clockStatus" class="space-y-3">
                    <div class="h-8 bg-white/20 rounded-lg animate-pulse"></div>
                    <div class="h-10 bg-white/20 rounded-xl animate-pulse"></div>
                </div>

                <!-- Clock Status -->
                <div v-else class="space-y-4">
                    <!-- Status Message -->
                    <div>
                        <p class="text-2xl font-bold">{{ statusMessage }}</p>
                        <p v-if="clockStatus?.duration" class="text-sm text-white/80 mt-1">
                            Durasi Kerja: {{ clockStatus.duration }}
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <!-- Clock In Button dengan accessible focus state -->
                        <Motion
                            v-if="canClockIn"
                            :whileTap="{ scale: 0.97 }"
                            class="flex-1"
                        >
                            <button
                                @click="handleClockIn"
                                :disabled="loading || gpsLoading"
                                :aria-busy="gpsLoading"
                                aria-label="Clock In - Mulai presensi hari ini"
                                class="w-full px-4 py-3 min-h-[48px] bg-white text-emerald-600 rounded-xl font-semibold
                                       hover:bg-white/95 active:bg-white/90
                                       disabled:opacity-50 disabled:cursor-not-allowed
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-600
                                       transition-all duration-150
                                       flex items-center justify-center gap-2"
                            >
                                <LogIn v-if="!gpsLoading" :size="18" aria-hidden="true" />
                                <MapPin v-else :size="18" class="animate-pulse" aria-hidden="true" />
                                <span>{{ gpsLoading ? 'Mencari Lokasi...' : 'Masuk' }}</span>
                            </button>
                        </Motion>

                        <!-- Clock Out Button dengan accessible focus state -->
                        <Motion
                            v-if="canClockOut"
                            :whileTap="{ scale: 0.97 }"
                            class="flex-1"
                        >
                            <button
                                @click="handleClockOut"
                                :disabled="loading || gpsLoading"
                                :aria-busy="gpsLoading"
                                aria-label="Clock Out - Selesai presensi hari ini"
                                class="w-full px-4 py-3 min-h-[48px] bg-white/10 text-white rounded-xl font-semibold
                                       border border-white/30
                                       hover:bg-white/20 active:bg-white/15
                                       disabled:opacity-50 disabled:cursor-not-allowed
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-600
                                       transition-all duration-150
                                       flex items-center justify-center gap-2"
                            >
                                <LogOut v-if="!gpsLoading" :size="18" aria-hidden="true" />
                                <MapPin v-else :size="18" class="animate-pulse" aria-hidden="true" />
                                <span>{{ gpsLoading ? 'Mencari Lokasi...' : 'Pulang' }}</span>
                            </button>
                        </Motion>

                        <!-- Already Clocked Out -->
                        <div 
                            v-if="clockStatus?.is_clocked_out" 
                            class="flex-1 px-4 py-3 min-h-[48px] bg-white/10 text-white rounded-xl font-semibold border border-white/30 flex items-center justify-center gap-2"
                            role="status"
                            aria-label="Presensi hari ini sudah selesai"
                        >
                            <CheckCircle2 :size="18" aria-hidden="true" />
                            <span>Selesai Hari Ini</span>
                        </div>
                    </div>

                    <!-- GPS Warning -->
                    <div v-if="!hasGeolocation" class="flex items-start gap-2 p-3 bg-amber-50/10 border border-amber-200/30 rounded-lg">
                        <AlertCircle :size="16" class="text-amber-200 flex-shrink-0 mt-0.5" />
                        <p class="text-xs text-amber-50">
                            Browser tidak mendukung GPS. Hubungi admin untuk bantuan.
                        </p>
                    </div>

                    <!-- Link to Attendance History dengan proper focus state -->
                    <div class="mt-3 pt-3 border-t border-white/20">
                        <a
                            href="/teacher/my-attendance"
                            class="text-xs text-white/80 hover:text-white flex items-center justify-center gap-1 transition-colors py-2 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50 focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-600"
                            @click="haptics.light()"
                        >
                            <Clock :size="14" aria-hidden="true" />
                            <span>Lihat Riwayat Presensi Saya →</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </Motion>
</template>
