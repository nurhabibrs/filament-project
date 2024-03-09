<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug', 
        'writer', 
        'year', 
        'pages',
        'image',
        'stock'
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function borrows() {
        return $this->hasMany(Borrow::class);
    }
}
