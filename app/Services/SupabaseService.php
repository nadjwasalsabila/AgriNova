<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseService
{
    private string $url;
    private string $anonKey;
    private string $serviceKey;

    public function __construct()
    {
        $this->url        = rtrim(env('SUPABASE_URL', ''), '/');
        $this->anonKey    = env('SUPABASE_ANON_KEY', '');
        $this->serviceKey = trim(env('SUPABASE_SERVICE_ROLE_KEY', ''));
    }

    // ─────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────

    public function isConfigured(): bool
    {
        return ! empty($this->url) && ! empty($this->anonKey);
    }

    private function authHeaders(string $token = null): array
    {
        $key = $this->serviceKey ?: $this->anonKey;

        return [
            'apikey'        => $this->anonKey,
            'Authorization' => 'Bearer ' . ($token ?? $key),
            'Content-Type'  => 'application/json',
        ];
    }

    // ─────────────────────────────────────────────
    //  Authentication
    // ─────────────────────────────────────────────

    /**
     * Sign in with email & password via Supabase Auth.
     * Returns the full auth response array including access_token and user.
     *
     * @throws \Exception on failure
     */
    public function signIn(string $email, string $password): array
    {
        if (! $this->isConfigured()) {
            throw new \Exception('Supabase belum dikonfigurasi. Isi SUPABASE_URL dan SUPABASE_ANON_KEY di .env');
        }

        $response = Http::withHeaders([
            'apikey'       => $this->anonKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->url}/auth/v1/token?grant_type=password", [
            'email'    => $email,
            'password' => $password,
        ]);

        if ($response->failed()) {
            $msg = $response->json('error_description')
                ?? $response->json('msg')
                ?? $response->json('message')
                ?? 'Email atau password tidak valid.';
            throw new \Exception($msg);
        }

        return $response->json();
    }

    /**
     * Get the authenticated user from a valid access token.
     *
     * @throws \Exception on invalid/expired token
     */
    public function getUser(string $accessToken): array
    {
        $response = Http::withHeaders([
            'apikey'        => $this->anonKey,
            'Authorization' => "Bearer {$accessToken}",
        ])->get("{$this->url}/auth/v1/user");

        if ($response->failed()) {
            throw new \Exception('Token tidak valid atau sudah kadaluarsa.');
        }

        return $response->json();
    }

    /**
     * Sign out the user (revoke the access token).
     */
    public function signOut(string $accessToken): void
    {
        Http::withHeaders([
            'apikey'        => $this->anonKey,
            'Authorization' => "Bearer {$accessToken}",
        ])->post("{$this->url}/auth/v1/logout");
    }

    /**
     * Refresh an expired access token using a refresh token.
     */
    public function refreshToken(string $refreshToken): array
    {
        $response = Http::withHeaders([
            'apikey'       => $this->anonKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->url}/auth/v1/token?grant_type=refresh_token", [
            'refresh_token' => $refreshToken,
        ]);

        if ($response->failed()) {
            throw new \Exception('Gagal memperbarui sesi. Silakan login kembali.');
        }

        return $response->json();
    }

    // ─────────────────────────────────────────────
    //  Admin Role Check
    // ─────────────────────────────────────────────

    /**
     * Check if a user has admin privileges.
     * Priority order:
     *   1. app_metadata.role === 'admin' (set via Supabase dashboard/service key)
     *   2. user_metadata.role === 'admin'
     *   3. Email in ADMIN_EMAILS env var (comma-separated fallback)
     */
    public function isAdmin(array $user): bool
    {
        // 1. Check app_metadata (most secure — set by service role only)
        $appRole = data_get($user, 'app_metadata.role');
        if ($appRole === 'admin') {
            return true;
        }

        // 2. Check user_metadata
        $userRole = data_get($user, 'user_metadata.role');
        if ($userRole === 'admin') {
            return true;
        }

        // 3. Fallback: check email against ADMIN_EMAILS env var
        $adminEmails = collect(explode(',', env('ADMIN_EMAILS', '')))
            ->map(fn ($e) => trim(strtolower($e)))
            ->filter();

        $userEmail = strtolower(data_get($user, 'email', ''));

        return $adminEmails->contains($userEmail);
    }

    // ─────────────────────────────────────────────
    //  Data — Count rows in a table
    // ─────────────────────────────────────────────

    /**
     * Count rows in a Supabase table using Content-Range header.
     * Returns -1 if table doesn't exist or on error.
     */
    public function count(string $table, string $accessToken = null, bool $suppressWarnings = false): int
    {
        if (! $this->isConfigured()) {
            return -1;
        }

        try {
            // Use service key for full access; fallback to anon key
            $key   = $this->serviceKey ?: $this->anonKey;
            $token = $accessToken ?? $key;

            $response = Http::withHeaders([
                'apikey'        => $key,
                'Authorization' => "Bearer {$token}",
                'Prefer'        => 'count=exact',
            ])->get("{$this->url}/rest/v1/{$table}", [
                'select' => 'id',
                'limit'  => 1,
            ]);

            if ($response->failed()) {
                if (! $suppressWarnings) {
                    Log::warning("SupabaseService: failed to count '{$table}'", [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                }
                return -1;
            }

            // Parse Content-Range: "0-0/142" → 142
            $range = $response->header('Content-Range');
            if ($range && preg_match('/\/(\d+)$/', $range, $matches)) {
                return (int) $matches[1];
            }

            return count($response->json() ?? []);
        } catch (\Throwable $e) {
            if (! $suppressWarnings) {
                Log::error("SupabaseService: exception counting '{$table}': " . $e->getMessage());
            }
            return -1;
        }
    }

    /**
     * Count users from Supabase Auth (requires service role key).
     */
    public function countUsers(): int
    {
        if (empty($this->serviceKey)) {
            return -1;
        }

        try {
            $response = Http::withHeaders([
                'apikey'        => $this->serviceKey,
                'Authorization' => "Bearer {$this->serviceKey}",
            ])->get("{$this->url}/auth/v1/admin/users", [
                'page'     => 1,
                'per_page' => 1,
            ]);

            if ($response->failed()) {
                return -1;
            }

            return (int) data_get($response->json(), 'total', -1);
        } catch (\Throwable $e) {
            Log::error('SupabaseService: exception counting users: ' . $e->getMessage());
            return -1;
        }
    }

    /**
     * Count rows by trying multiple table names in sequence until one succeeds.
     * Returns -1 if all tables fail or aren't configured.
     */
    public function countWithFallbacks(array $tables, string $accessToken = null): int
    {
        if (! $this->isConfigured()) {
            return -1;
        }

        foreach ($tables as $table) {
            $count = $this->count($table, $accessToken, true);
            if ($count >= 0) {
                return $count;
            }
        }

        Log::warning("SupabaseService: failed to count with fallbacks for tables: " . implode(', ', $tables));

        return -1;
    }

    // ─────────────────────────────────────────────
    //  Generic DB CRUD Operations
    // ─────────────────────────────────────────────

    /**
     * Fetch records from a table with filters.
     */
    public function select(string $table, array $params = [], string $accessToken = null): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        try {
            $key = $this->serviceKey ?: $this->anonKey;
            $token = $accessToken ?? $key;

            $response = Http::withHeaders([
                'apikey'        => $key,
                'Authorization' => "Bearer {$token}",
            ])->get("{$this->url}/rest/v1/{$table}", $params);

            if ($response->failed()) {
                Log::error("SupabaseService: failed to select from {$table}", [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];
            }

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error("SupabaseService: exception selecting from {$table}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single record from a table by ID.
     */
    public function selectSingle(string $table, $id, string $accessToken = null): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $key = $this->serviceKey ?: $this->anonKey;
            $token = $accessToken ?? $key;

            $response = Http::withHeaders([
                'apikey'        => $key,
                'Authorization' => "Bearer {$token}",
            ])->get("{$this->url}/rest/v1/{$table}", [
                'id' => 'eq.' . $id,
            ]);

            if ($response->failed() || empty($response->json())) {
                return null;
            }

            return $response->json()[0] ?? null;
        } catch (\Throwable $e) {
            Log::error("SupabaseService: exception selectSingle from {$table}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Insert a new record into a table.
     */
    public function insert(string $table, array $data, string $accessToken = null): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $key = $this->serviceKey ?: $this->anonKey;
            $token = $accessToken ?? $key;

            $response = Http::withHeaders([
                'apikey'        => $key,
                'Authorization' => "Bearer {$token}",
                'Prefer'        => 'return=representation',
                'Content-Type'  => 'application/json',
            ])->post("{$this->url}/rest/v1/{$table}", $data);

            if ($response->failed()) {
                throw new \Exception('Insert failed: ' . $response->body());
            }

            return $response->json()[0] ?? null;
        } catch (\Throwable $e) {
            Log::error("SupabaseService: exception inserting into {$table}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing record in a table by ID.
     */
    public function update(string $table, $id, array $data, string $accessToken = null): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $key = $this->serviceKey ?: $this->anonKey;
            $token = $accessToken ?? $key;

            $response = Http::withHeaders([
                'apikey'        => $key,
                'Authorization' => "Bearer {$token}",
                'Prefer'        => 'return=representation',
                'Content-Type'  => 'application/json',
            ])->patch("{$this->url}/rest/v1/{$table}?id=eq.{$id}", $data);

            if ($response->failed()) {
                throw new \Exception('Update failed: ' . $response->body());
            }

            return $response->json()[0] ?? null;
        } catch (\Throwable $e) {
            Log::error("SupabaseService: exception updating {$table}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update records matching an arbitrary PostgREST filter condition.
     * Example: $condition = "obat_id=eq.5"  or  "id=eq.3"
     */
    public function updateByCondition(string $table, string $condition, array $data, string $accessToken = null): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $key   = $this->serviceKey ?: $this->anonKey;
            $token = $accessToken ?? $key;

            $response = Http::withHeaders([
                'apikey'        => $key,
                'Authorization' => "Bearer {$token}",
                'Content-Type'  => 'application/json',
                'Prefer'        => 'return=minimal',
            ])->patch("{$this->url}/rest/v1/{$table}?{$condition}", $data);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("SupabaseService: exception in updateByCondition on {$table}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete records matching an arbitrary PostgREST filter condition.
     * Example: $condition = "obat_id=eq.5"
     */
    public function deleteByCondition(string $table, string $condition, string $accessToken = null): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $key   = $this->serviceKey ?: $this->anonKey;
            $token = $accessToken ?? $key;

            $response = Http::withHeaders([
                'apikey'        => $key,
                'Authorization' => "Bearer {$token}",
            ])->delete("{$this->url}/rest/v1/{$table}?{$condition}");

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("SupabaseService: exception in deleteByCondition on {$table}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a record from a table by ID.
     */
    public function delete(string $table, $id, string $accessToken = null): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $key = $this->serviceKey ?: $this->anonKey;
            $token = $accessToken ?? $key;

            $response = Http::withHeaders([
                'apikey'        => $key,
                'Authorization' => "Bearer {$token}",
            ])->delete("{$this->url}/rest/v1/{$table}?id=eq.{$id}");

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("SupabaseService: exception deleting from {$table}: " . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────
    //  Supabase Storage Operations
    // ─────────────────────────────────────────────

    /**
     * Upload a file to a Supabase Storage bucket.
     * Returns the public URL of the uploaded file.
     */
    public function uploadStorage(string $bucket, string $path, string $fileContents, string $mimeType): string
    {
        if (! $this->isConfigured()) {
            throw new \Exception('Supabase tidak dikonfigurasi.');
        }

        $url = "{$this->url}/storage/v1/object/{$bucket}/{$path}";
        $key = $this->serviceKey ?: $this->anonKey;

        $response = Http::withHeaders([
            'apikey'        => $this->anonKey,
            'Authorization' => "Bearer {$key}",
            'Content-Type'  => $mimeType,
        ])->withBody($fileContents, $mimeType)->post($url);

        if ($response->failed()) {
            throw new \Exception('Gagal mengupload file ke Storage Supabase: ' . $response->body());
        }

        return "{$this->url}/storage/v1/object/public/{$bucket}/{$path}";
    }

    /**
     * Delete a file from a Supabase Storage bucket.
     */
    public function deleteStorage(string $bucket, string $path): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $url = "{$this->url}/storage/v1/object/{$bucket}/{$path}";
        $key = $this->serviceKey ?: $this->anonKey;

        Http::withHeaders([
            'apikey'        => $this->anonKey,
            'Authorization' => "Bearer {$key}",
        ])->delete($url);
    }

    // ─────────────────────────────────────────────
    //  Manage Users & Subscriptions
    // ─────────────────────────────────────────────

    /**
     * Get all raw subscriptions from Supabase 'subscriptions' table.
     */
    public function getSubscriptions(array $filters = []): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        try {
            $key = $this->serviceKey ?: $this->anonKey;
            $query = [
                'select' => '*',
                'order'  => 'created_at.desc',
            ];

            if (! empty($filters['status'])) {
                $query['status'] = 'eq.' . $filters['status'];
            }
            if (! empty($filters['user_id'])) {
                $query['user_id'] = 'eq.' . $filters['user_id'];
            }
            if (! empty($filters['plan_name'])) {
                $query['plan_name'] = 'ilike.*' . $filters['plan_name'] . '*';
            }

            $response = Http::withHeaders([
                'apikey'        => $key,
                'Authorization' => "Bearer {$key}",
            ])->get("{$this->url}/rest/v1/subscriptions", $query);

            return $response->successful() ? ($response->json() ?? []) : [];
        } catch (\Throwable $e) {
            Log::error('SupabaseService getSubscriptions error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch all users from Supabase Auth admin API.
     */
    public function getAuthUsers(): array
    {
        if (empty($this->serviceKey)) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'apikey'        => $this->serviceKey,
                'Authorization' => "Bearer {$this->serviceKey}",
            ])->get("{$this->url}/auth/v1/admin/users", [
                'per_page' => 1000,
            ]);

            if ($response->failed()) {
                Log::warning('SupabaseService getAuthUsers failed: ' . $response->body());
                return [];
            }

            $json = $response->json();
            return $json['users'] ?? (is_array($json) ? $json : []);
        } catch (\Throwable $e) {
            Log::error('SupabaseService getAuthUsers error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get joined users with their subscription details, active plan, and transaction history.
     */
    public function getUsersWithSubscriptions(array $filters = []): array
    {
        $users = $this->getAuthUsers();
        $subscriptions = $this->getSubscriptions();

        // Index subscriptions by user_id
        $subsByUser = [];
        foreach ($subscriptions as $sub) {
            $uId = $sub['user_id'] ?? null;
            if ($uId) {
                $subsByUser[$uId][] = $sub;
            }
        }

        $now = now();
        $userList = [];

        foreach ($users as $user) {
            $uId = $user['id'];
            $meta = $user['user_metadata'] ?? [];
            $userSubs = $subsByUser[$uId] ?? [];

            // Sort user subscriptions by created_at desc
            usort($userSubs, fn ($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

            // Determine active subscription
            $activeSub = null;
            foreach ($userSubs as $s) {
                $status = strtolower($s['status'] ?? '');
                $expiresAt = ! empty($s['expires_at']) ? \Carbon\Carbon::parse($s['expires_at']) : null;
                if ($status === 'active' && ($expiresAt === null || $expiresAt->isAfter($now))) {
                    $activeSub = $s;
                    break;
                }
            }

            // Fallback to user_metadata if activeSub not found in table
            $isMetaPremium = (bool) ($meta['is_premium'] ?? false);
            $metaExpiry = ! empty($meta['premium_expiry']) ? \Carbon\Carbon::parse($meta['premium_expiry']) : null;
            $metaIsActive = $isMetaPremium && ($metaExpiry === null || $metaExpiry->isAfter($now));

            $planName = 'Gratis';
            $status = 'free';
            $expiresAt = null;
            $startedAt = null;

            if ($activeSub) {
                $planName = $activeSub['plan_name'] ?? 'Nova Basic';
                $status = 'active';
                $expiresAt = $activeSub['expires_at'] ?? null;
                $startedAt = $activeSub['started_at'] ?? null;
            } elseif ($metaIsActive) {
                $planName = $meta['premium_plan'] ?? 'Nova Basic';
                $status = 'active';
                $expiresAt = $meta['premium_expiry'] ?? null;
            } elseif (! empty($userSubs)) {
                // User previously had subscriptions but now expired
                $latest = $userSubs[0];
                $planName = $latest['plan_name'] ?? 'Nova Basic';
                $status = 'expired';
                $expiresAt = $latest['expires_at'] ?? null;
                $startedAt = $latest['started_at'] ?? null;
            }

            $totalSpent = array_sum(array_column($userSubs, 'amount'));

            $name = $meta['full_name'] 
                ?? $meta['name'] 
                ?? (explode('@', $user['email'] ?? 'User')[0]);

            $userData = [
                'id'                   => $uId,
                'email'                => $user['email'] ?? '-',
                'name'                 => $name,
                'phone'                => $user['phone'] ?? ($meta['phone'] ?? null),
                'avatar'               => $meta['avatar_url'] ?? null,
                'created_at'           => $user['created_at'] ?? null,
                'last_sign_in_at'      => $user['last_sign_in_at'] ?? null,
                'email_confirmed_at'   => $user['email_confirmed_at'] ?? null,
                'plan_name'            => $planName,
                'status'               => $status, // active, expired, free
                'is_active'            => $status === 'active',
                'expires_at'           => $expiresAt,
                'started_at'           => $startedAt,
                'active_subscription'  => $activeSub,
                'subscriptions'        => $userSubs,
                'total_orders'         => count($userSubs),
                'total_spent'          => $totalSpent,
                'user_metadata'        => $meta,
            ];

            // Filter: Search
            if (! empty($filters['search'])) {
                $q = strtolower($filters['search']);
                $searchable = strtolower($userData['name'] . ' ' . $userData['email'] . ' ' . $userData['id']);
                // Also search in user orders
                foreach ($userSubs as $sub) {
                    $searchable .= ' ' . strtolower($sub['order_id'] ?? '') . ' ' . strtolower($sub['payment_type'] ?? '');
                }
                if (! str_contains($searchable, $q)) {
                    continue;
                }
            }

            // Filter: Status (all, active, free, expired)
            if (! empty($filters['status']) && $filters['status'] !== 'all') {
                if ($userData['status'] !== $filters['status']) {
                    continue;
                }
            }

            // Filter: Plan Name
            if (! empty($filters['plan']) && $filters['plan'] !== 'all') {
                $planFilter = strtolower($filters['plan']);
                $userPlan = strtolower($userData['plan_name']);
                if (! str_contains($userPlan, $planFilter)) {
                    continue;
                }
            }

            $userList[] = $userData;
        }

        // Sort: Active users first, then by registration created_at desc
        usort($userList, function ($a, $b) {
            if ($a['is_active'] !== $b['is_active']) {
                return $a['is_active'] ? -1 : 1;
            }
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        return $userList;
    }

    /**
     * Get a single user with full subscription details.
     */
    public function getUserWithSubscriptions(string $userId): ?array
    {
        $users = $this->getUsersWithSubscriptions();
        foreach ($users as $user) {
            if ($user['id'] === $userId) {
                return $user;
            }
        }
        return null;
    }

    /**
     * Update subscription status in Supabase.
     */
    public function updateSubscriptionStatus(string $subId, string $status): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $key = $this->serviceKey ?: $this->anonKey;
            $response = Http::withHeaders([
                'apikey'        => $key,
                'Authorization' => "Bearer {$key}",
                'Content-Type'  => 'application/json',
                'Prefer'        => 'return=representation',
            ])->patch("{$this->url}/rest/v1/subscriptions?id=eq.{$subId}", [
                'status'     => $status,
                'updated_at' => now()->toIso8601String(),
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('SupabaseService updateSubscriptionStatus error: ' . $e->getMessage());
            return false;
        }
    }
}

