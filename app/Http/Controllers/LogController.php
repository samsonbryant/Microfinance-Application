<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    public function index()
    {
        $logPath = storage_path('logs');
        $logs = [];

        if (File::exists($logPath)) {
            $files = File::files($logPath);
            
            foreach ($files as $file) {
                if ($file->getExtension() === 'log') {
                    $logs[] = [
                        'name' => $file->getFilename(),
                        'path' => $file->getPathname(),
                        'size' => $file->getSize(),
                        'modified' => $file->getMTime(),
                    ];
                }
            }

            // Sort by modified time (newest first)
            usort($logs, function($a, $b) {
                return $b['modified'] - $a['modified'];
            });
        }

        return view('logs.index', compact('logs'));
    }
}

