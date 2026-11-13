<?php

namespace App\Http\Requests\Canteen;

use Illuminate\Foundation\Http\FormRequest;

class StudentCheckoutRequest extends FormRequest
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
            'canteen_stand_id' => ['required', 'uuid', 'exists:canteen_stands,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'uuid', 'exists:canteen_menus,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'pin' => ['required', 'digits:6'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'canteen_stand_id.required' => 'Stand kantin wajib dipilih.',
            'items.required' => 'Keranjang belanja tidak boleh kosong.',
            'items.min' => 'Pilih minimal 1 menu sebelum checkout.',
            'items.*.quantity.min' => 'Jumlah pesanan minimal 1 porsi.',
            'pin.required' => 'PIN dompet digital 6 digit wajib diisi.',
            'pin.digits' => 'PIN dompet digital harus berupa 6 digit angka.',
        ];
    }
}
