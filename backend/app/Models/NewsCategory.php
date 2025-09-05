<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;

class NewsCategory extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'parent_id',
        'sort_order',
        'is_active',
        'is_featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'news_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'news_count' => 'integer',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $appends = [
        'icon_url',
        'full_name',
        'breadcrumb',
        'children_count',
        'active_news_count',
    ];

    // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'slug',
                'parent_id',
                'is_active',
                'is_featured',
                'sort_order',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Media Collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('icon')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']);

        $this->addMediaCollection('banner')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(64)
            ->height(64)
            ->sharpen(10)
            ->optimize()
            ->performOnCollections('icon', 'banner');

        $this->addMediaConversion('medium')
            ->width(200)
            ->height(200)
            ->sharpen(10)
            ->optimize()
            ->performOnCollections('banner');
    }

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(NewsCategory::class, 'parent_id')
                    ->orderBy('sort_order')
                    ->orderBy('name');
    }

    public function activeChildren(): HasMany
    {
        return $this->children()->where('is_active', true);
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'category_id');
    }

    public function publishedNews(): HasMany
    {
        return $this->news()->published();
    }

    public function featuredNews(): HasMany
    {
        return $this->publishedNews()->featured();
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeParent(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeChild(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')
                    ->orderBy('name');
    }

    public function scopeWithNewsCount(Builder $query): Builder
    {
        return $query->withCount(['publishedNews as active_news_count']);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ILIKE', "%{$search}%")
              ->orWhere('description', 'ILIKE', "%{$search}%")
              ->orWhere('meta_keywords', 'ILIKE', "%{$search}%");
        });
    }

    // Accessors
    public function getIconUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('icon');
        return $media ? $media->getUrl() : null;
    }

    public function getFullNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->name . ' > ' . $this->name;
        }
        
        return $this->name;
    }

    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = [];
        $category = $this;
        
        while ($category) {
            array_unshift($breadcrumb, [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]);
            $category = $category->parent;
        }
        
        return $breadcrumb;
    }

    public function getChildrenCountAttribute(): int
    {
        return $this->children()->count();
    }

    public function getActiveNewsCountAttribute(): int
    {
        return $this->publishedNews()->count();
    }

    // Mutators
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $value;
        
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    public function setSlugAttribute($value): void
    {
        $this->attributes['slug'] = Str::slug($value);
    }

    // Methods
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    public function isChild(): bool
    {
        return !is_null($this->parent_id);
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function hasActiveChildren(): bool
    {
        return $this->activeChildren()->exists();
    }

    public function hasNews(): bool
    {
        return $this->news()->exists();
    }

    public function hasPublishedNews(): bool
    {
        return $this->publishedNews()->exists();
    }

    public function getDescendants(): \Illuminate\Database\Eloquent\Collection
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }
        
        return $descendants;
    }

    public function getAncestors(): \Illuminate\Database\Eloquent\Collection
    {
        $ancestors = collect();
        $category = $this->parent;
        
        while ($category) {
            $ancestors->push($category);
            $category = $category->parent;
        }
        
        return $ancestors->reverse();
    }

    public function getAllNews(): \Illuminate\Database\Eloquent\Collection
    {
        $newsIds = collect([$this->id]);
        
        // Include news from all descendants
        $descendants = $this->getDescendants();
        $newsIds = $newsIds->merge($descendants->pluck('id'));
        
        return News::whereIn('category_id', $newsIds)->get();
    }

    public function getAllPublishedNews(): \Illuminate\Database\Eloquent\Collection
    {
        $newsIds = collect([$this->id]);
        
        // Include news from all descendants
        $descendants = $this->getDescendants();
        $newsIds = $newsIds->merge($descendants->pluck('id'));
        
        return News::published()->whereIn('category_id', $newsIds)->get();
    }

    public function updateNewsCount(): void
    {
        $this->update([
            'news_count' => $this->publishedNews()->count(),
        ]);
    }

    public function canBeDeleted(): bool
    {
        // Cannot delete if has children or news
        return !$this->hasChildren() && !$this->hasNews();
    }

    public function getNextSortOrder(): int
    {
        $maxOrder = static::where('parent_id', $this->parent_id)
                         ->max('sort_order');
        
        return ($maxOrder ?? 0) + 1;
    }

    public function moveUp(): bool
    {
        $previousCategory = static::where('parent_id', $this->parent_id)
                                 ->where('sort_order', '<', $this->sort_order)
                                 ->orderByDesc('sort_order')
                                 ->first();
        
        if ($previousCategory) {
            $tempOrder = $this->sort_order;
            $this->update(['sort_order' => $previousCategory->sort_order]);
            $previousCategory->update(['sort_order' => $tempOrder]);
            
            return true;
        }
        
        return false;
    }

    public function moveDown(): bool
    {
        $nextCategory = static::where('parent_id', $this->parent_id)
                             ->where('sort_order', '>', $this->sort_order)
                             ->orderBy('sort_order')
                             ->first();
        
        if ($nextCategory) {
            $tempOrder = $this->sort_order;
            $this->update(['sort_order' => $nextCategory->sort_order]);
            $nextCategory->update(['sort_order' => $tempOrder]);
            
            return true;
        }
        
        return false;
    }

    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        // Deactivate all children as well
        $this->children()->update(['is_active' => false]);
        
        return $this->update(['is_active' => false]);
    }

    public function toggleFeatured(): bool
    {
        return $this->update(['is_featured' => !$this->is_featured]);
    }

    public function getTreeStructure(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'color' => $this->color,
            'icon_url' => $this->icon_url,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'news_count' => $this->active_news_count,
            'children' => $this->activeChildren->map(function ($child) {
                return $child->getTreeStructure();
            })->toArray(),
        ];
    }

    public static function getTree(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
                    ->parent()
                    ->ordered()
                    ->with(['activeChildren' => function ($query) {
                        $query->ordered();
                    }])
                    ->get();
    }

    public static function getFeaturedCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
                    ->featured()
                    ->withNewsCount()
                    ->ordered()
                    ->get();
    }

    public static function getCategoriesWithNews(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
                    ->has('publishedNews')
                    ->withNewsCount()
                    ->ordered()
                    ->get();
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            
            if (empty($category->sort_order)) {
                $category->sort_order = $category->getNextSortOrder();
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::deleted(function ($category) {
            // Update news count for parent category
            if ($category->parent) {
                $category->parent->updateNewsCount();
            }
        });
    }
}