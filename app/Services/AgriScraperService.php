<?php

namespace App\Services;

use App\Services\SupabaseService;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AgriScraperService
{
    public function __construct(
        private SupabaseService $supabase,
        private GeminiService $gemini
    ) {}

    /**
     * Scrape a single URL, process with Gemini, and save to Supabase.
     *
     * @param string $url The agricultural webpage URL to scrape.
     * @return array Results summary of the operation.
     */
    public function scrapeAndSyncUrl(string $url): array
    {
        Log::info("AgriScraperService: Starting scraping for URL: {$url}");

        try {
            $response = Http::withOptions([
                'version' => 1.1,
                'verify'  => false,
            ])->withHeaders([
                'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            ])->timeout(30)->get($url);

            if ($response->failed()) {
                throw new \Exception("Gagal mengambil halaman web. HTTP Status: " . $response->status());
            }

            $html = $response->body();
            $scrapedData = $this->parseHtmlContent($html, $url);

            if (empty($scrapedData['text_content'])) {
                throw new \Exception("Tidak ada konten teks yang berhasil diekstrak dari halaman.");
            }

            // ── Filter Relevansi: Cek dulu apakah konten ini tentang pertanian ──
            Log::info("AgriScraperService: Memeriksa relevansi konten...");
            if (! $this->gemini->isAgricultureContent($scrapedData['text_content'])) {
                throw new \Exception("Konten tidak relevan dengan pertanian (judi, hiburan, dll). Dilewati.");
            }

            // Call Gemini LLM to simplify and structure
            Log::info("AgriScraperService: Sending extracted text to Gemini API for processing...");
            $parsedJson = $this->gemini->processAgriContent($scrapedData['text_content']);

            if (! $parsedJson) {
                throw new \Exception("Gemini gagal memproses dan menstrukturkan konten.");
            }

            // Sync to Supabase Database
            $syncResult = $this->saveToDatabase($parsedJson, $scrapedData['image_url'] ?? null, $url);

            return [
                'success' => true,
                'message' => 'Scrape dan Sinkronisasi berhasil!',
                'source'  => $url,
                'data'    => $syncResult
            ];

        } catch (\Throwable $e) {
            Log::error("AgriScraperService: Error scraping URL: {$url}. Message: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'source'  => $url
            ];
        }
    }

    /**
     * Parse HTML, extract featured image (rejecting logos & icons), and extract readable text.
     */
    private function parseHtmlContent(string $html, string $baseUrl): array
    {
        // Disable external entity loading and suppress HTML errors
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // 1. Prioritaskan pengambilan thumbnail dari meta tags (og:image, twitter:image)
        $imageUrl = $this->extractMetaImage($xpath, $baseUrl);

        // 2. Jika tidak ada di meta tags, cari dari tag img artikel dengan filter ketat
        if (empty($imageUrl)) {
            $imageUrl = $this->extractArticleImage($xpath, $baseUrl);
        }

        // 3. Hapus tag yang tidak diperlukan sebelum mengambil plain text
        $junkTags = ['script', 'style', 'noscript', 'iframe', 'header', 'footer', 'nav'];
        foreach ($junkTags as $tag) {
            $elements = $dom->getElementsByTagName($tag);
            while ($elements->length > 0) {
                $p = $elements->item(0);
                $p->parentNode->removeChild($p);
            }
        }

        // 4. Extract clean plain text
        $body = $dom->getElementsByTagName('body');
        $textContent = '';
        if ($body->length > 0) {
            $textContent = $body->item(0)->textContent;
        } else {
            $textContent = $dom->textContent;
        }

        // Clean up whitespace
        $textContent = preg_replace('/\s+/', ' ', $textContent);
        $textContent = trim($textContent);

        // Limit to 20,000 characters to keep it within safe token limits
        if (strlen($textContent) > 20000) {
            $textContent = substr($textContent, 0, 20000) . '... (truncated)';
        }

        return [
            'text_content' => $textContent,
            'image_url'    => $imageUrl
        ];
    }

    /**
     * Ekstrak URL gambar dari open graph / twitter meta tags.
     */
    private function extractMetaImage(\DOMXPath $xpath, string $baseUrl): ?string
    {
        $metaQueries = [
            '//meta[@property="og:image"]/@content',
            '//meta[@name="og:image"]/@content',
            '//meta[@property="og:image:url"]/@content',
            '//meta[@property="og:image:secure_url"]/@content',
            '//meta[@name="twitter:image"]/@content',
            '//meta[@property="twitter:image"]/@content',
            '//link[@rel="image_src"]/@href',
        ];

        foreach ($metaQueries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes && $nodes->length > 0) {
                $raw = trim($nodes->item(0)->nodeValue);
                if ($this->isValidArticleImage($raw)) {
                    return $this->resolveAbsoluteUrl($raw, $baseUrl);
                }
            }
        }

        return null;
    }

    /**
     * Ekstrak gambar artikel dari elemen <img> dengan mengabaikan logo dan ikon.
     */
    private function extractArticleImage(\DOMXPath $xpath, string $baseUrl): ?string
    {
        // Cari gambar di dalam container artikel terlebih dahulu
        $articleImgQueries = [
            '//article//img',
            '//main//img',
            '//*[contains(@class, "post-")]//img',
            '//*[contains(@class, "entry-content")]//img',
            '//*[contains(@class, "content")]//img',
            '//img',
        ];

        foreach ($articleImgQueries as $query) {
            $nodes = $xpath->query($query);
            if (! $nodes) {
                continue;
            }

            foreach ($nodes as $img) {
                /** @var \DOMElement $img */
                // Periksa berbagai atribut lazy loading atau source gambar
                $src = $img->getAttribute('data-orig-file')
                    ?: $img->getAttribute('data-src')
                    ?: $img->getAttribute('data-lazy-src')
                    ?: $img->getAttribute('src');

                // Jika ada srcset, coba ambil link gambar dengan resolusi terbaik
                $srcset = $img->getAttribute('srcset') ?: $img->getAttribute('data-srcset');
                if ($srcset) {
                    $parts = explode(',', $srcset);
                    $lastPart = trim(end($parts));
                    $urlCandidate = preg_split('/\s+/', $lastPart)[0] ?? null;
                    if ($urlCandidate && $this->isValidArticleImage($urlCandidate)) {
                        $src = $urlCandidate;
                    }
                }

                if ($this->isValidArticleImage($src)) {
                    return $this->resolveAbsoluteUrl($src, $baseUrl);
                }
            }
        }

        return null;
    }

    /**
     * Validasi apakah sebuah URL gambar adalah gambar artikel yang sah (bukan logo/tracking/icon).
     */
    private function isValidArticleImage(?string $src): bool
    {
        if (empty($src)) {
            return false;
        }

        $srcLower = strtolower($src);

        // Abaikan base64, SVG, gif transparan/loader
        if (str_contains($srcLower, 'base64') ||
            str_contains($srcLower, '.svg') ||
            str_contains($srcLower, 'ajax-loader') ||
            str_contains($srcLower, 'loading.gif')) {
            return false;
        }

        // Abaikan logo dan branding situs
        $blacklistedKeywords = [
            'logo', 'brand', 'avatar', 'gravatar', 'icon', 'button', 'badge',
            'google-play', 'app-store', 'pixel', 'bping', 'tracking',
            'cdn-fileserver', 'banner', 'ads', 'ad-', 'header', 'footer',
            'cropped-logo', 'lumbung-informasi', 'horti_indonesia.png', 'msmb-logo'
        ];

        foreach ($blacklistedKeywords as $kw) {
            if (str_contains($srcLower, $kw)) {
                return false;
            }
        }

        // Pastikan memiliki ekstensi gambar yang umum atau URL yang valid
        $allowedExtensions = ['.jpg', '.jpeg', '.png', '.webp', '.bmp'];
        $hasValidExt = false;
        foreach ($allowedExtensions as $ext) {
            if (str_contains($srcLower, $ext)) {
                $hasValidExt = true;
                break;
            }
        }

        return $hasValidExt || filter_var($src, FILTER_VALIDATE_URL);
    }

    /**
     * Konversi URL relatif menjadi URL absolut.
     */
    private function resolveAbsoluteUrl(string $src, string $baseUrl): string
    {
        $src = trim($src);

        if (str_starts_with($src, '//')) {
            $parsedUrl = parse_url($baseUrl);
            return ($parsedUrl['scheme'] ?? 'https') . ':' . $src;
        }

        if (str_starts_with($src, '/')) {
            $parsedUrl = parse_url($baseUrl);
            $host = ($parsedUrl['scheme'] ?? 'https') . '://' . ($parsedUrl['host'] ?? '');
            return rtrim($host, '/') . $src;
        }

        if (! str_starts_with($src, 'http')) {
            return rtrim($baseUrl, '/') . '/' . ltrim($src, '/');
        }

        return $src;
    }

    /**
     * Save the processed data to Supabase database.
     */
    public function saveToDatabase(array $data, ?string $fallbackImageUrl, string $sourceUrl): array
    {
        $imageUrl = $fallbackImageUrl;

        // Jika tidak ada gambar yang valid dari hasil scrape (atau ditolak karena logo),
        // gunakan gambar berkualitas tinggi berdasarkan topik artikel
        if (empty($imageUrl) || ! $this->isValidArticleImage($imageUrl)) {
            $imageUrl = $this->getTopicSpecificImage($data['title'] ?? '', $data['category'] ?? '');
        }

        if ($data['is_pest_info'] ?? false) {
            // --- Save to 'hama' table ---
            $pest = $data['pest_details'] ?? [];
            $pestData = [
                'nama'           => $pest['nama'] ?? 'Hama/Penyakit Baru',
                'kategori'       => $pest['kategori'] ?? 'Umum',
                'deskripsi'      => $pest['deskripsi'] ?? 'Deskripsi tidak tersedia.',
                'ciri_ciri'      => $pest['ciri_ciri'] ?? 'Gejala tidak tersedia.',
                'cara_mengatasi' => $pest['cara_mengatasi'] ?? 'Penanganan tidak tersedia.',
                'gambar_url'     => $imageUrl,
            ];

            Log::info("AgriScraperService: Saving to 'hama' table...", $pestData);
            
            $existing = $this->supabase->select('hama', [
                'nama'   => 'eq.' . ($pest['nama'] ?? ''),
                'select' => 'id'
            ]);

            if (! empty($existing)) {
                $id = $existing[0]['id'];
                Log::info("AgriScraperService: Update existing hama ID {$id}");
                $record = $this->supabase->update('hama', $id, $pestData);
            } else {
                Log::info("AgriScraperService: Insert new hama record");
                $record = $this->supabase->insert('hama', $pestData);
            }

            return [
                'type' => 'hama',
                'record' => $record
            ];
        } else {
            // --- Save to 'tips' (articles) table ---
            
            $formattedContent = '';
            
            if (! empty($data['solusi_cepat'])) {
                $formattedContent .= "### ⚡ Solusi Cepat\n" . $data['solusi_cepat'] . "\n\n";
            }
            
            if (! empty($data['alat_bahan']) && is_array($data['alat_bahan'])) {
                $formattedContent .= "### 🛠️ Alat & Bahan\n";
                foreach ($data['alat_bahan'] as $item) {
                    $formattedContent .= "- " . trim($item) . "\n";
                }
                $formattedContent .= "\n";
            }
            
            $formattedContent .= "### 📖 Panduan Lengkap\n" . $data['content'];

            // ── Cantumkan Informasi Sumber Secara Rapi ──
            $sourceFormatted = $this->formatSourceAttribution($sourceUrl);
            if (! empty($sourceFormatted)) {
                $formattedContent .= "\n\n" . $sourceFormatted;
            }

            $articleData = [
                'title'        => $data['title'] ?? 'Tips Pertanian Baru',
                'category'     => $data['category'] ?? 'Tips Tani',
                'content'      => $formattedContent,
                'status'       => 'Publikasi',
                'published_at' => now()->toIso8601String(),
                'image_url'    => $imageUrl,
            ];

            Log::info("AgriScraperService: Saving to 'tips' table...", [
                'title'    => $articleData['title'],
                'category' => $articleData['category']
            ]);

            // Check if article with exact title already exists
            $existing = $this->supabase->select('tips', [
                'title'  => 'eq.' . ($data['title'] ?? ''),
                'select' => 'id'
            ]);

            if (! empty($existing)) {
                $id = $existing[0]['id'];
                Log::info("AgriScraperService: Update existing article ID {$id}");
                $record = $this->supabase->update('tips', $id, $articleData);
            } else {
                Log::info("AgriScraperService: Insert new article record");
                $record = $this->supabase->insert('tips', $articleData);
            }

            return [
                'type' => 'tips',
                'record' => $record
            ];
        }
    }

    /**
     * Format pencantuman sumber artikel.
     */
    public function formatSourceAttribution(string $sourceUrl): string
    {
        if (empty($sourceUrl)) {
            return '';
        }

        if (str_starts_with($sourceUrl, 'ai-search://')) {
            return "### 🌐 Sumber\nRiset AI Google Search (Data Terkini Pertanian)";
        }

        $label = $this->determineSourceLabel($sourceUrl);

        return "### 🌐 Sumber\n[{$label}]({$sourceUrl})";
    }

    /**
     * Tentukan label sumber yang mudah dibaca berdasarkan domain/URL.
     */
    public function determineSourceLabel(string $url): string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        if (str_contains($host, 'hortiindonesia')) {
            return 'Horti Indonesia';
        }
        if (str_contains($host, 'msmbindonesia')) {
            return 'MSMB Indonesia';
        }
        if (str_contains($host, 'digitani.ipb')) {
            return 'IPB Digitani';
        }
        if (str_contains($host, 'pertanian.go.id')) {
            return 'Kementerian Pertanian RI';
        }
        if (str_contains($host, 'demakkab.go.id')) {
            return 'Dinas Pertanian & Pangan Kab. Demak';
        }

        return ! empty($host) ? $host : 'Portal Berita Tani';
    }

    /**
     * Berikan gambar ilustrasi pertanian spesifik sesuai topik artikel.
     */
    public function getTopicSpecificImage(string $title, string $category = ''): string
    {
        $text = strtolower($title . ' ' . $category);

        if (str_contains($text, 'bawang')) {
            return 'https://images.unsplash.com/photo-1540148426945-6cf22a6b2383?w=800'; // Garlic/Onion harvest
        }
        if (str_contains($text, 'melon') || str_contains($text, 'semangka')) {
            return 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=800'; // Melon/Watermelon
        }
        if (str_contains($text, 'hidroponik') || str_contains($text, 'sayur')) {
            return 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?w=800'; // Hydroponics/Greenhouse
        }
        if (str_contains($text, 'ikan') || str_contains($text, 'koi') || str_contains($text, 'kolam') || str_contains($text, 'tambak')) {
            return 'https://images.unsplash.com/photo-1522069169874-c58ec4b76be5?w=800'; // Fish / Koi aquaculture
        }
        if (str_contains($text, 'jagung')) {
            return 'https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=800'; // Corn field
        }
        if (str_contains($text, 'padi') || str_contains($text, 'sawah') || str_contains($text, 'beras')) {
            return 'https://images.unsplash.com/photo-1536657464919-892534f60d6e?w=800'; // Lush rice terraces
        }
        if (str_contains($text, 'cabai') || str_contains($text, 'cabe')) {
            return 'https://images.unsplash.com/photo-1588252303782-cb80119abd6d?w=800'; // Fresh chili peppers
        }
        if (str_contains($text, 'lidah buaya') || str_contains($text, 'aloe')) {
            return 'https://images.unsplash.com/photo-1596547609652-9cf5d8d76921?w=800'; // Aloe vera plants
        }
        if (str_contains($text, 'hama') || str_contains($text, 'wereng') || str_contains($text, 'ulat') || str_contains($text, 'penyakit')) {
            return 'https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?w=800'; // Plant inspection
        }
        if (str_contains($text, 'pupuk') || str_contains($text, 'tanah') || str_contains($text, 'kompos')) {
            return 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=800'; // Fertile soil & seedling
        }
        if (str_contains($text, 'cuaca') || str_contains($text, 'kemarau') || str_contains($text, 'hujan') || str_contains($text, 'irigasi')) {
            return 'https://images.unsplash.com/photo-1534274988757-a28bf1a57c17?w=800'; // Irrigation and rain
        }

        // Default landscape foto pertanian premium
        return 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?w=800';
    }
}
