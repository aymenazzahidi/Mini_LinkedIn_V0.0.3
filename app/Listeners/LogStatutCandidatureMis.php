<?php

namespace App\Listeners;

use App\Events\StatutCandidatureMis;
use Illuminate\Support\Facades\Log;

class LogStatutCandidatureMis
{
    public function handle(StatutCandidatureMis $event): void
    {
        $candidature  = $event->candidature;
        $ancienStatut = $event->ancienStatut;
        $nouveauStatut = $candidature->statut;
        $date          = now()->format('Y-m-d H:i:s');

        Log::channel('candidatures')->info(
            "[{$date}] Statut mis à jour — Candidature #{$candidature->id} | {$ancienStatut} → {$nouveauStatut}"
        );
    }
}