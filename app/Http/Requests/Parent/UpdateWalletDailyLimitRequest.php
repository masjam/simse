<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWalletDailyLimitRequest extends FormRequest
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
            'student_id' => ['required', 'uuid', 'exists:students,id'],
            'daily_limit' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Data siswa wajib dipilih.',
            'daily_limit.numeric' => 'Batas pengeluaran harian harus berupa angka.',
            'daily_limit.min' => 'Batas pengeluaran harian tidak boleh negatif.',
            'daily_limit.max' => 'Batas pengeluaran harian maksimal Rp 10.000.000.',
        ];
    }
}
