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

        return view('admin.obat.index', [
            'title'    => 'Database Obat',
            'obatList' => $obatList,
            'search'   => $search,
        ]);
    }

    /**
     * Show the form for creating a new medicine.
     */
    public function create()
    {
        return view('admin.obat.create', [
            'title' => 'Tambah Obat Baru',
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
            $this->supabase->insert('obat', [
                'nama'      => $request->nama,
                'kategori'  => $request->kategori,
                'harga'     => (float) $request->harga,
                'status'    => $request->status,
                'deskripsi' => $request->deskripsi,
                'gambar'    => $imageUrl,
            ], $token);

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

        return view('admin.obat.edit', [
            'title' => 'Edit Data Obat',
            'obat'  => $obat,
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
            // Delete record
            $deleted = $this->supabase->delete('obat', $id, $token);

            if ($deleted) {
                // Delete image from storage
                if (! empty($obat['gambar'])) {
                    $this->deleteImageFromUrl($obat['gambar']);
                }

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
