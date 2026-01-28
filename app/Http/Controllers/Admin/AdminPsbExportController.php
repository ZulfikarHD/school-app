<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PsbRegistrationsExport;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\PsbRegistration;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * AdminPsbExportController - Controller untuk export data pendaftaran PSB
 *
 * Controller ini bertujuan untuk menyediakan fungsi export data pendaftaran
 * ke format Excel dengan filter berdasarkan status, tanggal, dan opsi kolom
 */
class AdminPsbExportController extends Controller
{
    /**
     * Export data pendaftaran PSB ke file Excel
     * dengan filter berdasarkan status dan range tanggal
     */
    public function export(Request $request): BinaryFileResponse
    {
        // Get active academic year
        $activeYear = AcademicYear::where('is_active', true)->first();

        // Build query dengan filters
        $query = PsbRegistration::with(['academicYear', 'payments'])
            ->when($activeYear, function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id);
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->input('status'));
            })
            ->when($request->filled('start_date'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->input('start_date'));
            })
            ->when($request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->input('end_date'));
            })
            ->orderBy('created_at', 'desc');

        // Generate filename dengan timestamp
        $timestamp = now()->format('Y-m-d_His');
        $filename = "psb_registrations_{$timestamp}.xlsx";

        return Excel::download(
            new PsbRegistrationsExport($query),
            $filename
        );
    }
}
