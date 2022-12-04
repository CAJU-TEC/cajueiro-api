<?php

namespace Tests\Feature\API\Controllers\Clients;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PostClientsEndpointTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_post_clients_endpoint()
    {
        $client = Client::factory(1)->makeOne()->toArray();

        $response = $this->postJson('/api/clients', $client);

        $response->assertStatus(201);

        $response->assertJson(function (AssertableJson $json) use ($client) {
            $json->hasAll(['id', 'description', 'address', 'created_at', 'updated_at'])->etc();
            $json->whereAll([
                // 'id' => $client['id'],
                'description' => $client['description'],
                'address' => $client['address'],
            ])->etc();
        });
    }
}
