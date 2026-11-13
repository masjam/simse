<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyStudentAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:Disetujui,Ditolak'],
            'verification_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Keputusan verifikasi wajib dipilih.',
            'status.in' => 'Status verifikasi hanya boleh Disetujui atau Ditolak.',
            'verification_notes.max' => 'Catatan verifikasi maksimal 500 karakter.',
        ];
    }
}
