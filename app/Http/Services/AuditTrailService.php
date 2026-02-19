<?php

namespace App\Http\Services;

use App\Models\AuditTrail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuditTrailService
{
    public static function log(
        string $action,
        string $resource,
        array  $oldValue   = [],
        array  $newValue   = [],
        string $moduleName = '',
        string $notes      = ''
    ): void {
        try {
            $user    = 1;
            $request = request();
            $start   = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);

            AuditTrail::create([
                'timestamp'      => now(),
                'user_id'        => 1,//$user?->id,
                'username'       => 'user',//$user?->name,
                'ip_address'     => $request->ip(),
                'action'         => $action,
                'resource'       => $resource,
                'request_method' => $request->method(),
                'request_path'   => $request->path(),
                'status_code'    => http_response_code(),
                'duration'       => round((microtime(true) - $start) * 1000) . 'ms',
                'old_value'      => $oldValue,
                'new_value'      => $newValue,
                'user_agent'     => $request->userAgent(),
                'request_id'     => $request->header('X-Request-ID', (string) Str::uuid()),
                'app_name'       => config('app.name'),
                'module_name'    => $moduleName,
                'notes'          => $notes,
            ]);

        } catch (\Exception $e) {
            // Never let audit logging break the main app
            Log::error('AuditTrail failed: ' . $e->getMessage());
        }
    }
}