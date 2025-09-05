<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'cpf' => $this->generateCPF(),
            'phone' => fake()->phoneNumber(),
            'birth_date' => fake()->dateTimeBetween('-65 years', '-18 years'),
            'gender' => fake()->randomElement(['M', 'F', 'O']),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip_code' => fake()->postcode(),
            'registration_number' => fake()->unique()->numerify('REG######'),
            'admission_date' => fake()->dateTimeBetween('-10 years', 'now'),
            'job_title' => fake()->jobTitle(),
            'department' => fake()->randomElement(['Administrativo', 'Operacional', 'Técnico', 'Gerencial']),
            'salary' => fake()->randomFloat(2, 1500, 15000),
            'status' => 'active',
            'profile_photo' => null,
            'two_factor_enabled' => false,
            'biometric_enabled' => false,
            'last_login_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'last_activity_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'login_attempts' => 0,
            'locked_until' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user should be verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the user should be an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => 'admin@sindicato.com',
            'name' => 'Administrador do Sistema',
            'job_title' => 'Administrador',
            'department' => 'Administrativo',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the user should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'last_login_at' => fake()->dateTimeBetween('-6 months', '-3 months'),
        ]);
    }

    /**
     * Indicate that the user should be suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
            'locked_until' => fake()->dateTimeBetween('now', '+30 days'),
        ]);
    }

    /**
     * Indicate that the user should have two-factor authentication enabled.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt('base32secret'),
        ]);
    }

    /**
     * Indicate that the user should have biometric authentication enabled.
     */
    public function withBiometric(): static
    {
        return $this->state(fn (array $attributes) => [
            'biometric_enabled' => true,
        ]);
    }

    /**
     * Indicate that the user should be locked.
     */
    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'login_attempts' => 5,
            'locked_until' => now()->addHours(1),
        ]);
    }

    /**
     * Indicate that the user should be a senior member.
     */
    public function senior(): static
    {
        return $this->state(fn (array $attributes) => [
            'admission_date' => fake()->dateTimeBetween('-20 years', '-10 years'),
            'salary' => fake()->randomFloat(2, 8000, 20000),
            'job_title' => fake()->randomElement(['Gerente', 'Coordenador', 'Supervisor', 'Especialista Senior']),
        ]);
    }

    /**
     * Indicate that the user should be a new member.
     */
    public function newMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'admission_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'salary' => fake()->randomFloat(2, 1500, 5000),
            'job_title' => fake()->randomElement(['Assistente', 'Auxiliar', 'Trainee', 'Estagiário']),
        ]);
    }

    /**
     * Indicate that the user should have recent activity.
     */
    public function recentlyActive(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login_at' => fake()->dateTimeBetween('-24 hours', 'now'),
            'last_activity_at' => fake()->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    /**
     * Generate a valid CPF number.
     */
    private function generateCPF(): string
    {
        $cpf = '';
        
        // Generate first 9 digits
        for ($i = 0; $i < 9; $i++) {
            $cpf .= fake()->numberBetween(0, 9);
        }
        
        // Calculate first verification digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        $cpf .= $digit1;
        
        // Calculate second verification digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        $cpf .= $digit2;
        
        return $cpf;
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            // Create user preferences after user creation
            if (!$user->preferences) {
                $user->preferences()->create([
                    'theme' => fake()->randomElement(['light', 'dark', 'system']),
                    'language' => 'pt-BR',
                    'timezone' => 'America/Sao_Paulo',
                    'email_notifications' => true,
                    'push_notifications' => true,
                ]);
            }
        });
    }
}