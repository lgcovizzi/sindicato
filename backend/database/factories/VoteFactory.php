<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Vote;
use App\Models\Voting;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Vote::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'voting_id' => Voting::factory(),
            'user_id' => User::factory(),
            'selected_options' => $this->generateSelectedOptions(),
            'justification' => fake()->boolean(30) ? fake()->sentence(10) : null,
            'is_abstention' => fake()->boolean(10),
            'verification_method' => fake()->randomElement(['password', 'biometric', 'sms', 'email']),
            'verification_data' => $this->generateVerificationData(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'location_data' => $this->generateLocationData(),
            'device_info' => $this->generateDeviceInfo(),
            'session_duration' => fake()->numberBetween(30, 600), // seconds
            'vote_weight' => 1.0,
            'is_valid' => true,
            'validation_errors' => null,
            'metadata' => $this->generateMetadata(),
        ];
    }

    /**
     * Indicate that the vote should be an abstention.
     */
    public function abstention(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_abstention' => true,
            'selected_options' => null,
            'justification' => fake()->sentence(15),
        ]);
    }

    /**
     * Indicate that the vote should be verified by biometric.
     */
    public function biometricVerified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_method' => 'biometric',
            'verification_data' => [
                'biometric_type' => fake()->randomElement(['fingerprint', 'face', 'voice']),
                'confidence_score' => fake()->randomFloat(2, 0.85, 1.0),
                'verification_time' => fake()->numberBetween(1, 5), // seconds
                'device_id' => fake()->uuid(),
                'biometric_hash' => fake()->sha256(),
            ],
        ]);
    }

    /**
     * Indicate that the vote should be verified by password.
     */
    public function passwordVerified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_method' => 'password',
            'verification_data' => [
                'password_verified' => true,
                'verification_time' => fake()->numberBetween(1, 3),
                'attempts' => 1,
            ],
        ]);
    }

    /**
     * Indicate that the vote should be verified by SMS.
     */
    public function smsVerified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_method' => 'sms',
            'verification_data' => [
                'phone_number' => fake()->phoneNumber(),
                'code_sent_at' => fake()->dateTimeThisHour(),
                'code_verified_at' => fake()->dateTimeThisHour(),
                'attempts' => fake()->numberBetween(1, 3),
            ],
        ]);
    }

    /**
     * Indicate that the vote should be invalid.
     */
    public function invalid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_valid' => false,
            'validation_errors' => [
                'error_code' => fake()->randomElement(['DUPLICATE_VOTE', 'INVALID_OPTIONS', 'VERIFICATION_FAILED', 'VOTING_CLOSED']),
                'error_message' => fake()->sentence(),
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Indicate that the vote should have multiple options selected.
     */
    public function multipleOptions(array $optionIds = null): static
    {
        $options = $optionIds ?? ['option_1', 'option_2', 'option_3'];
        
        return $this->state(fn (array $attributes) => [
            'selected_options' => array_slice($options, 0, fake()->numberBetween(2, count($options))),
        ]);
    }

    /**
     * Indicate that the vote should be ranked.
     */
    public function ranked(array $optionIds = null): static
    {
        $options = $optionIds ?? ['option_1', 'option_2', 'option_3', 'option_4'];
        shuffle($options);
        
        $rankedOptions = [];
        foreach ($options as $index => $optionId) {
            $rankedOptions[] = [
                'option_id' => $optionId,
                'rank' => $index + 1,
            ];
        }
        
        return $this->state(fn (array $attributes) => [
            'selected_options' => $rankedOptions,
        ]);
    }

    /**
     * Indicate that the vote should have a specific weight.
     */
    public function withWeight(float $weight): static
    {
        return $this->state(fn (array $attributes) => [
            'vote_weight' => $weight,
        ]);
    }

    /**
     * Indicate that the vote should have justification.
     */
    public function withJustification(string $justification = null): static
    {
        return $this->state(fn (array $attributes) => [
            'justification' => $justification ?? fake()->paragraphs(2, true),
        ]);
    }

    /**
     * Indicate that the vote should be from mobile device.
     */
    public function fromMobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_agent' => fake()->randomElement([
                'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15',
                'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0',
                'Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36',
            ]),
            'device_info' => [
                'type' => 'mobile',
                'platform' => fake()->randomElement(['iOS', 'Android']),
                'model' => fake()->randomElement(['iPhone 13', 'Samsung Galaxy S21', 'Pixel 6']),
                'os_version' => fake()->randomElement(['15.0', '11.0', '12.0']),
                'app_version' => '1.0.0',
                'screen_resolution' => fake()->randomElement(['1080x2340', '1170x2532', '1440x3200']),
            ],
        ]);
    }

    /**
     * Indicate that the vote should be from desktop.
     */
    public function fromDesktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_agent' => fake()->randomElement([
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            ]),
            'device_info' => [
                'type' => 'desktop',
                'platform' => fake()->randomElement(['Windows', 'macOS', 'Linux']),
                'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
                'browser_version' => fake()->randomElement(['91.0', '89.0', '14.1', '91.0']),
                'screen_resolution' => fake()->randomElement(['1920x1080', '2560x1440', '3840x2160']),
            ],
        ]);
    }

    /**
     * Generate selected options for the vote.
     */
    private function generateSelectedOptions(): array
    {
        $optionCount = fake()->numberBetween(1, 3);
        $options = [];
        
        for ($i = 1; $i <= $optionCount; $i++) {
            $options[] = 'option_' . fake()->numberBetween(1, 5);
        }
        
        return array_unique($options);
    }

    /**
     * Generate verification data.
     */
    private function generateVerificationData(): array
    {
        return [
            'verified_at' => fake()->dateTimeThisHour(),
            'verification_id' => fake()->uuid(),
            'confidence_score' => fake()->randomFloat(2, 0.8, 1.0),
            'attempts' => fake()->numberBetween(1, 3),
        ];
    }

    /**
     * Generate location data.
     */
    private function generateLocationData(): array
    {
        return [
            'country' => fake()->country(),
            'state' => fake()->state(),
            'city' => fake()->city(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'timezone' => fake()->timezone(),
            'accuracy' => fake()->numberBetween(10, 100), // meters
        ];
    }

    /**
     * Generate device information.
     */
    private function generateDeviceInfo(): array
    {
        return [
            'type' => fake()->randomElement(['desktop', 'mobile', 'tablet']),
            'platform' => fake()->randomElement(['Windows', 'macOS', 'Linux', 'iOS', 'Android']),
            'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'version' => fake()->randomElement(['91.0', '89.0', '14.1', '91.0']),
            'language' => fake()->randomElement(['pt-BR', 'en-US', 'es-ES']),
            'screen_resolution' => fake()->randomElement(['1920x1080', '1366x768', '1080x2340']),
        ];
    }

    /**
     * Generate metadata.
     */
    private function generateMetadata(): array
    {
        return [
            'vote_sequence' => fake()->numberBetween(1, 1000),
            'session_id' => fake()->uuid(),
            'referrer' => fake()->url(),
            'campaign_source' => fake()->randomElement(['email', 'sms', 'push', 'direct']),
            'voting_duration' => fake()->numberBetween(30, 600), // seconds
            'page_views' => fake()->numberBetween(1, 5),
            'interactions' => fake()->numberBetween(3, 20),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Vote $vote) {
            // Ensure vote is valid for active votings
            if ($vote->voting && $vote->voting->status === 'active') {
                $vote->update(['is_valid' => true]);
            }
        });
    }
}