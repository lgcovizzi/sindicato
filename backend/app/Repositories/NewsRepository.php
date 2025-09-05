<?php

namespace App\Repositories;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NewsRepository
{
    protected $model;

    public function __construct(News $model)
    {
        $this->model = $model;
    }

    /**
     * Get model instance.
     */
    public function getModel(): News
    {
        return $this->model;
    }

    /**
     * Find news by ID.
     */
    public function find(int $id): ?News
    {
        return $this->model->find($id);
    }

    /**
     * Find news by ID with relationships.
     */
    public function findWithRelations(int $id, array $relations = []): ?News
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Find news by slug.
     */
    public function findBySlug(string $slug): ?News
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Create new news.
     */
    public function create(array $data): News
    {
        return $this->model->create($data);
    }

    /**
     * Update news.
     */
    public function update(News $news, array $data): bool
    {
        return $news->update($data);
    }

    /**
     * Delete news.
     */
    public function delete(News $news): bool
    {
        return $news->delete();
    }

    /**
     * Get all news with pagination.
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
     * Get all news.
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
     * Get published news.
     */
    public function getPublishedNews(): Collection
    {
        return $this->model->published()->get();
    }

    /**
     * Get featured news.
     */
    public function getFeaturedNews(): Collection
    {
        return $this->model->featured()->get();
    }

    /**
     * Get breaking news.
     */
    public function getBreakingNews(): Collection
    {
        return $this->model->breaking()->get();
    }

    /**
     * Get pinned news.
     */
    public function getPinnedNews(): Collection
    {
        return $this->model->pinned()->get();
    }

    /**
     * Get news by category.
     */
    public function getNewsByCategory(string $category): Collection
    {
        return $this->model->byCategory($category)->get();
    }

    /**
     * Get news by author.
     */
    public function getNewsByAuthor(int $authorId): Collection
    {
        return $this->model->byAuthor($authorId)->get();
    }

    /**
     * Get news by priority.
     */
    public function getNewsByPriority(string $priority): Collection
    {
        return $this->model->byPriority($priority)->get();
    }

    /**
     * Get recent news.
     */
    public function getRecentNews(int $days = 7, int $limit = 10): Collection
    {
        return $this->model->recent($days)->limit($limit)->get();
    }

    /**
     * Get popular news.
     */
    public function getPopularNews(int $limit = 10): Collection
    {
        return $this->model->popular()->limit($limit)->get();
    }

    /**
     * Get news with tag.
     */
    public function getNewsWithTag(string $tag): Collection
    {
        return $this->model->withTag($tag)->get();
    }

    /**
     * Search news.
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->search($query)->paginate($perPage);
    }

    /**
     * Get news for homepage.
     */
    public function getHomepageNews(): array
    {
        return [
            'breaking' => $this->model->breaking()->limit(3)->get(),
            'featured' => $this->model->featured()->limit(5)->get(),
            'recent' => $this->model->published()->recent(7)->limit(10)->get(),
            'popular' => $this->model->popular()->limit(5)->get(),
        ];
    }

    /**
     * Get related news.
     */
    public function getRelatedNews(News $news, int $limit = 5): Collection
    {
        return $this->model->where('id', '!=', $news->id)
            ->where(function ($query) use ($news) {
                $query->where('category_id', $news->category_id)
                      ->orWhereHas('tags', function ($q) use ($news) {
                          $q->whereIn('name', $news->tags->pluck('name'));
                      });
            })
            ->published()
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending news.
     */
    public function getTrendingNews(int $days = 7, int $limit = 10): Collection
    {
        return $this->model->withCount(['views' => function ($query) use ($days) {
                $query->where('created_at', '>=', now()->subDays($days));
            }])
            ->published()
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get most commented news.
     */
    public function getMostCommentedNews(int $limit = 10): Collection
    {
        return $this->model->withCount('comments')
            ->published()
            ->orderBy('comments_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get most liked news.
     */
    public function getMostLikedNews(int $limit = 10): Collection
    {
        return $this->model->withCount('likes')
            ->published()
            ->orderBy('likes_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get most shared news.
     */
    public function getMostSharedNews(int $limit = 10): Collection
    {
        return $this->model->withCount('shares')
            ->published()
            ->orderBy('shares_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's bookmarked news.
     */
    public function getUserBookmarks(User $user): Collection
    {
        return $user->bookmarkedNews()->published()->orderBy('pivot.created_at', 'desc')->get();
    }

    /**
     * Get user's liked news.
     */
    public function getUserLikedNews(User $user): Collection
    {
        return $user->likedNews()->published()->orderBy('pivot.created_at', 'desc')->get();
    }

    /**
     * Get news statistics.
     */
    public function getNewsStatistics(News $news): array
    {
        $totalViews = $news->views()->count();
        $totalLikes = $news->likes()->count();
        $totalComments = $news->comments()->count();
        $totalShares = $news->shares()->count();
        $totalBookmarks = $news->bookmarks()->count();
        
        $viewsByDay = $news->views()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $engagementRate = $totalViews > 0 ? (($totalLikes + $totalComments + $totalShares) / $totalViews) * 100 : 0;

        return [
            'total_views' => $totalViews,
            'total_likes' => $totalLikes,
            'total_comments' => $totalComments,
            'total_shares' => $totalShares,
            'total_bookmarks' => $totalBookmarks,
            'views_by_day' => $viewsByDay,
            'engagement_rate' => round($engagementRate, 2),
            'reading_time' => $news->reading_time,
            'published_at' => $news->published_at,
            'last_updated' => $news->updated_at,
        ];
    }

    /**
     * Get news by date range.
     */
    public function getNewsByDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->model->whereBetween('published_at', [$startDate, $endDate])
            ->published()
            ->get();
    }

    /**
     * Get news statistics by period.
     */
    public function getNewsStatsByPeriod(string $period = 'month'): array
    {
        $dateFormat = match ($period) {
            'day' => 'Y-m-d',
            'week' => 'Y-W',
            'month' => 'Y-m',
            'year' => 'Y',
            default => 'Y-m',
        };

        return $this->model->selectRaw(
            "TO_CHAR(published_at, '{$dateFormat}') as period, COUNT(*) as count"
        )
        ->published()
        ->groupBy('period')
        ->orderBy('period')
        ->pluck('count', 'period')
        ->toArray();
    }

    /**
     * Get engagement statistics by period.
     */
    public function getEngagementStatsByPeriod(string $period = 'month'): array
    {
        $dateFormat = match ($period) {
            'day' => 'Y-m-d',
            'week' => 'Y-W',
            'month' => 'Y-m',
            'year' => 'Y',
            default => 'Y-m',
        };

        $views = DB::table('news_views')
            ->selectRaw("TO_CHAR(created_at, '{$dateFormat}') as period, COUNT(*) as count")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('count', 'period')
            ->toArray();

        $likes = DB::table('news_likes')
            ->selectRaw("TO_CHAR(created_at, '{$dateFormat}') as period, COUNT(*) as count")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('count', 'period')
            ->toArray();

        $comments = DB::table('news_comments')
            ->selectRaw("TO_CHAR(created_at, '{$dateFormat}') as period, COUNT(*) as count")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('count', 'period')
            ->toArray();

        return [
            'views' => $views,
            'likes' => $likes,
            'comments' => $comments,
        ];
    }

    /**
     * Get news requiring attention.
     */
    public function getNewsRequiringAttention(): Collection
    {
        return $this->model->where(function ($query) {
            $query->where('status', 'published')
                  ->whereDoesntHave('views')
                  ->where('published_at', '<', now()->subDays(7)) // No views after 7 days
                  ->orWhere(function ($q) {
                      $q->where('status', 'draft')
                        ->where('created_at', '<', now()->subDays(30)); // Draft for more than 30 days
                  })
                  ->orWhere(function ($q) {
                      $q->where('status', 'published')
                        ->whereHas('comments', function ($c) {
                            $c->where('is_approved', false);
                        }); // Has unapproved comments
                  });
        })->get();
    }

    /**
     * Get low-engagement news.
     */
    public function getLowEngagementNews(int $days = 30, float $threshold = 1.0): Collection
    {
        return $this->model->published()
            ->where('published_at', '>=', now()->subDays($days))
            ->get()
            ->filter(function ($news) use ($threshold) {
                $stats = $this->getNewsStatistics($news);
                return $stats['engagement_rate'] < $threshold;
            });
    }

    /**
     * Get outdated news.
     */
    public function getOutdatedNews(int $days = 365): Collection
    {
        return $this->model->published()
            ->where('published_at', '<', now()->subDays($days))
            ->where('is_evergreen', false)
            ->get();
    }

    /**
     * Increment news views.
     */
    public function incrementViews(News $news, User $user = null, array $metadata = []): void
    {
        $news->views()->create([
            'user_id' => $user?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);

        // Update view count cache
        $news->increment('view_count');
    }

    /**
     * Toggle news like.
     */
    public function toggleLike(News $news, User $user): bool
    {
        $existingLike = $news->likes()->where('user_id', $user->id)->first();
        
        if ($existingLike) {
            $existingLike->delete();
            $news->decrement('like_count');
            return false;
        } else {
            $news->likes()->create(['user_id' => $user->id]);
            $news->increment('like_count');
            return true;
        }
    }

    /**
     * Toggle news bookmark.
     */
    public function toggleBookmark(News $news, User $user): bool
    {
        $existingBookmark = $news->bookmarks()->where('user_id', $user->id)->first();
        
        if ($existingBookmark) {
            $existingBookmark->delete();
            return false;
        } else {
            $news->bookmarks()->create(['user_id' => $user->id]);
            return true;
        }
    }

    /**
     * Record news share.
     */
    public function recordShare(News $news, User $user = null, string $platform = 'unknown'): void
    {
        $news->shares()->create([
            'user_id' => $user?->id,
            'platform' => $platform,
            'ip_address' => request()->ip(),
        ]);

        $news->increment('share_count');
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
                    $query->where('category_id', $value);
                    break;
                    
                case 'author':
                    $query->where('author_id', $value);
                    break;
                    
                case 'priority':
                    $query->where('priority', $value);
                    break;
                    
                case 'is_featured':
                    $query->where('is_featured', $value === 'true' || $value === true);
                    break;
                    
                case 'is_breaking':
                    $query->where('is_breaking', $value === 'true' || $value === true);
                    break;
                    
                case 'is_pinned':
                    $query->where('is_pinned', $value === 'true' || $value === true);
                    break;
                    
                case 'is_evergreen':
                    $query->where('is_evergreen', $value === 'true' || $value === true);
                    break;
                    
                case 'published_from':
                    $query->where('published_at', '>=', $value);
                    break;
                    
                case 'published_to':
                    $query->where('published_at', '<=', $value);
                    break;
                    
                case 'created_from':
                    $query->where('created_at', '>=', $value);
                    break;
                    
                case 'created_to':
                    $query->where('created_at', '<=', $value);
                    break;
                    
                case 'search':
                    $query->search($value);
                    break;
                    
                case 'tags':
                    if (is_array($value)) {
                        $query->withAnyTag($value);
                    } else {
                        $query->withTag($value);
                    }
                    break;
                    
                case 'min_views':
                    $query->where('view_count', '>=', $value);
                    break;
                    
                case 'min_likes':
                    $query->where('like_count', '>=', $value);
                    break;
                    
                case 'min_comments':
                    $query->whereHas('comments', function ($q) use ($value) {
                        $q->havingRaw('COUNT(*) >= ?', [$value]);
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
            return $query->orderBy('published_at', 'desc');
        }

        foreach ($sorts as $field => $direction) {
            $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
            
            switch ($field) {
                case 'title':
                case 'status':
                case 'priority':
                case 'published_at':
                case 'created_at':
                case 'updated_at':
                case 'view_count':
                case 'like_count':
                case 'share_count':
                case 'reading_time':
                    $query->orderBy($field, $direction);
                    break;
                    
                case 'popularity':
                    $query->orderBy('view_count', $direction);
                    break;
                    
                case 'engagement':
                    $query->orderByRaw('(like_count + share_count + (SELECT COUNT(*) FROM news_comments WHERE news_id = news.id)) ' . strtoupper($direction));
                    break;
                    
                case 'comments_count':
                    $query->withCount('comments')->orderBy('comments_count', $direction);
                    break;
                    
                case 'category':
                    $query->join('news_categories', 'news.category_id', '=', 'news_categories.id')
                          ->orderBy('news_categories.name', $direction)
                          ->select('news.*');
                    break;
                    
                case 'author':
                    $query->join('users', 'news.author_id', '=', 'users.id')
                          ->orderBy('users.name', $direction)
                          ->select('news.*');
                    break;
            }
        }

        return $query;
    }

    /**
     * Bulk update news.
     */
    public function bulkUpdate(array $newsIds, array $data): int
    {
        return $this->model->whereIn('id', $newsIds)->update($data);
    }

    /**
     * Bulk delete news.
     */
    public function bulkDelete(array $newsIds): int
    {
        return $this->model->whereIn('id', $newsIds)->delete();
    }

    /**
     * Get news dashboard data.
     */
    public function getDashboardData(): array
    {
        return [
            'total_news' => $this->model->count(),
            'published_news' => $this->model->published()->count(),
            'draft_news' => $this->model->where('status', 'draft')->count(),
            'featured_news' => $this->model->featured()->count(),
            'breaking_news' => $this->model->breaking()->count(),
            'total_views' => DB::table('news_views')->count(),
            'total_likes' => DB::table('news_likes')->count(),
            'total_comments' => DB::table('news_comments')->count(),
            'recent_news' => $this->getRecentNews(7, 5),
            'popular_news' => $this->getPopularNews(5),
            'trending_news' => $this->getTrendingNews(7, 5),
            'requiring_attention' => $this->getNewsRequiringAttention(),
        ];
    }

    /**
     * Get news export data.
     */
    public function getExportData(array $filters = []): Collection
    {
        $query = $this->model->with(['category', 'author', 'editor', 'tags']);
        
        // Apply filters
        $query = $this->applyFilters($query, $filters);
        
        return $query->get();
    }

    /**
     * Archive old news.
     */
    public function archiveOldNews(int $daysOld = 365): int
    {
        return $this->model->where('status', 'published')
            ->where('is_evergreen', false)
            ->where('published_at', '<', now()->subDays($daysOld))
            ->update(['status' => 'archived']);
    }

    /**
     * Clean up draft news.
     */
    public function cleanupOldDrafts(int $daysOld = 90): int
    {
        return $this->model->where('status', 'draft')
            ->where('created_at', '<', now()->subDays($daysOld))
            ->whereDoesntHave('views')
            ->delete();
    }

    /**
     * Get news sitemap data.
     */
    public function getSitemapData(): Collection
    {
        return $this->model->published()
            ->select(['slug', 'updated_at', 'priority'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Get RSS feed data.
     */
    public function getRssFeedData(int $limit = 20): Collection
    {
        return $this->model->published()
            ->with(['category', 'author'])
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }
}