<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = ['name', 'category', 'brand', 'price', 'active'];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2'
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
