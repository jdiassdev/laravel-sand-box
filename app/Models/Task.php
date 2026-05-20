<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['name', 'category', 'completed'];

    protected $casts = [
        'completed' => 'boolean',
    ];

    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    public function scopePending($query)
    {
        return $query->where('completed', false);
    }
}
