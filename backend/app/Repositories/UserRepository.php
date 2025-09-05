<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserRepository
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Get model instance.
     */
    public function getModel(): User
    {
        return $this->model;
    }

    /**
     * Find user by ID.
     */
    public function find(int $id): ?User
    {
        return $this->model->find($id);
    }

    /**
     * Find user by ID with relationships.
     */
    public function findWithRelations(int $id, array $relations = []): ?User
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Find multiple users by IDs.
     */
    public function findMany(array $ids): Collection
    {
        return $this->model->whereIn('id', $ids)->get();
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Find user by CPF.
     */
    public function findByCpf(string $cpf): ?User
    {
        return $this->model->where('cpf', $cpf)->first();
    }

    /**
     * Find user by phone.
     */
    public function findByPhone(string $phone): ?User
    {
        return $this->model->where('phone', $phone)->first();
    }

    /**
     * Create new user.
     */
    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    /**
     * Update user.
     */
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    /**
     * Delete user.
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Get all users with pagination.
     */
    public function paginate(int $perPage = 15, array $filters = [], array $sorts = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        // Apply sorting
        $query = $this->applySorts($query, $sorts);

        return $query->paginate($perPage);
    }

    /**
     * Get all users.
     */
    public function all(array $filters = [], array $sorts = []): Collection
    {
        $query = $this->model->newQuery();

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        // Apply sorting
        $query = $this->applySorts($query, $sorts);

        return $query->get();
    }

    /**
     * Count total users.
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Count users by status.
     */
    public function countByStatus(string $status): int
    {
        return $this->model->where('status', $status)->count();
    }

    /**
     * Count verified users.
     */
    public function countVerified(): int
    {
        return $this->model->whereNotNull('email_verified_at')->count();
    }

    /**
     * Count unverified users.
     */
    public function countUnverified(): int
    {
        return $this->model->whereNull('email_verified_at')->count();
    }

    /**
     * Count users created after date.
     */
    public function countCreatedAfter(Carbon $date): int
    {
        return $this->model->where('created_at', '>=', $date)->count();
    }

    /**
     * Count users with recent logins.
     */
    public function countLoginsAfter(Carbon $date): int
    {
        return $this->model->where('last_login_at', '>=', $date)->count();
    }

    /**
     * Search users.
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where(function ($q) use ($query) {
            $q->where('name', 'ILIKE', "%{$query}%")
              ->orWhere('email', 'ILIKE', "%{$query}%")
              ->orWhere('cpf', 'ILIKE', "%{$query}%")
              ->orWhere('phone', 'ILIKE', "%{$query}%");
        })->paginate($perPage);
    }

    /**
     * Get users for export.
     */
    public function getForExport(array $filters = []): Collection
    {
        $query = $this->model->with(['roles']);

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Get active users.
     */
    public function getActiveUsers(): Collection
    {
        return $this->model->where('status', 'active')->get();
    }

    /**
     * Get users by role.
     */
    public function getUsersByRole(string $roleName): Collection
    {
        return $this->model->role($roleName)->get();
    }

    /**
     * Get users by department.
     */
    public function getUsersByDepartment(string $department): Collection
    {
        return $this->model->where('department', $department)->get();
    }

    /**
     * Get users with birthdays in current month.
     */
    public function getUsersWithBirthdaysThisMonth(): Collection
    {
        return $this->model->whereMonth('birth_date', now()->month)
            ->whereNotNull('birth_date')
            ->orderBy('birth_date')
            ->get();
    }

    /**
     * Get users who haven't logged in for X days.
     */
    public function getInactiveUsers(int $days = 30): Collection
    {
        $cutoffDate = now()->subDays($days);
        
        return $this->model->where(function ($query) use ($cutoffDate) {
            $query->where('last_login_at', '<', $cutoffDate)
                  ->orWhereNull('last_login_at');
        })->where('status', 'active')->get();
    }

    /**
     * Get users with incomplete profiles.
     */
    public function getUsersWithIncompleteProfiles(): Collection
    {
        return $this->model->where(function ($query) {
            $query->whereNull('phone')
                  ->orWhereNull('birth_date')
                  ->orWhereNull('address')
                  ->orWhereNull('email_verified_at');
        })->get();
    }

    /**
     * Get recent users.
     */
    public function getRecentUsers(int $days = 7, int $limit = 10): Collection
    {
        return $this->model->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get users statistics by period.
     */
    public function getUserStatsByPeriod(string $period = 'month'): array
    {
        $dateFormat = match ($period) {
            'day' => 'Y-m-d',
            'week' => 'Y-W',
            'month' => 'Y-m',
            'year' => 'Y',
            default => 'Y-m',
        };

        return $this->model->selectRaw(
            "TO_CHAR(created_at, '{$dateFormat}') as period, COUNT(*) as count"
        )
        ->groupBy('period')
        ->orderBy('period')
        ->pluck('count', 'period')
        ->toArray();
    }

    /**
     * Get top users by activity.
     */
    public function getTopUsersByActivity(int $limit = 10): Collection
    {
        return $this->model->withCount('activities')
            ->orderBy('activities_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get users eligible for voting.
     */
    public function getEligibleVoters(array $criteria = []): Collection
    {
        $query = $this->model->where('status', 'active')
            ->whereNotNull('email_verified_at');

        // Apply role criteria
        if (isset($criteria['roles']) && !empty($criteria['roles'])) {
            $query->whereHas('roles', function ($q) use ($criteria) {
                $q->whereIn('name', $criteria['roles']);
            });
        }

        // Apply department criteria
        if (isset($criteria['departments']) && !empty($criteria['departments'])) {
            $query->whereIn('department', $criteria['departments']);
        }

        // Apply age criteria
        if (isset($criteria['min_age'])) {
            $query->where('birth_date', '<=', now()->subYears($criteria['min_age']));
        }

        return $query->get();
    }

    /**
     * Apply filters to query.
     */
    private function applyFilters($query, array $filters)
    {
        foreach ($filters as $field => $value) {
            if (empty($value)) {
                continue;
            }

            switch ($field) {
                case 'status':
                    $query->where('status', $value);
                    break;
                    
                case 'role':
                    $query->whereHas('roles', function ($q) use ($value) {
                        $q->where('name', $value);
                    });
                    break;
                    
                case 'department':
                    $query->where('department', $value);
                    break;
                    
                case 'verified':
                    if ($value === 'yes') {
                        $query->whereNotNull('email_verified_at');
                    } elseif ($value === 'no') {
                        $query->whereNull('email_verified_at');
                    }
                    break;
                    
                case 'created_from':
                    $query->where('created_at', '>=', $value);
                    break;
                    
                case 'created_to':
                    $query->where('created_at', '<=', $value);
                    break;
                    
                case 'last_login_from':
                    $query->where('last_login_at', '>=', $value);
                    break;
                    
                case 'last_login_to':
                    $query->where('last_login_at', '<=', $value);
                    break;
                    
                case 'search':
                    $query->where(function ($q) use ($value) {
                        $q->where('name', 'ILIKE', "%{$value}%")
                          ->orWhere('email', 'ILIKE', "%{$value}%")
                          ->orWhere('cpf', 'ILIKE', "%{$value}%")
                          ->orWhere('phone', 'ILIKE', "%{$value}%");
                    });
                    break;
            }
        }

        return $query;
    }

    /**
     * Apply sorting to query.
     */
    private function applySorts($query, array $sorts)
    {
        if (empty($sorts)) {
            return $query->orderBy('created_at', 'desc');
        }

        foreach ($sorts as $field => $direction) {
            $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
            
            switch ($field) {
                case 'name':
                case 'email':
                case 'status':
                case 'created_at':
                case 'updated_at':
                case 'last_login_at':
                    $query->orderBy($field, $direction);
                    break;
                    
                case 'role':
                    $query->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                          ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                          ->orderBy('roles.name', $direction)
                          ->select('users.*');
                    break;
            }
        }

        return $query;
    }

    /**
     * Bulk update users.
     */
    public function bulkUpdate(array $userIds, array $data): int
    {
        return $this->model->whereIn('id', $userIds)->update($data);
    }

    /**
     * Bulk delete users.
     */
    public function bulkDelete(array $userIds): int
    {
        return $this->model->whereIn('id', $userIds)->delete();
    }

    /**
     * Get users with specific permissions.
     */
    public function getUsersWithPermission(string $permission): Collection
    {
        return $this->model->permission($permission)->get();
    }

    /**
     * Get users without specific role.
     */
    public function getUsersWithoutRole(string $roleName): Collection
    {
        return $this->model->whereDoesntHave('roles', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        })->get();
    }

    /**
     * Get duplicate users by email.
     */
    public function getDuplicateEmails(): Collection
    {
        return $this->model->select('email')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('email');
    }

    /**
     * Get duplicate users by CPF.
     */
    public function getDuplicateCpfs(): Collection
    {
        return $this->model->select('cpf')
            ->whereNotNull('cpf')
            ->groupBy('cpf')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('cpf');
    }

    /**
     * Get users by age range.
     */
    public function getUsersByAgeRange(int $minAge, int $maxAge): Collection
    {
        $maxDate = now()->subYears($minAge);
        $minDate = now()->subYears($maxAge + 1);
        
        return $this->model->whereBetween('birth_date', [$minDate, $maxDate])->get();
    }

    /**
     * Get users registered in date range.
     */
    public function getUsersRegisteredBetween(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate])->get();
    }

    /**
     * Get user activity summary.
     */
    public function getUserActivitySummary(User $user): array
    {
        return [
            'total_activities' => $user->activities()->count(),
            'total_votes' => $user->votes()->count(),
            'total_convenio_usage' => $user->convenioUsages()->count(),
            'favorite_convenios' => $user->favoriteConvenios()->count(),
            'profile_completion' => $this->calculateProfileCompletion($user),
            'last_activity' => $user->activities()->latest()->first()?->created_at,
            'registration_date' => $user->created_at,
            'last_login' => $user->last_login_at,
        ];
    }

    /**
     * Calculate profile completion percentage.
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
        ];

        $completedFields = array_filter($fields);
        $totalFields = count($fields);
        $completedCount = count($completedFields);

        return round(($completedCount / $totalFields) * 100);
    }
}