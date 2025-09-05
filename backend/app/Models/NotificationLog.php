<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'notifiable_type',
        'notifiable_id',
        'notification_type',
        'channel',
        'title',
        'message',
        'data',
        'status',
        'sent_at',
        'read_at',
        'clicked_at',
        'failed_at',
        'error_message',
        'attempts',
        'device_token',
        'platform',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'clicked_at' => 'datetime',
        'failed_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Notification channels.
     */
    const CHANNEL_DATABASE = 'database';
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_SMS = 'sms';
    const CHANNEL_PUSH = 'push';
    const CHANNEL_WEBSOCKET = 'websocket';
    const CHANNEL_SLACK = 'slack';

    /**
     * Notification statuses.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_CLICKED = 'clicked';
    const STATUS_FAILED = 'failed';
    const STATUS_BOUNCED = 'bounced';

    /**
     * Platforms.
     */
    const PLATFORM_WEB = 'web';
    const PLATFORM_ANDROID = 'android';
    const PLATFORM_IOS = 'ios';
    const PLATFORM_DESKTOP = 'desktop';

    /**
     * Relationship: User who received the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Notifiable entity (polymorphic).
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: By channel.
     */
    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope: By status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: By platform.
     */
    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope: Sent notifications.
     */
    public function scopeSent($query)
    {
        return $query->whereIn('status', [self::STATUS_SENT, self::STATUS_DELIVERED, self::STATUS_READ, self::STATUS_CLICKED]);
    }

    /**
     * Scope: Failed notifications.
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', [self::STATUS_FAILED, self::STATUS_BOUNCED]);
    }

    /**
     * Scope: Read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereIn('status', [self::STATUS_READ, self::STATUS_CLICKED]);
    }

    /**
     * Scope: Clicked notifications.
     */
    public function scopeClicked($query)
    {
        return $query->where('status', self::STATUS_CLICKED);
    }

    /**
     * Scope: Recent notifications.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Today's notifications.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Mark notification as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark notification as delivered.
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'status' => self::STATUS_READ,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark notification as clicked.
     */
    public function markAsClicked(): void
    {
        $this->update([
            'status' => self::STATUS_CLICKED,
            'clicked_at' => now(),
        ]);
    }

    /**
     * Mark notification as failed.
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Increment attempts count.
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    /**
     * Check if notification was successful.
     */
    public function wasSuccessful(): bool
    {
        return in_array($this->status, [
            self::STATUS_SENT,
            self::STATUS_DELIVERED,
            self::STATUS_READ,
            self::STATUS_CLICKED,
        ]);
    }

    /**
     * Check if notification failed.
     */
    public function hasFailed(): bool
    {
        return in_array($this->status, [
            self::STATUS_FAILED,
            self::STATUS_BOUNCED,
        ]);
    }

    /**
     * Check if notification was read.
     */
    public function wasRead(): bool
    {
        return in_array($this->status, [
            self::STATUS_READ,
            self::STATUS_CLICKED,
        ]);
    }

    /**
     * Check if notification was clicked.
     */
    public function wasClicked(): bool
    {
        return $this->status === self::STATUS_CLICKED;
    }

    /**
     * Get delivery time in seconds.
     */
    public function getDeliveryTimeAttribute(): ?int
    {
        if (!$this->sent_at || !$this->read_at) {
            return null;
        }

        return $this->read_at->diffInSeconds($this->sent_at);
    }

    /**
     * Get formatted delivery time.
     */
    public function getFormattedDeliveryTimeAttribute(): ?string
    {
        $deliveryTime = $this->delivery_time;
        
        if (!$deliveryTime) {
            return null;
        }

        if ($deliveryTime < 60) {
            return $deliveryTime . 's';
        }

        if ($deliveryTime < 3600) {
            return round($deliveryTime / 60, 1) . 'm';
        }

        return round($deliveryTime / 3600, 1) . 'h';
    }
}