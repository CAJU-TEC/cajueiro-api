<?php

namespace App\Http\Controllers\Images;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;

class ImageDestroyController extends Controller
{
    //
    public function __invoke($id)
    {
        try {
            $image = Image::find($id);
            $image->destroy();
        } catch (\Throwable $th) {
            return response()->json($th->getMessage());
        }
    }
}
