<?php

namespace Tests\Feature\API\Controllers\Clients;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GetSingleClientEndpointTest extends TestCase
{

    public function test_get_single_clients_endpoint()
    {
        $client = Client::factory(1)->createOne();

        $response = $this->getJson('/api/clients/' . $client->id);

        $response->assertStatus(200);

        $response->assertJson(function (AssertableJson $json) use ($client) {
            $json->whereAllType([
                'id' => 'string',
                'description' => 'string',
                'address' => 'string',
            ]);

            $json->hasAll(['id', 'description', 'address', 'created_at', 'updated_at'])->etc();

            $json->whereAll([
                'id' => $client->id,
                'description' => $client->description,
                'address' => $client->address,
            ]);
        });
    }
}
