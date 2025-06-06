<?php

namespace App\Http\Controllers\Permissions;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;

class PermissionsIndexController extends Controller
{
    public function __construct(private Role $groups) {}

    //
    public function __invoke()
    {
        try {
            return response()->json($this->groups->with(['users', 'permissions'])->latest()->paginate(10), 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
