<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\News;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = News::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'slug' => fake()->slug(),
            'summary' => fake()->paragraph(3),
            'content' => fake()->paragraphs(8, true),
            'category' => fake()->randomElement(['geral', 'sindical', 'trabalhista', 'beneficios', 'eventos', 'comunicados', 'juridico', 'saude']),
            'tags' => $this->generateTags(),
            'status' => 'published',
            'visibility' => 'public',
            'is_featured' => fake()->boolean(20),
            'is_urgent' => fake()->boolean(10),
            'is_breaking' => fake()->boolean(5),
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'access_groups' => null,
            'access_roles' => null,
            'exclude_users' => null,
            'featured_image' => fake()->imageUrl(1200, 630, 'news'),
            'featured_image_alt' => fake()->sentence(4),
            'gallery' => $this->generateGallery(),
            'attachments' => null,
            'video_url' => fake()->boolean(20) ? fake()->url() : null,
            'audio_url' => fake()->boolean(10) ? fake()->url() : null,
            'external_link' => fake()->boolean(15) ? fake()->url() : null,
            'source' => fake()->boolean(30) ? fake()->company() : null,
            'author_name' => fake()->name(),
            'author_email' => fake()->email(),
            'view_count' => fake()->numberBetween(0, 5000),
            'like_count' => fake()->numberBetween(0, 500),
            'share_count' => fake()->numberBetween(0, 200),
            'comment_count' => fake()->numberBetween(0, 100),
            'reading_time' => fake()->numberBetween(2, 15), // minutes
            'seo_title' => null,
            'seo_description' => null,
            'seo_keywords' => null,
            'social_title' => null,
            'social_description' => null,
            'social_image' => null,
            'allow_comments' => fake()->boolean(80),
            'comments_moderated' => fake()->boolean(60),
            'notify_subscribers' => fake()->boolean(70),
            'send_push_notification' => fake()->boolean(40),
            'push_notification_title' => null,
            'push_notification_body' => null,
            'schedule_notification' => null,
            'target_audience' => $this->generateTargetAudience(),
            'location_specific' => fake()->boolean(30),
            'location_data' => null,
            'language' => 'pt-BR',
            'published_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'expires_at' => fake()->boolean(20) ? fake()->dateTimeBetween('now', '+365 days') : null,
            'created_by' => User::factory(),
            'updated_by' => null,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the news should be featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'priority' => fake()->randomElement(['high', 'urgent']),
        ]);
    }

    /**
     * Indicate that the news should be urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_urgent' => true,
            'priority' => 'urgent',
            'send_push_notification' => true,
            'notify_subscribers' => true,
        ]);
    }

    /**
     * Indicate that the news should be breaking news.
     */
    public function breaking(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_breaking' => true,
            'is_urgent' => true,
            'is_featured' => true,
            'priority' => 'urgent',
            'send_push_notification' => true,
            'notify_subscribers' => true,
            'push_notification_title' => 'URGENTE: ' . fake()->sentence(4),
            'push_notification_body' => fake()->sentence(8),
        ]);
    }

    /**
     * Indicate that the news should be draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the news should be scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'published_at' => fake()->dateTimeBetween('now', '+30 days'),
        ]);
    }

    /**
     * Indicate that the news should be archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
            'published_at' => fake()->dateTimeBetween('-365 days', '-30 days'),
        ]);
    }

    /**
     * Indicate that the news should be private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'private',
            'access_groups' => ['admin', 'board'],
        ]);
    }

    /**
     * Indicate that the news should be restricted.
     */
    public function restricted(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'restricted',
            'access_roles' => ['member', 'premium'],
        ]);
    }

    /**
     * Indicate that the news should be in a specific category.
     */
    public function inCategory(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }

    /**
     * Indicate that the news should have video content.
     */
    public function withVideo(): static
    {
        return $this->state(fn (array $attributes) => [
            'video_url' => fake()->randomElement([
                'https://www.youtube.com/watch?v=' . fake()->lexify('???????????'),
                'https://vimeo.com/' . fake()->numberBetween(100000000, 999999999),
            ]),
            'reading_time' => fake()->numberBetween(5, 20),
        ]);
    }

    /**
     * Indicate that the news should have audio content.
     */
    public function withAudio(): static
    {
        return $this->state(fn (array $attributes) => [
            'audio_url' => fake()->url() . '/audio.mp3',
            'reading_time' => fake()->numberBetween(3, 15),
        ]);
    }

    /**
     * Indicate that the news should be popular.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'view_count' => fake()->numberBetween(1000, 10000),
            'like_count' => fake()->numberBetween(100, 1000),
            'share_count' => fake()->numberBetween(50, 500),
            'comment_count' => fake()->numberBetween(20, 200),
        ]);
    }

    /**
     * Indicate that the news should have location data.
     */
    public function withLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'location_specific' => true,
            'location_data' => [
                'city' => fake()->city(),
                'state' => fake()->stateAbbr(),
                'country' => 'Brasil',
                'latitude' => fake()->latitude(-30, -10),
                'longitude' => fake()->longitude(-60, -40),
                'radius' => fake()->numberBetween(10, 100), // km
            ],
        ]);
    }

    /**
     * Indicate that the news should be approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => User::factory(),
            'approved_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the news should have scheduled notifications.
     */
    public function withScheduledNotification(): static
    {
        return $this->state(fn (array $attributes) => [
            'notify_subscribers' => true,
            'send_push_notification' => true,
            'schedule_notification' => fake()->dateTimeBetween('now', '+7 days'),
            'push_notification_title' => fake()->sentence(4),
            'push_notification_body' => fake()->sentence(8),
        ]);
    }

    /**
     * Indicate that the news should have attachments.
     */
    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachments' => [
                [
                    'name' => 'documento.pdf',
                    'url' => fake()->url() . '/documento.pdf',
                    'size' => fake()->numberBetween(100000, 5000000), // bytes
                    'type' => 'application/pdf',
                ],
                [
                    'name' => 'planilha.xlsx',
                    'url' => fake()->url() . '/planilha.xlsx',
                    'size' => fake()->numberBetween(50000, 2000000),
                    'type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ],
            ],
        ]);
    }

    /**
     * Generate tags array.
     */
    private function generateTags(): array
    {
        $availableTags = [
            'sindical', 'trabalhista', 'benefícios', 'direitos', 'convenção',
            'assembleia', 'greve', 'negociação', 'salário', 'férias',
            'saúde', 'segurança', 'previdência', 'educação', 'capacitação'
        ];
        
        $tagCount = fake()->numberBetween(2, 6);
        return fake()->randomElements($availableTags, $tagCount);
    }

    /**
     * Generate gallery array.
     */
    private function generateGallery(): array
    {
        if (!fake()->boolean(40)) {
            return [];
        }
        
        $imageCount = fake()->numberBetween(2, 8);
        $gallery = [];
        
        for ($i = 0; $i < $imageCount; $i++) {
            $gallery[] = [
                'url' => fake()->imageUrl(800, 600, 'news'),
                'alt' => fake()->sentence(4),
                'caption' => fake()->sentence(8),
                'order' => $i + 1,
            ];
        }
        
        return $gallery;
    }

    /**
     * Generate target audience array.
     */
    private function generateTargetAudience(): array
    {
        $audiences = [
            'all_members', 'active_members', 'new_members', 'senior_members',
            'retirees', 'board_members', 'delegates', 'specific_departments',
            'specific_locations', 'premium_members'
        ];
        
        $audienceCount = fake()->numberBetween(1, 3);
        return fake()->randomElements($audiences, $audienceCount);
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (News $news) {
            // Generate SEO fields if not provided
            if (!$news->seo_title) {
                $news->update([
                    'seo_title' => $news->title,
                    'seo_description' => substr($news->summary, 0, 160),
                    'seo_keywords' => implode(', ', array_merge(
                        [$news->category],
                        array_slice($news->tags, 0, 4)
                    )),
                    'social_title' => $news->title,
                    'social_description' => substr($news->summary, 0, 200),
                    'social_image' => $news->featured_image,
                ]);
            }
            
            // Auto-approve if created by admin
            if ($news->creator && $news->creator->hasRole('admin')) {
                $news->update([
                    'approved_by' => $news->created_by,
                    'approved_at' => now(),
                ]);
            }
        });
    }
}