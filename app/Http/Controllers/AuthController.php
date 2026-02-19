<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Services\AuditTrailService;

class AuthController extends Controller
{
    /**
     * Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
             // Log failed attempt
            AuditTrailService::log(
                action:     'login_failed',
                resource:   'Auth',
                newValue:   ['email' => $request->email],
                moduleName: 'Authentication',
                notes:      'Failed login attempt'
            );
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = $request->user();
        $token = $user->createToken('auth-token')->plainTextToken;

        // Log successful login
        AuditTrailService::log(
            action:     'login',
            resource:   'Auth',
            newValue:   ['email' => $user->email, 'name' => $user->name],
            moduleName: 'Authentication',
            notes:      "User '{$user->name}' logged in"
        );

       
        return response()->json([
            'token' => $token,
            'user' => $user,
            'roles'       => $user->getRoleNames(),          
            'permissions' => $user->getAllPermissions()     
                                  ->pluck('name'),   
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        // Log before deleting token
        AuditTrailService::log(
            action:     'logout',
            resource:   'Auth',
            newValue:   ['email' => $user->email, 'name' => $user->name],
            moduleName: 'Authentication',
            notes:      "User '{$user->name}' logged out"
        );

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get current user
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }
}