<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientDestroyController extends Controller
{
    //
    public function __construct(private Client $client)
    {
    }

    public function __invoke($id)
    {
        $client = $this->client->find($id);
        $client->delete();

        return response()->json([], 204);
    }
}
