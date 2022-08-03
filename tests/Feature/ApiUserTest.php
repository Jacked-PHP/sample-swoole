<?php

namespace Tests\Feature;

use Faker\Factory;
use MyCode\DB\Models\User;
use Tests\TestCase;
use Tests\Traits\SwooleAppTestTrait;

class ApiUserTest extends TestCase
{
    use SwooleAppTestTrait;

    protected function setUp(): void
    {
        $this->setUpApp($this->getApp());

        $this->faker = Factory::create();
    }

    private function generateToken(): string
    {
        $user = User::find(1);

        $this->runCommand('generate:jwt-token', [
            '--name' => 'token-name',
            '--user' => $user->email,
            '--quiet' => null,
        ]);

        $user->refresh();
        return $user->tokens->first()->token;
    }

    public function test_can_get_users(): void
    {
        $token = $this->generateToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/users');
        $response->assertOk();

        $bodyStream = $response->getBody();
        $bodyStream->rewind();
        $data = json_decode($bodyStream->getContents(), true);

        $this->assertArrayHasKey('data', $data);

        $firstRecord = current($data['data']);
        $this->assertArrayHasKey('id', $firstRecord);
        $this->assertArrayHasKey('name', $firstRecord);
        $this->assertArrayHasKey('email', $firstRecord);
    }

    public function test_cant_get_users_without_authorization()
    {
        $response = $this->get('/api/users');
        $response->assertStatus(401);
    }

    public function test_can_create_user()
    {
        $token = $this->generateToken();

        $userData = User::factory()->make();

        $this->assertCount(0, User::where('email', $userData->email)->get());

        $response = $this
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])
            ->sPostJson('/api/users', $userData->toArray());
        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'name' => $userData->name,
                'email' => $userData->email,
            ]
        ]);

        $this->assertCount(1, User::where('email', $userData->email)->get());
    }

    public function test_cant_create_user_without_authorization()
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
        ];

        $this->assertCount(0, User::where('email', $data['email'])->get());

        $response = $this->post('/api/users', $data);
        $response->assertStatus(401);

        $this->assertCount(0, User::where('email', $data['email'])->get());
    }

    public function test_can_update_user()
    {
        $token = $this->generateToken();

        $newName = $this->faker->name;
        $userData = User::factory()->create();

        $this->assertCount(1, User::where('email', $userData->email)->where('name', $userData->name)->get());
        $this->assertCount(0, User::where('email', $userData->email)->where('name', $newName)->get());

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->sPutJson('/api/users/' . $userData->id, [
                'name' => $newName,
            ]);
        $body = $response->getBody();
        $body->rewind();
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'name' => $newName,
                'email' => $userData->email,
            ]
        ]);

        $this->assertCount(0, User::where('email', $userData->email)->where('name', $userData->name)->get());
        $this->assertCount(1, User::where('email', $userData->email)->where('name', $newName)->get());
    }

    public function test_cant_update_email()
    {
        $token = $this->generateToken();

        $newEmail = $this->faker->email;
        $userData = User::factory()->create();

        $this->assertCount(1, User::where('email', $userData->email)->get());
        $this->assertCount(0, User::where('email', $newEmail)->get());

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->sPutJson('/api/users/' . $userData->id, [
                'email' => $newEmail,
            ]);
        $response->assertStatus(422);
        $response->getBody()->rewind();
        $response->assertJson([
            'success' => false,
        ]);

        $this->assertCount(1, User::where('email', $userData->email)->get());
        $this->assertCount(0, User::where('email', $newEmail)->get());
    }

    public function test_cant_update_user_without_authorization()
    {
        $newName = $this->faker->name;
        $userData = User::factory()->create();

        $response = $this->put('/api/users/' . $userData->id, [
            'name' => $newName,
        ]);
        $response->assertStatus(401);

        $this->assertCount(1, User::where('email', $userData->email)->where('name', $userData->name)->get());
    }

    public function test_can_delete_user()
    {
        $token = $this->generateToken();

        $userData = User::factory()->create();

        $this->assertCount(1, User::where('email', $userData->email)->get());

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->delete('/api/users/' . $userData->id);
        $response->assertStatus(204);
        $response->assertJson([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);

        $this->assertCount(0, User::where('email', $userData->email)->get());
    }

    public function test_cant_delete_user_without_authorization()
    {
        $userData = User::factory()->create();

        $this->assertCount(1, User::where('email', $userData->email)->get());

        $response = $this->delete('/api/users/' . $userData->id);
        $response->assertStatus(401);

        $this->assertCount(1, User::where('email', $userData->email)->get());
    }
}