<?php

namespace App\Http\Middleware;

use App\Services\SupabaseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function __construct(private SupabaseService $supabase) {}

    /**
     * Handle an incoming request.
     *
     * Validates admin session:
     *  1. Session must have admin_authenticated = true
     *  2. If auth_method is 'supabase', check token expiry and refresh if needed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ── Not authenticated at all ──
        if (! session('admin_authenticated')) {
            return $this->redirectToLogin($request, 'Silakan login terlebih dahulu.');
        }

        // ── Supabase token validation ──
        if (session('auth_method') === 'supabase' && $this->supabase->isConfigured()) {
            $result = $this->validateSupabaseSession($request);
            if ($result !== null) {
                return $result;
            }
        }

        return $next($request);
    }

    /**
     * Validate and auto-refresh Supabase token.
     * Returns a redirect Response if session is invalid, null if OK.
     */
    private function validateSupabaseSession(Request $request): ?Response
    {
        $token     = session('supabase_token');
        $expiresAt = session('supabase_expires_at');
        $refresh   = session('supabase_refresh');

        // No token stored
        if (! $token) {
            return $this->redirectToLogin($request, 'Sesi tidak valid. Silakan login kembali.');
        }

        // Token still valid
        if ($expiresAt && now()->timestamp < $expiresAt) {
            return null;
        }

        // ── Token expired — try to refresh ──
        if ($refresh) {
            try {
                $newAuth = $this->supabase->refreshToken($refresh);

                session([
                    'supabase_token'      => data_get($newAuth, 'access_token', $token),
                    'supabase_refresh'    => data_get($newAuth, 'refresh_token', $refresh),
                    'supabase_expires_at' => now()->addSeconds(
                        data_get($newAuth, 'expires_in', 3600) - 60
                    )->timestamp,
                ]);

                return null; // Refreshed successfully — continue
            } catch (\Throwable) {
                // Refresh failed — force re-login
            }
        }

        // Flush session and redirect
        $request->session()->flush();
        $request->session()->regenerate();

        return $this->redirectToLogin($request, 'Sesi Anda telah berakhir. Silakan login kembali.');
    }

    private function redirectToLogin(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 401);
        }

        return redirect()->route('admin.login')->with('error', $message);
    }
}
