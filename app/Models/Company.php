<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $table = 'company';
    protected $fillable = [
        'name',
        'logo',
        'thumbnail',
        'description',
        'amount_of_employee',
        'tax_number',
        'status',
        'website',
        'email',
        'phone',
        'address',
        'contract'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
