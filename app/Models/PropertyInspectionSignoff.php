<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyInspectionSignoff extends Model
{
    protected $table = 'property_inspection_signoff';

    protected $fillable = [
        'property_id',
        'inspector_name',
        'inspector_signature',
        'inspection_date',
        'created_by'
    ];

    protected $casts = [
        'inspection_date' => 'date',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(PropertyMaster::class, 'property_id');
    }

    public function getSignatureUrlAttribute()
    {
        return $this->inspector_signature 
            ? asset('storage/' . $this->inspector_signature) 
            : null;
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