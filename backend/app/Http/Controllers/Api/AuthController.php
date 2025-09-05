<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\BiometricLoginRequest;
use App\Services\AuthService;
use App\Services\BiometricService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $authService;
    protected $biometricService;

    public function __construct(AuthService $authService, BiometricService $biometricService)
    {
        $this->authService = $authService;
        $this->biometricService = $biometricService;
        $this->middleware('auth:api', ['except' => ['login', 'register', 'biometricLogin', 'refresh']]);
    }

    /**
     * User login with email/password.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();
            $result = $this->authService->login($credentials);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'data' => [
                    'user' => $result['user'],
                    'access_token' => $result['access_token'],
                    'refresh_token' => $result['refresh_token'],
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * User registration.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $userData = $request->validated();
            $result = $this->authService->register($userData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? null,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuário registrado com sucesso',
                'data' => [
                    'user' => $result['user'],
                    'access_token' => $result['access_token'],
                    'refresh_token' => $result['refresh_token'],
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Biometric authentication login.
     */
    public function biometricLogin(BiometricLoginRequest $request): JsonResponse
    {
        try {
            $biometricData = $request->validated();
            $result = $this->biometricService->authenticate($biometricData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 401);
            }

            // Generate JWT tokens
            $tokens = $this->authService->generateTokens($result['user']);

            return response()->json([
                'success' => true,
                'message' => 'Autenticação biométrica realizada com sucesso',
                'data' => [
                    'user' => $result['user'],
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token'],
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'biometric_verified' => true,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na autenticação biométrica',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get authenticated user profile.
     */
    public function me(): JsonResponse
    {
        try {
            $user = auth('api')->user();
            $user->load(['preferences', 'roles', 'permissions']);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'roles' => $user->getRoleNames(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar perfil do usuário',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Refresh JWT token.
     */
    public function refresh(): JsonResponse
    {
        try {
            $newToken = auth('api')->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Token renovado com sucesso',
                'data' => [
                    'access_token' => $newToken,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao renovar token',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 401);
        }
    }

    /**
     * User logout.
     */
    public function logout(): JsonResponse
    {
        try {
            $user = auth('api')->user();
            
            // Invalidate current token
            auth('api')->logout();
            
            // Log logout activity
            activity('auth')
                ->performedOn($user)
                ->log('User logged out');

            return response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar logout',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Register biometric data for user.
     */
    public function registerBiometric(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'biometric_data' => 'required|string',
                'biometric_type' => 'required|in:fingerprint,face,voice',
                'device_info' => 'sometimes|array',
            ]);

            $user = auth('api')->user();
            $result = $this->biometricService->registerBiometric(
                $user,
                $request->biometric_data,
                $request->biometric_type,
                $request->device_info
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dados biométricos registrados com sucesso',
                'data' => $result['biometric'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar dados biométricos',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            $user = auth('api')->user();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Senha atual incorreta',
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            // Log password change
            activity('auth')
                ->performedOn($user)
                ->log('Password updated');

            return response()->json([
                'success' => true,
                'message' => 'Senha atualizada com sucesso',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar senha',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get user's active sessions.
     */
    public function activeSessions(): JsonResponse
    {
        try {
            $user = auth('api')->user();
            $sessions = $this->authService->getActiveSessions($user);

            return response()->json([
                'success' => true,
                'data' => $sessions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar sessões ativas',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Revoke specific session.
     */
    public function revokeSession(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'session_id' => 'required|string',
            ]);

            $user = auth('api')->user();
            $result = $this->authService->revokeSession($user, $request->session_id);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sessão revogada com sucesso',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao revogar sessão',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Revoke all sessions except current.
     */
    public function revokeAllSessions(): JsonResponse
    {
        try {
            $user = auth('api')->user();
            $currentToken = auth('api')->getToken();
            
            $result = $this->authService->revokeAllSessions($user, $currentToken);

            return response()->json([
                'success' => true,
                'message' => 'Todas as sessões foram revogadas',
                'data' => [
                    'revoked_sessions' => $result['revoked_count'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao revogar sessões',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}