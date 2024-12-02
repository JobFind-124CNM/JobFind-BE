<?php

namespace App\Services;

use App\Models\Position;
use Illuminate\Validation\ValidationException;
use Validator;

class PositionService
{
    protected $position;

    public function __construct(Position $position)
    {
        $this->position = $position;
    }

    public function findAll() {
        return Position::all();
    }

    public function getAllPositions()
    {
        return $this->position->all();
    }

    public function getPositionById($id)
    {
        return $this->position->find($id);
    }

    public function createPosition($params)
    {
        $this->validateParams($params);
        return $this->position->create($params);
    }

    public function updatePosition($id, $params)
    {
        $this->validateParams($params);
        $position = $this->getPositionById($id);
        if ($position) {
            $position->update($params);
            return $position;
        }
        return null;
    }

    public function deletePosition($id)
    {
        $position = $this->getPositionById($id);
        if ($position) {
            $position->delete();
            return true;
        }

        return false;
    }
    public function getAllPositionsPaginated($pageSize = 5, $page = 1, $search = null)
    {
        $query = Position::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->paginate($page, ['*'], 'page', $pageSize);
    }
    
    private function validateParams($params)
    {
        $validator = Validator::make($params, [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
