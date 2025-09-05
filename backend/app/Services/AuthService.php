<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class AuthService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Authenticate user with email and password.
     */
    public function login(array $credentials): array
    {
        try {
            // Attempt to authenticate
            if (!$token = auth('api')->attempt($credentials)) {
                return [
                    'success' => false,
                    'message' => 'Credenciais inválidas',
                ];
            }

            $user = auth('api')->user();

            // Check if user is active
            if ($user->status !== 'active') {
                auth('api')->logout();
                return [
                    'success' => false,
                    'message' => 'Conta desativada. Entre em contato com o administrador.',
                ];
            }

            // Update last login
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);

            // Generate refresh token
            $refreshToken = $this->generateRefreshToken($user);

            // Log login activity
            activity('auth')
                ->performedOn($user)
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log('User logged in');

            return [
                'success' => true,
                'user' => $user->load(['roles', 'permissions', 'preferences']),
                'access_token' => $token,
                'refresh_token' => $refreshToken,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro interno durante o login',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Register new user.
     */
    public function register(array $userData): array
    {
        try {
            DB::beginTransaction();

            // Check if email already exists
            if ($this->userRepository->findByEmail($userData['email'])) {
                return [
                    'success' => false,
                    'message' => 'Email já está em uso',
                ];
            }

            // Hash password
            $userData['password'] = Hash::make($userData['password']);
            $userData['status'] = 'active';
            $userData['email_verification_token'] = Str::random(60);

            // Create user
            $user = $this->userRepository->create($userData);

            // Assign default role
            $user->assignRole('member');

            // Create default preferences
            $user->preferences()->create([
                'theme' => 'light',
                'language' => 'pt-BR',
                'notifications_enabled' => true,
                'email_notifications' => true,
                'push_notifications' => true,
            ]);

            // Generate tokens
            $token = auth('api')->login($user);
            $refreshToken = $this->generateRefreshToken($user);

            // Log registration activity
            activity('auth')
                ->performedOn($user)
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log('User registered');

            DB::commit();

            return [
                'success' => true,
                'user' => $user->load(['roles', 'permissions', 'preferences']),
                'access_token' => $token,
                'refresh_token' => $refreshToken,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erro interno durante o registro',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate JWT tokens for user.
     */
    public function generateTokens(User $user): array
    {
        $accessToken = auth('api')->login($user);
        $refreshToken = $this->generateRefreshToken($user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    /**
     * Generate refresh token.
     */
    private function generateRefreshToken(User $user): string
    {
        $refreshToken = Str::random(64);
        $expiresAt = Carbon::now()->addDays(30);

        // Store refresh token in cache
        Cache::put(
            "refresh_token:{$user->id}:{$refreshToken}",
            [
                'user_id' => $user->id,
                'expires_at' => $expiresAt,
                'created_at' => now(),
            ],
            $expiresAt
        );

        return $refreshToken;
    }

    /**
     * Refresh access token using refresh token.
     */
    public function refreshToken(string $refreshToken): array
    {
        try {
            $tokenData = Cache::get("refresh_token:*:{$refreshToken}");

            if (!$tokenData) {
                return [
                    'success' => false,
                    'message' => 'Refresh token inválido ou expirado',
                ];
            }

            $user = $this->userRepository->find($tokenData['user_id']);

            if (!$user || $user->status !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado ou inativo',
                ];
            }

            // Generate new tokens
            $newAccessToken = auth('api')->login($user);
            $newRefreshToken = $this->generateRefreshToken($user);

            // Invalidate old refresh token
            Cache::forget("refresh_token:{$user->id}:{$refreshToken}");

            return [
                'success' => true,
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
                'user' => $user->load(['roles', 'permissions', 'preferences']),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao renovar token',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get user's active sessions.
     */
    public function getActiveSessions(User $user): array
    {
        try {
            $cachePattern = "refresh_token:{$user->id}:*";
            $sessions = [];

            // Get all refresh tokens for user from cache
            $keys = Cache::getRedis()->keys($cachePattern);
            
            foreach ($keys as $key) {
                $sessionData = Cache::get($key);
                if ($sessionData) {
                    $sessions[] = [
                        'id' => Str::afterLast($key, ':'),
                        'created_at' => $sessionData['created_at'],
                        'expires_at' => $sessionData['expires_at'],
                        'is_current' => false, // TODO: Implement current session detection
                    ];
                }
            }

            return $sessions;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Revoke specific session.
     */
    public function revokeSession(User $user, string $sessionId): array
    {
        try {
            $cacheKey = "refresh_token:{$user->id}:{$sessionId}";
            
            if (!Cache::has($cacheKey)) {
                return [
                    'success' => false,
                    'message' => 'Sessão não encontrada',
                ];
            }

            Cache::forget($cacheKey);

            // Log session revocation
            activity('auth')
                ->performedOn($user)
                ->withProperties(['session_id' => $sessionId])
                ->log('Session revoked');

            return [
                'success' => true,
                'message' => 'Sessão revogada com sucesso',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao revogar sessão',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Revoke all sessions except current.
     */
    public function revokeAllSessions(User $user, $currentToken = null): array
    {
        try {
            $cachePattern = "refresh_token:{$user->id}:*";
            $keys = Cache::getRedis()->keys($cachePattern);
            $revokedCount = 0;

            foreach ($keys as $key) {
                // TODO: Skip current session if token provided
                Cache::forget($key);
                $revokedCount++;
            }

            // Log mass session revocation
            activity('auth')
                ->performedOn($user)
                ->withProperties(['revoked_count' => $revokedCount])
                ->log('All sessions revoked');

            return [
                'success' => true,
                'revoked_count' => $revokedCount,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao revogar sessões',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify email address.
     */
    public function verifyEmail(string $token): array
    {
        try {
            $user = $this->userRepository->findByEmailVerificationToken($token);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Token de verificação inválido',
                ];
            }

            if ($user->hasVerifiedEmail()) {
                return [
                    'success' => false,
                    'message' => 'Email já foi verificado',
                ];
            }

            // Mark email as verified
            $user->update([
                'email_verified_at' => now(),
                'email_verification_token' => null,
            ]);

            // Log email verification
            activity('auth')
                ->performedOn($user)
                ->log('Email verified');

            return [
                'success' => true,
                'message' => 'Email verificado com sucesso',
                'user' => $user,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao verificar email',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send password reset email.
     */
    public function sendPasswordResetEmail(string $email): array
    {
        try {
            $user = $this->userRepository->findByEmail($email);

            if (!$user) {
                // Don't reveal if email exists for security
                return [
                    'success' => true,
                    'message' => 'Se o email existir, um link de redefinição será enviado',
                ];
            }

            // Generate reset token
            $token = Str::random(60);
            $expiresAt = Carbon::now()->addHours(2);

            // Store reset token
            Cache::put(
                "password_reset:{$user->id}:{$token}",
                [
                    'user_id' => $user->id,
                    'expires_at' => $expiresAt,
                ],
                $expiresAt
            );

            // TODO: Send email with reset link
            // Mail::to($user)->send(new PasswordResetMail($token));

            // Log password reset request
            activity('auth')
                ->performedOn($user)
                ->withProperties(['ip' => request()->ip()])
                ->log('Password reset requested');

            return [
                'success' => true,
                'message' => 'Link de redefinição enviado para o email',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao enviar email de redefinição',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Reset password using token.
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        try {
            $resetData = null;
            $userId = null;

            // Find reset token in cache
            $cacheKeys = Cache::getRedis()->keys("password_reset:*:{$token}");
            
            if (!empty($cacheKeys)) {
                $resetData = Cache::get($cacheKeys[0]);
                $userId = $resetData['user_id'] ?? null;
            }

            if (!$resetData || !$userId) {
                return [
                    'success' => false,
                    'message' => 'Token de redefinição inválido ou expirado',
                ];
            }

            $user = $this->userRepository->find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                ];
            }

            // Update password
            $user->update([
                'password' => Hash::make($newPassword),
            ]);

            // Remove reset token
            Cache::forget($cacheKeys[0]);

            // Revoke all existing sessions
            $this->revokeAllSessions($user);

            // Log password reset
            activity('auth')
                ->performedOn($user)
                ->withProperties(['ip' => request()->ip()])
                ->log('Password reset completed');

            return [
                'success' => true,
                'message' => 'Senha redefinida com sucesso',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao redefinir senha',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if user has valid session.
     */
    public function hasValidSession(User $user): bool
    {
        try {
            $cachePattern = "refresh_token:{$user->id}:*";
            $keys = Cache::getRedis()->keys($cachePattern);
            
            return !empty($keys);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get authentication statistics.
     */
    public function getAuthStatistics(): array
    {
        try {
            return [
                'total_users' => $this->userRepository->count(),
                'active_users' => $this->userRepository->countByStatus('active'),
                'verified_users' => $this->userRepository->countVerified(),
                'recent_logins' => $this->userRepository->countRecentLogins(24), // Last 24 hours
                'online_users' => $this->getOnlineUsersCount(),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get count of currently online users.
     */
    private function getOnlineUsersCount(): int
    {
        try {
            $keys = Cache::getRedis()->keys('refresh_token:*');
            $uniqueUsers = [];
            
            foreach ($keys as $key) {
                $parts = explode(':', $key);
                if (count($parts) >= 3) {
                    $uniqueUsers[$parts[1]] = true;
                }
            }
            
            return count($uniqueUsers);
        } catch (\Exception $e) {
            return 0;
        }
    }
}