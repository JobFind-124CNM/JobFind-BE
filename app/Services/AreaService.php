<?php

namespace App\Services;

use App\Models\Area;

use Illuminate\Validation\ValidationException;
use Validator;

class AreaService
{
    protected $area;

    public function __construct(Area $area)
    {
        $this->area = $area;
    }

    public function getAllAreas()
    {
        return $this->area->all();
    }

    public function getAreaById($id)
    {
        return $this->area->find($id);
    }

    public function createArea($params)
    {
        $this->validateParams($params);
        return $this->area->create($params);
    }

    public function updateArea($id, $params)
    {
        $this->validateParams($params);
        $area = $this->getAreaById($id);
        if ($area) {
            $area->update($params);
            return $area;
        }
        return null;
    }

    public function deleteArea($id)
    {
        $area = $this->getAreaById($id);
        if ($area) {
            $area->delete();
            return true;
        }
        return false;
    }

    public function getAllAreasPaginated($pageSize = 5, $page = 1, $search = null)
    {
        $query = Area::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->paginate($page, ['*'], 'page', $pageSize);
    }
    
    private function validateParams(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
