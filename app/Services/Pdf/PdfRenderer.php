<?php

namespace App\Services\Pdf;

use App\Models\DocumentVerification;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PdfRenderer
{
    /**
     * Default school information for official letterhead (Kop Surat).
     */
    public function getSchoolInfo(): array
    {
        return [
            'yayasan' => SystemSetting::getValue('school_foundation', 'PIMPINAN CABANG MUHAMMADIYAH WONOSARI'),
            'majelis' => SystemSetting::getValue('school_council', 'MAJELIS PENDIDIKAN DASAR DAN MENENGAH'),
            'name' => SystemSetting::getValue('school_name', 'SD MUHAMMADIYAH AL MUJAHIDIN WONOSARI'),
            'address' => SystemSetting::getValue('school_address', 'Jl. KH. Agus Salim No. 35, Ledoksari, Kepek, Wonosari, Gunungkidul, D.I. Yogyakarta 55813'),
            'phone' => SystemSetting::getValue('school_phone', '(0274) 391234'),
            'email' => SystemSetting::getValue('school_email', 'info@sdmujahidin-wns.sch.id'),
            'website' => SystemSetting::getValue('school_website', 'sdmujahidin-wns.sch.id'),
            'npsn' => SystemSetting::getValue('school_npsn', '20402111'),
            'principal' => SystemSetting::getValue('principal_name', 'Joko Kiswanto, S.Pd.I., M.Pd.'),
            'principal_nip' => SystemSetting::getValue('principal_nip', 'NBM. 1092384'),
        ];
    }

    /**
     * Create verification token and record in document_verifications.
     */
    public function createVerification(
        string $documentType,
        string $recipientName,
        ?Model $reference = null,
        ?array $metadata = null,
        ?string $title = null
    ): DocumentVerification {
        $token = Str::random(40);
        $contentData = json_encode([
            'type' => $documentType,
            'recipient' => $recipientName,
            'ref_id' => $reference?->getKey(),
            'metadata' => $metadata,
            'timestamp' => now()->toIso8601String(),
        ]);

        $contentHash = hash('sha256', $contentData);

        return DocumentVerification::create([
            'token' => $token,
            'document_type' => $documentType,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? (string) $reference->getKey() : null,
            'title' => $title ?: "Dokumen {$documentType} - {$recipientName}",
            'recipient_name' => $recipientName,
            'content_hash' => $contentHash,
            'metadata' => $metadata,
            'issued_by' => Auth::id(),
            'issued_at' => now(),
            'is_revoked' => false,
        ]);
    }

    /**
     * Generate QR Code as Data URI for DomPDF rendering.
     */
    public function generateQrCode(string $url): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64' => true,
            'scale' => 4,
        ]);

        return (new QRCode($options))->render($url);
    }

    /**
     * Prepare data with school info, verification, and QR code.
     */
    public function prepareData(array $data, ?DocumentVerification $verification = null): array
    {
        $data['school'] = $this->getSchoolInfo();

        if ($verification) {
            $data['verification'] = $verification;
            $verifyUrl = route('verifikasi.show', $verification->token);
            $data['verifyUrl'] = $verifyUrl;
            $data['qrCode'] = $this->generateQrCode($verifyUrl);
        }

        return $data;
    }

    /**
     * Render PDF binary string.
     */
    public function render(
        string $view,
        array $data = [],
        string $orientation = 'portrait',
        string $paper = 'a4',
        ?DocumentVerification $verification = null
    ): string {
        $data = $this->prepareData($data, $verification);

        $pdf = Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation)
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        return $pdf->output();
    }

    /**
     * Stream PDF response (inline view).
     */
    public function stream(
        string $view,
        array $data = [],
        string $filename = 'dokumen.pdf',
        string $orientation = 'portrait',
        string $paper = 'a4',
        ?DocumentVerification $verification = null
    ): Response {
        $data = $this->prepareData($data, $verification);

        return Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation)
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ])
            ->stream($filename);
    }

    /**
     * Download PDF response.
     */
    public function download(
        string $view,
        array $data = [],
        string $filename = 'dokumen.pdf',
        string $orientation = 'portrait',
        string $paper = 'a4',
        ?DocumentVerification $verification = null
    ): Response {
        $data = $this->prepareData($data, $verification);

        return Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation)
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ])
            ->download($filename);
    }
}
