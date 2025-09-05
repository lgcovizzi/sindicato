<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Voting;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voting>
 */
class VotingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Voting::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('now', '+7 days');
        $endsAt = fake()->dateTimeBetween($startsAt, $startsAt->format('Y-m-d H:i:s') . ' +30 days');

        return [
            'title' => fake()->sentence(6),
            'description' => fake()->paragraphs(3, true),
            'type' => fake()->randomElement(['simple', 'multiple', 'ranked', 'approval']),
            'status' => 'scheduled',
            'options' => $this->generateVotingOptions(),
            'settings' => $this->generateVotingSettings(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'quorum_required' => fake()->boolean(70),
            'quorum_percentage' => fake()->numberBetween(30, 80),
            'allow_abstention' => fake()->boolean(60),
            'allow_justification' => fake()->boolean(40),
            'is_secret' => fake()->boolean(30),
            'is_anonymous' => fake()->boolean(20),
            'requires_biometric' => fake()->boolean(50),
            'max_votes_per_user' => 1,
            'voting_method' => fake()->randomElement(['single_choice', 'multiple_choice', 'ranked_choice']),
            'access_groups' => null,
            'access_roles' => null,
            'exclude_users' => null,
            'results' => null,
            'total_votes' => 0,
            'total_participants' => 0,
            'quorum_reached_at' => null,
            'created_by' => User::factory(),
            'approved_by' => null,
            'approved_at' => null,
            'published_at' => null,
            'archived_at' => null,
        ];
    }

    /**
     * Indicate that the voting should be active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'starts_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'ends_at' => fake()->dateTimeBetween('now', '+30 days'),
            'published_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the voting should be finished.
     */
    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'finished',
            'starts_at' => fake()->dateTimeBetween('-30 days', '-7 days'),
            'ends_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'published_at' => fake()->dateTimeBetween('-30 days', '-7 days'),
            'total_votes' => fake()->numberBetween(50, 500),
            'total_participants' => fake()->numberBetween(40, 450),
            'results' => $this->generateResults(),
        ]);
    }

    /**
     * Indicate that the voting should be published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the voting should be approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => User::factory(),
            'approved_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the voting should require quorum.
     */
    public function withQuorum(int $percentage = 50): static
    {
        return $this->state(fn (array $attributes) => [
            'quorum_required' => true,
            'quorum_percentage' => $percentage,
        ]);
    }

    /**
     * Indicate that the voting should be secret.
     */
    public function secret(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_secret' => true,
            'is_anonymous' => true,
        ]);
    }

    /**
     * Indicate that the voting should require biometric verification.
     */
    public function withBiometric(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_biometric' => true,
        ]);
    }

    /**
     * Indicate that the voting should allow multiple choices.
     */
    public function multipleChoice(int $maxVotes = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'multiple',
            'voting_method' => 'multiple_choice',
            'max_votes_per_user' => $maxVotes,
        ]);
    }

    /**
     * Indicate that the voting should be ranked choice.
     */
    public function rankedChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'ranked',
            'voting_method' => 'ranked_choice',
        ]);
    }

    /**
     * Indicate that the voting should have specific access groups.
     */
    public function withAccessGroups(array $groups): static
    {
        return $this->state(fn (array $attributes) => [
            'access_groups' => $groups,
        ]);
    }

    /**
     * Indicate that the voting should have specific access roles.
     */
    public function withAccessRoles(array $roles): static
    {
        return $this->state(fn (array $attributes) => [
            'access_roles' => $roles,
        ]);
    }

    /**
     * Indicate that the voting should exclude specific users.
     */
    public function excludingUsers(array $userIds): static
    {
        return $this->state(fn (array $attributes) => [
            'exclude_users' => $userIds,
        ]);
    }

    /**
     * Indicate that the voting should have reached quorum.
     */
    public function quorumReached(): static
    {
        return $this->state(fn (array $attributes) => [
            'quorum_required' => true,
            'quorum_reached_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Generate voting options.
     */
    private function generateVotingOptions(): array
    {
        $optionCount = fake()->numberBetween(2, 5);
        $options = [];

        for ($i = 1; $i <= $optionCount; $i++) {
            $options[] = [
                'id' => 'option_' . $i,
                'text' => fake()->sentence(3),
                'description' => fake()->sentence(8),
                'order' => $i,
            ];
        }

        return $options;
    }

    /**
     * Generate voting settings.
     */
    private function generateVotingSettings(): array
    {
        return [
            'show_results_during_voting' => fake()->boolean(30),
            'show_participant_count' => fake()->boolean(70),
            'allow_vote_change' => fake()->boolean(40),
            'send_notifications' => fake()->boolean(80),
            'auto_close_on_quorum' => fake()->boolean(50),
            'require_justification_for_abstention' => fake()->boolean(30),
            'minimum_participation_time' => fake()->numberBetween(30, 300), // seconds
            'maximum_session_time' => fake()->numberBetween(600, 3600), // seconds
        ];
    }

    /**
     * Generate voting results.
     */
    private function generateResults(): array
    {
        $options = $this->generateVotingOptions();
        $totalVotes = fake()->numberBetween(50, 500);
        $results = [];

        $remainingVotes = $totalVotes;
        foreach ($options as $index => $option) {
            $votes = $index === count($options) - 1 
                ? $remainingVotes 
                : fake()->numberBetween(0, $remainingVotes);
            
            $results[] = [
                'option_id' => $option['id'],
                'option_text' => $option['text'],
                'votes' => $votes,
                'percentage' => $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 2) : 0,
            ];
            
            $remainingVotes -= $votes;
        }

        return $results;
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Voting $voting) {
            // Auto-approve if created by admin
            if ($voting->creator && $voting->creator->hasRole('admin')) {
                $voting->update([
                    'approved_by' => $voting->created_by,
                    'approved_at' => now(),
                ]);
            }
        });
    }
}