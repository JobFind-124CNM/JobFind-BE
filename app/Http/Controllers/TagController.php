<?php

namespace App\Http\Controllers;

use App\Services\TagService;
use Illuminate\Http\Request;

class TagController extends Controller
{
    protected $tagService;

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    // public function index()
    // {
    //     $tags = $this->tagService->getAllTags();
    //     return response()->json([
    //         'code' => 200,
    //         'message' => 'Tags retrieved successfully',
    //         'data' => $tags
    //     ], 200);
    // }

    public function index(Request $request)
    {
        $perPage = $request->query('p', 1);
        $size = $request->query('s', 5);
        $search = $request->query('q', null);
        $tags = $this->tagService->getAllTagsPaginated($perPage, $size, $search);
        return response()->json([
            'code' => 200,
            'message' => 'Tags retrieved successfully',
            'data' => $tags->items(),
            'pagination' => [
                'total' => $tags->total(),
                'size' => $tags->perPage(),
                'current_page' => $tags->currentPage(),
                'last_page' => $tags->lastPage(),
                'from' => $tags->firstItem(),
                'to' => $tags->lastItem(),
            ]
        ], 200);
    }


    public function show($id)
    {
        $tag = $this->tagService->getTagById($id);
        return response()->json([
            'code' => 200,
            'message' => 'Tag retrieved successfully',
            'data' => $tag
        ], 200);
    }

    public function store(Request $request)
    {
        $tag = $this->tagService->createTag($request->all());
        return response()->json([
            'code' => 201,
            'message' => 'Tag created successfully',
            'data' => $tag
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $tag = $this->tagService->updateTag($id, $request->all());
        return response()->json([
            'code' => 200,
            'message' => 'Tag updated successfully',
            'data' => $tag
        ], 200);
    }

    public function destroy($id)
    {
        $this->tagService->deleteTag($id);
        return response()->json([
            'code' => 204,
            'message' => 'Tag deleted successfully',
            'data' => []
        ], 204);
    }
}