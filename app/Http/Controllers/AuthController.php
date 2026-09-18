<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private SupabaseService $supabase) {}

    // ─────────────────────────────────────────────
    //  Show Login Form
    // ─────────────────────────────────────────────

    public function showLogin()
    {
        if (session('admin_authenticated')) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login', [
            'supabaseConfigured' => $this->supabase->isConfigured(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  Handle Login
    // ─────────────────────────────────────────────

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
        ]);

        // ── Supabase Auth ──
        if ($this->supabase->isConfigured()) {
            return $this->loginWithSupabase($request);
        }

        // ── Fallback: .env credentials (dev mode) ──
        return $this->loginWithEnvCredentials($request);
    }

    /**
     * Authenticate via Supabase — verify admin role.
     */
    private function loginWithSupabase(Request $request)
    {
        try {
            // 1. Sign in with Supabase Auth
            $auth = $this->supabase->signIn($request->email, $request->password);

            $accessToken  = data_get($auth, 'access_token');
            $refreshToken = data_get($auth, 'refresh_token');
            $expiresIn    = data_get($auth, 'expires_in', 3600);
            $user         = data_get($auth, 'user', []);

            if (! $accessToken) {
                throw new \Exception('Tidak mendapatkan token dari Supabase.');
            }

            // 2. Check admin role
            if (! $this->supabase->isAdmin($user)) {
                // Sign out from Supabase to invalidate the token
                $this->supabase->signOut($accessToken);

                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Akun ini tidak memiliki akses admin.']);
            }

            // 3. Store session
            $request->session()->regenerate();

            session([
                'admin_authenticated'  => true,
                'admin_name'           => data_get($user, 'user_metadata.full_name')
                                       ?? data_get($user, 'user_metadata.name')
                                       ?? explode('@', $request->email)[0],
                'admin_email'          => data_get($user, 'email', $request->email),
                'admin_id'             => data_get($user, 'id'),
                'supabase_token'       => $accessToken,
                'supabase_refresh'     => $refreshToken,
                'supabase_expires_at'  => now()->addSeconds($expiresIn - 60)->timestamp,
                'auth_method'          => 'supabase',
            ]);

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Selamat datang kembali, ' . session('admin_name') . '!');

        } catch (\Exception $e) {
            // Fallback to env credentials if they match the admin configuration in .env
            $adminEmail = env('ADMIN_EMAIL', 'admin@agrinova.id');
            $adminPass  = env('ADMIN_PASSWORD_PLAIN', 'admin123');

            if ($request->email === $adminEmail && $request->password === $adminPass) {
                return $this->loginWithEnvCredentials($request);
            }

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $e->getMessage()]);
        }
    }

    /**
     * Dev fallback: authenticate using .env credentials.
     */
    private function loginWithEnvCredentials(Request $request)
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@agrinova.id');
        $adminPass  = env('ADMIN_PASSWORD_PLAIN', 'admin123');

        if ($request->email !== $adminEmail || $request->password !== $adminPass) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password tidak sesuai.']);
        }

        $request->session()->regenerate();

        session([
            'admin_authenticated' => true,
            'admin_name'          => env('ADMIN_NAME', 'Administrator'),
            'admin_email'         => $adminEmail,
            'admin_id'            => null,
            'supabase_token'      => null,
            'auth_method'         => 'env',
        ]);

        return redirect()->intended(route('admin.dashboard'))
            ->with('success', 'Selamat datang kembali!');
    }

    // ─────────────────────────────────────────────
    //  Logout
    // ─────────────────────────────────────────────

    public function logout(Request $request)
    {
        // Revoke Supabase token if present
        $token = session('supabase_token');
        if ($token && $this->supabase->isConfigured()) {
            try {
                $this->supabase->signOut($token);
            } catch (\Throwable) {
                // Silently ignore if token already expired
            }
        }

        $request->session()->flush();
        $request->session()->regenerate();

        return redirect()->route('admin.login')
            ->with('success', 'Anda telah berhasil logout.');
    }
}
