<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->query('p', 1);
            $size = $request->query('s', 5);
            $search = $request->query('q', null);
            $categories = $this->categoryService->getAllCategoriesPaginated($perPage, $size, $search);
            return response()->json([
                'message' => 'Catogories retrieved successfully',
                'data' => $categories->items(),
                'pagination' => [
                    'total' => $categories->total(),
                    'size' => $categories->perPage(),
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'from' => $categories->firstItem(),
                    'to' => $categories->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve catogories.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $category = $this->categoryService->createCategory($request->all());
            return response()->json([
                'message' => 'Category created successfully.',
                'data' => $category,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = $this->categoryService->getCategoryById($id);

            if (!$category) {
                return response()->json([
                    'message' => 'Category not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Category retrieved successfully.',
                'data' => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $category = $this->categoryService->updateCategory($id, $request->all());

            if (!$category) {
                return response()->json([
                    'message' => 'Category not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Category updated successfully.',
                'data' => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->categoryService->deleteCategory($id);

            if (!$deleted) {
                return response()->json([
                    'message' => 'Category not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Category deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
