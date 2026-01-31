<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'               => fake()->name(),
            'email'              => fake()->unique()->safeEmail(),
            'email_verified_at'  => now(),
            'password'           => static::$password ??= Hash::make('password'),
            'role'               => 'learner',
            'remember_token'     => Str::random(10),
            'otp_code'           => null,
            'otp_expires_at'     => null,
            'otp_attempts'       => 0,
            'otp_request_count'  => 0,
            'otp_last_sent_at'   => null,
            'otp_locked_at'      => null,
            'otp_request_locked_at' => null,
            'otp_verified_at'    => now(),
            'otp_used_at'        => null,
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
     * Indicate that the user has not completed OTP verification (for testing OTP flow).
     */
    public function unverifiedOtp(): static
    {
        return $this->state(fn (array $attributes) => [
            'otp_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is fully verified (email + OTP). Ready to log in. Default factory users are already OTP verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at'  => $attributes['email_verified_at'] ?? now(),
            'otp_verified_at'    => now(),
            'otp_code'           => null,
            'otp_expires_at'     => null,
            'otp_attempts'       => 0,
            'otp_request_count'  => 0,
            'otp_last_sent_at'   => null,
            'otp_locked_at'      => null,
            'otp_request_locked_at' => null,
        ]);
    }

    /**
     * Indicate that the user has the admin role.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Indicate that the user has the instructor role.
     */
    public function instructor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'instructor',
        ]);
    }
}
