<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use App\Services\DropboxService;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Firebase;
use Kreait\Firebase\Storage\Bucket;
use Tymon\JWTAuth\Exceptions\JWTException;

class PostController extends Controller
{
    protected $postService;
    protected $dropboxService;

    public function __construct(PostService $postService, DropboxService $dropboxService)
    {
        $this->postService = $postService;
        $this->dropboxService = $dropboxService;
    }

    public function index(Request $request)
    {
        $filters = [
            'category_id' => $request->query('category_id'),
            'company_id' => $request->query('company_id'),
            'area_id' => $request->query('area_id'),
            'form_of_work_ids' => $request->query('form_of_work_ids', []),
            'posted_within' => $request->query('posted_within', []),
            'salary_min' => $request->query('salary_min'),
            'salary_max' => $request->query('salary_max')
        ];

        $pageSize = $request->query('s', 5);
        $page = $request->query('p', 1);
        $search = $request->query('q', "");

        if ($this->hasFilters($filters)) {
            $posts = $this->postService->advancedSearchPosts($filters, $pageSize, $page, $search);
        } else {
            $posts = $this->postService->getAllPosts($pageSize, $page, $search);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Posts retrieved successfully',
            'data' => $posts->items(),
            'pagination' => [
                'total' => $posts->total(),
                'size' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ]
        ], 200);
    }

    private function hasFilters($filters)
    {
        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                return true;
            }
        }
        return false;
    }

    public function show($id)
    {
        return response()->json([
            'code' => 200,
            'message' => 'Post retrieved successfully',
            'data' => $this->postService->getPostById($id)
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $post = $this->postService->createPost($request->all(), $user->id);

            return response()->json([
                'code' => 201,
                'message' => 'Post created successfully',
                'data' => $post
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 403,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $post = $this->postService->updatePost($id, $request->all());

            return response()->json([
                'code' => 200,
                'message' => 'Post updated successfully',
                'data' => $post
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'code' => 422,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy($id)
    {
        $this->postService->deletePost($id);
        return response()->json([
            'code' => 204,
            'message' => 'Post deleted successfully'
        ], 204);
    }

    public function getByCategory($categoryId, Request $request)
    {
        $pageSize = $request->query('s', 10);
        $page = $request->query('p', 1);

        $posts = $this->postService->getPostsByCategory($categoryId, $pageSize, $page);

        return response()->json([
            'code' => 200,
            'message' => 'Posts retrieved successfully',
            'data' => $posts->items(),
            'pagination' => [
                'total' => $posts->total(),
                'size' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ]
        ], 200);
    }

    public function getByCompany($companyId, Request $request)
    {
        $pageSize = $request->query('s', 10);
        $page = $request->query('p', 1);

        $posts = $this->postService->getPostsByCompany($companyId, $pageSize, $page);

        return response()->json([
            'code' => 200,
            'message' => 'Posts retrieved successfully',
            'data' => $posts->items(),
            'pagination' => [
                'total' => $posts->total(),
                'size' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ]
        ], 200);
    }

    public function getByFormOfWork($formOfWorkId, Request $request)
    {
        $pageSize = $request->query('pageSize', 10);
        $page = $request->query('page', 1);

        $posts = $this->postService->getPostsByFormOfWork($formOfWorkId, $pageSize, $page);

        return response()->json([
            'code' => 200,
            'message' => 'Posts retrieved successfully',
            'data' => $posts->items(),
            'pagination' => [
                'total' => $posts->total(),
                'size' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ]
        ], 200);
    }

    public function getByArea($areaId, Request $request)
    {
        $pageSize = $request->query('pageSize', 10);
        $page = $request->query('page', 1);

        $posts = $this->postService->getPostsByArea($areaId, $pageSize, $page);

        return response()->json([
            'code' => 200,
            'message' => 'Posts retrieved successfully',
            'data' => $posts->items(),
            'pagination' => [
                'total' => $posts->total(),
                'size' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ]
        ], 200);
    }

    public function getByCategoryAndCompany($companyId, $categoryId, Request $request)
    {
        $pageSize = $request->query('pageSize', 10);
        $page = $request->query('page', 1);

        $posts = $this->postService->getPostsByCategoryAndCompany($categoryId, $companyId, $pageSize, $page);

        return response()->json([
            'code' => 200,
            'message' => 'Posts retrieved successfully',
            'data' => $posts->items(),
            'pagination' => [
                'total' => $posts->total(),
                'size' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ]
        ], 200);
    }

    public function getByTag($tagId, Request $request)
    {
        $pageSize = $request->query('pageSize', 10);
        $page = $request->query('page', 1);

        $posts = $this->postService->getPostsByTag($tagId, $pageSize, $page);

        return response()->json([
            'code' => 200,
            'message' => 'Posts retrieved successfully',
            'data' => $posts->items(),
            'pagination' => [
                'total' => $posts->total(),
                'size' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ]
        ], 200);
    }

    public function advancedSearch(Request $request)
    {
        $filters = [
            'category_id' => $request->query('category_id'),
            'company_id' => $request->query('company_id'),
            'area_id' => $request->query('area_id'),
            'form_of_work_ids' => $request->query('form_of_work_ids', []),
            'posted_within' => $request->query('posted_within', []),
            'salary_min' => $request->query('salary_min'),
            'salary_max' => $request->query('salary_max')
        ];

        $pageSize = $request->query('s', 10);
        $page = $request->query('p', 1);
        $search = $request->query('q', null);

        $posts = $this->postService->advancedSearchPosts($filters, $pageSize, $page, $search);

        return response()->json([
            'code' => 200,
            'message' => 'Posts retrieved successfully',
            'data' => $posts->items(),
            'pagination' => [
                'total' => $posts->total(),
                'size' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ]
        ], 200);
    }

    public function apply(Request $request, $postId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'cv' => 'file|mimes:pdf,doc,docx',
            'cover_letter' => 'nullable|string',
            'subject' => 'nullable|string',
        ]);

        $cvUrl = $this->dropboxService->uploadFile($request->file('cv'));
        // $cvUrl = "";

        $data = [
            'cv' => $cvUrl,
            'cover_letter' => $request->input('cover_letter'),
            'subject' => $request->input('subject'),
        ];

        $application = $this->postService->applyForJob($user, $postId, $data);

        return response()->json([
            'code' => 200,
            'message' => 'Job application submitted successfully',
            'data' => $application
        ], 200);
    }

    public function getUserAppliedPosts(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        $pageSize = $request->query('s', 10);
        $page = $request->query('p', 1);

        $result = $this->postService->getUserAppliedPosts($user->id, $pageSize, $page);

        return response()->json([
            'code' => 200,
            'message' => 'Posts retrieved successfully',
            'data' => $result['data'],
            'pagination' => $result['pagination']
        ], 200);
    }

    public function getPostUsers(Request $request, $postId)
    {
        $pageSize = $request->query('s', 10);
        $page = $request->query('p', 1);

        $result = $this->postService->getPostUsers($postId, $pageSize, $page);

        return response()->json([
            'code' => 200,
            'message' => 'Users retrieved successfully',
            'data' => $result['data'],
            'pagination' => $result['pagination']
        ], 200);
    }

    public function getCandidatesApplied($postId, $userId)
    {
        $post = Post::with(['users' => function ($query) use ($userId) {
            $query
                ->where('users.id', $userId)
                ->wherePivot('user_post.status', 'Applied')
                ->select('users.id', 'users.email', 'users.username', 'users.phone', 'user_post.cv', 
                        'user_post.subject', 'user_post.cover_letter', 
                        'user_post.status', 'user_post.created_at');
        }])->findOrFail($postId);

        if ($post->users->isEmpty()) {
            return response()->json([
                'code' => 404,
                'message' => 'Candidate not found for this post',
                'data' => []
            ]);
        }

        $pivotData = $post->users->map(function ($user) {
            return [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'username' => $user->username,
                    'phone' => $user->phone,
                ],
                'pivot' => [
                    'cv' => $user->pivot->cv,
                    'subject' => $user->pivot->subject,
                    'cover_letter' => $user->pivot->cover_letter,
                    'status' => $user->pivot->status,
                    'created_at' => $user->pivot->created_at,
                ]
            ];
        });

        return response()->json([
            'code' => 200,
            'message' => 'Candidate retrieved successfully',
            'data' => $pivotData
        ]);
    }

     public function getUserCompanyPosts(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        $pageSize = $request->query('s', 10);
        $page = $request->query('p', 1);

        $filters = [
            'keyword' => $request->query('q'),
            'area_id' => $request->query('area_id')
        ];

        $posts = $this->postService->getUserCompanyPosts($user->id, $pageSize, $page, $filters);

        return response()->json([
            'code' => 200,
            'message' => 'Posts retrieved successfully',
            'data' => $posts->items(),
            'pagination' => [
                'total' => $posts->total(),
                'size' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ]
        ], 200);
    }

    public function getPostsByKeywordAndCategoryAndArea(Request $request) {
        $keyword = $request->query('keyword');
        $categoryId = $request->query('category_id');
        $areaId = $request->query('area_id');
        $pageSize = $request->query('s', 10);
        $page = $request->query('p', 1);

        $posts = $this->postService->getPostsByKeywordAndCategoryAndArea($keyword, $categoryId, $areaId, $pageSize, $page);

        return response()->json([
            'code' => 200,
            'message' => 'Posts retrieved successfully',
            'data' => $posts->items(),
            'pagination' => [
                'total' => $posts->total(),
                'size' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ]
        ], 200);
    }

    public function getTopNewestPosts()
    {
        try {
            $posts = $this->postService->getTopNewestPosts();
    
            return response()->json([
                'code' => 200,
                'message' => 'Top 5 newest posts retrieved successfully',
                'data' => $posts
            ], 200);
        } catch(JWTException $e) {
            throw new JWTException('Unauthorized', 401);
        }
    }
}