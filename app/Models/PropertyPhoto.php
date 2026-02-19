<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyPhoto extends Model
{
    protected $table = 'property_photos';

    protected $fillable = [
        'property_id',
        'photo_type',
        'photo_path',
        'photo_description',
        'created_by'
    ];

    // Photo type constants
    const TYPE_FRONT = 1;
    const TYPE_BACK = 2;
    const TYPE_LEFT = 3;
    const TYPE_RIGHT = 4;
    const TYPE_AERIAL = 5;
    const TYPE_BOUNDARY = 6;
    const TYPE_OTHER = 7;

    public function property(): BelongsTo
    {
        return $this->belongsTo(PropertyMaster::class, 'property_id');
    }

    public function getPhotoUrlAttribute()
    {
        return asset('storage/' . $this->photo_path);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}