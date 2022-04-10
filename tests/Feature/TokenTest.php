<?php

namespace Tests\Feature;

use App\Models\User;
use http\Env\Response;
use Tests\TestCase;

class TokenTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
//    public function testGetToken()
//    {
//        $response = $this->post('api/v1/getToken');
//
//        $response->assertStatus(200);
//    }

    public function testGenerateBearerToken()
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

        $this->assertSame(200, $response->status());

        $this->assertIsString(
            json_decode($response->getContent(), true)['token']
        );
    }
}
