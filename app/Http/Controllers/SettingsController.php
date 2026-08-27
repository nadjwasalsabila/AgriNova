<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    public function __construct(private SupabaseService $supabase) {}

    /**
     * Show the settings page.
     */
    public function index()
    {
        return view('admin.settings.index', [
            'title'      => 'Pengaturan Akun',
            'adminName'  => session('admin_name', 'Administrator'),
            'adminEmail' => session('admin_email', 'admin@gmail.com'),
            'authMethod' => session('auth_method', 'env'),
        ]);
    }

    /**
     * Update profile information (Nama & Email).
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ], [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $authMethod = session('auth_method');
        $token      = session('supabase_token');
        $userId     = session('admin_id');

        if ($authMethod === 'supabase' && $token && $userId && $this->supabase->isConfigured()) {
            try {
                $url = rtrim(env('SUPABASE_URL'), '/');
                $key = env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_ANON_KEY');

                // Update user metadata & email in Supabase Auth via REST API
                $response = Http::withHeaders([
                    'apikey'        => env('SUPABASE_ANON_KEY'),
                    'Authorization' => "Bearer {$token}",
                    'Content-Type'  => 'application/json',
                ])->put("{$url}/auth/v1/user", [
                    'email' => $request->email,
                    'data'  => [
                        'full_name' => $request->name,
                        'name'      => $request->name,
                    ],
                ]);

                if ($response->failed()) {
                    $msg = $response->json('msg') ?? $response->json('message') ?? 'Gagal memperbarui profil di Supabase.';
                    return back()->withErrors(['email' => $msg])->withInput();
                }
            } catch (\Throwable $e) {
                return back()->withErrors(['email' => 'Gagal memperbarui profil: ' . $e->getMessage()])->withInput();
            }
        }

        // Update session state
        session([
            'admin_name'  => $request->name,
            'admin_email' => $request->email,
        ]);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required'     => 'Password baru wajib diisi.',
            'new_password.min'          => 'Password baru minimal 6 karakter.',
            'new_password.confirmed'    => 'Konfirmasi password baru tidak cocok.',
        ]);

        $authMethod = session('auth_method');
        $token      = session('supabase_token');

        if ($authMethod === 'supabase' && $token && $this->supabase->isConfigured()) {
            try {
                $url = rtrim(env('SUPABASE_URL'), '/');

                // Update password in Supabase Auth
                $response = Http::withHeaders([
                    'apikey'        => env('SUPABASE_ANON_KEY'),
                    'Authorization' => "Bearer {$token}",
                    'Content-Type'  => 'application/json',
                ])->put("{$url}/auth/v1/user", [
                    'password' => $request->new_password,
                ]);

                if ($response->failed()) {
                    $msg = $response->json('msg') ?? $response->json('message') ?? 'Gagal memperbarui password di Supabase.';
                    return back()->withErrors(['current_password' => $msg]);
                }
            } catch (\Throwable $e) {
                return back()->withErrors(['current_password' => 'Gagal memperbarui password: ' . $e->getMessage()]);
            }
        } else {
            // Dev/Env mode fallback validation
            $adminPass = env('ADMIN_PASSWORD_PLAIN', 'admin123');
            if ($request->current_password !== $adminPass) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
            }
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Password berhasil diperbarui!');
    }
}
