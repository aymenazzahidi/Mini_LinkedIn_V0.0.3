<?php

namespace App\Http\Controllers;

use App\Events\CandidatureDeposee;
use App\Events\StatutCandidatureMis;
use App\Models\Candidature;
use App\Models\Offre;
use Illuminate\Http\Request;

class CandidatureController extends Controller
{
    // POST /api/offres/{offre}/candidater
    public function candidater(Request $request, Offre $offre)
    {
        $user   = auth()->user();
        $profil = $user->profil;

        if (!$profil) {
            return response()->json(['message' => 'Créez d\'abord votre profil.'], 422);
        }

        if (!$offre->actif) {
            return response()->json(['message' => 'Cette offre n\'est plus active.'], 422);
        }

        if (Candidature::where('offre_id', $offre->id)->where('profil_id', $profil->id)->exists()) {
            return response()->json(['message' => 'Vous avez déjà postulé à cette offre.'], 422);
        }

        $data = $request->validate([
            'message' => 'nullable|string',
        ]);

        $candidature = Candidature::create([
            'offre_id'  => $offre->id,
            'profil_id' => $profil->id,
            'message'   => $data['message'] ?? null,
            'statut'    => 'en_attente',
        ]);

        // Déclencher l'événement
        event(new CandidatureDeposee($candidature));

        return response()->json($candidature->load('offre', 'profil'), 201);
    }

    // GET /api/mes-candidatures
    public function mesCandidatures()
    {
        $profil = auth()->user()->profil;

        if (!$profil) {
            return response()->json(['message' => 'Profil non trouvé.'], 404);
        }

        $candidatures = $profil->candidatures()->with('offre.recruteur')->get();

        return response()->json($candidatures);
    }

    // GET /api/offres/{offre}/candidatures
    public function candidaturesOffre(Offre $offre)
    {
        if ($offre->user_id !== auth()->id()) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $candidatures = $offre->candidatures()->with('profil.user', 'profil.competences')->get();

        return response()->json($candidatures);
    }

    // PATCH /api/candidatures/{candidature}/statut
    public function changerStatut(Request $request, Candidature $candidature)
    {
        $offre = $candidature->offre;

        if ($offre->user_id !== auth()->id()) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $data = $request->validate([
            'statut' => 'required|in:en_attente,acceptee,refusee',
        ]);

        $ancienStatut = $candidature->statut;
        $candidature->update(['statut' => $data['statut']]);

        // Déclencher l'événement
        event(new StatutCandidatureMis($candidature, $ancienStatut));

        return response()->json($candidature);
    }
}