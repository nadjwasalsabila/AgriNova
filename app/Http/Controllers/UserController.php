<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private SupabaseService $supabase) {}

    /**
     * Display a listing of users and subscription transactions.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status', 'all');
        $plan   = $request->query('plan', 'all');
        $tab    = $request->query('tab', 'users'); // 'users' or 'transactions'

        // 1. Get joined user list with filters
        $filters = [
            'search' => $search,
            'status' => $status,
            'plan'   => $plan,
        ];
        $users = $this->supabase->getUsersWithSubscriptions($filters);

        // 2. Get all raw subscriptions for transactions tab & metrics
        $allSubscriptions = $this->supabase->getSubscriptions();

        // 3. Filter subscriptions for the transactions tab if requested
        $transactions = $allSubscriptions;
        if (! empty($search)) {
            $q = strtolower($search);
            $transactions = array_filter($transactions, function ($item) use ($q) {
                $orderId = strtolower($item['order_id'] ?? '');
                $planName = strtolower($item['plan_name'] ?? '');
                $payment = strtolower($item['payment_type'] ?? '');
                $userId = strtolower($item['user_id'] ?? '');
                return str_contains($orderId, $q) 
                    || str_contains($planName, $q) 
                    || str_contains($payment, $q) 
                    || str_contains($userId, $q);
            });
        }
        if (! empty($status) && $status !== 'all') {
            $transactions = array_filter($transactions, fn ($item) => strtolower($item['status'] ?? '') === strtolower($status));
        }
        if (! empty($plan) && $plan !== 'all') {
            $transactions = array_filter($transactions, fn ($item) => str_contains(strtolower($item['plan_name'] ?? ''), strtolower($plan)));
        }

        // Map user email/name into each transaction for display
        $allUsers = $this->supabase->getAuthUsers();
        $userMap = [];
        foreach ($allUsers as $u) {
            $meta = $u['user_metadata'] ?? [];
            $userMap[$u['id']] = [
                'email' => $u['email'] ?? '-',
                'name'  => $meta['full_name'] ?? $meta['name'] ?? explode('@', $u['email'] ?? 'User')[0],
            ];
        }

        foreach ($transactions as &$tx) {
            $uId = $tx['user_id'] ?? '';
            $tx['user_name'] = $userMap[$uId]['name'] ?? 'User';
            $tx['user_email'] = $userMap[$uId]['email'] ?? '-';
        }
        unset($tx);

        // 4. Calculate high-level summary metrics
        $unfilteredUsers = $this->supabase->getUsersWithSubscriptions();
        $totalUsers = count($unfilteredUsers);
        $activeSubscribers = count(array_filter($unfilteredUsers, fn ($u) => $u['is_active']));
        $freeUsers = count(array_filter($unfilteredUsers, fn ($u) => $u['status'] === 'free'));
        $expiredUsers = count(array_filter($unfilteredUsers, fn ($u) => $u['status'] === 'expired'));
        
        $totalRevenue = array_sum(array_column($allSubscriptions, 'amount'));

        // Determine most popular plan
        $planCounts = [];
        foreach ($allSubscriptions as $s) {
            $p = $s['plan_name'] ?? 'Nova Basic';
            $planCounts[$p] = ($planCounts[$p] ?? 0) + 1;
        }
        arsort($planCounts);
        $popularPlan = ! empty($planCounts) ? array_key_first($planCounts) : 'Nova Basic';

        return view('admin.users.index', [
            'title'             => 'Kelola Pengguna & Langganan',
            'users'             => $users,
            'transactions'      => array_values($transactions),
            'totalUsers'        => $totalUsers,
            'activeSubscribers' => $activeSubscribers,
            'freeUsers'         => $freeUsers,
            'expiredUsers'      => $expiredUsers,
            'totalRevenue'      => $totalRevenue,
            'popularPlan'       => $popularPlan,
            'search'            => $search,
            'status'            => $status,
            'plan'              => $plan,
            'tab'               => $tab,
        ]);
    }

    /**
     * Display detailed user information and all their subscriptions.
     */
    public function show(string $id)
    {
        $user = $this->supabase->getUserWithSubscriptions($id);

        if (! $user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Pengguna tidak ditemukan di basis data Supabase.');
        }

        return view('admin.users.show', [
            'title' => 'Detail Pengguna: ' . $user['name'],
            'user'  => $user,
        ]);
    }

    /**
     * Update a subscription status.
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:active,cancelled,expired,pending',
        ]);

        $success = $this->supabase->updateSubscriptionStatus($id, $request->status);

        if ($success) {
            return back()->with('success', 'Status langganan berhasil diperbarui menjadi "' . ucfirst($request->status) . '".');
        }

        return back()->with('error', 'Gagal memperbarui status langganan di Supabase.');
    }
}
