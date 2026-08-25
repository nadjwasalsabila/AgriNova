<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private SupabaseService $supabase) {}

    /**
     * Show the admin dashboard with live stats from Supabase.
     */
    public function index()
    {
        $token = session('supabase_token');

        // ── Fetch counts (each wrapped to handle missing tables gracefully) ──
        $counts = $this->fetchCounts($token);

        // ── Build stat cards in exact requested order ──
        $stats = [
            [
                'label'      => 'Total Tanaman',
                'value'      => $this->formatCount($counts['tanaman']),
                'raw'        => $counts['tanaman'],
                'icon'       => 'plant',
                'icon_bg'    => 'bg-[#E8F5E9]',
                'icon_color' => 'text-[#1B5E20]',
                'href'       => '#',
            ],
            [
                'label'      => 'Total Penyakit',
                'value'      => $this->formatCount($counts['penyakit']),
                'raw'        => $counts['penyakit'],
                'icon'       => 'bug',
                'icon_bg'    => 'bg-[#FFEBEE]',
                'icon_color' => 'text-[#B71C1C]',
                'href'       => '#',
            ],
            [
                'label'      => 'Total Obat',
                'value'      => $this->formatCount($counts['obat']),
                'raw'        => $counts['obat'],
                'icon'       => 'pill',
                'icon_bg'    => 'bg-[#F3E5F5]',
                'icon_color' => 'text-[#6A1B9A]',
                'href'       => '#',
            ],
            [
                'label'      => 'Total Artikel',
                'value'      => $this->formatCount($counts['artikel']),
                'raw'        => $counts['artikel'],
                'icon'       => 'lightbulb',
                'icon_bg'    => 'bg-[#FFF9C4]',
                'icon_color' => 'text-[#F57F17]',
                'href'       => '#',
            ],
            [
                'label'      => 'Total Pengguna',
                'value'      => $this->formatCount($counts['users']),
                'raw'        => $counts['users'],
                'icon'       => 'users',
                'icon_bg'    => 'bg-[#FFF3E0]',
                'icon_color' => 'text-[#E65100]',
                'href'       => '#',
            ],
            [
                'label'      => 'Total Transaksi',
                'value'      => $this->formatCount($counts['transaksi']),
                'raw'        => $counts['transaksi'],
                'icon'       => 'receipt',
                'icon_bg'    => 'bg-[#E3F2FD]',
                'icon_color' => 'text-[#1565C0]',
                'href'       => '#',
            ],
        ];

        // ── Recent scans for activity feed ──
        $recentScans = $this->fetchRecentScans($token);

        return view('admin.dashboard', [
            'title'            => 'Dashboard',
            'stats'            => $stats,
            'recentScans'      => $recentScans,
            'supabaseOnline'   => $this->supabase->isConfigured(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  Private helpers
    // ─────────────────────────────────────────────

    private function fetchCounts(?string $token): array
    {
        if (! $this->supabase->isConfigured()) {
            return array_fill_keys(['tanaman', 'penyakit', 'obat', 'artikel', 'users', 'transaksi'], -1);
        }

        // Run counts with database structure fallbacks
        return [
            'tanaman'   => $this->supabase->countWithFallbacks(['tanaman', 'plants'], $token),
            'penyakit'  => $this->supabase->countWithFallbacks(['penyakit', 'diseases', 'hama'], $token),
            'obat'      => $this->supabase->countWithFallbacks(['obat', 'medicines'], $token),
            'artikel'   => $this->supabase->countWithFallbacks(['tips', 'artikel', 'articles'], $token),
            'users'     => $this->supabase->countUsers() === -1 
                ? $this->supabase->countWithFallbacks(['users', 'profiles', 'members'], $token) 
                : $this->supabase->countUsers(),
            'transaksi' => $this->supabase->countWithFallbacks(['transaksi', 'transactions'], $token),
        ];
    }

    private function fetchRecentScans(?string $token): array
    {
        if (! $this->supabase->isConfigured() || ! $token) {
            return [];
        }

        try {
            $key = env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_ANON_KEY');
            $url = rtrim(env('SUPABASE_URL'), '/');

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey'        => $key,
                'Authorization' => "Bearer {$token}",
            ])->get("{$url}/rest/v1/prediction_history", [
                'select'   => 'id,created_at,plant_type,disease_name,confidence,image_url',
                'order'    => 'created_at.desc',
                'limit'    => 8,
            ]);

            return $response->successful() ? ($response->json() ?? []) : [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Format count for display.
     * -1 = table doesn't exist or error → show "N/A"
     */
    private function formatCount(int $count): string
    {
        if ($count < 0) return 'N/A';
        if ($count >= 1_000_000) return number_format($count / 1_000_000, 1) . 'M';
        if ($count >= 1_000) return number_format($count / 1_000, 1) . 'K';
        return (string) $count;
    }
}
