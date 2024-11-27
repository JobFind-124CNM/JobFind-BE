<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Validation\ValidationException;
use Validator;

class CategoryService
{
    protected $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function getAllCategories()
    {
        return $this->category->all();
    }

    public function getCategoryById($id)
    {
        return $this->category->find($id);
    }

    public function createCategory($params)
    {
        $this->validateParams($params);
        return $this->category->create($params);
    }

    public function updateCategory($id, $params)
    {
        $this->validateParams($params);
        $category = $this->getCategoryById($id);

        if ($category) {
            $category->update($params);
            return $category;
        }
        return null;
    }

    public function deleteCategory($id)
    {
        $category = $this->getCategoryById($id);

        if ($category) {
            $category->delete();
            return true;
        }

        return false;
    }
    public function getAllCategoriesPaginated($pageSize = 5, $page = 1, $search = null)
    {
        $query = Category::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->paginate($page, ['*'], 'page', $pageSize);
    }
    
    private function validateParams($params)
    {
        $validator = Validator::make($params, [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
