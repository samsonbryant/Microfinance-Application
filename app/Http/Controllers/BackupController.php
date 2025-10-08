<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $backups = $this->getBackupFiles();
        return view('backup.index', compact('backups'));
    }

    public function create()
    {
        return view('backup.create');
    }

    public function store(Request $request)
    {
        try {
            // Create backup
            Artisan::call('backup:run');
            
            return redirect()->route('backup.index')
                ->with('success', 'Backup created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create backup: ' . $e->getMessage());
        }
    }

    public function restore(Request $request, $backup)
    {
        try {
            // Implement restore logic here
            return back()->with('success', 'Backup restored successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to restore backup: ' . $e->getMessage());
        }
    }

    public function destroy($backup)
    {
        try {
            Storage::delete('backups/' . $backup);
            return back()->with('success', 'Backup deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }

    private function getBackupFiles()
    {
        $files = Storage::files('backups');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file),
                'size' => Storage::size($file),
                'date' => Storage::lastModified($file),
            ];
        }

        return collect($backups)->sortByDesc('date');
    }
}

