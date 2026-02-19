<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Master;

class MasterController extends Controller
{
    /**
     * Get all masters grouped by type
     */
    public function index()
    {
        try {
            $masters = Master::where('status', 'A')
                ->select('id', 'type', 'code', 'name')
                ->orderBy('type')
                ->orderBy('name')
                ->get()
                ->groupBy('type');

            return response()->json([
                'success' => true,
                'data' => $masters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch masters',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get masters by specific type
     */
    public function getByType(string $type)
    {
        try {
            $masters = Master::where('type', $type)
                ->where('status', 'A')
                ->select('id', 'code', 'name')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $masters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch masters',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}