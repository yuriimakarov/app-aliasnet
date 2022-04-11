<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class TokenTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetTokenRoute(): void
    {
        $response = $this->post('api/v1/getToken');

        $response->assertStatus(200);
    }

    /**
     * @return void
     */
    public function testGenerateBearerToken(): string
    {
        $user = User::factory()->create();

        $response = $this->call('POST',
            'api/v1/getToken',
            [],
            [],
            [],
            [
                'PHP_AUTH_USER' => $user->name,
                'PHP_AUTH_PW' => $user->password
            ]);
        $responseArray = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->status());

        $this->assertArrayHasKey('token', $responseArray);

        $this->assertIsString($responseArray['token']);

        return $responseArray['token'];
    }
}
