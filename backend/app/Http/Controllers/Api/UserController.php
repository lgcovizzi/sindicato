<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UpdatePreferencesRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use App\Services\UserService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        $this->middleware('auth:api');
        $this->middleware('role:admin|manager', ['only' => ['index', 'show', 'destroy', 'updateRole']]);
    }

    /**
     * Get paginated list of users (Admin/Manager only).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $users = QueryBuilder::for(User::class)
                ->allowedFilters([
                    AllowedFilter::exact('status'),
                    AllowedFilter::exact('role'),
                    AllowedFilter::partial('name'),
                    AllowedFilter::partial('email'),
                    AllowedFilter::exact('email_verified_at'),
                    AllowedFilter::scope('active'),
                    AllowedFilter::scope('verified'),
                ])
                ->allowedSorts([
                    AllowedSort::field('name'),
                    AllowedSort::field('email'),
                    AllowedSort::field('created_at'),
                    AllowedSort::field('updated_at'),
                    AllowedSort::field('last_login_at'),
                ])
                ->with(['roles', 'preferences'])
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => new UserCollection($users),
                'meta' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar usuários',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get specific user details (Admin/Manager only).
     */
    public function show(User $user): JsonResponse
    {
        try {
            $user->load([
                'roles',
                'permissions',
                'preferences',
                'votes' => function ($query) {
                    $query->latest()->limit(10);
                },
                'activities' => function ($query) {
                    $query->latest()->limit(20);
                },
            ]);

            return response()->json([
                'success' => true,
                'data' => new UserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar usuário',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update user profile.
     */
    public function update(UpdateUserRequest $request, User $user = null): JsonResponse
    {
        try {
            // If no user specified, update current user
            if (!$user) {
                $user = auth('api')->user();
            }

            // Check if user can update this profile
            if (!$this->canUpdateUser($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não autorizado a atualizar este perfil',
                ], 403);
            }

            $userData = $request->validated();
            $result = $this->userService->updateUser($user, $userData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? null,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso',
                'data' => new UserResource($result['user']),
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
                'message' => 'Erro ao atualizar perfil',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(UpdatePreferencesRequest $request): JsonResponse
    {
        try {
            $user = auth('api')->user();
            $preferences = $request->validated();
            
            $result = $this->userService->updatePreferences($user, $preferences);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Preferências atualizadas com sucesso',
                'data' => $result['preferences'],
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
                'message' => 'Erro ao atualizar preferências',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get user preferences.
     */
    public function getPreferences(): JsonResponse
    {
        try {
            $user = auth('api')->user();
            $preferences = $this->userService->getUserPreferences($user);

            return response()->json([
                'success' => true,
                'data' => $preferences,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar preferências',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Upload user avatar.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $user = auth('api')->user();
            $result = $this->userService->uploadAvatar($user, $request->file('avatar'));

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Avatar atualizado com sucesso',
                'data' => [
                    'avatar_url' => $result['avatar_url'],
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Arquivo inválido',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer upload do avatar',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(): JsonResponse
    {
        try {
            $user = auth('api')->user();
            $result = $this->userService->deleteAvatar($user);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Avatar removido com sucesso',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover avatar',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get user activity log.
     */
    public function getActivityLog(Request $request): JsonResponse
    {
        try {
            $user = auth('api')->user();
            $activities = $this->userService->getUserActivityLog(
                $user,
                $request->get('per_page', 15),
                $request->get('filter')
            );

            return response()->json([
                'success' => true,
                'data' => $activities,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar histórico de atividades',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get user voting history.
     */
    public function getVotingHistory(Request $request): JsonResponse
    {
        try {
            $user = auth('api')->user();
            $votes = $this->userService->getUserVotingHistory(
                $user,
                $request->get('per_page', 15),
                $request->get('status')
            );

            return response()->json([
                'success' => true,
                'data' => $votes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar histórico de votações',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update user role (Admin only).
     */
    public function updateRole(Request $request, User $user): JsonResponse
    {
        try {
            $request->validate([
                'role' => 'required|string|exists:roles,name',
            ]);

            // Check if current user is admin
            if (!auth('api')->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas administradores podem alterar roles',
                ], 403);
            }

            $result = $this->userService->updateUserRole($user, $request->role);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role atualizada com sucesso',
                'data' => new UserResource($result['user']),
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
                'message' => 'Erro ao atualizar role',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Deactivate user account (Admin/Manager only).
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            // Prevent self-deletion
            if ($user->id === auth('api')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível desativar sua própria conta',
                ], 422);
            }

            $result = $this->userService->deactivateUser($user);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuário desativado com sucesso',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao desativar usuário',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Reactivate user account (Admin only).
     */
    public function reactivate(User $user): JsonResponse
    {
        try {
            $result = $this->userService->reactivateUser($user);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuário reativado com sucesso',
                'data' => new UserResource($result['user']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao reativar usuário',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get user statistics (Admin/Manager only).
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $stats = $this->userService->getUserStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Check if current user can update the specified user.
     */
    private function canUpdateUser(User $user): bool
    {
        $currentUser = auth('api')->user();
        
        // Users can update their own profile
        if ($currentUser->id === $user->id) {
            return true;
        }
        
        // Admins and managers can update other users
        return $currentUser->hasAnyRole(['admin', 'manager']);
    }
}