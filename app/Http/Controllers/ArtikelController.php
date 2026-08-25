<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArtikelController extends Controller
{
    public function __construct(private SupabaseService $supabase) {}

    /**
     * Display a listing of the articles.
     */
    public function index(Request $request)
    {
        $token = session('supabase_token');
        $search = $request->query('search');

        // PostgREST query parameters
        $params = [
            'select' => '*',
            'order'  => 'published_at.desc',
        ];

        // Apply search filter if provided
        if (! empty($search)) {
            $params['or'] = "(title.ilike.*{$search}*,content.ilike.*{$search}*,category.ilike.*{$search}*)";
        }

        // Fetch articles from 'tips' table in Supabase
        $articles = $this->supabase->select('tips', $params, $token);

        return view('admin.artikel.index', [
            'title'    => 'Artikel & Tips Pertanian',
            'articles' => $articles,
            'search'   => $search,
        ]);
    }

    /**
     * Show the form for creating a new article.
     */
    public function create()
    {
        return view('admin.artikel.create', [
            'title' => 'Tulis Artikel Baru',
        ]);
    }

    /**
     * Store a newly created article in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'category'     => ['required', 'string', 'max:100'],
            'status'       => ['required', 'string', 'in:Publikasi,Draf'],
            'published_at' => ['required', 'date'],
            'content'      => ['required', 'string'],
            'thumbnail'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'title.required'        => 'Judul artikel wajib diisi.',
            'category.required'     => 'Kategori wajib dipilih.',
            'status.required'       => 'Status publikasi wajib dipilih.',
            'published_at.required' => 'Tanggal publikasi wajib diisi.',
            'content.required'      => 'Isi konten artikel wajib diisi.',
            'thumbnail.image'       => 'File harus berupa gambar.',
            'thumbnail.mimes'       => 'Format gambar yang diperbolehkan: jpeg, png, jpg, webp.',
            'thumbnail.max'         => 'Ukuran gambar maksimal 2MB.',
        ]);

        $token = session('supabase_token');
        $imageUrl = null;

        try {
            // Upload to Supabase Storage if uploaded
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = 'tips/' . $filename;

                $imageUrl = $this->supabase->uploadStorage(
                    'images',
                    $path,
                    file_get_contents($file->getRealPath()),
                    $file->getMimeType()
                );
            }

            // Save to database 'tips' table (which the mobile app queries)
            $this->supabase->insert('tips', [
                'title'        => $request->title,
                'category'     => $request->category,
                'content'      => $request->input('content'),
                'status'       => $request->status,
                'published_at' => date('c', strtotime($request->published_at)),
                'image_url'    => $imageUrl,
            ], $token);

            return redirect()->route('admin.artikel.index')
                ->with('success', 'Artikel berhasil diterbitkan!');

        } catch (\Throwable $e) {
            // Clean up uploaded image if insert fails
            if ($imageUrl) {
                $this->deleteImageFromUrl($imageUrl);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal menyimpan artikel: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified article.
     */
    public function edit($id)
    {
        $token = session('supabase_token');
        $article = $this->supabase->selectSingle('tips', $id, $token);

        if (! $article) {
            return redirect()->route('admin.artikel.index')
                ->with('error', 'Artikel tidak ditemukan.');
        }

        return view('admin.artikel.edit', [
            'title'   => 'Edit Artikel',
            'article' => $article,
        ]);
    }

    /**
     * Update the specified article in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'category'     => ['required', 'string', 'max:100'],
            'status'       => ['required', 'string', 'in:Publikasi,Draf'],
            'published_at' => ['required', 'date'],
            'content'      => ['required', 'string'],
            'thumbnail'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'title.required'        => 'Judul artikel wajib diisi.',
            'category.required'     => 'Kategori wajib dipilih.',
            'status.required'       => 'Status publikasi wajib dipilih.',
            'published_at.required' => 'Tanggal publikasi wajib diisi.',
            'content.required'      => 'Isi konten artikel wajib diisi.',
            'thumbnail.image'       => 'File harus berupa gambar.',
            'thumbnail.mimes'       => 'Format gambar yang diperbolehkan: jpeg, png, jpg, webp.',
            'thumbnail.max'         => 'Ukuran gambar maksimal 2MB.',
        ]);

        $token = session('supabase_token');
        $article = $this->supabase->selectSingle('tips', $id, $token);

        if (! $article) {
            return redirect()->route('admin.artikel.index')
                ->with('error', 'Artikel tidak ditemukan.');
        }

        $oldImageUrl = $article['image_url'] ?? null;
        $imageUrl = $oldImageUrl;

        try {
            // Upload new image if present
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = 'tips/' . $filename;

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

            // Update database 'tips' table
            $this->supabase->update('tips', $id, [
                'title'        => $request->title,
                'category'     => $request->category,
                'content'      => $request->input('content'),
                'status'       => $request->status,
                'published_at' => date('c', strtotime($request->published_at)),
                'image_url'    => $imageUrl,
            ], $token);

            return redirect()->route('admin.artikel.index')
                ->with('success', 'Artikel berhasil diperbarui!');

        } catch (\Throwable $e) {
            // Clean up newly uploaded image if update fails
            if ($request->hasFile('thumbnail') && $imageUrl !== $oldImageUrl) {
                $this->deleteImageFromUrl($imageUrl);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal memperbarui artikel: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified article from storage.
     */
    public function destroy($id)
    {
        $token = session('supabase_token');
        $article = $this->supabase->selectSingle('tips', $id, $token);

        if (! $article) {
            return redirect()->route('admin.artikel.index')
                ->with('error', 'Artikel tidak ditemukan.');
        }

        try {
            // Delete record
            $deleted = $this->supabase->delete('tips', $id, $token);

            if ($deleted) {
                // Delete image from storage
                if (! empty($article['image_url'])) {
                    $this->deleteImageFromUrl($article['image_url']);
                }

                return redirect()->route('admin.artikel.index')
                    ->with('success', 'Artikel berhasil dihapus!');
            }

            return redirect()->route('admin.artikel.index')
                ->with('error', 'Gagal menghapus artikel.');

        } catch (\Throwable $e) {
            return redirect()->route('admin.artikel.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    //  Helper to delete image from Supabase Storage
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
