<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Support\UserStatus;

class UserService 
{
    public function getAllUsers($pageSize = 5, $page = 1, $search = "")
    {
        $query = User::with('roles', 'company');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate($pageSize, ['*'], 'page', $page);
    }

    public function getUserById($userId)
    {
        return User::with('roles')->find($userId);
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);

        if ($user) {
            $user->is_deleted = true;
            $user->status = UserStatus::BANNED;
            $user->save();
        }

        return $user;
    }

    public function activeUser($userId)
    {
        $user = User::find($userId);

        if ($user) {
            $user->is_deleted = false;
            $user->status = UserStatus::ACTIVE;
            $user->save();
        }

        return $user;
    }

    public function addRolesToUser(int $userId, array $roleNames)
    {
        $user = User::findOrFail($userId);

        $currentRoles = $user->roles->pluck('name')->toArray();

        $rolesToAdd = array_diff($roleNames, $currentRoles);
        $rolesToRemove = array_diff($currentRoles, $roleNames);

        foreach ($rolesToRemove as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $user->roles()->detach($role);
            }
        }

        foreach ($rolesToAdd as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $user->roles()->attach($role);
        }
        
        $user->load('roles');

        return $user;
    }

    public function changeUserStatus(int $userId, string $status)
    {
        $user = User::findOrFail($userId);
        $user->status = $status;
        $user->save();

        return $user;
    }
}