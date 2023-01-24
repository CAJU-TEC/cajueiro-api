<?php

namespace App\Http\Controllers\Collaborators;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Collaborator\CollaboratorUpdateRequest;
use App\Models\Collaborator;
use App\Models\User;
use App\Supports\DatesTimes\DateSupport;
use DomainException;
use Illuminate\Http\Request;

class CollaboratorsUpdateController extends Controller
{
    public function __invoke(CollaboratorUpdateRequest $request, $id)
    {
        try {
            $collaborator = Collaborator::with(['image', 'email'])->find($id);
            $collaborator->update($request->only([
                'first_name',
                'last_name',
                'formation',
                'birth',
                'entrance',
                'egress',
                'cpf',
                'cnpj',
                'address',
                'postal',
                'number'
            ]));

            if ($request->email) {
                $collaborator->email()->updateOrCreate([
                    'emailable_id' => $id,
                ], [
                    'description' => $request->email
                ]);

                $emailReturn = User::where('email', $request->email)->first();
                if ($emailReturn) {
                    $emailReturn->updateOrCreate([
                        'email' => $request->email,
                    ], [
                        'email' => $request->email,
                        'password' => (new DateSupport)->formatPasswordBrazil($request->birth)
                    ]);
                }
            }

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

            return response()->json($collaborator, 200);
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }
    }
}
