<?php

namespace App\Http\Controllers;

use App\Services\FormOfWorkService;
use Illuminate\Http\Request;

class FormOfWorkController extends Controller
{
    protected $formOfWorkService;

    public function __construct(FormOfWorkService $formOfWorkService)
    {
        $this->formOfWorkService = $formOfWorkService;
    }

    public function index(Request $request)
    {

        try {
            $perPage = $request->query('p', 1);
            $size = $request->query('s', 5);
            $search = $request->query('q', null);
            $form_of_works = $this->formOfWorkService->getAllFormOfWorkPaginated($perPage, $size, $search);
            return response()->json([
                'message' => 'Form of works retrieved successfully',
                'data' => $form_of_works->items(),
                'pagination' => [
                    'total' => $form_of_works->total(),
                    'size' => $form_of_works->perPage(),
                    'current_page' => $form_of_works->currentPage(),
                    'last_page' => $form_of_works->lastPage(),
                    'from' => $form_of_works->firstItem(),
                    'to' => $form_of_works->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve form of works.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function store(Request $request)
    {
        try {
            $formOfWork = $this->formOfWorkService->createFormOfWork($request->all());
            return response()->json([
                'message' => 'The form of work created successfully.',
                'data' => $formOfWork,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create the form of work.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $formOfWork = $this->formOfWorkService->getFormOfWorkById($id);

            if (!$formOfWork) {
                return response()->json([
                    'message' => 'The form of work not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'The form of work retrieved successfully.',
                'data' => $formOfWork,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve the form of work.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $formOfWork = $this->formOfWorkService->updateFormOfWork($id, $request->all());

            if (!$formOfWork) {
                return response()->json([
                    'message' => 'The form of work not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'The form of work updated successfully.',
                'data' => $formOfWork,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update the form of work.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->formOfWorkService->deleteFormOfWork($id);

            if (!$deleted) {
                return response()->json([
                    'message' => 'The form of work not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'The form of work deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete the form of work.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
