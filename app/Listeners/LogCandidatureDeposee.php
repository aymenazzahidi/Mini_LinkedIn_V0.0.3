<?php

namespace App\Listeners;

use App\Events\CandidatureDeposee;
use Illuminate\Support\Facades\Log;

class LogCandidatureDeposee
{
    public function handle(CandidatureDeposee $event): void
    {
        $candidature = $event->candidature->load('profil.user', 'offre');
        $candidat    = $candidature->profil->user->name;
        $offre       = $candidature->offre->titre;
        $date        = now()->format('Y-m-d H:i:s');

        Log::channel('candidatures')->info(
            "[{$date}] Nouvelle candidature — Candidat : {$candidat} | Offre : {$offre}"
        );
    }
}