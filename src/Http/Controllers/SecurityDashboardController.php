<?php

namespace Sagor\LaravelSecurity\Http\Controllers;

use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Dashboard\DashboardManager;
use Sagor\LaravelSecurity\Models\BlockedIp;
use Sagor\LaravelSecurity\Models\MalwareScan;
use Sagor\LaravelSecurity\Models\SecurityEvent;

class SecurityDashboardController
{
    /**
     * Display Security Dashboard.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        // Enforce Authentication if enabled in config ('require_auth' => true or SECURITY_DASHBOARD_AUTH=true)
        if (config('security.dashboard.require_auth', false)) {
            $user = $request->user();
            $isLoggedIn = $user !== null || (function_exists('auth') && auth()->check());

            if (!$isLoggedIn) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message' => 'Unauthenticated access to Security Dashboard.',
                        'code' => 'UNAUTHENTICATED_SECURITY_ACCESS',
                    ], 401);
                }

                if (function_exists('route') && \Illuminate\Support\Facades\Route::has('login')) {
                    return redirect()->guest(route('login'));
                }

                if (view()->exists('laravel-security::blocked')) {
                    return response(view('laravel-security::blocked', [
                        'reason' => 'ACCESS DENIED: Only authenticated logged-in users are authorized to access the Security Command Center & Cyber Desk Workstation.',
                        'ip' => $request->ip(),
                    ]), 403);
                }

                return response('Access Denied: Only authenticated logged-in users can access the Security Dashboard.', 403);
            }
        }

        $manager = new DashboardManager();
        $stats = $manager->getOverviewStats();

        $events = collect();
        $blockedIps = collect();
        $malwareScans = collect();

        $search = trim((string) $request->input('search', ''));
        $type = trim((string) $request->input('type', ''));
        $severity = trim((string) $request->input('severity', ''));
        $action = trim((string) $request->input('action', ''));
        $perPage = (int) $request->input('per_page', 20);
        if ($perPage < 5) { $perPage = 20; }

        try {
            if (class_exists('Sagor\LaravelSecurity\Models\SecurityEvent')) {
                $query = SecurityEvent::query();

                if ($search !== '') {
                    $query->where(function ($q) use ($search) {
                        $q->where('event_id', 'like', "%{$search}%")
                          ->orWhere('route', 'like', "%{$search}%")
                          ->orWhere('type', 'like', "%{$search}%")
                          ->orWhere('ip_hash', 'like', "%{$search}%");
                    });
                }

                if ($type !== '') {
                    $query->where('type', $type);
                }

                if ($severity !== '') {
                    $query->where('severity', $severity);
                }

                if ($action !== '') {
                    $query->where('action', $action);
                }

                $events = $query->orderBy('id', 'desc')->paginate($perPage)->appends($request->all());
            }

            if (class_exists('Sagor\LaravelSecurity\Models\BlockedIp')) {
                $blockedIps = BlockedIp::orderBy('id', 'desc')->paginate(10);
            }
            if (class_exists('Sagor\LaravelSecurity\Models\MalwareScan')) {
                $malwareScans = MalwareScan::orderBy('id', 'desc')->paginate(10);
            }
        } catch (\Throwable $e) {
            // Database tables might not be migrated yet
        }

        $view = $request->input('view', 'dashboard');

        if (($view === 'attempts' || $request->routeIs('security.attempts')) && view()->exists('laravel-security::attempts')) {
            return view('laravel-security::attempts', compact('stats', 'events', 'blockedIps', 'malwareScans', 'search', 'type', 'severity', 'action', 'perPage'));
        }

        if (view()->exists('laravel-security::dashboard')) {
            return view('laravel-security::dashboard', compact('stats', 'events', 'blockedIps', 'malwareScans', 'search', 'type', 'severity', 'action', 'perPage'));
        }

        // Inline HTML fallback if views fail to publish
        return response()->json([
            'status' => 'Laravel Security Firewall Dashboard Active',
            'stats' => $stats,
        ]);
    }

    /**
     * Display Cyber Desk Security Attempts Page.
     *
     * @param Request $request
     * @return mixed
     */
    public function attempts(Request $request)
    {
        $request->merge(['view' => 'attempts']);
        return $this->index($request);
    }
}
