<?php

namespace App\Http\Requests\ParentPortal;

use Illuminate\Foundation\Http\FormRequest;

class StoreParentAchievementRequest extends FormRequest
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
            'student_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:200'],
            'category' => ['required', 'string', 'in:Akademik,Olahraga,Seni & Budaya,Keagamaan,Sains & Teknologi,Lainnya'],
            'level' => ['required', 'string', 'in:Sekolah,Kecamatan,Kabupaten/Kota,Provinsi,Nasional,Internasional'],
            'year' => ['required', 'integer', 'min:2015', 'max:2035'],
            'organizer' => ['nullable', 'string', 'max:200'],
            'achievement_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:3072'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Data anak wajib dipilih.',
            'student_id.uuid' => 'Identitas anak tidak valid.',
            'name.required' => 'Nama prestasi atau juara lomba wajib diisi.',
            'name.max' => 'Nama prestasi maksimal 200 karakter.',
            'category.required' => 'Kategori lomba wajib dipilih.',
            'category.in' => 'Kategori lomba tidak valid.',
            'level.required' => 'Tingkat lomba wajib dipilih.',
            'level.in' => 'Tingkat lomba tidak valid.',
            'year.required' => 'Tahun perolehan prestasi wajib diisi.',
            'year.integer' => 'Tahun harus berupa angka.',
            'year.min' => 'Tahun minimal 2015.',
            'year.max' => 'Tahun maksimal 2035.',
            'achievement_date.date' => 'Format tanggal lomba tidak valid.',
            'certificate.file' => 'Sertifikat/piagam harus berupa berkas yang valid.',
            'certificate.mimes' => 'Sertifikat/piagam hanya mendukung format PDF, JPG, JPEG, atau PNG.',
            'certificate.max' => 'Ukuran berkas sertifikat maksimal 3 MB.',
        ];
    }
}
