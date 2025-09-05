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
use Carbon\Carbon;

class Convenio extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, LogsActivity, InteractsWithMedia, HasSlug;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'company_name',
        'company_cnpj',
        'company_contact',
        'category_id',
        'discount_percentage',
        'discount_description',
        'terms_conditions',
        'how_to_use',
        'valid_from',
        'valid_until',
        'is_active',
        'is_featured',
        'is_online',
        'website_url',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'postal_code',
        'latitude',
        'longitude',
        'qr_code_data',
        'usage_limit',
        'usage_count',
        'minimum_purchase',
        'maximum_discount',
        'applicable_days',
        'applicable_hours',
        'target_audience',
        'requirements',
        'exclusions',
        'metadata',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'approved_at' => 'datetime',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_online' => 'boolean',
        'discount_percentage' => 'decimal:2',
        'minimum_purchase' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'applicable_days' => 'array',
        'applicable_hours' => 'array',
        'target_audience' => 'array',
        'requirements' => 'array',
        'exclusions' => 'array',
        'metadata' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'valid_from',
        'valid_until',
        'approved_at',
    ];

    /**
     * Convenio categories constants.
     */
    const CATEGORY_HEALTH = 'health';
    const CATEGORY_EDUCATION = 'education';
    const CATEGORY_LEISURE = 'leisure';
    const CATEGORY_FOOD = 'food';
    const CATEGORY_SHOPPING = 'shopping';
    const CATEGORY_SERVICES = 'services';
    const CATEGORY_AUTOMOTIVE = 'automotive';
    const CATEGORY_TECHNOLOGY = 'technology';
    const CATEGORY_TRAVEL = 'travel';
    const CATEGORY_FINANCE = 'finance';

    /**
     * Target audience constants.
     */
    const AUDIENCE_ALL = 'all';
    const AUDIENCE_MEMBERS = 'members';
    const AUDIENCE_DEPENDENTS = 'dependents';
    const AUDIENCE_RETIREES = 'retirees';

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
                'title', 'company_name', 'discount_percentage',
                'is_active', 'is_featured', 'valid_from', 'valid_until'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Media collections configuration.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();

        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);

        $this->addMediaCollection('qr_codes')
            ->acceptsMimeTypes(['image/png', 'image/svg+xml'])
            ->singleFile();
    }

    /**
     * Relationship: Category.
     */
    public function category()
    {
        return $this->belongsTo(ConvenioCategory::class, 'category_id');
    }

    /**
     * Relationship: Creator.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Approver.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relationship: Usage logs.
     */
    public function usageLogs()
    {
        return $this->hasMany(ConvenioUsage::class);
    }

    /**
     * Relationship: Reviews.
     */
    public function reviews()
    {
        return $this->hasMany(ConvenioReview::class);
    }

    /**
     * Relationship: Favorites.
     */
    public function favorites()
    {
        return $this->hasMany(ConvenioFavorite::class);
    }

    /**
     * Scope: Active convenios.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', now());
            });
    }

    /**
     * Scope: Featured convenios.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Online convenios.
     */
    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    /**
     * Scope: Physical convenios.
     */
    public function scopePhysical($query)
    {
        return $query->where('is_online', false)
            ->whereNotNull('address');
    }

    /**
     * Scope: By category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: By city.
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', 'like', '%' . $city . '%');
    }

    /**
     * Scope: By state.
     */
    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    /**
     * Scope: Near location.
     */
    public function scopeNearLocation($query, $latitude, $longitude, $radiusKm = 10)
    {
        return $query->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw(
                '*, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->having('distance', '<', $radiusKm)
            ->orderBy('distance');
    }

    /**
     * Scope: Search by text.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%')
              ->orWhere('description', 'like', '%' . $search . '%')
              ->orWhere('company_name', 'like', '%' . $search . '%')
              ->orWhere('city', 'like', '%' . $search . '%');
        });
    }

    /**
     * Check if convenio is currently valid.
     */
    public function isValid()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->valid_from && $this->valid_from > $now) {
            return false;
        }

        if ($this->valid_until && $this->valid_until < $now) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if convenio is available today.
     */
    public function isAvailableToday()
    {
        if (!$this->isValid()) {
            return false;
        }

        if (!$this->applicable_days) {
            return true;
        }

        $today = strtolower(now()->format('l')); // monday, tuesday, etc.
        return in_array($today, array_map('strtolower', $this->applicable_days));
    }

    /**
     * Check if convenio is available now.
     */
    public function isAvailableNow()
    {
        if (!$this->isAvailableToday()) {
            return false;
        }

        if (!$this->applicable_hours) {
            return true;
        }

        $currentTime = now()->format('H:i');
        $startTime = $this->applicable_hours['start'] ?? '00:00';
        $endTime = $this->applicable_hours['end'] ?? '23:59';

        return $currentTime >= $startTime && $currentTime <= $endTime;
    }

    /**
     * Get logo URL.
     */
    public function getLogoUrlAttribute()
    {
        $media = $this->getFirstMedia('logo');
        return $media ? $media->getUrl() : null;
    }

    /**
     * Get QR code URL.
     */
    public function getQrCodeUrlAttribute()
    {
        $media = $this->getFirstMedia('qr_codes');
        return $media ? $media->getUrl() : null;
    }

    /**
     * Get formatted discount.
     */
    public function getFormattedDiscountAttribute()
    {
        if ($this->discount_percentage) {
            return $this->discount_percentage . '% de desconto';
        }
        return $this->discount_description ?: 'Desconto especial';
    }

    /**
     * Get full address.
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Get distance from user location.
     */
    public function getDistanceFromUser($userLatitude, $userLongitude)
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        $earthRadius = 6371; // km

        $latDelta = deg2rad($this->latitude - $userLatitude);
        $lonDelta = deg2rad($this->longitude - $userLongitude);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($userLatitude)) * cos(deg2rad($this->latitude)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    /**
     * Get average rating.
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    /**
     * Get reviews count.
     */
    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Get usage statistics.
     */
    public function getUsageStatsAttribute()
    {
        return [
            'total_usage' => $this->usage_count,
            'monthly_usage' => $this->usageLogs()
                ->whereMonth('created_at', now()->month)
                ->count(),
            'weekly_usage' => $this->usageLogs()
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'daily_usage' => $this->usageLogs()
                ->whereDate('created_at', today())
                ->count(),
        ];
    }

    /**
     * Check if user can use this convenio.
     */
    public function canBeUsedBy(User $user)
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check target audience
        if ($this->target_audience && !in_array(self::AUDIENCE_ALL, $this->target_audience)) {
            $userType = $user->getUserType(); // Implement this method in User model
            if (!in_array($userType, $this->target_audience)) {
                return false;
            }
        }

        // Check requirements
        if ($this->requirements) {
            foreach ($this->requirements as $requirement) {
                if (!$user->meetsRequirement($requirement)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Generate QR code for convenio.
     */
    public function generateQrCode()
    {
        $qrData = [
            'convenio_id' => $this->id,
            'company_name' => $this->company_name,
            'discount' => $this->formatted_discount,
            'valid_until' => $this->valid_until?->format('Y-m-d'),
            'generated_at' => now()->toISOString(),
        ];

        $this->update(['qr_code_data' => json_encode($qrData)]);

        // Generate QR code image and store in media collection
        // Implementation depends on QR code library
        
        return $qrData;
    }

    /**
     * Record usage.
     */
    public function recordUsage(User $user, $metadata = [])
    {
        $this->usageLogs()->create([
            'user_id' => $user->id,
            'used_at' => now(),
            'metadata' => $metadata,
        ]);

        $this->increment('usage_count');

        return $this;
    }

    /**
     * Boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($convenio) {
            if (!$convenio->valid_from) {
                $convenio->valid_from = now();
            }
        });
    }
}