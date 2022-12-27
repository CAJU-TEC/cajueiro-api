<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientStoreSimpliedController extends Controller
{
    //
    public function __construct(private Client $client, private Email $email)
    {
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
        ]);
        if (empty($request->email)) return response()->json(['message' => 'E-mail não preenchido.'], 422);

        $email = $this->email
            ->where('description', $request->email)
            ->where('emailable_type', 'App\Models\Client')
            ->first();

        if (!empty($email)) return response()->json(['message' => 'Já existe um e-mail cadastrado.'], 422);

        $client = $this->client->create($request->only([
            'first_name',
            'last_name',
        ]));

        if ($request->email) {
            $client->email()->updateOrCreate([
                'emailable_id' => $client->id,
            ], [
                'description' => $request->email
            ]);
        }

        return response()->json($client, 201);
    }
}
