<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
     public function index(Request $request)
    {
        try {
            // Get query parameters
            $perPage   = $request->input('per_page', 15);
            $search    = $request->input('search', '');
            $sortBy    = $request->input('sortBy', '');
            $sortOrder = $request->input('sortOrder', '');

            // Build query
            $query = Role::query();

            // Add search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")                        
                        ->orWhere('display_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
           
            if (!empty($sortBy)) {
                $query->orderBy($sortBy, $sortOrder);
            }
            // Get paginated results with relationships
            $roles = $query->with(['creator', 'updater']) // 'project'
                ->latest()
                ->paginate($perPage);
              


            return response()->json([
                'success' => true,
                'data' => $roles
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

     public function getRoles(Request $request)
    {
        try {
            // Get query parameters
           $roles = Role::all();

        return response()->json([
            'success' => true,
            'data' => $roles
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
            'guard_name' => 'required',
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
            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'guard_name' => $request->guard_name,
                'description' => $request->description,
                'created_by'  =>$created_by
            ]);

          

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'data' => $role
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    public function update(Request $request)
    {
         $id = $request->input('id', '');   
         $updated_by = Auth::id() ?? 1;


        try {
            $role = Role::findOrFail($id);

            // Update basic fields
            $role->description = $request->description;
            $role->display_name = $request->display_name;
            $role->updated_by = $updated_by;
           
            $role->save();

           
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => $role
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        
         $id = $request->input('id', '');
        try {
            $role = Role::findOrFail($id);
            $role->delete(); // This uses soft delete if you have it configured

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
