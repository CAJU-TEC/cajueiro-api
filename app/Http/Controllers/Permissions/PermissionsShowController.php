<?php

namespace App\Http\Controllers\Permissions;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Exception;

class PermissionsShowController extends Controller
{
    //
    public function __construct(private Role $group) {}

    public function __invoke($id)
    {
        try {
            $group = $this->group->findOrFail($id);
            return response()->json($group, 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
