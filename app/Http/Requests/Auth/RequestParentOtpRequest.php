<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RequestParentOtpRequest extends FormRequest
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
            'phone_number' => ['required', 'string', 'min:8', 'max:20'],
            'birth_date' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone_number.required' => 'Nomor WhatsApp orang tua wajib diisi.',
            'phone_number.min' => 'Nomor WhatsApp minimal 8 digit.',
            'phone_number.max' => 'Nomor WhatsApp maksimal 20 karakter.',
            'birth_date.required' => 'Tanggal lahir anak wajib diisi.',
            'birth_date.date' => 'Format tanggal lahir anak tidak valid.',
        ];
    }
}
