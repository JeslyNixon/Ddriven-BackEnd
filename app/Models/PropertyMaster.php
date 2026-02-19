<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyMaster extends Model
{
   
    use HasFactory;

    protected $table = 'property_master';

    protected $fillable = [
        'project_id',
        'bank',
        'owner_name',
        'purpose_valuation',
        'purpose_due_diligence',
        'purpose_feasibility',
        'site_address',
        'person_met',
        'contact_number',
        'status',
        'land_shape',
        'level_vs_road',
        'topography',
        'soil_type',
        'water_stagnation',
        'land_remarks',
        'north_boundary',
        'south_boundary',
        'east_boundary',
        'west_boundary',
        'boundaries_identified',
        'boundary_demarcation',
        'boundary_remarks',
        'road_widening_signs',
        'high_tension_lines',
        'canal_drain',
        'water_body_nearby',
        'other_restrictions',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'purpose_valuation' => 'boolean',
        'purpose_due_diligence' => 'boolean',
        'purpose_feasibility' => 'boolean',
        'boundaries_identified' => 'boolean',
        'road_widening_signs' => 'boolean',
        'high_tension_lines' => 'boolean',
        'canal_drain' => 'boolean',
        'water_body_nearby' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
 
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function propertyStatus()
    {
        return $this->belongsTo(Status::class, 'status');
    }

    // ==========================================
    // MAIN RELATIONSHIPS - THESE WERE MISSING!
    // ==========================================

    /**
     * Location Access relationship (One-to-One)
     */
    public function locationAccess()
    {
        return $this->hasOne(PropertyLocationAccess::class, 'property_id');
    }

    /**
     * Photos relationship (One-to-Many)
     */
    public function photos()
    {
        return $this->hasMany(PropertyPhoto::class, 'property_id');
    }

    /**
     * Summary relationship (One-to-One)
     */
    public function summary()
    {
        return $this->hasOne(PropertySummary::class, 'property_id');
    }

    /**
     * Signoff relationship (One-to-One)
     */
    public function signoff()
    {
        return $this->hasOne(PropertyInspectionSignoff::class, 'property_id');
    }

    // ==========================================
    // MASTER RELATIONSHIPS FOR LAND PARTICULARS
    // ==========================================

    /**
     * Land Shape Master
     */
    public function landShapeMaster()
    {
        return $this->belongsTo(Master::class, 'land_shape', 'id');
    }

    /**
     * Level vs Road Master
     */
    public function levelVsRoadMaster()
    {
        return $this->belongsTo(Master::class, 'level_vs_road', 'id');
    }

    /**
     * Topography Master
     */
    public function topographyMaster()
    {
        return $this->belongsTo(Master::class, 'topography', 'id');
    }

    /**
     * Soil Type Master
     */
    public function soilTypeMaster()
    {
        return $this->belongsTo(Master::class, 'soil_type', 'id');
    }

    /**
     * High Tension Lines Master
     */
    public function highTensionLinesMaster()
    {
        return $this->belongsTo(Master::class, 'high_tension_lines', 'id');
    }

    /**
     * Canal Drain Master
     */
    public function canalDrainMaster()
    {
        return $this->belongsTo(Master::class, 'canal_drain', 'id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}