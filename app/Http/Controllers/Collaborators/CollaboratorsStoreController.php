<?php

namespace App\Http\Controllers\Collaborators;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use Illuminate\Http\Request;

class CollaboratorsStoreController extends Controller
{
    public function __construct(private Collaborator $collaborator)
    {
    }

    public function __invoke(Request $request)
    {
        $collaborator = $this->collaborator->create($request->only([
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
            $name = $collaborator->id . '.' . explode(
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

            $collaborator->image()->updateOrCreate([
                'uri' => $name
            ]);
        }

        return response()->json($collaborator, 201);
    }
}
