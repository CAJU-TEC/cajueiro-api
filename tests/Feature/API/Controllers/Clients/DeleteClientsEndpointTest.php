<?php

namespace Tests\Feature\API\Controllers\Clients;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeleteClientsEndpointTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_delete_clients_endpoint()
    {
        $client = Client::factory(1)->createOne();
        $response = $this->deleteJson('/api/clients/' . $client->id);

        $response->assertStatus(204);
    }
}
