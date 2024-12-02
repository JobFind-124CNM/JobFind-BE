<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Validation\ValidationException;
use Validator;
use App\Services\DropboxService;
use App\Services\CloudinaryService;

class CompanyService
{
    protected $company;
    protected $dropBoxService;
    protected $cloudinaryService;

    public function __construct(Company $company, DropboxService $dropboxService, CloudinaryService $cloudinaryService)
    {
        $this->company = $company;
        $this->dropboxService = $dropboxService;
        $this->cloudinaryService = $cloudinaryService;
    }

    public function getAllCompanies()
    {
        return $this->company->all();
    }

    public function getCompanyById($id)
    {
        return $this->company->find($id);
    }

     public function createCompany($params)
    {
        $this->validateParams($params);

        $companyData = [
            'name' => $params['name'],
            'description' => $params['description'],
            'website' => $params['website'],
            'amount_of_employee' => $params['amountOfEmployees'],
            'tax_number' => $params['tax_number'],
            'status' => "Pending",
            'address' => $params['address'],
            'phone' => $params['phone'],
            'email' => $params['email'],
        ];

        if (isset($params['logo'])) {
            $companyData['logo'] = $this->cloudinaryService->uploadFile($params['logo']);
        }

        if (isset($params['thumbnail'])) {
            $companyData['thumbnail'] = $this->cloudinaryService->uploadFile($params['thumbnail']);
        }

        if (isset($params['contract'])) {
            $companyData['contract'] = $this->dropboxService->uploadFile($params['contract']);
        }

        $company = Company::create($companyData);

        return $company;
    }

    public function updateCompanyStatus($companyId, $status)
    {
        $company = Company::findOrFail($companyId);
        $company->status = $status;
        $company->save();

        return $company;
    }

    public function getPendingCompanies($pageSize = 5, $page = 1)
    {
        $companies = Company::whereIn('status', ['Pending', 'Rejected'])
            ->paginate($pageSize, ['*'], 'page', $page);

        return $companies;
    }

    public function updateCompany($id, $params)
    {
        $this->validateParams($params);

        $companyData = [
            'name' => $params['name'],
            'description' => $params['description'],
            'website' => $params['website'],
            'amount_of_employee' => $params['amountOfEmployees'],
            'tax_number' => $params['tax_number'],
            'status' => "Pending",
            'address' => $params['address'],
            'phone' => $params['phone'],
            'email' => $params['email'],
        ];

        if (isset($params['logo'])) {
            $companyData['logo'] = $this->cloudinaryService->uploadFile($params['logo']);
        }

        if (isset($params['thumbnail'])) {
            $companyData['thumbnail'] = $this->cloudinaryService->uploadFile($params['thumbnail']);
        }

        if (isset($params['contract'])) {
            $companyData['contract'] = $this->dropboxService->uploadFile($params['contract']);
        }

        $company = $this->getCompanyById($id);

        if ($company) {
            $company->update($companyData);
            return $company;
        }

        return null;
    }

    public function updateStatusCompany($id, $status)
    {

        $this->validateStatus($status);

        $company = $this->getCompanyById($id);

        if ($company) {
            $company->status = $status;
            $company->save();

            return $company;
        }

        return null;
    }


    public function deleteCompany($id)
    {
        $company = $this->getCompanyById($id);

        if ($company) {
            $company->delete();
            return true;
        }

        return false;
    }
    public function getAllCompaniesPaginated($pageSize = 5, $page = 1, $search = null)
    {
        $query = Company::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->paginate($page, ['*'], 'page', $pageSize);
    }

    private function validateParams($params)
    {
        $validator = Validator::make($params, [
            'name' => 'required|string|max:255',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:2048',
            'thumbnail' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:2048',
            'contract' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'description' => 'required|string',
            'amountOfEmployees' => 'required|integer',
            'tax_number' => 'required|string|max:20',
            'website' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
    
    private function validateStatus($status)
    {
        if (!in_array($status, ['Active', 'Inactive', 'Pending'])) {
            throw new \InvalidArgumentException("Invalid status value");
        }
    }

}
