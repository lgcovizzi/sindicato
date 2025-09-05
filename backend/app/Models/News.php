<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Tags\HasTags;
use Carbon\Carbon;

class News extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, LogsActivity, InteractsWithMedia, HasSlug, HasTags;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category_id',
        'status',
        'priority',
        'is_featured',
        'is_breaking',
        'is_pinned',
        'allow_comments',
        'views_count',
        'likes_count',
        'shares_count',
        'reading_time',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'published_at',
        'expires_at',
        'author_id',
        'editor_id',
        'approved_by',
        'approved_at',
        'source_url',
        'source_name',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_breaking' => 'boolean',
        'is_pinned' => 'boolean',
        'allow_comments' => 'boolean',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'shares_count' => 'integer',
        'reading_time' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'published_at',
        'expires_at',
        'approved_at',
    ];

    /**
     * News status constants.
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_REVIEW = 'pending_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_REJECTED = 'rejected';

    /**
     * Priority constants.
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title', 'status', 'priority', 'is_featured',
                'is_breaking', 'published_at', 'approved_by'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Media collections configuration.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

        $this->addMediaCollection('videos')
            ->acceptsMimeTypes(['video/mp4', 'video/webm', 'video/ogg']);
    }

    /**
     * Relationship: Category.
     */
    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    /**
     * Relationship: Author.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Relationship: Editor.
     */
    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    /**
     * Relationship: Approver.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relationship: Comments.
     */
    public function comments()
    {
        return $this->hasMany(NewsComment::class)->whereNull('parent_id');
    }

    /**
     * Relationship: All comments (including replies).
     */
    public function allComments()
    {
        return $this->hasMany(NewsComment::class);
    }

    /**
     * Relationship: Likes.
     */
    public function likes()
    {
        return $this->hasMany(NewsLike::class);
    }

    /**
     * Relationship: Shares.
     */
    public function shares()
    {
        return $this->hasMany(NewsShare::class);
    }

    /**
     * Relationship: Views.
     */
    public function views()
    {
        return $this->hasMany(NewsView::class);
    }

    /**
     * Relationship: Bookmarks.
     */
    public function bookmarks()
    {
        return $this->hasMany(NewsBookmark::class);
    }

    /**
     * Scope: Published news.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: Featured news.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Breaking news.
     */
    public function scopeBreaking($query)
    {
        return $query->where('is_breaking', true);
    }

    /**
     * Scope: Pinned news.
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope: By category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: By author.
     */
    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    /**
     * Scope: By priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Recent news.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('published_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Popular news.
     */
    public function scopePopular($query, $days = 30)
    {
        return $query->where('published_at', '>=', now()->subDays($days))
            ->orderBy('views_count', 'desc')
            ->orderBy('likes_count', 'desc');
    }

    /**
     * Scope: Search by text.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%')
              ->orWhere('excerpt', 'like', '%' . $search . '%')
              ->orWhere('content', 'like', '%' . $search . '%')
              ->orWhere('seo_keywords', 'like', '%' . $search . '%');
        });
    }

    /**
     * Scope: With tag.
     */
    public function scopeWithTag($query, $tag)
    {
        return $query->withAnyTags([$tag]);
    }

    /**
     * Check if news is published.
     */
    public function isPublished()
    {
        return $this->status === self::STATUS_PUBLISHED &&
               $this->published_at <= now() &&
               ($this->expires_at === null || $this->expires_at > now());
    }

    /**
     * Check if news is expired.
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Check if news is draft.
     */
    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if news is pending review.
     */
    public function isPendingReview()
    {
        return $this->status === self::STATUS_PENDING_REVIEW;
    }

    /**
     * Get featured image URL.
     */
    public function getFeaturedImageUrlAttribute()
    {
        $media = $this->getFirstMedia('featured_image');
        return $media ? $media->getUrl() : null;
    }

    /**
     * Get featured image thumbnail URL.
     */
    public function getFeaturedImageThumbnailAttribute()
    {
        $media = $this->getFirstMedia('featured_image');
        return $media ? $media->getUrl('thumb') : null;
    }

    /**
     * Get excerpt or generate from content.
     */
    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // Generate excerpt from content
        $content = strip_tags($this->content);
        return str_limit($content, 200);
    }

    /**
     * Get reading time in minutes.
     */
    public function getReadingTimeAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // Calculate reading time (average 200 words per minute)
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / 200));
    }

    /**
     * Get formatted published date.
     */
    public function getFormattedPublishedDateAttribute()
    {
        return $this->published_at ? $this->published_at->format('d/m/Y H:i') : null;
    }

    /**
     * Get time since publication.
     */
    public function getTimeSincePublicationAttribute()
    {
        return $this->published_at ? $this->published_at->diffForHumans() : null;
    }

    /**
     * Get SEO title or fallback to title.
     */
    public function getSeoTitleAttribute($value)
    {
        return $value ?: $this->title;
    }

    /**
     * Get SEO description or fallback to excerpt.
     */
    public function getSeoDescriptionAttribute($value)
    {
        return $value ?: $this->excerpt;
    }

    /**
     * Get engagement statistics.
     */
    public function getEngagementStatsAttribute()
    {
        return [
            'views' => $this->views_count,
            'likes' => $this->likes_count,
            'shares' => $this->shares_count,
            'comments' => $this->allComments()->count(),
            'bookmarks' => $this->bookmarks()->count(),
            'engagement_rate' => $this->calculateEngagementRate(),
        ];
    }

    /**
     * Calculate engagement rate.
     */
    protected function calculateEngagementRate()
    {
        if ($this->views_count === 0) {
            return 0;
        }

        $totalEngagements = $this->likes_count + $this->shares_count + 
                           $this->allComments()->count() + $this->bookmarks()->count();
        
        return round(($totalEngagements / $this->views_count) * 100, 2);
    }

    /**
     * Increment views count.
     */
    public function incrementViews(User $user = null, $ipAddress = null)
    {
        // Record view
        $this->views()->create([
            'user_id' => $user?->id,
            'ip_address' => $ipAddress ?: request()->ip(),
            'user_agent' => request()->userAgent(),
            'viewed_at' => now(),
        ]);

        // Update counter
        $this->increment('views_count');

        return $this;
    }

    /**
     * Toggle like for user.
     */
    public function toggleLike(User $user)
    {
        $like = $this->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $this->decrement('likes_count');
            return false; // Unliked
        } else {
            $this->likes()->create(['user_id' => $user->id]);
            $this->increment('likes_count');
            return true; // Liked
        }
    }

    /**
     * Record share.
     */
    public function recordShare(User $user, $platform = null, $url = null)
    {
        $this->shares()->create([
            'user_id' => $user->id,
            'platform' => $platform,
            'shared_url' => $url,
            'shared_at' => now(),
        ]);

        $this->increment('shares_count');

        return $this;
    }

    /**
     * Publish news.
     */
    public function publish(User $approver = null)
    {
        $this->update([
            'status' => self::STATUS_PUBLISHED,
            'published_at' => now(),
            'approved_by' => $approver?->id,
            'approved_at' => now(),
        ]);

        // Broadcast news published event
        broadcast(new \App\Events\NewsPublished($this));

        return $this;
    }

    /**
     * Archive news.
     */
    public function archive()
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
        return $this;
    }

    /**
     * Get related news.
     */
    public function getRelatedNews($limit = 5)
    {
        return static::published()
            ->where('id', '!=', $this->id)
            ->where(function ($query) {
                $query->where('category_id', $this->category_id)
                      ->orWhereHas('tags', function ($q) {
                          $q->whereIn('name', $this->tags->pluck('name'));
                      });
            })
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Generate social media preview.
     */
    public function getSocialPreviewAttribute()
    {
        return [
            'title' => $this->seo_title,
            'description' => $this->seo_description,
            'image' => $this->featured_image_url,
            'url' => route('news.show', $this->slug),
            'author' => $this->author->name,
            'published_at' => $this->published_at->toISOString(),
        ];
    }

    /**
     * Boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($news) {
            if (!$news->author_id && auth()->check()) {
                $news->author_id = auth()->id();
            }
        });

        static::updating(function ($news) {
            if ($news->isDirty('content')) {
                // Recalculate reading time when content changes
                $wordCount = str_word_count(strip_tags($news->content));
                $news->reading_time = max(1, ceil($wordCount / 200));
            }
        });
    }
}