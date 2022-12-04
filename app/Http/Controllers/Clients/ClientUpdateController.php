<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use DomainException;
use Illuminate\Http\Request;

class ClientUpdateController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        try {
            $client = Client::with('image')->find($id);
            $client->update($request->only([
                'description',
                'email',
                'address'
            ]));

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
