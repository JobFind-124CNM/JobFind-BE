<?php

namespace App\Http\Controllers;

use App\Services\CompanyService;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }


    public function index()
    {
        try {
            $companies = $this->companyService->getAllCompanies();
            return response()->json([
                'message' => 'Companies retrieved successfully.',
                'data' => $companies,
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
        try {
            $company = $this->companyService->createCompany($request->all());
            return response()->json([
                'message' => 'Company created successfully.',
                'data' => $company,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create company.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
            $company = $this->companyService->updateCompany($id, $request->all());

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
}
