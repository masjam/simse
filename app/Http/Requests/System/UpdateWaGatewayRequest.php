<?php

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWaGatewayRequest extends FormRequest
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
            'wa_driver' => ['required', 'string', 'in:log,fonnte,wablas'],
            'wa_is_active' => ['required', 'boolean'],
            'wa_fonnte_token' => ['nullable', 'string', 'max:255'],
            'wa_fonnte_endpoint' => ['required', 'url', 'max:255'],
            'wa_wablas_token' => ['nullable', 'string', 'max:255'],
            'wa_wablas_endpoint' => ['required', 'url', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'wa_driver.required' => 'Driver WhatsApp Gateway wajib dipilih.',
            'wa_driver.in' => 'Driver WhatsApp hanya mendukung log, fonnte, atau wablas.',
            'wa_fonnte_endpoint.required' => 'Endpoint Fonnte wajib diisi.',
            'wa_fonnte_endpoint.url' => 'Format URL endpoint Fonnte tidak valid.',
            'wa_wablas_endpoint.required' => 'Endpoint Wablas wajib diisi.',
            'wa_wablas_endpoint.url' => 'Format URL endpoint Wablas tidak valid.',
        ];
    }
}
