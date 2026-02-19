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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

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

   public function generatePdf(Request $request)
{
    try {
        $query = PropertyMaster::query();

        // Filter by status
         $filteredStatus =$request->status;
        if ($request->filled('status') && $request->status !== 'all') {
            $query->whereHas('propertyStatus', function($q) use ($request,$filteredStatus) {
                $q->where('name',  $filteredStatus);
            });
        }

        // Sort
        $sortBy = $request->get('sortBy', 'created_at');
        $sortOrder = $request->get('sortOrder', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Load relationships - THIS IS IMPORTANT
        $properties = $query->with(['creator', 'updater', 'propertyStatus'])->get();
// \Log::info('Properties data:', $properties->toArray());
      
        // Generate PDF
        $pdf = Pdf::loadView('pdf.properties', compact('properties','filteredStatus'))
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 20)
            ->setOption('margin-right', 20);
        
        // Return as stream (for modal view)
        return $pdf->stream('properties-list-' . date('d-m-Y') . '.pdf');
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to generate PDF: ' . $e->getMessage()
        ], 500);
    }
}
 public function generateSpreadsheet(Request $request)
    {
        try {
            $query = PropertyMaster::query();
            
            // Filter by status
            $filteredStatus = $request->status ?? 'all';
            if ($request->filled('status') && $request->status !== 'all') {
                $query->whereHas('propertyStatus', function($q) use ($filteredStatus) {
                    $q->where('name', $filteredStatus);
                });
            }
            
            // Sort
            $sortBy = $request->get('sortBy', 'created_at');
            $sortOrder = $request->get('sortOrder', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Load relationships
            $properties = $query->with(['creator', 'updater', 'propertyStatus'])->get();
            
            // Create new Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Properties List');
            
            // Set up title row
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A1', 'Properties List Report');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 18,
                    'color' => ['rgb' => '2c3e50'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(30);
            
            // Meta information row
            $sheet->mergeCells('A2:G2');
            $metaInfo = 'Generated on ' . date('F d, Y \a\t h:i A') . 
                       ' | Status: ' . ucfirst($filteredStatus) . 
                       ' | Total Properties: ' . $properties->count();
            $sheet->setCellValue('A2', $metaInfo);
            $sheet->getStyle('A2')->applyFromArray([
                'font' => [
                    'size' => 10,
                    'color' => ['rgb' => '7f8c8d'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension(2)->setRowHeight(20);

            
            // Header row (row 3)
            $headers = ['#', 'Project ID', 'Status', 'Owner Name', 'Bank', 'Site Address', 'Created Date'];
            $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
            
            foreach ($headers as $index => $header) {
                $sheet->setCellValue($columns[$index] . '3', $header);
            }
            
            // Style header row
            $sheet->getStyle('A3:G3')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '34495e'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension(3)->setRowHeight(25);
            
            // Add data rows
            $rowIndex = 4;
            $dataRowNumber = 1;
            
            foreach ($properties as $property) {
                $sheet->setCellValue('A' . $rowIndex, $dataRowNumber);
                $sheet->setCellValue('B' . $rowIndex, $property->project_id);
                $sheet->setCellValue('C' . $rowIndex, $property->propertyStatus->name ?? 'N/A');
                $sheet->setCellValue('D' . $rowIndex, $property->owner_name);
                $sheet->setCellValue('E' . $rowIndex, $property->bank);
                $sheet->setCellValue('F' . $rowIndex, $property->site_address);
                $sheet->setCellValue('G' . $rowIndex, $property->created_at->format('M d, Y'));
                
                // Apply alternating row colors
                if ($rowIndex % 2 == 0) {
                    $sheet->getStyle("A{$rowIndex}:G{$rowIndex}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'f8f9fa'],
                        ],
                    ]);
                }
                
                $rowIndex++;
                $dataRowNumber++;
            }
            
            $lastRow = $rowIndex - 1;
            
            // Apply borders to all data
            $sheet->getStyle("A3:G{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'e0e0e0'],
                    ],
                ],
            ]);
            
            // Center align specific columns
            $sheet->getStyle("A4:A{$lastRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C4:C{$lastRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G4:G{$lastRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Wrap text for address column
            $sheet->getStyle("F4:F{$lastRow}")->getAlignment()
                ->setWrapText(true);
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(8);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(40);
            $sheet->getColumnDimension('G')->setWidth(15);
            
            // Generate filename
            $filename = 'properties-list-' . date('Y-m-d') . '.xlsx';
            
            // Create writer and save to temp file
            $writer = new Xlsx($spreadsheet);
            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            $writer->save($tempFile);
            
            // Return file as download
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('Excel export failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate spreadsheet: ' . $e->getMessage()
            ], 500);
        }
    }
     public function approveProperty(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:property_master,id',
            'inspector_name' => 'required|string|min:3|max:100',
            'inspection_date' => 'required|date',
            'declaration_accepted' => 'required|boolean',
            'signature' => 'required|string', // base64 encoded image
            'signature_type' => 'required|in:draw,upload'
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

            $propertyId = $request->id;
            $inspectorName = $request->inspector_name;
            $inspectionDate = $request->inspection_date;
            $signatureBase64 = $request->signature;
            $userId = Auth::id() ?? 1; // Get authenticated user ID

            // Save signature file
            $signaturePath = $this->saveSignature($signatureBase64);

            // Insert into property_inspection_signoff
            $signoffId = DB::table('property_inspection_signoff')->insertGetId([
                'property_id' => $propertyId,
                'inspector_name' => $inspectorName,
                'inspector_signature' => $signaturePath,
                'inspection_date' => $inspectionDate,
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update property_master status to 'approved'
            DB::table('property_master')
                ->where('id', $propertyId)
                ->update([
                    'status' => 3,
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Property approved successfully',
                'data' => [
                    'signoff_id' => $signoffId,
                    'property_id' => $propertyId,
                    'status' => 'approved'
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete signature file if it was created
            if (isset($signaturePath)) {
                Storage::disk('public')->delete($signaturePath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk Properties Approval
     * POST /api/bulk-approve-properties
     */
    public function bulkApproveProperties(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'property_ids' => 'required|array|min:1',
            'property_ids.*' => 'required|exists:property_master,id',
            'inspector_name' => 'required|string|min:3|max:100',
            'inspection_date' => 'required|date',
            'declaration_accepted' => 'required|boolean',
            'signature' => 'required|string', // base64 encoded image
            'signature_type' => 'required|in:draw,upload'
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

            $propertyIds = $request->property_ids;
            $inspectorName = $request->inspector_name;
            $inspectionDate = $request->inspection_date;
            $signatureBase64 = $request->signature;
            $userId = Auth::id() ?? 1; // Get authenticated user ID

            // Save signature file (single signature for all properties)
            $signaturePath = $this->saveSignature($signatureBase64);

            $signoffIds = [];
            $currentTimestamp = now();

            // Prepare bulk insert data for property_inspection_signoff
            $signoffRecords = [];
            foreach ($propertyIds as $propertyId) {
                $signoffRecords[] = [
                    'property_id' => $propertyId,
                    'inspector_name' => $inspectorName,
                    'inspector_signature' => $signaturePath,
                    'inspection_date' => $inspectionDate,
                    'created_by' => $userId,
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp
                ];
            }

            // Bulk insert into property_inspection_signoff
            DB::table('property_inspection_signoff')->insert($signoffRecords);

            // Update all property_master statuses to 'approved'
            DB::table('property_master')
                ->whereIn('id', $propertyIds)
                ->update([
                    'status' => 3,
                    'updated_at' => $currentTimestamp
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($propertyIds) . ' properties approved successfully',
                'data' => [
                    'approved_count' => count($propertyIds),
                    'property_ids' => $propertyIds,
                    'status' => 'approved'
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete signature file if it was created
            if (isset($signaturePath)) {
                Storage::disk('public')->delete($signaturePath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve properties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to save base64 signature to storage
     * 
     * @param string $signatureBase64
     * @return string Path to saved signature
     */
    private function saveSignature($signatureBase64)
    {
        // Remove base64 prefix if exists
        $signatureData = preg_replace('/^data:image\/\w+;base64,/', '', $signatureBase64);
        
        // Decode base64
        $decodedSignature = base64_decode($signatureData);
        
        if ($decodedSignature === false) {
            throw new \Exception('Invalid signature data');
        }

        // Generate unique filename
        $fileName = 'signatures/' . Str::uuid() . '.png';
        
        // Save to storage (public disk)
        Storage::disk('public')->put($fileName, $decodedSignature);
        
        return $fileName;
    }

    /**
     * Optional: Get signature URL for display
     * 
     * @param string $signaturePath
     * @return string Public URL
     */
    private function getSignatureUrl($signaturePath)
    {
        return Storage::disk('public')->url($signaturePath);
    }

}