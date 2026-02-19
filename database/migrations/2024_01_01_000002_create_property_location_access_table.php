<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('property_location_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            
            // Access status
            $table->tinyInteger('access_status')->comment('1=Accessible, 2=Landlocked')->nullable();
            $table->string('landlocked_distance', 100)->nullable();
            $table->tinyInteger('access_roads_count')->comment('1=One, 2=Two, 3=More')->nullable();
            
            // Primary road
            $table->string('primary_road_name', 150)->nullable();
            $table->tinyInteger('primary_road_type')->comment('1=CC, 2=BT, 3=Earthen')->nullable();
            $table->string('primary_road_width', 50)->nullable();
            
            // Secondary road
            $table->string('secondary_road_name', 150)->nullable();
            $table->tinyInteger('secondary_road_type')->comment('1=CC, 2=BT, 3=Earthen')->nullable();
            $table->string('secondary_road_width', 50)->nullable();
            
            // Tertiary road
            $table->string('tertiary_road_name', 150)->nullable();
            $table->tinyInteger('tertiary_road_type')->comment('1=CC, 2=BT, 3=Earthen')->nullable();
            $table->string('tertiary_road_width', 50)->nullable();
            
            // Public transport
            $table->set('public_transport', ['bus', 'metro', 'train', 'auto'])->nullable();
            $table->string('nearest_transport_node', 150)->nullable();
            
            // Neighbourhood
            $table->tinyInteger('neighbourhood')->comment('1=Residential, 2=Commercial, 3=Industrial, 4=Others')->nullable();
            $table->tinyInteger('development_status')->comment('1=Developed, 2=Developing')->nullable();
            
            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('property_id')->references('id')->on('property_master')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('property_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_location_access');
    }
};
