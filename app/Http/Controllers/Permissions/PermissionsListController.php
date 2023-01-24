<?php

namespace App\Http\Controllers\Permissions;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Exception;
use Illuminate\Http\Request;

class PermissionsListController extends Controller
{
    public function __construct(private Permission $permissions)
    {
    }

    //
    public function __invoke()
    {
        try {
            return response()->json($this->permissions->latest()->get(), 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
