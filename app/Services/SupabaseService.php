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
        $this->serviceKey = env('SUPABASE_SERVICE_ROLE_KEY', '');
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
}
