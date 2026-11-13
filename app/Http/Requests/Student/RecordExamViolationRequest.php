<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class RecordExamViolationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAnyRole(['Siswa', 'Super Admin']);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:Tab Switch,Keluar Layar Penuh,Koneksi Terputus,Buka Aplikasi Lain'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Jenis pelanggaran ujian wajib diisi.',
            'type.in' => 'Jenis pelanggaran ujian tidak valid.',
        ];
    }
}
