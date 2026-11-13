<?php

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;

class SendAnnouncementRequest extends FormRequest
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
            'target_role' => ['required', 'string', 'in:all,Orang Tua,Guru Pengajar,Siswa'],
            'title' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:1500'],
            'send_whatsapp' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'target_role.required' => 'Target penerima pengumuman wajib dipilih.',
            'target_role.in' => 'Target penerima pengumuman tidak valid.',
            'title.required' => 'Judul pengumuman wajib diisi.',
            'message.required' => 'Isi pengumuman wajib diisi.',
        ];
    }
}
