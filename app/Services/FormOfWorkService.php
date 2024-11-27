<?php

namespace App\Services;

use App\Models\FormOfWork;
use Illuminate\Validation\ValidationException;
use Validator;

class FormOfWorkService
{
    protected $formOfWork;

    public function __construct(FormOfWork $formOfWork)
    {
        $this->formOfWork = $formOfWork;
    }

    public function getAllFormOfWorks()
    {
        return $this->formOfWork->all();
    }

    public function getFormOfWorkById($id)
    {
        return $this->formOfWork->find($id);
    }

    public function createFormOfWork($params)
    {
        $this->validateParams($params);
        return $this->formOfWork->create($params);
    }

    public function updateFormOfWork($id, $params)
    {
        $this->validateParams($params);
        $formOfWork = $this->getFormOfWorkById($id);

        if ($formOfWork) {
            $formOfWork->update($params);
            return $formOfWork;
        }

        return null;
    }

    public function deleteFormOfWork($id)
    {
        $formOfWork = $this->getFormOfWorkById($id);

        if ($formOfWork) {
            $formOfWork->delete();
            return true;
        }

        return false;
    }
    public function getAllFormOfWorkPaginated($pageSize = 5, $page = 1, $search = null)
    {
        $query = FormOfWork::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->paginate($page, ['*'], 'page', $pageSize);
    }
    
    private function validateParams($params)
    {
        $validator = Validator::make($params, [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
