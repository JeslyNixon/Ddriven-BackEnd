<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; 
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',   
        'address',
        'is_active',
        'created_by', 
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function scopeRole($query, $roleName)
    {
        return $query->whereHas('roles', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }
     public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role')
                    ->withTimestamps();
    }

    /**
     * Check if user has a specific role
     * 
     * @param string $roleName
     * @return bool
     */
    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the given roles
     * 
     * @param array $roleNames
     * @return bool
     */
    public function hasAnyRole(array $roleNames)
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Check if user has all of the given roles
     * 
     * @param array $roleNames
     * @return bool
     */
    public function hasAllRoles(array $roleNames)
    {
        $userRoleNames = $this->roles()->pluck('name')->toArray();
        
        foreach ($roleNames as $roleName) {
            if (!in_array($roleName, $userRoleNames)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Assign a role to the user
     * 
     * @param mixed $role Role ID or Role model
     * @return void
     */
    public function assignRole($role)
    {
        $roleId = is_numeric($role) ? $role : $role->id;
        
        if (!$this->roles()->where('role_id', $roleId)->exists()) {
            $this->roles()->attach($roleId);
        }
    }

    /**
     * Remove a role from the user
     * 
     * @param mixed $role Role ID or Role model
     * @return void
     */
    public function removeRole($role)
    {
        $roleId = is_numeric($role) ? $role : $role->id;
        $this->roles()->detach($roleId);
    }

    /**
     * Sync user roles (replace all existing roles)
     * 
     * @param array $roleIds
     * @return void
     */
    public function syncRoles(array $roleIds)
    {
        $this->roles()->sync($roleIds);
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