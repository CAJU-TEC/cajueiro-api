<?php

namespace Tests\Feature\API\Controllers\Clients;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PatchClientsEndpointTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_patch_clients_endpoint()
    {
        $client = Client::factory(1)->createOne();

        $payload = [
            'description' => 'CAJU Tecnologia',
        ];

        $response = $this->patchJson('/api/clients/' . $client->id, $payload);

        $response->assertStatus(200);

        $response->assertJson(function (AssertableJson $json) use ($payload) {
            $json->hasAll(['id', 'description', 'address', 'created_at', 'updated_at'])->etc();
            $json->where('description', $payload['description']);
        });
    }
}
