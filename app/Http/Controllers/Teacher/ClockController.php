<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClockInRequest;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClockController extends Controller
{
    /**
     * Constructor untuk inject AttendanceService dependency
     */
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Clock in guru dengan GPS coordinates
     * dan return JSON response untuk API consumption
     */
    public function clockIn(ClockInRequest $request): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->clockIn(
                $request->user(),
                $request->input('latitude'),
                $request->input('longitude')
            );

            return response()->json([
                'success' => true,
                'message' => 'Berhasil clock in',
                'data' => [
                    'clock_in' => $attendance->clock_in,
                    'status' => $attendance->status,
                    'is_late' => $attendance->is_late,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Clock out guru dengan GPS coordinates
     * dan calculate duration kerja
     */
    public function clockOut(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        try {
            $attendance = $this->attendanceService->clockOut(
                $request->user(),
                $request->input('latitude'),
                $request->input('longitude')
            );

            return response()->json([
                'success' => true,
                'message' => 'Berhasil clock out',
                'data' => [
                    'clock_in' => $attendance->clock_in,
                    'clock_out' => $attendance->clock_out,
                    'duration' => $attendance->duration,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get current clock status guru untuk widget dashboard
     * yang menampilkan apakah sudah clock in atau belum
     */
    public function status(Request $request): JsonResponse
    {
        $today = Carbon::today()->format('Y-m-d');
        $isClockedIn = $this->attendanceService->isAlreadyClockedIn($request->user(), $today);

        $attendance = null;
        $clockStatus = [
            'is_clocked_in' => false,
            'is_clocked_out' => false,
            'clock_in' => null,
            'clock_out' => null,
            'status' => null,
            'is_late' => false,
            'duration' => null,
        ];

        if ($isClockedIn) {
            $attendance = $request->user()->teacherAttendances()
                ->whereDate('tanggal', $today)
                ->first();

            if ($attendance) {
                $clockStatus = [
                    'is_clocked_in' => true,
                    'is_clocked_out' => $attendance->clock_out !== null,
                    'clock_in' => $attendance->clock_in,
                    'clock_out' => $attendance->clock_out,
                    'status' => $attendance->status,
                    'is_late' => $attendance->is_late ?? false,
                    'duration' => $attendance->duration ? $attendance->duration.' menit' : null,
                ];
            }
        }

        return response()->json([
            'data' => $clockStatus,
        ]);
    }

    /**
     * Show teacher's own attendance history dengan summary dan filters
     */
    public function myAttendance(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $teacher = $request->user();

        // Get attendances for the month
        $attendances = $teacher->teacherAttendances()
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->orderBy('tanggal', 'desc')
            ->paginate(31);

        // Calculate summary
        $allAttendances = $teacher->teacherAttendances()
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->get();

        // Calculate total hours from clock_in and clock_out
        $totalMinutes = 0;
        foreach ($allAttendances as $attendance) {
            if ($attendance->clock_in && $attendance->clock_out) {
                $dateStr = $attendance->tanggal->format('Y-m-d');
                $clockIn = \Carbon\Carbon::parse($dateStr.' '.$attendance->clock_in);
                $clockOut = \Carbon\Carbon::parse($dateStr.' '.$attendance->clock_out);
                $totalMinutes += $clockOut->diffInMinutes($clockIn);
            }
        }

        $summary = [
            'total_days' => $allAttendances->count(),
            'present_days' => $allAttendances->whereIn('status', ['HADIR', 'TERLAMBAT'])->count(),
            'late_days' => $allAttendances->where('is_late', true)->count(),
            'total_hours' => number_format($totalMinutes / 60, 1),
            'present_percentage' => $allAttendances->count() > 0
                ? number_format(($allAttendances->whereIn('status', ['HADIR', 'TERLAMBAT'])->count() / $allAttendances->count()) * 100, 1)
                : '0',
        ];

        return inertia('Teacher/Attendance/MyAttendance', [
            'title' => 'Riwayat Presensi Saya',
            'attendances' => $attendances,
            'summary' => $summary,
            'filters' => [
                'month' => (int) $month,
                'year' => (int) $year,
            ],
        ]);
    }
}
