<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\AgriScraperService;
use App\Services\GeminiService;

class ScrapeAgriData extends Command
{
    protected $signature = 'agri:scrape-sync
                            {url? : URL khusus artikel pertanian yang ingin di-scrape}
                            {--force : Paksa jalankan meskipun belum 2 hari}';

    protected $description = 'Ambil 1 artikel pertanian per 2 hari dari 9 website utama. Jika tidak ada yang baru (5 tahun terakhir), Gemini generate artikel sendiri via Google Search.';

    private const LAST_RUN_KEY          = 'agri:last_run_date';
    private const SCRAPED_URL_PREFIX    = 'agri:scraped_url:';
    private const URL_CACHE_DAYS        = 90;
    private const RUN_INTERVAL_DAYS     = 2;
    private const MAX_ARTICLE_AGE_YEARS = 5;

    // ── 9 Sumber Utama ──
    private function getPrimarySources(): array
    {
        return [
            'hortiindonesia' => [
                'label'   => 'Horti Indonesia',
                'url'     => 'https://hortiindonesia.com',
                'pattern' => '/^https:\/\/hortiindonesia\.com\/berita\/[^\/]+\/?$/',
                'exclude' => ['/berita/kategori/', '/berita/penulis/', '/berita/tag/'],
            ],
            'msmb' => [
                'label'   => 'MSMB Indonesia',
                'url'     => 'https://msmbindonesia.com/blog/',
                'pattern' => '/^https:\/\/msmbindonesia\.com\/[^\/]+\/?$/',
                'exclude' => [
                    '/blog/', '/category/', '/tag/', '/author/', '/page/',
                    '/tentang-kami/', '/kontak/', '/about', '/amerta/', '/bathara/',
                    '/jinawi/', '/wisanggeni/', '/ritx-', '/wp-content/', '/wp-json/',
                    '/feed/', '/comments/', 'linkedin.com',
                ],
            ],
            'digitani' => [
                'label'   => 'Digitani IPB',
                'url'     => 'https://digitani.ipb.ac.id',
                'pattern' => '/^https:\/\/digitani\.ipb\.ac\.id\/[^\/]{10,}\/?$/',
                'exclude' => [
                    '/artikel/', '/berita/', '/category/', '/tag/', '/author/', '/page/',
                    '/konsultasi/', '/profil', '/tentang', '/kontak/', '/feed/',
                    '/comments/', '/wp-content/', '/wp-json/', 'app-digitani', 'apps.apple',
                    '/privacy', '/syarat', '/kebijakan', '/download', '/galeri',
                ],
            ],
            'pertanian_go' => [
                'label'   => 'Kementan RI',
                'url'     => 'https://pertanian.go.id/home/?show=news',
                'pattern' => '/^https:\/\/pertanian\.go\.id\/home\/\?show=news&act=view&id=\d+$/',
                'exclude' => [],
            ],
            'cybex' => [
                'label'   => 'Cybex Kementan',
                'url'     => 'https://cybex.pertanian.go.id/mobile/artikel',
                'pattern' => '/^https:\/\/cybex\.pertanian\.go\.id\/mobile\/artikel\/\d+\/[^\/]+$/',
                'exclude' => ['/kategori/', '/pencarian/'],
            ],
            'litbang' => [
                'label'   => 'Litbang Pertanian',
                'url'     => 'https://litbang.pertanian.go.id/berita',
                'pattern' => '/^https:\/\/litbang\.pertanian\.go\.id\/berita\/[^\/]+\/?$/',
                'exclude' => ['/kategori/', '/tag/', '/penulis/'],
            ],
            'sinartani' => [
                'label'   => 'Tabloid Sinar Tani',
                'url'     => 'https://tabloidsinartani.com',
                'pattern' => '/^https:\/\/tabloidsinartani\.com\/detail\/[^\/]+\/[^\/]+$/',
                'exclude' => ['/tag/', '/kategori/', '/penulis/', '/search/'],
            ],
            'agropustaka' => [
                'label'   => 'Agropustaka',
                'url'     => 'https://agropustaka.id/berita',
                'pattern' => '/^https:\/\/agropustaka\.id\/[^\/]+\/[^\/]+\/?$/',
                'exclude' => ['/kategori/', '/tag/', '/author/', '/page/', '/pencarian/'],
            ],
            'petanidigital' => [
                'label'   => 'Petani Digital',
                'url'     => 'https://petanidigital.id/blog',
                'pattern' => '/^https:\/\/petanidigital\.id\/[^\/]+\/[^\/]+\/?$/',
                'exclude' => ['/kategori/', '/tag/', '/author/', '/page/', '/kontak/'],
            ],
        ];
    }

    // ─────────────────────────────────────────────
    //  Entry Point
    // ─────────────────────────────────────────────

    public function handle(AgriScraperService $scraper, GeminiService $gemini): int
    {
        $targetUrl = $this->argument('url');
        $force     = $this->option('force');

        // ── Mode: URL Spesifik (manual) ──
        if ($targetUrl) {
            $this->info("🔗 Mode URL Tunggal: {$targetUrl}");
            $this->performScrape($scraper, $targetUrl);
            return Command::SUCCESS;
        }

        // ── INTERVAL GATE — Hanya jalan setiap 2 hari ──
        $lastRun = Cache::get(self::LAST_RUN_KEY);
        $today   = now()->setTimezone('Asia/Jakarta')->startOfDay();

        if (! $force && $lastRun !== null) {
            $lastRunDate   = \Carbon\Carbon::parse($lastRun)->setTimezone('Asia/Jakarta')->startOfDay();
            $daysSinceLast = $lastRunDate->diffInDays($today);

            if ($daysSinceLast < self::RUN_INTERVAL_DAYS) {
                $nextRun = $lastRunDate->copy()->addDays(self::RUN_INTERVAL_DAYS)->format('d M Y');
                $this->warn("⏸️  Pipeline sudah berjalan " . $lastRunDate->format('d M Y') . " ({$daysSinceLast} hari lalu).");
                $this->line("   Jadwal berikutnya: {$nextRun}.");
                $this->line("   Gunakan --force untuk paksa jalan sekarang.");
                return Command::SUCCESS;
            }
        }

        // ── Mulai Pipeline ──
        $this->printBanner();
        Cache::put(self::LAST_RUN_KEY, now()->toIso8601String(), now()->addDays(60));

        // ── Step 1 & 2: Crawl 9 sumber, proses langsung begitu ketemu artikel valid ──
        $this->info('📡 Tahap 1/2 — Crawling 9 website pertanian utama...');
        $this->newLine();

        $scraped     = false;
        $sourceStats = [];
        $cutoffDate  = now()->subYears(self::MAX_ARTICLE_AGE_YEARS);

        foreach ($this->getPrimarySources() as $cfg) {
            $label = $cfg['label'];
            $this->line("   📡 Crawling [{$label}]...");

            $allLinks    = $this->crawlLinksFromPage($cfg['url'], $cfg['pattern'], $cfg['exclude']);
            $totalFound  = count($allLinks);
            $newCount    = 0;
            $cachedCount = 0;

            foreach ($allLinks as $link) {
                if ($this->isUrlAlreadyScraped($link)) {
                    $cachedCount++;
                    continue;
                }

                if ($this->isArticleUrlTooOld($link, $cutoffDate)) {
                    continue;
                }

                $newCount++;

                // Langsung coba scrape — stop kalau berhasil 1
                $this->line("   🔄 Mencoba: {$link}");
                $result = $scraper->scrapeAndSyncUrl($link);
                $this->markUrlAsScraped($link);

                if ($result['success']) {
                    $type   = $result['data']['type'] ?? 'unknown';
                    $record = $result['data']['record'] ?? [];
                    $this->info('   ✅ BERHASIL disimpan ke database.');
                    $this->line('   Tipe : ' . strtoupper($type));
                    $this->line('   Judul: ' . ($type === 'hama' ? ($record['nama'] ?? '-') : ($record['title'] ?? '-')));
                    $scraped = true;

                    $sourceStats[] = ['label' => $label, 'found' => $totalFound, 'new' => $newCount, 'cached' => $cachedCount];
                    break 2; // Keluar dari dua loop — sudah dapat 1 artikel
                }

                $this->warn("   ⚠️  Gagal: {$result['message']} — lanjut...");
                Log::warning('ScrapeAgriData: Skip ' . $link . ' — ' . $result['message']);
            }

            $sourceStats[] = ['label' => $label, 'found' => $totalFound, 'new' => $newCount, 'cached' => $cachedCount];
        }

        $this->printSourceStats($sourceStats);
        $this->newLine();

        // ── Step 3: Fallback — Gemini generate artikel sendiri ──
        if (! $scraped) {
            $this->warn('📭 Tidak ada artikel valid dari 9 sumber utama.');
            $this->info('🤖 Tahap 2/2 — Gemini mencari & membuat artikel sendiri via Google Search...');
            $this->newLine();

            $scraped = $this->generateViaGemini($gemini, $scraper);

            if (! $scraped) {
                $this->error('❌ Gemini juga gagal menghasilkan artikel.');
                $this->line('   Pipeline akan coba lagi 2 hari kemudian.');
                Log::warning('ScrapeAgriData: Semua sumber dan Gemini fallback gagal.');
            }
        }

        $this->newLine();
        $this->printFooter();

        return Command::SUCCESS;
    }

    // ─────────────────────────────────────────────
    //  Cek apakah URL artikel terlalu lama
    // ─────────────────────────────────────────────

    private function isArticleUrlTooOld(string $url, \Carbon\Carbon $cutoffDate): bool
    {
        if (preg_match('/\/(20\d{2})[\/\-]/', $url, $matches)) {
            $yearInUrl  = (int) $matches[1];
            $cutoffYear = (int) $cutoffDate->format('Y');
            return $yearInUrl < $cutoffYear;
        }

        return false; // Tidak ada info tahun di URL → anggap valid
    }

    // ─────────────────────────────────────────────
    //  Fallback: Gemini generate artikel sendiri
    // ─────────────────────────────────────────────

    private function generateViaGemini(GeminiService $gemini, AgriScraperService $scraper): bool
    {
        $this->line('   🔍 Gemini mencari topik pertanian trending Indonesia...');

        // Coba generate tips/artikel dulu
        $topics = $gemini->discoverTrendingAgriTopics();

        if (! empty($topics)) {
            foreach ($topics as $topic) {
                $this->line("   📝 Topik: \"{$topic}\"");
                $this->line('   ⚙️  Generating konten...');

                $data = $gemini->searchAndGenerateAgriContent($topic);

                if ($data) {
                    $sourceUrl = 'ai-search://' . md5($topic);
                    $result    = $scraper->saveToDatabase($data, null, $sourceUrl);
                    $type      = $result['type'] ?? 'unknown';
                    $record    = $result['record'] ?? [];

                    $this->info('   ✅ BERHASIL — Artikel AI disimpan ke database.');
                    $this->line('   Tipe : ' . strtoupper($type));
                    $this->line('   Judul: ' . ($type === 'hama' ? ($record['nama'] ?? '-') : ($record['title'] ?? '-')));
                    Log::info('ScrapeAgriData: Gemini fallback berhasil.', ['topic' => $topic]);
                    return true;
                }

                $this->warn('   ⚠️  Gagal generate topik ini, coba berikutnya...');
            }
        }

        // Kalau artikel gagal → coba hama trending
        $this->line('   🐛 Mencoba generate data hama trending...');
        $pests = $gemini->discoverTrendingPests();

        if (! empty($pests)) {
            foreach ($pests as $pest) {
                $this->line("   🐛 Hama: \"{$pest['nama']}\" pada {$pest['tanaman']}");

                $hamaData = $gemini->searchAndGenerateHamaData($pest);

                if ($hamaData) {
                    $wrappedData = ['is_pest_info' => true, 'pest_details' => $hamaData];
                    $sourceUrl   = 'ai-search://hama-' . md5($pest['nama']);
                    $result      = $scraper->saveToDatabase($wrappedData, null, $sourceUrl);
                    $record      = $result['record'] ?? [];

                    $this->info('   ✅ BERHASIL — Data hama AI disimpan ke database.');
                    $this->line('   Nama: ' . ($record['nama'] ?? '-'));
                    Log::info('ScrapeAgriData: Gemini hama fallback berhasil.', ['pest' => $pest['nama']]);
                    return true;
                }

                $this->warn('   ⚠️  Gagal generate hama ini, coba berikutnya...');
            }
        }

        return false;
    }

    // ─────────────────────────────────────────────
    //  Web Crawling
    // ─────────────────────────────────────────────

    private function crawlLinksFromPage(string $pageUrl, string $pattern, array $excludeList): array
    {
        try {
            $response = Http::withOptions([
                'version' => 1.1,
                'verify'  => false,
            ])->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->timeout(20)->get($pageUrl);

            if ($response->failed()) {
                $this->warn("   ⚠️  HTTP {$response->status()} dari {$pageUrl}");
                return [];
            }

            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTML('<?xml encoding="UTF-8">' . $response->body());
            libxml_clear_errors();

            $links      = [];
            $parsedBase = parse_url($pageUrl);
            $host       = $parsedBase['scheme'] . '://' . $parsedBase['host'];

            foreach ($dom->getElementsByTagName('a') as $a) {
                $href = trim($a->getAttribute('href'));

                if (empty($href)
                    || str_starts_with($href, '#')
                    || str_starts_with($href, 'javascript:')
                    || str_starts_with($href, 'mailto:')
                    || str_starts_with($href, 'tel:')
                ) {
                    continue;
                }

                if (str_starts_with($href, '//')) {
                    $href = $parsedBase['scheme'] . ':' . $href;
                } elseif (str_starts_with($href, '/')) {
                    $href = $host . $href;
                } elseif (! str_starts_with($href, 'http')) {
                    $href = rtrim($pageUrl, '/') . '/' . ltrim($href, '/');
                }

                $href = strtok($href, '?');
                $href = strtok($href ?: '', '#');

                if (empty($href) || ! filter_var($href, FILTER_VALIDATE_URL)) {
                    continue;
                }

                $excluded = false;
                foreach ($excludeList as $ex) {
                    if (str_contains($href, $ex)) {
                        $excluded = true;
                        break;
                    }
                }
                if ($excluded) continue;

                if (preg_match($pattern, $href) && ! in_array($href, $links, true)) {
                    $links[] = $href;
                }
            }

            return $links;

        } catch (\Throwable $e) {
            $this->warn("   ⚠️  Exception crawling {$pageUrl}: " . $e->getMessage());
            Log::warning("ScrapeAgriData: crawlLinksFromPage [{$pageUrl}] — " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────────
    //  URL Dedup Cache
    // ─────────────────────────────────────────────

    private function isUrlAlreadyScraped(string $url): bool
    {
        return Cache::has(self::SCRAPED_URL_PREFIX . md5($url));
    }

    private function markUrlAsScraped(string $url): void
    {
        Cache::put(
            self::SCRAPED_URL_PREFIX . md5($url),
            true,
            now()->addDays(self::URL_CACHE_DAYS)
        );
    }

    // ─────────────────────────────────────────────
    //  Manual URL Scrape
    // ─────────────────────────────────────────────

    private function performScrape(AgriScraperService $scraper, string $url): void
    {
        $result = $scraper->scrapeAndSyncUrl($url);

        if ($result['success']) {
            $type   = $result['data']['type'] ?? 'unknown';
            $record = $result['data']['record'] ?? [];
            $this->info('✅ BERHASIL. Tipe: ' . strtoupper($type));
            $this->line('   ➔ ' . ($type === 'hama' ? ($record['nama'] ?? '-') : ($record['title'] ?? '-')));
            $this->markUrlAsScraped($url);
        } else {
            $this->error('❌ GAGAL: ' . $result['message']);
        }
    }

    // ─────────────────────────────────────────────
    //  Display Helpers
    // ─────────────────────────────────────────────

    private function printBanner(): void
    {
        $now = now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB';
        $this->info('════════════════════════════════════════════════════════');
        $this->info(' 🌾 Petani Maju — Pipeline Artikel Harian');
        $this->info(" 🕐 {$now}");
        $this->info('════════════════════════════════════════════════════════');
        $this->newLine();
    }

    private function printSourceStats(array $stats): void
    {
        if (empty($stats)) return;
        $this->line('┌─────────────────────────────┬────────┬──────┬─────────┐');
        $this->line('│ Sumber                      │  Total │  Baru│  Cache  │');
        $this->line('├─────────────────────────────┼────────┼──────┼─────────┤');
        foreach ($stats as $s) {
            $label  = str_pad($s['label'], 27);
            $total  = str_pad($s['found'], 6, ' ', STR_PAD_LEFT);
            $new    = str_pad($s['new'], 6, ' ', STR_PAD_LEFT);
            $cached = str_pad($s['cached'], 7, ' ', STR_PAD_LEFT);
            $this->line("│ {$label} │ {$total} │{$new} │{$cached} │");
        }
        $this->line('└─────────────────────────────┴────────┴──────┴─────────┘');
    }

    private function printFooter(): void
    {
        $this->info('════════════════════════════════════════════════════════');
        $this->info(' 🎉 Pipeline selesai. Artikel pertanian hari ini sudah diperbarui.');
        $this->info('════════════════════════════════════════════════════════');
    }
}