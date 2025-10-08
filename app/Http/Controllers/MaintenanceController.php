<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class MaintenanceController extends Controller
{
    public function index()
    {
        return view('maintenance.index');
    }

    public function clearCache(Request $request)
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return back()->with('success', 'All caches cleared successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    public function optimizeApp(Request $request)
    {
        try {
            Artisan::call('optimize');
            Artisan::call('config:cache');
            Artisan::call('route:cache');

            return back()->with('success', 'Application optimized successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to optimize application: ' . $e->getMessage());
        }
    }
}

