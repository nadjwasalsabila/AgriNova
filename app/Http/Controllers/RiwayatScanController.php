<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;

class RiwayatScanController extends Controller
{
    public function __construct(private SupabaseService $supabase) {}

    /**
     * Display a paginated listing of AI scan history.
     */
    public function index(Request $request)
    {
        $token    = session('supabase_token');
        $search   = $request->query('search');
        $filter   = $request->query('filter'); // plant_type filter
        $page     = (int) $request->query('page', 1);
        $perPage  = 20;
        $offset   = ($page - 1) * $perPage;

        // PostgREST query params
        $params = [
            'select' => '*',
            'order'  => 'created_at.desc',
            'limit'  => $perPage,
            'offset' => $offset,
        ];

        // Filter by plant type
        if (! empty($filter)) {
            $params['plant_type'] = 'ilike.*' . $filter . '*';
        }

        // Search by disease or plant_type
        if (! empty($search)) {
            $params['or'] = "(disease.ilike.*{$search}*,plant_type.ilike.*{$search}*)";
        }

        $records = $this->supabase->select('prediction_history', $params, $token);

        // Count total for pagination
        $countParams = ['select' => 'id'];
        if (! empty($filter)) {
            $countParams['plant_type'] = 'ilike.*' . $filter . '*';
        }
        if (! empty($search)) {
            $countParams['or'] = "(disease.ilike.*{$search}*,plant_type.ilike.*{$search}*)";
        }
        $totalCount = $this->supabase->count('prediction_history');
        $totalPages = $totalCount > 0 ? (int) ceil($totalCount / $perPage) : 1;

        // Distinct plant types for filter dropdown (from all records)
        $allPlants = $this->supabase->select('prediction_history', [
            'select' => 'plant_type',
            'order'  => 'plant_type.asc',
        ], $token);
        $plantTypes = collect($allPlants)->pluck('plant_type')->unique()->sort()->values()->all();

        return view('admin.riwayat-scan.index', [
            'title'      => 'Riwayat Scan AI',
            'records'    => $records,
            'search'     => $search,
            'filter'     => $filter,
            'plantTypes' => $plantTypes,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalCount' => $totalCount,
            'totalPages' => $totalPages,
        ]);
    }
}
