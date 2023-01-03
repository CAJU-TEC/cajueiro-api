<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;

class ClientShowController extends Controller
{
    //
    public function __construct(private Client $client)
    {
    }

    public function __invoke($id)
    {
        $client = $this->client
            ->with(['email'])
            ->findOrFail($id);
        return response()->json($client, 200);
    }
}
