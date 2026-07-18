<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $superAdminEmail = 'youssouphbadji2013@gmail.com';
        $users = User::with('roles')
            ->where('email', '!=', $superAdminEmail)
            ->paginate(15);

        // Pre-compute URLs server-side to avoid Ziggy stale manifest issues
        $users->getCollection()->transform(function ($user) {
            $user->edit_url = route('admin.users.edit', $user->id);
            $user->delete_url = route('admin.users.destroy', $user->id);
            $user->toggle_status_url = route('admin.users.toggle-status', $user->id);
            return $user;
        });

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'create_url' => route('admin.users.create'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Form', [
            'roles' => Role::all()->pluck('name'),
            'is_edit' => false,
            'back_url' => route('admin.users.index'),
            'store_url' => route('admin.users.store'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('users')->whereNull('deleted_at'),
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::onlyTrashed()->where('email', $validated['email'])->first();

        if ($user) {
            $user->restore();
            $user->update([
                'name' => $validated['name'],
                'password' => Hash::make($validated['password']),
                'is_active' => true,
            ]);
            $user->syncRoles($validated['role']);
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
            $user->assignRole($validated['role']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Form', [
            'user' => $user->load('roles'),
            'roles' => Role::all()->pluck('name'),
            'is_edit' => true,
            'back_url' => route('admin.users.index'),
            'update_url' => route('admin.users.update', $user->id),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => 'required|string|exists:roles,name',
        ]);

        if ((int) $user->id === (int) auth()->id() && !$user->hasRole($validated['role'])) {
            return back()->withErrors(['role' => 'Vous ne pouvez pas modifier votre propre rôle.']);
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles($validated['role']);

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function toggleStatus(User $user)
    {
        if (!auth()->user()->hasRole(\App\Enums\UserRole::ADMIN->value)) {
            abort(403, "Seul l'Administrateur technique peut suspendre ou réactiver un compte.");
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier le statut de votre propre compte.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $msg = $user->is_active
            ? "Le compte de {$user->name} a été réactivé."
            : "Le compte de {$user->name} a été suspendu.";

        return redirect()->route('admin.users.index')->with('success', $msg);
    }
}
