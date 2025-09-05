<?php

namespace App\Repositories;

use App\Models\Convenio;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConvenioRepository
{
    protected $model;

    public function __construct(Convenio $model)
    {
        $this->model = $model;
    }

    /**
     * Get model instance.
     */
    public function getModel(): Convenio
    {
        return $this->model;
    }

    /**
     * Find convenio by ID.
     */
    public function find(int $id): ?Convenio
    {
        return $this->model->find($id);
    }

    /**
     * Find convenio by ID with relationships.
     */
    public function findWithRelations(int $id, array $relations = []): ?Convenio
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Find convenio by slug.
     */
    public function findBySlug(string $slug): ?Convenio
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Create new convenio.
     */
    public function create(array $data): Convenio
    {
        return $this->model->create($data);
    }

    /**
     * Update convenio.
     */
    public function update(Convenio $convenio, array $data): bool
    {
        return $convenio->update($data);
    }

    /**
     * Delete convenio.
     */
    public function delete(Convenio $convenio): bool
    {
        return $convenio->delete();
    }

    /**
     * Get all convenios with pagination.
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
     * Get all convenios.
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
     * Get active convenios.
     */
    public function getActiveConvenios(): Collection
    {
        return $this->model->active()->get();
    }

    /**
     * Get featured convenios.
     */
    public function getFeaturedConvenios(): Collection
    {
        return $this->model->featured()->get();
    }

    /**
     * Get online convenios.
     */
    public function getOnlineConvenios(): Collection
    {
        return $this->model->online()->get();
    }

    /**
     * Get physical convenios.
     */
    public function getPhysicalConvenios(): Collection
    {
        return $this->model->physical()->get();
    }

    /**
     * Get convenios by category.
     */
    public function getConveniosByCategory(string $category): Collection
    {
        return $this->model->byCategory($category)->get();
    }

    /**
     * Get convenios by city.
     */
    public function getConveniosByCity(string $city): Collection
    {
        return $this->model->byCity($city)->get();
    }

    /**
     * Get convenios by state.
     */
    public function getConveniosByState(string $state): Collection
    {
        return $this->model->byState($state)->get();
    }

    /**
     * Get convenios near location.
     */
    public function getConveniosNearLocation(float $latitude, float $longitude, float $radius = 10): Collection
    {
        return $this->model->nearLocation($latitude, $longitude, $radius)->get();
    }

    /**
     * Search convenios.
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->search($query)->paginate($perPage);
    }

    /**
     * Get convenios for user.
     */
    public function getConveniosForUser(User $user): Collection
    {
        return $this->model->where(function ($query) use ($user) {
            $query->where('is_public', true)
                  ->orWhereHas('eligibleUsers', function ($q) use ($user) {
                      $q->where('user_id', $user->id);
                  })
                  ->orWhereHas('eligibleRoles', function ($q) use ($user) {
                      $q->whereIn('role_id', $user->roles->pluck('id'));
                  });
        })->active()->get();
    }

    /**
     * Get user's favorite convenios.
     */
    public function getUserFavorites(User $user): Collection
    {
        return $user->favoriteConvenios()->active()->get();
    }

    /**
     * Get user's convenio usage history.
     */
    public function getUserUsageHistory(User $user): Collection
    {
        return $this->model->whereHas('usageLogs', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['usageLogs' => function ($query) use ($user) {
            $query->where('user_id', $user->id)->orderBy('created_at', 'desc');
        }])->get();
    }

    /**
     * Get popular convenios.
     */
    public function getPopularConvenios(int $limit = 10): Collection
    {
        return $this->model->withCount('usageLogs')
            ->orderBy('usage_logs_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent convenios.
     */
    public function getRecentConvenios(int $days = 7, int $limit = 10): Collection
    {
        return $this->model->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get expiring convenios.
     */
    public function getExpiringConvenios(int $days = 30): Collection
    {
        return $this->model->where('valid_until', '<=', now()->addDays($days))
            ->where('valid_until', '>', now())
            ->where('status', 'active')
            ->orderBy('valid_until')
            ->get();
    }

    /**
     * Get expired convenios.
     */
    public function getExpiredConvenios(): Collection
    {
        return $this->model->where('valid_until', '<', now())
            ->where('status', 'active')
            ->get();
    }

    /**
     * Get convenios by discount range.
     */
    public function getConveniosByDiscountRange(float $minDiscount, float $maxDiscount): Collection
    {
        return $this->model->whereBetween('discount_percentage', [$minDiscount, $maxDiscount])
            ->active()
            ->get();
    }

    /**
     * Get convenios with highest discounts.
     */
    public function getHighestDiscountConvenios(int $limit = 10): Collection
    {
        return $this->model->active()
            ->orderBy('discount_percentage', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get convenios statistics.
     */
    public function getConvenioStatistics(Convenio $convenio): array
    {
        $totalUsage = $convenio->usageLogs()->count();
        $uniqueUsers = $convenio->usageLogs()->distinct('user_id')->count();
        $averageRating = $convenio->reviews()->avg('rating') ?? 0;
        $totalReviews = $convenio->reviews()->count();
        $totalFavorites = $convenio->favorites()->count();
        
        $usageByMonth = $convenio->usageLogs()
            ->selectRaw('DATE_TRUNC(\'month\', created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        return [
            'total_usage' => $totalUsage,
            'unique_users' => $uniqueUsers,
            'average_rating' => round($averageRating, 2),
            'total_reviews' => $totalReviews,
            'total_favorites' => $totalFavorites,
            'usage_by_month' => $usageByMonth,
            'is_popular' => $totalUsage > 100,
            'engagement_rate' => $uniqueUsers > 0 ? ($totalReviews / $uniqueUsers) * 100 : 0,
        ];
    }

    /**
     * Get convenios by date range.
     */
    public function getConveniosByDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate])->get();
    }

    /**
     * Get convenio statistics by period.
     */
    public function getConvenioStatsByPeriod(string $period = 'month'): array
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
     * Get usage statistics by period.
     */
    public function getUsageStatsByPeriod(string $period = 'month'): array
    {
        $dateFormat = match ($period) {
            'day' => 'Y-m-d',
            'week' => 'Y-W',
            'month' => 'Y-m',
            'year' => 'Y',
            default => 'Y-m',
        };

        return DB::table('convenio_usage_logs')
            ->selectRaw(
                "TO_CHAR(created_at, '{$dateFormat}') as period, COUNT(*) as count"
            )
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('count', 'period')
            ->toArray();
    }

    /**
     * Get convenios requiring attention.
     */
    public function getConveniosRequiringAttention(): Collection
    {
        return $this->model->where(function ($query) {
            $query->where('valid_until', '<', now()) // Expired
                  ->orWhere(function ($q) {
                      $q->where('status', 'active')
                        ->whereDoesntHave('usageLogs')
                        ->where('created_at', '<', now()->subDays(30)); // No usage after 30 days
                  })
                  ->orWhere(function ($q) {
                      $q->where('status', 'active')
                        ->whereHas('reviews', function ($r) {
                            $r->where('rating', '<=', 2);
                        })
                        ->whereDoesntHave('reviews', function ($r) {
                            $r->where('rating', '>', 2);
                        }); // Only bad reviews
                  });
        })->get();
    }

    /**
     * Get low-rated convenios.
     */
    public function getLowRatedConvenios(float $threshold = 3.0): Collection
    {
        return $this->model->whereHas('reviews')
            ->get()
            ->filter(function ($convenio) use ($threshold) {
                $averageRating = $convenio->reviews()->avg('rating');
                return $averageRating < $threshold;
            });
    }

    /**
     * Get unused convenios.
     */
    public function getUnusedConvenios(int $days = 30): Collection
    {
        return $this->model->whereDoesntHave('usageLogs', function ($query) use ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        })->where('status', 'active')->get();
    }

    /**
     * Check if user can use convenio.
     */
    public function canUserUseConvenio(Convenio $convenio, User $user): bool
    {
        // Check if convenio is active
        if ($convenio->status !== 'active') {
            return false;
        }

        // Check if convenio is valid
        if (!$convenio->isValid()) {
            return false;
        }

        // Check if convenio is available
        if (!$convenio->isAvailable()) {
            return false;
        }

        // Check if user is eligible
        if (!$convenio->is_public) {
            $isEligible = $convenio->eligibleUsers()->where('user_id', $user->id)->exists()
                || $convenio->eligibleRoles()->whereIn('role_id', $user->roles->pluck('id'))->exists();
            
            if (!$isEligible) {
                return false;
            }
        }

        // Check usage limits
        if ($convenio->usage_limit_per_user > 0) {
            $userUsageCount = $convenio->usageLogs()->where('user_id', $user->id)->count();
            if ($userUsageCount >= $convenio->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

    /**
     * Record convenio usage.
     */
    public function recordUsage(Convenio $convenio, User $user, array $metadata = []): bool
    {
        if (!$this->canUserUseConvenio($convenio, $user)) {
            return false;
        }

        $convenio->usageLogs()->create([
            'user_id' => $user->id,
            'used_at' => now(),
            'metadata' => $metadata,
        ]);

        // Update usage count
        $convenio->increment('usage_count');

        return true;
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
                    
                case 'category':
                    $query->where('category', $value);
                    break;
                    
                case 'type':
                    $query->where('type', $value);
                    break;
                    
                case 'city':
                    $query->where('city', $value);
                    break;
                    
                case 'state':
                    $query->where('state', $value);
                    break;
                    
                case 'is_featured':
                    $query->where('is_featured', $value === 'true' || $value === true);
                    break;
                    
                case 'is_public':
                    $query->where('is_public', $value === 'true' || $value === true);
                    break;
                    
                case 'min_discount':
                    $query->where('discount_percentage', '>=', $value);
                    break;
                    
                case 'max_discount':
                    $query->where('discount_percentage', '<=', $value);
                    break;
                    
                case 'valid_from':
                    $query->where('valid_from', '>=', $value);
                    break;
                    
                case 'valid_until':
                    $query->where('valid_until', '<=', $value);
                    break;
                    
                case 'created_from':
                    $query->where('created_at', '>=', $value);
                    break;
                    
                case 'created_to':
                    $query->where('created_at', '<=', $value);
                    break;
                    
                case 'near_location':
                    if (isset($value['latitude'], $value['longitude'])) {
                        $radius = $value['radius'] ?? 10;
                        $query->nearLocation($value['latitude'], $value['longitude'], $radius);
                    }
                    break;
                    
                case 'search':
                    $query->search($value);
                    break;
                    
                case 'tags':
                    if (is_array($value)) {
                        foreach ($value as $tag) {
                            $query->whereJsonContains('tags', $tag);
                        }
                    } else {
                        $query->whereJsonContains('tags', $value);
                    }
                    break;
                    
                case 'min_rating':
                    $query->whereHas('reviews', function ($q) use ($value) {
                        $q->havingRaw('AVG(rating) >= ?', [$value]);
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
                case 'title':
                case 'category':
                case 'type':
                case 'city':
                case 'state':
                case 'discount_percentage':
                case 'valid_from':
                case 'valid_until':
                case 'created_at':
                case 'updated_at':
                case 'usage_count':
                    $query->orderBy($field, $direction);
                    break;
                    
                case 'rating':
                    $query->leftJoin('convenio_reviews', 'convenios.id', '=', 'convenio_reviews.convenio_id')
                          ->groupBy('convenios.id')
                          ->orderByRaw('AVG(convenio_reviews.rating) ' . strtoupper($direction))
                          ->select('convenios.*');
                    break;
                    
                case 'popularity':
                    $query->withCount('usageLogs')->orderBy('usage_logs_count', $direction);
                    break;
                    
                case 'distance':
                    // This would require latitude/longitude parameters
                    // For now, we'll just order by city
                    $query->orderBy('city', $direction);
                    break;
            }
        }

        return $query;
    }

    /**
     * Bulk update convenios.
     */
    public function bulkUpdate(array $convenioIds, array $data): int
    {
        return $this->model->whereIn('id', $convenioIds)->update($data);
    }

    /**
     * Bulk delete convenios.
     */
    public function bulkDelete(array $convenioIds): int
    {
        return $this->model->whereIn('id', $convenioIds)->delete();
    }

    /**
     * Get convenio dashboard data.
     */
    public function getDashboardData(): array
    {
        return [
            'total_convenios' => $this->model->count(),
            'active_convenios' => $this->model->where('status', 'active')->count(),
            'featured_convenios' => $this->model->where('is_featured', true)->count(),
            'expired_convenios' => $this->getExpiredConvenios()->count(),
            'total_usage' => DB::table('convenio_usage_logs')->count(),
            'recent_convenios' => $this->getRecentConvenios(7, 5),
            'popular_convenios' => $this->getPopularConvenios(5),
            'expiring_soon' => $this->getExpiringConvenios(30),
            'requiring_attention' => $this->getConveniosRequiringAttention(),
        ];
    }

    /**
     * Get convenio export data.
     */
    public function getExportData(array $filters = []): Collection
    {
        $query = $this->model->with(['category', 'creator', 'reviews', 'usageLogs']);
        
        // Apply filters
        $query = $this->applyFilters($query, $filters);
        
        return $query->get();
    }

    /**
     * Archive old convenios.
     */
    public function archiveOldConvenios(int $daysOld = 365): int
    {
        return $this->model->where('status', 'inactive')
            ->where('updated_at', '<', now()->subDays($daysOld))
            ->update(['status' => 'archived']);
    }

    /**
     * Clean up expired convenios.
     */
    public function cleanupExpiredConvenios(): int
    {
        return $this->model->where('status', 'active')
            ->where('valid_until', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Get convenios by partner.
     */
    public function getConveniosByPartner(string $partnerName): Collection
    {
        return $this->model->where('partner_name', 'ILIKE', "%{$partnerName}%")->get();
    }

    /**
     * Get top partners by convenio count.
     */
    public function getTopPartners(int $limit = 10): array
    {
        return $this->model->select('partner_name')
            ->groupBy('partner_name')
            ->orderByRaw('COUNT(*) DESC')
            ->limit($limit)
            ->pluck('partner_name')
            ->toArray();
    }
}