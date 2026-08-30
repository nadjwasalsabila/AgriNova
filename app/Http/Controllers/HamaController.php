<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;

class HamaController extends Controller
{
    public function __construct(private SupabaseService $supabase) {}

    /**
     * Display a listing of pests/diseases with category tabs.
     * Tabs: hama | semua_penyakit | padi | teh | tomat
     */
    public function index(Request $request)
    {
        $token = session('supabase_token');
        $search = $request->query('search');
        $tab    = $request->query('tab', 'hama'); // default tab: hama

        $hamaList   = [];
        $activeTab  = $tab;
        $dataSource = 'hama'; // 'hama' | 'penyakit'

        if ($tab === 'hama') {
            // ── Tabel hama ──
            $params = ['select' => '*', 'order' => 'nama.asc'];
            if (! empty($search)) {
                $params['or'] = "(nama.ilike.*{$search}*,deskripsi.ilike.*{$search}*,kategori.ilike.*{$search}*)";
            }
            $raw = $this->supabase->select('hama', $params, $token);

            // Normalise ke format seragam
            $hamaList = collect($raw)->map(fn ($r) => [
                'id'            => $r['id'],
                'nama'          => $r['nama'] ?? '-',
                'kategori'      => $r['kategori'] ?? 'Umum',
                'gambar_url'    => $r['gambar_url'] ?? null,
                'deskripsi'     => $r['deskripsi'] ?? '-',
                'ciri_ciri'     => $r['ciri_ciri'] ?? '-',
                'cara_mengatasi'=> $r['cara_mengatasi'] ?? '-',
                'obat'          => null,
                'jenis'         => 'hama',
            ])->all();

            $dataSource = 'hama';

        } else {
            // ── Tabel penyakit ──
            $tables = match($tab) {
                'padi'          => ['penyakit_padi'],
                'teh'           => ['penyakit_teh'],
                'tomat'         => ['penyakit_tomat'],
                default         => ['penyakit_padi', 'penyakit_teh', 'penyakit_tomat'],
            };
            $labelMap = [
                'penyakit_padi'  => 'Padi',
                'penyakit_teh'   => 'Teh',
                'penyakit_tomat' => 'Tomat',
            ];

            $all = [];
            foreach ($tables as $tbl) {
                $params = ['select' => '*', 'order' => 'nama_penyakit.asc'];
                if (! empty($search)) {
                    $params['or'] = "(nama_penyakit.ilike.*{$search}*,deskripsi_penyakit.ilike.*{$search}*)";
                }
                $rows = $this->supabase->select($tbl, $params, $token);

                // Fetch many-to-many relations for this plant type
                $type = match ($tbl) {
                    'penyakit_padi'  => 'padi',
                    'penyakit_teh'   => 'teh',
                    'penyakit_tomat' => 'tomat',
                    default          => null,
                };

                $relations = [];
                if ($type) {
                    $relations = $this->supabase->select('obat_penyakit_relation', [
                        'select'        => 'penyakit_id,obat(*)',
                        'penyakit_type' => 'eq.' . $type
                    ], $token);
                }

                // Group obats by disease ID
                $obatMap = [];
                foreach ($relations as $rel) {
                    if (! empty($rel['obat'])) {
                        $obatMap[$rel['penyakit_id']][] = $rel['obat'];
                    }
                }

                foreach ($rows as $r) {
                    $linkedObats = $obatMap[$r['id']] ?? [];

                    // Comma-separated names for quick text fallback representation
                    $obatNamaList = collect($linkedObats)->pluck('nama')->implode(', ');
                    if (empty($obatNamaList)) {
                        $obatNamaList = $r['obat'] ?? null;
                    }

                    $all[] = [
                        'id'             => $r['id'],
                        'nama'           => $r['nama_penyakit'] ?? '-',
                        'kategori'       => $labelMap[$tbl] ?? $tbl,
                        'gambar_url'     => $r['image'] ?? null,
                        'deskripsi'      => $r['deskripsi_penyakit'] ?? '-',
                        'ciri_ciri'      => null,
                        'cara_mengatasi' => $r['penanganan'] ?? '-',
                        'obat'           => $obatNamaList,
                        'obats'          => $linkedObats,
                        'jenis'          => 'penyakit',
                        '_table'         => $tbl,
                    ];
                }
            }

            // Sort by nama when merging multiple tables
            if (count($tables) > 1) {
                usort($all, fn ($a, $b) => strcmp($a['nama'], $b['nama']));
            }

            $hamaList   = $all;
            $dataSource = 'penyakit';
        }

        return view('admin.hama.index', [
            'title'      => 'Database Hama & Penyakit',
            'hamaList'   => $hamaList,
            'search'     => $search,
            'activeTab'  => $activeTab,
            'dataSource' => $dataSource,
        ]);
    }

    /**
     * Display the specified pest/disease details and recommended medicines.
     */
    public function show($id)
    {
        $token = session('supabase_token');
        $hama = $this->supabase->selectSingle('hama', $id, $token);

        if (! $hama) {
            return redirect()->route('admin.hama.index')
                ->with('error', 'Data hama & penyakit tidak ditemukan.');
        }

        // Fetch recommended medicines relation (PostgREST embedded query)
        $relationParams = [
            'hama_id' => 'eq.' . $id,
            'select'  => '*,obat(*)',
            'order'   => 'created_at.desc',
        ];
        $recommendations = $this->supabase->select('rekomendasi_obat', $relationParams, $token);

        // Fetch all active medicines to populate the dropdown
        $obatParams = [
            'select' => 'id,nama,kategori,harga,status',
            'status' => 'eq.Aktif',
            'order'  => 'nama.asc',
        ];
        $allObat = $this->supabase->select('obat', $obatParams, $token);

        // Filter out medicines that are already recommended for this disease
        $recommendedObatIds = collect($recommendations)->pluck('obat_id')->toArray();
        $availableObat = collect($allObat)->filter(function ($obat) use ($recommendedObatIds) {
            return ! in_array($obat['id'], $recommendedObatIds);
        });

        return view('admin.hama.show', [
            'title'           => 'Kelola Rekomendasi Obat: ' . $hama['nama'],
            'hama'            => $hama,
            'recommendations' => $recommendations,
            'availableObat'  => $availableObat,
        ]);
    }

    /**
     * Store a new recommendation relation.
     */
    public function storeRekomendasi(Request $request, $id)
    {
        $request->validate([
            'obat_id' => ['required', 'integer'],
        ], [
            'obat_id.required' => 'Silakan pilih obat terlebih dahulu.',
            'obat_id.integer'  => 'ID obat tidak valid.',
        ]);

        $token = session('supabase_token');

        try {
            // Verify if relation already exists (though select options are filtered)
            $existingParams = [
                'hama_id' => 'eq.' . $id,
                'obat_id' => 'eq.' . $request->obat_id,
            ];
            $existing = $this->supabase->select('rekomendasi_obat', $existingParams, $token);

            if (! empty($existing)) {
                return back()->withErrors(['error' => 'Obat ini sudah direkomendasikan untuk hama/penyakit ini.']);
            }

            // Insert new relation
            $this->supabase->insert('rekomendasi_obat', [
                'hama_id' => (int) $id,
                'obat_id' => (int) $request->obat_id,
            ], $token);

            return redirect()->route('admin.hama.show', $id)
                ->with('success', 'Rekomendasi obat berhasil ditambahkan!');

        } catch (\Throwable $e) {
            return back()
                ->withErrors(['error' => 'Gagal menyimpan rekomendasi: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the recommendation relation.
     */
    public function destroyRekomendasi($hamaId, $id)
    {
        $token = session('supabase_token');

        try {
            $deleted = $this->supabase->delete('rekomendasi_obat', $id, $token);

            if ($deleted) {
                return redirect()->route('admin.hama.show', $hamaId)
                    ->with('success', 'Rekomendasi obat berhasil dihapus!');
            }

            return redirect()->route('admin.hama.show', $hamaId)
                ->with('error', 'Gagal menghapus rekomendasi obat.');

        } catch (\Throwable $e) {
            return redirect()->route('admin.hama.show', $hamaId)
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
