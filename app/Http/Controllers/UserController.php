<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Get query parameters
            $perPage   = $request->input('per_page', 15);
            $search    = $request->input('search', '');
            $role    = $request->input('role', '');
            $sortBy    = $request->input('sortBy', '');
            $sortOrder = $request->input('sortOrder', '');

            // Build query
            $query = User::query();

            // Add search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if (!empty($role)) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            }
            if (!empty($sortBy)) {
                $query->orderBy($sortBy, $sortOrder);
            }
            // Get paginated results with relationships
            $users = $query->with(['creator', 'updater', 'roles']) // 'project'
                ->latest()
                ->paginate($perPage);



            return response()->json([
                'success' => true,
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            Log::error('Property Index Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching properties',
                'error' => $e->getMessage()
            ], 500);
        }
    }
     public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|regex:/^(?=.*[A-Z])(?=.*[^a-zA-Z0-9])/',
            'role_id' => 'required|exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $created_by = Auth::id() ?? 1;
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'created_by' => $created_by
            ]);

            // Assign single role
            $user->roles()->attach($request->role_id);

            // Load relationships
            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    public function update(Request $request)
    {
         $id = $request->input('id', '');
           $updated_by = Auth::id() ?? 1;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role_id' => 'required|exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($id);

            // Update basic fields
            $user->name = $request->name;
            $user->email = $request->email;
            $user->updated_by = $updated_by;

            // Update password only if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // Sync to single role (detach all, attach one)
            $user->roles()->sync([$request->role_id]);

            // Load relationships
            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        
         $id = $request->input('id', '');
        try {
            $user = User::findOrFail($id);
            $user->delete(); // This uses soft delete if you have it configured

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
