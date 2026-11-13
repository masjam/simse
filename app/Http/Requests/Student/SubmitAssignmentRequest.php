<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAssignmentRequest extends FormRequest
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
            'text_answer' => ['nullable', 'string', 'max:5000', 'required_without:file'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,zip', 'max:10240', 'required_without:text_answer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'text_answer.required_without' => 'Harap isi jawaban teks atau unggah file tugas.',
            'text_answer.max' => 'Jawaban teks maksimal 5.000 karakter.',
            'file.required_without' => 'Harap unggah file tugas atau isi jawaban teks.',
            'file.file' => 'Lampiran tugas harus berupa berkas yang valid.',
            'file.mimes' => 'Format file harus berupa PDF, DOC, DOCX, JPG, JPEG, PNG, atau ZIP.',
            'file.max' => 'Ukuran file tugas maksimal 10 MB.',
        ];
    }
}
