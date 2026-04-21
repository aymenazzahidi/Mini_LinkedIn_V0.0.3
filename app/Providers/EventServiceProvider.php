<?php

namespace App\Providers;

use App\Events\CandidatureDeposee;
use App\Events\StatutCandidatureMis;
use App\Listeners\LogCandidatureDeposee;
use App\Listeners\LogStatutCandidatureMis;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CandidatureDeposee::class => [
            LogCandidatureDeposee::class,
        ],
        StatutCandidatureMis::class => [
            LogStatutCandidatureMis::class,
        ],
    ];
}