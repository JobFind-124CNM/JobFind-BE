<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'post';

    protected $fillable = [
        'title',
        'description',
        'status',
        'company_id',
        'area_id',
        'due_at',
        'benefit',
        'form_of_work_id',
        'amount',
        'salary',
        'category_id',
        "qualification"
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
    }

    public function formOfWork()
    {
        return $this->belongsTo(FormOfWork::class);
    }

    public function levels()
    {
        return $this->belongsToMany(Level::class, 'post_level');
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class, 'post_position', 'post_id', 'position_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_post', 'post_id', 'user_id')
                    ->withPivot('cv', 'cover_letter', 'status', 'subject')
                    ->withTimestamps();
    }
}
