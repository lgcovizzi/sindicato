<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Carbon\Carbon;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Update user profile.
     */
    public function updateUser(User $user, array $userData): array
    {
        try {
            DB::beginTransaction();

            // Remove sensitive fields that shouldn't be updated here
            unset($userData['password'], $userData['email_verified_at'], $userData['status']);

            // Handle email change
            if (isset($userData['email']) && $userData['email'] !== $user->email) {
                // Check if email is already in use
                if ($this->userRepository->findByEmail($userData['email'])) {
                    return [
                        'success' => false,
                        'message' => 'Este email já está em uso',
                    ];
                }

                // Mark email as unverified and generate new verification token
                $userData['email_verified_at'] = null;
                $userData['email_verification_token'] = Str::random(60);
            }

            // Update user
            $user->update($userData);

            // Log activity
            activity('user')
                ->performedOn($user)
                ->withProperties($userData)
                ->log('Profile updated');

            DB::commit();

            return [
                'success' => true,
                'user' => $user->fresh(['roles', 'permissions', 'preferences']),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erro ao atualizar perfil',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(User $user, array $preferences): array
    {
        try {
            DB::beginTransaction();

            // Get or create user preferences
            $userPreferences = $user->preferences ?: $user->preferences()->create([]);

            // Update preferences
            $userPreferences->update($preferences);

            // Log activity
            activity('user')
                ->performedOn($user)
                ->withProperties($preferences)
                ->log('Preferences updated');

            DB::commit();

            return [
                'success' => true,
                'preferences' => $userPreferences->fresh(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erro ao atualizar preferências',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get user preferences with defaults.
     */
    public function getUserPreferences(User $user): array
    {
        $preferences = $user->preferences;

        if (!$preferences) {
            // Return default preferences
            return [
                'theme' => 'light',
                'language' => 'pt-BR',
                'notifications_enabled' => true,
                'email_notifications' => true,
                'push_notifications' => true,
                'voting_reminders' => true,
                'news_notifications' => true,
                'convenio_notifications' => true,
                'density' => 'normal',
                'sidebar_collapsed' => false,
                'show_avatars' => true,
                'auto_refresh' => true,
                'sound_enabled' => true,
            ];
        }

        return $preferences->toArray();
    }

    /**
     * Upload user avatar.
     */
    public function uploadAvatar(User $user, UploadedFile $file): array
    {
        try {
            // Validate file
            if (!$file->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Arquivo inválido',
                ];
            }

            // Remove existing avatar
            $user->clearMediaCollection('avatars');

            // Add new avatar
            $media = $user->addMediaFromRequest('avatar')
                ->toMediaCollection('avatars');

            // Log activity
            activity('user')
                ->performedOn($user)
                ->withProperties(['media_id' => $media->id])
                ->log('Avatar updated');

            return [
                'success' => true,
                'avatar_url' => $media->getUrl(),
                'media' => $media,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao fazer upload do avatar',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(User $user): array
    {
        try {
            $user->clearMediaCollection('avatars');

            // Log activity
            activity('user')
                ->performedOn($user)
                ->log('Avatar removed');

            return [
                'success' => true,
                'message' => 'Avatar removido com sucesso',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao remover avatar',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get user activity log.
     */
    public function getUserActivityLog(User $user, int $perPage = 15, ?string $filter = null): array
    {
        try {
            $query = $user->activities()->latest();

            if ($filter) {
                $query->where('log_name', $filter);
            }

            $activities = $query->paginate($perPage);

            return [
                'data' => $activities->items(),
                'meta' => [
                    'total' => $activities->total(),
                    'per_page' => $activities->perPage(),
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'data' => [],
                'meta' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get user voting history.
     */
    public function getUserVotingHistory(User $user, int $perPage = 15, ?string $status = null): array
    {
        try {
            $query = $user->votes()->with(['voting', 'option'])->latest();

            if ($status) {
                $query->whereHas('voting', function ($q) use ($status) {
                    $q->where('status', $status);
                });
            }

            $votes = $query->paginate($perPage);

            return [
                'data' => $votes->items(),
                'meta' => [
                    'total' => $votes->total(),
                    'per_page' => $votes->perPage(),
                    'current_page' => $votes->currentPage(),
                    'last_page' => $votes->lastPage(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'data' => [],
                'meta' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update user role.
     */
    public function updateUserRole(User $user, string $roleName): array
    {
        try {
            DB::beginTransaction();

            // Remove all existing roles
            $user->syncRoles([]);

            // Assign new role
            $user->assignRole($roleName);

            // Log activity
            activity('user')
                ->performedOn($user)
                ->withProperties(['new_role' => $roleName])
                ->log('Role updated');

            DB::commit();

            return [
                'success' => true,
                'user' => $user->fresh(['roles', 'permissions']),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erro ao atualizar role',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Deactivate user account.
     */
    public function deactivateUser(User $user): array
    {
        try {
            DB::beginTransaction();

            // Update user status
            $user->update([
                'status' => 'inactive',
                'deactivated_at' => now(),
            ]);

            // TODO: Revoke all active sessions
            // $this->authService->revokeAllSessions($user);

            // Log activity
            activity('user')
                ->performedOn($user)
                ->log('Account deactivated');

            DB::commit();

            return [
                'success' => true,
                'message' => 'Usuário desativado com sucesso',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erro ao desativar usuário',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Reactivate user account.
     */
    public function reactivateUser(User $user): array
    {
        try {
            DB::beginTransaction();

            // Update user status
            $user->update([
                'status' => 'active',
                'deactivated_at' => null,
            ]);

            // Log activity
            activity('user')
                ->performedOn($user)
                ->log('Account reactivated');

            DB::commit();

            return [
                'success' => true,
                'user' => $user->fresh(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erro ao reativar usuário',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get user statistics.
     */
    public function getUserStatistics(): array
    {
        try {
            $now = Carbon::now();
            $lastMonth = $now->copy()->subMonth();
            $lastWeek = $now->copy()->subWeek();
            $today = $now->copy()->startOfDay();

            return [
                'total_users' => $this->userRepository->count(),
                'active_users' => $this->userRepository->countByStatus('active'),
                'inactive_users' => $this->userRepository->countByStatus('inactive'),
                'verified_users' => $this->userRepository->countVerified(),
                'unverified_users' => $this->userRepository->countUnverified(),
                'new_users_today' => $this->userRepository->countCreatedAfter($today),
                'new_users_this_week' => $this->userRepository->countCreatedAfter($lastWeek),
                'new_users_this_month' => $this->userRepository->countCreatedAfter($lastMonth),
                'recent_logins_today' => $this->userRepository->countLoginsAfter($today),
                'recent_logins_this_week' => $this->userRepository->countLoginsAfter($lastWeek),
                'users_by_role' => $this->getUsersByRole(),
                'users_by_status' => $this->getUsersByStatus(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get users count by role.
     */
    private function getUsersByRole(): array
    {
        try {
            return DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_type', User::class)
                ->groupBy('roles.name')
                ->selectRaw('roles.name as role, count(*) as count')
                ->pluck('count', 'role')
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get users count by status.
     */
    private function getUsersByStatus(): array
    {
        try {
            return $this->userRepository->getModel()
                ->groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->pluck('count', 'status')
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Search users.
     */
    public function searchUsers(string $query, int $perPage = 15): array
    {
        try {
            $users = $this->userRepository->search($query, $perPage);

            return [
                'data' => $users->items(),
                'meta' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'data' => [],
                'meta' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Export users data.
     */
    public function exportUsers(array $filters = []): array
    {
        try {
            $users = $this->userRepository->getForExport($filters);

            $exportData = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'email_verified' => $user->email_verified_at ? 'Sim' : 'Não',
                    'roles' => $user->getRoleNames()->implode(', '),
                    'created_at' => $user->created_at->format('d/m/Y H:i'),
                    'last_login_at' => $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca',
                ];
            });

            return [
                'success' => true,
                'data' => $exportData,
                'count' => $exportData->count(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao exportar usuários',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Bulk update users.
     */
    public function bulkUpdateUsers(array $userIds, array $updateData): array
    {
        try {
            DB::beginTransaction();

            $users = $this->userRepository->findMany($userIds);
            $updatedCount = 0;

            foreach ($users as $user) {
                $user->update($updateData);
                $updatedCount++;

                // Log activity
                activity('user')
                    ->performedOn($user)
                    ->withProperties($updateData)
                    ->log('Bulk update applied');
            }

            DB::commit();

            return [
                'success' => true,
                'updated_count' => $updatedCount,
                'message' => "$updatedCount usuários atualizados com sucesso",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erro na atualização em lote',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get user dashboard data.
     */
    public function getUserDashboardData(User $user): array
    {
        try {
            return [
                'profile_completion' => $this->calculateProfileCompletion($user),
                'recent_votes' => $user->votes()->with('voting')->latest()->limit(5)->get(),
                'upcoming_votings' => $user->eligibleVotings()->upcoming()->limit(3)->get(),
                'recent_activities' => $user->activities()->latest()->limit(10)->get(),
                'notifications_count' => $user->unreadNotifications()->count(),
                'convenios_used' => $user->convenioUsages()->count(),
                'favorite_convenios' => $user->favoriteConvenios()->limit(5)->get(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate user profile completion percentage.
     */
    private function calculateProfileCompletion(User $user): int
    {
        $fields = [
            'name' => !empty($user->name),
            'email' => !empty($user->email),
            'phone' => !empty($user->phone),
            'birth_date' => !empty($user->birth_date),
            'address' => !empty($user->address),
            'avatar' => $user->hasMedia('avatars'),
            'email_verified' => $user->hasVerifiedEmail(),
            'preferences' => $user->preferences !== null,
        ];

        $completedFields = array_filter($fields);
        $totalFields = count($fields);
        $completedCount = count($completedFields);

        return round(($completedCount / $totalFields) * 100);
    }
}