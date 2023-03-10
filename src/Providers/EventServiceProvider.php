<?php

namespace Outl1ne\LaravelElasticLogger\Providers;

use Outl1ne\LaravelElasticLogger\Listeners\PrepareLogPayload;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Log\Events\MessageLogged;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MessageLogged::class => [
            PrepareLogPayload::class
        ],
    ];

    /**
     * @return void
     */
    public function boot(): void
    {
        parent::boot();
    }
}
