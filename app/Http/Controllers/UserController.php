<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    //'
    public function index()
    {
        // Ubah menjadi 'restaurants' (plural)
        $users = User::with(['restaurants', 'roles'])->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $restaurants = Restaurant::all();
        $roles = Role::all(); // Ambil semua role dari Spatie
        return view('users.create', compact('restaurants', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'required|string|max:20|unique:users,nik',
            'password' => 'required|string|min:6',
            'role' => 'required|exists:roles,name',
            // Validasi Array Restoran
            'restaurants' => 'nullable|array',
            'restaurants.*' => 'exists:restaurants,id',
        ]);

        // 1. Buat User (Tanpa restaurant_id)
        $user = User::create([
            'name' => $request->name,
            'nik' => $request->nik,
            'password' => Hash::make($request->password),
        ]);

        // 2. Assign Role
        $user->assignRole($request->role);

        // 3. Assign Restaurants (Pivot)
        if ($request->has('restaurants')) {
            $user->restaurants()->sync($request->restaurants);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $restaurants = Restaurant::all();
        $roles = Role::all();
        return view('users.edit', compact('user', 'restaurants', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'required|string|max:20|unique:users,nik,' . $user->id,
            'role' => 'required|exists:roles,name',
            'password' => 'nullable|string|min:6',
            // Validasi Array
            'restaurants' => 'nullable|array',
            'restaurants.*' => 'exists:restaurants,id',
        ]);

        // 1. Update Data Diri
        $data = [
            'name' => $request->name,
            'nik' => $request->nik,
        ];
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);

        // 2. Sync Role
        $user->syncRoles($request->role);

        // 3. Sync Restaurants (Pivot)
        // Jika kosong (uncheck semua), sync([]) akan menghapus semua akses
        $user->restaurants()->sync($request->input('restaurants', []));

        return redirect()->route('users.index')->with('success', 'Data user diperbarui.');
    }

    public function destroy(User $user)
    {
        // Mencegah hapus diri sendiri
        if (auth()->id() == $user->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }
}
