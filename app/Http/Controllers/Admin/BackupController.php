<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    /**
     * Display a listing of system backups.
     */
    public function index(): Response
    {
        $disk = Storage::disk('local');
        $backupName = config('backup.backup.name', env('APP_NAME', 'laravel-backup'));

        $files = $disk->allFiles($backupName);
        $backups = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $backups[] = [
                    'path' => $file,
                    'filename' => basename($file),
                    'size' => $this->formatBytes($disk->size($file)),
                    'created_at' => date('d M Y H:i:s', $disk->lastModified($file)),
                ];
            }
        }

        usort($backups, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return Inertia::render('Admin/Backups/Index', [
            'backups' => $backups,
        ]);
    }

    /**
     * Trigger a new manual backup.
     */
    public function create(): RedirectResponse
    {
        try {
            Artisan::call('backup:run');

            return redirect()->back()->with('success', 'Cadangan sistem baru berhasil dibuat.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membuat cadangan: '.$e->getMessage());
        }
    }

    /**
     * Download a backup zip file.
     */
    public function download(string $filename): StreamedResponse
    {
        $backupName = config('backup.backup.name', env('APP_NAME', 'laravel-backup'));
        $path = "{$backupName}/{$filename}";

        if (! Storage::disk('local')->exists($path)) {
            abort(404, 'File cadangan tidak ditemukan.');
        }

        return Storage::disk('local')->download($path);
    }

    /**
     * Delete an existing backup file.
     */
    public function destroy(string $filename): RedirectResponse
    {
        $backupName = config('backup.backup.name', env('APP_NAME', 'laravel-backup'));
        $path = "{$backupName}/{$filename}";

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }

        return redirect()->back()->with('success', 'File cadangan berhasil dihapus.');
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}
