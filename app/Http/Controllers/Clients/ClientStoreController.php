<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Client\ClientStoreRequest;
use App\Models\Client;

class ClientStoreController extends Controller
{
    public function __construct(private Client $client) {}

    public function __invoke(ClientStoreRequest $request)
    {
        $client = $this->client->create($request->only([
            'corporate_id',
            'first_name',
            'last_name',
            'address'
        ]));

        if ($request->email) {
            $client->email()->updateOrCreate([
                'emailable_id' => $client->id,
            ], [
                'description' => $request->email
            ]);
        }

        if ($request->image) {
            $name = $client->id . '.' . explode(
                '/',
                explode(
                    ':',
                    substr(
                        $request->image,
                        0,
                        strpos($request->image, ';')
                    )
                )[1]
            )[1];
            $uri = storage_path('app/public/images/') . $name;
            \Image::make($request->image)->save($uri);

            $client->image()->updateOrCreate([
                'uri' => $name
            ]);
        }

        return response()->json($client, 201);
    }
}
