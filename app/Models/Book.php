<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'title',
        'author',
        'publisher',
        'isbn',
        'category',
        'cover_photo',
        'file_path',
        'physical_stock',
    ];

    protected $casts = [
        'physical_stock' => 'integer',
    ];

    public function loans()
    {
        return $this->hasMany(BookLoan::class);
    }
}
