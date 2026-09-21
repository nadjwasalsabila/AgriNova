<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', '');
        $this->model  = env('GEMINI_MODEL', 'gemini-2.5-flash');
    }

    /**
     * Check if Gemini Service is configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Strict relevance check: returns false if content is NOT about agriculture.
     * This blocks gambling, entertainment, politics, gossip, and all non-farm content.
     */
    public function isAgricultureContent(string $rawContent): bool
    {
        if (! $this->isConfigured()) {
            return true; // Default allow if not configured
        }

        try {
            $snippet = substr($rawContent, 0, 4000);
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

            $prompt = <<<PROMPT
Kamu adalah filter konten ketat untuk platform pertanian Indonesia "Petani Maju". Tugasmu adalah menentukan apakah sebuah teks BENAR-BENAR berkaitan dengan pertanian.

TOPIK YANG DIIZINKAN (jawab YA):
- Budidaya tanaman pangan (padi, jagung, kedelai, singkong, dll)
- Hortikultura (sayuran, buah-buahan, tanaman hias)
- Perkebunan (kelapa sawit, karet, teh, kopi, kakao, tebu, dll)
- Hama dan penyakit tanaman (ulat, kutu, jamur, bakteri, virus tanaman)
- Pupuk, pestisida, dan pengairan / irigasi
- Teknologi pertanian (smart farming, drone pertanian, greenhouse)
- Peternakan dan perikanan (ayam, sapi, ikan, udang, tambak)
- Ketahanan pangan, agribisnis, dan ekonomi pertanian
- Benih, pembibitan, dan teknik bercocok tanam

TOPIK YANG DILARANG KERAS (jawab TIDAK):
- Judi online, slot, casino, togel, poker
- Gosip artis, berita selebriti, hiburan
- Politik, pemerintahan umum (kecuali kebijakan pertanian spesifik)
- Teknologi umum (HP, laptop, aplikasi non-pertanian)
- Keuangan dan investasi saham (kecuali agribisnis)
- Olahraga, musik, film
- Kesehatan manusia / medis (kecuali jika terkait petani secara langsung)
- Promosi produk non-pertanian
- Konten dewasa atau tidak pantas

INSTRUKSI PENTING:
- Baca teks dengan cermat sebelum memutuskan
- Jika teks DOMINAN membahas pertanian, jawab YA walaupun ada sedikit topik lain
- Jika teks DOMINAN membahas topik terlarang di atas, jawab TIDAK
- Jawab HANYA dengan satu kata: YA atau TIDAK (huruf kapital, tanpa tanda baca, tanpa penjelasan)

TEKS YANG PERLU DIPERIKSA:
{$snippet}
PROMPT;

            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(25)
                ->post($url, [
                    'contents' => [[
                        'parts' => [['text' => $prompt]]
                    ]],
                    'generationConfig' => [
                        'temperature'     => 0.0,
                        'maxOutputTokens' => 10,
                        'thinkingConfig'  => ['thinkingBudget' => 0],
                    ]
                ]);

            $text = strtoupper(trim(data_get($response->json(), 'candidates.0.content.parts.0.text', 'YA')));
            $isAgri = str_contains($text, 'YA');

            Log::info("GeminiService: isAgricultureContent result: {$text} → " . ($isAgri ? 'LOLOS' : 'DITOLAK'));

            return $isAgri;
        } catch (\Throwable $e) {
            Log::warning("GeminiService: isAgricultureContent error: " . $e->getMessage() . " — defaulting to allow");
            return true; // Default allow on error
        }
    }

    /**
     * Process agricultural raw content and return a structured JSON array.
     */
    public function processAgriContent(string $rawContent): ?array
    {
        $prompt = <<<PROMPT
Anda adalah asisten AI pertanian ahli di Indonesia. Tugas Anda adalah menganalisis, menyederhanakan, dan menstrukturkan konten mentah (artikel/hama) pertanian berikut ke dalam bahasa petani lokal ("Bahasa Tani") yang ramah, sopan, mudah dipahami, praktis, dan informatif.

Berikut adalah konten pertanian mentah yang perlu diproses:
=== KONTEN MENTAH ===
{$rawContent}
======================

Silakan lakukan hal berikut:
1. Analisis apakah konten tersebut berisi panduan/tutorial tips bercocok tanam secara umum, ATAU info spesifik mengenai hama/penyakit tanaman. Set flag "is_pest_info" menjadi true jika ini info hama/penyakit, atau false jika ini tips/tutorial biasa.
2. Paragraf atau konten utama harus disederhanakan agar tidak terlalu akademis/rumit. Gunakan istilah bahasa Indonesia yang akrab bagi petani lokal.
3. Ekstrak data sesuai dengan format JSON berikut:

Format Output JSON:
{
  "is_pest_info": false, // Set ke true jika ini info hama/penyakit, false jika tips/tutorial bercocok tanam umum.
  
  // Isi bagian ini jika "is_pest_info" = false (Tips / Tutorial)
  "title": "Judul Artikel yang Menarik & Sederhana",
  "category": "Pilih salah satu: Tips Tani | Hama & Penyakit | Cuaca & Musim | Teknologi Tani | Pupuk & Tanah | Lainnya",
  "solusi_cepat": "Ringkasan solusi cepat atau ringkasan tips dalam 2-3 kalimat.",
  "alat_bahan": ["Alat/bahan 1", "Alat/bahan 2"], // Jika ada, jika tidak ada isi array kosong
  "content": "Isi artikel lengkap yang sudah disederhanakan dalam Bahasa Tani. Gunakan format markdown dasar (seperti list, subheadings, emoji) untuk visual yang menarik.",

  // Isi bagian ini jika "is_pest_info" = true (Informasi Hama & Penyakit)
  "pest_details": {
    "nama": "Nama Hama atau Penyakit",
    "kategori": "Kategori tanaman yang diserang (contoh: Padi | Teh | Tomat | Umum)",
    "deskripsi": "Penjelasan sederhana apa itu hama/penyakit ini dan dampaknya.",
    "ciri_ciri": "Gejala serangan atau ciri fisik hama secara singkat dalam poin-poin.",
    "cara_mengatasi": "Langkah-langkah penanganan/pengendalian secara praktis."
  }
}

Harap pastikan output hanya berupa JSON valid dan tidak ada teks pendahuluan atau penutup lainnya.
PROMPT;

        return $this->callGeminiApi($prompt);
    }

    /**
     * Discover trending agricultural topics in Indonesia using Google Search Grounding.
     *
     * AI actively searches the internet to find what topics are currently trending
     * in Indonesian agriculture — pest outbreaks, weather advisories, cultivation tips, etc.
     * Returns an array of 2-3 specific, actionable topic strings for further processing.
     *
     * @return string[] Array of trending topic strings, empty on failure.
     */
    public function discoverTrendingAgriTopics(): array
    {
        $today  = now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y');
        $month  = now()->setTimezone('Asia/Jakarta')->translatedFormat('F Y');
        $season = $this->getCurrentIndonesiaSeason();

        $prompt = <<<PROMPT
Hari ini tanggal {$today}. Musim saat ini di Indonesia: {$season}.

Gunakan kemampuan pencarian internet kamu untuk menemukan topik-topik pertanian Indonesia yang SEDANG HANGAT DIBICARAKAN atau PALING RELEVAN saat ini di bulan {$month}.

Fokus pada:
- Serangan hama atau penyakit tanaman yang sedang terjadi di Indonesia
- Cuaca ekstrem atau anomali iklim yang mempengaruhi pertanian Indonesia
- Tips budidaya yang relevan dengan musim {$season} saat ini
- Teknologi atau inovasi pertanian Indonesia yang baru dirilis atau dipublikasikan
- Komoditas pertanian yang sedang menjadi perhatian (harga, kelangkaan, overproducing)

Cari dan temukan TEPAT 2 topik yang paling relevan dan spesifik untuk petani Indonesia hari ini.

Kembalikan HANYA array JSON berisi 2 string topik (tanpa teks lain di luar JSON), contoh:
["hama wereng coklat menyerang lahan padi jawa tengah agustus 2026", "cara mengatasi kekeringan tanaman cabai musim kemarau panjang indonesia"]

Pastikan setiap topik:
- Spesifik dan actionable untuk petani lokal Indonesia
- Mencerminkan kondisi TERKINI (bukan topik umum yang tidak bergantung waktu)
- Dalam Bahasa Indonesia
- Cukup detail untuk bisa dicari lebih lanjut
PROMPT;

        try {
            // Gunakan Google Search Grounding untuk mencari topik trending secara real-time
            $result = $this->callGeminiApiRaw($prompt, enableSearch: true, mimeType: 'application/json');

            if (empty($result)) {
                return [];
            }

            // Bersihkan response: hapus markdown code fence jika ada
            $cleaned = preg_replace('/^```(?:json)?\s*/i', '', trim($result));
            $cleaned = preg_replace('/\s*```$/', '', $cleaned);

            $topics = json_decode($cleaned, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($topics)) {
                Log::warning('GeminiService: discoverTrendingAgriTopics failed to parse JSON.', [
                    'raw' => $result
                ]);
                return [];
            }

            // Validasi: pastikan semua item adalah string non-kosong
            $topics = array_values(array_filter($topics, fn ($t) => is_string($t) && ! empty(trim($t))));

            Log::info('GeminiService: discoverTrendingAgriTopics found topics.', ['topics' => $topics]);
            return $topics;

        } catch (\Throwable $e) {
            Log::error('GeminiService: discoverTrendingAgriTopics exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Discover pests and diseases currently threatening Indonesian agriculture.
     *
     * Menggunakan Google Search Grounding untuk mencari hama/penyakit yang
     * SEDANG MEWABAH atau menjadi perhatian petani Indonesia saat ini.
     * Berbeda dari PRIORITY_HAMA_LIST yang statis — ini berbasis data internet real-time.
     *
     * @return array[] Array of pest candidates: [{nama, tanaman, kategori_hama}, ...]
     */
    public function discoverTrendingPests(): array
    {
        $today  = now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y');
        $month  = now()->setTimezone('Asia/Jakarta')->translatedFormat('F Y');
        $season = $this->getCurrentIndonesiaSeason();

        $prompt = <<<PROMPT
Hari ini tanggal {$today}. Musim saat ini di Indonesia: {$season}.

Gunakan kemampuan pencarian internet kamu untuk menemukan hama dan penyakit tanaman pertanian yang SEDANG MEWABAH, menyebar, atau paling banyak dilaporkan petani Indonesia di bulan {$month}.

Fokus pencarian:
- Hama atau penyakit tanaman yang sedang menjadi wabah/outbreak di wilayah Indonesia
- Laporan serangan hama terbaru dari Kementan, BPTP, atau portal pertanian Indonesia
- Hama musiman yang biasa muncul di {$season} dan sedang aktif saat ini
- Penyakit tanaman yang sedang dilaporkan meningkat di berbagai daerah

Kembalikan HANYA array JSON berisi TEPAT 3 hama/penyakit (tanpa teks lain di luar JSON), dalam format:
[
  {"nama": "Nama Hama atau Penyakit", "tanaman": "Padi", "kategori_hama": "Serangga"},
  {"nama": "Nama Hama atau Penyakit", "tanaman": "Cabai", "kategori_hama": "Jamur"},
  {"nama": "Nama Hama atau Penyakit", "tanaman": "Jagung", "kategori_hama": "Serangga"}
]

Panduan isi field:
- "nama": Nama spesifik hama/penyakit (bukan generik seperti "hama tanaman")
- "tanaman": Tanaman yang diserang (Padi | Cabai | Tomat | Jagung | Bawang | Teh | Kentang | Umum)
- "kategori_hama": Jenis organisme (Serangga | Jamur | Bakteri | Virus | Tungau | Mamalia | Nematoda)
- Pastikan ketiga item BERBEDA dan spesifik, berdasarkan kondisi terkini di Indonesia
PROMPT;

        try {
            $result = $this->callGeminiApiRaw($prompt, enableSearch: true, mimeType: 'application/json');

            if (empty($result)) {
                return [];
            }

            // Bersihkan markdown code fence jika ada
            $cleaned = preg_replace('/^```(?:json)?\s*/i', '', trim($result));
            $cleaned = preg_replace('/\s*```$/', '', $cleaned);

            $pests = json_decode($cleaned, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($pests)) {
                Log::warning('GeminiService: discoverTrendingPests failed to parse JSON.', [
                    'raw' => $result
                ]);
                return [];
            }

            // Validasi: pastikan setiap item punya field wajib
            $valid = array_values(array_filter($pests, function ($p) {
                return is_array($p)
                    && ! empty(trim($p['nama'] ?? ''))
                    && ! empty(trim($p['tanaman'] ?? ''))
                    && ! empty(trim($p['kategori_hama'] ?? ''));
            }));

            Log::info('GeminiService: discoverTrendingPests found pests.', ['count' => count($valid), 'pests' => $valid]);
            return $valid;

        } catch (\Throwable $e) {
            Log::error('GeminiService: discoverTrendingPests exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search the internet for a specific agricultural topic and return structured content.
     *
     * Menggunakan Google Search Grounding untuk menemukan informasi aktual dari internet —
     * bukan dari pengetahuan internal AI. Hasilnya distrukturkan siap simpan ke database.
     *
     * @param string $topic Topik spesifik (misalnya hasil dari discoverTrendingAgriTopics())
     * @return array|null Structured JSON data siap disimpan ke DB, atau null jika gagal.
     */
    public function searchAndGenerateAgriContent(string $topic): ?array
    {
        $today  = now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y');
        $season = $this->getCurrentIndonesiaSeason();

        $prompt = <<<PROMPT
Hari ini tanggal {$today}. Musim di Indonesia saat ini: {$season}.

SEKARANG CARI di internet informasi terbaru dan faktual mengenai topik berikut:
"{$topic}"

Aturan WAJIB:
- Semua informasi HARUS bersumber dari hasil pencarian internet, BUKAN dari pengetahuan training AI
- Utamakan sumber dari website pertanian Indonesia yang terpercaya (Kementan, BPTP, universitas pertanian, portal tani)
- Jika tersedia, sertakan data terkini (tahun 2025-2026)
- Jangan membuat-buat fakta, angka, atau klaim yang tidak ada sumbernya di internet

Setelah menemukan informasi dari internet, susunlah satu artikel lengkap atau data hama terstruktur:
1. Tulis dalam "Bahasa Tani" Indonesia — sopan, ramah, mudah dipahami petani lokal, sangat praktis
2. Tentukan tipe konten: hama/penyakit ("is_pest_info": true) atau tips/tutorial ("is_pest_info": false)
3. Format output HANYA berupa JSON valid ini (tanpa teks apapun di luar JSON):

{
  "is_pest_info": false,

  "title": "Judul artikel yang menarik, spesifik, dan relevan dengan kondisi terkini",
  "category": "Pilih satu: Tips Tani | Hama & Penyakit | Cuaca & Musim | Teknologi Tani | Pupuk & Tanah | Lainnya",
  "solusi_cepat": "Inti informasi atau solusi utama dalam 2-3 kalimat padat.",
  "alat_bahan": ["Item 1", "Item 2"],
  "content": "Isi artikel lengkap dalam Bahasa Tani dengan format markdown (gunakan ##, list -, emoji yang relevan). Minimal 4 paragraf / poin utama.",

  "pest_details": {
    "nama": "Nama hama atau penyakit (isi HANYA jika is_pest_info = true)",
    "kategori": "Tanaman yang diserang, misal: Padi | Cabai | Tomat | Umum",
    "deskripsi": "Penjelasan hama/penyakit dan dampaknya bagi petani.",
    "ciri_ciri": "Gejala serangan atau ciri fisik, tulis dalam poin-poin.",
    "cara_mengatasi": "Langkah pengendalian praktis, organik maupun kimiawi."
  }
}
PROMPT;

        return $this->callGeminiApi($prompt, enableSearch: true);
    }

    /**
     * Search the internet and generate structured hama/penyakit data for a specific pest.
     *
     * Output mengikuti schema tabel 'hama' yang sudah ada:
     *   - nama, kategori, deskripsi, ciri_ciri, cara_mengatasi
     *
     * Data 100% berbasis pencarian internet (Google Search Grounding), bukan hallucination.
     *
     * @param array $candidate Array dengan keys: nama, tanaman, kategori_hama
     * @return array|null Structured pest data or null on failure.
     */
    public function searchAndGenerateHamaData(array $candidate): ?array
    {
        $namaHama   = $candidate['nama']          ?? '';
        $tanaman    = $candidate['tanaman']        ?? 'Umum';
        $kategoriHama = $candidate['kategori_hama'] ?? 'Serangga';
        $today      = now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y');

        $prompt = <<<PROMPT
Hari ini tanggal {$today}.

CARI di internet informasi lengkap dan faktual mengenai hama/penyakit berikut:
- Nama: "{$namaHama}"
- Menyerang tanaman: {$tanaman}
- Jenis: {$kategoriHama}

Sumber yang diprioritaskan:
1. Website resmi Kementerian Pertanian RI (pertanian.go.id)
2. BPTP (Balai Pengkajian Teknologi Pertanian)
3. Universitas pertanian Indonesia (IPB, UGM, Unila, dll)
4. Portal pertanian Indonesia terpercaya (tabloidsinartani.com, cybex.pertanian.go.id, dll)

ATURAN WAJIB:
- Semua informasi HARUS berdasarkan fakta dari hasil pencarian internet
- Gunakan bahasa Indonesia yang mudah dipahami petani lokal ("Bahasa Tani")
- Ciri-ciri dan cara mengatasi harus SPESIFIK dan PRAKTIS, bukan generik
- Sertakan metode pengendalian organik DAN kimia jika tersedia
- JANGAN mengarang atau mengisi dengan informasi generik jika tidak ditemukan

Kembalikan HANYA JSON valid ini (tanpa teks apapun di luar JSON):
{
  "nama": "Nama resmi hama/penyakit sesuai yang ditemukan di internet",
  "kategori": "Kategori tanaman yang diserang — gunakan nama tanaman spesifik: Padi | Cabai | Tomat | Jagung | Bawang | Teh | Umum",
  "deskripsi": "Deskripsi 2-3 kalimat: apa itu hama/penyakit ini, penyebabnya, dan seberapa berbahaya bagi petani Indonesia. Gunakan bahasa yang ramah dan mudah dipahami.",
  "ciri_ciri": "Gejala serangan yang bisa dilihat petani langsung di lapangan. Tulis dalam format poin yang jelas:\n• Ciri 1\n• Ciri 2\n• Ciri 3\n(minimal 3 ciri-ciri spesifik berdasarkan data internet)",
  "cara_mengatasi": "Langkah pengendalian yang praktis dan sudah terbukti. Format:\n**Pengendalian Organik/Biologis:**\n• Metode 1\n• Metode 2\n**Pengendalian Kimia (jika diperlukan):**\n• Nama pestisida/fungisida yang direkomendasikan\n**Pencegahan:**\n• Tips pencegahan\n(Berikan rekomendasi spesifik berdasarkan standar Kementan/BPTP)"
}
PROMPT;

        $result = $this->callGeminiApi($prompt, enableSearch: true);

        // Validasi field wajib ada dan tidak kosong
        if (! $result) {
            return null;
        }

        $requiredFields = ['nama', 'kategori', 'deskripsi', 'ciri_ciri', 'cara_mengatasi'];
        foreach ($requiredFields as $field) {
            if (empty(trim($result[$field] ?? ''))) {
                Log::warning("GeminiService: searchAndGenerateHamaData — field '{$field}' kosong untuk {$namaHama}");
                return null;
            }
        }

        return $result;
    }

    /**
     * Determine the current agricultural season in Indonesia based on the calendar.
     * Indonesia has two main seasons: Musim Hujan (Oct-Mar) and Musim Kemarau (Apr-Sep).
     */
    private function getCurrentIndonesiaSeason(): string
    {
        $month = (int) now()->setTimezone('Asia/Jakarta')->format('n');
        return in_array($month, [10, 11, 12, 1, 2, 3]) ? 'Musim Hujan' : 'Musim Kemarau';
    }

    /**
     * Call the Gemini API and return a decoded JSON array.
     * Used for structured data endpoints (processAgriContent, searchAndGenerateAgriContent).
     *
     * @param bool $enableSearch  Aktifkan Google Search Grounding tool
     */
    private function callGeminiApi(string $prompt, bool $enableSearch = false): ?array
    {
        $raw = $this->callGeminiApiRaw($prompt, $enableSearch, 'application/json');

        if ($raw === null) {
            return null;
        }

        // Bersihkan response: hapus markdown code fence jika ada
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $cleaned = preg_replace('/\s*```$/', '', $cleaned);

        $jsonData = json_decode($cleaned, true);

        // Fallback jika ada teks pengantar di luar format JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            $firstBrace = strpos($cleaned, '{');
            $lastBrace = strrpos($cleaned, '}');
            if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
                $sub = substr($cleaned, $firstBrace, $lastBrace - $firstBrace + 1);
                // Bersihkan trailing commas: misal ", }" atau ", ]"
                $sub = preg_replace('/,\s*([\}\]])/', '$1', $sub);
                $jsonData = json_decode($sub, true);
            }
        }

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($jsonData)) {
            Log::error('GeminiService: Failed to decode JSON response from Gemini.', [
                'error'    => json_last_error_msg(),
                'raw_text' => $raw,
            ]);
            return null;
        }

        return $jsonData;
    }

    /**
     * Low-level Gemini API call with automatic retry & exponential backoff.
     *
     * Melakukan hingga 3 percobaan dengan jeda yang meningkat (5s, 10s) untuk
     * menangani rate limit, timeout, atau error sementara dari Gemini API.
     *
     * @param bool   $enableSearch  Aktifkan Google Search Grounding tool (butuh internet aktif)
     * @param string $mimeType      Response MIME type ('application/json' atau 'text/plain')
     * @return string|null Raw text response dari Gemini, atau null jika semua percobaan gagal.
     */
    private function callGeminiApiRaw(string $prompt, bool $enableSearch = false, string $mimeType = 'text/plain'): ?string
    {
        if (! $this->isConfigured()) {
            Log::error('GeminiService: GEMINI_API_KEY is not configured in .env');
            return null;
        }

        $maxAttempts = 3;
        $retryDelays = [0, 5, 10]; // Detik jeda sebelum percobaan ke-1, ke-2, ke-3

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => $mimeType,
                'temperature'      => 0.2,
            ],
        ];

        // Aktifkan Google Search Grounding agar AI mencari data real dari internet
        if ($enableSearch) {
            $body['tools'] = [
                ['googleSearch' => (object)[]]
            ];
        }

        $currentModel = $this->model;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            // Tunggu sebelum retry (tidak perlu tunggu di percobaan pertama)
            if ($retryDelays[$attempt - 1] > 0) {
                Log::info("GeminiService: Waiting {$retryDelays[$attempt - 1]}s before retry attempt {$attempt}...");
                sleep($retryDelays[$attempt - 1]);
            }

            // Jika percobaan sebelumnya terkena 503 (overloaded), coba model cadangan
            if ($attempt === 2 && $currentModel === 'gemini-flash-latest') {
                $currentModel = 'gemini-2.5-flash';
                Log::info("GeminiService: Switching to fallback model {$currentModel} on attempt 2...");
            } elseif ($attempt === 3 && $currentModel === 'gemini-2.5-flash') {
                $currentModel = 'gemini-flash-latest';
                Log::info("GeminiService: Switching to fallback model {$currentModel} on attempt 3...");
            }

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$currentModel}:generateContent?key={$this->apiKey}";

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->timeout(90)->post($url, $body);

                // HTTP 429 = Rate limit — selalu retry
                // HTTP 5xx = Server error — retry
                // HTTP 4xx lain (selain 429) = Client error — jangan retry
                if ($response->failed()) {
                    $status = $response->status();
                    $isRetryable = $status === 429 || $status >= 500;

                    Log::warning("GeminiService: API call failed (attempt {$attempt}/{$maxAttempts}) with model {$currentModel}.", [
                        'status'       => $status,
                        'retryable'    => $isRetryable,
                        'enableSearch' => $enableSearch,
                    ]);

                    if (! $isRetryable || $attempt === $maxAttempts) {
                        Log::error('GeminiService: API call permanently failed.', [
                            'status' => $status,
                            'body'   => $response->body(),
                        ]);
                        return null;
                    }

                    continue; // Coba lagi
                }

                $result       = $response->json();
                $textResponse = data_get($result, 'candidates.0.content.parts.0.text');

                if (empty($textResponse)) {
                    // Response kosong kadang terjadi karena safety filter Gemini — tidak perlu retry
                    Log::error('GeminiService: Empty text response from Gemini API.', [
                        'result'       => $result,
                        'enableSearch' => $enableSearch,
                    ]);
                    return null;
                }

                if ($attempt > 1) {
                    Log::info("GeminiService: Succeeded on attempt {$attempt}.");
                }

                return $textResponse;

            } catch (\Throwable $e) {
                Log::warning("GeminiService: Exception on attempt {$attempt}/{$maxAttempts}: " . $e->getMessage());

                if ($attempt === $maxAttempts) {
                    Log::error('GeminiService: All retry attempts exhausted.', [
                        'trace'        => $e->getTraceAsString(),
                        'enableSearch' => $enableSearch,
                    ]);
                    return null;
                }
            }
        }

        return null;
    }
}
