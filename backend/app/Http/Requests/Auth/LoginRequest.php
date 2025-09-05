<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::exists('users', 'email')->where(function ($query) {
                    return $query->where('is_active', true);
                })
            ],
            'password' => 'required_without:biometric_data|string|min:6',
            'biometric_data' => 'required_without:password|string',
            'biometric_type' => 'required_with:biometric_data|in:fingerprint,face,voice',
            'device_info' => 'sometimes|array',
            'device_info.device_id' => 'sometimes|string|max:255',
            'device_info.device_type' => 'sometimes|string|max:100',
            'device_info.device_name' => 'sometimes|string|max:255',
            'device_info.os_version' => 'sometimes|string|max:100',
            'device_info.app_version' => 'sometimes|string|max:50',
            'device_info.ip_address' => 'sometimes|ip',
            'device_info.user_agent' => 'sometimes|string|max:500',
            'remember_me' => 'sometimes|boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.exists' => 'Email não encontrado ou usuário inativo.',
            'password.required_without' => 'A senha é obrigatória quando dados biométricos não são fornecidos.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'biometric_data.required_without' => 'Dados biométricos são obrigatórios quando senha não é fornecida.',
            'biometric_type.required_with' => 'O tipo biométrico é obrigatório quando dados biométricos são fornecidos.',
            'biometric_type.in' => 'Tipo biométrico deve ser: fingerprint, face ou voice.',
            'device_info.array' => 'Informações do dispositivo devem ser um objeto.',
            'device_info.device_id.max' => 'ID do dispositivo não pode exceder 255 caracteres.',
            'device_info.ip_address.ip' => 'Endereço IP deve ter formato válido.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'email' => 'email',
            'password' => 'senha',
            'biometric_data' => 'dados biométricos',
            'biometric_type' => 'tipo biométrico',
            'device_info' => 'informações do dispositivo',
            'remember_me' => 'lembrar-me'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize email to lowercase
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower($this->email)
            ]);
        }

        // Set default device info if not provided
        if (!$this->has('device_info')) {
            $this->merge([
                'device_info' => [
                    'ip_address' => $this->ip(),
                    'user_agent' => $this->userAgent(),
                    'device_type' => $this->getDeviceType()
                ]
            ]);
        }
    }

    /**
     * Get device type from user agent.
     */
    private function getDeviceType(): string
    {
        $userAgent = $this->userAgent();
        
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'mobile';
        }
        
        if (preg_match('/Tablet|iPad/', $userAgent)) {
            return 'tablet';
        }
        
        return 'desktop';
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Log failed login attempt
        activity('auth')
            ->withProperties([
                'email' => $this->input('email'),
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'errors' => $validator->errors()->toArray()
            ])
            ->log('Failed login validation');

        parent::failedValidation($validator);
    }
}