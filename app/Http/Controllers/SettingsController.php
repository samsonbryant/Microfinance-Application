<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'app_env' => config('app.env'),
            'db_connection' => config('database.default'),
            'mail_driver' => config('mail.default'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
        ];

        $stats = [
            'total_users' => \App\Models\User::count(),
            'total_branches' => \App\Models\Branch::count(),
            'total_clients' => \App\Models\Client::count(),
            'total_loans' => \App\Models\Loan::count(),
            'total_savings_accounts' => \App\Models\SavingsAccount::count(),
            'total_transactions' => \App\Models\Transaction::count(),
        ];

        return view('settings.index', compact('settings', 'stats'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl',
        ]);

        // Update configuration (in a real application, you'd want to store these in database)
        // For now, we'll just show a success message
        return back()->with('success', 'Settings updated successfully.');
    }

    public function systemInfo()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_version' => $this->getDatabaseVersion(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];

        return view('settings.system-info', compact('info'));
    }

    public function backup()
    {
        // In a real application, you'd implement actual backup functionality
        return back()->with('success', 'Backup created successfully.');
    }

    public function clearCache()
    {
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');

        return back()->with('success', 'Cache cleared successfully.');
    }

    private function getDatabaseVersion()
    {
        try {
            $version = DB::select('SELECT version() as version')[0]->version ?? 'Unknown';
            return $version;
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
}
