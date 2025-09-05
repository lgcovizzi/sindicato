<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SystemNotification extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, LogsActivity, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'message',
        'type',
        'priority',
        'category',
        'target_audience',
        'departments',
        'positions',
        'user_ids',
        'scheduled_at',
        'expires_at',
        'is_active',
        'is_urgent',
        'requires_action',
        'action_url',
        'action_text',
        'icon',
        'color',
        'sound',
        'vibration_pattern',
        'metadata',
        'sent_count',
        'read_count',
        'click_count',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'departments' => 'array',
        'positions' => 'array',
        'user_ids' => 'array',
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_urgent' => 'boolean',
        'requires_action' => 'boolean',
        'vibration_pattern' => 'array',
        'metadata' => 'array',
        'sent_count' => 'integer',
        'read_count' => 'integer',
        'click_count' => 'integer',
    ];

    /**
     * Notification types.
     */
    const TYPE_INFO = 'info';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_SYSTEM = 'system';
    const TYPE_VOTING = 'voting';
    const TYPE_NEWS = 'news';
    const TYPE_CONVENIO = 'convenio';
    const TYPE_MAINTENANCE = 'maintenance';

    /**
     * Priority levels.
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Target audiences.
     */
    const AUDIENCE_ALL = 'all';
    const AUDIENCE_DEPARTMENTS = 'departments';
    const AUDIENCE_POSITIONS = 'positions';
    const AUDIENCE_SPECIFIC = 'specific';

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title', 'type', 'priority', 'target_audience',
                'is_active', 'is_urgent', 'scheduled_at'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);

        $this->addMediaCollection('icons')
            ->singleFile()
            ->acceptsMimeTypes(['image/svg+xml', 'image/png', 'image/webp']);
    }

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(64)
            ->height(64)
            ->performOnCollections('attachments', 'icons');
    }

    /**
     * Relationship: User who created this notification.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Notification logs.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * Scope: Active notifications.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: Scheduled notifications.
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now());
    }

    /**
     * Scope: Urgent notifications.
     */
    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    /**
     * Scope: By type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: By priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: For user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('target_audience', self::AUDIENCE_ALL)
              ->orWhere(function ($subQ) use ($user) {
                  $subQ->where('target_audience', self::AUDIENCE_DEPARTMENTS)
                       ->whereJsonContains('departments', $user->department);
              })
              ->orWhere(function ($subQ) use ($user) {
                  $subQ->where('target_audience', self::AUDIENCE_POSITIONS)
                       ->whereJsonContains('positions', $user->position);
              })
              ->orWhere(function ($subQ) use ($user) {
                  $subQ->where('target_audience', self::AUDIENCE_SPECIFIC)
                       ->whereJsonContains('user_ids', $user->id);
              });
        });
    }

    /**
     * Check if notification is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if notification is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->scheduled_at && $this->scheduled_at->isFuture();
    }

    /**
     * Check if notification should be sent now.
     */
    public function shouldSendNow(): bool
    {
        if (!$this->is_active || $this->isExpired()) {
            return false;
        }

        if ($this->isScheduled()) {
            return false;
        }

        return true;
    }

    /**
     * Get notification icon URL.
     */
    public function getIconUrlAttribute(): ?string
    {
        $icon = $this->getFirstMedia('icons');
        return $icon ? $icon->getUrl() : null;
    }

    /**
     * Increment sent count.
     */
    public function incrementSentCount(int $count = 1): void
    {
        $this->increment('sent_count', $count);
    }

    /**
     * Increment read count.
     */
    public function incrementReadCount(int $count = 1): void
    {
        $this->increment('read_count', $count);
    }

    /**
     * Increment click count.
     */
    public function incrementClickCount(int $count = 1): void
    {
        $this->increment('click_count', $count);
    }

    /**
     * Get delivery rate percentage.
     */
    public function getDeliveryRateAttribute(): float
    {
        if ($this->sent_count === 0) {
            return 0;
        }

        return round(($this->read_count / $this->sent_count) * 100, 2);
    }

    /**
     * Get click-through rate percentage.
     */
    public function getClickThroughRateAttribute(): float
    {
        if ($this->read_count === 0) {
            return 0;
        }

        return round(($this->click_count / $this->read_count) * 100, 2);
    }
}