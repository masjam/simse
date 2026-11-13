<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SaveExamAnswerRequest extends FormRequest
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
            'question_id' => ['required', 'uuid', 'exists:questions,id'],
            'option_id' => ['nullable', 'uuid', 'exists:options,id'],
            'text_answer' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'question_id.required' => 'ID soal ujian wajib disertakan.',
            'question_id.uuid' => 'Format ID soal tidak valid.',
            'question_id.exists' => 'Soal ujian tidak ditemukan.',
            'option_id.uuid' => 'Format pilihan jawaban tidak valid.',
            'option_id.exists' => 'Pilihan jawaban tidak ditemukan.',
            'text_answer.max' => 'Jawaban esai maksimal 5.000 karakter.',
        ];
    }
}
