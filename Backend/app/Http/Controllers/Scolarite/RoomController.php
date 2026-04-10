<?php

namespace App\Http\Controllers\Scolarite;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Scolarite/RoomsIndex', [
            'rooms' => Room::orderBy('nom')->get(),
        ]);
    }

    public function store(StoreRoomRequest $request): RedirectResponse
    {
        Room::create($request->validated());
        return back()->with('success', 'La salle a été créée avec succès.');
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:rooms,nom,' . $room->id],
            'capacite' => ['required', 'integer', 'min:1'],
            'type_salle' => ['required', 'string', 'max:255'],
        ]);

        $room->update($validated);
        return back()->with('success', 'La salle a été mise à jour.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $room->delete();
        return back()->with('success', 'La salle a été supprimée.');
    }
}
