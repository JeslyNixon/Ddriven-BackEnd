<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PropertyMaster;
use App\Models\PropertyLocationAccess;
use App\Models\PropertyPhoto;
use App\Models\PropertySummary;
use App\Models\PropertyInspectionSignoff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PropertyController extends Controller
{
    
    public function saveProperties(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'propertyId' => 'nullable|exists:property_master,id', // Check if updating
            'propertyDetails.projectId' => 'required|string|max:50',
            'propertyDetails.ownerName' => 'required|string|max:100',
            'propertyDetails.address' => 'required|string',
            
            // Validate master IDs exist
            'landParticulars.shapeId' => 'nullable|exists:masters,id',
            'landParticulars.levelVsRoadId' => 'nullable|exists:masters,id',
            'landParticulars.topographyId' => 'nullable|exists:masters,id',
            'landParticulars.soilTypeId' => 'nullable|exists:masters,id',
            
            'locationAccess.accessStatusId' => 'nullable|exists:masters,id',
            'locationAccess.accessRoadsCountId' => 'nullable|exists:masters,id',
            'locationAccess.primaryRoadTypeId' => 'nullable|exists:masters,id',
            'locationAccess.neighbourhoodId' => 'nullable|exists:masters,id',
            'locationAccess.developmentStatusId' => 'nullable|exists:masters,id',
            
            'otherObservations.highTensionLinesId' => 'nullable|exists:masters,id',
            'otherObservations.canalDrainId' => 'nullable|exists:masters,id',
            
            'photos' => 'nullable|array',
            'photos.*' => 'file|mimes:jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $userId = 1;
            $propertyId = $request->input('propertyId'); // Get property ID if editing

            if($request->input('propertyDetails.isDraft') == 1)
                $status = 1;
            else
                $status = 2;

            $propertyData = [
                'project_id' => $request->input('propertyDetails.projectId'),
                'bank' => $request->input('propertyDetails.bank'),
                'owner_name' => $request->input('propertyDetails.ownerName'),
                'purpose_valuation' => $request->input('propertyDetails.purposeValuation', false),
                'purpose_due_diligence' => $request->input('propertyDetails.purposeDueDiligence', false),
                'purpose_feasibility' => $request->input('propertyDetails.purposeFeasibility', false),
                'site_address' => $request->input('propertyDetails.address'),
                'person_met' => $request->input('propertyDetails.personMet'),
                'contact_number' => $request->input('propertyDetails.contactNumber'),
                'status' => $status,
                
                // Save master IDs directly
                'land_shape' => $request->input('landParticulars.shapeId'),
                'level_vs_road' => $request->input('landParticulars.levelVsRoadId'),
                'topography' => $request->input('landParticulars.topographyId'),
                'soil_type' => $request->input('landParticulars.soilTypeId'),
                'water_stagnation' => $request->input('landParticulars.waterStagnation', false),
                'land_remarks' => $request->input('landParticulars.remarks'),
                
                // Boundaries
                'north_boundary' => $request->input('siteBoundaries.north'),
                'south_boundary' => $request->input('siteBoundaries.south'),
                'east_boundary' => $request->input('siteBoundaries.east'),
                'west_boundary' => $request->input('siteBoundaries.west'),
                'boundaries_identified' => $request->input('siteBoundaries.boundariesIdentified', false),
                'boundary_demarcation' => $request->input('siteBoundaries.boundaryDemarcation'),
                'boundary_remarks' => $request->input('siteBoundaries.boundaryDetails'),
                
                // Observations
                'road_widening_signs' => $request->input('otherObservations.roadWideninsSigns', false),
                'high_tension_lines' => $request->input('otherObservations.highTensionLinesId'),
                'canal_drain' => $request->input('otherObservations.canalDrainId'),
                'water_body_nearby' => $request->input('otherObservations.waterBodyNearby', false),
                'other_restrictions' => $request->input('otherObservations.otherRestrictions'),
            ];

            // Check if UPDATE or CREATE
            if ($propertyId) {
                // UPDATE existing property
                $propertyMaster = PropertyMaster::findOrFail($propertyId);
                $propertyMaster->update($propertyData + ['updated_by' => $userId]);
                $message = 'Property inspection updated successfully';
            } else {
                // CREATE new property
                $propertyMaster = PropertyMaster::create($propertyData + ['created_by' => $userId]);
                $message = 'Property inspection saved successfully';
            }

            // =============================================
            // 2. Location Access
            // updateOrCreate = finds by property_id, updates if found, inserts if not
            // NO DUPLICATE ever — works for both new and edit
            // =============================================
            $locationData = [
                'access_status'          => $request->input('locationAccess.accessStatusId'),
                'landlocked_distance'    => $request->input('locationAccess.landlockDistance'),
                'access_roads_count'     => $request->input('locationAccess.accessRoadsCountId'),
                'primary_road_name'      => $request->input('locationAccess.primaryRoadName'),
                'primary_road_type'      => $request->input('locationAccess.primaryRoadTypeId'),
                'primary_road_width'     => $request->input('locationAccess.primaryRoadWidth'),
                'secondary_road_name'    => $request->input('locationAccess.secondaryRoadName'),
                'secondary_road_type'    => $request->input('locationAccess.secondaryRoadTypeId'),
                'secondary_road_width'   => $request->input('locationAccess.secondaryRoadWidth'),
                'tertiary_road_name'     => $request->input('locationAccess.tertiaryRoadName'),
                'tertiary_road_type'     => $request->input('locationAccess.tertiaryRoadTypeId'),
                'tertiary_road_width'    => $request->input('locationAccess.tertiaryRoadWidth'),
                'public_transport'       => $request->input('locationAccess.publicTransport'),
                'nearest_transport_node' => $request->input('locationAccess.nearestTransportNode'),
                'neighbourhood'          => $request->input('locationAccess.neighbourhoodId'),
                'development_status'     => $request->input('locationAccess.developmentStatusId'),
            ];

            if ($this->hasData($locationData)) {
                PropertyLocationAccess::updateOrCreate(
                    ['property_id' => $propertyMaster->id],       // search key — no duplicate
                    $locationData + ['created_by' => $userId, 'updated_by' => $userId]
                );
            }

            // =============================================
            // 3. Photos - only new uploaded files are added
            // =============================================
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    $fileName = time() . '_' . $index . '.' . $photo->getClientOriginalExtension();
                    $filePath = $photo->storeAs(
                        'property-photos/' . $propertyMaster->project_id,
                        $fileName,
                        'public'
                    );

                    PropertyPhoto::create([
                        'property_id'       => $propertyMaster->id,
                        'photo_type'        => $request->input("photoTypes.{$index}", PropertyPhoto::TYPE_OTHER),
                        'photo_path'        => $filePath,
                        'photo_description' => $request->input("photoDescriptions.{$index}"),
                        'created_by'        => $userId,
                    ]);
                }
            }

            // =============================================
            // 4. Summary
            // updateOrCreate = finds by property_id, updates if found, inserts if not
            // NO DUPLICATE ever — works for both new and edit
            // =============================================
            $summaryData = [
                'key_positives' => $request->input('summary.keyPositives'),
                'key_negatives' => $request->input('summary.keyNegatives'),
                'red_flags'     => $request->input('summary.redFlags'),
            ];

            if ($this->hasData($summaryData)) {
                PropertySummary::updateOrCreate(
                    ['property_id' => $propertyMaster->id],       // search key — no duplicate
                    $summaryData + ['created_by' => $userId, 'updated_by' => $userId]
                );
            }

            // =============================================
            // 5. Signoff
            // updateOrCreate = finds by property_id, updates if found, inserts if not
            // NO DUPLICATE ever — works for both new and edit
            // =============================================
            $inspectorName = $request->input('inspector.name');

            if (!empty($inspectorName)) {
                $signoffData = [
                    'inspector_name'  => $inspectorName,
                    'inspection_date' => $request->input('inspector.date', now()->format('Y-m-d')),
                ];

                PropertyInspectionSignoff::updateOrCreate(
                    ['property_id' => $propertyMaster->id],
                    $signoffData + ['created_by' => $userId, 'updated_by' => $userId]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'property_id' => $propertyMaster->id,
                    'project_id' => $propertyMaster->project_id,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save property inspection',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all properties with master data joined
     */
    public function index(Request $request)
    {
        try {
            // Get query parameters
            $perPage   = $request->input('per_page', 15);
            $search    = $request->input('search', '');
            $status    = $request->input('status', '');
            $sortBy    = $request->input('sortBy', '');
            $sortOrder = $request->input('sortOrder', '');

            // Build query
            $query = PropertyMaster::query();

            // Add search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('owner_name', 'like', "%{$search}%")
                        ->orWhere('site_address', 'like', "%{$search}%")
                        ->orWhere('bank', 'like', "%{$search}%")
                        ->orWhere('project_id', 'like', "%{$search}%");
                });
            }
            // Filter by status relationship
            if (!empty($status)) {
                $query->whereHas('propertyStatus', function ($q) use ($status) {
                    $q->where('name', $status);
                });
            }
            if (!empty($sortBy)) {
                $query->orderBy($sortBy, $sortOrder);
            }
            // Get paginated results with relationships
            $properties = $query->with(['creator', 'updater','propertyStatus']) // 'project'
                ->latest()
                ->paginate($perPage);
                $properties->getCollection()->transform(function ($property) {
            $property->status = $property->propertyStatus;
            return $property;
        });


            return response()->json([
                'success' => true,
                'data' => $properties
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

    /**
     * Get single property with all related data
     */
    public function show($id)
    {
        $property = PropertyMaster::with([
            'locationAccess', 
            'photos', 
            'summary', 
            'signoff',
            'landShapeMaster',
            'levelVsRoadMaster',
            'topographyMaster',
            'soilTypeMaster',
            'locationAccess.accessStatusMaster',
            'locationAccess.neighbourhoodMaster',
        ])
        ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $property
        ]);
    }

     public function destroy(Request $request)
    {
        $id = $request->input('id');
        try {
            $property = PropertyMaster::findOrFail($id);
            $property->delete();

            return response()->json([
                'success' => true,
                'message' => 'Property deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =============================================
// Helper - Check if array has any real data
// =============================================
private function hasData(array $data): bool
{
    foreach ($data as $value) {
        // If any field has a non-null, non-empty value → has data
        if (!is_null($value) && $value !== '' && $value !== '0' && $value !== 0) {
            return true;
        }
    }
    return false; // All fields are empty/null → skip saving
}
}