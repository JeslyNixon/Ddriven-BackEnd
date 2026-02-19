<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MastersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $masters = [
            // Land Shape
            ['type' => 'land_shape', 'code' => 'REGULAR', 'name' => 'Regular', 'status' => 'A'],
            ['type' => 'land_shape', 'code' => 'IRREGULAR', 'name' => 'Irregular', 'status' => 'A'],
            
            // Level vs Road
            ['type' => 'level_vs_road', 'code' => 'AT', 'name' => 'At Road Level', 'status' => 'A'],
            ['type' => 'level_vs_road', 'code' => 'ABOVE', 'name' => 'Above Road Level', 'status' => 'A'],
            ['type' => 'level_vs_road', 'code' => 'BELOW', 'name' => 'Below Road Level', 'status' => 'A'],
            
            // Topography
            ['type' => 'topography', 'code' => 'FLAT', 'name' => 'Flat', 'status' => 'A'],
            ['type' => 'topography', 'code' => 'SLOPING', 'name' => 'Sloping', 'status' => 'A'],
            
            // Soil Type
            ['type' => 'soil_type', 'code' => 'RED', 'name' => 'Red Soil', 'status' => 'A'],
            ['type' => 'soil_type', 'code' => 'BLACK', 'name' => 'Black Soil', 'status' => 'A'],
            ['type' => 'soil_type', 'code' => 'SANDY', 'name' => 'Sandy Soil', 'status' => 'A'],
            ['type' => 'soil_type', 'code' => 'FILLED', 'name' => 'Filled Soil', 'status' => 'A'],
            
            // Boundary Demarcation
            ['type' => 'boundary_demarcation', 'code' => 'WALL', 'name' => 'Compound Wall', 'status' => 'A'],
            ['type' => 'boundary_demarcation', 'code' => 'FENCING', 'name' => 'Fencing (Barbed Wire / Chain Link)', 'status' => 'A'],
            ['type' => 'boundary_demarcation', 'code' => 'STONES', 'name' => 'Survey Stones', 'status' => 'A'],
            ['type' => 'boundary_demarcation', 'code' => 'NATURAL', 'name' => 'Natural Boundaries (Canal / Road / River)', 'status' => 'A'],
            ['type' => 'boundary_demarcation', 'code' => 'Not_on_Ground', 'name' => 'Not Identified on Ground', 'status' => 'A'],
            
            // High Tension Lines
            ['type' => 'high_tension_lines', 'code' => 'NONE', 'name' => 'None', 'status' => 'A'],
            ['type' => 'high_tension_lines', 'code' => 'NEAR', 'name' => 'Near', 'status' => 'A'],
            ['type' => 'high_tension_lines', 'code' => 'CROSSING', 'name' => 'Crossing', 'status' => 'A'],
            
            // Canal/Drain
            ['type' => 'canal_drain', 'code' => 'NONE', 'name' => 'None', 'status' => 'A'],
            ['type' => 'canal_drain', 'code' => 'NEAR', 'name' => 'Near', 'status' => 'A'],
            ['type' => 'canal_drain', 'code' => 'PASSING', 'name' => 'Passing Through', 'status' => 'A'],
            
            // Access Status
            ['type' => 'access_status', 'code' => 'ACCESSIBLE', 'name' => 'Accessible', 'status' => 'A'],
            ['type' => 'access_status', 'code' => 'LANDLOCKED', 'name' => 'Landlocked', 'status' => 'A'],
            
            // Access Roads Count
            ['type' => 'access_roads_count', 'code' => 'ONE', 'name' => 'One Road', 'status' => 'A'],
            ['type' => 'access_roads_count', 'code' => 'TWO', 'name' => 'Two Roads', 'status' => 'A'],
            ['type' => 'access_roads_count', 'code' => 'MORE', 'name' => 'More than Two', 'status' => 'A'],
            
            // Road Type
            ['type' => 'road_type', 'code' => 'CC', 'name' => 'Cement Concrete (CC)', 'status' => 'A'],
            ['type' => 'road_type', 'code' => 'BT', 'name' => 'Bituminous (BT)', 'status' => 'A'],
            ['type' => 'road_type', 'code' => 'EARTHEN', 'name' => 'Earthen', 'status' => 'A'],
            
            // Public Transport
            ['type' => 'public_transport', 'code' => 'MTC', 'name' => 'MTC', 'status' => 'A'],
            ['type' => 'public_transport', 'code' => 'METRO', 'name' => 'Metro Rail', 'status' => 'A'],
            ['type' => 'public_transport', 'code' => 'TRAIN', 'name' => 'LOcal Train', 'status' => 'A'],
            ['type' => 'public_transport', 'code' => 'MRTS', 'name' => 'MRTS', 'status' => 'A'],
            
            // Neighbourhood
            ['type' => 'neighbourhood', 'code' => 'RESIDENTIAL', 'name' => 'Residential', 'status' => 'A'],
            ['type' => 'neighbourhood', 'code' => 'COMMERCIAL', 'name' => 'Commercial', 'status' => 'A'],
            ['type' => 'neighbourhood', 'code' => 'INDUSTRIAL', 'name' => 'Industrial', 'status' => 'A'],
            ['type' => 'neighbourhood', 'code' => 'OTHERS', 'name' => 'Others', 'status' => 'A'],
            
            // Development Status
            ['type' => 'development_status', 'code' => 'DEVELOPED', 'name' => 'Developed', 'status' => 'A'],
            ['type' => 'development_status', 'code' => 'DEVELOPING', 'name' => 'Developing', 'status' => 'A'],
            
            // Photo Types
            ['type' => 'photo_type', 'code' => 'FRONT', 'name' => 'Front View', 'status' => 'A'],
            ['type' => 'photo_type', 'code' => 'BACK', 'name' => 'Back View', 'status' => 'A'],
            ['type' => 'photo_type', 'code' => 'LEFT', 'name' => 'Left View', 'status' => 'A'],
            ['type' => 'photo_type', 'code' => 'RIGHT', 'name' => 'Right View', 'status' => 'A'],
            ['type' => 'photo_type', 'code' => 'AERIAL', 'name' => 'Aerial View', 'status' => 'A'],
            ['type' => 'photo_type', 'code' => 'BOUNDARY', 'name' => 'Boundary', 'status' => 'A'],
            ['type' => 'photo_type', 'code' => 'OTHER', 'name' => 'Other', 'status' => 'A'],
        ];

        foreach ($masters as $master) {
            DB::table('masters')->insert([
                'type' => $master['type'],
                'code' => $master['code'],
                'name' => $master['name'],
                'status' => $master['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
