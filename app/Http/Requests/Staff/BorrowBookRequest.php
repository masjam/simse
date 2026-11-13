<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class BorrowBookRequest extends FormRequest
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
            'book_id' => ['required', 'uuid', 'exists:books,id'],
            'borrower_id' => ['required', 'uuid', 'exists:users,id'],
            'loan_duration_days' => ['required', 'integer', 'min:1', 'max:30'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'book_id.required' => 'Buku yang dipinjam wajib dipilih.',
            'borrower_id.required' => 'Peminjam (Siswa/Guru/Staf) wajib dipilih.',
            'loan_duration_days.required' => 'Durasi peminjaman wajib diisi.',
            'loan_duration_days.min' => 'Durasi peminjaman minimal 1 hari.',
            'loan_duration_days.max' => 'Durasi peminjaman maksimal 30 hari.',
        ];
    }
}
