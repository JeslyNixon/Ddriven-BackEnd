<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master extends Model
{
    use HasFactory;

    protected $table = 'masters';

    protected $fillable = [
        'type',
        'code',
        'name',
        'description',
        'status',
        'sort_order'
    ];

    /**
     * Scope to get active masters
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }

    /**
     * Scope to get by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}