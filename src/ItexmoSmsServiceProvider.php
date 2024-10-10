<?php

namespace Agnes\ItexmoSms;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ItexmoSmsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/itexmo.php', 'itexmo'
        );

        $this->app->singleton(ItexmoSms::class, function ($app) {
            return new ItexmoSms($app['config']['itexmo']);
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/itexmo.php' => $this->app->configPath('itexmo.php'),
            ], 'itexmo-config');
        }
    }

    public function provides(){
        return [ItexmoSms::class];
    }
}