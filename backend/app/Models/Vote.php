<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;

class Vote extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'voting_id',
        'voting_option_id',
        'user_id',
        'vote_value',
        'is_anonymous',
        'ip_address',
        'user_agent',
        'biometric_hash',
        'device_fingerprint',
        'geolocation',
        'voted_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'voted_at' => 'datetime',
        'is_anonymous' => 'boolean',
        'geolocation' => 'array',
        'metadata' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'voted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'ip_address',
        'user_agent',
        'biometric_hash',
        'device_fingerprint',
        'user_id', // Hidden when voting is anonymous
    ];

    /**
     * Vote values constants.
     */
    const VALUE_YES = 'yes';
    const VALUE_NO = 'no';
    const VALUE_ABSTENTION = 'abstention';
    const VALUE_RANKED = 'ranked'; // For ranked voting
    const VALUE_APPROVAL = 'approval'; // For approval voting

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['voting_id', 'voting_option_id', 'vote_value', 'voted_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('voting_activity');
    }

    /**
     * Relationship: Voting this vote belongs to.
     */
    public function voting()
    {
        return $this->belongsTo(Voting::class);
    }

    /**
     * Relationship: Voting option selected.
     */
    public function option()
    {
        return $this->belongsTo(VotingOption::class, 'voting_option_id');
    }

    /**
     * Relationship: User who cast the vote.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: By voting.
     */
    public function scopeByVoting($query, $votingId)
    {
        return $query->where('voting_id', $votingId);
    }

    /**
     * Scope: By user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: By option.
     */
    public function scopeByOption($query, $optionId)
    {
        return $query->where('voting_option_id', $optionId);
    }

    /**
     * Scope: Anonymous votes.
     */
    public function scopeAnonymous($query)
    {
        return $query->where('is_anonymous', true);
    }

    /**
     * Scope: Non-anonymous votes.
     */
    public function scopeIdentified($query)
    {
        return $query->where('is_anonymous', false);
    }

    /**
     * Scope: By vote value.
     */
    public function scopeByValue($query, $value)
    {
        return $query->where('vote_value', $value);
    }

    /**
     * Scope: Recent votes.
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('voted_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope: Today's votes.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('voted_at', today());
    }

    /**
     * Get formatted vote time.
     */
    public function getFormattedVoteTimeAttribute()
    {
        return $this->voted_at->format('d/m/Y H:i:s');
    }

    /**
     * Get time since vote.
     */
    public function getTimeSinceVoteAttribute()
    {
        return $this->voted_at->diffForHumans();
    }

    /**
     * Check if vote is valid.
     */
    public function isValid()
    {
        // Check if voting is still active
        if (!$this->voting->isActive()) {
            return false;
        }

        // Check if user is eligible
        if (!$this->voting->isUserEligible($this->user)) {
            return false;
        }

        // Check if user hasn't exceeded vote limit
        $userVotes = static::where('voting_id', $this->voting_id)
            ->where('user_id', $this->user_id)
            ->count();

        if ($userVotes > $this->voting->max_votes_per_user) {
            return false;
        }

        return true;
    }

    /**
     * Verify biometric authentication.
     */
    public function verifyBiometric($biometricData)
    {
        if (!$this->biometric_hash) {
            return false;
        }

        // Implement biometric verification logic
        $hashedData = hash('sha256', $biometricData . config('app.key'));
        return hash_equals($this->biometric_hash, $hashedData);
    }

    /**
     * Get vote location if available.
     */
    public function getLocationAttribute()
    {
        if (!$this->geolocation) {
            return null;
        }

        return [
            'latitude' => $this->geolocation['lat'] ?? null,
            'longitude' => $this->geolocation['lng'] ?? null,
            'address' => $this->geolocation['address'] ?? null,
        ];
    }

    /**
     * Check if vote was cast from mobile device.
     */
    public function isMobileVote()
    {
        if (!$this->user_agent) {
            return false;
        }

        $mobileKeywords = ['Mobile', 'Android', 'iPhone', 'iPad', 'Windows Phone'];
        foreach ($mobileKeywords as $keyword) {
            if (strpos($this->user_agent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get device type.
     */
    public function getDeviceTypeAttribute()
    {
        if ($this->isMobileVote()) {
            return 'mobile';
        }

        if ($this->user_agent && strpos($this->user_agent, 'Tablet') !== false) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Anonymize vote data.
     */
    public function anonymize()
    {
        $this->update([
            'is_anonymous' => true,
            'ip_address' => null,
            'user_agent' => null,
            'device_fingerprint' => null,
            'geolocation' => null,
        ]);

        return $this;
    }

    /**
     * Boot method to set default values.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vote) {
            if (!$vote->voted_at) {
                $vote->voted_at = now();
            }

            // Set IP address from request if available
            if (!$vote->ip_address && request()) {
                $vote->ip_address = request()->ip();
            }

            // Set user agent from request if available
            if (!$vote->user_agent && request()) {
                $vote->user_agent = request()->userAgent();
            }
        });

        static::created(function ($vote) {
            // Broadcast vote cast event
            broadcast(new \App\Events\VoteCast($vote));

            // Update voting results in real-time
            $vote->voting->calculateResults();
        });
    }
}