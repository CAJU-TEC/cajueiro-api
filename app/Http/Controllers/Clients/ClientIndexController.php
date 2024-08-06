<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientIndexController extends Controller
{
    public function __construct(private Client $client)
    {
    }

    //
    public function __invoke()
    {
        return response()->json($this->client->with(['image', 'email', 'corporate'])->latest()->get(), 200);
    }
}
