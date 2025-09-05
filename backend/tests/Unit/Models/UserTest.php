<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\BiometricData;
use App\Models\UserPreference;
use App\Models\Voting;
use App\Models\Vote;
use App\Models\News;
use App\Models\Convenio;
use App\Models\SystemNotification;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_can_create_a_user()
    {
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'cpf' => '12345678901',
            'phone' => '11999999999',
            'password' => 'password123',
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertEquals($userData['cpf'], $user->cpf);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $expectedFillable = [
            'name', 'email', 'cpf', 'phone', 'birth_date', 'address',
            'city', 'state', 'postal_code', 'registration_number',
            'admission_date', 'job_title', 'department', 'salary',
            'status', 'profile_photo', 'password', 'email_verified_at',
            'phone_verified_at', 'two_factor_enabled', 'two_factor_secret',
            'biometric_enabled', 'last_login_at', 'last_activity_at',
            'login_attempts', 'locked_until', 'preferences', 'metadata'
        ];

        $user = new User();
        $this->assertEquals($expectedFillable, $user->getFillable());
    }

    /** @test */
    public function it_has_hidden_attributes()
    {
        $expectedHidden = [
            'password', 'remember_token', 'two_factor_secret'
        ];

        $user = new User();
        $this->assertEquals($expectedHidden, $user->getHidden());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'birth_date' => '1990-01-01',
            'admission_date' => '2020-01-01',
            'last_login_at' => now(),
            'last_activity_at' => now(),
            'locked_until' => now()->addHour(),
            'two_factor_enabled' => true,
            'biometric_enabled' => true,
            'preferences' => ['theme' => 'dark'],
            'metadata' => ['source' => 'web']
        ]);

        $this->assertInstanceOf(Carbon::class, $user->email_verified_at);
        $this->assertInstanceOf(Carbon::class, $user->phone_verified_at);
        $this->assertInstanceOf(Carbon::class, $user->birth_date);
        $this->assertInstanceOf(Carbon::class, $user->admission_date);
        $this->assertInstanceOf(Carbon::class, $user->last_login_at);
        $this->assertInstanceOf(Carbon::class, $user->last_activity_at);
        $this->assertInstanceOf(Carbon::class, $user->locked_until);
        $this->assertIsBool($user->two_factor_enabled);
        $this->assertIsBool($user->biometric_enabled);
        $this->assertIsArray($user->preferences);
        $this->assertIsArray($user->metadata);
    }

    /** @test */
    public function it_has_biometric_data_relationship()
    {
        $user = User::factory()->create();
        $biometricData = BiometricData::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(BiometricData::class, $user->biometricData);
        $this->assertEquals($biometricData->id, $user->biometricData->id);
    }

    /** @test */
    public function it_has_user_preferences_relationship()
    {
        $user = User::factory()->create();
        $preferences = UserPreference::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(UserPreference::class, $user->userPreferences);
        $this->assertEquals($preferences->id, $user->userPreferences->id);
    }

    /** @test */
    public function it_has_created_votings_relationship()
    {
        $user = User::factory()->create();
        $voting = Voting::factory()->create(['created_by' => $user->id]);

        $this->assertTrue($user->createdVotings()->exists());
        $this->assertEquals($voting->id, $user->createdVotings->first()->id);
    }

    /** @test */
    public function it_has_votes_relationship()
    {
        $user = User::factory()->create();
        $vote = Vote::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->votes()->exists());
        $this->assertEquals($vote->id, $user->votes->first()->id);
    }

    /** @test */
    public function it_has_created_news_relationship()
    {
        $user = User::factory()->create();
        $news = News::factory()->create(['created_by' => $user->id]);

        $this->assertTrue($user->createdNews()->exists());
        $this->assertEquals($news->id, $user->createdNews->first()->id);
    }

    /** @test */
    public function it_has_created_convenios_relationship()
    {
        $user = User::factory()->create();
        $convenio = Convenio::factory()->create(['created_by' => $user->id]);

        $this->assertTrue($user->createdConvenios()->exists());
        $this->assertEquals($convenio->id, $user->createdConvenios->first()->id);
    }

    /** @test */
    public function it_has_notifications_relationship()
    {
        $user = User::factory()->create();
        $notification = SystemNotification::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->notifications()->exists());
        $this->assertEquals($notification->id, $user->notifications->first()->id);
    }

    /** @test */
    public function it_has_activity_logs_relationship()
    {
        $user = User::factory()->create();
        $activityLog = ActivityLog::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->activityLogs()->exists());
        $this->assertEquals($activityLog->id, $user->activityLogs->first()->id);
    }

    /** @test */
    public function it_can_check_if_user_is_active()
    {
        $activeUser = User::factory()->create(['status' => 'active']);
        $inactiveUser = User::factory()->create(['status' => 'inactive']);

        $this->assertTrue($activeUser->isActive());
        $this->assertFalse($inactiveUser->isActive());
    }

    /** @test */
    public function it_can_check_if_user_is_verified()
    {
        $verifiedUser = User::factory()->verified()->create();
        $unverifiedUser = User::factory()->unverified()->create();

        $this->assertTrue($verifiedUser->isVerified());
        $this->assertFalse($unverifiedUser->isVerified());
    }

    /** @test */
    public function it_can_check_if_user_is_locked()
    {
        $lockedUser = User::factory()->locked()->create();
        $unlockedUser = User::factory()->create();

        $this->assertTrue($lockedUser->isLocked());
        $this->assertFalse($unlockedUser->isLocked());
    }

    /** @test */
    public function it_can_check_if_user_has_biometric_enabled()
    {
        $biometricUser = User::factory()->withBiometric()->create();
        $regularUser = User::factory()->create(['biometric_enabled' => false]);

        $this->assertTrue($biometricUser->hasBiometricEnabled());
        $this->assertFalse($regularUser->hasBiometricEnabled());
    }

    /** @test */
    public function it_can_check_if_user_has_two_factor_enabled()
    {
        $twoFactorUser = User::factory()->withTwoFactor()->create();
        $regularUser = User::factory()->create(['two_factor_enabled' => false]);

        $this->assertTrue($twoFactorUser->hasTwoFactorEnabled());
        $this->assertFalse($regularUser->hasTwoFactorEnabled());
    }

    /** @test */
    public function it_can_get_full_name_attribute()
    {
        $user = User::factory()->create(['name' => 'João Silva Santos']);
        
        $this->assertEquals('João Silva Santos', $user->full_name);
        $this->assertEquals('João Silva Santos', $user->getFullNameAttribute());
    }

    /** @test */
    public function it_can_get_initials_attribute()
    {
        $user = User::factory()->create(['name' => 'João Silva Santos']);
        
        $this->assertEquals('JSS', $user->initials);
        $this->assertEquals('JSS', $user->getInitialsAttribute());
    }

    /** @test */
    public function it_can_get_age_attribute()
    {
        $user = User::factory()->create([
            'birth_date' => Carbon::now()->subYears(30)
        ]);
        
        $this->assertEquals(30, $user->age);
        $this->assertEquals(30, $user->getAgeAttribute());
    }

    /** @test */
    public function it_can_get_years_of_service_attribute()
    {
        $user = User::factory()->create([
            'admission_date' => Carbon::now()->subYears(5)
        ]);
        
        $this->assertEquals(5, $user->years_of_service);
        $this->assertEquals(5, $user->getYearsOfServiceAttribute());
    }

    /** @test */
    public function it_can_update_last_activity()
    {
        $user = User::factory()->create(['last_activity_at' => null]);
        
        $user->updateLastActivity();
        
        $this->assertNotNull($user->fresh()->last_activity_at);
        $this->assertTrue($user->fresh()->last_activity_at->isToday());
    }

    /** @test */
    public function it_can_lock_user_account()
    {
        $user = User::factory()->create();
        
        $user->lockAccount(60); // 60 minutes
        
        $this->assertTrue($user->fresh()->isLocked());
        $this->assertNotNull($user->fresh()->locked_until);
    }

    /** @test */
    public function it_can_unlock_user_account()
    {
        $user = User::factory()->locked()->create();
        
        $user->unlockAccount();
        
        $this->assertFalse($user->fresh()->isLocked());
        $this->assertNull($user->fresh()->locked_until);
        $this->assertEquals(0, $user->fresh()->login_attempts);
    }

    /** @test */
    public function it_can_increment_login_attempts()
    {
        $user = User::factory()->create(['login_attempts' => 0]);
        
        $user->incrementLoginAttempts();
        
        $this->assertEquals(1, $user->fresh()->login_attempts);
    }

    /** @test */
    public function it_can_reset_login_attempts()
    {
        $user = User::factory()->create(['login_attempts' => 5]);
        
        $user->resetLoginAttempts();
        
        $this->assertEquals(0, $user->fresh()->login_attempts);
    }

    /** @test */
    public function it_can_enable_biometric()
    {
        $user = User::factory()->create(['biometric_enabled' => false]);
        
        $user->enableBiometric();
        
        $this->assertTrue($user->fresh()->biometric_enabled);
    }

    /** @test */
    public function it_can_disable_biometric()
    {
        $user = User::factory()->withBiometric()->create();
        
        $user->disableBiometric();
        
        $this->assertFalse($user->fresh()->biometric_enabled);
    }

    /** @test */
    public function it_can_enable_two_factor()
    {
        $user = User::factory()->create(['two_factor_enabled' => false]);
        $secret = 'test-secret';
        
        $user->enableTwoFactor($secret);
        
        $this->assertTrue($user->fresh()->two_factor_enabled);
        $this->assertEquals($secret, $user->fresh()->two_factor_secret);
    }

    /** @test */
    public function it_can_disable_two_factor()
    {
        $user = User::factory()->withTwoFactor()->create();
        
        $user->disableTwoFactor();
        
        $this->assertFalse($user->fresh()->two_factor_enabled);
        $this->assertNull($user->fresh()->two_factor_secret);
    }

    /** @test */
    public function it_uses_active_scope()
    {
        User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'inactive']);
        User::factory()->create(['status' => 'suspended']);
        
        $activeUsers = User::active()->get();
        
        $this->assertCount(1, $activeUsers);
        $this->assertEquals('active', $activeUsers->first()->status);
    }

    /** @test */
    public function it_uses_verified_scope()
    {
        User::factory()->verified()->create();
        User::factory()->unverified()->create();
        
        $verifiedUsers = User::verified()->get();
        
        $this->assertCount(1, $verifiedUsers);
        $this->assertNotNull($verifiedUsers->first()->email_verified_at);
    }

    /** @test */
    public function it_uses_with_biometric_scope()
    {
        User::factory()->withBiometric()->create();
        User::factory()->create(['biometric_enabled' => false]);
        
        $biometricUsers = User::withBiometric()->get();
        
        $this->assertCount(1, $biometricUsers);
        $this->assertTrue($biometricUsers->first()->biometric_enabled);
    }

    /** @test */
    public function it_uses_recently_active_scope()
    {
        User::factory()->recentlyActive()->create();
        User::factory()->create(['last_activity_at' => Carbon::now()->subDays(10)]);
        
        $recentlyActiveUsers = User::recentlyActive()->get();
        
        $this->assertCount(1, $recentlyActiveUsers);
    }

    /** @test */
    public function it_automatically_creates_user_preferences_after_creation()
    {
        $user = User::factory()->create();
        
        $this->assertNotNull($user->userPreferences);
        $this->assertInstanceOf(UserPreference::class, $user->userPreferences);
    }

    /** @test */
    public function it_validates_cpf_format()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create(['cpf' => 'invalid-cpf']);
    }

    /** @test */
    public function it_validates_email_uniqueness()
    {
        User::factory()->create(['email' => 'test@example.com']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create(['email' => 'test@example.com']);
    }

    /** @test */
    public function it_validates_cpf_uniqueness()
    {
        User::factory()->create(['cpf' => '12345678901']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create(['cpf' => '12345678901']);
    }
}