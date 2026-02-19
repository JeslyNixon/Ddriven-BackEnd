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
        Schema::create('property_master', function (Blueprint $table) {
            $table->id();
            $table->string('project_id', 50);
            $table->string('bank', 100)->nullable();
            $table->string('owner_name', 100);
            $table->boolean('purpose_valuation')->default(false);
            $table->boolean('purpose_due_diligence')->default(false);
            $table->boolean('purpose_feasibility')->default(false);
            $table->text('site_address');
            $table->string('person_met', 100)->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->enum('status', ['draft', 'completed', 'approved'])->default('draft');
            
            // Land characteristics
            $table->tinyInteger('land_shape')->comment('1=Regular, 2=Irregular')->nullable();
            $table->tinyInteger('level_vs_road')->comment('1=At, 2=Above, 3=Below')->nullable();
            $table->tinyInteger('topography')->comment('1=Flat, 2=Sloping')->nullable();
            $table->tinyInteger('soil_type')->comment('1=Red, 2=Black, 3=Sandy, 4=Filled')->nullable();
            $table->boolean('water_stagnation')->default(false);
            $table->text('land_remarks')->nullable();
            
            // Boundaries
            $table->string('north_boundary', 150)->nullable();
            $table->string('south_boundary', 150)->nullable();
            $table->string('east_boundary', 150)->nullable();
            $table->string('west_boundary', 150)->nullable();
            $table->boolean('boundaries_identified')->default(false);
            $table->set('boundary_demarcation', ['wall', 'fencing', 'stones', 'natural'])->nullable();
            $table->text('boundary_remarks')->nullable();
            
            // Restrictions and observations
            $table->boolean('road_widening_signs')->default(false);
            $table->tinyInteger('high_tension_lines')->comment('1=None, 2=Near, 3=Crossing')->nullable();
            $table->tinyInteger('canal_drain')->comment('1=None, 2=Near, 3=Passing')->nullable();
            $table->boolean('water_body_nearby')->default(false);
            $table->text('other_restrictions')->nullable();
            
            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('project_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_master');
    }
};
