<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use Illuminate\Http\Request;

class OffreController extends Controller
{
    // GET /api/offres
    public function index(Request $request)
    {
        $query = Offre::where('actif', true)->with('recruteur');

        if ($request->filled('localisation')) {
            $query->where('localisation', 'like', '%' . $request->localisation . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $offres = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($offres);
    }

    // GET /api/offres/{offre}
    public function show(Offre $offre)
    {
        return response()->json($offre->load('recruteur', 'candidatures'));
    }

    // POST /api/offres
    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'        => 'required|string|max:255',
            'description'  => 'required|string',
            'localisation' => 'required|string',
            'type'         => 'required|in:CDI,CDD,stage',
        ]);

        $offre = auth()->user()->offres()->create($data);

        return response()->json($offre, 201);
    }

    // PUT /api/offres/{offre}
    public function update(Request $request, Offre $offre)
    {
        if ($offre->user_id !== auth()->id()) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $data = $request->validate([
            'titre'        => 'sometimes|string|max:255',
            'description'  => 'sometimes|string',
            'localisation' => 'sometimes|string',
            'type'         => 'sometimes|in:CDI,CDD,stage',
            'actif'        => 'sometimes|boolean',
        ]);

        $offre->update($data);

        return response()->json($offre);
    }

    // DELETE /api/offres/{offre}
    public function destroy(Offre $offre)
    {
        if ($offre->user_id !== auth()->id()) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $offre->delete();

        return response()->json(['message' => 'Offre supprimée.']);
    }
}