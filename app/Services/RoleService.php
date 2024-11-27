<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RoleService
{
    public function getAllRoles()
    {
        return Role::all();
    }

    public function getAllRolesPaginated($pageSize = 5, $page = 1, $search = null)
    {
        $query = Role::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->paginate($page, ['*'], 'page', $pageSize);
    }

    public function getRoleById($id)
    {
        return Role::findOrFail($id);
    }

    public function createRole(array $data)
    {
        $this->validateRole($data);
        return Role::create($data);
    }

    public function updateRole($id, array $data)
    {
        $this->validateRole($data);
        $role = Role::findOrFail($id);
        $role->update($data);
        return $role;
    }

    public function deleteRole($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return $role;
    }

    public function assignRoleToUser(User $user, $roleName)
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $user->roles()->attach($role->id);
    }

    private function validateRole(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}