<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Validation\ValidationException;
use Validator;

class CompanyService
{
    protected $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
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
        return $this->company->create($params);
    }

    public function updateCompany($id, $params)
    {
        $this->validateParams($params);
        $company = $this->getCompanyById($id);

        if ($company) {
            $company->update($params);
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
            'logo' => 'required|string|max:255',
            'thumbnail' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'amount_of_employee' => 'required|int',
            'tax_number' => 'required|string|max:20',
            'status' => 'required',
            'website' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
