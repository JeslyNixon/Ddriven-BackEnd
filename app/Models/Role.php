<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'display_name',
        'description',
        'created_by', 
        'updated_by',
    ];

    /**
     * The users that belong to the role (Many-to-Many)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role')
                    ->withTimestamps();
    }

    /**
     * Get all users with this role
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersWithRole()
    {
        return $this->users()->get();
    }

    /**
     * Check if any users have this role
     * 
     * @return bool
     */
    public function hasUsers()
    {
        return $this->users()->exists();
    }

    /**
     * Count users with this role
     * 
     * @return int
     */
    public function usersCount()
    {
        return $this->users()->count();
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
