<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase, WithFaker;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear all caches
        Cache::flush();
        
        // Disable queue processing during tests
        Queue::fake();
        
        // Set up database
        $this->artisan('migrate:fresh');
        
        // Seed basic data if needed
        $this->seedBasicData();
    }

    /**
     * Clean up after each test.
     */
    protected function tearDown(): void
    {
        // Clear any remaining data
        DB::disconnect();
        Cache::flush();
        
        parent::tearDown();
    }

    /**
     * Seed basic data for tests.
     */
    protected function seedBasicData(): void
    {
        // Create basic roles and permissions if needed
        // This can be customized per test class
    }

    /**
     * Create a user for testing.
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * Create an admin user for testing.
     */
    protected function createAdmin(array $attributes = []): User
    {
        return User::factory()->admin()->create($attributes);
    }

    /**
     * Create a verified user for testing.
     */
    protected function createVerifiedUser(array $attributes = []): User
    {
        return User::factory()->verified()->create($attributes);
    }

    /**
     * Authenticate a user for API testing.
     */
    protected function actingAsUser(User $user = null): User
    {
        $user = $user ?: $this->createUser();
        
        // Create JWT token for the user
        $token = JWTAuth::fromUser($user);
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);
        
        return $user;
    }

    /**
     * Authenticate an admin user for API testing.
     */
    protected function actingAsAdmin(User $admin = null): User
    {
        $admin = $admin ?: $this->createAdmin();
        
        // Create JWT token for the admin
        $token = JWTAuth::fromUser($admin);
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);
        
        return $admin;
    }

    /**
     * Set API headers for JSON requests.
     */
    protected function withApiHeaders(): self
    {
        return $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Assert that the response has the expected JSON structure.
     */
    protected function assertJsonStructure(array $structure, $response = null): void
    {
        $response = $response ?: $this->response;
        $response->assertJsonStructure($structure);
    }

    /**
     * Assert that the response contains validation errors.
     */
    protected function assertValidationErrors(array $fields, $response = null): void
    {
        $response = $response ?: $this->response;
        $response->assertStatus(422);
        
        foreach ($fields as $field) {
            $response->assertJsonValidationErrors($field);
        }
    }

    /**
     * Assert that the response is a successful API response.
     */
    protected function assertSuccessResponse($response = null): void
    {
        $response = $response ?: $this->response;
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
    }

    /**
     * Assert that the response is an error API response.
     */
    protected function assertErrorResponse(int $status = 400, $response = null): void
    {
        $response = $response ?: $this->response;
        $response->assertStatus($status)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors'
                ]);
    }

    /**
     * Assert that the response is unauthorized.
     */
    protected function assertUnauthorized($response = null): void
    {
        $response = $response ?: $this->response;
        $response->assertStatus(401);
    }

    /**
     * Assert that the response is forbidden.
     */
    protected function assertForbidden($response = null): void
    {
        $response = $response ?: $this->response;
        $response->assertStatus(403);
    }

    /**
     * Assert that the response is not found.
     */
    protected function assertNotFound($response = null): void
    {
        $response = $response ?: $this->response;
        $response->assertStatus(404);
    }

    /**
     * Create fake biometric data for testing.
     */
    protected function createFakeBiometricData(): array
    {
        return [
            'fingerprint' => base64_encode($this->faker->randomBytes(1024)),
            'face_encoding' => json_encode(array_fill(0, 128, $this->faker->randomFloat(4, -1, 1))),
            'voice_print' => base64_encode($this->faker->randomBytes(512))
        ];
    }

    /**
     * Create fake voting options.
     */
    protected function createFakeVotingOptions(): array
    {
        return [
            [
                'id' => 'option_1',
                'text' => 'Opção 1',
                'description' => 'Descrição da opção 1'
            ],
            [
                'id' => 'option_2',
                'text' => 'Opção 2',
                'description' => 'Descrição da opção 2'
            ],
            [
                'id' => 'option_3',
                'text' => 'Opção 3',
                'description' => 'Descrição da opção 3'
            ]
        ];
    }

    /**
     * Create fake convenio data.
     */
    protected function createFakeConvenioData(): array
    {
        return [
            'name' => $this->faker->company,
            'description' => $this->faker->paragraph,
            'category' => $this->faker->randomElement(['alimentacao', 'saude', 'educacao', 'lazer']),
            'discount_percentage' => $this->faker->numberBetween(5, 50),
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'website' => $this->faker->url
        ];
    }

    /**
     * Create fake news data.
     */
    protected function createFakeNewsData(): array
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraphs(3, true),
            'category' => $this->faker->randomElement(['geral', 'sindical', 'juridico', 'beneficios']),
            'status' => 'published',
            'visibility' => 'public'
        ];
    }

    /**
     * Mock external services for testing.
     */
    protected function mockExternalServices(): void
    {
        // Mock biometric verification service
        $this->mock('App\Services\BiometricVerificationService', function ($mock) {
            $mock->shouldReceive('verify')->andReturn(true);
            $mock->shouldReceive('register')->andReturn(true);
        });

        // Mock notification service
        $this->mock('App\Services\NotificationService', function ($mock) {
            $mock->shouldReceive('send')->andReturn(true);
            $mock->shouldReceive('sendToast')->andReturn(true);
        });
    }

    /**
     * Assert that a job was dispatched.
     */
    protected function assertJobDispatched(string $jobClass): void
    {
        Queue::assertPushed($jobClass);
    }

    /**
     * Assert that an event was dispatched.
     */
    protected function assertEventDispatched(string $eventClass): void
    {
        \Illuminate\Support\Facades\Event::assertDispatched($eventClass);
    }

    /**
     * Travel to a specific time for testing.
     */
    protected function travelTo(\Carbon\Carbon $date): void
    {
        \Illuminate\Support\Facades\Date::setTestNow($date);
    }

    /**
     * Travel back to the current time.
     */
    protected function travelBack(): void
    {
        \Illuminate\Support\Facades\Date::setTestNow();
    }

    /**
     * Assert database has record with given attributes.
     */
    protected function assertDatabaseHasRecord(string $table, array $attributes): void
    {
        $this->assertDatabaseHas($table, $attributes);
    }

    /**
     * Assert database missing record with given attributes.
     */
    protected function assertDatabaseMissingRecord(string $table, array $attributes): void
    {
        $this->assertDatabaseMissing($table, $attributes);
    }
}