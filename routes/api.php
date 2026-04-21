<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidatureController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\ProfilController;
use Illuminate\Support\Facades\Route;

// ─── AUTH (public) ──────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ─── OFFRES PUBLIQUES ────────────────────────────────────────────────────────
Route::get('/offres',        [OffreController::class, 'index']);
Route::get('/offres/{offre}', [OffreController::class, 'show']);

// ─── ROUTES PROTÉGÉES (JWT) ──────────────────────────────────────────────────
Route::middleware('auth:api')->group(function () {

    // Auth
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me',       [AuthController::class, 'me']);

    // ── Profil (candidat uniquement) ─────────────────────────────────────────
    Route::middleware('role:candidat')->group(function () {
        Route::post('/profil',                                    [ProfilController::class, 'store']);
        Route::get('/profil',                                     [ProfilController::class, 'show']);
        Route::put('/profil',                                     [ProfilController::class, 'update']);
        Route::post('/profil/competences',                        [ProfilController::class, 'addCompetence']);
        Route::delete('/profil/competences/{competence}',         [ProfilController::class, 'removeCompetence']);

        // Candidatures
        Route::post('/offres/{offre}/candidater',                 [CandidatureController::class, 'candidater']);
        Route::get('/mes-candidatures',                           [CandidatureController::class, 'mesCandidatures']);
    });

    // ── Offres & candidatures (recruteur uniquement) ─────────────────────────
    Route::middleware('role:recruteur')->group(function () {
        Route::post('/offres',                                    [OffreController::class, 'store']);
        Route::put('/offres/{offre}',                             [OffreController::class, 'update']);
        Route::delete('/offres/{offre}',                          [OffreController::class, 'destroy']);

        Route::get('/offres/{offre}/candidatures',                [CandidatureController::class, 'candidaturesOffre']);
        Route::patch('/candidatures/{candidature}/statut',        [CandidatureController::class, 'changerStatut']);
    });

    // ── Administration (admin uniquement) ────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users',              [AdminController::class, 'users']);
        Route::delete('/users/{user}',    [AdminController::class, 'deleteUser']);
        Route::patch('/offres/{offre}',   [AdminController::class, 'toggleOffre']);
    });
});