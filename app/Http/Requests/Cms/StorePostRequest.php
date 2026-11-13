<?php

namespace App\Http\Requests\Cms;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', 'string', 'in:Berita,Pengumuman,Prestasi,Artikel'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'is_published' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul berita / artikel wajib diisi.',
            'category.required' => 'Kategori artikel wajib dipilih.',
            'content.required' => 'Konten berita / artikel wajib diisi.',
        ];
    }
}
