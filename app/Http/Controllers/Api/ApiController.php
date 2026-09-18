<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupabaseService;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function __construct(private SupabaseService $supabase) {}

    /**
     * Get all articles/tips with optional search.
     */
    public function getTips(Request $request)
    {
        $search = $request->query('search');

        $params = [
            'select' => '*',
            'status' => 'eq.Publikasi', // Only show published articles
            'order'  => 'published_at.desc',
        ];

        if (! empty($search)) {
            $params['or'] = "(title.ilike.*{$search}*,content.ilike.*{$search}*,category.ilike.*{$search}*)";
        }

        $articles = $this->supabase->select('tips', $params);

        $formattedArticles = collect($articles)->map(function ($article) {
            return $this->formatArticleData($article);
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar tips pertanian berhasil diambil.',
            'data'    => $formattedArticles
        ]);
    }

    /**
     * Get a single tip details by ID.
     */
    public function getTipById($id)
    {
        $article = $this->supabase->selectSingle('tips', $id);

        if (! $article) {
            return response()->json([
                'success' => false,
                'message' => 'Tips pertanian tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail tips pertanian berhasil diambil.',
            'data'    => $this->formatArticleData($article)
        ]);
    }

    /**
     * Get all pests (hama) with optional search.
     */
    public function getHama(Request $request)
    {
        $search = $request->query('search');

        $params = [
            'select' => '*',
            'order'  => 'nama.asc',
        ];

        if (! empty($search)) {
            $params['or'] = "(nama.ilike.*{$search}*,deskripsi.ilike.*{$search}*,kategori.ilike.*{$search}*)";
        }

        $hama = $this->supabase->select('hama', $params);

        return response()->json([
            'success' => true,
            'message' => 'Daftar hama & penyakit berhasil diambil.',
            'data'    => $hama
        ]);
    }

    /**
     * Get a single pest/disease details by ID.
     */
    public function getHamaById($id)
    {
        $hama = $this->supabase->selectSingle('hama', $id);

        if (! $hama) {
            return response()->json([
                'success' => false,
                'message' => 'Data hama & penyakit tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail hama & penyakit berhasil diambil.',
            'data'    => $hama
        ]);
    }

    /**
     * Helper: Parse markdown content to extract Quick Solutions and Tools/Materials sections.
     */
    private function formatArticleData(array $article): array
    {
        $content = $article['content'] ?? '';
        
        $solusiCepat = '';
        $alatBahan = [];
        $panduanLengkap = $content;

        // Parse ### ⚡ Solusi Cepat
        if (preg_match('/### ⚡ Solusi Cepat(.*?)(###|$)/s', $content, $matches)) {
            $solusiCepat = trim($matches[1]);
        }

        // Parse ### 🛠️ Alat & Bahan
        if (preg_match('/### 🛠️ Alat & Bahan(.*?)(###|$)/s', $content, $matches)) {
            $alatBahanRaw = trim($matches[1]);
            // Extract items starting with '-'
            preg_match_all('/-\s*(.*)/', $alatBahanRaw, $listMatches);
            if (! empty($listMatches[1])) {
                $alatBahan = array_map('trim', $listMatches[1]);
            }
        }

        // Parse ### 📖 Panduan Lengkap
        if (preg_match('/### 📖 Panduan Lengkap(.*)/s', $content, $matches)) {
            $panduanLengkap = trim($matches[1]);
        }

        // Parse ### 🌐 Sumber
        $sumberLabel = null;
        $sumberUrl = null;
        if (preg_match('/### 🌐 Sumber\s*[:\n]\s*(.*?)(###|$)/su', $content, $matches)) {
            $rawSumber = trim($matches[1]);
            if (preg_match('/\[([^\]]+)\]\((https?:\/\/[^\)]+)\)/', $rawSumber, $linkMatch)) {
                $sumberLabel = $linkMatch[1];
                $sumberUrl = $linkMatch[2];
            } else {
                $sumberLabel = $rawSumber;
                if (filter_var($rawSumber, FILTER_VALIDATE_URL)) {
                    $sumberUrl = $rawSumber;
                }
            }
        }

        // Clean panduanLengkap so it doesn't leave ### 🌐 Sumber hanging at the end of text
        $cleanPanduan = preg_replace('/\n*### 🌐 Sumber.*$/su', '', $panduanLengkap);
        $panduanLengkap = trim($cleanPanduan);

        // Include source directly and in parsed object
        $article['source'] = $sumberUrl ?: $sumberLabel;
        $article['source_label'] = $sumberLabel;
        $article['source_url'] = $sumberUrl;

        // Include both original fields and parsed fields for total compatibility
        $article['parsed'] = [
            'solusi_cepat'    => $solusiCepat ?: null,
            'alat_bahan'      => ! empty($alatBahan) ? $alatBahan : null,
            'panduan_lengkap' => $panduanLengkap,
            'sumber'          => $sumberUrl ?: $sumberLabel,
        ];

        return $article;
    }
}
