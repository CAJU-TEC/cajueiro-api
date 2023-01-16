<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use DomainException;
use Illuminate\Http\Request;

class AuthUpdateController extends Controller
{
    private User $user;

    //
    public function __invoke(Request $request, $id)
    {
        try {
            $this->user = User::with(['roles', 'permissions'])->find($id);

            $this->user->update($request->only([
                'name',
                'email',
            ]));

            $this->updatePermissions([
                'permissions' => $request->permissions
            ]);

            $this->updateRoles([
                'roles' => $request->roles
            ]);

            return response()->json($this->user, 200);
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }
    }

    private function updatePermissions(array $config): void
    {
        $permissions = Permission::whereIn('id', $config['permissions'])
            ->get()
            ->map(fn ($e) => $e->name);
        $this->user->syncPermissions($permissions);
    }

    private function updateRoles(array $config): void
    {
        $roles = Role::whereIn('id', $config['roles'])
            ->get()
            ->map(fn ($e) => $e->name);
        $this->user->syncRoles($roles);
    }
}
