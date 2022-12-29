<?php

namespace Outl1ne\LaravelElasticLogger;

use Illuminate\Support\ServiceProvider;
use Outl1ne\LaravelElasticLogger\Providers\EventServiceProvider;

class LaravelElasticLoggerServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/config/config.php', 'elastic_logging');
        if(config('elastic_logging.elastic_enabled')){
            $this->app->register(EventServiceProvider::class);
        }
    }

    public function register(){}
}
