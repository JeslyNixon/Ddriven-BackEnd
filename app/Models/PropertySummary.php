<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertySummary extends Model
{
    protected $table = 'property_summary';

    protected $fillable = [
        'property_id',
        'key_positives',
        'key_negatives',
        'red_flags',
        'created_by'
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(PropertyMaster::class, 'property_id');
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