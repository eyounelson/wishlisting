<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // REGISTER TESTS
    // ========================================

    public function test_register_validation_passes_with_valid_data(): void
    {
        $response = $this->postJson('/api/register', $this->validRegistrationData());

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'token',
            'data' => ['id', 'name', 'email', 'created_at'],
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    // Name field validation tests
    public function test_register_name_is_required(): void
    {
        $response = $this->postJson('/api/register', $this->validRegistrationData([
            'name' => '',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name' => 'The name field is required.']);
    }

    public function test_register_name_must_be_a_string(): void
    {
        $response = $this->postJson('/api/register', $this->validRegistrationData([
            'name' => ['not', 'a', 'string'],
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name' => 'The name field must be a string.']);
    }

    public function test_register_name_cannot_exceed_maximum_length(): void
    {
        $response = $this->postJson('/api/register', $this->validRegistrationData([
            'name' => str_repeat('a', 256),
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name' => 'The name field must not be greater than 255 characters.']);
    }

    // Email field validation tests
    public function test_register_email_is_required(): void
    {
        $response = $this->postJson('/api/register', $this->validRegistrationData([
            'email' => '',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email' => 'The email field is required.']);
    }

    public function test_register_email_must_be_valid_email_format(): void
    {
        $response = $this->postJson('/api/register', $this->validRegistrationData([
            'email' => 'not-an-email',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email' => 'The email field must be a valid email address.']);
    }

    public function test_register_email_must_be_unique(): void
    {
        $this->createUser(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/register', $this->validRegistrationData([
            'email' => 'existing@example.com',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email' => 'The email has already been taken.']);
    }

    // Password field validation tests
    public function test_register_password_is_required(): void
    {
        $response = $this->postJson('/api/register', $this->validRegistrationData([
            'password' => '',
            'password_confirmation' => '',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password' => 'The password field is required.']);
    }

    public function test_register_password_must_be_a_string(): void
    {
        $response = $this->postJson('/api/register', $this->validRegistrationData([
            'password' => 12345678,
            'password_confirmation' => 12345678,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password' => 'The password field must be a string.']);
    }

    public function test_register_password_must_meet_minimum_length(): void
    {
        $response = $this->postJson('/api/register', $this->validRegistrationData([
            'password' => 'short',
            'password_confirmation' => 'short',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password' => 'The password field must be at least 8 characters.']);
    }

    public function test_register_password_must_be_confirmed(): void
    {
        $response = $this->postJson('/api/register', $this->validRegistrationData([
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password' => 'The password field confirmation does not match.']);
    }

    // ========================================
    // LOGIN TESTS
    // ========================================

    public function test_login_validation_passes_with_valid_credentials(): void
    {
        $user = $this->createUser([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token',
            'data' => ['id', 'name', 'email', 'created_at'],
        ]);
    }

    // Email field validation tests
    public function test_login_email_is_required(): void
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email' => 'The email field is required.']);
    }

    public function test_login_email_must_be_valid_email_format(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email' => 'The email field must be a valid email address.']);
    }

    // Password field validation tests
    public function test_login_password_is_required(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password' => 'The password field is required.']);
    }

    public function test_login_password_must_be_a_string(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => ['array'],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password' => 'The password field must be a string.']);
    }

    // Authentication logic tests
    public function test_login_fails_with_incorrect_email(): void
    {
        $this->createUser([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email' => 'The provided credentials are incorrect.']);
    }

    public function test_login_fails_with_incorrect_password(): void
    {
        $this->createUser([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email' => 'The provided credentials are incorrect.']);
    }

    // ========================================
    // LOGOUT TESTS
    // ========================================

    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $response->assertStatus(204);

        // Verify token was revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }
}
