<script setup lang="ts">
/**
 * ScheduleMatrix Component - Matrix view untuk jadwal mengajar
 * Menampilkan jadwal dalam format grid (hari x waktu) dengan color coding
 */
import { computed } from 'vue';
import { Clock, User, GraduationCap } from 'lucide-vue-next';

interface Schedule {
    id: number;
    hari: { value: string; label?: string } | string;
    jam_mulai: string;
    jam_selesai: string;
    ruangan: string | null;
    time_range: string;
    teacher?: {
        id: number;
        nama_lengkap: string;
    };
    subject: {
        id: number;
        kode_mapel: string;
        nama_mapel: string;
    };
    school_class?: {
        id: number;
        tingkat: number;
        nama: string;
    };
}

interface HariOption {
    value: string;
    label: string;
}

interface Props {
    schedules: Schedule[];
    hariOptions: HariOption[];
    showTeacher?: boolean;  // Show teacher name (for class view)
    showClass?: boolean;    // Show class name (for teacher view)
}

const props = withDefaults(defineProps<Props>(), {
    showTeacher: false,
    showClass: true
});

const emit = defineEmits(['click-schedule']);

// Time slots from 07:00 to 16:00 (30-minute increments)
const timeSlots = computed(() => {
    const slots: string[] = [];
    for (let hour = 7; hour <= 15; hour++) {
        slots.push(`${hour.toString().padStart(2, '0')}:00`);
        slots.push(`${hour.toString().padStart(2, '0')}:30`);
    }
    slots.push('16:00');
    return slots;
});

// Group schedules by day
const schedulesByDay = computed(() => {
    const grouped: Record<string, Schedule[]> = {};
    props.hariOptions.forEach(h => {
        grouped[h.value] = [];
    });

    props.schedules.forEach(schedule => {
        const hariValue = typeof schedule.hari === 'object' ? schedule.hari.value : schedule.hari;
        if (grouped[hariValue]) {
            grouped[hariValue].push(schedule);
        }
    });

    return grouped;
});

// Get schedule at specific day and time slot
const getScheduleAt = (hari: string, time: string): Schedule | null => {
    const schedules = schedulesByDay.value[hari] || [];
    return schedules.find(s => {
        const start = s.jam_mulai.substring(0, 5);
        const end = s.jam_selesai.substring(0, 5);
        return time >= start && time < end;
    }) || null;
};

// Check if this time slot is the start of a schedule
const isScheduleStart = (hari: string, time: string): boolean => {
    const schedule = getScheduleAt(hari, time);
    if (!schedule) return false;
    return schedule.jam_mulai.substring(0, 5) === time;
};

// Calculate row span for a schedule
const getRowSpan = (schedule: Schedule): number => {
    const start = schedule.jam_mulai.substring(0, 5);
    const end = schedule.jam_selesai.substring(0, 5);
    const startIndex = timeSlots.value.indexOf(start);
    const endIndex = timeSlots.value.indexOf(end);
    if (startIndex === -1 || endIndex === -1) return 1;
    return endIndex - startIndex;
};

// Generate color based on subject ID (consistent colors)
const getSubjectColor = (subjectId: number): string => {
    const colors = [
        'bg-blue-100 dark:bg-blue-900/40 border-blue-300 dark:border-blue-700 text-blue-800 dark:text-blue-200',
        'bg-emerald-100 dark:bg-emerald-900/40 border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200',
        'bg-amber-100 dark:bg-amber-900/40 border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-200',
        'bg-purple-100 dark:bg-purple-900/40 border-purple-300 dark:border-purple-700 text-purple-800 dark:text-purple-200',
        'bg-rose-100 dark:bg-rose-900/40 border-rose-300 dark:border-rose-700 text-rose-800 dark:text-rose-200',
        'bg-cyan-100 dark:bg-cyan-900/40 border-cyan-300 dark:border-cyan-700 text-cyan-800 dark:text-cyan-200',
        'bg-orange-100 dark:bg-orange-900/40 border-orange-300 dark:border-orange-700 text-orange-800 dark:text-orange-200',
        'bg-indigo-100 dark:bg-indigo-900/40 border-indigo-300 dark:border-indigo-700 text-indigo-800 dark:text-indigo-200',
        'bg-pink-100 dark:bg-pink-900/40 border-pink-300 dark:border-pink-700 text-pink-800 dark:text-pink-200',
        'bg-teal-100 dark:bg-teal-900/40 border-teal-300 dark:border-teal-700 text-teal-800 dark:text-teal-200',
    ];
    return colors[subjectId % colors.length];
};

// Format class name
const formatClassName = (schedule: Schedule): string => {
    if (schedule.school_class) {
        return `${schedule.school_class.tingkat}${schedule.school_class.nama}`;
    }
    return '-';
};
</script>

<template>
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200 dark:border-zinc-800 overflow-hidden">
        <!-- Desktop Matrix View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-zinc-800/50">
                        <th class="sticky left-0 z-10 bg-slate-50 dark:bg-zinc-800/50 px-3 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-r border-slate-200 dark:border-zinc-700 w-20">
                            <div class="flex items-center gap-1.5">
                                <Clock class="w-4 h-4" />
                                <span>Waktu</span>
                            </div>
                        </th>
                        <th
                            v-for="option in hariOptions"
                            :key="option.value"
                            class="px-2 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-zinc-700 min-w-[140px]"
                        >
                            {{ option.label }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(time, index) in timeSlots"
                        :key="time"
                        :class="[
                            index % 2 === 0 ? 'bg-white dark:bg-zinc-900' : 'bg-slate-50/50 dark:bg-zinc-800/20',
                            time.endsWith(':00') ? 'border-t border-slate-200 dark:border-zinc-700' : ''
                        ]"
                    >
                        <!-- Time Column -->
                        <td class="sticky left-0 z-10 px-3 py-1 text-xs text-slate-500 dark:text-slate-400 border-r border-slate-200 dark:border-zinc-700 font-mono"
                            :class="index % 2 === 0 ? 'bg-white dark:bg-zinc-900' : 'bg-slate-50/50 dark:bg-zinc-800/20'">
                            {{ time }}
                        </td>

                        <!-- Day Columns -->
                        <template v-for="option in hariOptions" :key="option.value">
                            <!-- If schedule starts at this slot -->
                            <td
                                v-if="isScheduleStart(option.value, time)"
                                :rowspan="getRowSpan(getScheduleAt(option.value, time)!)"
                                class="p-1 border-l border-slate-100 dark:border-zinc-800 align-top"
                            >
                                <button
                                    @click="emit('click-schedule', getScheduleAt(option.value, time))"
                                    class="w-full h-full min-h-[60px] p-2 rounded-lg border-2 text-left transition-all hover:shadow-md cursor-pointer"
                                    :class="getSubjectColor(getScheduleAt(option.value, time)!.subject.id)"
                                >
                                    <div class="font-semibold text-sm truncate">
                                        {{ getScheduleAt(option.value, time)!.subject.nama_mapel }}
                                    </div>
                                    <div class="text-xs mt-1 opacity-80">
                                        {{ getScheduleAt(option.value, time)!.time_range }}
                                    </div>
                                    <div v-if="showTeacher && getScheduleAt(option.value, time)!.teacher" class="text-xs mt-1 flex items-center gap-1 opacity-80">
                                        <User class="w-3 h-3" />
                                        <span class="truncate">{{ getScheduleAt(option.value, time)!.teacher?.nama_lengkap }}</span>
                                    </div>
                                    <div v-if="showClass && getScheduleAt(option.value, time)!.school_class" class="text-xs mt-1 flex items-center gap-1 opacity-80">
                                        <GraduationCap class="w-3 h-3" />
                                        <span>Kelas {{ formatClassName(getScheduleAt(option.value, time)!) }}</span>
                                    </div>
                                    <div v-if="getScheduleAt(option.value, time)!.ruangan" class="text-xs mt-1 opacity-70">
                                        {{ getScheduleAt(option.value, time)!.ruangan }}
                                    </div>
                                </button>
                            </td>

                            <!-- Empty cell if no schedule and not covered by rowspan -->
                            <td
                                v-else-if="!getScheduleAt(option.value, time)"
                                class="p-1 border-l border-slate-100 dark:border-zinc-800"
                            >
                                <div class="h-8"></div>
                            </td>
                            <!-- Cell is covered by rowspan - don't render -->
                        </template>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile/Tablet Card View (grouped by day) -->
        <div class="lg:hidden">
            <div
                v-for="option in hariOptions"
                :key="option.value"
                class="border-b border-slate-200 dark:border-zinc-800 last:border-b-0"
            >
                <!-- Day Header -->
                <div class="px-4 py-3 bg-slate-50 dark:bg-zinc-800/50 font-semibold text-slate-700 dark:text-slate-300">
                    {{ option.label }}
                </div>

                <!-- Schedules for this day -->
                <div v-if="schedulesByDay[option.value]?.length > 0" class="divide-y divide-slate-100 dark:divide-zinc-800">
                    <button
                        v-for="schedule in schedulesByDay[option.value]"
                        :key="schedule.id"
                        @click="emit('click-schedule', schedule)"
                        class="w-full p-4 text-left hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="w-2 h-full min-h-[60px] rounded-full shrink-0"
                                :class="getSubjectColor(schedule.subject.id).split(' ')[0]"
                            ></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-1">
                                    <Clock class="w-4 h-4" />
                                    <span>{{ schedule.time_range }}</span>
                                </div>
                                <h4 class="font-semibold text-slate-900 dark:text-slate-100">
                                    {{ schedule.subject.nama_mapel }}
                                </h4>
                                <div class="mt-1 text-sm text-slate-600 dark:text-slate-400 space-y-0.5">
                                    <div v-if="showTeacher && schedule.teacher" class="flex items-center gap-1.5">
                                        <User class="w-3.5 h-3.5" />
                                        <span>{{ schedule.teacher.nama_lengkap }}</span>
                                    </div>
                                    <div v-if="showClass && schedule.school_class" class="flex items-center gap-1.5">
                                        <GraduationCap class="w-3.5 h-3.5" />
                                        <span>Kelas {{ formatClassName(schedule) }}</span>
                                    </div>
                                    <div v-if="schedule.ruangan" class="text-slate-500">
                                        {{ schedule.ruangan }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </button>
                </div>

                <!-- Empty State for day -->
                <div v-else class="p-4 text-center text-slate-400 dark:text-slate-500 text-sm">
                    Tidak ada jadwal
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="px-4 py-3 bg-slate-50 dark:bg-zinc-800/50 border-t border-slate-200 dark:border-zinc-700">
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <span class="font-medium">Keterangan:</span>
                <span>Klik jadwal untuk melihat detail atau edit</span>
            </div>
        </div>
    </div>
</template>
