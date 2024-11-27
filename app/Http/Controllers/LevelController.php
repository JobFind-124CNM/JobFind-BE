<?php

namespace App\Http\Controllers;

use App\Services\LevelService;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    protected $levelService;

    public function __construct(LevelService $levelService)
    {
        $this->levelService = $levelService;
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->query('p', 1);
            $size = $request->query('s', 5);
            $search = $request->query('q', null);
            $levels = $this->levelService->getAllLevelsPaginated($perPage, $size, $search);
            return response()->json([
                'message' => 'Levels retrieved successfully',
                'data' => $levels->items(),
                'pagination' => [
                    'total' => $levels->total(),
                    'size' => $levels->perPage(),
                    'current_page' => $levels->currentPage(),
                    'last_page' => $levels->lastPage(),
                    'from' => $levels->firstItem(),
                    'to' => $levels->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve levels.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $level = $this->levelService->createLevel($request->all());
            return response()->json([
                'message' => 'Level created successfully.',
                'data' => $level,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create level.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $level = $this->levelService->getLevelById($id);

            if (!$level) {
                return response()->json([
                    'message' => 'Level not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Level retrieved successfully.',
                'data' => $level,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve level.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $level = $this->levelService->updateLevel($id, $request->all());

            if (!$level) {
                return response()->json([
                    'message' => 'Level not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Level updated successfully.',
                'data' => $level,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update level.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->levelService->deleteLevel($id);

            if (!$deleted) {
                return response()->json([
                    'message' => 'Level not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Level deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete level.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
