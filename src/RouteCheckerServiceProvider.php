<?php

namespace Juddling\RouteChecker;

use Illuminate\Support\ServiceProvider;

class RouteCheckerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            Commands\FindInvalidRouteCallsCommand::class,
            Commands\FindInvalidViewCallsCommand::class,
            Commands\FindInvalidRouteDefinitionsCommand::class,
            Commands\DieDumpCallsCommand::class,
        ]);
    }
}
