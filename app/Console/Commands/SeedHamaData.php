<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SupabaseService;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SeedHamaData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'agri:seed-hama
                            {--count=5 : Jumlah data hama/penyakit baru yang akan di-generate}
                            {--kategori= : Fokus pada kategori tanaman tertentu (misal: Padi, Cabai, Tomat)}
                            {--auto-discover : Gunakan AI untuk mencari hama trending dari internet (mode otomatis harian)}
                            {--dry-run : Tampilkan data yang akan dibuat tanpa menyimpan ke database}';

    /**
     * The console command description.
     */
    protected $description = 'Generate dan seed data hama & penyakit tanaman secara otomatis menggunakan AI Google Search. Data mengikuti template yang sudah ada.';

    /**
     * Daftar hama/penyakit pertanian Indonesia yang penting.
     * Dikelompokkan berdasarkan kategori tanaman.
     * Digunakan sebagai panduan agar AI tidak mengulang data yang sudah ada.
     */
    private const PRIORITY_HAMA_LIST = [
        // Padi
        ['nama' => 'Wereng Coklat',           'tanaman' => 'Padi',    'kategori_hama' => 'Serangga'],
        ['nama' => 'Hama Putih Palsu',         'tanaman' => 'Padi',    'kategori_hama' => 'Serangga'],
        ['nama' => 'Lalat Bibit',              'tanaman' => 'Padi',    'kategori_hama' => 'Serangga'],
        ['nama' => 'Penyakit Blast',           'tanaman' => 'Padi',    'kategori_hama' => 'Jamur'],
        ['nama' => 'Penyakit Kresek (BLB)',    'tanaman' => 'Padi',    'kategori_hama' => 'Bakteri'],
        // Cabai
        ['nama' => 'Thrips',                   'tanaman' => 'Cabai',   'kategori_hama' => 'Serangga'],
        ['nama' => 'Kutu Kebul',               'tanaman' => 'Cabai',   'kategori_hama' => 'Serangga'],
        ['nama' => 'Antraknosa Cabai',          'tanaman' => 'Cabai',   'kategori_hama' => 'Jamur'],
        ['nama' => 'Layu Fusarium',            'tanaman' => 'Cabai',   'kategori_hama' => 'Jamur'],
        ['nama' => 'Virus Kuning Cabai',       'tanaman' => 'Cabai',   'kategori_hama' => 'Virus'],
        // Tomat
        ['nama' => 'Ulat Buah Tomat',          'tanaman' => 'Tomat',   'kategori_hama' => 'Serangga'],
        ['nama' => 'Busuk Buah Tomat',         'tanaman' => 'Tomat',   'kategori_hama' => 'Jamur'],
        ['nama' => 'Penyakit Layu Tomat',      'tanaman' => 'Tomat',   'kategori_hama' => 'Bakteri'],
        // Bawang
        ['nama' => 'Ulat Bawang',              'tanaman' => 'Bawang',  'kategori_hama' => 'Serangga'],
        ['nama' => 'Penyakit Moler',           'tanaman' => 'Bawang',  'kategori_hama' => 'Jamur'],
        ['nama' => 'Bercak Ungu Bawang',       'tanaman' => 'Bawang',  'kategori_hama' => 'Jamur'],
        // Jagung
        ['nama' => 'Fall Armyworm (FAW)',      'tanaman' => 'Jagung',  'kategori_hama' => 'Serangga'],
        ['nama' => 'Penyakit Bulai Jagung',    'tanaman' => 'Jagung',  'kategori_hama' => 'Jamur'],
        ['nama' => 'Penggerek Batang Jagung',  'tanaman' => 'Jagung',  'kategori_hama' => 'Serangga'],
        // Teh
        ['nama' => 'Empoasca (Helopeltis)',    'tanaman' => 'Teh',     'kategori_hama' => 'Serangga'],
        ['nama' => 'Penyakit Cacar Daun Teh',  'tanaman' => 'Teh',     'kategori_hama' => 'Jamur'],
        // Umum / Multi-tanaman
        ['nama' => 'Ulat Grayak',              'tanaman' => 'Umum',    'kategori_hama' => 'Serangga'],
        ['nama' => 'Trips (Thrips tabaci)',     'tanaman' => 'Umum',    'kategori_hama' => 'Serangga'],
        ['nama' => 'Penyakit Layu Bakteri',    'tanaman' => 'Umum',    'kategori_hama' => 'Bakteri'],
        ['nama' => 'Tungau Merah (Spider Mite)','tanaman' => 'Umum',   'kategori_hama' => 'Tungau'],
    ];

    public function handle(SupabaseService $supabase, GeminiService $gemini): int
    {
        $count       = (int) $this->option('count');
        $kategori    = $this->option('kategori');
        $dryRun      = $this->option('dry-run');
        $autoDiscover = $this->option('auto-discover');

        $this->printBanner($dryRun, $autoDiscover);

        // ── Mode: Auto-Discover (dijalankan otomatis harian oleh scheduler) ──
        if ($autoDiscover) {
            return $this->runAutoDiscover($supabase, $gemini, $dryRun);
        }

        // ── Step 1: Ambil nama hama yang sudah ada di database ──
        $this->line('📥 Memeriksa data hama yang sudah ada di database...');
        $existing = $supabase->select('hama', [
            'select' => 'id,nama,kategori',
            'order'  => 'nama.asc',
        ]);

        $existingNames = array_map(
            fn ($h) => strtolower(trim($h['nama'] ?? '')),
            $existing
        );

        $this->info('   ✅ Ditemukan ' . count($existing) . ' data hama yang sudah ada.');
        $this->newLine();

        // ── Step 2: Pilih hama dari priority list yang belum ada ──
        $candidates = $this->selectCandidates($existingNames, $kategori, $count);

        if (empty($candidates)) {
            $this->info('✨ Semua data hama prioritas sudah ada di database. Tidak perlu tambahan.');
            $this->line('   Gunakan --count lebih besar atau --kategori yang berbeda jika ingin menambah lebih banyak.');
            return Command::SUCCESS;
        }

        $this->info('📋 Hama yang akan di-generate (' . count($candidates) . ' item):');
        foreach ($candidates as $i => $c) {
            $this->line('   ' . ($i + 1) . '. ' . $c['nama'] . ' [' . $c['tanaman'] . ']');
        }
        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 [DRY RUN] Tidak ada yang disimpan. Hapus flag --dry-run untuk eksekusi.');
            return Command::SUCCESS;
        }

        // ── Step 3: Generate & simpan setiap data hama ──
        $this->info('🤖 Memulai generasi data hama dengan AI Google Search...');
        $this->info('   Setiap data dicari dari internet — bukan dibuat-buat.');
        $this->newLine();

        $successCount = 0;
        $failCount    = 0;

        foreach ($candidates as $candidate) {
            $this->line("🔍 [{$candidate['nama']} — {$candidate['tanaman']}]");

            // Generate data via AI dengan Google Search Grounding
            $data = $gemini->searchAndGenerateHamaData($candidate);

            if (! $data) {
                $failCount++;
                $this->warn("   ❌ AI gagal generate data untuk: {$candidate['nama']}");
                $this->newLine();
                sleep(2);
                continue;
            }

            // Cari gambar yang representatif
            $imageUrl = $this->findHamaImage($candidate['nama'], $candidate['tanaman']);

            // Siapkan payload sesuai schema tabel hama
            $payload = [
                'nama'           => $data['nama']           ?? $candidate['nama'],
                'kategori'       => $data['kategori']       ?? $candidate['tanaman'],
                'deskripsi'      => $data['deskripsi']      ?? '',
                'ciri_ciri'      => $data['ciri_ciri']      ?? '',
                'cara_mengatasi' => $data['cara_mengatasi'] ?? '',
                'gambar_url'     => $imageUrl,
            ];

            // Validasi field wajib tidak kosong
            if (empty($payload['deskripsi']) || empty($payload['ciri_ciri']) || empty($payload['cara_mengatasi'])) {
                $failCount++;
                $this->warn("   ⚠️  Data tidak lengkap, dilewati: {$candidate['nama']}");
                $this->newLine();
                continue;
            }

            // Cek duplikat sebelum insert
            $existing = $supabase->select('hama', [
                'select' => 'id',
                'nama'   => 'ilike.*' . $payload['nama'] . '*',
                'limit'  => 1,
            ]);

            if (! empty($existing)) {
                $this->warn("   ⏭️  Sudah ada data serupa untuk: {$payload['nama']} (ID: {$existing[0]['id']})");
                $this->newLine();
                continue;
            }

            try {
                $record = $supabase->insert('hama', $payload);
                $successCount++;

                $this->info("   ✅ [BERHASIL] Tersimpan ke database");
                $this->line("   ➔ Nama     : {$payload['nama']}");
                $this->line("   ➔ Kategori : {$payload['kategori']}");
                $this->line("   ➔ Gambar   : " . (empty($imageUrl) ? '(default)' : 'OK'));

                Log::info("SeedHamaData: Inserted hama — {$payload['nama']}", [
                    'kategori' => $payload['kategori']
                ]);
            } catch (\Throwable $e) {
                $failCount++;
                $this->error("   ❌ Gagal menyimpan: " . $e->getMessage());
                Log::error("SeedHamaData: Failed to insert {$candidate['nama']}: " . $e->getMessage());
            }

            $this->newLine();
            sleep(3); // Jeda antar request Gemini API untuk menghindari rate limit
        }

        // ── Summary ──
        $this->line('════════════════════════════════════════════════════════');
        $this->info("📊 Selesai! {$successCount} data hama berhasil ditambahkan, {$failCount} gagal.");
        $this->line('════════════════════════════════════════════════════════');

        return $successCount > 0 ? Command::SUCCESS : Command::FAILURE;
    }

    // ─────────────────────────────────────────────
    //  Auto-Discover Mode (Daily Scheduler)
    // ─────────────────────────────────────────────

    /**
     * Mode otomatis: AI mencari hama trending dari internet, lalu generate & simpan ke DB.
     *
     * Flow:
     *   1. Gemini (Google Search Grounding) mencari 3 hama yang sedang mewabah hari ini
     *   2. Filter hama yang sudah ada di database (skip duplikat)
     *   3. Generate detail lengkap dari internet untuk hama yang belum ada
     *   4. Simpan ke tabel 'hama'
     */
    private function runAutoDiscover(SupabaseService $supabase, GeminiService $gemini, bool $dryRun): int
    {
        $this->info('══════════════════════════════════════════════════════');
        $this->info(' 🔍 Mode Auto-Discover: Mencari Hama Trending Hari Ini');
        $this->info('══════════════════════════════════════════════════════');
        $this->newLine();

        // Step 1: Minta AI cari hama yang sedang mewabah
        $this->line('🌐 Step 1/3: AI mencari hama/penyakit yang sedang mewabah di Indonesia...');
        $trendingPests = $gemini->discoverTrendingPests();

        if (empty($trendingPests)) {
            $this->error('❌ AI gagal menemukan hama trending. Cek koneksi internet dan GEMINI_API_KEY.');
            return Command::FAILURE;
        }

        $this->info('   ✅ AI menemukan ' . count($trendingPests) . ' hama/penyakit trending:');
        foreach ($trendingPests as $i => $pest) {
            $this->line('   ' . ($i + 1) . ". {$pest['nama']} [{$pest['tanaman']}]");
        }
        $this->newLine();

        // Step 2: Filter hama yang sudah ada di database
        $this->line('📥 Step 2/3: Memeriksa duplikat di database...');
        $existing = $supabase->select('hama', [
            'select' => 'id,nama',
            'order'  => 'nama.asc',
        ]);
        $existingNames = array_map(
            fn ($h) => strtolower(trim($h['nama'] ?? '')),
            $existing
        );

        $newCandidates = [];
        $skippedCount  = 0;
        foreach ($trendingPests as $pest) {
            $namaLower = strtolower(trim($pest['nama']));
            $isDuplicate = false;
            foreach ($existingNames as $ex) {
                if (
                    str_contains($ex, $namaLower) ||
                    str_contains($namaLower, $ex) ||
                    (similar_text($ex, $namaLower, $pct) && $pct > 75)
                ) {
                    $isDuplicate = true;
                    break;
                }
            }

            if ($isDuplicate) {
                $this->line("   ⏭️  [{$pest['nama']}] sudah ada di database, dilewati.");
                $skippedCount++;
            } else {
                $newCandidates[] = $pest;
            }
        }

        if (empty($newCandidates)) {
            $this->info('✨ Semua hama trending sudah ada di database. Tidak ada yang perlu ditambahkan.');
            return Command::SUCCESS;
        }

        $this->info("   ✅ {$skippedCount} dilewati (sudah ada), " . count($newCandidates) . ' akan diproses.');
        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 [DRY RUN] Data yang akan ditambahkan:');
            foreach ($newCandidates as $c) {
                $this->line("   • {$c['nama']} [{$c['tanaman']}] — {$c['kategori_hama']}");
            }
            $this->warn('   Hapus flag --dry-run untuk menyimpan ke database.');
            return Command::SUCCESS;
        }

        // Step 3: Generate detail & simpan
        $this->line('📝 Step 3/3: AI menyusun detail hama dari internet dan menyimpan ke database...');
        $this->newLine();

        $successCount = 0;
        $failCount    = 0;

        foreach ($newCandidates as $candidate) {
            $this->line("🔍 [{$candidate['nama']} — {$candidate['tanaman']}]");

            $data = $gemini->searchAndGenerateHamaData($candidate);

            if (! $data) {
                $failCount++;
                $this->warn("   ❌ AI gagal generate detail untuk: {$candidate['nama']}");
                $this->newLine();
                sleep(2);
                continue;
            }

            $imageUrl = $this->findHamaImage($candidate['nama'], $candidate['tanaman']);

            $payload = [
                'nama'           => $data['nama']           ?? $candidate['nama'],
                'kategori'       => $data['kategori']       ?? $candidate['tanaman'],
                'deskripsi'      => $data['deskripsi']      ?? '',
                'ciri_ciri'      => $data['ciri_ciri']      ?? '',
                'cara_mengatasi' => $data['cara_mengatasi'] ?? '',
                'gambar_url'     => $imageUrl,
            ];

            if (empty($payload['deskripsi']) || empty($payload['ciri_ciri']) || empty($payload['cara_mengatasi'])) {
                $failCount++;
                $this->warn("   ⚠️  Data tidak lengkap dari AI, dilewati: {$candidate['nama']}");
                $this->newLine();
                continue;
            }

            try {
                $supabase->insert('hama', $payload);
                $successCount++;
                $this->info("   ✅ [TERSIMPAN] {$payload['nama']} [{$payload['kategori']}]");
                Log::info("SeedHamaData: Auto-discover inserted hama — {$payload['nama']}");
            } catch (\Throwable $e) {
                $failCount++;
                $this->error('   ❌ Gagal menyimpan: ' . $e->getMessage());
                Log::error("SeedHamaData: Auto-discover failed to insert {$candidate['nama']}: " . $e->getMessage());
            }

            $this->newLine();
            sleep(3);
        }

        $this->line('════════════════════════════════════════════════════════');
        $this->info("📊 Auto-Discover selesai. {$successCount} hama baru ditambahkan, {$failCount} gagal.");
        $this->line('════════════════════════════════════════════════════════');

        return $successCount > 0 ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Pilih kandidat hama dari priority list yang belum ada di database.
     * Filter berdasarkan kategori tanaman jika ditentukan.
     */
    private function selectCandidates(array $existingNames, ?string $kategori, int $count): array
    {
        $candidates = [];

        foreach (self::PRIORITY_HAMA_LIST as $item) {
            // Filter kategori jika ditentukan
            if ($kategori && ! str_contains(strtolower($item['tanaman']), strtolower($kategori))) {
                continue;
            }

            // Skip jika sudah ada di database
            $namaLower = strtolower(trim($item['nama']));
            $alreadyExists = false;
            foreach ($existingNames as $existing) {
                // Cek kesamaan dengan threshold fuzzy (nama mengandung existing atau sebaliknya)
                if (
                    str_contains($existing, $namaLower) ||
                    str_contains($namaLower, $existing) ||
                    similar_text($existing, $namaLower, $pct) && $pct > 80
                ) {
                    $alreadyExists = true;
                    break;
                }
            }

            if (! $alreadyExists) {
                $candidates[] = $item;
            }

            if (count($candidates) >= $count) {
                break;
            }
        }

        return $candidates;
    }

    /**
     * Cari gambar representatif untuk hama dari Unsplash berdasarkan nama & tanaman.
     * Gunakan fallback image pertanian jika tidak ditemukan.
     */
    private function findHamaImage(string $namaHama, string $tanaman): string
    {
        // Mapping gambar default berdasarkan jenis hama/penyakit
        $imageMap = [
            'serangga'  => 'https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?w=800',
            'jamur'     => 'https://images.unsplash.com/photo-1590599145008-e4ec48e9283a?w=800',
            'bakteri'   => 'https://images.unsplash.com/photo-1576086213369-97a306d36557?w=800',
            'virus'     => 'https://images.unsplash.com/photo-1628863353691-0071c8c1874c?w=800',
            'tungau'    => 'https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?w=800',
            'mamalia'   => 'https://images.unsplash.com/photo-1605019543152-67b2de4ce3a3?w=800',
            'default'   => 'https://images.unsplash.com/photo-1599599810769-bcde5a160d32?w=800',
        ];

        $namaLower = strtolower($namaHama);

        if (str_contains($namaLower, 'penyakit') || str_contains($namaLower, 'blast') ||
            str_contains($namaLower, 'antraknosa') || str_contains($namaLower, 'busuk') ||
            str_contains($namaLower, 'layu') || str_contains($namaLower, 'bercak') ||
            str_contains($namaLower, 'bulai') || str_contains($namaLower, 'cacar')) {
            return $imageMap['jamur'];
        }

        if (str_contains($namaLower, 'bakteri') || str_contains($namaLower, 'kresek') ||
            str_contains($namaLower, 'blb')) {
            return $imageMap['bakteri'];
        }

        if (str_contains($namaLower, 'virus') || str_contains($namaLower, 'kuning')) {
            return $imageMap['virus'];
        }

        if (str_contains($namaLower, 'tikus') || str_contains($namaLower, 'babi') ||
            str_contains($namaLower, 'tupai')) {
            return $imageMap['mamalia'];
        }

        if (str_contains($namaLower, 'tungau') || str_contains($namaLower, 'mite')) {
            return $imageMap['tungau'];
        }

        // Default: serangga (paling umum)
        return $imageMap['serangga'];
    }

    private function printBanner(bool $dryRun, bool $autoDiscover = false): void
    {
        $mode = $dryRun ? ' [DRY RUN]' : '';
        $modeLabel = $autoDiscover ? ' | Auto-Discover' : '';
        $now  = now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB';
        $this->info('════════════════════════════════════════════════════════');
        $this->info(" 🐛 Petani Maju — Hama & Penyakit Auto-Seeder{$mode}{$modeLabel}");
        $this->info(" 🕐 {$now}");
        $this->info('════════════════════════════════════════════════════════');
        $this->newLine();
    }
}
