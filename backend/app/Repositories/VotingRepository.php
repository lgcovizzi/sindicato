<?php

namespace App\Repositories;

use App\Models\Voting;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VotingRepository
{
    protected $model;

    public function __construct(Voting $model)
    {
        $this->model = $model;
    }

    /**
     * Get model instance.
     */
    public function getModel(): Voting
    {
        return $this->model;
    }

    /**
     * Find voting by ID.
     */
    public function find(int $id): ?Voting
    {
        return $this->model->find($id);
    }

    /**
     * Find voting by ID with relationships.
     */
    public function findWithRelations(int $id, array $relations = []): ?Voting
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Find voting by slug.
     */
    public function findBySlug(string $slug): ?Voting
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Create new voting.
     */
    public function create(array $data): Voting
    {
        return $this->model->create($data);
    }

    /**
     * Update voting.
     */
    public function update(Voting $voting, array $data): bool
    {
        return $voting->update($data);
    }

    /**
     * Delete voting.
     */
    public function delete(Voting $voting): bool
    {
        return $voting->delete();
    }

    /**
     * Get all votings with pagination.
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
     * Get all votings.
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
     * Get active votings.
     */
    public function getActiveVotings(): Collection
    {
        return $this->model->active()->get();
    }

    /**
     * Get upcoming votings.
     */
    public function getUpcomingVotings(): Collection
    {
        return $this->model->upcoming()->get();
    }

    /**
     * Get ongoing votings.
     */
    public function getOngoingVotings(): Collection
    {
        return $this->model->ongoing()->get();
    }

    /**
     * Get completed votings.
     */
    public function getCompletedVotings(): Collection
    {
        return $this->model->completed()->get();
    }

    /**
     * Get featured votings.
     */
    public function getFeaturedVotings(): Collection
    {
        return $this->model->featured()->get();
    }

    /**
     * Get urgent votings.
     */
    public function getUrgentVotings(): Collection
    {
        return $this->model->urgent()->get();
    }

    /**
     * Get votings by type.
     */
    public function getVotingsByType(string $type): Collection
    {
        return $this->model->where('type', $type)->get();
    }

    /**
     * Get votings by category.
     */
    public function getVotingsByCategory(string $category): Collection
    {
        return $this->model->where('category', $category)->get();
    }

    /**
     * Get votings created by user.
     */
    public function getVotingsByCreator(int $creatorId): Collection
    {
        return $this->model->where('created_by', $creatorId)->get();
    }

    /**
     * Get votings where user can vote.
     */
    public function getVotingsForUser(User $user): Collection
    {
        return $this->model->whereHas('eligibleUsers', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->orWhere('is_public', true)->get();
    }

    /**
     * Get votings where user has voted.
     */
    public function getVotingsUserVoted(User $user): Collection
    {
        return $this->model->whereHas('votes', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();
    }

    /**
     * Get votings where user hasn't voted yet.
     */
    public function getVotingsUserNotVoted(User $user): Collection
    {
        return $this->model->whereDoesntHave('votes', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('status', 'active')->get();
    }

    /**
     * Search votings.
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where(function ($q) use ($query) {
            $q->where('title', 'ILIKE', "%{$query}%")
              ->orWhere('description', 'ILIKE', "%{$query}%")
              ->orWhere('category', 'ILIKE', "%{$query}%")
              ->orWhere('tags', 'ILIKE', "%{$query}%");
        })->paginate($perPage);
    }

    /**
     * Get voting statistics.
     */
    public function getVotingStatistics(Voting $voting): array
    {
        $totalVotes = $voting->votes()->count();
        $totalEligible = $voting->eligibleUsers()->count();
        $participationRate = $totalEligible > 0 ? ($totalVotes / $totalEligible) * 100 : 0;
        
        $optionStats = $voting->options()->withCount('votes')->get()->map(function ($option) use ($totalVotes) {
            $voteCount = $option->votes_count;
            $percentage = $totalVotes > 0 ? ($voteCount / $totalVotes) * 100 : 0;
            
            return [
                'id' => $option->id,
                'title' => $option->title,
                'votes' => $voteCount,
                'percentage' => round($percentage, 2),
            ];
        });

        return [
            'total_votes' => $totalVotes,
            'total_eligible' => $totalEligible,
            'participation_rate' => round($participationRate, 2),
            'quorum_reached' => $voting->hasReachedQuorum(),
            'options' => $optionStats,
            'winner' => $voting->getWinner(),
            'is_tie' => $voting->isTie(),
            'status' => $voting->status,
            'time_remaining' => $voting->getTimeRemaining(),
        ];
    }

    /**
     * Get voting results.
     */
    public function getVotingResults(Voting $voting): array
    {
        $results = $voting->results()->with('option')->orderBy('votes_count', 'desc')->get();
        
        return $results->map(function ($result) {
            return [
                'option_id' => $result->option_id,
                'option_title' => $result->option->title,
                'votes_count' => $result->votes_count,
                'percentage' => $result->percentage,
                'is_winner' => $result->is_winner,
                'ranking_position' => $result->ranking_position,
            ];
        })->toArray();
    }

    /**
     * Get recent votings.
     */
    public function getRecentVotings(int $days = 7, int $limit = 10): Collection
    {
        return $this->model->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get popular votings.
     */
    public function getPopularVotings(int $limit = 10): Collection
    {
        return $this->model->withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get votings ending soon.
     */
    public function getVotingsEndingSoon(int $hours = 24): Collection
    {
        return $this->model->where('status', 'active')
            ->where('end_date', '<=', now()->addHours($hours))
            ->where('end_date', '>', now())
            ->orderBy('end_date')
            ->get();
    }

    /**
     * Get votings by date range.
     */
    public function getVotingsByDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate])->get();
    }

    /**
     * Get voting statistics by period.
     */
    public function getVotingStatsByPeriod(string $period = 'month'): array
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
     * Get participation statistics by period.
     */
    public function getParticipationStatsByPeriod(string $period = 'month'): array
    {
        $dateFormat = match ($period) {
            'day' => 'Y-m-d',
            'week' => 'Y-W',
            'month' => 'Y-m',
            'year' => 'Y',
            default => 'Y-m',
        };

        return DB::table('votes')
            ->join('votings', 'votes.voting_id', '=', 'votings.id')
            ->selectRaw(
                "TO_CHAR(votes.created_at, '{$dateFormat}') as period, COUNT(*) as count"
            )
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('count', 'period')
            ->toArray();
    }

    /**
     * Get votings requiring attention.
     */
    public function getVotingsRequiringAttention(): Collection
    {
        return $this->model->where(function ($query) {
            $query->where('status', 'active')
                  ->where('end_date', '<', now()) // Expired but not closed
                  ->orWhere(function ($q) {
                      $q->where('status', 'active')
                        ->whereRaw('(SELECT COUNT(*) FROM votes WHERE voting_id = votings.id) = 0')
                        ->where('created_at', '<', now()->subHours(24)); // No votes after 24h
                  });
        })->get();
    }

    /**
     * Get low participation votings.
     */
    public function getLowParticipationVotings(float $threshold = 20.0): Collection
    {
        return $this->model->whereHas('votes')
            ->get()
            ->filter(function ($voting) use ($threshold) {
                $stats = $this->getVotingStatistics($voting);
                return $stats['participation_rate'] < $threshold;
            });
    }

    /**
     * Get votings without quorum.
     */
    public function getVotingsWithoutQuorum(): Collection
    {
        return $this->model->where('status', 'completed')
            ->get()
            ->filter(function ($voting) {
                return !$voting->hasReachedQuorum();
            });
    }

    /**
     * Check if user can vote in voting.
     */
    public function canUserVote(Voting $voting, User $user): bool
    {
        // Check if voting is active
        if ($voting->status !== 'active') {
            return false;
        }

        // Check if user already voted
        if ($voting->votes()->where('user_id', $user->id)->exists()) {
            return false;
        }

        // Check if user is eligible
        if (!$voting->is_public) {
            return $voting->eligibleUsers()->where('user_id', $user->id)->exists();
        }

        return true;
    }

    /**
     * Get user's vote in voting.
     */
    public function getUserVote(Voting $voting, User $user)
    {
        return $voting->votes()->where('user_id', $user->id)->first();
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
                    
                case 'type':
                    $query->where('type', $value);
                    break;
                    
                case 'category':
                    $query->where('category', $value);
                    break;
                    
                case 'is_public':
                    $query->where('is_public', $value === 'true' || $value === true);
                    break;
                    
                case 'is_anonymous':
                    $query->where('is_anonymous', $value === 'true' || $value === true);
                    break;
                    
                case 'requires_biometric':
                    $query->where('requires_biometric', $value === 'true' || $value === true);
                    break;
                    
                case 'is_featured':
                    $query->where('is_featured', $value === 'true' || $value === true);
                    break;
                    
                case 'is_urgent':
                    $query->where('is_urgent', $value === 'true' || $value === true);
                    break;
                    
                case 'created_by':
                    $query->where('created_by', $value);
                    break;
                    
                case 'start_date_from':
                    $query->where('start_date', '>=', $value);
                    break;
                    
                case 'start_date_to':
                    $query->where('start_date', '<=', $value);
                    break;
                    
                case 'end_date_from':
                    $query->where('end_date', '>=', $value);
                    break;
                    
                case 'end_date_to':
                    $query->where('end_date', '<=', $value);
                    break;
                    
                case 'created_from':
                    $query->where('created_at', '>=', $value);
                    break;
                    
                case 'created_to':
                    $query->where('created_at', '<=', $value);
                    break;
                    
                case 'search':
                    $query->where(function ($q) use ($value) {
                        $q->where('title', 'ILIKE', "%{$value}%")
                          ->orWhere('description', 'ILIKE', "%{$value}%")
                          ->orWhere('category', 'ILIKE', "%{$value}%")
                          ->orWhere('tags', 'ILIKE', "%{$value}%");
                    });
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
                case 'status':
                case 'type':
                case 'category':
                case 'start_date':
                case 'end_date':
                case 'created_at':
                case 'updated_at':
                    $query->orderBy($field, $direction);
                    break;
                    
                case 'votes_count':
                    $query->withCount('votes')->orderBy('votes_count', $direction);
                    break;
                    
                case 'participation_rate':
                    // This would require a more complex query
                    // For now, we'll sort by votes count as a proxy
                    $query->withCount('votes')->orderBy('votes_count', $direction);
                    break;
            }
        }

        return $query;
    }

    /**
     * Bulk update votings.
     */
    public function bulkUpdate(array $votingIds, array $data): int
    {
        return $this->model->whereIn('id', $votingIds)->update($data);
    }

    /**
     * Bulk delete votings.
     */
    public function bulkDelete(array $votingIds): int
    {
        return $this->model->whereIn('id', $votingIds)->delete();
    }

    /**
     * Get voting dashboard data.
     */
    public function getDashboardData(): array
    {
        return [
            'total_votings' => $this->model->count(),
            'active_votings' => $this->model->where('status', 'active')->count(),
            'completed_votings' => $this->model->where('status', 'completed')->count(),
            'upcoming_votings' => $this->model->where('status', 'scheduled')->count(),
            'total_votes' => DB::table('votes')->count(),
            'recent_votings' => $this->getRecentVotings(7, 5),
            'popular_votings' => $this->getPopularVotings(5),
            'ending_soon' => $this->getVotingsEndingSoon(24),
            'requiring_attention' => $this->getVotingsRequiringAttention(),
        ];
    }

    /**
     * Get voting export data.
     */
    public function getExportData(array $filters = []): Collection
    {
        $query = $this->model->with(['creator', 'options.votes']);
        
        // Apply filters
        $query = $this->applyFilters($query, $filters);
        
        return $query->get();
    }

    /**
     * Archive old votings.
     */
    public function archiveOldVotings(int $daysOld = 365): int
    {
        return $this->model->where('status', 'completed')
            ->where('end_date', '<', now()->subDays($daysOld))
            ->update(['status' => 'archived']);
    }

    /**
     * Clean up expired votings.
     */
    public function cleanupExpiredVotings(): int
    {
        return $this->model->where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'completed']);
    }
}