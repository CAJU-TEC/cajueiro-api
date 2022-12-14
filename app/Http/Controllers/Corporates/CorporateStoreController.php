<?php

namespace App\Http\Controllers\Corporates;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Corporate\CorporateStoreRequest;
use App\Models\Corporate;
use Illuminate\Http\Request;

class CorporateStoreController extends Controller
{
    public function __construct(private Corporate $corporate)
    {
    }

    public function __invoke(CorporateStoreRequest $request)
    {
        $corporate = $this->corporate->create($request->only([
            'first_name',
            'last_name',
            'address'
        ]));

        if ($request->email) {
            $corporate->email()->updateOrCreate([
                'emailable_id' => $corporate->id,
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

            $corporate->image()->updateOrCreate([
                'uri' => $name
            ]);
        }

        return response()->json($corporate, 201);
    }
}
