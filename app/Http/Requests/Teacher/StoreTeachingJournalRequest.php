<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeachingJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAnyRole(['Guru Pengajar', 'Guru BK', 'Super Admin']);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'student_class_id' => ['required', 'uuid', 'exists:student_classes,id'],
            'subject_id' => ['required', 'uuid', 'exists:subjects,id'],
            'teaching_schedule_id' => ['nullable', 'uuid', 'exists:teaching_schedules,id'],
            'date' => ['required', 'date'],
            'meeting_number' => ['nullable', 'integer', 'min:1', 'max:200'],
            'topic' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:5000'],
            'activities' => ['nullable', 'string', 'max:3000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_class_id.required' => 'Kelas wajib dipilih.',
            'student_class_id.exists' => 'Kelas yang dipilih tidak valid.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran yang dipilih tidak valid.',
            'date.required' => 'Tanggal mengajar wajib diisi.',
            'date.date' => 'Format tanggal mengajar tidak valid.',
            'meeting_number.integer' => 'Pertemuan ke harus berupa angka.',
            'meeting_number.min' => 'Pertemuan ke minimal 1.',
            'topic.required' => 'Topik atau pokok bahasan wajib diisi.',
            'topic.max' => 'Topik maksimal 255 karakter.',
            'summary.required' => 'Ringkasan materi jurnal wajib diisi.',
            'summary.max' => 'Ringkasan materi maksimal 5.000 karakter.',
        ];
    }
}
