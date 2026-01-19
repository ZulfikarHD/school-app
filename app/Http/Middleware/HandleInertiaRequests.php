<?php

namespace App\Http\Middleware;

use App\Models\LeaveRequest;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * Sprint C Enhancement:
     * - Added pendingCounts untuk menampilkan badge notification di navigation
     * - Pending counts dihitung berdasarkan role user untuk leave request verification
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            'pendingCounts' => $this->getPendingCounts($request),
        ];
    }

    /**
     * Mendapatkan pending counts berdasarkan role user
     * untuk ditampilkan sebagai badge notification di navigation
     *
     * @return array<string, int>
     */
    protected function getPendingCounts(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            return [
                'leaveRequests' => 0,
            ];
        }

        $pendingLeaveRequests = 0;

        // Teacher: pending leave requests untuk siswa di kelas yang diampu
        if ($user->role === 'TEACHER') {
            $pendingLeaveRequests = LeaveRequest::whereHas('student.kelas', function ($query) use ($user) {
                $query->where('wali_kelas_id', $user->id);
            })
                ->pending()
                ->count();
        }

        // Admin/Superadmin: semua pending leave requests
        if (in_array($user->role, ['ADMIN', 'SUPERADMIN'])) {
            $pendingLeaveRequests = LeaveRequest::pending()->count();
        }

        return [
            'leaveRequests' => $pendingLeaveRequests,
        ];
    }
}
