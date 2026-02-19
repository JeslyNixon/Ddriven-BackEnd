<?php
// app/Http/Controllers/Api/CompanySettingController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;

class CompanySettingController extends Controller
{
    /**
     * Get company settings (public access)
     */
    public function index()
    {
        $settings = CompanySetting::first();
        
        if (!$settings) {
            return response()->json([
                'company_name' => 'DDRIVN STUDIO'
            ]);
        }
        
        return response()->json($settings);
    }
}