<?php

namespace App\Http\Requests\Canteen;

use Illuminate\Foundation\Http\FormRequest;

class RfidPaymentRequest extends FormRequest
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
            'rfid_uid' => ['required', 'string', 'max:100'],
            'pin' => ['required', 'digits:6'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'uuid', 'exists:canteen_menus,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rfid_uid.required' => 'Kartu RFID siswa wajib di-tap terlebih dahulu.',
            'pin.required' => 'PIN 6 digit siswa wajib diisi.',
            'pin.digits' => 'PIN harus berupa 6 digit angka.',
            'items.required' => 'Pilih minimal 1 menu untuk dibayar.',
            'items.min' => 'Pilih minimal 1 menu untuk dibayar.',
        ];
    }
}
