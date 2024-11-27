<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $pageSize = $request->query('s', 5);
        $page = $request->query('p', 1);
        $search = $request->query('q', "");

        $users = $this->userService->getAllUsers($pageSize, $page, $search);

        return response()->json([
            'code' => 200,
            'message' => 'Users retrieved successfully',
            'data' => $users->items(),
            'pagination' => [
                'total' => $users->total(),
                'size' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ]
        ], 200);
    }

    public function destroy($id)
    {
        $user = $this->userService->deleteUser($id);
        return response()->json([
            'code' => 200,
            'message' => 'User deleted successfully',
            'data' => []
        ], 200);
    }

    public function active($id)
    {
        $user = $this->userService->activeUser($id);
        $user->load('roles');
        return response()->json([
            'code' => 200,
            'message' => 'User active successfully',
            'data' => [$user]
        ], 200);
    }

    public function addRoleToUser(Request $request, $userId)
    {
        $roleNames = $request->input('role_names', []);
        $user = $this->userService->addRolesToUser($userId, $roleNames);

        return response()->json([
            'status' => true,
            'message' => 'Roles added to user successfully',
            'data' => $user->roles
        ], 200);
    }

    public function changeStatus(Request $request, $userId)
    {
        $status = $request->input('status');
        $user = $this->userService->changeUserStatus($userId, $status);

        return response()->json([
            'status' => true,
            'message' => 'User status updated successfully',
            'data' => $user
        ], 200);
    }
}
