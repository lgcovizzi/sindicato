<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Convenio;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Convenio>
 */
class ConvenioFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Convenio::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' - ' . fake()->randomElement(['Desconto', 'Benefício', 'Parceria']),
            'description' => fake()->paragraphs(3, true),
            'company_name' => fake()->company(),
            'company_cnpj' => $this->generateCNPJ(),
            'company_contact' => fake()->phoneNumber(),
            'company_email' => fake()->companyEmail(),
            'company_website' => fake()->url(),
            'category' => fake()->randomElement(['saude', 'educacao', 'alimentacao', 'lazer', 'tecnologia', 'vestuario', 'servicos', 'outros']),
            'type' => fake()->randomElement(['desconto', 'cashback', 'pontos', 'brinde', 'servico_gratuito']),
            'discount_type' => fake()->randomElement(['percentage', 'fixed_amount', 'buy_x_get_y', 'free_shipping']),
            'discount_value' => fake()->randomFloat(2, 5, 50),
            'discount_description' => fake()->sentence(8),
            'minimum_purchase' => fake()->boolean(60) ? fake()->randomFloat(2, 50, 500) : null,
            'maximum_discount' => fake()->boolean(40) ? fake()->randomFloat(2, 20, 200) : null,
            'usage_limit_per_user' => fake()->boolean(30) ? fake()->numberBetween(1, 10) : null,
            'total_usage_limit' => fake()->boolean(20) ? fake()->numberBetween(100, 1000) : null,
            'location_type' => fake()->randomElement(['online', 'physical', 'both']),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'postal_code' => fake()->postcode(),
            'latitude' => fake()->latitude(-30, -10),
            'longitude' => fake()->longitude(-60, -40),
            'business_hours' => $this->generateBusinessHours(),
            'phone' => fake()->phoneNumber(),
            'whatsapp' => fake()->phoneNumber(),
            'status' => 'active',
            'is_featured' => fake()->boolean(20),
            'is_exclusive' => fake()->boolean(30),
            'requires_card' => fake()->boolean(40),
            'requires_app' => fake()->boolean(25),
            'access_groups' => null,
            'access_roles' => null,
            'exclude_users' => null,
            'images' => $this->generateImages(),
            'documents' => null,
            'qr_code' => fake()->uuid(),
            'promo_code' => fake()->boolean(50) ? strtoupper(fake()->lexify('??????')) : null,
            'terms_and_conditions' => fake()->paragraphs(5, true),
            'usage_instructions' => fake()->paragraphs(2, true),
            'total_usage_count' => fake()->numberBetween(0, 500),
            'total_savings_amount' => fake()->randomFloat(2, 0, 10000),
            'average_rating' => fake()->randomFloat(1, 3.5, 5.0),
            'total_ratings' => fake()->numberBetween(0, 200),
            'click_count' => fake()->numberBetween(0, 1000),
            'view_count' => fake()->numberBetween(0, 2000),
            'share_count' => fake()->numberBetween(0, 100),
            'seo_title' => null,
            'seo_description' => null,
            'seo_keywords' => null,
            'starts_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'ends_at' => fake()->dateTimeBetween('now', '+365 days'),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    /**
     * Indicate that the convenio should be featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'is_exclusive' => fake()->boolean(60),
        ]);
    }

    /**
     * Indicate that the convenio should be exclusive.
     */
    public function exclusive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_exclusive' => true,
            'access_groups' => ['premium', 'vip'],
        ]);
    }

    /**
     * Indicate that the convenio should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the convenio should be expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'ends_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    /**
     * Indicate that the convenio should be pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the convenio should be online only.
     */
    public function onlineOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'location_type' => 'online',
            'address' => null,
            'city' => null,
            'state' => null,
            'postal_code' => null,
            'latitude' => null,
            'longitude' => null,
            'phone' => null,
            'business_hours' => null,
        ]);
    }

    /**
     * Indicate that the convenio should be physical only.
     */
    public function physicalOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'location_type' => 'physical',
            'requires_app' => false,
        ]);
    }

    /**
     * Indicate that the convenio should be in a specific category.
     */
    public function inCategory(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }

    /**
     * Indicate that the convenio should have a specific discount type.
     */
    public function withDiscountType(string $type, float $value): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => $type,
            'discount_value' => $value,
        ]);
    }

    /**
     * Indicate that the convenio should require a card.
     */
    public function requiresCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_card' => true,
        ]);
    }

    /**
     * Indicate that the convenio should require the app.
     */
    public function requiresApp(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_app' => true,
            'location_type' => fake()->randomElement(['online', 'both']),
        ]);
    }

    /**
     * Indicate that the convenio should have usage limits.
     */
    public function withUsageLimits(int $perUser = null, int $total = null): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_limit_per_user' => $perUser,
            'total_usage_limit' => $total,
        ]);
    }

    /**
     * Indicate that the convenio should have minimum purchase requirement.
     */
    public function withMinimumPurchase(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'minimum_purchase' => $amount,
        ]);
    }

    /**
     * Indicate that the convenio should have a promo code.
     */
    public function withPromoCode(string $code = null): static
    {
        return $this->state(fn (array $attributes) => [
            'promo_code' => $code ?? strtoupper(fake()->lexify('??????')),
        ]);
    }

    /**
     * Indicate that the convenio should have high ratings.
     */
    public function highRated(): static
    {
        return $this->state(fn (array $attributes) => [
            'average_rating' => fake()->randomFloat(1, 4.0, 5.0),
            'total_ratings' => fake()->numberBetween(50, 500),
        ]);
    }

    /**
     * Indicate that the convenio should be popular.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_usage_count' => fake()->numberBetween(200, 1000),
            'view_count' => fake()->numberBetween(1000, 5000),
            'click_count' => fake()->numberBetween(500, 2000),
            'share_count' => fake()->numberBetween(50, 200),
        ]);
    }

    /**
     * Generate a valid CNPJ.
     */
    private function generateCNPJ(): string
    {
        $cnpj = '';
        for ($i = 0; $i < 12; $i++) {
            $cnpj .= fake()->numberBetween(0, 9);
        }
        
        // Calculate verification digits
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum1 = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum1 += intval($cnpj[$i]) * $weights1[$i];
        }
        $digit1 = $sum1 % 11 < 2 ? 0 : 11 - ($sum1 % 11);
        
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum2 = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum2 += intval($cnpj[$i]) * $weights2[$i];
        }
        $sum2 += $digit1 * $weights2[12];
        $digit2 = $sum2 % 11 < 2 ? 0 : 11 - ($sum2 % 11);
        
        return $cnpj . $digit1 . $digit2;
    }

    /**
     * Generate business hours.
     */
    private function generateBusinessHours(): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hours = [];
        
        foreach ($days as $day) {
            if (fake()->boolean(85)) { // 85% chance of being open
                $openHour = fake()->numberBetween(6, 10);
                $closeHour = fake()->numberBetween(17, 22);
                
                $hours[$day] = [
                    'open' => sprintf('%02d:00', $openHour),
                    'close' => sprintf('%02d:00', $closeHour),
                    'is_open' => true,
                ];
            } else {
                $hours[$day] = [
                    'open' => null,
                    'close' => null,
                    'is_open' => false,
                ];
            }
        }
        
        return $hours;
    }

    /**
     * Generate images array.
     */
    private function generateImages(): array
    {
        $imageCount = fake()->numberBetween(1, 5);
        $images = [];
        
        for ($i = 0; $i < $imageCount; $i++) {
            $images[] = [
                'url' => fake()->imageUrl(800, 600, 'business'),
                'alt' => fake()->sentence(3),
                'type' => fake()->randomElement(['logo', 'banner', 'product', 'store']),
                'order' => $i + 1,
            ];
        }
        
        return $images;
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Convenio $convenio) {
            // Generate SEO fields if not provided
            if (!$convenio->seo_title) {
                $convenio->update([
                    'seo_title' => $convenio->name . ' - Convênio Sindical',
                    'seo_description' => substr($convenio->description, 0, 160),
                    'seo_keywords' => implode(', ', [
                        $convenio->category,
                        $convenio->company_name,
                        'desconto',
                        'convênio',
                        'sindical'
                    ]),
                ]);
            }
        });
    }
}