<?php

namespace App\Http\Controllers\Corporates;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Corporate\CorporateStoreRequest;
use App\Models\Corporate;
use Exception;
use Illuminate\Http\Request;

class CorporateStoreController extends Controller
{
    public function __construct(private Corporate $corporate)
    {
    }

    public function __invoke(CorporateStoreRequest $request)
    {
        try {
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
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }


        return response()->json($corporate, 201);
    }
}
