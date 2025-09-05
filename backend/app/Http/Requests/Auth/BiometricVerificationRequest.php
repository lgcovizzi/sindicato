<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BiometricVerificationRequest extends FormRequest
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
        $rules = [
            'biometric_data' => 'required|string|min:10',
            'type' => 'required|in:fingerprint,face,voice,iris',
            'action' => 'sometimes|in:login,verification,registration,transaction',
            'device_info' => 'sometimes|array',
            'device_info.device_id' => 'sometimes|string|max:255',
            'device_info.device_type' => 'sometimes|string|max:100',
            'device_info.device_name' => 'sometimes|string|max:255',
            'device_info.os_version' => 'sometimes|string|max:100',
            'device_info.app_version' => 'sometimes|string|max:50',
            'device_info.biometric_sensor' => 'sometimes|string|max:255',
            'device_info.security_level' => 'sometimes|in:low,medium,high,very_high',
            'quality_threshold' => 'sometimes|numeric|min:0|max:100',
            'confidence_threshold' => 'sometimes|numeric|min:0|max:100',
            'template_format' => 'sometimes|string|max:50',
            'encryption_method' => 'sometimes|string|max:50',
            'metadata' => 'sometimes|array'
        ];

        // Add email validation for login action
        if ($this->input('action') === 'login') {
            $rules['email'] = [
                'required',
                'email',
                'max:255',
                Rule::exists('users', 'email')->where(function ($query) {
                    return $query->where('is_active', true);
                })
            ];
        }

        // Add user_id validation for other actions when authenticated
        if (auth()->check() && $this->input('action') !== 'login') {
            $rules['user_id'] = [
                'sometimes',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                })
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'biometric_data.required' => 'Os dados biométricos são obrigatórios.',
            'biometric_data.string' => 'Os dados biométricos devem ser uma string.',
            'biometric_data.min' => 'Os dados biométricos devem ter pelo menos 10 caracteres.',
            'type.required' => 'O tipo biométrico é obrigatório.',
            'type.in' => 'Tipo biométrico deve ser: fingerprint, face, voice ou iris.',
            'action.in' => 'Ação deve ser: login, verification, registration ou transaction.',
            'email.required' => 'O email é obrigatório para login biométrico.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.exists' => 'Email não encontrado ou usuário inativo.',
            'user_id.integer' => 'ID do usuário deve ser um número inteiro.',
            'user_id.exists' => 'Usuário não encontrado ou inativo.',
            'device_info.array' => 'Informações do dispositivo devem ser um objeto.',
            'device_info.device_id.max' => 'ID do dispositivo não pode exceder 255 caracteres.',
            'device_info.security_level.in' => 'Nível de segurança deve ser: low, medium, high ou very_high.',
            'quality_threshold.numeric' => 'Limite de qualidade deve ser um número.',
            'quality_threshold.min' => 'Limite de qualidade deve ser maior ou igual a 0.',
            'quality_threshold.max' => 'Limite de qualidade deve ser menor ou igual a 100.',
            'confidence_threshold.numeric' => 'Limite de confiança deve ser um número.',
            'confidence_threshold.min' => 'Limite de confiança deve ser maior ou igual a 0.',
            'confidence_threshold.max' => 'Limite de confiança deve ser menor ou igual a 100.',
            'metadata.array' => 'Metadados devem ser um objeto.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'biometric_data' => 'dados biométricos',
            'type' => 'tipo biométrico',
            'action' => 'ação',
            'email' => 'email',
            'user_id' => 'ID do usuário',
            'device_info' => 'informações do dispositivo',
            'quality_threshold' => 'limite de qualidade',
            'confidence_threshold' => 'limite de confiança',
            'template_format' => 'formato do template',
            'encryption_method' => 'método de criptografia',
            'metadata' => 'metadados'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize email to lowercase if provided
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower($this->email)
            ]);
        }

        // Set default action if not provided
        if (!$this->has('action')) {
            $action = 'verification';
            
            // Determine action based on context
            if ($this->has('email') && !auth()->check()) {
                $action = 'login';
            } elseif ($this->routeIs('*.register-biometric')) {
                $action = 'registration';
            }
            
            $this->merge(['action' => $action]);
        }

        // Set default device info if not provided
        if (!$this->has('device_info')) {
            $this->merge([
                'device_info' => [
                    'device_type' => $this->getDeviceType(),
                    'security_level' => 'medium',
                    'biometric_sensor' => $this->getBiometricSensor()
                ]
            ]);
        }

        // Set default thresholds based on biometric type
        $this->setDefaultThresholds();
    }

    /**
     * Set default quality and confidence thresholds based on biometric type.
     */
    private function setDefaultThresholds(): void
    {
        $type = $this->input('type');
        $defaults = [
            'fingerprint' => ['quality' => 70, 'confidence' => 85],
            'face' => ['quality' => 75, 'confidence' => 80],
            'voice' => ['quality' => 65, 'confidence' => 75],
            'iris' => ['quality' => 90, 'confidence' => 95]
        ];

        if (isset($defaults[$type])) {
            $merge = [];
            
            if (!$this->has('quality_threshold')) {
                $merge['quality_threshold'] = $defaults[$type]['quality'];
            }
            
            if (!$this->has('confidence_threshold')) {
                $merge['confidence_threshold'] = $defaults[$type]['confidence'];
            }
            
            if (!empty($merge)) {
                $this->merge($merge);
            }
        }
    }

    /**
     * Get device type from user agent.
     */
    private function getDeviceType(): string
    {
        $userAgent = $this->userAgent();
        
        if (preg_match('/Mobile|Android|iPhone/', $userAgent)) {
            return 'mobile';
        }
        
        if (preg_match('/Tablet|iPad/', $userAgent)) {
            return 'tablet';
        }
        
        return 'desktop';
    }

    /**
     * Get biometric sensor type based on device and biometric type.
     */
    private function getBiometricSensor(): string
    {
        $type = $this->input('type', 'fingerprint');
        $deviceType = $this->getDeviceType();
        
        $sensors = [
            'fingerprint' => [
                'mobile' => 'capacitive',
                'tablet' => 'capacitive',
                'desktop' => 'optical'
            ],
            'face' => [
                'mobile' => 'front_camera',
                'tablet' => 'front_camera',
                'desktop' => 'webcam'
            ],
            'voice' => [
                'mobile' => 'microphone',
                'tablet' => 'microphone',
                'desktop' => 'microphone'
            ],
            'iris' => [
                'mobile' => 'infrared_camera',
                'tablet' => 'infrared_camera',
                'desktop' => 'infrared_scanner'
            ]
        ];

        return $sensors[$type][$deviceType] ?? 'unknown';
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate biometric data format based on type
            $this->validateBiometricDataFormat($validator);
            
            // Validate device compatibility
            $this->validateDeviceCompatibility($validator);
            
            // Validate security requirements
            $this->validateSecurityRequirements($validator);
        });
    }

    /**
     * Validate biometric data format based on type.
     */
    private function validateBiometricDataFormat($validator): void
    {
        $type = $this->input('type');
        $data = $this->input('biometric_data');
        
        if (!$data) return;
        
        $patterns = [
            'fingerprint' => '/^[A-Za-z0-9+\/=]+$/', // Base64 pattern
            'face' => '/^[A-Za-z0-9+\/=]+$/',        // Base64 pattern
            'voice' => '/^[A-Za-z0-9+\/=]+$/',       // Base64 pattern
            'iris' => '/^[A-Za-z0-9+\/=]+$/'        // Base64 pattern
        ];
        
        if (isset($patterns[$type]) && !preg_match($patterns[$type], $data)) {
            $validator->errors()->add('biometric_data', 'Formato dos dados biométricos inválido para o tipo ' . $type . '.');
        }
        
        // Validate minimum data length based on type
        $minLengths = [
            'fingerprint' => 100,
            'face' => 200,
            'voice' => 150,
            'iris' => 300
        ];
        
        if (isset($minLengths[$type]) && strlen($data) < $minLengths[$type]) {
            $validator->errors()->add('biometric_data', 'Dados biométricos insuficientes para o tipo ' . $type . '.');
        }
    }

    /**
     * Validate device compatibility.
     */
    private function validateDeviceCompatibility($validator): void
    {
        $type = $this->input('type');
        $deviceType = $this->input('device_info.device_type', $this->getDeviceType());
        
        // Some biometric types may not be supported on certain devices
        $incompatible = [
            'iris' => ['desktop'] // Iris scanning typically not available on desktop
        ];
        
        if (isset($incompatible[$type]) && in_array($deviceType, $incompatible[$type])) {
            $validator->errors()->add('type', 'Tipo biométrico ' . $type . ' não é compatível com dispositivo ' . $deviceType . '.');
        }
    }

    /**
     * Validate security requirements.
     */
    private function validateSecurityRequirements($validator): void
    {
        $action = $this->input('action');
        $securityLevel = $this->input('device_info.security_level', 'medium');
        
        // High-security actions require higher security levels
        $highSecurityActions = ['transaction'];
        $requiredLevels = ['high', 'very_high'];
        
        if (in_array($action, $highSecurityActions) && !in_array($securityLevel, $requiredLevels)) {
            $validator->errors()->add('device_info.security_level', 'Ação ' . $action . ' requer nível de segurança alto ou muito alto.');
        }
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Log failed biometric validation attempt
        activity('biometric')
            ->withProperties([
                'type' => $this->input('type'),
                'action' => $this->input('action'),
                'email' => $this->input('email'),
                'user_id' => $this->input('user_id'),
                'device_type' => $this->input('device_info.device_type'),
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'errors' => $validator->errors()->toArray()
            ])
            ->log('Failed biometric validation');

        parent::failedValidation($validator);
    }
}