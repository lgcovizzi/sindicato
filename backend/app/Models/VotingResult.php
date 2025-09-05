<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class VotingResult extends Model
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
        'votes_count',
        'percentage',
        'ranking_position',
        'is_winner',
        'margin_of_victory',
        'statistical_data',
        'calculated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'votes_count' => 'integer',
        'percentage' => 'decimal:2',
        'ranking_position' => 'integer',
        'is_winner' => 'boolean',
        'margin_of_victory' => 'decimal:2',
        'statistical_data' => 'array',
        'calculated_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'calculated_at',
    ];

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'votes_count', 'percentage', 'ranking_position', 
                'is_winner', 'calculated_at'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('voting_results');
    }

    /**
     * Relationship: Voting this result belongs to.
     */
    public function voting()
    {
        return $this->belongsTo(Voting::class);
    }

    /**
     * Relationship: Voting option this result is for.
     */
    public function option()
    {
        return $this->belongsTo(VotingOption::class, 'voting_option_id');
    }

    /**
     * Scope: Winners only.
     */
    public function scopeWinners($query)
    {
        return $query->where('is_winner', true);
    }

    /**
     * Scope: By voting.
     */
    public function scopeByVoting($query, $votingId)
    {
        return $query->where('voting_id', $votingId);
    }

    /**
     * Scope: Ordered by ranking.
     */
    public function scopeByRanking($query)
    {
        return $query->orderBy('ranking_position');
    }

    /**
     * Scope: Ordered by votes count.
     */
    public function scopeByVotesCount($query, $direction = 'desc')
    {
        return $query->orderBy('votes_count', $direction);
    }

    /**
     * Scope: Ordered by percentage.
     */
    public function scopeByPercentage($query, $direction = 'desc')
    {
        return $query->orderBy('percentage', $direction);
    }

    /**
     * Get formatted percentage.
     */
    public function getFormattedPercentageAttribute()
    {
        return number_format($this->percentage, 2) . '%';
    }

    /**
     * Get formatted votes count.
     */
    public function getFormattedVotesCountAttribute()
    {
        return number_format($this->votes_count);
    }

    /**
     * Get result status text.
     */
    public function getStatusTextAttribute()
    {
        if ($this->is_winner) {
            return 'Vencedor';
        }

        switch ($this->ranking_position) {
            case 1:
                return '1º Lugar';
            case 2:
                return '2º Lugar';
            case 3:
                return '3º Lugar';
            default:
                return $this->ranking_position . 'º Lugar';
        }
    }

    /**
     * Get result color based on position.
     */
    public function getResultColorAttribute()
    {
        if ($this->is_winner) {
            return '#28a745'; // Green for winner
        }

        switch ($this->ranking_position) {
            case 1:
                return '#ffd700'; // Gold
            case 2:
                return '#c0c0c0'; // Silver
            case 3:
                return '#cd7f32'; // Bronze
            default:
                return '#6c757d'; // Gray
        }
    }

    /**
     * Check if result is statistically significant.
     */
    public function isStatisticallySignificant()
    {
        // Implement statistical significance calculation
        $totalVotes = $this->voting->total_votes;
        $marginOfError = $this->calculateMarginOfError($totalVotes);
        
        return $this->margin_of_victory > $marginOfError;
    }

    /**
     * Calculate margin of error.
     */
    protected function calculateMarginOfError($totalVotes, $confidenceLevel = 95)
    {
        if ($totalVotes < 30) {
            return null; // Sample too small for reliable calculation
        }

        // Simplified margin of error calculation
        // For 95% confidence level, z-score = 1.96
        $zScore = $confidenceLevel === 95 ? 1.96 : 2.58; // 99% = 2.58
        $proportion = $this->percentage / 100;
        
        $marginOfError = $zScore * sqrt(($proportion * (1 - $proportion)) / $totalVotes);
        
        return round($marginOfError * 100, 2); // Convert to percentage
    }

    /**
     * Get confidence interval.
     */
    public function getConfidenceInterval($confidenceLevel = 95)
    {
        $marginOfError = $this->calculateMarginOfError(
            $this->voting->total_votes, 
            $confidenceLevel
        );

        if (!$marginOfError) {
            return null;
        }

        return [
            'lower' => max(0, $this->percentage - $marginOfError),
            'upper' => min(100, $this->percentage + $marginOfError),
            'margin_of_error' => $marginOfError,
            'confidence_level' => $confidenceLevel,
        ];
    }

    /**
     * Get statistical summary.
     */
    public function getStatisticalSummaryAttribute()
    {
        $totalVotes = $this->voting->total_votes;
        $eligibleVoters = $this->voting->eligible_voters_count;
        
        return [
            'votes_count' => $this->votes_count,
            'percentage' => $this->percentage,
            'total_votes' => $totalVotes,
            'eligible_voters' => $eligibleVoters,
            'participation_rate' => $eligibleVoters > 0 ? 
                round(($totalVotes / $eligibleVoters) * 100, 2) : 0,
            'margin_of_error' => $this->calculateMarginOfError($totalVotes),
            'confidence_interval_95' => $this->getConfidenceInterval(95),
            'is_statistically_significant' => $this->isStatisticallySignificant(),
        ];
    }

    /**
     * Compare with another result.
     */
    public function compareWith(VotingResult $other)
    {
        return [
            'vote_difference' => $this->votes_count - $other->votes_count,
            'percentage_difference' => $this->percentage - $other->percentage,
            'ranking_difference' => $other->ranking_position - $this->ranking_position,
            'is_ahead' => $this->votes_count > $other->votes_count,
            'margin' => abs($this->percentage - $other->percentage),
        ];
    }

    /**
     * Get trend data if available.
     */
    public function getTrendDataAttribute()
    {
        if (!$this->statistical_data || !isset($this->statistical_data['trend'])) {
            return null;
        }

        return $this->statistical_data['trend'];
    }

    /**
     * Check if result shows upward trend.
     */
    public function hasUpwardTrend()
    {
        $trend = $this->trend_data;
        if (!$trend || count($trend) < 2) {
            return false;
        }

        $recent = array_slice($trend, -3); // Last 3 data points
        return $recent[count($recent) - 1] > $recent[0];
    }

    /**
     * Export result data.
     */
    public function toExport()
    {
        return [
            'option_title' => $this->option->title,
            'votes_count' => $this->votes_count,
            'percentage' => $this->percentage,
            'ranking_position' => $this->ranking_position,
            'is_winner' => $this->is_winner,
            'margin_of_victory' => $this->margin_of_victory,
            'calculated_at' => $this->calculated_at->format('Y-m-d H:i:s'),
            'statistical_summary' => $this->statistical_summary,
        ];
    }

    /**
     * Boot method to set default values.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($result) {
            if (!$result->calculated_at) {
                $result->calculated_at = now();
            }
        });

        static::saved(function ($result) {
            // Broadcast result update event
            broadcast(new \App\Events\VotingResultsUpdated($result->voting));
        });
    }
}