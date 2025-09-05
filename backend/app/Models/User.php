<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, HasMedia, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    use HasRoles, LogsActivity, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'rg',
        'phone',
        'birth_date',
        'gender',
        'address',
        'city',
        'state',
        'zip_code',
        'registration_number',
        'admission_date',
        'department',
        'position',
        'salary',
        'is_active',
        'is_verified',
        'email_verified_at',
        'phone_verified_at',
        'biometric_verified_at',
        'last_login_at',
        'preferences',
        'metadata',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'user_id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'roles' => $this->roles->pluck('name'),
            'permissions' => $this->getAllPermissions()->pluck('name'),
        ];
    }

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'email', 'cpf', 'phone', 'department', 
                'position', 'is_active', 'is_verified'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);
    }

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->performOnCollections('avatar');

        $this->addMediaConversion('preview')
            ->width(500)
            ->height(500)
            ->performOnCollections('avatar', 'documents');
    }

    /**
     * Get the user's biometric data.
     */
    public function biometricData(): HasMany
    {
        return $this->hasMany(BiometricData::class);
    }

    /**
     * Get the user's biometric verification logs.
     */
    public function biometricVerificationLogs(): HasMany
    {
        return $this->hasMany(BiometricVerificationLog::class);
    }

    /**
     * Check if user has biometric verification.
     */
    public function hasBiometricVerification(): bool
    {
        return !is_null($this->biometric_verified_at) && 
               $this->biometricData()->where('is_active', true)->exists();
    }

    /**
     * Get user's avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $avatar = $this->getFirstMedia('avatar');
        return $avatar ? $avatar->getUrl('thumb') : null;
    }

    /**
     * Scope to filter active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter verified users.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter users with biometric verification.
     */
    public function scopeWithBiometric($query)
    {
        return $query->whereNotNull('biometric_verified_at');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'biometric_data',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'biometric_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'birth_date' => 'date',
        'admission_date' => 'date',
        'salary' => 'decimal:2',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'preferences' => 'array',
        'metadata' => 'array',
        'password' => 'hashed',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'email_verified_at',
        'birth_date',
        'admission_date',
        'last_login_at',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'user_id' => $this->id,
            'email' => $this->email,
            'roles' => $this->roles->pluck('name'),
            'permissions' => $this->getAllPermissions()->pluck('name'),
        ];
    }

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'email', 'status', 'department', 'position',
                'biometric_enabled', 'two_factor_enabled'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Media collections configuration.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
    }

    /**
     * Media conversions configuration.
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->performOnCollections('avatar');

        $this->addMediaConversion('medium')
            ->width(300)
            ->height(300)
            ->performOnCollections('avatar');
    }

    /**
     * Relationship: User preferences.
     */
    public function preferences()
    {
        return $this->hasMany(UserPreference::class);
    }

    /**
     * Relationship: User votes.
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Relationship: User notifications.
     */
    public function notifications()
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Relationship: User sessions.
     */
    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * Relationship: Biometric data.
     */
    public function biometricData()
    {
        return $this->hasMany(BiometricData::class);
    }

    /**
     * Scope: Active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Users by department.
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope: Users with biometric enabled.
     */
    public function scopeBiometricEnabled($query)
    {
        return $query->where('biometric_enabled', true);
    }

    /**
     * Get user's full name.
     */
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    /**
     * Get user's avatar URL.
     */
    public function getAvatarUrlAttribute()
    {
        $avatar = $this->getFirstMedia('avatar');
        return $avatar ? $avatar->getUrl('medium') : asset('images/default-avatar.png');
    }

    /**
     * Get user's avatar thumbnail URL.
     */
    public function getAvatarThumbUrlAttribute()
    {
        $avatar = $this->getFirstMedia('avatar');
        return $avatar ? $avatar->getUrl('thumb') : asset('images/default-avatar.png');
    }

    /**
     * Check if user can vote in a specific voting.
     */
    public function canVoteIn(Voting $voting)
    {
        // Check if user is eligible based on department, position, etc.
        if ($voting->departments && !in_array($this->department, $voting->departments)) {
            return false;
        }

        // Check if user already voted
        if ($this->votes()->where('voting_id', $voting->id)->exists()) {
            return false;
        }

        // Check if voting is active
        if (!$voting->isActive()) {
            return false;
        }

        return true;
    }

    /**
     * Get user's notification preference for a specific type.
     */
    public function getNotificationPreference($type)
    {
        $preferences = $this->notification_preferences ?? [];
        return $preferences[$type] ?? true;
    }

    /**
     * Update user's last login timestamp.
     */
    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Check if user has biometric authentication enabled.
     */
    public function hasBiometricEnabled()
    {
        return $this->biometric_enabled && $this->biometricData()->exists();
    }

    /**
     * Get user's active sessions count.
     */
    public function getActiveSessionsCount()
    {
        return $this->sessions()
            ->where('expires_at', '>', now())
            ->count();
    }
}