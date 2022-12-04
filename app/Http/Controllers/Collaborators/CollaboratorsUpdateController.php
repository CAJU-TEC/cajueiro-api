<?php

namespace App\Http\Controllers\Collaborators;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Collaborator\CollaboratorUpdateRequest;
use App\Models\Collaborator;
use DomainException;
use Illuminate\Http\Request;

class CollaboratorsUpdateController extends Controller
{
    public function __invoke(CollaboratorUpdateRequest $request, $id)
    {
        try {
            $client = Collaborator::with('image')->find($id);
            $client->update($request->only([
                'first_name',
                'last_name',
                'formation',
                'birth',
                'entrance',
                'egress',
                'cpf',
                'cnpj',
                'email',
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
