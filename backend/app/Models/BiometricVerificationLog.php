<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BiometricVerificationLog extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'biometric_data_id',
        'type',
        'action',
        'success',
        'confidence_score',
        'device_id',
        'device_type',
        'ip_address',
        'user_agent',
        'failure_reason',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'success' => 'boolean',
        'confidence_score' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'user_id', 'type', 'action', 'success', 
                'confidence_score', 'device_id'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user that owns the verification log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the biometric data associated with this verification.
     */
    public function biometricData(): BelongsTo
    {
        return $this->belongsTo(BiometricData::class);
    }

    /**
     * Check if the verification was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Check if the verification failed.
     */
    public function isFailed(): bool
    {
        return !$this->success;
    }

    /**
     * Get the confidence level description.
     */
    public function getConfidenceLevelAttribute(): string
    {
        if (!$this->confidence_score) {
            return 'unknown';
        }

        if ($this->confidence_score >= 0.9) {
            return 'very_high';
        } elseif ($this->confidence_score >= 0.8) {
            return 'high';
        } elseif ($this->confidence_score >= 0.7) {
            return 'medium';
        } elseif ($this->confidence_score >= 0.6) {
            return 'low';
        } else {
            return 'very_low';
        }
    }

    /**
     * Scope to filter successful verifications.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope to filter failed verifications.
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope to filter by verification type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by action.
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by device.
     */
    public function scopeFromDevice($query, string $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    /**
     * Scope to filter by confidence score range.
     */
    public function scopeWithConfidence($query, float $min, float $max = 1.0)
    {
        return $query->whereBetween('confidence_score', [$min, $max]);
    }

    /**
     * Scope to filter recent verifications.
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}