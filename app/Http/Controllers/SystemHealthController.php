<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SystemHealthController extends Controller
{
    public function index()
    {
        $health = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'system_info' => $this->getSystemInfo(),
        ];

        return view('system-health.index', compact('health'));
    }

    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => 'healthy',
                'message' => 'Database connection is active',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    private function checkCache()
    {
        try {
            Cache::put('health_check', 'ok', 10);
            $value = Cache::get('health_check');
            
            return [
                'status' => $value === 'ok' ? 'healthy' : 'unhealthy',
                'message' => 'Cache is working',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Cache error: ' . $e->getMessage(),
            ];
        }
    }

    private function checkStorage()
    {
        $totalSpace = disk_total_space(storage_path());
        $freeSpace = disk_free_space(storage_path());
        $usedPercentage = (($totalSpace - $freeSpace) / $totalSpace) * 100;

        return [
            'status' => $usedPercentage < 90 ? 'healthy' : 'warning',
            'total' => $this->formatBytes($totalSpace),
            'free' => $this->formatBytes($freeSpace),
            'used_percentage' => round($usedPercentage, 2),
        ];
    }

    private function checkQueue()
    {
        try {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();

            return [
                'status' => $failedJobs > 10 ? 'warning' : 'healthy',
                'pending' => $pendingJobs,
                'failed' => $failedJobs,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unknown',
                'message' => 'Could not check queue status',
            ];
        }
    }

    private function getSystemInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ];
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

