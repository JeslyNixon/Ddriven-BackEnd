<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyLocationAccess extends Model
{
    protected $table = 'property_location_access';

    protected $fillable = [
        'property_id', 'access_status', 'landlocked_distance', 'access_roads_count',
        'primary_road_name', 'primary_road_type', 'primary_road_width',
        'secondary_road_name', 'secondary_road_type', 'secondary_road_width',
        'tertiary_road_name', 'tertiary_road_type', 'tertiary_road_width',
        'public_transport', 'nearest_transport_node',
        'neighbourhood', 'development_status',
        'created_by', 'updated_by'
    ];

    public function property()
    {
        return $this->belongsTo(PropertyMaster::class, 'property_id');
    }

    // Master relationships
    public function accessStatusMaster()
    {
        return $this->belongsTo(Master::class, 'access_status');
    }

    public function accessRoadsCountMaster()
    {
        return $this->belongsTo(Master::class, 'access_roads_count');
    }

    public function primaryRoadTypeMaster()
    {
        return $this->belongsTo(Master::class, 'primary_road_type');
    }

    public function secondaryRoadTypeMaster()
    {
        return $this->belongsTo(Master::class, 'secondary_road_type');
    }

    public function tertiaryRoadTypeMaster()
    {
        return $this->belongsTo(Master::class, 'tertiary_road_type');
    }

    public function neighbourhoodMaster()
    {
        return $this->belongsTo(Master::class, 'neighbourhood');
    }

    public function developmentStatusMaster()
    {
        return $this->belongsTo(Master::class, 'development_status');
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