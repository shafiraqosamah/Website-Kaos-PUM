<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $roleFilter = strtolower((string) $request->query('role', 'all'));
        $search = trim((string) $request->query('q', ''));

        $allowedRoles = [
            User::ROLE_CUSTOMER,
            User::ROLE_FINANCE,
            User::ROLE_PRODUCTION,
            User::ROLE_ADMIN,
            User::ROLE_MANAGER,
            User::ROLE_OWNER,
        ];

        $usersQuery = User::query()->latest('id');

        if ($roleFilter !== 'all' && in_array($roleFilter, $allowedRoles, true)) {
            $usersQuery->where('role', $roleFilter);
        }

        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('company_name', 'like', '%' . $search . '%');
            });
        }

        $users = $usersQuery->paginate(12)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roleFilter' => $roleFilter,
            'search' => $search,
            'roleOptions' => [
                'all' => 'Semua Role',
                User::ROLE_CUSTOMER => 'Customer',
                User::ROLE_FINANCE => 'Keuangan/Finance',
                User::ROLE_PRODUCTION => 'Produksi',
                User::ROLE_ADMIN => 'Admin',
                User::ROLE_MANAGER => 'Manager',
                User::ROLE_OWNER => 'Owner',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'company_name' => ['nullable', 'string', 'max:190'],
            'role' => ['required', 'string', 'in:' . implode(',', [
                User::ROLE_CUSTOMER,
                User::ROLE_FINANCE,
                User::ROLE_PRODUCTION,
                User::ROLE_ADMIN,
                User::ROLE_MANAGER,
                User::ROLE_OWNER,
            ])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    public function show(User $user): View
    {
        return view('admin.users.show', [
            'userData' => $user,
        ]);
    }
}
