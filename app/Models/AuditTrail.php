<?php
// app/Models/AuditTrail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'timestamp',
        'user_id',
        'username',
        'ip_address',
        'action',
        'resource',
        'request_method',
        'request_path',
        'status_code',
        'duration',
        'old_value',
        'new_value',
        'user_agent',
        'request_id',
        'app_name',
        'module_name',
        'notes',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'timestamp' => 'datetime',
    ];
}