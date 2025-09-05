<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class UserPreference extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'theme',
        'language',
        'timezone',
        'density',
        'notifications_enabled',
        'email_notifications',
        'push_notifications',
        'sms_notifications',
        'voting_notifications',
        'news_notifications',
        'convenio_notifications',
        'system_notifications',
        'sound_enabled',
        'vibration_enabled',
        'auto_sync',
        'offline_mode',
        'biometric_login',
        'two_factor_enabled',
        'session_timeout',
        'preferences_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'notifications_enabled' => 'boolean',
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'voting_notifications' => 'boolean',
        'news_notifications' => 'boolean',
        'convenio_notifications' => 'boolean',
        'system_notifications' => 'boolean',
        'sound_enabled' => 'boolean',
        'vibration_enabled' => 'boolean',
        'auto_sync' => 'boolean',
        'offline_mode' => 'boolean',
        'biometric_login' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'session_timeout' => 'integer',
        'preferences_data' => 'array',
    ];

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'theme', 'language', 'notifications_enabled',
                'biometric_login', 'two_factor_enabled'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationship: User that owns this preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Filter by theme.
     */
    public function scopeByTheme($query, $theme)
    {
        return $query->where('theme', $theme);
    }

    /**
     * Scope: Filter by language.
     */
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope: Users with notifications enabled.
     */
    public function scopeNotificationsEnabled($query)
    {
        return $query->where('notifications_enabled', true);
    }

    /**
     * Get default preferences for a user.
     */
    public static function getDefaults(): array
    {
        return [
            'theme' => 'system',
            'language' => 'pt_BR',
            'timezone' => 'America/Sao_Paulo',
            'density' => 'normal',
            'notifications_enabled' => true,
            'email_notifications' => true,
            'push_notifications' => true,
            'sms_notifications' => false,
            'voting_notifications' => true,
            'news_notifications' => true,
            'convenio_notifications' => true,
            'system_notifications' => true,
            'sound_enabled' => true,
            'vibration_enabled' => true,
            'auto_sync' => true,
            'offline_mode' => false,
            'biometric_login' => false,
            'two_factor_enabled' => false,
            'session_timeout' => 120,
            'preferences_data' => [],
        ];
    }

    /**
     * Check if user has specific notification type enabled.
     */
    public function hasNotificationEnabled(string $type): bool
    {
        if (!$this->notifications_enabled) {
            return false;
        }

        $field = $type . '_notifications';
        return $this->$field ?? false;
    }

    /**
     * Update specific preference.
     */
    public function updatePreference(string $key, $value): bool
    {
        if (in_array($key, $this->fillable)) {
            return $this->update([$key => $value]);
        }

        return false;
    }

    /**
     * Get preference value with fallback to default.
     */
    public function getPreference(string $key, $default = null)
    {
        $defaults = self::getDefaults();
        return $this->$key ?? $defaults[$key] ?? $default;
    }
}