<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RoleService;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function all(Request $request)
    {
        $roles = $this->roleService->getAllRoles();
        return response()->json([
            'code' => 200,
            'message' => 'Roles retrieved successfully',
            'data' => $roles
        ], 200);
    }

    public function index(Request $request)
    {
        $perPage = $request->query('p', 1);
        $size = $request->query('s', 5);
        $search = $request->query('q', null);
        $roles = $this->roleService->getAllRolesPaginated($perPage, $size, $search);
        return response()->json([
            'code' => 200,
            'message' => 'Roles retrieved successfully',
            'data' => $roles->items(),
            'pagination' => [
                'total' => $roles->total(),
                'size' => $roles->perPage(),
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'from' => $roles->firstItem(),
                'to' => $roles->lastItem(),
            ]
        ], 200);
    }

    public function show($id)
    {
        $role = $this->roleService->getRoleById($id);
        return response()->json([
            'code' => 200,
            'message' => 'Role retrieved successfully',
            'data' => $role
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $role = $this->roleService->createRole($data);
        return response()->json([
            'code' => 201,
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $role = $this->roleService->updateRole($id, $data);
        return response()->json([
            'code' => 200,
            'message' => 'Role updated successfully',
            'data' => $role
        ], 200);
    }

    public function destroy($id)
    {
        $role = $this->roleService->deleteRole($id);
        return response()->json([
            'code' => 200,
            'message' => 'Role deleted successfully',
            'data' => $role
        ], 200);
    }

    public function assignRoleToUser(Request $request, $userId)
    {
        $roleName = $request->input('role_name');
        $user = User::findOrFail($userId);
        $this->roleService->assignRoleToUser($user, $roleName);
        return response()->json([
            'code' => 200,
            'message' => 'Role assigned to user successfully',
            'data' => []
        ], 200);
    }

    public function revokeRoleFromUser(Request $request, $userId)
    {
        $roleName = $request->input('role_name');
        $user = User::findOrFail($userId);
        $this->roleService->revokeRoleFromUser($user, $roleName);
        return response()->json([
            'code' => 200,
            'message' => 'Role revoked from user successfully',
            'data' => []
        ], 200);
    }

    public function getRolesForUser($userId)
    {
        $user = User::findOrFail($userId);
        $roles = $this->roleService->getRolesForUser($user);
        return response()->json([
            'code' => 200,
            'message' => 'Roles retrieved successfully',
            'data' => $roles
        ], 200);
    }
}
