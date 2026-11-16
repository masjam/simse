<?php

namespace App\Services\Security;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SecureUploadService
{
    /**
     * MIME types dan ekstensi yang diizinkan untuk dokumen sekolah.
     *
     * @var array<string, array<string>>
     */
    private const ALLOWED_MIME_EXTENSIONS = [
        'application/pdf' => ['pdf'],
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
    ];

    /**
     * Unggah berkas secara aman ke disk privat dengan validasi ketat.
     *
     * @return array{path: string, original_name: string, mime_type: string, size_bytes: int}
     */
    public function upload(
        UploadedFile $file,
        string $directory = 'documents',
        int $maxSizeBytes = 5242880 // 5 MB
    ): array {
        if (! $file->isValid()) {
            throw new InvalidArgumentException('Berkas yang diunggah tidak valid atau rusak.');
        }

        if ($file->getSize() > $maxSizeBytes) {
            $maxMb = round($maxSizeBytes / 1048576, 1);
            throw new InvalidArgumentException("Ukuran berkas melebihi batas maksimal {$maxMb} MB.");
        }

        $clientExtension = strtolower($file->getClientOriginalExtension());
        $detectedMime = $file->getMimeType();

        // 1. Validasi MIME Type terhadap Whitelist
        if (! array_key_exists($detectedMime, self::ALLOWED_MIME_EXTENSIONS)) {
            throw new InvalidArgumentException("Tipe berkas ({$detectedMime}) tidak diizinkan oleh kebijakan keamanan sekolah.");
        }

        // 2. Validasi Ekstensi terhadap MIME Type
        $allowedExtensions = self::ALLOWED_MIME_EXTENSIONS[$detectedMime];
        if (! in_array($clientExtension, $allowedExtensions, true)) {
            throw new InvalidArgumentException('Ekstensi berkas tidak sesuai dengan tipe konten sebenarnya.');
        }

        // 3. Deteksi potensi berkas berbahaya / script injection
        $dangerousSignatures = ['<?php', '<script', 'eval(', 'base64_decode', '#!/bin/'];
        $sampleContent = @file_get_contents($file->getRealPath(), false, null, 0, 2048);
        if ($sampleContent !== false) {
            foreach ($dangerousSignatures as $sig) {
                if (stripos($sampleContent, $sig) !== false && $detectedMime !== 'application/pdf') {
                    throw new InvalidArgumentException('Konten berkas terindikasi mengandung instruksi berbahaya.');
                }
            }
        }

        // 4. Generate Nama Acak Terproteksi
        $safeFileName = Str::uuid()->toString().'.'.$clientExtension;
        $cleanDirectory = trim($directory, '/');
        $targetPath = $cleanDirectory.'/'.$safeFileName;

        // 5. Simpan ke Disk Privat 'local'
        Storage::disk('local')->putFileAs($cleanDirectory, $file, $safeFileName);

        return [
            'path' => $targetPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $detectedMime,
            'size_bytes' => $file->getSize(),
        ];
    }
}
