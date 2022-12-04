<?php

namespace Tests\Feature\API\Controllers\Clients;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PostClientsShouldValidateWhenTryCreateAInvalidClientTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_post_clients_should_validate_when_try_create_a_invalid_client()
    {

        $response = $this->postJson('/api/clients', []);

        $response->assertStatus(422);

        $response->assertJson(function (AssertableJson $json) {
            $json->hasAll(['message', 'errors', 'errors.description', 'errors.address']);

            $json->where('errors.description.0', 'Este campo é obrigatório')
                ->where('errors.address.0', 'Este campo é obrigatório');;
        });
    }
}
