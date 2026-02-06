<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\CreateUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    private CreateUser $action;

    public function test_it_creates_a_user_with_valid_data(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $user = $this->action->execute($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    }

    public function test_it_hashes_the_password_when_creating_user(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'plain-password',
        ];

        $user = $this->action->execute($data);

        $this->assertTrue(Hash::check('plain-password', $user->password));
        $this->assertNotEquals('plain-password', $user->password);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(CreateUser::class);
    }
}
