<?php

namespace Sagor\LaravelSecurity\Dashboard;

use Illuminate\Support\Facades\DB;
use Sagor\LaravelSecurity\Models\BlockedIp;
use Sagor\LaravelSecurity\Models\MalwareScan;
use Sagor\LaravelSecurity\Models\SecurityEvent;

class DashboardManager
{
    /**
     * Fetch overview metrics and summary statistics for dashboard display.
     *
     * @return array
     */
    public function getOverviewStats(): array
    {
        $stats = [
            'total_events' => 0,
            'total_blocked' => 0,
            'total_throttled' => 0,
            'blocked_ips_count' => 0,
            'malware_scans_count' => 0,
            'redis_status' => 'OK',
            'top_threats' => [],
            'hourly_labels' => [],
            'hourly_counts' => [],
            'hourly_blocked' => [],
            'threat_distribution' => [],
        ];

        try {
            if (class_exists('Sagor\LaravelSecurity\Models\SecurityEvent')) {
                $stats['total_events'] = SecurityEvent::count();
                $stats['total_blocked'] = SecurityEvent::where('action', 'block')->count();
                $stats['total_throttled'] = SecurityEvent::where('action', 'throttle')->count();

                // Top threat categories
                $top = SecurityEvent::select('type', DB::raw('count(*) as total'))
                    ->groupBy('type')
                    ->orderBy('total', 'desc')
                    ->limit(5)
                    ->get();
                $stats['top_threats'] = $top ? $top->toArray() : [];

                // Real 24-hour time-series data for chart
                $labels = [];
                $counts = [];
                $blocked = [];

                for ($i = 23; $i >= 0; $i--) {
                    $hourTime = date('Y-m-d H:00:00', strtotime("-{$i} hours"));
                    $hourNext = date('Y-m-d H:59:59', strtotime("-{$i} hours"));
                    $label = date('H:00', strtotime("-{$i} hours"));

                    $totalCount = SecurityEvent::whereBetween('created_at', [$hourTime, $hourNext])->count();
                    $blockedCount = SecurityEvent::whereBetween('created_at', [$hourTime, $hourNext])
                        ->where('action', 'block')
                        ->count();

                    $labels[] = $label;
                    $counts[] = $totalCount;
                    $blocked[] = $blockedCount;
                }

                $stats['hourly_labels'] = $labels;
                $stats['hourly_counts'] = $counts;
                $stats['hourly_blocked'] = $blocked;

                // Category distribution percentage breakdown
                $distribution = SecurityEvent::select('type', DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray();

                $stats['threat_distribution'] = $distribution;
            }

            if (class_exists('Sagor\LaravelSecurity\Models\BlockedIp')) {
                $stats['blocked_ips_count'] = BlockedIp::count();
            }

            if (class_exists('Sagor\LaravelSecurity\Models\MalwareScan')) {
                $stats['malware_scans_count'] = MalwareScan::count();
            }
        } catch (\Throwable $e) {
            // Fail open
        }

        return $stats;
    }
}
