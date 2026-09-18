<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SupabaseService;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;

class CleanupSpamContent extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'agri:cleanup
                            {--dry-run : Tampilkan daftar konten yang akan dihapus tanpa benar-benar menghapus}
                            {--table=tips : Tabel yang akan dibersihkan (tips atau hama)}';

    /**
     * The console command description.
     */
    protected $description = 'Scan dan hapus konten spam, tidak relevan, atau berkualitas rendah dari database Supabase.';

    /**
     * Kata kunci yang langsung ditandai sebagai spam (lowercase).
     * Artikel yang judulnya mengandung salah satu kata ini akan dihapus tanpa konfirmasi AI.
     */
    private const SPAM_KEYWORDS = [
        // Judi & perjudian
        'togel', 'slot', 'judi', 'casino', 'poker', 'bandar', 'taruhan',
        'gacor', 'rtp', 'jackpot', 'scatter', 'maxwin', 'situs judi',
        'agen slot', 'bo slot', 'link slot', 'daftar slot',
        // Platform judi terkenal
        'gelastogel', 'nagaslot', 'hokibet', 'rajabet', 'dewabet',
        // Konten tidak relevan yang lolos filter
        'artis', 'selebriti', 'gosip', 'viral tiktok',
        // Placeholder / error content
        'konten tidak relevan', 'tidak ada tips', 'tidak tersedia',
        'panduan cepat cari informasi di internet',
    ];

    /**
     * Judul yang mengindikasikan artikel placeholder atau error dari AI.
     */
    private const ERROR_TITLE_PATTERNS = [
        '/^tips pertanian baru$/i',
        '/^hama\/penyakit baru$/i',
        '/^artikel pertanian baru$/i',
    ];

    public function handle(SupabaseService $supabase, GeminiService $gemini): int
    {
        $table  = $this->option('table');
        $dryRun = $this->option('dry-run');

        $this->printBanner($dryRun);

        if (! in_array($table, ['tips', 'hama'])) {
            $this->error("❌ Tabel '{$table}' tidak valid. Gunakan 'tips' atau 'hama'.");
            return Command::FAILURE;
        }

        // ── Ambil semua data dari tabel ──
        $this->line("📥 Mengambil semua data dari tabel '{$table}'...");
        $records = $supabase->select($table, [
            'select' => 'id,title,content,category',
            'order'  => 'id.asc',
            'limit'  => 1000,
        ]);

        if (empty($records)) {
            $this->warn("⚠️  Tabel '{$table}' kosong atau gagal diambil.");
            return Command::SUCCESS;
        }

        $total = count($records);
        $this->info("📋 Ditemukan {$total} record. Mulai scanning...");
        $this->newLine();

        $toDelete  = [];
        $toKeep    = [];
        $uncertain = [];

        foreach ($records as $record) {
            $id    = $record['id'] ?? '?';
            $title = $record['title'] ?? ($record['nama'] ?? '');
            $lower = strtolower($title);

            // ── Cek 1: Keyword blacklist (instant reject) ──
            $spamKw = $this->matchesSpamKeyword($lower);
            if ($spamKw) {
                $toDelete[] = ['id' => $id, 'title' => $title, 'reason' => "Keyword: '{$spamKw}'"];
                $this->line("   🗑️  [SPAM-KW] ID:{$id} — {$title}");
                continue;
            }

            // ── Cek 2: Error title pattern (placeholder dari AI) ──
            if ($this->matchesErrorPattern($title)) {
                $toDelete[] = ['id' => $id, 'title' => $title, 'reason' => 'Judul placeholder/error AI'];
                $this->line("   🗑️  [PLACEHOLDER] ID:{$id} — {$title}");
                continue;
            }

            // ── Cek 3: Konten terlalu pendek (< 100 karakter) ──
            $contentLength = strlen($record['content'] ?? '');
            if ($contentLength < 100) {
                $toDelete[] = ['id' => $id, 'title' => $title, 'reason' => "Konten terlalu pendek ({$contentLength} char)"];
                $this->line("   🗑️  [TOO-SHORT] ID:{$id} — {$title}");
                continue;
            }

            $toKeep[] = ['id' => $id, 'title' => $title];
        }

        $this->newLine();
        $this->line('─────────────────────────────────────────────────────');
        $this->info('📊 Hasil Scanning:');
        $this->line("   ✅ Akan dipertahankan : " . count($toKeep) . " record");
        $this->line("   🗑️  Akan dihapus       : " . count($toDelete) . " record");
        $this->line('─────────────────────────────────────────────────────');
        $this->newLine();

        if (empty($toDelete)) {
            $this->info('✨ Tidak ada konten spam/tidak relevan ditemukan. Database sudah bersih!');
            return Command::SUCCESS;
        }

        // Tampilkan tabel konten yang akan dihapus
        $this->line('📋 Daftar konten yang akan dihapus:');
        $this->table(
            ['ID', 'Judul', 'Alasan'],
            array_map(fn ($r) => [
                $r['id'],
                strlen($r['title']) > 50 ? substr($r['title'], 0, 47) . '...' : $r['title'],
                $r['reason'],
            ], $toDelete)
        );
        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 [DRY RUN] Tidak ada yang dihapus. Hapus flag --dry-run untuk eksekusi.');
            return Command::SUCCESS;
        }

        // ── Eksekusi penghapusan ──
        $this->info('🗑️  Menghapus konten spam dari database...');
        $deletedCount = 0;
        $failedCount  = 0;

        foreach ($toDelete as $item) {
            $success = $supabase->deleteByCondition($table, 'id=eq.' . $item['id']);
            if ($success) {
                $deletedCount++;
                $this->line("   ✅ Dihapus ID:{$item['id']} — {$item['title']}");
                Log::info("CleanupSpamContent: Deleted {$table} ID:{$item['id']} — Reason: {$item['reason']}");
            } else {
                $failedCount++;
                $this->error("   ❌ Gagal hapus ID:{$item['id']} — {$item['title']}");
            }
        }

        $this->newLine();
        $this->line('═════════════════════════════════════════════════════');
        $this->info("✅ Selesai! {$deletedCount} konten dihapus, {$failedCount} gagal.");
        $this->line('═════════════════════════════════════════════════════');

        return Command::SUCCESS;
    }

    /**
     * Cek apakah judul mengandung kata kunci spam.
     * Returns matched keyword string or null.
     */
    private function matchesSpamKeyword(string $lowerTitle): ?string
    {
        foreach (self::SPAM_KEYWORDS as $keyword) {
            if (str_contains($lowerTitle, $keyword)) {
                return $keyword;
            }
        }
        return null;
    }

    /**
     * Cek apakah judul cocok dengan pola error/placeholder dari AI.
     */
    private function matchesErrorPattern(string $title): bool
    {
        foreach (self::ERROR_TITLE_PATTERNS as $pattern) {
            if (preg_match($pattern, $title)) {
                return true;
            }
        }
        return false;
    }

    private function printBanner(bool $dryRun): void
    {
        $mode = $dryRun ? ' [DRY RUN — tidak ada yang dihapus]' : '';
        $this->info('════════════════════════════════════════════════════════');
        $this->info(" 🧹 Petani Maju — Content Cleanup Tool{$mode}");
        $this->info('════════════════════════════════════════════════════════');
        $this->newLine();
    }
}
