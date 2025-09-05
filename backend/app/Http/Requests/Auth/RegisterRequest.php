<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255|min:2',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'cpf' => [
                'required',
                'string',
                'size:11',
                'regex:/^[0-9]{11}$/',
                Rule::unique('users', 'cpf')
            ],
            'rg' => 'sometimes|string|max:20',
            'phone' => [
                'required',
                'string',
                'regex:/^\([0-9]{2}\)\s[0-9]{4,5}-[0-9]{4}$/',
                Rule::unique('users', 'phone')
            ],
            'birth_date' => 'required|date|before:today|after:1900-01-01',
            'address' => 'sometimes|string|max:500',
            'gender' => 'sometimes|in:male,female,other,prefer_not_to_say',
            'occupation' => 'sometimes|string|max:255',
            'department' => 'sometimes|string|max:255',
            'admission_date' => 'sometimes|date|before_or_equal:today',
            'salary' => 'sometimes|numeric|min:0|max:999999.99',
            'emergency_contact_name' => 'sometimes|string|max:255',
            'emergency_contact_phone' => 'sometimes|string|regex:/^\([0-9]{2}\)\s[0-9]{4,5}-[0-9]{4}$/',
            'terms_accepted' => 'required|accepted',
            'privacy_policy_accepted' => 'required|accepted',
            'marketing_emails' => 'sometimes|boolean',
            'device_info' => 'sometimes|array',
            'device_info.device_id' => 'sometimes|string|max:255',
            'device_info.device_type' => 'sometimes|string|max:100',
            'device_info.device_name' => 'sometimes|string|max:255',
            'device_info.os_version' => 'sometimes|string|max:100',
            'device_info.app_version' => 'sometimes|string|max:50',
            'device_info.ip_address' => 'sometimes|ip',
            'device_info.user_agent' => 'sometimes|string|max:500'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 2 caracteres.',
            'name.max' => 'O nome não pode exceder 255 caracteres.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.unique' => 'Este email já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.size' => 'O CPF deve ter exatamente 11 dígitos.',
            'cpf.regex' => 'O CPF deve conter apenas números.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'phone.required' => 'O telefone é obrigatório.',
            'phone.regex' => 'O telefone deve estar no formato (XX) XXXXX-XXXX.',
            'phone.unique' => 'Este telefone já está cadastrado.',
            'birth_date.required' => 'A data de nascimento é obrigatória.',
            'birth_date.date' => 'A data de nascimento deve ser uma data válida.',
            'birth_date.before' => 'A data de nascimento deve ser anterior a hoje.',
            'birth_date.after' => 'A data de nascimento deve ser posterior a 1900.',
            'gender.in' => 'Gênero deve ser: masculino, feminino, outro ou prefiro não informar.',
            'admission_date.date' => 'A data de admissão deve ser uma data válida.',
            'admission_date.before_or_equal' => 'A data de admissão não pode ser futura.',
            'salary.numeric' => 'O salário deve ser um valor numérico.',
            'salary.min' => 'O salário deve ser maior ou igual a zero.',
            'salary.max' => 'O salário não pode exceder R$ 999.999,99.',
            'emergency_contact_phone.regex' => 'O telefone de emergência deve estar no formato (XX) XXXXX-XXXX.',
            'terms_accepted.required' => 'Você deve aceitar os termos de uso.',
            'terms_accepted.accepted' => 'Você deve aceitar os termos de uso.',
            'privacy_policy_accepted.required' => 'Você deve aceitar a política de privacidade.',
            'privacy_policy_accepted.accepted' => 'Você deve aceitar a política de privacidade.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'email',
            'password' => 'senha',
            'cpf' => 'CPF',
            'rg' => 'RG',
            'phone' => 'telefone',
            'birth_date' => 'data de nascimento',
            'address' => 'endereço',
            'gender' => 'gênero',
            'occupation' => 'profissão',
            'department' => 'departamento',
            'admission_date' => 'data de admissão',
            'salary' => 'salário',
            'emergency_contact_name' => 'nome do contato de emergência',
            'emergency_contact_phone' => 'telefone do contato de emergência',
            'terms_accepted' => 'termos de uso',
            'privacy_policy_accepted' => 'política de privacidade',
            'marketing_emails' => 'emails de marketing'
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

        // Remove non-numeric characters from CPF
        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/[^0-9]/', '', $this->cpf)
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

        // Set default values for optional boolean fields
        $this->merge([
            'marketing_emails' => $this->boolean('marketing_emails', false),
            'is_active' => true,
            'is_verified' => false
        ]);
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
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate CPF format
            if ($this->has('cpf') && !$this->isValidCPF($this->cpf)) {
                $validator->errors()->add('cpf', 'O CPF informado é inválido.');
            }

            // Validate minimum age (18 years)
            if ($this->has('birth_date')) {
                $birthDate = \Carbon\Carbon::parse($this->birth_date);
                $age = $birthDate->diffInYears(\Carbon\Carbon::now());
                
                if ($age < 18) {
                    $validator->errors()->add('birth_date', 'Você deve ter pelo menos 18 anos para se cadastrar.');
                }
            }

            // Validate admission date is not before birth date
            if ($this->has('admission_date') && $this->has('birth_date')) {
                $birthDate = \Carbon\Carbon::parse($this->birth_date);
                $admissionDate = \Carbon\Carbon::parse($this->admission_date);
                
                if ($admissionDate->lt($birthDate->addYears(16))) {
                    $validator->errors()->add('admission_date', 'A data de admissão deve ser posterior aos 16 anos de idade.');
                }
            }
        });
    }

    /**
     * Validate CPF using algorithm.
     */
    private function isValidCPF(string $cpf): bool
    {
        // Remove any non-numeric characters
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Check if CPF has 11 digits
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        // Check for known invalid CPFs
        $invalidCPFs = [
            '00000000000', '11111111111', '22222222222', '33333333333',
            '44444444444', '55555555555', '66666666666', '77777777777',
            '88888888888', '99999999999'
        ];
        
        if (in_array($cpf, $invalidCPFs)) {
            return false;
        }
        
        // Validate first check digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        
        if (intval($cpf[9]) !== $digit1) {
            return false;
        }
        
        // Validate second check digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        
        return intval($cpf[10]) === $digit2;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Log failed registration attempt
        activity('auth')
            ->withProperties([
                'email' => $this->input('email'),
                'cpf' => $this->input('cpf'),
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'errors' => $validator->errors()->toArray()
            ])
            ->log('Failed registration validation');

        parent::failedValidation($validator);
    }
}