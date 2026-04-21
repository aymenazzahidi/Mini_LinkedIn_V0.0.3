<?php

namespace App\Http\Controllers;

use App\Models\Competence;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    // POST /api/profil
    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->profil) {
            return response()->json(['message' => 'Profil déjà créé.'], 422);
        }

        $data = $request->validate([
            'titre'        => 'required|string|max:255',
            'bio'          => 'nullable|string',
            'localisation' => 'nullable|string',
            'disponible'   => 'nullable|boolean',
        ]);

        $profil = $user->profil()->create($data);

        return response()->json($profil->load('competences'), 201);
    }

    // GET /api/profil
    public function show()
    {
        $profil = auth()->user()->profil;

        if (!$profil) {
            return response()->json(['message' => 'Profil non trouvé.'], 404);
        }

        return response()->json($profil->load('competences'));
    }

    // PUT /api/profil
    public function update(Request $request)
    {
        $profil = auth()->user()->profil;

        if (!$profil) {
            return response()->json(['message' => 'Profil non trouvé.'], 404);
        }

        $data = $request->validate([
            'titre'        => 'sometimes|string|max:255',
            'bio'          => 'nullable|string',
            'localisation' => 'nullable|string',
            'disponible'   => 'nullable|boolean',
        ]);

        $profil->update($data);

        return response()->json($profil->load('competences'));
    }

    // POST /api/profil/competences
    public function addCompetence(Request $request)
    {
        $data = $request->validate([
            'competence_id' => 'required|exists:competences,id',
            'niveau'        => 'required|in:débutant,intermédiaire,expert',
        ]);

        $profil = auth()->user()->profil;

        if (!$profil) {
            return response()->json(['message' => 'Profil non trouvé.'], 404);
        }

        if ($profil->competences()->where('competence_id', $data['competence_id'])->exists()) {
            return response()->json(['message' => 'Compétence déjà ajoutée.'], 422);
        }

        $profil->competences()->attach($data['competence_id'], ['niveau' => $data['niveau']]);

        return response()->json($profil->load('competences'));
    }

    // DELETE /api/profil/competences/{competence}
    public function removeCompetence(Competence $competence)
    {
        $profil = auth()->user()->profil;

        if (!$profil) {
            return response()->json(['message' => 'Profil non trouvé.'], 404);
        }

        $profil->competences()->detach($competence->id);

        return response()->json(['message' => 'Compétence retirée.']);
    }
}