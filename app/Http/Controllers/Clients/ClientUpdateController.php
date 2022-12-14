<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Client\ClientUpdateRequest;
use App\Models\Client;
use DomainException;

class ClientUpdateController extends Controller
{
    public function __invoke(ClientUpdateRequest $request, $id)
    {
        try {
            $client = Client::with(['image', 'email'])->find($id);
            $client->update($request->only([
                'corporate_id',
                'first_name',
                'last_name',
                'address'
            ]));

            if ($request->email) {
                $client->email()->updateOrCreate([
                    'emailable_id' => $id,
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

            return response()->json($client, 200);
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }
    }
}
