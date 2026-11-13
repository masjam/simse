<?php

namespace App\Http\Requests\Ppdb;

use Illuminate\Foundation\Http\FormRequest;

class StorePpdbRequest extends FormRequest
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
            // Tahap 1: Data Calon Siswa
            'student_name' => ['required', 'string', 'max:150'],
            'nik' => ['required', 'string', 'digits:16'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'string', 'in:L,P'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'address' => ['required', 'string', 'max:500'],
            'previous_school' => ['nullable', 'string', 'max:150'],

            // Tahap 2: Data Orang Tua / Wali
            'parent_name' => ['required', 'string', 'max:150'],
            'parent_phone' => ['required', 'string', 'max:30'],
            'parent_email' => ['nullable', 'email', 'max:100'],
            'parent_job' => ['nullable', 'string', 'max:100'],

            // Tahap 3: Upload Dokumen Privat
            'kk_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:3072'],
            'birth_certificate_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:3072'],
            'ijazah_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:3072'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_name.required' => 'Nama lengkap calon siswa wajib diisi.',
            'nik.required' => 'NIK calon siswa wajib diisi 16 digit.',
            'nik.digits' => 'NIK calon siswa harus tepat 16 digit.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'birth_place.required' => 'Tempat lahir wajib diisi.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'address.required' => 'Alamat tempat tinggal wajib diisi.',
            'parent_name.required' => 'Nama orang tua / wali wajib diisi.',
            'parent_phone.required' => 'Nomor WhatsApp / telepon orang tua wajib diisi.',
            'kk_file.mimes' => 'Berkas Kartu Keluarga harus berformat PDF, JPG, atau PNG.',
            'kk_file.max' => 'Ukuran berkas Kartu Keluarga maksimal 3 MB.',
            'birth_certificate_file.mimes' => 'Berkas Akta Kelahiran harus berformat PDF, JPG, atau PNG.',
            'birth_certificate_file.max' => 'Ukuran berkas Akta Kelahiran maksimal 3 MB.',
        ];
    }
}
