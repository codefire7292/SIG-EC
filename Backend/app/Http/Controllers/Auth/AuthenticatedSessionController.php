<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $loginValue = trim($request->login);
        $isEmail = filter_var($loginValue, FILTER_VALIDATE_EMAIL);
        $loginField = $isEmail ? 'email' : 'telephone';

        // Si ce n'est pas un email, valider le format téléphone
        if (!$isEmail) {
            // Permet +, espaces, tirets et chiffres (min 7 chiffres, max 20 caractères)
            if (!preg_match('/^\+?[0-9\s\-]{7,20}$/', $loginValue)) {
                throw ValidationException::withMessages([
                    'login' => "Le format de l'identifiant (email ou téléphone) est invalide.",
                ]);
            }
        }

        if (! Auth::attempt([$loginField => $loginValue, 'password' => $request->password], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'login' => "Ces identifiants ne correspondent pas à nos enregistrements.",
            ]);
        }

        $user = Auth::user();

        // Security check: ensure account is active
        if (isset($user->is_active) && !$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => "Votre compte est actuellement désactivé. Veuillez contacter l'administration.",
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Display the change password view.
     */
    public function changePassword(): \Inertia\Response
    {
        return Inertia::render('Auth/ChangePassword');
    }

    /**
     * Handle a password change request.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $mustChange = $user->must_change_password;
        
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
            'profile_photo' => [$mustChange ? 'required' : 'nullable', 'image', 'max:2500'],
        ]);

        if (Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => "Le nouveau mot de passe doit être différent du mot de passe actuel.",
            ]);
        }

        // Update fields explicitly
        $user->password = Hash::make($request->password);
        $user->must_change_password = false;

        // Store profile photo if provided
        if ($request->hasFile('profile_photo')) {
            try {
                $path = $request->file('profile_photo')->store('profile-photos', 'public');
                $user->profile_photo_path = $path;
            } catch (\Exception $e) {
                \Log::error('Profile photo storage error: ' . $e->getMessage());
                throw ValidationException::withMessages([
                    'profile_photo' => "Erreur lors de l'enregistrement de l'image.",
                ]);
            }
        }

        $user->save();

        // Refresh user in guard
        Auth::setUser($user);

        return redirect()->route('dashboard')->with('success', 'Votre profil a été mis à jour avec succès.');
    }
}
