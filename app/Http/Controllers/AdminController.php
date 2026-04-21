<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\User;

class AdminController extends Controller
{
    // GET /api/admin/users
    public function users()
    {
        $users = User::withCount(['profil', 'offres'])->paginate(20);
        return response()->json($users);
    }

    // DELETE /api/admin/users/{user}
    public function deleteUser(User $user)
    {
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Impossible de supprimer un admin.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }

    // PATCH /api/admin/offres/{offre}
    public function toggleOffre(Offre $offre)
    {
        $offre->update(['actif' => !$offre->actif]);

        return response()->json([
            'message' => 'Statut de l\'offre mis à jour.',
            'actif'   => $offre->actif,
        ]);
    }
}