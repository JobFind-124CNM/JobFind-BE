<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TagService
{
    public function getAllTags()
    {
        return Tag::all();
    }

    public function getAllTagsPaginated($pageSize = 5, $page = 1, $search = null)
    {
        $query = Tag::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->paginate($page, ['*'], 'page', $pageSize);
    }

    public function getTagById($id)
    {
        return Tag::findOrFail($id);
    }

    public function createTag(array $data)
    {
        $this->validateTag($data);
        return Tag::create($data);
    }

    public function updateTag($id, array $data)
    {
        $this->validateTag($data);
        $tag = Tag::findOrFail($id);
        $tag->update($data);
        return $tag;
    }

    public function deleteTag($id)
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();
        return $tag;
    }

    private function validateTag(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}