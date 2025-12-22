<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Handle an incoming request dan log critical activities untuk audit trail,
     * yaitu: data modifications, unauthorized access attempts, dan important operations
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log hanya untuk authenticated users dan specific actions
        if (Auth::check() && $this->shouldLog($request)) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => $this->getActionName($request),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => $this->getRequestData($request),
                'status' => $response->isSuccessful() ? 'success' : 'failed',
            ]);
        }

        return $response;
    }

    /**
     * Tentukan apakah request perlu di-log berdasarkan method dan route
     */
    protected function shouldLog(Request $request): bool
    {
        // Log untuk POST, PUT, PATCH, DELETE (data modifications)
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])
            && ! $request->is('_ignition/*'); // Exclude debug routes
    }

    /**
     * Generate action name dari route atau method
     */
    protected function getActionName(Request $request): string
    {
        $routeName = $request->route()?->getName();

        if ($routeName) {
            return $routeName;
        }

        return strtolower($request->method()).'_'.$request->path();
    }

    /**
     * Extract relevant data dari request untuk logging
     */
    protected function getRequestData(Request $request): array
    {
        $data = $request->except(['password', 'password_confirmation', '_token']);

        // Limit size untuk prevent large payload
        return array_slice($data, 0, 20);
    }
}
