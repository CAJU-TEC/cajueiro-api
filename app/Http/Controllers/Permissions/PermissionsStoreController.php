<?php

namespace App\Http\Controllers\Permissions;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;

class PermissionsStoreController extends Controller
{
    public function __construct(private Role $groups) {}

    public function __invoke(Request $request)
    {
        try {
            $groups = $this->groups->create($request->only([
                'name',
                'display_name',
                'description',
            ]));

            return response()->json($groups, 201);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
