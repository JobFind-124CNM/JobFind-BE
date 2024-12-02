<?php

namespace App\Http\Controllers;

use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }


    public function index(Request $request)
    {
        try {
            $perPage = $request->query('p', 1);
            $size = $request->query('s', 5);
            $search = $request->query('q', null);
            $companies = $this->companyService->getAllCompaniesPaginated($perPage, $size, $search);
            return response()->json([
                'message' => 'Companies retrieved successfully',
                'data' => $companies->items(),
                'pagination' => [
                    'total' => $companies->total(),
                    'size' => $companies->perPage(),
                    'current_page' => $companies->currentPage(),
                    'last_page' => $companies->lastPage(),
                    'from' => $companies->firstItem(),
                    'to' => $companies->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve companies.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function store(Request $request)
    {
        $params = $request->all();

        if ($request->hasFile('logo')) {
            $params['logo'] = $request->file('logo');
        }

        if ($request->hasFile('thumbnail')) {
            $params['thumbnail'] = $request->file('thumbnail');
        }

        if ($request->hasFile('contract')) {
            $params['contract'] = $request->file('contract');
        }

        $company = $this->companyService->createCompany($params);

        return response()->json([
            'code' => 201,
            'message' => 'Company created successfully',
            'data' => $company
        ], 201);
    }

    public function getCompanyByUser() {
        $user = Auth::user();

        $company = $this->companyService->getCompanyById($user->company_id);
        return response()->json([
            'message' => 'Company retrieved successfully',
            'data' => $company
        ], 200);
    }

    public function updateCompanyStatus(Request $request, $companyId)
    {
        $status = $request->input('status');

        $company = $this->companyService->updateCompanyStatus($companyId, $status);

        return response()->json([
            'code' => 200,
            'message' => 'Company status updated successfully',
            'data' => $company
        ], 200);
    }

    public function getPendingCompanies(Request $request)
    {
        $pageSize = $request->query('s', 5);
        $page = $request->query('p', 1);

        $companies = $this->companyService->getPendingCompanies($pageSize, $page);

        return response()->json([
            'code' => 200,
            'message' => 'Pending companies retrieved successfully',
            'data' => $companies->items(),
            'pagination' => [
                'total' => $companies->total(),
                'size' => $companies->perPage(),
                'current_page' => $companies->currentPage(),
                'last_page' => $companies->lastPage(),
                'from' => $companies->firstItem(),
                'to' => $companies->lastItem(),
            ]
        ], 200);
    }

    public function show($id)
    {
        try {
            $company = $this->companyService->getCompanyById($id);

            if (!$company) {
                return response()->json([
                    'message' => 'Company not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Company retrieved successfully.',
                'data' => $company,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve company.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $params = $request->all();

            if ($request->hasFile('logo')) {
                $params['logo'] = $request->file('logo');
            }

            if ($request->hasFile('thumbnail')) {
                $params['thumbnail'] = $request->file('thumbnail');
            }

            if ($request->hasFile('contract')) {
                $params['contract'] = $request->file('contract');
            }

            $company = $this->companyService->updateCompany($id, $params);

            if (!$company) {
                return response()->json([
                    'message' => 'Company not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Company updated successfully.',
                'data' => $company,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update company.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->companyService->deleteCompany($id);

            if (!$deleted) {
                return response()->json([
                    'message' => 'Company not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Company deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete company.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateStatus(Request $request,$id)
    {
        $request->validate([
            'status' => 'required|string|in:Active,Inactive,Pending',
        ]);

        $company = $this->companyService->updateStatusCompany($id, $request->status);

        if ($company) {
            return response()->json([
                'message' => 'Company status updated successfully',
            ], 200);
        }

        return response()->json([
            'message' => 'Company not found'
        ], 404);
    }
}
