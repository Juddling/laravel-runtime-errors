<?php

namespace Juddling\RouteChecker;

use Illuminate\Support\Collection;
use Illuminate\Routing\Route;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Output\OutputInterface;

class FindInvalidRouteCalls extends FindInvalid
{
	/** @var Collection */
	protected $routeNames;
	protected $nameOfArgument = 'Route Names';


	protected function getFunctionName()
	{
		return 'route';
	}

	protected function check(string $routeName): bool
	{
		if ($this->routeNames) {
			return $this->routeNames->contains($routeName);
		}

		// set the routes
		$this->routeNames = collect(\Route::getRoutes())->map(function (Route $route) {
			return $route->getName();
		})->filter();

		// now try this function again
		return $this->check($routeName);
	}
}
