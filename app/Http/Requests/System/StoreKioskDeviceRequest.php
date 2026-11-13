<?php

namespace App\Http\Requests\System;

use App\Models\KioskDevice;
use Illuminate\Foundation\Http\FormRequest;

class StoreKioskDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('mac_address')) {
            $this->merge([
                'mac_address' => KioskDevice::normalizeMac((string) $this->input('mac_address')),
            ]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'mac_address' => [
                'required',
                'string',
                'regex:/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/',
                'unique:kiosk_devices,mac_address',
            ],
            'location' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama perangkat kiosk wajib diisi.',
            'mac_address.required' => 'MAC Address PC wajib diisi.',
            'mac_address.regex' => 'Format MAC Address tidak valid (contoh: AA:BB:CC:DD:EE:FF).',
            'mac_address.unique' => 'MAC Address tersebut sudah terdaftar di sistem.',
        ];
    }
}
