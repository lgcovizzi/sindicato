<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BiometricData extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'template_hash',
        'device_id',
        'device_type',
        'quality_score',
        'is_active',
        'is_primary',
        'metadata',
        'last_used_at',
        'usage_count',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quality_score' => 'decimal:2',
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
        'metadata' => 'array',
        'last_used_at' => 'datetime',
        'usage_count' => 'integer',
        'expires_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'template_hash',
    ];

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'user_id', 'type', 'device_id', 'is_active', 
                'is_primary', 'quality_score'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user that owns the biometric data.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the verification logs for this biometric data.
     */
    public function verificationLogs(): HasMany
    {
        return $this->hasMany(BiometricVerificationLog::class);
    }

    /**
     * Check if the biometric data is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the biometric data is valid for use.
     */
    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Update the last used timestamp and increment usage count.
     */
    public function markAsUsed(): void
    {
        $this->update([
            'last_used_at' => now(),
            'usage_count' => $this->usage_count + 1,
        ]);
    }

    /**
     * Set this biometric data as primary for the user.
     */
    public function setAsPrimary(): void
    {
        // Remove primary flag from other biometric data of the same type
        static::where('user_id', $this->user_id)
            ->where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Set this as primary
        $this->update(['is_primary' => true]);
    }

    /**
     * Scope to filter active biometric data.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter primary biometric data.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to filter by biometric type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter non-expired biometric data.
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to filter valid biometric data.
     */
    public function scopeValid($query)
    {
        return $query->active()->notExpired();
    }
}