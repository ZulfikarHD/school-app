<script setup lang="ts">
/**
 * ConflictWarning Component - Menampilkan warning konflik jadwal
 * dengan detail informasi jadwal yang berkonflik
 */
import { AlertTriangle, Clock, User, GraduationCap, MapPin } from 'lucide-vue-next';

interface ConflictDetail {
    message: string;
    conflicting_schedule?: {
        id: number;
        subject: string;
        class?: string;
        teacher?: string;
        time: string;
    };
}

interface Props {
    conflicts: Record<string, ConflictDetail>;
    timeErrors: Record<string, string>;
    isChecking?: boolean;
}

defineProps<Props>();

// Get icon for conflict type
const getConflictIcon = (type: string) => {
    switch (type) {
        case 'teacher':
            return User;
        case 'class':
            return GraduationCap;
        case 'room':
            return MapPin;
        default:
            return Clock;
    }
};
</script>

<template>
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-4 space-y-3">
        <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400">
            <AlertTriangle class="w-5 h-5" />
            <span class="font-medium">Peringatan Konflik Jadwal</span>
            <span v-if="isChecking" class="text-xs text-amber-500">(memeriksa...)</span>
        </div>

        <!-- Time Errors -->
        <div v-if="Object.keys(timeErrors).length > 0" class="space-y-2">
            <div
                v-for="(message, key) in timeErrors"
                :key="key"
                class="flex items-start gap-2 text-sm text-amber-700 dark:text-amber-300"
            >
                <Clock class="w-4 h-4 mt-0.5 shrink-0" />
                <span>{{ message }}</span>
            </div>
        </div>

        <!-- Conflict Details -->
        <div v-if="Object.keys(conflicts).length > 0" class="space-y-3">
            <div
                v-for="(conflict, type) in conflicts"
                :key="type"
                class="bg-white dark:bg-zinc-800 rounded-lg p-3 space-y-2"
            >
                <div class="flex items-center gap-2 text-sm font-medium text-amber-800 dark:text-amber-300">
                    <component :is="getConflictIcon(String(type))" class="w-4 h-4" />
                    <span>{{ conflict.message }}</span>
                </div>

                <div v-if="conflict.conflicting_schedule" class="pl-6 text-xs text-slate-600 dark:text-slate-400 space-y-1">
                    <p><span class="font-medium">Mapel:</span> {{ conflict.conflicting_schedule.subject }}</p>
                    <p v-if="conflict.conflicting_schedule.class">
                        <span class="font-medium">Kelas:</span> {{ conflict.conflicting_schedule.class }}
                    </p>
                    <p v-if="conflict.conflicting_schedule.teacher">
                        <span class="font-medium">Guru:</span> {{ conflict.conflicting_schedule.teacher }}
                    </p>
                    <p><span class="font-medium">Waktu:</span> {{ conflict.conflicting_schedule.time }}</p>
                </div>
            </div>
        </div>

        <p class="text-xs text-amber-600 dark:text-amber-400">
            Jadwal dengan konflik masih dapat disimpan, namun sangat disarankan untuk menghindari tumpang tindih.
        </p>
    </div>
</template>
