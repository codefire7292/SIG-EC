<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function index()
    {
        return Inertia::render('Contact', [
            'settings' => Setting::getGroup('general')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // In a real app, we might send an email or store in DB
        // For now, we'll just simulate success
        
        return back()->with('success', 'Message reçu ! Notre réseau de neurones traite votre demande.');
    }
}
