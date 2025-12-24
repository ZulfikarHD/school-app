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
        if ($isClockedIn) {
            $attendance = $request->user()->teacherAttendances()
                ->whereDate('tanggal', $today)
                ->first();
        }

        return response()->json([
            'is_clocked_in' => $isClockedIn,
            'attendance' => $attendance,
        ]);
    }
}
