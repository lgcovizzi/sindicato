<?php

namespace App\Services;

use App\Models\User;
use App\Models\BiometricData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BiometricService
{
    /**
     * Register biometric data for user.
     */
    public function registerBiometric(User $user, array $biometricData): array
    {
        try {
            // Validate biometric data format
            if (!$this->validateBiometricData($biometricData)) {
                return [
                    'success' => false,
                    'message' => 'Dados biométricos inválidos',
                ];
            }

            // Hash biometric template for security
            $hashedTemplate = $this->hashBiometricTemplate($biometricData['template']);

            // Store biometric data
            $biometric = $user->biometricData()->create([
                'type' => $biometricData['type'], // fingerprint, face, voice
                'template_hash' => $hashedTemplate,
                'quality_score' => $biometricData['quality_score'] ?? 0,
                'device_info' => $biometricData['device_info'] ?? null,
                'enrollment_date' => now(),
                'is_active' => true,
            ]);

            // Log activity
            activity('biometric')
                ->performedOn($user)
                ->withProperties([
                    'type' => $biometricData['type'],
                    'quality_score' => $biometricData['quality_score'] ?? 0,
                ])
                ->log('Biometric data registered');

            return [
                'success' => true,
                'biometric_id' => $biometric->id,
                'message' => 'Dados biométricos registrados com sucesso',
            ];
        } catch (\Exception $e) {
            Log::error('Biometric registration failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao registrar dados biométricos',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify biometric data against stored template.
     */
    public function verifyBiometric(User $user, array $biometricData): array
    {
        try {
            // Check if user has biometric data registered
            $storedBiometric = $user->biometricData()
                ->where('type', $biometricData['type'])
                ->where('is_active', true)
                ->first();

            if (!$storedBiometric) {
                return [
                    'success' => false,
                    'message' => 'Dados biométricos não encontrados',
                    'code' => 'BIOMETRIC_NOT_FOUND',
                ];
            }

            // Validate input biometric data
            if (!$this->validateBiometricData($biometricData)) {
                return [
                    'success' => false,
                    'message' => 'Dados biométricos inválidos',
                    'code' => 'INVALID_BIOMETRIC_DATA',
                ];
            }

            // Check rate limiting
            if ($this->isRateLimited($user)) {
                return [
                    'success' => false,
                    'message' => 'Muitas tentativas de verificação. Tente novamente em alguns minutos.',
                    'code' => 'RATE_LIMITED',
                ];
            }

            // Perform biometric matching
            $matchResult = $this->performBiometricMatch(
                $storedBiometric->template_hash,
                $biometricData['template']
            );

            // Log verification attempt
            $this->logVerificationAttempt($user, $biometricData['type'], $matchResult['success']);

            if ($matchResult['success']) {
                // Update last verification timestamp
                $storedBiometric->update([
                    'last_verified_at' => now(),
                    'verification_count' => $storedBiometric->verification_count + 1,
                ]);

                // Clear rate limiting cache
                $this->clearRateLimit($user);

                // Log successful verification
                activity('biometric')
                    ->performedOn($user)
                    ->withProperties([
                        'type' => $biometricData['type'],
                        'confidence_score' => $matchResult['confidence_score'],
                    ])
                    ->log('Biometric verification successful');

                return [
                    'success' => true,
                    'confidence_score' => $matchResult['confidence_score'],
                    'message' => 'Verificação biométrica bem-sucedida',
                ];
            } else {
                // Increment failed attempts
                $this->incrementFailedAttempts($user);

                return [
                    'success' => false,
                    'message' => 'Verificação biométrica falhou',
                    'code' => 'BIOMETRIC_MISMATCH',
                    'confidence_score' => $matchResult['confidence_score'],
                ];
            }
        } catch (\Exception $e) {
            Log::error('Biometric verification failed', [
                'user_id' => $user->id,
                'type' => $biometricData['type'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro na verificação biométrica',
                'code' => 'VERIFICATION_ERROR',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update biometric data.
     */
    public function updateBiometric(User $user, string $type, array $biometricData): array
    {
        try {
            $storedBiometric = $user->biometricData()
                ->where('type', $type)
                ->where('is_active', true)
                ->first();

            if (!$storedBiometric) {
                return [
                    'success' => false,
                    'message' => 'Dados biométricos não encontrados',
                ];
            }

            // Validate new biometric data
            if (!$this->validateBiometricData($biometricData)) {
                return [
                    'success' => false,
                    'message' => 'Dados biométricos inválidos',
                ];
            }

            // Hash new template
            $hashedTemplate = $this->hashBiometricTemplate($biometricData['template']);

            // Update biometric data
            $storedBiometric->update([
                'template_hash' => $hashedTemplate,
                'quality_score' => $biometricData['quality_score'] ?? $storedBiometric->quality_score,
                'device_info' => $biometricData['device_info'] ?? $storedBiometric->device_info,
                'updated_at' => now(),
            ]);

            // Log activity
            activity('biometric')
                ->performedOn($user)
                ->withProperties(['type' => $type])
                ->log('Biometric data updated');

            return [
                'success' => true,
                'message' => 'Dados biométricos atualizados com sucesso',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao atualizar dados biométricos',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete biometric data.
     */
    public function deleteBiometric(User $user, string $type): array
    {
        try {
            $deleted = $user->biometricData()
                ->where('type', $type)
                ->delete();

            if ($deleted) {
                // Log activity
                activity('biometric')
                    ->performedOn($user)
                    ->withProperties(['type' => $type])
                    ->log('Biometric data deleted');

                return [
                    'success' => true,
                    'message' => 'Dados biométricos removidos com sucesso',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Dados biométricos não encontrados',
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao remover dados biométricos',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get user's biometric data summary.
     */
    public function getBiometricSummary(User $user): array
    {
        try {
            $biometrics = $user->biometricData()->where('is_active', true)->get();

            $summary = [];
            foreach ($biometrics as $biometric) {
                $summary[] = [
                    'type' => $biometric->type,
                    'quality_score' => $biometric->quality_score,
                    'enrollment_date' => $biometric->enrollment_date->format('d/m/Y'),
                    'last_verified_at' => $biometric->last_verified_at ? $biometric->last_verified_at->format('d/m/Y H:i') : null,
                    'verification_count' => $biometric->verification_count,
                    'is_active' => $biometric->is_active,
                ];
            }

            return [
                'success' => true,
                'biometrics' => $summary,
                'total_registered' => count($summary),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao obter dados biométricos',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate biometric challenge for authentication.
     */
    public function generateBiometricChallenge(User $user): array
    {
        try {
            $challenge = [
                'challenge_id' => Str::uuid(),
                'user_id' => $user->id,
                'timestamp' => now()->timestamp,
                'expires_at' => now()->addMinutes(5)->timestamp,
                'nonce' => Str::random(32),
            ];

            // Store challenge in cache for 5 minutes
            Cache::put(
                "biometric_challenge_{$challenge['challenge_id']}",
                $challenge,
                300 // 5 minutes
            );

            return [
                'success' => true,
                'challenge' => $challenge,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao gerar desafio biométrico',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify biometric challenge response.
     */
    public function verifyBiometricChallenge(string $challengeId, array $response): array
    {
        try {
            // Get challenge from cache
            $challenge = Cache::get("biometric_challenge_{$challengeId}");

            if (!$challenge) {
                return [
                    'success' => false,
                    'message' => 'Desafio biométrico expirado ou inválido',
                    'code' => 'CHALLENGE_EXPIRED',
                ];
            }

            // Check if challenge is still valid
            if (now()->timestamp > $challenge['expires_at']) {
                Cache::forget("biometric_challenge_{$challengeId}");
                return [
                    'success' => false,
                    'message' => 'Desafio biométrico expirado',
                    'code' => 'CHALLENGE_EXPIRED',
                ];
            }

            // Get user
            $user = User::find($challenge['user_id']);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                    'code' => 'USER_NOT_FOUND',
                ];
            }

            // Verify biometric data
            $verificationResult = $this->verifyBiometric($user, $response['biometric_data']);

            // Clear challenge from cache
            Cache::forget("biometric_challenge_{$challengeId}");

            return $verificationResult;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na verificação do desafio biométrico',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate biometric data format.
     */
    private function validateBiometricData(array $data): bool
    {
        // Check required fields
        if (!isset($data['type']) || !isset($data['template'])) {
            return false;
        }

        // Validate biometric type
        $allowedTypes = ['fingerprint', 'face', 'voice', 'iris'];
        if (!in_array($data['type'], $allowedTypes)) {
            return false;
        }

        // Validate template format (basic validation)
        if (empty($data['template']) || !is_string($data['template'])) {
            return false;
        }

        // Validate quality score if provided
        if (isset($data['quality_score'])) {
            if (!is_numeric($data['quality_score']) || $data['quality_score'] < 0 || $data['quality_score'] > 100) {
                return false;
            }
        }

        return true;
    }

    /**
     * Hash biometric template for secure storage.
     */
    private function hashBiometricTemplate(string $template): string
    {
        // Use a secure hashing algorithm
        // In production, you might want to use a more sophisticated approach
        return hash('sha256', $template . config('app.key'));
    }

    /**
     * Perform biometric matching.
     */
    private function performBiometricMatch(string $storedHash, string $inputTemplate): array
    {
        // Hash the input template
        $inputHash = $this->hashBiometricTemplate($inputTemplate);

        // Simple hash comparison (in production, use more sophisticated matching)
        $isMatch = hash_equals($storedHash, $inputHash);

        // Simulate confidence score (in production, this would come from the biometric engine)
        $confidenceScore = $isMatch ? rand(85, 99) : rand(10, 40);

        return [
            'success' => $isMatch,
            'confidence_score' => $confidenceScore,
        ];
    }

    /**
     * Check if user is rate limited.
     */
    private function isRateLimited(User $user): bool
    {
        $key = "biometric_attempts_{$user->id}";
        $attempts = Cache::get($key, 0);
        
        // Allow 5 attempts per 15 minutes
        return $attempts >= 5;
    }

    /**
     * Increment failed attempts counter.
     */
    private function incrementFailedAttempts(User $user): void
    {
        $key = "biometric_attempts_{$user->id}";
        $attempts = Cache::get($key, 0);
        Cache::put($key, $attempts + 1, 900); // 15 minutes
    }

    /**
     * Clear rate limiting cache.
     */
    private function clearRateLimit(User $user): void
    {
        Cache::forget("biometric_attempts_{$user->id}");
    }

    /**
     * Log verification attempt.
     */
    private function logVerificationAttempt(User $user, string $type, bool $success): void
    {
        Log::info('Biometric verification attempt', [
            'user_id' => $user->id,
            'type' => $type,
            'success' => $success,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get biometric statistics.
     */
    public function getBiometricStatistics(): array
    {
        try {
            return [
                'total_registered_users' => User::whereHas('biometricData')->count(),
                'total_biometric_records' => BiometricData::count(),
                'biometrics_by_type' => BiometricData::groupBy('type')
                    ->selectRaw('type, count(*) as count')
                    ->pluck('count', 'type')
                    ->toArray(),
                'recent_verifications' => BiometricData::whereNotNull('last_verified_at')
                    ->where('last_verified_at', '>=', now()->subDays(7))
                    ->count(),
                'average_quality_score' => BiometricData::avg('quality_score'),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cleanup expired biometric data.
     */
    public function cleanupExpiredData(): array
    {
        try {
            // Remove biometric data older than 2 years
            $expiredCount = BiometricData::where('enrollment_date', '<', now()->subYears(2))
                ->delete();

            // Clear expired challenges from cache
            // This would be handled by Redis TTL, but we can also manually clean
            
            return [
                'success' => true,
                'expired_records_removed' => $expiredCount,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na limpeza de dados biométricos',
                'error' => $e->getMessage(),
            ];
        }
    }
}