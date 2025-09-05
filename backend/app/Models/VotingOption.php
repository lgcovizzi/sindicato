<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class VotingOption extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, LogsActivity, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'voting_id',
        'title',
        'description',
        'order',
        'color',
        'is_active',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'description', 'order', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Media collections configuration.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf']);
    }

    /**
     * Relationship: Voting that this option belongs to.
     */
    public function voting()
    {
        return $this->belongsTo(Voting::class);
    }

    /**
     * Relationship: Votes for this option.
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Relationship: Results for this option.
     */
    public function results()
    {
        return $this->hasMany(VotingResult::class);
    }

    /**
     * Scope: Active options.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Ordered by position.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get votes count for this option.
     */
    public function getVotesCountAttribute()
    {
        return $this->votes()->count();
    }

    /**
     * Get percentage of votes for this option.
     */
    public function getPercentageAttribute()
    {
        $totalVotes = $this->voting->total_votes;
        if ($totalVotes === 0) {
            return 0;
        }
        return round(($this->votes_count / $totalVotes) * 100, 2);
    }

    /**
     * Get option image URL.
     */
    public function getImageUrlAttribute()
    {
        $media = $this->getFirstMedia('images');
        return $media ? $media->getUrl() : null;
    }

    /**
     * Get option color or default.
     */
    public function getDisplayColorAttribute()
    {
        return $this->color ?: '#007bff';
    }

    /**
     * Check if option is winning.
     */
    public function isWinning()
    {
        $maxVotes = $this->voting->options()
            ->withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->first()
            ->votes_count ?? 0;

        return $this->votes_count === $maxVotes && $maxVotes > 0;
    }

    /**
     * Get ranking position of this option.
     */
    public function getRankingPosition()
    {
        $options = $this->voting->options()
            ->withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->get();

        $position = 1;
        foreach ($options as $index => $option) {
            if ($option->id === $this->id) {
                return $position;
            }
            if ($index + 1 < $options->count() && 
                $option->votes_count > $options[$index + 1]->votes_count) {
                $position = $index + 2;
            }
        }

        return $position;
    }

    /**
     * Boot method to set default order.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($option) {
            if (!$option->order) {
                $maxOrder = static::where('voting_id', $option->voting_id)
                    ->max('order') ?? 0;
                $option->order = $maxOrder + 1;
            }
        });
    }
}