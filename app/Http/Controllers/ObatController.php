<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ObatController extends Controller
{
    public function __construct(private SupabaseService $supabase) {}

    /**
     * Display a listing of the medicines.
     */
    public function index(Request $request)
    {
        $token = session('supabase_token');
        $search = $request->query('search');

        // PostgREST query parameters
        $params = [
            'select' => '*',
            'order'  => 'created_at.desc',
        ];

        // Apply search filter if provided
        if (! empty($search)) {
            $params['or'] = "(nama.ilike.*{$search}*,deskripsi.ilike.*{$search}*,kategori.ilike.*{$search}*)";
        }

        $obatList = $this->supabase->select('obat', $params, $token);

        // Count how many penyakit are linked to each obat using the junction table
        if (! empty($obatList)) {
            foreach ($obatList as &$ob) {
                $relations = $this->supabase->select('obat_penyakit_relation', [
                    'select'  => 'id',
                    'obat_id' => 'eq.' . $ob['id'],
                ], $token);
                $ob['penyakit_count'] = count($relations);
            }
            unset($ob);
        }

        return view('admin.obat.index', [
            'title'    => 'Daftar Obat Pertanian',
            'obatList' => $obatList,
            'search'   => $search,
        ]);
    }

    /**
     * Show the form for creating a new medicine.
     */
    public function create()
    {
        $token       = session('supabase_token');
        $allPenyakit = $this->fetchAllPenyakit($token);

        return view('admin.obat.create', [
            'title'       => 'Tambah Obat Baru',
            'allPenyakit' => $allPenyakit,
        ]);
    }

    /**
     * Store a newly created medicine in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'      => ['required', 'string', 'max:255'],
            'kategori'  => ['required', 'string', 'max:100'],
            'harga'     => ['required', 'numeric', 'min:0'],
            'status'    => ['required', 'string', 'in:Aktif,Tidak Aktif'],
            'deskripsi' => ['required', 'string'],
            'gambar'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'penyakit'  => ['nullable', 'array'],
        ], [
            'nama.required'      => 'Nama obat wajib diisi.',
            'kategori.required'  => 'Kategori obat wajib diisi.',
            'harga.required'     => 'Harga obat wajib diisi.',
            'harga.numeric'      => 'Harga harus berupa angka.',
            'status.required'    => 'Status obat wajib dipilih.',
            'deskripsi.required' => 'Deskripsi obat wajib diisi.',
            'gambar.image'       => 'File harus berupa gambar.',
            'gambar.mimes'       => 'Format gambar yang diperbolehkan: jpeg, png, jpg, webp.',
            'gambar.max'         => 'Ukuran gambar maksimal 2MB.',
        ]);

        $token = session('supabase_token');
        $imageUrl = null;

        try {
            // ── Upload image to Supabase Storage if uploaded ──
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = 'obat/' . $filename;

                $imageUrl = $this->supabase->uploadStorage(
                    'images',
                    $path,
                    file_get_contents($file->getRealPath()),
                    $file->getMimeType()
                );
            }

            // ── Save to database ──
            $newObat = $this->supabase->insert('obat', [
                'nama'      => $request->nama,
                'kategori'  => $request->kategori,
                'harga'     => (float) $request->harga,
                'status'    => $request->status,
                'deskripsi' => $request->deskripsi,
                'gambar'    => $imageUrl,
            ], $token);

            // ── Save penyakit relations ──
            $newObatId = $newObat[0]['id'] ?? $newObat['id'] ?? null;
            if ($newObatId) {
                $this->syncPenyakitRelations((int) $newObatId, $request->input('penyakit', []), $token);
            }

            // ── Notification ──
            \App\Services\NotificationService::add(
                'obat_create',
                'Obat Baru Ditambahkan 💊',
                "Obat \"{$request->nama}\" ({$request->kategori}) berhasil ditambahkan.",
                route('admin.obat.index')
            );

            return redirect()->route('admin.obat.index')
                ->with('success', 'Data obat berhasil ditambahkan!');

        } catch (\Throwable $e) {
            // Clean up uploaded image if DB insert fails
            if ($imageUrl) {
                $this->deleteImageFromUrl($imageUrl);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified medicine.
     */
    public function edit($id)
    {
        $token = session('supabase_token');
        $obat = $this->supabase->selectSingle('obat', $id, $token);

        if (! $obat) {
            return redirect()->route('admin.obat.index')
                ->with('error', 'Data obat tidak ditemukan.');
        }

        $allPenyakit    = $this->fetchAllPenyakit($token);
        $linkedPenyakit = $this->getLinkedPenyakitKeys((int) $id, $token);

        return view('admin.obat.edit', [
            'title'          => 'Edit Data Obat',
            'obat'           => $obat,
            'allPenyakit'    => $allPenyakit,
            'linkedPenyakit' => $linkedPenyakit,
        ]);
    }

    /**
     * Update the specified medicine in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama'      => ['required', 'string', 'max:255'],
            'kategori'  => ['required', 'string', 'max:100'],
            'harga'     => ['required', 'numeric', 'min:0'],
            'status'    => ['required', 'string', 'in:Aktif,Tidak Aktif'],
            'deskripsi' => ['required', 'string'],
            'gambar'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'penyakit'  => ['nullable', 'array'],
        ], [
            'nama.required'      => 'Nama obat wajib diisi.',
            'kategori.required'  => 'Kategori obat wajib diisi.',
            'harga.required'     => 'Harga obat wajib diisi.',
            'harga.numeric'      => 'Harga harus berupa angka.',
            'status.required'    => 'Status obat wajib dipilih.',
            'deskripsi.required' => 'Deskripsi obat wajib diisi.',
            'gambar.image'       => 'File harus berupa gambar.',
            'gambar.mimes'       => 'Format gambar yang diperbolehkan: jpeg, png, jpg, webp.',
            'gambar.max'         => 'Ukuran gambar maksimal 2MB.',
        ]);

        $token = session('supabase_token');
        $obat = $this->supabase->selectSingle('obat', $id, $token);

        if (! $obat) {
            return redirect()->route('admin.obat.index')
                ->with('error', 'Data obat tidak ditemukan.');
        }

        $oldImageUrl = $obat['gambar'] ?? null;
        $imageUrl = $oldImageUrl;

        try {
            // ── Process new image upload if present ──
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = 'obat/' . $filename;

                $imageUrl = $this->supabase->uploadStorage(
                    'images',
                    $path,
                    file_get_contents($file->getRealPath()),
                    $file->getMimeType()
                );

                // Delete old image from Storage
                if ($oldImageUrl) {
                    $this->deleteImageFromUrl($oldImageUrl);
                }
            }

            // ── Update database ──
            $this->supabase->update('obat', $id, [
                'nama'      => $request->nama,
                'kategori'  => $request->kategori,
                'harga'     => (float) $request->harga,
                'status'    => $request->status,
                'deskripsi' => $request->deskripsi,
                'gambar'    => $imageUrl,
            ], $token);

            // ── Sync penyakit relations ──
            $this->syncPenyakitRelations((int) $id, $request->input('penyakit', []), $token);

            // ── Notification ──
            \App\Services\NotificationService::add(
                'obat_update',
                'Data Obat Diperbarui ✏️',
                "Informasi obat \"{$request->nama}\" telah diperbarui.",
                route('admin.obat.index')
            );

            return redirect()->route('admin.obat.index')
                ->with('success', 'Data obat berhasil diperbarui!');

        } catch (\Throwable $e) {
            // Clean up newly uploaded image if update fails
            if ($request->hasFile('gambar') && $imageUrl !== $oldImageUrl) {
                $this->deleteImageFromUrl($imageUrl);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal memperbarui data: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified medicine from storage.
     */
    public function destroy($id)
    {
        $token = session('supabase_token');
        $obat = $this->supabase->selectSingle('obat', $id, $token);

        if (! $obat) {
            return redirect()->route('admin.obat.index')
                ->with('error', 'Data obat tidak ditemukan.');
        }

        try {
            // Clear penyakit relations in junction table
            $this->clearPenyakitRelations((int) $id, $token);

            // Delete record
            $deleted = $this->supabase->delete('obat', $id, $token);

            if ($deleted) {
                // Delete image from storage
                if (! empty($obat['gambar'])) {
                    $this->deleteImageFromUrl($obat['gambar']);
                }

                // ── Notification ──
                $namaObat = $obat['nama'] ?? 'Obat';
                \App\Services\NotificationService::add(
                    'obat_delete',
                    'Obat Dihapus 🗑️',
                    "Data obat \"{$namaObat}\" telah dihapus dari sistem.",
                    route('admin.obat.index')
                );

                return redirect()->route('admin.obat.index')
                    ->with('success', 'Data obat berhasil dihapus!');
            }

            return redirect()->route('admin.obat.index')
                ->with('error', 'Gagal menghapus data obat.');

        } catch (\Throwable $e) {
            return redirect()->route('admin.obat.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    //  Penyakit Relation Helpers (Many-to-Many)
    // ─────────────────────────────────────────────

    /**
     * Fetch all penyakit from all 3 plant tables, grouped by table.
     */
    private function fetchAllPenyakit(?string $token = null): array
    {
        $groups = [
            'penyakit_padi'  => '🌾 Padi',
            'penyakit_teh'   => '🍵 Teh',
            'penyakit_tomat' => '🍅 Tomat',
        ];

        $result = [];
        foreach ($groups as $table => $label) {
            $rows = $this->supabase->select($table, [
                'select' => 'id,nama_penyakit',
                'order'  => 'id.asc',
            ], $token);
            $result[$table] = [
                'label' => $label,
                'data'  => $rows,
            ];
        }

        return $result;
    }

    /**
     * Get set of currently linked penyakit keys for a given obat_id from junction table.
     * Returns array like: ["penyakit_padi:3" => true, "penyakit_teh:1" => true]
     */
    private function getLinkedPenyakitKeys(int $obatId, ?string $token): array
    {
        $relations = $this->supabase->select('obat_penyakit_relation', [
            'select'  => 'penyakit_id,penyakit_type',
            'obat_id' => 'eq.' . $obatId,
        ], $token);

        $linked = [];
        foreach ($relations as $rel) {
            $table = match ($rel['penyakit_type']) {
                'padi'  => 'penyakit_padi',
                'teh'   => 'penyakit_teh',
                'tomat' => 'penyakit_tomat',
                default => null,
            };

            if ($table) {
                $linked["{$table}:{$rel['penyakit_id']}"] = true;
            }
        }

        return $linked;
    }

    /**
     * Sync penyakit relations for an obat using many-to-many junction table.
     *
     * @param int    $obatId
     * @param array  $selected  e.g. ["penyakit_padi:3", "penyakit_teh:1"]
     * @param string $token
     */
    private function syncPenyakitRelations(int $obatId, array $selected, ?string $token): void
    {
        // Step 1: clear all existing junction table records for this obat
        $this->clearPenyakitRelations($obatId, $token);

        // Remove duplicates from selected list to prevent unique constraint violation
        $selected = array_unique($selected);

        // Step 2: insert new junction rows
        $relations = [];
        foreach ($selected as $key) {
            $parts = explode(':', $key, 2);
            if (count($parts) !== 2) {
                continue;
            }
            [$table, $penyakitId] = $parts;

            $type = match ($table) {
                'penyakit_padi'  => 'padi',
                'penyakit_teh'   => 'teh',
                'penyakit_tomat' => 'tomat',
                default          => null,
            };

            if ($type) {
                $relations[] = [
                    'obat_id'       => $obatId,
                    'penyakit_id'   => (int) $penyakitId,
                    'penyakit_type' => $type,
                ];
            }
        }

        if (! empty($relations)) {
            $this->supabase->insert('obat_penyakit_relation', $relations, $token);
        }
    }

    /**
     * Remove all junction table records for this obat.
     */
    private function clearPenyakitRelations(int $obatId, ?string $token): void
    {
        $this->supabase->deleteByCondition('obat_penyakit_relation', "obat_id=eq.{$obatId}", $token);
    }

    // ─────────────────────────────────────────────
    //  Helper to delete image using Supabase URL
    // ─────────────────────────────────────────────

    private function deleteImageFromUrl(string $url): void
    {
        $marker = '/public/images/';
        $pos = strpos($url, $marker);
        if ($pos !== false) {
            $path = substr($url, $pos + strlen($marker));
            $this->supabase->deleteStorage('images', $path);
        }
    }
}
