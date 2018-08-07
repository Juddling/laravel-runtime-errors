<?php

namespace Juddling\RouteChecker;

use Illuminate\Support\ServiceProvider;

class RouteCheckerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            FindInvalidRouteCallsCommand::class,
            FindInvalidViewCallsCommand::class,
            FindInvalidRouteDefinitionsCommand::class,
            Commands\DieDumpCallsCommand::class,
        ]);
    }
}
