<?php

namespace App\Services;

use App\Models\Level;
use Illuminate\Validation\ValidationException;
use Validator;

class LevelService
{
    protected $level;

    public function __construct(Level $level)
    {
        $this->level = $level;
    }

    public function getAllLevels()
    {
        return $this->level->all();
    }

    public function getLevelById($id)
    {
        return $this->level->find($id);
    }

    public function createLevel($params)
    {
        $this->validateParams($params);
        return $this->level->create($params);
    }

    public function updateLevel($id, $params)
    {
        $this->validateParams($params);
        $level = $this->getLevelById($id);

        if ($level) {
            $level->update($params);
            return $level;
        }

        return null;
    }

    public function deleteLevel($id)
    {
        $level = $this->getLevelById($id);

        if ($level) {
            $level->delete();
            return true;
        }

        return false;
    }
    public function getAllLevelsPaginated($pageSize = 5, $page = 1, $search = null)
    {
        $query = Level::query();

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