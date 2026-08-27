<?php

namespace App\Services;

class NotificationService
{
    /**
     * Add a new notification to the session array.
     */
    public static function add(string $type, string $title, string $message, ?string $link = null, ?string $time = null): void
    {
        $notifications = session('admin_notifications', []);
        
        // Unshift to put newest notification on top
        array_unshift($notifications, [
            'id'       => (string) \Illuminate\Support\Str::uuid(),
            'type'     => $type, // 'scan', 'obat_create', 'obat_update', 'obat_delete'
            'title'    => $title,
            'message'  => $message,
            'link'     => $link ?? '#',
            'time'     => $time ?? now()->locale('id')->diffForHumans(),
            'is_read'  => false,
        ]);

        // Keep maximum 30 notifications
        $notifications = array_slice($notifications, 0, 30);

        session(['admin_notifications' => $notifications]);
    }

    /**
     * Get all notifications from session, plus check for new AI scan records from Supabase.
     */
    public static function getNotifications(?SupabaseService $supabase = null): array
    {
        $notifications = session('admin_notifications', []);

        // Auto-check recent AI scan from Supabase if configured
        if ($supabase && $supabase->isConfigured()) {
            try {
                $lastCheckedId = session('last_notified_scan_id');
                $token = session('supabase_token');

                $recentScans = $supabase->select('prediction_history', [
                    'select' => 'id,created_at,plant_type,disease,confidence',
                    'order'  => 'created_at.desc',
                    'limit'  => 1,
                ], $token);

                if (! empty($recentScans)) {
                    $latest = $recentScans[0];
                    $latestId = $latest['id'] ?? null;

                    if ($latestId && $latestId !== $lastCheckedId) {
                        // Avoid duplicates if notification already exists in session
                        $alreadyExists = collect($notifications)->contains(fn ($n) => ($n['meta_id'] ?? null) === $latestId);

                        if (! $alreadyExists) {
                            $disease = $latest['disease'] ?? 'Penyakit Terdeteksi';
                            $plant   = $latest['plant_type'] ?? 'Tanaman';
                            $conf    = isset($latest['confidence']) ? round($latest['confidence'] * 100) . '%' : '';

                            array_unshift($notifications, [
                                'id'       => (string) \Illuminate\Support\Str::uuid(),
                                'meta_id'  => $latestId,
                                'type'     => 'scan',
                                'title'    => 'Hasil Scan AI Baru 🔍',
                                'message'  => "Deteksi {$disease} pada {$plant} ({$conf})",
                                'link'     => route('admin.riwayat-scan.index'),
                                'time'     => \Carbon\Carbon::parse($latest['created_at'] ?? now())->locale('id')->diffForHumans(),
                                'is_read'  => false,
                            ]);

                            session([
                                'last_notified_scan_id' => $latestId,
                                'admin_notifications'   => $notifications,
                            ]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Silently ignore API check error
            }
        }

        return $notifications;
    }

    /**
     * Count unread notifications.
     */
    public static function countUnread(): int
    {
        $notifications = session('admin_notifications', []);
        return collect($notifications)->where('is_read', false)->count();
    }

    /**
     * Mark all notifications as read.
     */
    public static function markAllAsRead(): void
    {
        $notifications = session('admin_notifications', []);
        foreach ($notifications as &$n) {
            $n['is_read'] = true;
        }
        session(['admin_notifications' => $notifications]);
    }
}
