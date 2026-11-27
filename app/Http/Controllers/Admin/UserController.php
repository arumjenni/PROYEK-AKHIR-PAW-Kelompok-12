<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /* ----------------------------------------------------------- READ */

    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'petugas' ? User::ROLE_PETUGAS : User::ROLE_WARGA;
        $q = trim((string) $request->query('q', ''));

        $counts = User::whereIn('role', [User::ROLE_WARGA, User::ROLE_PETUGAS])
            ->selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role');

        $query = User::where('role', $tab);

        if ($q !== '') {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                    ->orWhere('username', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('nik', 'like', $like)
                    ->orWhere('badge_number', 'like', $like);
            });
        }

        if ($tab === User::ROLE_WARGA) {
            $query->withCount([
                'reports as valid_count' => fn ($r) => $r->where('status', 'approved'),
                'reports as total_count',
            ]);
        } else {
            $query->withCount([
                'reviewedReports as month_count' => fn ($r) => $r->where('reviewed_at', '>=', now()->startOfMonth()),
                'reviewedReports as total_count',
            ]);
        }

        return view('admin.users.index', [
            'users' => $query->orderBy('name')->paginate(12)->withQueryString(),
            'tab' => $tab,
            'q' => $q,
            'counts' => [
                'warga' => (int) ($counts[User::ROLE_WARGA] ?? 0),
                'petugas' => (int) ($counts[User::ROLE_PETUGAS] ?? 0),
            ],
        ]);
    }

    /* ----------------------------------------------------------- CREATE */

    public function create(Request $request): View
    {
        return view('admin.users.form', [
            'user' => null,
            'role' => $request->query('role') === 'petugas' ? User::ROLE_PETUGAS : User::ROLE_WARGA,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $role = $request->input('role') === User::ROLE_PETUGAS ? User::ROLE_PETUGAS : User::ROLE_WARGA;

        $data = $request->validate($this->rules(null, $role) + [
            'username' => ['required', 'string', 'alpha_dash', 'min:4', 'max:30', 'unique:users,username'],
        ]);

        $data = $this->clean($data, $role);
        User::create($data + ['role' => $role, 'points' => 0, 'is_active' => true]);

        return redirect()->route('admin.users.index', ['tab' => $role])
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /* ----------------------------------------------------------- UPDATE */

    public function edit(User $user): View
    {
        abort_if($user->isAdmin(), 404);

        return view('admin.users.form', ['user' => $user, 'role' => $user->role]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 404);

        $data = $request->validate($this->rules($user, $user->role));
        $data = $this->clean($data, $user->role);

        if (empty($data['password'])) {
            unset($data['password']); // kosong = password tidak diubah
        }

        $user->update($data);

        return redirect()->route('admin.users.index', ['tab' => $user->role])
            ->with('success', "Data {$user->name} berhasil diperbarui.");
    }

    /** Aktifkan / nonaktifkan akun. Akun nonaktif tidak bisa masuk, riwayat laporan tetap tersimpan. */
    public function toggle(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active
            ? "Akun {$user->name} diaktifkan kembali."
            : "Akun {$user->name} dinonaktifkan.");
    }

    /* ----------------------------------------------------------- helper */

    private function rules(?User $user, string $role): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => [$user ? 'nullable' : 'required', Password::min(6)],
        ];

        if ($role === User::ROLE_PETUGAS) {
            $rules += [
                'badge_number' => ['required', 'string', 'max:30', Rule::unique('users', 'badge_number')->ignore($user?->id)],
                'unit' => ['nullable', 'string', 'max:100'],
                'shift_start' => ['nullable', 'date_format:H:i'],
                'shift_end' => ['nullable', 'date_format:H:i'],
            ];
        } else {
            $rules += [
                'nik' => ['nullable', 'digits:16', Rule::unique('users', 'nik')->ignore($user?->id)],
            ];
        }

        return $rules;
    }

    /** Ubah string kosong jadi null supaya kolom unique tidak bentrok. */
    private function clean(array $data, string $role): array
    {
        foreach (['phone', 'nik', 'badge_number', 'unit', 'shift_start', 'shift_end'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] === null) {
                continue;
            }
            if (array_key_exists($key, $data) && trim((string) $data[$key]) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
