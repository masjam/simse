<?php

namespace App\Http\Requests\Canteen;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:500', 'max:1000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:10000'],
            'description' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'string', 'max:500'],
            'is_available' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama menu wajib diisi.',
            'price.required' => 'Harga menu wajib diisi.',
            'price.min' => 'Harga menu minimal Rp 500.',
            'stock.required' => 'Stok menu wajib diisi.',
            'stock.min' => 'Stok menu tidak boleh negatif.',
        ];
    }
}
