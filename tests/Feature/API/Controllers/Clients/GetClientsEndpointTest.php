<?php

namespace Tests\Feature\API\Controllers\Clients;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GetClientsEndpointTest extends TestCase
{

    public function test_get_clients_endpoint()
    {
        $clients = Client::factory(3)->create();

        $response = $this->getJson('/api/clients');

        $response->assertStatus(200);
        $response->assertJsonCount(3);

        $response->assertJson(function (AssertableJson $json) use ($clients) {
            $json->whereAllType([
                '0.id' => 'string',
                '0.description' => 'string',
                '0.address' => 'string',
            ]);

            $json->hasAll(['0.id', '0.description', '0.address']);

            $client = $clients->first();
            $json->whereAll([
                '0.id' => $client->id,
                '0.description' => $client->description,
                '0.address' => $client->address,
            ]);
        });
    }
}
