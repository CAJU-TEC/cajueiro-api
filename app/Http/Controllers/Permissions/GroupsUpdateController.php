<?php

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\Controller;
use App\Models\Role;
use DomainException;
use Illuminate\Http\Request;

class GroupsUpdateController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        try {
            $groups = Role::find($id);
            $groups->update($request->only([
                'name',
                'display_name',
                'description',
            ]));

            return response()->json($groups, 200);
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
