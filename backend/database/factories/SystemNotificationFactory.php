<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SystemNotification;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SystemNotification>
 */
class SystemNotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = SystemNotification::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'message' => fake()->paragraph(2),
            'type' => fake()->randomElement(['info', 'success', 'warning', 'error', 'system']),
            'category' => fake()->randomElement(['general', 'voting', 'convenio', 'news', 'system', 'security', 'maintenance']),
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'icon' => fake()->randomElement(['bell', 'info', 'warning', 'error', 'check', 'star', 'heart']),
            'color' => fake()->randomElement(['blue', 'green', 'yellow', 'red', 'purple', 'gray']),
            'action_url' => fake()->boolean(60) ? fake()->url() : null,
            'action_text' => fake()->boolean(60) ? fake()->randomElement(['Ver mais', 'Acessar', 'Confirmar', 'Visualizar']) : null,
            'is_dismissible' => fake()->boolean(80),
            'auto_dismiss' => fake()->boolean(40),
            'dismiss_after' => fake()->boolean(40) ? fake()->numberBetween(5, 60) : null, // seconds
            'channels' => $this->generateChannels(),
            'delivery_status' => 'pending',
            'sent_at' => null,
            'delivered_at' => null,
            'read_at' => null,
            'clicked_at' => null,
            'dismissed_at' => null,
            'failed_at' => null,
            'failure_reason' => null,
            'retry_count' => 0,
            'max_retries' => 3,
            'scheduled_for' => fake()->boolean(20) ? fake()->dateTimeBetween('now', '+7 days') : null,
            'expires_at' => fake()->boolean(30) ? fake()->dateTimeBetween('+1 day', '+30 days') : null,
            'target_audience' => $this->generateTargetAudience(),
            'segmentation_rules' => null,
            'related_type' => fake()->boolean(50) ? fake()->randomElement(['App\\Models\\Voting', 'App\\Models\\News', 'App\\Models\\Convenio']) : null,
            'related_id' => fake()->boolean(50) ? fake()->numberBetween(1, 100) : null,
            'metadata' => $this->generateMetadata(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the notification should be urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
            'type' => 'warning',
            'color' => 'red',
            'icon' => 'warning',
            'channels' => ['database', 'push', 'email', 'sms'],
            'auto_dismiss' => false,
            'is_dismissible' => true,
        ]);
    }

    /**
     * Indicate that the notification should be a success message.
     */
    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'success',
            'color' => 'green',
            'icon' => 'check',
            'priority' => 'normal',
            'auto_dismiss' => true,
            'dismiss_after' => 10,
        ]);
    }

    /**
     * Indicate that the notification should be an error message.
     */
    public function error(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'error',
            'color' => 'red',
            'icon' => 'error',
            'priority' => 'high',
            'auto_dismiss' => false,
            'is_dismissible' => true,
        ]);
    }

    /**
     * Indicate that the notification should be sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_status' => 'sent',
            'sent_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the notification should be delivered.
     */
    public function delivered(): static
    {
        $sentAt = fake()->dateTimeBetween('-7 days', 'now');
        
        return $this->state(fn (array $attributes) => [
            'delivery_status' => 'delivered',
            'sent_at' => $sentAt,
            'delivered_at' => fake()->dateTimeBetween($sentAt, 'now'),
        ]);
    }

    /**
     * Indicate that the notification should be read.
     */
    public function read(): static
    {
        $sentAt = fake()->dateTimeBetween('-7 days', 'now');
        $deliveredAt = fake()->dateTimeBetween($sentAt, 'now');
        
        return $this->state(fn (array $attributes) => [
            'delivery_status' => 'delivered',
            'sent_at' => $sentAt,
            'delivered_at' => $deliveredAt,
            'read_at' => fake()->dateTimeBetween($deliveredAt, 'now'),
        ]);
    }

    /**
     * Indicate that the notification should be clicked.
     */
    public function clicked(): static
    {
        $sentAt = fake()->dateTimeBetween('-7 days', 'now');
        $deliveredAt = fake()->dateTimeBetween($sentAt, 'now');
        $readAt = fake()->dateTimeBetween($deliveredAt, 'now');
        
        return $this->state(fn (array $attributes) => [
            'delivery_status' => 'delivered',
            'sent_at' => $sentAt,
            'delivered_at' => $deliveredAt,
            'read_at' => $readAt,
            'clicked_at' => fake()->dateTimeBetween($readAt, 'now'),
            'action_url' => fake()->url(),
            'action_text' => 'Ver mais',
        ]);
    }

    /**
     * Indicate that the notification should be dismissed.
     */
    public function dismissed(): static
    {
        $sentAt = fake()->dateTimeBetween('-7 days', 'now');
        $deliveredAt = fake()->dateTimeBetween($sentAt, 'now');
        
        return $this->state(fn (array $attributes) => [
            'delivery_status' => 'delivered',
            'sent_at' => $sentAt,
            'delivered_at' => $deliveredAt,
            'dismissed_at' => fake()->dateTimeBetween($deliveredAt, 'now'),
            'is_dismissible' => true,
        ]);
    }

    /**
     * Indicate that the notification should be failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_status' => 'failed',
            'failed_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'failure_reason' => fake()->randomElement([
                'Invalid device token',
                'User not found',
                'Network timeout',
                'Service unavailable',
                'Rate limit exceeded'
            ]),
            'retry_count' => fake()->numberBetween(1, 3),
        ]);
    }

    /**
     * Indicate that the notification should be scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_status' => 'scheduled',
            'scheduled_for' => fake()->dateTimeBetween('now', '+30 days'),
        ]);
    }

    /**
     * Indicate that the notification should be for voting category.
     */
    public function voting(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'voting',
            'type' => 'info',
            'icon' => 'vote',
            'color' => 'blue',
            'related_type' => 'App\\Models\\Voting',
            'related_id' => fake()->numberBetween(1, 100),
        ]);
    }

    /**
     * Indicate that the notification should be for convenio category.
     */
    public function convenio(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'convenio',
            'type' => 'info',
            'icon' => 'star',
            'color' => 'purple',
            'related_type' => 'App\\Models\\Convenio',
            'related_id' => fake()->numberBetween(1, 100),
        ]);
    }

    /**
     * Indicate that the notification should be for news category.
     */
    public function news(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'news',
            'type' => 'info',
            'icon' => 'news',
            'color' => 'blue',
            'related_type' => 'App\\Models\\News',
            'related_id' => fake()->numberBetween(1, 100),
        ]);
    }

    /**
     * Indicate that the notification should be system maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'maintenance',
            'type' => 'warning',
            'icon' => 'warning',
            'color' => 'yellow',
            'priority' => 'high',
            'target_audience' => ['all_users'],
            'channels' => ['database', 'push', 'email'],
        ]);
    }

    /**
     * Indicate that the notification should be security related.
     */
    public function security(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'security',
            'type' => 'warning',
            'icon' => 'shield',
            'color' => 'red',
            'priority' => 'urgent',
            'auto_dismiss' => false,
            'is_dismissible' => true,
        ]);
    }

    /**
     * Indicate that the notification should be broadcast to all users.
     */
    public function broadcast(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'target_audience' => ['all_users'],
            'channels' => ['database', 'push'],
        ]);
    }

    /**
     * Indicate that the notification should have specific channels.
     */
    public function withChannels(array $channels): static
    {
        return $this->state(fn (array $attributes) => [
            'channels' => $channels,
        ]);
    }

    /**
     * Indicate that the notification should expire.
     */
    public function withExpiration(int $days = 7): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => fake()->dateTimeBetween('now', "+{$days} days"),
        ]);
    }

    /**
     * Generate channels array.
     */
    private function generateChannels(): array
    {
        $availableChannels = ['database', 'push', 'email', 'sms', 'websocket'];
        $channelCount = fake()->numberBetween(1, 3);
        
        return fake()->randomElements($availableChannels, $channelCount);
    }

    /**
     * Generate target audience array.
     */
    private function generateTargetAudience(): array
    {
        $audiences = [
            'all_users', 'active_members', 'new_members', 'premium_members',
            'board_members', 'delegates', 'specific_roles', 'specific_groups'
        ];
        
        $audienceCount = fake()->numberBetween(1, 2);
        return fake()->randomElements($audiences, $audienceCount);
    }

    /**
     * Generate metadata array.
     */
    private function generateMetadata(): array
    {
        return [
            'campaign_id' => fake()->uuid(),
            'source' => fake()->randomElement(['system', 'admin', 'automated', 'api']),
            'template_id' => fake()->boolean(50) ? fake()->numberBetween(1, 20) : null,
            'batch_id' => fake()->boolean(30) ? fake()->uuid() : null,
            'tracking_id' => fake()->uuid(),
            'version' => '1.0',
            'environment' => fake()->randomElement(['production', 'staging', 'development']),
            'device_types' => fake()->randomElements(['mobile', 'desktop', 'tablet'], fake()->numberBetween(1, 3)),
            'languages' => ['pt-BR'],
            'timezone' => 'America/Sao_Paulo',
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (SystemNotification $notification) {
            // Auto-send notifications that are not scheduled
            if (!$notification->scheduled_for && $notification->delivery_status === 'pending') {
                $notification->update([
                    'delivery_status' => 'sent',
                    'sent_at' => now(),
                ]);
                
                // Simulate delivery for most notifications
                if (fake()->boolean(85)) {
                    $notification->update([
                        'delivery_status' => 'delivered',
                        'delivered_at' => now()->addSeconds(fake()->numberBetween(1, 30)),
                    ]);
                }
            }
        });
    }
}