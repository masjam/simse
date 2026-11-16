<?php

namespace App\Http\Controllers;

use App\Models\DocumentVerification;
use Inertia\Inertia;
use Inertia\Response;

class DocumentVerificationController extends Controller
{
    /**
     * Show public document verification page without exposing sensitive data.
     */
    public function show(string $token): Response
    {
        $verification = DocumentVerification::with('issuer:id,name')
            ->where('token', $token)
            ->first();

        if (! $verification) {
            return Inertia::render('DocumentVerification/Show', [
                'isValid' => false,
                'token' => $token,
                'document' => null,
            ]);
        }

        return Inertia::render('DocumentVerification/Show', [
            'isValid' => ! $verification->is_revoked,
            'token' => $token,
            'document' => [
                'document_type' => $verification->document_type,
                'title' => $verification->title,
                'recipient_masked' => $verification->masked_recipient_name,
                'issued_at' => $verification->issued_at?->translatedFormat('d F Y H:i:s'),
                'issuer_name' => $verification->issuer?->name ?? 'Sistem Informasi Akademik SIMSE',
                'is_revoked' => $verification->is_revoked,
                'revoked_at' => $verification->revoked_at?->translatedFormat('d F Y H:i:s'),
                'revoked_reason' => $verification->revoked_reason,
                'content_hash_short' => substr($verification->content_hash, 0, 16).'...',
            ],
        ]);
    }
}
