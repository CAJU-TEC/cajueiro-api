<?php

namespace App\Http\Controllers\Corporates;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Corporate\CorporateUpdateRequest;
use App\Models\Corporate;
use DomainException;

class CorporateUpdateController extends Controller
{
    public function __invoke(CorporateUpdateRequest $request, $id)
    {
        try {
            $corporate = Corporate::with(['image', 'email'])->find($id);
            $corporate->update($request->only([
                'initials',
                'first_name',
                'last_name',
                'address'
            ]));

            if ($request->email) {
                $corporate->email()->updateOrCreate([
                    'emailable_id' => $id,
                ], [
                    'description' => $request->email
                ]);
            }

            if ($request->image) {
                $name = $corporate->id . '.' . explode(
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

                $corporate->image()->updateOrCreate(
                    ['imageable_id' => $corporate->id],
                    ['uri' => $name]
                );
            }

            return response()->json($corporate, 200);
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }
    }
}
