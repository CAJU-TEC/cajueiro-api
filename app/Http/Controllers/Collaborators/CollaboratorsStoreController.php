<?php

namespace App\Http\Controllers\Collaborators;

use App\Events\Collaborators\CollaboratorsSuccessful;
use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Models\User;
use App\Supports\DatesTimes\DateSupport;
use App\Supports\StringClear;
use Illuminate\Http\Request;

class CollaboratorsStoreController extends Controller
{
    public function __construct(private Collaborator $collaborator)
    {
    }

    public function __invoke(Request $request)
    {
        // $user = CollaboratorsSuccessful::dispatch($request);
        if (empty($request->cpf) && empty($request->cnpj))
            return response()->json([
                'Preencha algum documento, CPF/CNPJ.'
            ], 401);

        if (!empty($request->cpf))
            $request->cpf = (new StringClear)($request->cpf);

        if (!empty($request->cnpj))
            $request->cnpj = (new StringClear)($request->cnpj);

        $collaborator = $this->collaborator->updateOrCreate(
            [
                'cpf' => $request->cpf
            ],
            $request->only([
                'jobplan_id',
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
                'number',
                'pix'
            ]),
        );

        if ($request->email) {
            $collaborator->email()->updateOrCreate([
                'description' => $request->email
            ]);

            $emailReturn = User::where('email', $request->email)->get();
            if (count($emailReturn) == 0) {
                $payload = User::create([
                    'email' => $request->email,
                    'name' => $collaborator->full_name,
                    'password' => (new DateSupport)->formatPasswordBrazil($collaborator->birth)
                ]);
                $collaborator->update([
                    'user_id' => $payload->id
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

        $collaborator = Collaborator::with([
            'email',
            'user'
        ])->find($collaborator->id);

        return response()->json($collaborator, 201);
    }
}
